/**
 * Bundle the exact WordPress theme/plugin asset files the frozen site loads into
 * public/, as real committed files, so the deploy is self-contained.
 *
 * What gets copied:
 *   - every /wp-content/* and /wp-includes/* file referenced by a frozen page
 *     (src/data/frozen/*.{html,head.html})
 *   - everything those CSS files pull in via url() / @import (fonts, mask SVGs…)
 *   - the Elementor JS runtime + its widget chunks (webpack loads these by
 *     hashed name at render time — they never appear in the HTML)
 *
 * NOT copied: wp-content/uploads/ (the media library — client host state, large,
 * rsynced by the server; see DEPLOYMENT.md). It stays a symlink locally.
 *
 *   npm run collect-assets
 */
import fs from "fs";
import path from "path";

const ROOT = path.join(import.meta.dirname, "../..");
const WP_DOCROOT = process.env.OOX_WP_DOCROOT || path.join(ROOT, "../site");
const FROZEN = path.join(ROOT, "src/data/frozen");
const PUBLIC = path.join(ROOT, "public");

const isUploads = (p) => p.startsWith("/wp-content/uploads/");
const wpPath = (p) => /^\/(wp-content|wp-includes)\//.test(p);

/** normalise a ref found in HTML/CSS to a site-absolute path, or null */
function norm(ref, baseDir) {
  if (!ref) return null;
  let r = ref.trim().replace(/^['"]|['"]$/g, "").split("#")[0].split("?")[0];
  if (!r || r.startsWith("data:") || /^https?:\/\//.test(r) || r.startsWith("//")) return null;
  if (!r.startsWith("/")) r = path.posix.normalize(path.posix.join(baseDir, r));
  r = r.replace(/^\/_css/, ""); // rewritten-CSS paths mirror the real tree
  return wpPath(r) && !isUploads(r) ? r : null;
}

const want = new Set();
const cssSeen = new Set();

/** add a CSS file and everything it references */
function addCss(sitePath) {
  if (cssSeen.has(sitePath)) return;
  cssSeen.add(sitePath);
  want.add(sitePath);
  const disk = path.join(WP_DOCROOT, sitePath);
  if (!fs.existsSync(disk)) return;
  const css = fs.readFileSync(disk, "utf-8");
  const baseDir = path.posix.dirname(sitePath);
  for (const m of css.matchAll(/url\(\s*([^)]+?)\s*\)/g)) {
    const p = norm(m[1], baseDir);
    if (p) (p.endsWith(".css") ? addCss(p) : want.add(p));
  }
  for (const m of css.matchAll(/@import\s+(?:url\()?\s*(['"][^'"]+['"])/g)) {
    const p = norm(m[1], baseDir);
    if (p) addCss(p);
  }
}

// ---- 1. refs in the frozen pages ------------------------------------------
for (const f of fs.readdirSync(FROZEN)) {
  if (!/\.html$/.test(f)) continue;
  const html = fs.readFileSync(path.join(FROZEN, f), "utf-8");
  for (const m of html.matchAll(/\b(?:href|src|data-src)=(['"])(.*?)\1/gi)) {
    const p = norm(m[2], "/");
    if (!p) continue;
    if (p.endsWith(".css")) addCss(p);
    else want.add(p);
  }
  for (const m of html.matchAll(/\bsrcset=(['"])(.*?)\1/gi)) {
    for (const cand of m[2].split(",")) {
      const p = norm(cand.trim().split(/\s+/)[0], "/");
      if (p) want.add(p);
    }
  }
  for (const m of html.matchAll(/url\(\s*([^)]+?)\s*\)/g)) {
    const p = norm(m[1], "/");
    if (p) (p.endsWith(".css") ? addCss(p) : want.add(p));
  }
}

// ---- 2. Elementor JS runtime + widget chunks + bundled micro-libs --------
// Elementor code-splits per widget and loads chunks (and small libs like
// dialog / share-link / waypoints) by hashed or fixed name via webpack at
// render time, so they never appear in the HTML. Take every minified JS under
// assets/js + assets/lib wholesale rather than guess which a page needs.
for (const sub of ["js", "lib"]) {
  const dir = path.join(WP_DOCROOT, "wp-content/plugins/elementor/assets", sub);
  for (const f of walk(dir)) {
    if (/\.min\.js$/.test(f) && !/\.map$/.test(f)) {
      want.add("/" + path.relative(WP_DOCROOT, f).split(path.sep).join("/"));
    }
  }
}

// ---- 3. copy ------------------------------------------------------------
// wipe the previous mirror so removed refs don't linger
for (const d of ["wp-content", "wp-includes"]) {
  const dir = path.join(PUBLIC, d);
  if (fs.existsSync(dir) && !fs.lstatSync(dir).isSymbolicLink()) {
    fs.rmSync(dir, { recursive: true, force: true });
  }
}

let n = 0, bytes = 0;
const missing = [];
for (const rel of [...want].sort()) {
  const src = path.join(WP_DOCROOT, rel);
  if (!fs.existsSync(src) || fs.statSync(src).isDirectory()) { missing.push(rel); continue; }
  const dest = path.join(PUBLIC, rel);
  fs.mkdirSync(path.dirname(dest), { recursive: true });
  fs.copyFileSync(src, dest);
  n++; bytes += fs.statSync(src).size;
}

// keep the media library resolvable in local dev (gitignored, rsynced in prod)
const uploadsLink = path.join(PUBLIC, "wp-content/uploads");
if (!fs.existsSync(uploadsLink)) {
  fs.symlinkSync(path.join(WP_DOCROOT, "wp-content/uploads"), uploadsLink);
}

fs.writeFileSync(
  path.join(FROZEN, "_assets.json"),
  JSON.stringify({ generatedAt: new Date().toISOString(), count: n, bytes, files: [...want].sort() }, null, 2),
);

console.log(`bundled ${n} files (${(bytes / 1e6).toFixed(1)} MB) → public/wp-content, public/wp-includes`);
if (missing.length) console.log(`\n${missing.length} referenced but not found on disk:\n  ${missing.join("\n  ")}`);

function* walk(dir) {
  if (!fs.existsSync(dir)) return;
  for (const e of fs.readdirSync(dir, { withFileTypes: true })) {
    const p = path.join(dir, e.name);
    if (e.isDirectory()) yield* walk(p);
    else yield p;
  }
}
