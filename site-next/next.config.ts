import type { NextConfig } from "next";
import path from "path";

const nextConfig: NextConfig = {
  output: "standalone",
  outputFileTracingRoot: path.resolve(__dirname),
  poweredByHeader: false,
  compress: false,
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
