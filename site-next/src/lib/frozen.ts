import "server-only";
import fs from "fs";
import path from "path";

const DIR = path.join(process.cwd(), "src/data/frozen");

export interface FrozenPage {
  key: string;
  routePath: string;
  bodyClass: string;
  lang: string;
  headHtml: string;
  bodyHtml: string;
}

export interface FrozenAssets {
  /** <link rel="stylesheet"> hrefs, in order */
  stylesheets: string[];
  /** raw <link rel="preconnect|dns-prefetch|preload"> tags to pass through */
  headLinks: string[];
  /** inline <style> bodies from <head> */
  styles: string[];
  /** every <script>, head then body, in document order */
  scripts: { src?: string; code?: string; module?: boolean }[];
  /** body markup with all <script> tags removed */
  bodyHtml: string;
  bodyClass: string;
  lang: string;
}

/** frozen/<key>.html basename convention: "/" -> home, "/a/b/" -> "a__b". */
export function routeKey(routePath: string): string {
  if (routePath === "/" || routePath === "") return "home";
  return routePath.replace(/^\/|\/$/g, "").replace(/\//g, "__") || "home";
}

export function hasFrozen(routePath: string): boolean {
  return fs.existsSync(path.join(DIR, `${routeKey(routePath)}.html`));
}

export function getFrozen(routePath: string): FrozenPage | null {
  const key = routeKey(routePath);
  const htmlPath = path.join(DIR, `${key}.html`);
  if (!fs.existsSync(htmlPath)) return null;
  const bodyHtml = fs.readFileSync(htmlPath, "utf-8");
  const headHtml = readOptional(path.join(DIR, `${key}.head.html`));
  const meta = readJson(path.join(DIR, `${key}.meta.json`));
  return {
    key,
    routePath,
    bodyHtml,
    headHtml,
    bodyClass: String(meta.bodyClass ?? ""),
    lang: String(meta.lang ?? "en-US"),
  };
}

/** Parse a frozen page into the pieces the renderer needs. */
export function getFrozenAssets(routePath: string): FrozenAssets | null {
  const page = getFrozen(routePath);
  if (!page) return null;

  const stylesheets: string[] = [];
  const headLinks: string[] = [];
  const styles: string[] = [];
  const scripts: FrozenAssets["scripts"] = [];

  for (const m of page.headHtml.matchAll(/<link\b[^>]*>/gi)) {
    const tag = m[0];
    const rel = /rel=["']([^"']+)["']/i.exec(tag)?.[1] ?? "";
    const href = /href=["']([^"']+)["']/i.exec(tag)?.[1] ?? "";
    if (rel.includes("stylesheet") && href) stylesheets.push(href);
    else if (/preconnect|dns-prefetch|preload/i.test(rel)) headLinks.push(tag);
  }
  for (const m of page.headHtml.matchAll(/<style\b[^>]*>([\s\S]*?)<\/style>/gi)) {
    styles.push(m[1]);
  }
  for (const m of page.headHtml.matchAll(/<script\b([^>]*)>([\s\S]*?)<\/script>/gi)) {
    pushScript(scripts, m[1], m[2]);
  }

  // Body: pull scripts (in order) then strip them from the markup.
  for (const m of page.bodyHtml.matchAll(/<script\b([^>]*)>([\s\S]*?)<\/script>/gi)) {
    pushScript(scripts, m[1], m[2]);
  }
  const bodyHtml = page.bodyHtml.replace(/<script\b[^>]*>[\s\S]*?<\/script>/gi, "");

  // Dedupe external scripts by URL (ignoring ?ver=) — WordPress sometimes
  // enqueues the same handle in both <head> and the footer, and the legacy
  // omero addon scripts declare top-level `let`s that throw on a second run.
  const seenSrc = new Set<string>();
  const dedupedScripts = scripts.filter((s) => {
    if (!s.src) return true;
    const key = s.src.split("?")[0];
    if (seenSrc.has(key)) return false;
    seenSrc.add(key);
    return true;
  });

  return { stylesheets: dedupe(stylesheets), headLinks, styles, scripts: dedupedScripts, bodyHtml, bodyClass: page.bodyClass, lang: page.lang };
}

function dedupe(arr: string[]): string[] {
  const seen = new Set<string>();
  return arr.filter((x) => {
    const k = x.split("?")[0];
    if (seen.has(k)) return false;
    seen.add(k);
    return true;
  });
}

function pushScript(
  out: FrozenAssets["scripts"],
  attrs: string,
  code: string,
) {
  const src = /src=["']([^"']+)["']/i.exec(attrs)?.[1];
  const type = /type=["']([^"']+)["']/i.exec(attrs)?.[1] ?? "";
  // Anything that isn't executable JS: JSON-LD, Elementor's `text/template`
  // widget payloads, importmaps, speculation rules, etc.
  if (type && !/^(text\/javascript|application\/javascript|module)$/i.test(type)) return;
  const module = /module/i.test(type);
  if (src) {
    if (/wp-emoji-loader|wp-emoji-release/i.test(src)) return;
    // Form plugin JS is replaced by <FrozenForms> — dropping it stops the
    // original handlers from double-submitting to /wp-json or admin-ajax.
    if (/contact-form-7|\/newsletter\/|plugins\/newsletter|mailchimp-for-wp|mc4wp/i.test(src)) return;
    // WP login widget ajax — inert here (no wp-admin).
    if (/frontend\/login\.js|omero-login/i.test(src)) return;
    out.push({ src, module });
    return;
  }
  const trimmed = code.trim();
  if (!trimmed) return;
  // Bare JSON blobs (config <script> with no type) — not runnable as statements.
  if (/^[[{]/.test(trimmed) && !/^\{\s*(function|var|let|const|window|document|"use)/.test(trimmed)) return;
  if (/_wpemojiSettings|wpemoji/i.test(trimmed)) return;
  out.push({ code: trimmed, module });
}

export function allFrozenKeys(): string[] {
  if (!fs.existsSync(DIR)) return [];
  return fs
    .readdirSync(DIR)
    .filter((f) => f.endsWith(".html") && !f.endsWith(".head.html"))
    .map((f) => f.replace(/\.html$/, ""));
}

function readOptional(p: string): string {
  try {
    return fs.readFileSync(p, "utf-8");
  } catch {
    return "";
  }
}
function readJson(p: string): Record<string, unknown> {
  try {
    return JSON.parse(fs.readFileSync(p, "utf-8"));
  } catch {
    return {};
  }
}
