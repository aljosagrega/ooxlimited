/**
 * Make the frozen marketing pages editable.
 *
 * Walks each frozen page body, tags every editable text node + image with a
 * stable `data-oox-e="<id>"` attribute (written back into the frozen HTML), and
 * emits `src/data/pagemaps/<key>.json` — the ordered list of
 * `{ id, kind, label, group, value }` the admin renders as a form.
 *
 * At render time `applyPageEdits()` (src/lib/fieldMap.ts) patches the frozen
 * body from a page's `edits` map. Layout is never touched — only leaf content.
 *
 * Run after `npm run freeze`:  npx tsx scripts/snapshot/build-pagemaps.ts
 */
import fs from "fs";
import path from "path";
import * as cheerio from "cheerio";

const ROOT = path.join(__dirname, "../..");
const FROZEN = path.join(ROOT, "src/data/frozen");
const OUT = path.join(ROOT, "src/data/pagemaps");

const pages: { path: string; slug: string; isFront: boolean }[] = JSON.parse(
  fs.readFileSync(path.join(ROOT, "src/data/pages.json"), "utf-8"),
);

function routeKey(routePath: string): string {
  if (routePath === "/") return "home";
  return routePath.replace(/^\/|\/$/g, "").replace(/\//g, "__") || "home";
}

const TEXT_TAGS = new Set(["h1", "h2", "h3", "h4", "h5", "h6", "p", "li", "blockquote", "figcaption"]);
const SKIP_CLASS = /elementor-screen-only|sr-only|visually-hidden/;

interface Entry {
  id: string;
  kind: "text" | "html" | "image" | "imageAlt" | "href";
  label: string;
  group: string;
  value: string;
}

function clean(s: string): string {
  return s.replace(/\s+/g, " ").trim();
}
function truncate(s: string, n = 60): string {
  return s.length > n ? s.slice(0, n - 1) + "…" : s;
}

/** Header / footer / nav is chrome — edited via Menus + Settings, not here. */
const CHROME_SEL =
  "header, footer, nav, .elementor-location-header, .elementor-location-footer, " +
  ".site-header, .site-footer, .hfe-nav-menu, . elementor-widget-nav-menu, " +
  ".elementor-widget-omero-nav-menu, .omero-menu-canvas, .breadcrumbs, .lexus-breadcrumb, " +
  ".hfe-scroll-to-top-wrap, .elementor-widget-omero-menu-canvas, " +
  ".omero-mobile-nav, .menu-scroll-mobile, .mobile-nav-tabs, .search-popup, " +
  ".omero-login-form-ajax, .elementor-widget-omero-login, form.omero-login-form-ajax";

/** Content pulled from another module (Blog posts / Team) — the article and
 *  profile lists themselves. Their surrounding copy on the index pages stays
 *  editable; the generated rows do not. Keyed by route key. */
const MODULE_LOOP_SEL: Record<string, string> = {
  blog:
    ".elementor-widget-omero-blog-archive-featured-grid, .elementor-widget-omero-blog-archive, " +
    ".elementor-widget-omero-blog-grid, .elementor-widget-posts, .elementor-posts-container, " +
    "[class*='elementor-widget-omero-blog'], .elementor-pagination, nav.pagination",
  blog__page__2: "same-as-blog",
  "game-development-team":
    ".elementor-widget-omero-teams-list, .elementor-widget-omero-teams-accordion, " +
    ".elementor-widget-omero-team, .omero-team, [class*='elementor-widget-omero-team']",
};
MODULE_LOOP_SEL.blog__page__2 = MODULE_LOOP_SEL.blog;

function buildPagemap(key: string) {
  const htmlPath = path.join(FROZEN, `${key}.html`);
  if (!fs.existsSync(htmlPath)) return null;
  // Start from a clean slate so the map is deterministic across re-runs even
  // when the frozen HTML wasn't regenerated (skip selectors changed, etc.).
  const raw = fs
    .readFileSync(htmlPath, "utf-8")
    .replace(/ data-oox-e(?:-alt)?="[^"]*"/g, "");
  const $ = cheerio.load(raw, {}, false);

  const entries: Entry[] = [];
  let n = 0;
  const id = () => `e${++n}`;
  const seenValues = new Set<string>();
  const skipSel = MODULE_LOOP_SEL[key] ? `${CHROME_SEL}, ${MODULE_LOOP_SEL[key]}` : CHROME_SEL;
  const inChrome = (el: cheerio.Cheerio<never>) => el.closest(skipSel).length > 0;

  // Track the current section label from the nearest preceding heading.
  const groupFor = (el: cheerio.Cheerio<never>): string => {
    const h = el.closest("section, .elementor-section, .e-con, .elementor-widget-wrap")
      .find("h1, h2, h3").first().text();
    return clean(h) ? truncate(clean(h), 40) : "Page";
  };

  // ---- text nodes -------------------------------------------------------
  $("h1, h2, h3, h4, h5, h6, p, li, blockquote, figcaption, button, a.elementor-button, .elementor-button-text").each((_, node) => {
    const el = $(node);
    const tag = (node as unknown as { tagName: string }).tagName?.toLowerCase();
    if (!tag || el.attr("data-oox-e")) return;
    if (SKIP_CLASS.test(el.attr("class") || "")) return;
    if (inChrome(el)) return;

    const childTags = el.children().toArray().map((c) => (c as unknown as { tagName: string }).tagName?.toLowerCase());
    const inlineOnly = childTags.every((t) => ["b", "i", "em", "strong", "u", "span", "br", "a"].includes(t));
    if (!inlineOnly) return;
    const txt = clean(el.text());
    if (!txt || txt.length < 2 || txt.length > 600) return;
    if (seenValues.has(tag + "|" + txt)) return;
    seenValues.add(tag + "|" + txt);

    const isBtn = tag === "button" || /elementor-button/.test(el.attr("class") || "");
    const eid = id();
    el.attr("data-oox-e", eid);
    const rawHtml = clean(el.html() || "");
    const hasMarkup = /<(b|i|em|strong|u|span|a|br)\b/i.test(rawHtml);
    entries.push({
      id: eid,
      kind: hasMarkup ? "html" : "text",
      label: isBtn ? `Button: “${truncate(txt, 40)}”` : `${tag.toUpperCase()}: “${truncate(txt)}”`,
      group: groupFor(el),
      value: hasMarkup ? rawHtml : txt,
    });
  });

  // ---- images ---------------------------------------------------------
  $("img").each((_, node) => {
    const el = $(node);
    if (SKIP_CLASS.test(el.attr("class") || "") || inChrome(el)) return;
    const src = el.attr("src") || "";
    if (!src || src.startsWith("data:")) return;
    if (seenValues.has("img|" + src)) return;
    seenValues.add("img|" + src);
    const eid = id();
    el.attr("data-oox-e", eid);
    const alt = el.attr("alt") || "";
    entries.push({
      id: eid,
      kind: "image",
      label: `Image${alt ? `: ${truncate(alt, 40)}` : ` (${src.split("/").pop()})`}`,
      group: groupFor(el),
      value: src,
    });
    if (alt) {
      const aid = id();
      el.attr("data-oox-e-alt", aid);
      entries.push({ id: aid, kind: "imageAlt", label: `Alt for ${truncate(alt, 30)}`, group: groupFor(el), value: alt });
    }
  });

  fs.writeFileSync(htmlPath, $.html());
  fs.mkdirSync(OUT, { recursive: true });
  fs.writeFileSync(path.join(OUT, `${key}.json`), JSON.stringify({ entries }, null, 2));
  return entries.length;
}

// Every public route gets a pagemap so its frozen content is editable from the
// admin: the 6 marketing pages + every blog post, team member and service.
const routeKeys = new Set<string>();
for (const p of pages) routeKeys.add(routeKey(p.path));

const posts: { slug: string }[] = JSON.parse(fs.readFileSync(path.join(ROOT, "src/data/posts.json"), "utf-8"));
const team: { slug: string }[] = JSON.parse(fs.readFileSync(path.join(ROOT, "src/data/team.json"), "utf-8"));
const services: { slug: string }[] = JSON.parse(fs.readFileSync(path.join(ROOT, "src/data/services.json"), "utf-8"));
for (const p of posts) routeKeys.add(routeKey(`/${p.slug}/`));
for (const t of team) routeKeys.add(routeKey(`/team/${t.slug}/`));
for (const s of services) routeKeys.add(routeKey(`/service/${s.slug}/`));

let total = 0;
let done = 0;
for (const key of routeKeys) {
  const count = buildPagemap(key);
  if (count != null) {
    total += count;
    done++;
  }
}
console.log(`\n${total} editable fields across ${done} pages -> src/data/pagemaps/`);
