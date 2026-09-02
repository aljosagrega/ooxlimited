import { NextRequest, NextResponse } from "next/server";
import redirectsJson from "@/data/redirects.json";
import postsJson from "@/data/posts.json";

type Redirect = { from: string; to: string; type: number };

const normalize = (p: string) => {
  if (!p) return "/";
  let out = p.startsWith("/") ? p : `/${p}`;
  out = out.replace(/\/{2,}/g, "/");
  if (!out.endsWith("/") && !/\.[a-z0-9]+$/i.test(out)) out += "/";
  return out;
};

// Explicit redirect table (Yoast Premium export + slug changes), keyed by
// normalized source path.
const TABLE = new Map<string, { to: string; type: number }>();
for (const r of redirectsJson as Redirect[]) {
  TABLE.set(normalize(r.from), {
    to: r.to.startsWith("http") ? r.to : normalize(r.to),
    type: r.type === 302 || r.type === 307 ? 307 : 308,
  });
}

// Every post's historical slugs -> its current canonical URL.
for (const p of postsJson as { slug: string; oldSlugs?: string[] }[]) {
  for (const old of p.oldSlugs ?? []) {
    TABLE.set(normalize(old), { to: `/${p.slug}/`, type: 308 });
  }
}

const SITE_URL = process.env.SITE_URL || "https://ooxlimited.com";
const ROBOTS_TXT = `User-agent: *
Allow: /
Disallow: /admin/
Disallow: /api/

Sitemap: ${SITE_URL}/sitemap.xml
`;

export function middleware(req: NextRequest) {
  const { pathname } = req.nextUrl;

  // robots.txt — served here because the optional catch-all's static 404
  // pre-empts a public/ file or a metadata route in the production build.
  if (pathname === "/robots.txt") {
    return new NextResponse(ROBOTS_TXT, {
      headers: { "content-type": "text/plain; charset=utf-8", "cache-control": "public, max-age=3600" },
    });
  }

  // wp-admin / wp-login -> the new admin
  if (/^\/wp-(admin|login\.php)/.test(pathname)) {
    return NextResponse.redirect(new URL("/admin/login/", req.url), 308);
  }

  // Legacy Yoast sitemaps -> the Next sitemap
  if (/^\/(sitemap_index\.xml|[a-z-]+-sitemap\.xml|sitemap\.xsl)$/.test(pathname)) {
    return NextResponse.redirect(new URL("/sitemap.xml", req.url), 308);
  }

  const hit = TABLE.get(normalize(pathname));
  if (hit) {
    const dest = hit.to.startsWith("http") ? hit.to : new URL(hit.to, req.url);
    return NextResponse.redirect(dest, hit.type);
  }

  return NextResponse.next();
}

export const config = {
  matcher: [
    "/robots.txt",
    "/((?!_next/|wp-content/|wp-includes/|api/|admin/).*\\.xml$)",
    "/((?!_next/|wp-content/|wp-includes/|api/|admin/|.*\\.[a-z0-9]+$).*)",
  ],
};
