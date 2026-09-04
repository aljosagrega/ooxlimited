import type { Metadata } from "next";
import FrozenView from "@/components/FrozenView";
import FrozenBodyClass from "@/components/FrozenBodyClass";
import { notFound } from "next/navigation";
import { hasFrozen, hasFrozenKey, getFrozenByKey, routeKey } from "@/lib/frozen";
import { applyPageEdits, getPagemap } from "@/lib/fieldMap";
import { getPageEdits } from "@/lib/pageEdits";
import { applyChromePatch } from "@/lib/chromePatch";
import { applySingleContent } from "@/lib/singleContent";
import { POST_TEMPLATE_KEY, renderTemplatedPost, applyBlogIndex, blogPageCount } from "@/lib/blogRender";
import { applyImageAlt } from "@/lib/imageAlt";
import { applyFrozenFixups } from "@/lib/frozenFixups";
import {
  getAllPages, getAllPosts, getServices, getTeam, getPageByPath, getPost,
  getService, getTeamMember, getSiteSettings, postAuthorName, isPostLive,
} from "@/lib/content";
import type { Post } from "@/lib/types";
import { pageJsonLd, renderJsonLd } from "@/lib/jsonLd";
import type { SeoFields } from "@/lib/types";

type Props = { params: Promise<{ slug?: string[] }> };

const SITE_URL = process.env.SITE_URL || "https://ooxlimited.com";

function pathFromSlug(slug?: string[]): string {
  if (!slug || slug.length === 0) return "/";
  return "/" + slug.join("/") + "/";
}

export function generateStaticParams() {
  const routes = new Set<string>(["/"]);
  for (const p of getAllPages()) routes.add(p.path);
  for (const s of getServices()) routes.add(`/service/${s.slug}/`);
  for (const t of getTeam()) routes.add(`/team/${t.slug}/`);

  // Every non-draft post gets a route — from its own frozen snapshot, or the
  // shared `_post-template` shell for posts created in the admin CMS.
  const postRoutes = new Set(getAllPosts().map((p) => `/${p.slug}/`));
  for (const r of postRoutes) routes.add(r);

  // One blog archive page per BLOG_PAGE_SIZE posts; page ≥3 borrows the page-2
  // shell (see CatchAll).
  const blogRoutes = new Set<string>();
  for (let i = 2; i <= blogPageCount(); i++) blogRoutes.add(`/blog/page/${i}/`);
  for (const r of blogRoutes) routes.add(r);

  return [...routes]
    .filter((r) => hasFrozen(r) || postRoutes.has(r) || blogRoutes.has(r))
    .map((r) => ({ slug: r === "/" ? [] : r.replace(/^\/|\/$/g, "").split("/") }));
}

// NOTE: `dynamicParams` is deliberately left at its default (true).
//
// Setting it to false looks right — generateStaticParams knows every valid route
// — but it is incompatible with the admin. Every admin write calls
// `revalidatePath("/", "layout")`, which drops the prerendered pages for this
// whole segment; with dynamicParams:false there is no way to render them again,
// so Next raises `NoFallbackError` and *every* page 404s until the next deploy.
// Leaving it true lets an invalidated page re-render on demand. The cost is that
// unknown paths now reach the component, so `resolveRoute` below owns the 404s.
const BLOG_PAGE_SHELL_KEY = "blog__page__2";

type Resolved = {
  /** frozen file key to render from */
  renderKey: string;
  /** set when the route is a CMS post with no frozen snapshot of its own */
  cmsPost: Post | null;
};

/**
 * Decide what (if anything) a request path renders. Returns null for a 404.
 * This is the sole gate on the public site, so it has to reject draft posts and
 * out-of-range archive pages explicitly — a drafted post's frozen snapshot is
 * still sitting on disk from the WordPress migration.
 */
function resolveRoute(path: string): Resolved | null {
  const key = routeKey(path);
  const slug = path.replace(/^\/|\/$/g, "");

  // /blog/page/N/ — valid only while there are posts to fill it. Page 3+ has no
  // snapshot of its own and borrows the page-2 archive shell.
  const blogPage = path.match(/^\/blog\/page\/(\d+)\/$/);
  if (blogPage) {
    const n = Number(blogPage[1]);
    if (n < 2 || n > blogPageCount()) return null;
    return { renderKey: hasFrozenKey(key) ? key : BLOG_PAGE_SHELL_KEY, cmsPost: null };
  }

  // A bare /slug/ may be a post. getPost() already excludes drafts.
  if (slug && !slug.includes("/")) {
    const post = getPost(slug);
    if (post) {
      // Own snapshot when it has one, else the shared CMS post shell.
      return hasFrozenKey(key)
        ? { renderKey: key, cmsPost: null }
        : { renderKey: POST_TEMPLATE_KEY, cmsPost: post };
    }
    // No live post owns this slug. If a draft does, the route is gone — even
    // though its frozen file is still on disk.
    if (getAllPosts(true).some((p) => p.slug === slug && !isPostLive(p))) return null;
  }

  return hasFrozenKey(key) ? { renderKey: key, cmsPost: null } : null;
}

