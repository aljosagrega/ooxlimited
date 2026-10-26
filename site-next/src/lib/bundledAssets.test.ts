import { test } from "node:test";
import assert from "node:assert/strict";
import fs from "fs";
import path from "path";

const ELEMENTOR = path.join(import.meta.dirname, "../../public/wp-content/plugins/elementor/assets");

// The Elementor runtime injects these stylesheets by name, so no frozen page
// references them; a missing one is a 404 in the browser console.
test("every conditional stylesheet the Elementor runtime requests is bundled", () => {
  const runtime = fs.readFileSync(path.join(ELEMENTOR, "js/frontend.min.js"), "utf-8");
  const names = [...new Set([...runtime.matchAll(/css\/conditionals\/([\w-]+)\$\{/g)].map((m) => m[1]))];
  assert.ok(names.includes("dialog"), "runtime no longer names its conditionals as expected");
  for (const name of names) {
    assert.ok(fs.existsSync(path.join(ELEMENTOR, `css/conditionals/${name}.min.css`)), `${name}.min.css`);
  }
});
