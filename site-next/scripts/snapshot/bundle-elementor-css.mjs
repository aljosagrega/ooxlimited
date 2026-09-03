/**
 * One-off / re-runnable: bundle every Elementor generated stylesheet
 * (/wp-content/uploads/elementor/css/*) that a frozen <head> links into
 * public/_css/, and repoint the <link href> at the /_css/ copy.
 *
 * `freeze.ts` now does this on every run; this catches frozen heads captured
 * before that change without a full re-scrape of the WordPress copy.
 *
 *   node scripts/snapshot/bundle-elementor-css.mjs
 */
import fs from "fs";
import path from "path";

const ROOT = path.join(import.meta.dirname, "../..");
const WP_DOCROOT = process.env.OOX_WP_DOCROOT || path.join(ROOT, "../site");
const FROZEN = path.join(ROOT, "src/data/frozen");
const CSS_DEST = path.join(ROOT, "public/_css");

const PREFIX = "/wp-content/uploads/elementor/css/";
const re = new RegExp(`(["'])${PREFIX.replace(/[/]/g, "\\/")}([a-z0-9._-]+\\.css)([^"']*)\\1`, "gi");

let copied = 0;
const missing = new Set();
const touched = [];

for (const f of fs.readdirSync(FROZEN)) {
  if (!f.endsWith(".head.html")) continue;
  const p = path.join(FROZEN, f);
  let html = fs.readFileSync(p, "utf-8");
  let changed = false;

  html = html.replace(re, (m, q, file, query) => {
    const src = path.join(WP_DOCROOT, PREFIX, file);
    if (!fs.existsSync(src)) {
      missing.add(file);
      return m;
    }
    const dest = path.join(CSS_DEST, PREFIX.replace(/^\//, ""), file);
    fs.mkdirSync(path.dirname(dest), { recursive: true });
    const css = fs
      .readFileSync(src, "utf-8")
      .replace(/https?:\/\/localhost:8080/g, "")
      .replace(/https?:\/\/(?:www\.)?ooxlimited\.com/g, "");
    fs.writeFileSync(dest, css);
    copied++;
    changed = true;
    return `${q}/_css${PREFIX}${file}${query}${q}`;
  });

  if (changed) {
    fs.writeFileSync(p, html);
    touched.push(f);
  }
}

console.log(`copied ${copied} stylesheet refs → public/_css${PREFIX}`);
console.log(`rewrote ${touched.length} frozen head files`);
if (missing.size) console.log(`\nreferenced but not on disk in ${WP_DOCROOT}:\n  ${[...missing].join("\n  ")}`);
