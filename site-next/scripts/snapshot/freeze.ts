/**
 * Snapshot the running local WordPress site (http://localhost:8080) into
 * src/data/frozen/<key>.{html,head.html,meta.json} — the rendered <body> and the
 * <head> resource stack for each public route.
 *
 * Static assets are NOT downloaded here: public/wp-content and public/wp-includes
 * are symlinks to the WordPress docroot (see repo setup), so every /wp-content/*
 * and /wp-includes/* ref in the frozen HTML already resolves. Only the origin is
 * rewritten (absolute -> root-relative) so the mirror is host-agnostic.
 *
 * Re-runnable. From site-next/:  npm run freeze
 */
import fs from "fs";
import path from "path";

const WP = process.env.OOX_WP_ORIGIN || "http://localhost:8080";
const ROOT = path.join(__dirname, "../..");
const FROZEN = path.join(ROOT, "src/data/frozen");
const PUBLIC = path.join(ROOT, "public");

/**
 * Some stylesheets bake in ABSOLUTE URLs (mask-image, background-image) pointing
 * at the WP or live origin — e.g. omero-child's `style.css`:
 *   .elementor-widget-omero-teams-list … .post-thumbnail{
 *     mask-image:url("https://ooxlimited.com/.../maskk.svg") }
 * Served from the Next app those become cross-origin, the fetch fails, and a
 * failed `mask-image` renders the element INVISIBLE (this is why team photos /
 * history images "disappear"). Fetch every CSS a frozen page links, and any
 * that carries an absolute origin gets a rewritten copy under `public/_css/`
 * with the frozen `<link href>` repointed.
 *
 * Elementor's per-page compiled CSS lives under
 * `/wp-content/uploads/elementor/css/` — the same tree as the media library,
 * which `collect-assets` deliberately does NOT bundle (it's host state, rsynced
 * at deploy). But `post-<id>.css` / `custom-widget-*.css` are generated BUILD
 * output, not media, and the deploy's rsynced `uploads/` only happens to carry
 * whatever the live WordPress last compiled — so most `post-*.css` 404 there and
 * the page's layout collapses. Force every `uploads/elementor/css/*` stylesheet
 * a frozen page links into `public/_css/` too, regardless of absolute origins,
 * so the bundle is genuinely self-contained.
 */
// The WordPress docroot that Docker serves (repo-root `site/`), or an override.
const WP_DOCROOT = process.env.OOX_WP_DOCROOT || path.join(ROOT, "../site");
const CSS_DEST = path.join(PUBLIC, "_css");

/** Map a site-absolute CSS path to its file on disk. */
function cssDiskPath(sitePath: string): string | null {
  const clean = sitePath.split("?")[0];
  if (clean.startsWith("/wp-content/") || clean.startsWith("/wp-includes/")) {
    return path.join(WP_DOCROOT, clean);
  }
  return null;
}

const rewrittenCss = new Map<string, string>(); // original site path -> "/_css/..." path

/** Elementor per-page/per-widget compiled CSS — build output, not media. */
const ELEMENTOR_GENERATED_CSS = /^\/wp-content\/uploads\/elementor\/css\//;

