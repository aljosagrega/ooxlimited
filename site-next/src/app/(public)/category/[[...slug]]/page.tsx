import type { Metadata } from "next";
import { notFound } from "next/navigation";
import FrozenView from "@/components/FrozenView";
import FrozenBodyClass from "@/components/FrozenBodyClass";
import ArchiveFilters from "@/components/ArchiveFilters";
import { getFrozenByKey } from "@/lib/frozen";
import { applyChromePatch } from "@/lib/chromePatch";
import { renderArchive, ARCHIVE_PAGE_SIZE } from "@/lib/archiveRender";
import { paginationHtml } from "@/lib/blogRender";
import { filterPosts, getCategory, listCategories } from "@/lib/taxonomy";
import { getSiteSettings } from "@/lib/content";

/**
 * Category archive — the "Blog - otvara se kada se kliknu tagovi" design.
 *
 *   /category/                     all posts, filterable
 *   /category/<slug>/              one category
 *   ?cat=a&cat=b                   additional categories (OR), from the sidebar
 *   ?q=text                        search
 *   ?page=2                        paging
 *
 * Dynamic because it reads searchParams — deliberately kept to this one route
 * so the rest of the public site stays statically prerendered.
 */
const SHELL_KEY = "blog";

type Props = {
  params: Promise<{ slug?: string[] }>;
  searchParams: Promise<Record<string, string | string[] | undefined>>;
};

const asArray = (v: string | string[] | undefined): string[] =>
  v === undefined ? [] : Array.isArray(v) ? v : [v];

function slugOf(slug?: string[]): string | null {
  return slug && slug.length ? slug[0] : null;
}

export async function generateMetadata({ params, searchParams }: Props): Promise<Metadata> {
  const base = slugOf((await params).slug);
  const term = base ? getCategory(base) : null;
  const q = String((await searchParams).q ?? "").trim();
  const title = q
    ? `Search: ${q}`
    : term
      ? `${term.name} — ${getSiteSettings().title}`
      : `Blog — ${getSiteSettings().title}`;
  return {
    title,
    // Filtered and searched views are near-duplicates of the archive; only the
    // clean category pages should be indexed.
    robots: q || asArray((await searchParams).cat).length ? { index: false, follow: true } : undefined,
  };
}

export default async function CategoryArchive({ params, searchParams }: Props) {
  const sp = await searchParams;
  const base = slugOf((await params).slug);
  const term = base ? getCategory(base) : null;
  if (base && !term) notFound();

  const cats = [...new Set([...(term ? [term.slug] : []), ...asArray(sp.cat)])].filter((s) =>
    listCategories().some((c) => c.slug === s),
  );
  const q = String(sp.q ?? "").trim();
  const page = Math.max(1, parseInt(String(sp.page ?? "1"), 10) || 1);

  const all = filterPosts({ cats, q });
  const totalPages = Math.max(1, Math.ceil(all.length / ARCHIVE_PAGE_SIZE));
  const posts = all.slice((page - 1) * ARCHIVE_PAGE_SIZE, page * ARCHIVE_PAGE_SIZE);

  const frozen = getFrozenByKey(SHELL_KEY, "/blog/");
  if (!frozen) throw new Error("frozen blog shell unavailable for the category archive");

  // keep the current filters when paging
  const pageHref = (n: number) => {
    const p = new URLSearchParams();
    for (const c of asArray(sp.cat)) p.append("cat", c);
    if (q) p.set("q", q);
    if (n > 1) p.set("page", String(n));
    const qs = p.toString();
    return `/category/${term ? `${term.slug}/` : ""}${qs ? `?${qs}` : ""}`;
  };

  const heading = q ? `Results for “${q}”` : term ? term.name : "All posts";

  const body = renderArchive(applyChromePatch(frozen.bodyHtml), {
    posts,
    page,
    totalPages,
    heading,
    q,
    activeCats: cats,
    pageHref,
    paginationHtml,
  });

  return (
    <>
      <FrozenBodyClass className={frozen.bodyClass} lang={frozen.lang} />
      <FrozenView frozenKey={SHELL_KEY} patchedBody={body} />
      <ArchiveFilters />
    </>
  );
}
