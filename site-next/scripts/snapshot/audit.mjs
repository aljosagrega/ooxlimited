/**
 * Fidelity audit: capture WP and Next at identical scroll offsets, tile them
 * side by side per viewport band, so every gap / margin / effect can be compared.
 *
 *   node scripts/snapshot/audit.mjs /about-us/ --w 1280
 *
 * Output: scratchpad/audit/<route>__<w>__band<NN>.png   (WP left | Next right)
 */
import { chromium } from "playwright";
import sharp from "sharp";
import fs from "fs";
import path from "path";

const OUT = path.join(process.cwd(), "scratchpad/audit");
fs.mkdirSync(OUT, { recursive: true });

const args = process.argv.slice(2);
let width = 1280;
const routes = [];
for (let i = 0; i < args.length; i++) {
  if (args[i] === "--w") width = Number(args[++i]);
  else routes.push(args[i]);
}
if (!routes.length) routes.push("/");

const WP = process.env.WP_ORIGIN || "http://localhost:8080";
const NEXT = process.env.NEXT_ORIGIN || "http://localhost:3000";
const VH = 900;
const key = (r) => (r === "/" ? "home" : r.replace(/^\/|\/$/g, "").replace(/\//g, "__"));

async function grab(browser, origin, route) {
  const ctx = await browser.newContext({ viewport: { width, height: VH }, deviceScaleFactor: 1 });
  const page = await ctx.newPage();
  await page.goto(origin + route, { waitUntil: "networkidle", timeout: 45000 }).catch(() => {});
  await page.waitForTimeout(2500);
  // stepped scroll to trigger reveals, end at top
  const total = await page.evaluate(async () => {
    const h = document.body.scrollHeight;
    for (let y = 0; y < h; y += 260) { window.scrollTo(0, y); await new Promise(r => setTimeout(r, 200)); }
    window.scrollTo(0, h); await new Promise(r => setTimeout(r, 500));
    window.scrollTo(0, 0);
    await new Promise(r => setTimeout(r, 700));
    return document.body.scrollHeight;
  });
  const bands = [];
  const n = Math.ceil(total / VH);
  for (let i = 0; i < n; i++) {
    await page.evaluate((y) => window.scrollTo(0, y), i * VH);
    await page.waitForTimeout(500);
    bands.push(await page.screenshot());
  }
  await ctx.close();
  return bands;
}

const browser = await chromium.launch();
for (const route of routes) {
  const [wpBands, nextBands] = await Promise.all([
    grab(browser, WP, route),
    grab(browser, NEXT, route),
  ]);
  const n = Math.max(wpBands.length, nextBands.length);
  const half = Math.round(width * 0.46);
  for (let i = 0; i < n; i++) {
    const parts = [];
    if (wpBands[i]) parts.push({ input: await sharp(wpBands[i]).resize(half).toBuffer(), top: 24, left: 0 });
    if (nextBands[i]) parts.push({ input: await sharp(nextBands[i]).resize(half).toBuffer(), top: 24, left: half + 8 });
    const bh = Math.round((VH / width) * half) + 24;
    const lbl = Buffer.from(`<svg width="${half * 2 + 8}" height="24"><rect width="100%" height="24" fill="#111"/><text x="8" y="17" font-family="monospace" font-size="13" fill="#a78bfa">WP</text><text x="${half + 16}" y="17" font-family="monospace" font-size="13" fill="#5eead4">NEXT — ${route} @${width} band ${i}</text></svg>`);
    const file = path.join(OUT, `${key(route)}__${width}__band${String(i).padStart(2, "0")}.png`);
    await sharp({ create: { width: half * 2 + 8, height: bh, channels: 4, background: "#000" } })
      .composite([{ input: lbl, top: 0, left: 0 }, ...parts]).png().toFile(file);
    console.log(file);
  }
}
await browser.close();
