import type { Metadata } from "next";
import { notFound } from "next/navigation";
import FrozenView from "@/components/FrozenView";
import FrozenBodyClass from "@/components/FrozenBodyClass";
import { hasFrozen, getFrozen, routeKey } from "@/lib/frozen";
import { applyPageEdits, getPagemap } from "@/lib/fieldMap";
import { applyChromePatch } from "@/lib/chromePatch";
import { applySingleContent } from "@/lib/singleContent";
import {
  getAllPages, getAllPosts, getServices, getTeam, getPageByPath, getPost,
  getService, getTeamMember, getSiteSettings,
} from "@/lib/content";
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
    if (t) return { seo: {}, title: `${t.name} — ${t.position || "OOX Limited"}` };
  }
  return null;
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const path = pathFromSlug((await params).slug);
  const info = seoFor(path);
  const settings = getSiteSettings();
  const fallbackTitle = info?.title
    ? `${info.title}${path === "/" ? "" : " – " + settings.title}`
    : settings.title;

  const seo = info?.seo ?? {};
  const canonical = seo.canonicalUrl
    ? seo.canonicalUrl.startsWith("http")
      ? seo.canonicalUrl
      : `${SITE_URL}${seo.canonicalUrl}`
    : `${SITE_URL}${path === "/" ? "/" : path}`;

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
      type: path.match(/^\/[^/]+\/$/) && getPost(path.replace(/\//g, "")) ? "article" : "website",
      images: seo.ogImage ? [seo.ogImage.startsWith("http") ? seo.ogImage : `${SITE_URL}${seo.ogImage}`] : undefined,
    },
  };
}

export default async function CatchAll({ params }: Props) {
  const path = pathFromSlug((await params).slug);
  if (!hasFrozen(path)) notFound();

  const frozen = getFrozen(path)!;
  const page = getPageByPath(path);
  let body = applyChromePatch(frozen.bodyHtml);
  body = applySingleContent(path, body);
  if (page?.edits && Object.keys(page.edits).length) {
    body = applyPageEdits(body, getPagemap(routeKey(path)), page.edits);
  }
  const patched = body === frozen.bodyHtml ? undefined : body;

  return (
    <>
      <FrozenBodyClass className={frozen.bodyClass} lang={frozen.lang} />
      <FrozenView routePath={path} patchedBody={patched} />
    </>
  );
}
