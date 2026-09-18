import type { MetadataRoute } from "next";
import {
  getAllPages, getAllPosts, getServices, getTeam,
} from "@/lib/content";

const SITE_URL = process.env.SITE_URL || "https://ooxlimited.com";

// `force-static` (the old setting here) prerenders once at build time using the
// CI runner's checked-in seed data, then never re-runs — it can't see admin
// edits at all, ever, not even after a deploy. Same revalidate window as the
// catch-all page (see its comment) so a deleted/added team member or post
// clears out within 5 minutes instead of staying wrong forever.
export const revalidate = 300;

export default function sitemap(): MetadataRoute.Sitemap {
  const url = (p: string) => `${SITE_URL}${p.startsWith("/") ? p : `/${p}`}`;
  const out: MetadataRoute.Sitemap = [];

  for (const p of getAllPages()) {
    if (p.noindex) continue;
    out.push({ url: url(p.path), changeFrequency: "monthly", priority: p.isFront ? 1 : 0.8 });
  }
  for (const s of getServices()) {
    if (s.noindex) continue;
    out.push({ url: url(`/service/${s.slug}/`), changeFrequency: "monthly", priority: 0.7 });
  }
  for (const t of getTeam()) {
    out.push({ url: url(`/team/${t.slug}/`), changeFrequency: "yearly", priority: 0.5 });
  }
  for (const post of getAllPosts()) {
    if (post.noindex) continue;
    out.push({
      url: url(`/${post.slug}/`),
      lastModified: post.modified || post.date || undefined,
      changeFrequency: "monthly",
      priority: 0.6,
    });
  }
  return out;
}
