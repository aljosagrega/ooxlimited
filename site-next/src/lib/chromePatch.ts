import "server-only";
import * as cheerio from "cheerio";
import { getMenus, getSiteSettings } from "./content";
import type { MenuItem } from "./types";

/**
 * The header + footer markup is baked into every frozen page. This patches the
 * editable parts of it — nav menus, footer social links, contact email — from
 * the JSON store, so `/admin` edits take effect without re-freezing. Layout,
 * classes and styling are never touched.
 *
 * Runs on every route (see the catch-all page). If nothing in the store differs
 * from what's frozen, the output is byte-identical.
 */
export function applyChromePatch(bodyHtml: string): string {
  const settings = getSiteSettings();
  const menus = getMenus();

  // Cheap bail-out: only load cheerio if there's something to sync.
  if (!settings.socialLinks.length && !menus.main.length && !menus.footer.length) {
    return bodyHtml;
  }

  const $ = cheerio.load(bodyHtml, {}, false);

  /* ---- footer social icons ------------------------------------------------ */
  // Elementor renders each as a.elementor-social-icon-<slug> in document order.
  const socialAnchors = $("a.elementor-social-icon").toArray();
  if (socialAnchors.length && settings.socialLinks.length) {
    settings.socialLinks.forEach((link, i) => {
      const a = socialAnchors[i];
      if (!a || !link.href) return;
      $(a).attr("href", link.href);
      $(a).find(".elementor-screen-only").text(link.label);
    });
    // Any leftover frozen icons beyond the configured list — leave as-is
    // (removing DOM nodes risks layout shift in the grid).
  }

  /* ---- nav menus -------------------------------------------------------- */
  patchMenu($, "nav.main-navigation ul.menu, .hfe-nav-menu__layout-horizontal .menu, ul#menu-1-bffea36", menus.main);
  patchMenu($, ".elementor-widget-footer .menu, footer ul.menu, .site-footer ul.menu", menus.footer);

  // NOTE: footer contact email is left as frozen — the studio's footer address
  // (admin@ooxcit.com) differs from the form recipient and isn't an admin field.

  return $.html();
}

/** Rewrite an existing frozen <ul> menu's top-level items in place from a tree.
 *  Only touches items that line up 1:1 by position; never adds/removes DOM to
 *  avoid disturbing Elementor's submenu markup + JS hooks. */
function patchMenu($: cheerio.CheerioAPI, selector: string, tree: MenuItem[]) {
  if (!tree.length) return;
  const ul = $(selector).first();
  if (!ul.length) return;

  const topItems = ul.children("li").toArray();
  const flatTop = tree;

  topItems.forEach((li, i) => {
    const item = flatTop[i];
    if (!item) return;
    const anchor = $(li).children("a").first();
    if (!anchor.length) return;
    if (item.url) anchor.attr("href", item.url);
    const titleSpan = anchor.find(".menu-title").first();
    if (titleSpan.length) titleSpan.text(item.label);
    else if (!anchor.children().length) anchor.text(item.label);

    // one level of submenu
    const sub = $(li).children("ul").first();
    if (sub.length && item.children.length) {
      sub.children("li").toArray().forEach((subLi, j) => {
        const child = item.children[j];
        if (!child) return;
        const subA = $(subLi).children("a").first();
        if (!subA.length) return;
        if (child.url) subA.attr("href", child.url);
        const st = subA.find(".menu-title").first();
        if (st.length) st.text(child.label);
        else if (!subA.children().length) subA.text(child.label);
      });
    }
  });
}
