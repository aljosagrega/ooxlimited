import "server-only";
import { getAllPosts } from "./content";
import { stripHtmlText } from "./seoScore";
import type { Post, TermRef } from "./types";

/**
 * Categories are the site's only taxonomy — every post's `tags` array is empty,
 * and the chips the theme renders on blog cards (and calls "tags") are
 * categories. The archive, the sidebar's "Popular tags" pills and its
 * "Categories" checklist are therefore three views of this one list.
 */
export interface CategoryTerm extends TermRef {
  count: number;
}

export function listCategories(): CategoryTerm[] {
  const byslug = new Map<string, CategoryTerm>();
  for (const p of getAllPosts()) {
    for (const c of p.categories || []) {
      if (!c?.slug) continue;
      const hit = byslug.get(c.slug);
      if (hit) hit.count++;
      else byslug.set(c.slug, { id: c.id, name: c.name, slug: c.slug, count: 1 });
    }
  }
  return [...byslug.values()].sort((a, b) => b.count - a.count || a.name.localeCompare(b.name));
}

export function getCategory(slug: string): CategoryTerm | null {
  return listCategories().find((c) => c.slug === slug) ?? null;
}

/** Every term the post carries, for rendering its chips. */
export function postCategories(p: Post): TermRef[] {
  return (p.categories || []).filter((c): c is TermRef => !!c?.slug && !!c.name);
}

/**
 * Archive filter. Categories are OR-ed (selecting two shows posts in either —
 * the design's checkboxes are a multi-select, not a narrowing AND), then the
 * search term is AND-ed on top of that.
 */
export function filterPosts({ cats, q }: { cats?: string[]; q?: string }): Post[] {
  const wanted = new Set((cats || []).filter(Boolean));
  const needle = (q || "").trim().toLowerCase();

  return getAllPosts().filter((p) => {
    if (wanted.size) {
      const has = (p.categories || []).some((c) => c?.slug && wanted.has(c.slug));
      if (!has) return false;
    }
    if (needle) {
      const hay = [
        p.title || "",
        stripHtmlText(p.excerpt || ""),
        stripHtmlText(p.bodyHtml || ""),
        ...(p.categories || []).map((c) => c?.name || ""),
      ]
        .join(" ")
        .toLowerCase();
      // every whitespace-separated term must appear somewhere
      if (!needle.split(/\s+/).every((t) => hay.includes(t))) return false;
    }
    return true;
  });
}

export const categoryHref = (slug: string): string => `/category/${slug}/`;
