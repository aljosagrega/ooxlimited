import "server-only";
import * as cheerio from "cheerio";
import type { LegalDoc } from "./legalContent";

/**
 * The legal pages (/privacy/, /terms-of-service/), built on a frozen shell the
 * same way the 404 is (see notFoundRender.ts): the shell's header, footer and
 * stylesheet stack are kept, its #content region is replaced by the document.
 * Typography lives in frozenOverrides.ts under `.oox-legal`.
 */

/** Any frozen page works as the shell; contact-us is the smallest. */
export const LEGAL_SHELL_KEY = "contact-us";

/**
 * Replace a frozen page's content region with a legal document. Returns null
 * if the shell has no #content, so the caller can fall back.
 */
export function renderLegal(shellBody: string, doc: LegalDoc): string | null {
  const $ = cheerio.load(shellBody, {}, false);
  const content = $("#content").first();
  if (!content.length) return null;
  // White page under a header styled for a dark hero — reuse the 404's header
  // darkening, which is keyed off this class (see frozenOverrides.ts).
  $("#page").addClass("oox-404-shell");
  content.html(`<div class="col-fluid"><div id="primary"><main id="main" class="site-main">
<article class="oox-legal">
  <header class="oox-legal__header">
    <h1 class="oox-legal__title">${doc.title}</h1>
    <p class="oox-legal__updated">Last updated: ${doc.updated}</p>
  </header>
  <div class="oox-legal__body">${doc.html}</div>
</article>
</main></div></div>`);
  return $.html();
}