function seoFor(path: string): { seo: SeoFields; title: string } | null {
  const page = getPageByPath(path);
  if (page) return { seo: page, title: page.title };
  const postSlug = path.replace(/^\/|\/$/g, "");
  const post = getPost(postSlug);
  if (post) return { seo: post, title: post.title };
  const svc = path.match(/^\/service\/([^/]+)\/$/);
  if (svc) {
    const s = getService(svc[1]);
    if (s) return { seo: s, title: s.title };
  }
  const tm = path.match(/^\/team\/([^/]+)\/$/);
  if (tm) {
    const t = getTeamMember(tm[1]);
    if (t) {
      const role = t.position ? `${t.position} at OOX Limited` : "part of the OOX Limited team";
      return {
        seo: {
          metaDescription:
            `${t.name} is ${role} — a game and app development studio building mobile ` +
            `games, apps and playable prototypes from concept to launch.`,
        },
        title: `${t.name} — ${t.position || "OOX Limited"}`,
      };
    }
  }
  return null;
}

/** Open Graph object type for a route: article for posts, profile for team. */
function ogTypeFor(path: string): "article" | "profile" | "website" {
  const slug = path.replace(/\//g, "");
  if (path.match(/^\/[^/]+\/$/) && getPost(slug)) return "article";
  if (path.match(/^\/team\/[^/]+\/$/)) return "profile";
  return "website";
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const path = pathFromSlug((await params).slug);
  const info = seoFor(path);
  const settings = getSiteSettings();
  const fallbackTitle = info?.title
    ? `${info.title}${path === "/" ? "" : " – " + settings.title}`
    : settings.title;

  // Per-page overrides set in /admin/pages win over the migrated record values.
  const pe = getPageEdits(routeKey(path));
  const seo: SeoFields = {
    ...(info?.seo ?? {}),
    ...(pe.__seoTitle ? { metaTitle: pe.__seoTitle } : {}),
    ...(pe.__seoDesc ? { metaDescription: pe.__seoDesc } : {}),
    ...(pe.__seoNoindex === "1" ? { noindex: true } : {}),
  };
  const canonical = seo.canonicalUrl
    ? seo.canonicalUrl.startsWith("http")
      ? seo.canonicalUrl
      : `${SITE_URL}${seo.canonicalUrl}`
    : `${SITE_URL}${path === "/" ? "/" : path}`;

  const post = getPost(path.replace(/\//g, ""));
  const ogType = ogTypeFor(path);
  const abs = (u: string) => (u.startsWith("http") ? u : `${SITE_URL}${u}`);
  const ogImage = seo.ogImage
    ? abs(seo.ogImage)
    : post?.featuredImage?.url
      ? abs(post.featuredImage.url)
      : `${SITE_URL}/og-default.png`;

  return {
    title: seo.metaTitle || fallbackTitle,
    description: seo.metaDescription || settings.tagline,
    alternates: { canonical },
    robots: seo.noindex ? { index: false, follow: true } : undefined,
    openGraph: {
      title: seo.ogTitle || seo.metaTitle || fallbackTitle,
      description: seo.ogDesc || seo.metaDescription || settings.tagline,
      url: canonical,
      siteName: settings.title,
      locale: "en_US",
      type: ogType,
      images: [ogImage],
      ...(ogType === "article" && post
        ? {
            publishedTime: post.date || undefined,
            modifiedTime: post.modified || post.date || undefined,
            authors: [postAuthorName(post) || settings.title],
          }
        : {}),
    },
    twitter: {
      card: "summary_large_image",
      title: seo.ogTitle || seo.metaTitle || fallbackTitle,
      description: seo.ogDesc || seo.metaDescription || settings.tagline,
      images: [ogImage],
    },
  };
}

export default async function CatchAll({ params }: Props) {
  const path = pathFromSlug((await params).slug);
  const key = routeKey(path);

  const resolved = resolveRoute(path);
  if (!resolved) notFound();
  const { renderKey, cmsPost } = resolved;

  const frozen = getFrozenByKey(renderKey, path);
  if (!frozen) {
    // resolveRoute only returns a key it has just seen on disk, so a miss here
    // is a transient filesystem failure (release swap / prune under a running
    // server). Throw rather than notFound(): a thrown render keeps serving the
    // last good page and retries, where a notFound() would be cached as a 404.
    throw new Error(`frozen shell "${renderKey}" unavailable for ${path}`);
  }

  let body = applyChromePatch(frozen.bodyHtml);
  if (cmsPost) {
    body = renderTemplatedPost(body, cmsPost);
  } else {
    body = applySingleContent(path, body);
    body = applyBlogIndex(path, body);
  }
  body = applyFrozenFixups(path, body);
  body = applyImageAlt(body);
  if (!cmsPost) {
    const edits = getPageEdits(key);
    if (Object.keys(edits).length) {
      body = applyPageEdits(body, getPagemap(key), edits);
    }
  }
  const patched = body === frozen.bodyHtml ? undefined : body;

  return (
    <>
      <FrozenBodyClass className={frozen.bodyClass} lang={frozen.lang} />
      {pageJsonLd(path).map((node, i) => (
        <script
          key={i}
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: renderJsonLd(node) }}
        />
      ))}
      <FrozenView frozenKey={renderKey} patchedBody={patched} />
    </>
  );
}
