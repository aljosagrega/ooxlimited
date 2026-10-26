import { NextRequest, NextResponse } from "next/server";
import { isProbePath } from "@/lib/probePaths";
import { redirectFor } from "@/lib/redirectTable";

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

  const hit = redirectFor(pathname);
  if (hit) {
    const dest = hit.to.startsWith("http") ? hit.to : new URL(hit.to, req.url);
    return NextResponse.redirect(dest, hit.type);
  }

  // Scanner probes are answered here rather than at the catch-all. `[[...slug]]` runs
  // with `revalidate` and `dynamicParams: true` (see the note on that export), so every
  // unique path it 404s is written to the on-disk ISR cache and never evicted. That is
  // unbounded in the size of the probe space, not the route space: 296k entries, 96% of
  // them numbered `.htm` probes, filled the disk and took the site down on 2026-09-17.
  // Terminating in middleware means no cache entry is minted.
  if (isProbePath(pathname)) {
    return new NextResponse(null, { status: 404 });
  }

  return NextResponse.next();
}

export const config = {
  matcher: [
    "/robots.txt",
    "/sitemap.xsl",
    // Any extensioned path, so a probe is refused above before it can reach the
    // catch-all and mint an ISR entry. Subsumes the legacy-sitemap `.xml` rule.
    // `_css/` is excluded only to keep middleware off the hottest static prefix.
    "/((?!_next/|wp-content/|wp-includes/|api/|admin/|_css/).*\\.[a-z0-9]+$)",
    "/((?!_next/|wp-content/|wp-includes/|api/|admin/|.*\\.[a-z0-9]+$).*)",
  ],
};
