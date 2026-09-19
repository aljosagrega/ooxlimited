import { test } from "node:test";
import assert from "node:assert/strict";
import { PROBE_EXT } from "@/lib/probePaths";
import pagesJson from "@/data/pages.json";
import postsJson from "@/data/posts.json";
import redirectsJson from "@/data/redirects.json";

// The paths that took the site down on 2026-09-17: each unique one minted a
// permanent on-disk ISR entry at the catch-all.
test("refuses the probe shapes that filled the disk", () => {
  for (const p of [
    "/17950100291.htm",
    "/32370275039.htm",
    "/01.php",
    "/0.php",
    "/wp-login.php",
    "/.env",
    "/backup.sql",
    "/db.sqlite",
    "/site.zip",
    "/config.yml",
    "/docker-compose.yaml",
    "/terraform.tfstate",
    "/error.log",
    "/index.php5",
    "/shell.phtml",
    "/.htaccess",
    "/wp-config.php.bak",
  ]) {
    assert.equal(PROBE_EXT.test(p), true, `expected ${p} to be refused`);
  }
});

// Everything Next actually serves on this site. A false positive here is a live
// asset going 404, which is the failure this deny-list is shaped to avoid.
test("passes the assets the app really serves", () => {
  for (const p of [
    "/sitemap.xml",
    "/sitemap.xsl",
    "/robots.txt",
    "/icon.png",
    "/og-default.png",
    "/apple-touch-icon.png",
    "/_css/wp-content/themes/omero-child/style.css",
    "/_next/static/chunks/main.js",
    "/media/uploads/photo.jpeg",
    "/wp-content/uploads/2024/01/hero.webp",
    "/fonts/inter.woff2",
    "/logo.svg",
    "/video.mp4",
  ]) {
    assert.equal(PROBE_EXT.test(p), false, `expected ${p} to pass through`);
  }
});

// The deny-list rests on this: a real route never carries a file extension, so
// refusing extensioned probes can never shadow one. Asserted against the content
// itself rather than a fixture, so a future page/post/redirect that broke the
// assumption would fail here instead of 404ing in production.
test("no real route carries a file extension", () => {
  const paths = [
    ...(pagesJson as { path: string }[]).map((p) => p.path),
    ...(postsJson as { slug: string }[]).map((p) => `/${p.slug}/`),
    ...(redirectsJson as { from: string }[]).map((r) => r.from),
  ];
  assert.ok(paths.length > 0, "expected content to load");
  for (const p of paths) {
    assert.equal(PROBE_EXT.test(p), false, `route ${p} would be refused as a probe`);
  }
});