function rewriteCssFile(sitePath: string) {
  const clean = sitePath.split("?")[0];
  if (rewrittenCss.has(clean)) return;
  const disk = cssDiskPath(clean);
  if (!disk || !fs.existsSync(disk)) return;

  let css = fs.readFileSync(disk, "utf-8");
  const hasAbsoluteOrigin = /(https?:\/\/localhost:8080|https?:\/\/(?:www\.)?ooxlimited\.com)/.test(css);
  // Absolute-origin CSS must be rewritten; Elementor's generated CSS must be
  // bundled even when clean, because the deploy's `uploads/` won't carry it.
  if (!hasAbsoluteOrigin && !ELEMENTOR_GENERATED_CSS.test(clean)) return;

  css = css
    .replace(/https?:\/\/localhost:8080/g, "")
    .replace(/https?:\/\/(?:www\.)?ooxlimited\.com/g, "");

  const dest = path.join(CSS_DEST, clean.replace(/^\//, ""));
  fs.mkdirSync(path.dirname(dest), { recursive: true });
  fs.writeFileSync(dest, css);
  rewrittenCss.set(clean, "/_css" + clean);
}


const read = <T,>(p: string): T =>
  JSON.parse(fs.readFileSync(path.join(ROOT, "src/data", p), "utf-8"));

function routeKey(routePath: string): string {
  if (routePath === "/") return "home";
  return routePath.replace(/^\/|\/$/g, "").replace(/\//g, "__") || "home";
}

async function fetchText(url: string): Promise<string> {
  const r = await fetch(url, { redirect: "manual" });
  if (r.status >= 300 && r.status < 400) throw new Error(`${r.status} redirect -> ${r.headers.get("location")}`);
  if (!r.ok) throw new Error(`${r.status}`);
  return r.text();
}

function splitDoc(html: string) {
  const headMatch = html.match(/<head[^>]*>([\s\S]*?)<\/head>/i);
  const bodyMatch = html.match(/<body([^>]*)>([\s\S]*?)<\/body>/i);
  const head = headMatch?.[1] ?? "";
  const bodyAttrs = bodyMatch?.[1] ?? "";
  let body = bodyMatch?.[2] ?? html;
  const lang = html.match(/<html[^>]*\blang="([^"]+)"/i)?.[1] ?? "en-US";
  const bodyClass = bodyAttrs.match(/class="([^"]*)"/i)?.[1] ?? "";

  const headLines: string[] = [];

  for (const m of head.matchAll(/<link\b[^>]*>/gi)) {
    const tag = m[0];
    if (!/rel=["'](stylesheet|preconnect|dns-prefetch|preload)["']/i.test(tag)) continue;
    if (/dashicons|wp-block-library|admin-bar/i.test(tag)) continue;
    headLines.push(tag);
  }
  for (const m of head.matchAll(/<style\b[^>]*>[\s\S]*?<\/style>/gi)) {
    if (/#wpadminbar|admin-bar/i.test(m[0])) continue;
    headLines.push(m[0]);
  }
  for (const m of head.matchAll(/<script\b[^>]*>[\s\S]*?<\/script>/gi)) {
    const t = m[0];
    if (/wp-emoji|wpemoji|_wpemojiSettings/i.test(t)) continue;
    if (/gtag\/js|googletagmanager|google-analytics|hotjar|clarity|facebook\.net|fbevents/i.test(t)) continue;
    headLines.push(t);
  }

  // Body cleanup: drop the admin bar + analytics/GTM noscript + emoji.
  body = body
    .replace(/<div id="wpadminbar"[\s\S]*?<\/div>\s*(?=<\/body>|$)/i, "")
    .replace(/<style[^>]*id="wp-emoji[\s\S]*?<\/style>/gi, "")
    .replace(/<!--\s*Google Tag Manager[\s\S]*?-->/gi, "")
    .replace(/<noscript><iframe[^>]*googletagmanager[\s\S]*?<\/noscript>/gi, "");

  return { headHtml: headLines.join("\n"), bodyHtml: body, bodyClass, lang };
}

async function freezeRoute(routePath: string) {
  const key = routeKey(routePath);
  let html: string;
  try {
    html = await fetchText(WP + routePath);
  } catch (e) {
    console.warn(`  ! skip ${routePath}: ${(e as Error).message}`);
    return false;
  }
  const split = splitDoc(html);
  let headHtml = split.headHtml;
  const { bodyHtml, bodyClass, lang } = split;

  // Rewrite any linked CSS that carries an absolute origin (broken cross-origin
  // masks/backgrounds), and repoint the <link href> at the rewritten copy.
  for (const m of headHtml.matchAll(/<link\b[^>]*rel=["']stylesheet["'][^>]*>/gi)) {
    const href = /href=["']([^"']+)["']/i.exec(m[0])?.[1];
    if (!href) continue;
    const sitePath = href.replace(/^https?:\/\/[^/]+/, "").split("?")[0];
    rewriteCssFile(sitePath);
    const rewritten = rewrittenCss.get(sitePath);
    if (rewritten) headHtml = headHtml.split(sitePath).join(rewritten);
  }

  const deorigin = (s: string) =>
    s
      .split(WP).join("")
      .replace(/https?:\/\/localhost:8080/g, "")
      .replace(/https?:\\\/\\\/localhost:8080/g, "")
      // Some Elementor JSON / inline-style fields still hold absolute live-domain
      // URLs that the DB search-replace didn't reach.
      .replace(/https?:\/\/(?:www\.)?ooxlimited\.com/g, "")
      .replace(/https?:\\\/\\\/(?:www\.)?ooxlimited\.com/g, "");

  fs.mkdirSync(FROZEN, { recursive: true });
  fs.writeFileSync(path.join(FROZEN, `${key}.html`), deorigin(bodyHtml));
  fs.writeFileSync(path.join(FROZEN, `${key}.head.html`), deorigin(headHtml));
  fs.writeFileSync(
    path.join(FROZEN, `${key}.meta.json`),
    JSON.stringify({ routePath, bodyClass, lang }, null, 2),
  );
  console.log(`  froze ${routePath} -> ${key} (${(bodyHtml.length / 1024).toFixed(0)} KB)`);
  return true;
}

async function main() {
  const pages = read<{ path: string }[]>("pages.json");
  const posts = read<{ slug: string }[]>("posts.json");
  const services = read<{ slug: string }[]>("services.json");
  const team = read<{ slug: string }[]>("team.json");

  const routes = new Set<string>();
  for (const p of pages) routes.add(p.path);
  for (const p of posts) routes.add(`/${p.slug}/`);
  for (const s of services) routes.add(`/service/${s.slug}/`);
  for (const t of team) routes.add(`/team/${t.slug}/`);
  routes.add("/blog/page/2/");


  console.log(`freezing ${routes.size} routes from ${WP}`);
  let ok = 0;
  for (const r of [...routes].sort()) if (await freezeRoute(r)) ok++;
  console.log(`\nfroze ${ok}/${routes.size} routes.`);
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});
