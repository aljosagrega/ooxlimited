import { test } from "node:test";
import assert from "node:assert/strict";
import { buildRedirectTable, normalize } from "./redirectTable";

test("a post's old slugs and the explicit table both redirect", () => {
  const table = buildRedirectTable(
    [
      { from: "/elementor-1", to: "keeper", type: 301 },
      { from: "/old-temp", to: "https://example.com/x", type: 302 },
    ],
    [{ slug: "keeper-2", oldSlugs: ["dropped"] }, { slug: "plain" }],
  );
  assert.deepEqual(table.get(normalize("/elementor-1")), { to: "/keeper/", type: 308 });
  assert.deepEqual(table.get(normalize("old-temp")), { to: "https://example.com/x", type: 307 });
  assert.deepEqual(table.get(normalize("/dropped")), { to: "/keeper-2/", type: 308 });
  assert.equal(table.get(normalize("/plain/")), undefined);
});
