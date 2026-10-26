import { test } from "node:test";
import assert from "node:assert/strict";
import fs from "fs";
import os from "os";
import path from "path";

// A prerendered sitemap carries the CI runner's seed data, not the live CMS
// content, until it regenerates.
test("the sitemap renders per request, never from a build-time prerender", () => {
  const src = fs.readFileSync(path.join(import.meta.dirname, "sitemap.ts"), "utf-8");
  assert.match(src, /^export const dynamic = "force-dynamic";$/m);
  assert.doesNotMatch(src, /^export const (revalidate|dynamic = "force-static")/m);
});

// Live content can hold a post whose slug the bundled redirect table claims
// (the seed's oldSlugs): that URL redirects, so the sitemap must not list it.
test("the sitemap leaves out paths the middleware redirects", async () => {
  const dir = fs.mkdtempSync(path.join(os.tmpdir(), "sitemap-"));
  fs.mkdirSync(path.join(dir, "src/data"), { recursive: true });
  const write = (name: string, rows: unknown[]) =>
    fs.writeFileSync(path.join(dir, "src/data", `${name}.json`), JSON.stringify(rows));
  write("pages", []);
  write("services", []);
  write("team", []);
  write("posts", [
    { slug: "rapid-prototyping-game-development", date: "2026-06-11T00:00:00Z" },
    { slug: "rapid-prototyping-game-development-2", date: "2026-08-26T00:00:00Z" },
  ]);
  const cwd = process.cwd();
  process.chdir(dir);
  try {
    const { default: sitemap } = await import("./sitemap");
    const paths = sitemap().map((e) => new URL(e.url).pathname);
    assert.ok(paths.includes("/rapid-prototyping-game-development-2/"));
    assert.ok(!paths.includes("/rapid-prototyping-game-development/"));
  } finally {
    process.chdir(cwd);
    fs.rmSync(dir, { recursive: true, force: true });
  }
});
