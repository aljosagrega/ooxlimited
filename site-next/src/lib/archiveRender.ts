import "server-only";
import * as cheerio from "cheerio";
import { getAllPosts, postAuthorName } from "./content";
import { listCategories, postCategories, categoryHref, type CategoryTerm } from "./taxonomy";
import { BLOG_PAGE_SIZE } from "./blogRender";
import type { Post } from "./types";

/**
 * The category archive ("Blog - otvara se kada se kliknu tagovi" in the Nove
 * stranice na sajtu Figma) and the sidebar the blog index was missing.
 *
 * Built as HTML injected into the frozen blog shell rather than as React, for
 * two reasons: the hero, footer and every surrounding style come free from that
 * shell, and the filters are a plain GET <form>, so search and the category
 * multi-select work with no JavaScript at all. ArchiveFilters.tsx only adds
 * auto-submit on top.
 *
 * Card internals (thumb-wrap / thumb-shape / wp-post-image) are copied from the
 * theme's own markup so the notched image mask and hover behaviour apply here
 * exactly as they do on the blog grid.
 */

const AUTHOR_HREF = "/game-development-team/";
const FALLBACK_IMG = "/og-default.png";

function esc(s: unknown): string {
  return String(s ?? "").replace(/[&<>"]/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[c] as string));
}

function fmtDate(iso: string): string {
  const d = new Date(iso);
  if (isNaN(+d)) return "";
  return d.toLocaleDateString("en-US", { month: "long", day: "numeric", year: "numeric" }).toLowerCase();
}

function thumbFor(p: Post): { src: string; alt: string } {
  return { src: p.featuredImage?.url || FALLBACK_IMG, alt: p.featuredImage?.alt || p.title || "" };
}

/* ------------------------------------------------------------------ list --- */

function chipList(p: Post): string {
  const cats = postCategories(p);
  if (!cats.length) return "";
  return (
    `<ul class="omero-baf__tags oox-arch__tags">` +
    cats.map((c) => `<li class="omero-baf__tag"><a href="${categoryHref(c.slug)}">${esc(c.name)}</a></li>`).join("") +
    `</ul>`
  );
}

function row(p: Post): string {
  const href = `/${p.slug}/`;
  const t = thumbFor(p);
  return `
<article class="oox-arch__row post-${p.id} post type-post status-publish format-standard hentry">
  <a class="oox-arch__thumb" href="${href}" aria-hidden="true" tabindex="-1">
    <div class="omero-baf__thumb-wrap"><div class="omero-baf__thumb-shape">
      <img loading="lazy" decoding="async" src="${esc(t.src)}" class="wp-post-image" alt="${esc(t.alt)}">
    </div></div>
  </a>
  <div class="oox-arch__body">
    <div class="oox-arch__head">
      ${chipList(p)}
      <div class="omero-baf__meta oox-arch__meta">${esc(fmtDate(p.date))} <span class="omero-baf__sep">by</span> <a href="${AUTHOR_HREF}" rel="author">${esc(postAuthorName(p) || "OOX Limited")}</a></div>
    </div>
    <h2 class="omero-baf__title oox-arch__title"><a href="${href}" rel="bookmark">${esc(p.title)}</a></h2>
    ${p.excerpt ? `<p class="oox-arch__excerpt">${esc(p.excerpt)}</p>` : ""}
    <a class="omero-baf__more oox-arch__more" href="${href}">Continue reading</a>
  </div>
</article>`;
}

/* --------------------------------------------------------------- sidebar --- */

function searchBox(q: string): string {
  return `
<div class="oox-arch__search">
  <label class="screen-reader-text" for="oox-arch-q">Search posts</label>
  <input type="search" id="oox-arch-q" name="q" value="${esc(q)}" placeholder="search" autocomplete="off">
  <button type="submit" class="oox-arch__search-go" aria-label="Search">
    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><line x1="16.5" y1="16.5" x2="21" y2="21"></line></svg>
  </button>
</div>`;
}

function popularTags(terms: CategoryTerm[], active: Set<string>): string {
  if (!terms.length) return "";
  return `
<section class="oox-arch__block">
  <h2 class="oox-arch__block-title">Popular tags</h2>
  <ul class="oox-arch__pills">
    ${terms
      .slice(0, 8)
      .map(
        (t) =>
          `<li><a class="oox-arch__pill${active.has(t.slug) ? " is-active" : ""}" href="${categoryHref(t.slug)}">${esc(t.name)}</a></li>`,
      )
      .join("")}
  </ul>
</section>`;
}

function recentPosts(posts: Post[]): string {
  if (!posts.length) return "";
  return `
<section class="oox-arch__block">
  <h2 class="oox-arch__block-title">Recent Posts</h2>
  <ul class="oox-arch__recent">
    ${posts
      .slice(0, 4)
      .map((p) => {
        const t = thumbFor(p);
        const href = `/${p.slug}/`;
        return `<li class="oox-arch__recent-item">
          <a class="oox-arch__recent-thumb" href="${href}" aria-hidden="true" tabindex="-1"><div class="omero-baf__thumb-wrap"><div class="omero-baf__thumb-shape"><img loading="lazy" decoding="async" src="${esc(t.src)}" class="wp-post-image" alt="${esc(t.alt)}"></div></div></a>
          <div class="oox-arch__recent-body">
            <div class="omero-baf__meta">${esc(fmtDate(p.date))} <span class="omero-baf__sep">by</span> <a href="${AUTHOR_HREF}" rel="author">${esc(postAuthorName(p) || "OOX Limited")}</a></div>
            <h3 class="oox-arch__recent-title"><a href="${href}">${esc(p.title)}</a></h3>
            <a class="omero-baf__more" href="${href}">Continue reading</a>
          </div>
        </li>`;
      })
      .join("")}
  </ul>
</section>`;
}

function categoryChecklist(terms: CategoryTerm[], active: Set<string>): string {
  if (!terms.length) return "";
  return `
<section class="oox-arch__block">
  <h2 class="oox-arch__block-title">Categories</h2>
  <ul class="oox-arch__cats">
    ${terms
      .map(
        (t) => `<li class="oox-arch__cat">
      <label>
        <input type="checkbox" name="cat" value="${esc(t.slug)}"${active.has(t.slug) ? " checked" : ""}>
        <span class="oox-arch__cat-box" aria-hidden="true"></span>
        <span class="oox-arch__cat-name">${esc(t.name)}</span>
        <span class="oox-arch__cat-count">${t.count}</span>
      </label>
    </li>`,
      )
      .join("")}
  </ul>
  <noscript><button type="submit" class="oox-arch__apply">Apply</button></noscript>
</section>`;
}

export interface SidebarOptions {
  q?: string;
  activeCats?: string[];
  withRecent?: boolean;
  withCategories?: boolean;
  /** the URL the filter form submits to */
  action?: string;
}

/**
 * The whole sidebar is one GET form: the search field and the category
 * checkboxes submit together, so a visitor without JavaScript still gets both.
 */
export function sidebarHtml(o: SidebarOptions): string {
  const terms = listCategories();
  const active = new Set(o.activeCats || []);
  return `
<aside class="oox-arch__side">
  <form class="oox-arch__filters" method="get" action="${esc(o.action || "/category/")}" role="search">
    ${searchBox(o.q || "")}
    ${popularTags(terms, active)}
    ${o.withRecent ? recentPosts(getAllPosts()) : ""}
    ${o.withCategories ? categoryChecklist(terms, active) : ""}
  </form>
</aside>`;
}

/* ---------------------------------------------------------------- archive -- */

export interface ArchiveOptions {
  posts: Post[];
  page: number;
  totalPages: number;
  heading: string;
  q?: string;
  activeCats?: string[];
  pageHref: (n: number) => string;
  paginationHtml: (current: number, total: number, href: (n: number) => string) => string;
}

/**
 * Replace the blog shell's card region with the archive: filtered list on the
 * left, filter sidebar on the right. Everything above (hero) and below
 * (the "Insights on…" copy, footer) is left exactly as frozen.
 */
export function renderArchive(shellBody: string, o: ArchiveOptions): string {
  const $ = cheerio.load(shellBody, {}, false);
  const baf = $(".omero-baf").first();
  if (!baf.length) return shellBody;

  const list = o.posts.length
    ? o.posts.map(row).join("")
    : `<p class="oox-arch__empty">No posts found${o.q ? ` for “${esc(o.q)}”` : ""}. <a href="/blog/">Back to all posts</a></p>`;

  baf.replaceWith(`
<div class="oox-arch">
  <div class="oox-arch__main">
    <h1 class="oox-arch__heading">${esc(o.heading)}</h1>
    ${list}
    ${o.paginationHtml(o.page, o.totalPages, o.pageHref)}
  </div>
  ${sidebarHtml({ q: o.q, activeCats: o.activeCats, withRecent: true, withCategories: true })}
</div>`);

  return $.html();
}

/**
 * The blog index gets the lighter half of the same sidebar — search and
 * "Popular tags" only, sitting beside the featured post, exactly as the Figma
 * "Blog" frame shows it. Recent posts and the category checklist belong to the
 * archive frame, not this one.
 *
 * Called from the catch-all route after applyBlogIndex (rather than from
 * blogRender) so this module and blogRender don't import each other.
 */
export function applyBlogSidebar(routePath: string, body: string): string {
  if (!/^\/blog\/(?:page\/\d+\/)?$/.test(routePath)) return body;

  const $ = cheerio.load(body, {}, false);
  const featured = $(".omero-baf__featured").first();
  if (!featured.length || $(".oox-arch__side").length) return body;

  featured.wrap('<div class="oox-blogtop"></div>');
  $(".oox-blogtop").first().append(sidebarHtml({ withRecent: false, withCategories: false }));
  return $.html();
}

/** Posts-per-page, shared with the blog index so paging behaves identically. */
export const ARCHIVE_PAGE_SIZE = BLOG_PAGE_SIZE;
