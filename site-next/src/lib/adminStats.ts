import "server-only";
import { readArray } from "./jsonStore";
import { computeSeoScore, seoBucket, wordCount } from "./seoScore";
import type { Page, Post, Service } from "./types";

const SITE = "ooxlimited.com";

function stripTags(s: string) {
  return s.replace(/<[^>]+>/g, " ").replace(/\s+/g, " ").trim();
}

type Kind = "page" | "post" | "service";

function seoInput(title: string, desc: string, slug: string, body: string, hasImage: boolean) {
  return {
    effectiveTitle: title || "",
    effectiveDescription: desc || "",
    slug: slug || "",
    bodyWordCount: wordCount(body || ""),
    hasImage,
  };
}

function pageInput(p: Page) {
  return seoInput(p.metaTitle || p.title, p.metaDescription || "", p.slug, "", !!p.ogImage);
}
function postInput(p: Post) {
  return seoInput(p.metaTitle || p.title, p.metaDescription || stripTags(p.excerpt || ""), p.slug, p.bodyHtml, !!p.featuredImage?.url);
}
function serviceInput(s: Service) {
  return seoInput(s.metaTitle || s.title, s.metaDescription || stripTags(s.excerpt || ""), s.slug, s.excerpt, !!s.thumbnail?.url);
}

/* --------------------------------------------------------------- dashboard -- */

export interface DashboardStats {
  totals: { pages: number; posts: number; services: number; team: number };
  postsByMonth: { label: string; value: number; sublabel: string }[];
  seoBuckets: { good: number; ok: number; poor: number };
  worstSeo: { id: number; kind: Kind; title: string; score: string; url: string }[];
  postsByCategory: { label: string; value: number }[];
  submissions: { label: string; value: number }[];
  recentSubmissions: number;
}

export function getDashboardStats(): DashboardStats {
  const pages = readArray<Page>("pages");
  const posts = readArray<Post>("posts");
  const services = readArray<Service>("services");
  const team = readArray<{ id: number }>("team");
  const subs = readArray<{ kind: string; handled?: boolean; createdAt: string }>("submissions");

  // posts per month, last 12
  const months: { key: string; label: string; count: number }[] = [];
  const now = new Date();
  for (let i = 11; i >= 0; i--) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
    months.push({
      key: `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}`,
      label: d.toLocaleString("en", { month: "short" }),
      count: 0,
    });
  }
  for (const p of posts) {
    const key = (p.date || "").slice(0, 7);
    const m = months.find((x) => x.key === key);
    if (m) m.count++;
  }

  const scored = [
    ...pages.map((p) => ({ id: p.id, kind: "page" as const, title: p.title, url: p.path, ...computeSeoScore(pageInput(p)) })),
    ...posts.map((p) => ({ id: p.id, kind: "post" as const, title: p.title, url: `/${p.slug}/`, ...computeSeoScore(postInput(p)) })),
    ...services.map((s) => ({ id: s.id, kind: "service" as const, title: s.title, url: `/service/${s.slug}/`, ...computeSeoScore(serviceInput(s)) })),
  ];
  const buckets = { good: 0, ok: 0, poor: 0 };
  for (const s of scored) buckets[seoBucket(s.good, s.total)]++;
  const worstSeo = [...scored]
    .sort((a, b) => a.good / a.total - b.good / b.total || b.issues - a.issues)
    .slice(0, 6)
    .map((s) => ({ id: s.id, kind: s.kind, title: s.title, score: `${s.good}/${s.total}`, url: s.url }));

  const catCount = new Map<string, number>();
  for (const p of posts) for (const c of p.categories ?? []) catCount.set(c.name, (catCount.get(c.name) ?? 0) + 1);
  const postsByCategory = [...catCount.entries()]
    .map(([label, value]) => ({ label, value }))
    .sort((a, b) => b.value - a.value)
    .slice(0, 8);

  const subKinds = new Map<string, number>();
  for (const s of subs) subKinds.set(s.kind, (subKinds.get(s.kind) ?? 0) + 1);
  const thirtyDaysAgo = Date.now() - 30 * 864e5;

  return {
    totals: { pages: pages.length, posts: posts.length, services: services.length, team: team.length },
    postsByMonth: months.map((m) => ({ label: m.label, value: m.count, sublabel: m.key })),
    seoBuckets: buckets,
    worstSeo,
    postsByCategory,
    submissions: [
      { label: "Contact", value: subKinds.get("contact") ?? 0 },
      { label: "Newsletter", value: subKinds.get("newsletter") ?? 0 },
    ],
    recentSubmissions: subs.filter((s) => +new Date(s.createdAt) > thirtyDaysAgo).length,
  };
}

/* --------------------------------------------------------------- /admin/seo -- */

export interface SeoRow {
  id: number;
  kind: Kind;
  title: string;
  url: string;
  good: number;
  total: number;
  issues: number;
  noindex: boolean;
  words: number;
  hasDescription: boolean;
  hasTitle: boolean;
}

export function getSeoOverview(): SeoRow[] {
  const pages = readArray<Page>("pages");
  const posts = readArray<Post>("posts");
  const services = readArray<Service>("services");

  const row = (id: number, kind: Kind, title: string, url: string, noindex: boolean, inp: ReturnType<typeof seoInput>): SeoRow => {
    const sc = computeSeoScore(inp);
    return {
      id, kind, title, url,
      good: sc.good, total: sc.total, issues: sc.issues, noindex,
      words: inp.bodyWordCount,
      hasDescription: inp.effectiveDescription.length > 0,
      hasTitle: inp.effectiveTitle.length > 0,
    };
  };

  const rows: SeoRow[] = [
    ...pages.map((p) => row(p.id, "page", p.title, p.path, !!p.noindex, pageInput(p))),
    ...posts.map((p) => row(p.id, "post", p.title, `/${p.slug}/`, !!p.noindex, postInput(p))),
    ...services.map((s) => row(s.id, "service", s.title, `/service/${s.slug}/`, !!s.noindex, serviceInput(s))),
  ];
  return rows.sort((a, b) => a.good / a.total - b.good / b.total || b.issues - a.issues);
}

export const SEO_SITE_HOST = SITE;
