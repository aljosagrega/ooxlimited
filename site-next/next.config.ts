import type { NextConfig } from "next";
import path from "path";

const nextConfig: NextConfig = {
  output: "standalone",
  outputFileTracingRoot: path.resolve(__dirname),
  poweredByHeader: false,
  // Gzip the proxied HTML on the Node side — the frozen Elementor pages are
  // 100-400 KB of markup uncompressed. nginx doesn't gzip proxied responses
  // unless `gzip_proxied` is set (see DEPLOYMENT.md §5); this is the safety net
  // so a page is never shipped uncompressed regardless of the proxy config.
  compress: true,
  // The frozen legacy scripts declare top-level `let`/`const` globals and are
  // not idempotent — StrictMode's double-invoke of effects re-runs them and
  // throws "already declared", breaking carousel / accordion init.
  reactStrictMode: false,
  // Every WordPress URL carries a trailing slash — keep it.
  trailingSlash: true,
  // `src/data/frozen/*.html` is read at runtime via fs; make sure the tracer
  // bundles the whole data dir into the standalone output.
  outputFileTracingIncludes: {
    "/**": ["./src/data/**/*"],
  },
  async headers() {
    return [
      {
        source: "/:path*",
        headers: [
          { key: "X-Content-Type-Options", value: "nosniff" },
          { key: "Referrer-Policy", value: "strict-origin-when-cross-origin" },
          { key: "X-Frame-Options", value: "SAMEORIGIN" },
          // The site is HTTPS-only in production; tell browsers to stop trying
          // plain HTTP. One year, subdomains included. (Add `preload` and submit
          // to hstspreload.org only once every subdomain is known HTTPS-only.)
          { key: "Strict-Transport-Security", value: "max-age=31536000; includeSubDomains" },
          { key: "X-DNS-Prefetch-Control", value: "on" },
          // No part of the site uses these APIs; deny them site-wide.
          { key: "Permissions-Policy", value: "camera=(), microphone=(), geolocation=(), browsing-topics=()" },
        ],
      },
      {
        // Frozen theme/plugin assets + content images — hashed by ?ver=, safe to cache hard.
        source: "/wp-content/:path*",
        headers: [{ key: "Cache-Control", value: "public, max-age=31536000, immutable" }],
      },
      {
        source: "/wp-includes/:path*",
        headers: [{ key: "Cache-Control", value: "public, max-age=31536000, immutable" }],
      },
    ];
  },
};

export default nextConfig;
