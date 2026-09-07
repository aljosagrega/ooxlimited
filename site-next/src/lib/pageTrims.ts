import "server-only";
import * as cheerio from "cheerio";
import { getFrozenByKey } from "./frozen";

/**
 * Blocks removed from frozen pages at render time.
 *
 * The frozen HTML is a capture of the WordPress page, so a block the client no
 * longer wants cannot simply be deleted from it — `npm run freeze` would bring
 * it straight back. Listing it here removes it on every render instead, and
 * deleting the entry restores it.
 *
 * This is for whole blocks the client has asked to drop. Editing the *text* of a
 * block is not this: that is done in the admin, through the page editor.
 */
interface Trim {
  /** route path this applies to */
  path: string;
  /** selector for the element to remove */
  selector: string;
  /** why it is gone, and anything the removal cost */
  reason: string;
}

const TRIMS: Trim[] = [
  {
    path: "/contact-us/",
    selector: "section.oox-contact-seo",
    reason:
      'Client: "this text box below, Let\'s discuss..., I just put it there because the ' +
      'page didn\'t have enough text, but I essentially don\'t need it and it\'s too much ' +
      "on page\". Note the trade-off: as its class name says, this block was the contact " +
      "page's search-engine copy — removing it leaves that page thin on text.",
  },
];

export function applyPageTrims(routePath: string, bodyHtml: string): string {
  const trims = TRIMS.filter((t) => t.path === routePath);
  if (!trims.length) return bodyHtml;

  const $ = cheerio.load(bodyHtml, {}, false);
  let changed = false;
  for (const t of trims) {
    const el = $(t.selector);
    if (el.length) {
      el.remove();
      changed = true;
    }
  }
  return changed ? $.html() : bodyHtml;
}

/**
 * The pagemap ids that live inside a trimmed block, worked out from the frozen
 * HTML rather than hard-coded — so this keeps up if the block's contents change.
 *
 * Without this the admin lies about a trimmed page: the SEO report keeps
 * counting words the visitor never sees, and the page editor keeps offering
 * fields that edit nothing.
 */
export function trimmedPagemapIds(routeKey: string, routePath: string): Set<string> {
  const out = new Set<string>();
  const trims = TRIMS.filter((t) => t.path === routePath);
  if (!trims.length) return out;

  const frozen = getFrozenByKey(routeKey, routePath);
  if (!frozen) return out;

  const $ = cheerio.load(frozen.bodyHtml, {}, false);
  for (const t of trims) {
    $(t.selector).each((_, el) => {
      const scope = $(el);
      for (const attr of ["data-oox-e", "data-oox-e-alt", "data-oox-e-href"]) {
        const self = scope.attr(attr);
        if (self) out.add(self);
        scope.find(`[${attr}]`).each((__, n) => {
          const v = $(n).attr(attr);
          if (v) out.add(v);
        });
      }
    });
  }
  return out;
}
