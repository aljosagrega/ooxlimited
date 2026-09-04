import "server-only";
import * as cheerio from "cheerio";
import { sanitizeBodyHtml } from "./sanitize";
import { getAllPosts, postAuthorName } from "./content";
import type { Post } from "./types";

/**
 * Dynamic blog rendering on top of the frozen WordPress snapshot.
 *
 * The blog is still served from frozen markup, but its post list and the
 * individual post pages are regenerated from posts.json at render time so that
 * articles created/edited in the admin CMS go live and drafts drop off — no
 * re-`freeze` needed.
 *
 * - `renderTemplatedPost()` fills the shared `_post-template` shell (a stripped
 *   copy of a real frozen post) with a CMS post that has no snapshot of its own.
 * - `applyBlogIndex()` rebuilds the `/blog/` (and `/blog/page/N/`) card grid.
 *
 * Both keep the theme's layout, classes and styling untouched — only text,
 * links, images and list membership change.
 */

/**
 * frozen/<key>.html shell borrowed by every CMS post without its own snapshot.
 * It is a copy of a real frozen post (`co-development-vs-outsourcing`) with the
 * `<article class="oox-blog-article">` body emptied. Regenerate after a re-freeze
 * by copying a fresh single-post snapshot's `.html` / `.head.html` / `.meta.json`
 * and clearing that one element.
 */
export const POST_TEMPLATE_KEY = "_post-template";

/** posts per blog archive page — matches the frozen omero widget */
export const BLOG_PAGE_SIZE = 8;

const AUTHOR_HREF = "/game-development-team/";
const FALLBACK_IMG = "/og-default.png";

/* eslint-disable @typescript-eslint/no-explicit-any */

