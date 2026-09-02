// Regenerates the static brand assets that live outside the frozen WordPress
// tree: the app icons (browser tab / home screen) and the default Open Graph
// share image. Source of truth is the OOX logomark already shipped in
// public/wp-content/uploads/. Run: node scripts/gen-brand-assets.mjs
import sharp from "sharp";
import { readFileSync } from "node:fs";

const LOGO_PNG = "public/wp-content/uploads/2026/03/ooxlogo.png"; // 154x154, full-colour mark
const OUT = { icon: "src/app/icon.png", apple: "src/app/apple-icon.png", og: "public/og-default.png" };

// Brand gradient from the site's .text-gradient-oox rule (#5B8CFF -> #7B5CFF).
const GRAD_TOP = "#5B8CFF";
const GRAD_BOT = "#7B5CFF";

async function icon(size, file, pad) {
  const logo = await sharp(LOGO_PNG)
    .resize(size - pad * 2, size - pad * 2, { fit: "contain", background: { r: 0, g: 0, b: 0, alpha: 0 } })
    .toBuffer();
  await sharp({
    create: { width: size, height: size, channels: 4, background: { r: 255, g: 255, b: 255, alpha: 0 } },
  })
    .composite([{ input: logo, gravity: "centre" }])
    .png()
    .toFile(file);
  console.log("wrote", file, `${size}x${size}`);
}

async function og() {
  const W = 1200;
  const H = 630;
  const logo = await sharp(LOGO_PNG).resize(240, 240, { fit: "contain" }).toBuffer();
  const bg = Buffer.from(
    `<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}">
      <defs><linearGradient id="g" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0" stop-color="${GRAD_TOP}"/><stop offset="1" stop-color="${GRAD_BOT}"/>
      </linearGradient></defs>
      <rect width="${W}" height="${H}" fill="url(#g)"/>
      <text x="80" y="430" font-family="Poppins, Arial, sans-serif" font-size="76" font-weight="700" fill="#FFFFFF">OOX Limited</text>
      <text x="82" y="492" font-family="Poppins, Arial, sans-serif" font-size="34" font-weight="400" fill="#EEF3FA">Game &amp; App Development Studio</text>
    </svg>`,
  );
  await sharp(bg)
    .composite([{ input: logo, top: 80, left: 80 }])
    .png()
    .toFile(OUT.og);
  console.log("wrote", OUT.og, `${W}x${H}`);
}

await icon(256, OUT.icon, 24);
await icon(180, OUT.apple, 16);
await og();
