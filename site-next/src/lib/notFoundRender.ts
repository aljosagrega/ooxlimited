import "server-only";
import * as cheerio from "cheerio";

/**
 * The 404 page, built on a frozen shell so it carries the real header and
 * footer instead of Next's bare default page.
 *
 * Design: "404" in the site's blue-to-purple gradient with the game asset
 * sitting in the middle zero, the heading, one line of copy and a "go back"
 * button — from the OOX Website Figma (frame "404", node 266-608).
 *
 * The button reuses the theme's own Elementor button classes (the same markup
 * the header's "get in touch" uses), so it is the site's button, not a copy of
 * it: any future theme change to buttons follows here automatically.
 *
 * The 3D asset is optional. It renders only when the file exists at
 * ASSET_SRC — drop the PNG exported from Figma there and it appears; without
 * it the numerals simply close up, no broken image.
 */
export const NOT_FOUND_ASSET_SRC = "/404-asset.png";

function block(hasAsset: boolean): string {
  return `
<div class="oox-404">
  <div class="oox-404__numerals" aria-hidden="true">
    <span class="oox-404__digit">4</span>
    <span class="oox-404__zero">
      0${hasAsset ? `<img class="oox-404__asset" src="${NOT_FOUND_ASSET_SRC}" alt="" />` : ""}
    </span>
    <span class="oox-404__digit">4</span>
  </div>
  <h1 class="oox-404__title">Oops! Page is not found</h1>
  <p class="oox-404__text">We&#39;re not being able to find the page you&#39;re looking for</p>
  <div class="oox-404__cta oox-btn elementor-button-default elementor-widget elementor-widget-button">
    <a class="elementor-button elementor-button-link elementor-size-xs" href="/">
      <span class="elementor-button-content-wrapper">
        <span class="elementor-button-text">go back</span>
      </span>
    </a>
  </div>
</div>`;
}

/**
 * Replace a frozen page's content region with the 404 block, keeping the
 * header, the footer and the theme's stylesheet stack exactly as they are.
 * Returns null if the shell has no #content, so the caller can fall back.
 */
export function render404(shellBody: string, hasAsset: boolean): string | null {
  const $ = cheerio.load(shellBody, {}, false);
  const content = $("#content").first();
  if (!content.length) return null;
  // Scope hook: the frozen header is styled for a dark hero (near-white nav and
  // a white logo mark), which is invisible on this page's white ground. The
  // overrides that darken it are keyed off this class so no other page moves.
  $("#page").addClass("oox-404-shell");
  content.html(`<div class="col-fluid"><div id="primary"><main id="main" class="site-main">${block(hasAsset)}</main></div></div>`);
  return $.html();
}
