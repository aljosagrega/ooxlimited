import { test } from "node:test";
import assert from "node:assert/strict";
import fs from "fs";
import path from "path";

// Read as text: the route imports `server-only` content, which throws outside
// Next. A prerendered sitemap carries the CI runner's seed data, not the live
// CMS content, until it regenerates.
test("the sitemap renders per request, never from a build-time prerender", () => {
  const src = fs.readFileSync(path.join(import.meta.dirname, "sitemap.ts"), "utf-8");
  assert.match(src, /^export const dynamic = "force-dynamic";$/m);
  assert.doesNotMatch(src, /^export const (revalidate|dynamic = "force-static")/m);
});
