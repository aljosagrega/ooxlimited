/**
 * Pixel-diff helper: screenshot the same route on the live WordPress copy
 * (localhost:8080) and the Next app (localhost:3000) at several widths.
 *
 *   node scripts/snapshot/shots.mjs [route...] [--w 1280] [--full]
 *
 * Output: scratchpad/shots/<route>__<width>__{wp,next}.png
 */
import { chromium } from "playwright";
import fs from "fs";
import path from "path";

const OUT = path.join(process.cwd(), "scratchpad/shots");
fs.mkdirSync(OUT, { recursive: true });

const args = process.argv.slice(2);
const widths = [];
const routes = [];
let full = false;
for (let i = 0; i < args.length; i++) {
  if (args[i] === "--w") widths.push(Number(args[++i]));
  else if (args[i] === "--full") full = true;
  else routes.push(args[i]);
}
if (!widths.length) widths.push(1280, 390);
if (!routes.length) routes.push("/");

const key = (r) => (r === "/" ? "home" : r.replace(/^\/|\/$/g, "").replace(/\//g, "__"));

const browser = await chromium.launch();
for (const route of routes) {
  for (const w of widths) {
    for (const [label, origin] of [
      ["wp", process.env.WP_ORIGIN || "http://localhost:8080"],
      ["next", process.env.NEXT_ORIGIN || "http://localhost:3000"],
    ]) {
      const ctx = await browser.newContext({ viewport: { width: w, height: 900 }, deviceScaleFactor: 1 });
      const page = await ctx.newPage();
      try {
        await page.goto(origin + route, { waitUntil: "networkidle", timeout: 45000 });
      } catch {
        await page.waitForTimeout(2000);
      }
      await page.waitForTimeout(2500);
      // Stepped scroll so GSAP ScrollTrigger / lenis / IntersectionObserver
      // reveals fire the way they would for a real visitor.
      await page.evaluate(async () => {
        const h = document.body.scrollHeight;
        for (let y = 0; y < h; y += 500) {
          window.scrollTo(0, y);
          await new Promise((r) => setTimeout(r, 120));
        }
        window.scrollTo(0, h);
        await new Promise((r) => setTimeout(r, 500));
        window.scrollTo(0, 0);
        await new Promise((r) => setTimeout(r, 400));
      });
      await page.waitForTimeout(800);
      const file = path.join(OUT, `${key(route)}__${w}__${label}.png`);
      await page.screenshot({ path: file, fullPage: full });
      console.log(file);
      await ctx.close();
    }
  }
}
await browser.close();