function esc(s: unknown): string {
  return String(s ?? "").replace(/[&<>"]/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[c] as string));
}

/** "august 28, 2026" — matches the frozen card markup (theme also lowercases in CSS) */
function fmtDate(iso: string): string {
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return "";
  return d
    .toLocaleDateString("en-US", { month: "long", day: "numeric", year: "numeric", timeZone: "UTC" })
    .toLowerCase();
}

function setThumb(img: any, post: Post) {
  // The theme's thumb CSS is `object-fit:cover; height:100%` with no width, and
  // relies on `img{max-width:100%}` clamping a >=container width down to a
  // square. Without width/height attributes a portrait source collapses to a
  // narrow strip — so force square attributes; the real crop is `object-fit`.
  img
    .attr("src", post.featuredImage?.url || FALLBACK_IMG)
    .attr("alt", post.featuredImage?.alt || post.title)
    .attr("width", "1000")
    .attr("height", "1000")
    .removeAttr("srcset")
    .removeAttr("sizes");
}

/* ------------------------------------------------------------- blog index --- */

function fillBafCard($: any, card: any, post: Post, kind: "featured" | "card") {
  const href = `/${post.slug}/`;
  card.attr(
    "class",
    `omero-baf__${kind} post-${post.id} post type-post status-publish format-standard has-post-thumbnail hentry`,
  );
  card
    .find("a[href]")
    .not(".omero-baf__tags a, .omero-baf__meta a")
    .each((_: number, a: any) => $(a).attr("href", href));

  const img = card.find("img.wp-post-image").first();
  if (img.length) setThumb(img, post);

  const meta = card.find(".omero-baf__meta").first();
  if (meta.length) {
    meta.html(
      `${esc(fmtDate(post.date))} <span class="omero-baf__sep">by</span> ` +
        `<a href="${AUTHOR_HREF}" rel="author">${esc(postAuthorName(post) || "OOX Limited")}</a>`,
    );
  }

  card.find(".omero-baf__title a").first().text(post.title).attr("href", href);

  if (kind === "featured") {
    const ex = card.find(".omero-baf__excerpt").first();
    if (ex.length) ex.text(post.excerpt || "");
    const tags = card.find(".omero-baf__tags").first();
    if (tags.length) {
      const cats = (post.categories || []).filter((c) => c && c.name);
      if (cats.length) {
        tags.html(cats.map((c) => `<li class="omero-baf__tag"><a href="/blog/">${esc(c.name)}</a></li>`).join(""));
      } else {
        tags.remove();
      }
    }
  }
}

/** URL for blog archive page `n` (page 1 is the bare `/blog/`). */
export const blogPageHref = (n: number): string => (n <= 1 ? "/blog/" : `/blog/page/${n}/`);

/** Highest blog archive page number that has at least one post. */
export function blogPageCount(): number {
  return Math.max(1, Math.ceil(getAllPosts().length / BLOG_PAGE_SIZE));
}

/** Which page numbers to render: 1, last, and a ±1 window around current, with
 *  `0` marking an elided gap. */
function pageWindow(current: number, total: number): number[] {
  const keep = new Set<number>([1, total, current - 1, current, current + 1]);
  const nums = [...keep].filter((n) => n >= 1 && n <= total).sort((a, b) => a - b);
  const out: number[] = [];
  for (let i = 0; i < nums.length; i++) {
    if (i > 0 && nums[i] - nums[i - 1] > 1) out.push(0);
    out.push(nums[i]);
  }
  return out;
}

/**
 * Standard WordPress `paginate_links()` markup — the omero theme already ships
 * `.pagination` / `.page-numbers` styles, so no new CSS is needed.
 */
function paginationHtml(current: number, total: number): string {
  if (total <= 1) return "";
  const parts: string[] = [];
  if (current > 1) {
    parts.push(`<a class="prev page-numbers" href="${blogPageHref(current - 1)}">Previous</a>`);
  }
  for (const n of pageWindow(current, total)) {
    if (n === 0) {
      parts.push(`<span class="page-numbers dots">&hellip;</span>`);
    } else if (n === current) {
      parts.push(`<span aria-current="page" class="page-numbers current">${n}</span>`);
    } else {
      parts.push(`<a class="page-numbers" href="${blogPageHref(n)}">${n}</a>`);
    }
  }
  if (current < total) {
    parts.push(`<a class="next page-numbers" href="${blogPageHref(current + 1)}">Next</a>`);
  }
  // `.pagination` / `.page-numbers` styling is theme-provided; the theme only
  // flex-centres it via a `.blog-style-grid + .pagination` sibling rule that
  // doesn't match here, so restore that one declaration inline.
  return (
    `<nav class="pagination" aria-label="Blog pages" style="display:flex;justify-content:center">` +
    `<div class="nav-links">${parts.join("")}</div></nav>`
  );
}

/**
 * Rebuild the archive card grid for `/blog/` and `/blog/page/N/` from live
 * posts and inject pagination. No-op for any other route, or if the frozen
 * markup lacks the expected card containers.
 */
export function applyBlogIndex(routePath: string, body: string): string {
  const m = routePath.match(/^\/blog\/(?:page\/(\d+)\/)?$/);
  if (!m) return body;
  const page = m[1] ? parseInt(m[1], 10) : 1;
  const total = blogPageCount();

  const slice = getAllPosts().slice((page - 1) * BLOG_PAGE_SIZE, page * BLOG_PAGE_SIZE);
  if (!slice.length) return body;

  const $ = cheerio.load(body, {}, false);
  const baf = $(".omero-baf").first();
  const featured = baf.find(".omero-baf__featured").first();
  const grid = baf.find(".omero-baf__grid").first();
  const cardProto = grid.find(".omero-baf__card").first();
  if (!baf.length || !featured.length || !grid.length || !cardProto.length) return body;

  fillBafCard($, featured, slice[0], "featured");

  const proto = cardProto.clone();
  grid.empty();
  for (const post of slice.slice(1)) {
    const c = proto.clone();
    fillBafCard($, c, post, "card");
    grid.append(c);
  }

  const nav = paginationHtml(page, total);
  if (nav) {
    const host = baf.closest(".elementor-widget-container");
    (host.length ? host : baf).append(nav);
  }

  return $.html();
}

/* ----------------------------------------------------------- single posts --- */

function rebuildRecentPosts($: any, current: Post) {
  const grid = $(".omero-related-posts__grid").first();
  const proto = grid.find(".omero-related-posts__card").first();
  if (!grid.length || !proto.length) return;

  const recent = getAllPosts().filter((p) => p.slug !== current.slug).slice(0, 3);
  if (!recent.length) {
    $(".omero-related-posts").remove();
    return;
  }

  const p0 = proto.clone();
  grid.empty();
  for (const post of recent) {
    const c = p0.clone();
    const href = `/${post.slug}/`;
    c.find("a[href]").each((_: number, a: any) => $(a).attr("href", href));
    const img = c.find("img.wp-post-image").first();
    if (img.length) setThumb(img, post);
    const meta = c.find(".omero-related-posts__meta").first();
    if (meta.length) {
      meta.html(
        `${esc(fmtDate(post.date))}<span class="omero-related-posts__sep"> by </span>` +
          `<a href="${AUTHOR_HREF}" rel="author">${esc(postAuthorName(post) || "OOX Limited")}</a>`,
      );
    }
    c.find(".omero-related-posts__card-title a").first().text(post.title).attr("href", href);
    grid.append(c);
  }
}

/**
 * Fill the `_post-template` shell with a CMS post that has no frozen snapshot.
 * The visible article content is entirely `post.bodyHtml`; everything else
 * (title, canonical, OG, JSON-LD) is already synthesised from posts.json by the
 * route's `generateMetadata` / `pageJsonLd`.
 */
export function renderTemplatedPost(shellBody: string, post: Post): string {
  const $ = cheerio.load(shellBody, {}, false);

  const art = $("article.oox-blog-article").first();
  if (!art.length) return shellBody;
  art.html(sanitizeBodyHtml(post.bodyHtml || ""));

  const outer = $('article[id^="post-"]').first();
  if (outer.length) {
    outer.attr("id", `post-${post.id}`);
    outer.attr(
      "class",
      `post-${post.id} post type-post status-publish format-standard has-post-thumbnail hentry omero-style-post-1`,
    );
  }

  rebuildRecentPosts($, post);
  return $.html();
}

/* eslint-enable @typescript-eslint/no-explicit-any */
