import type { Metadata } from "next";
import { notFound } from "next/navigation";
import FrozenView from "@/components/FrozenView";
import FrozenBodyClass from "@/components/FrozenBodyClass";
import { hasFrozen, getFrozen, routeKey } from "@/lib/frozen";
import { applyPageEdits, getPagemap } from "@/lib/fieldMap";
import { getPageEdits } from "@/lib/pageEdits";
import { applyChromePatch } from "@/lib/chromePatch";
import { applySingleContent } from "@/lib/singleContent";
import { applyImageAlt } from "@/lib/imageAlt";
import { applyFrozenFixups } from "@/lib/frozenFixups";
import {
  getAllPages, getAllPosts, getServices, getTeam, getPageByPath, getPost,
  getService, getTeamMember, getSiteSettings,
} from "@/lib/content";
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
  for (const p of getAllPosts()) routes.add(`/${p.slug}/`);
  for (const s of getServices()) routes.add(`/service/${s.slug}/`);
  for (const t of getTeam()) routes.add(`/team/${t.slug}/`);
  routes.add("/blog/page/2/");

  return [...routes]
    .filter((r) => hasFrozen(r))
    .map((r) => ({ slug: r === "/" ? [] : r.replace(/^\/|\/$/g, "").split("/") }));
}

export const dynamicParams = false;

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
            authors: [post.author || settings.title],
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
  if (!hasFrozen(path)) notFound();

  const frozen = getFrozen(path)!;
  const key = routeKey(path);
  let body = applyChromePatch(frozen.bodyHtml);
  body = applySingleContent(path, body);
  body = applyFrozenFixups(path, body);
  body = applyImageAlt(body);
  const edits = getPageEdits(key);
  if (Object.keys(edits).length) {
    body = applyPageEdits(body, getPagemap(key), edits);
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
      <FrozenView routePath={path} patchedBody={patched} />
    </>
  );
}
