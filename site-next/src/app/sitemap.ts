import type { MetadataRoute } from "next";
import {
  getAllPages, getAllPosts, getServices, getTeam,
} from "@/lib/content";

const SITE_URL = process.env.SITE_URL || "https://ooxlimited.com";

// Rendered on every request, off the server's live content files. A prerender
// — `force-static`, or `revalidate` — is built on the CI runner from the repo's
// seed data, and serves those paths until it first regenerates: after a deploy
// it listed members deleted in the admin, whose pages 404. The render is a few
// small JSON reads.
export const dynamic = "force-dynamic";

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
  // Legal pages are dedicated routes, not pages.json records.
  for (const p of ["/privacy/", "/terms-of-service/"]) {
    out.push({ url: url(p), changeFrequency: "yearly", priority: 0.3 });
  }
  return out;
}
