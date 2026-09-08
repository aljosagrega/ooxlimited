/**
 * CSS layered on top of the frozen WordPress stylesheet stack.
 *
 * WHY THIS EXISTS. The public design is frozen Elementor output (see PLAN.md);
 * `npm run freeze` overwrites `src/data/frozen/` wholesale, so a fix edited into
 * a frozen file — or into the theme CSS under `public/_css/` — is lost on the
 * next capture. Anything we deliberately change about the frozen design belongs
 * here instead: it is rendered by <FrozenView> as the LAST <style> on the page,
 * so it wins ties against the theme without `!important` escalation, and it
 * survives a re-freeze.
 *
 * RULES
 * - Every block says what it fixes and, where relevant, who asked for it.
 * - Scope every selector to the thing it fixes. This file loads on every public
 *   page; an unscoped rule silently changes pages nobody reviewed.
 * - This file may change how the site LOOKS (unlike frozenFixups.ts, which is
 *   markup-only and must stay visually neutral).
 */
export const FROZEN_OVERRIDES_CSS = `
/* --------------------------------------------------------------------------
 * Blog article: keep authored content inside its column.
 *
 * The article column is 850px and the theme only ever styled the elements the
 * migrated posts happened to use (p / h2 / ul / img / blockquote). Anything else
 * an author writes in the CMS runs straight out of the column: a code block
 * measured 1197px inside an 804px column, and a 7-column table overflowed at
 * every width at or below 1024px, on mobile by more than twice the column.
 * Reported by the client as "there is a bleed on blogs".
 * ------------------------------------------------------------------------ */
.oox-blog-article table {
  display: block;          /* so overflow-x can apply to a table element */
  width: 100%;
  max-width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  border-collapse: collapse;
  margin: 36px 0;
  font-size: 16px;
}
.oox-blog-article th,
.oox-blog-article td {
  border: 1px solid #e6e6ef;
  padding: 10px 14px;
  text-align: left;
  vertical-align: top;
}
.oox-blog-article th {
  background: #f7f5ff;
  font-family: "Poppins", sans-serif;
  font-weight: 700;
}
.oox-blog-article pre {
  max-width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  background: #f7f5ff;
  border-radius: 14px;
  padding: 18px 20px;
  margin: 36px 0;
  font-size: 15px;
  line-height: 1.6;
}
.oox-blog-article pre code { white-space: pre; }
/* Inline code and long unbroken URLs must not push the column open either. */
.oox-blog-article :not(pre) > code {
  background: #f2effe;
  border-radius: 6px;
  padding: 2px 6px;
  font-size: 0.92em;
  overflow-wrap: anywhere;
}
.oox-blog-article a { overflow-wrap: anywhere; }
.oox-blog-article iframe,
.oox-blog-article video,
.oox-blog-article embed,
.oox-blog-article object { max-width: 100%; }

/* --------------------------------------------------------------------------
 * Blog article: keep the left sidebar (contents + recent posts) in view while
 * reading. Client: "as I scroll the post, the tabs on the left stay there when
 * I come to the end of them".
 *
 * Two things had to change for this to work at all:
 *  1. .site.hfeed carries "overflow: hidden", which makes it a scroll container
 *     and silently disables position:sticky for everything inside it. It is
 *     swapped for "overflow-x: clip" — clip does NOT create a scroll container,
 *     so sticky works while sideways scroll stays blocked. (visible + clip is
 *     the one legal mixed overflow pair; do not put "hidden" back here.)
 *  2. The aside is a stretched flex child (measured 9393px tall against 1677px
 *     of content), so it must be align-self:flex-start before sticky can bite.
 *
 * Deliberately no max-height / overflow here: an inner scrollbar would be a new
 * piece of UI in a design that is meant to stay as-is. The sidebar content
 * (902px) fits a normal desktop viewport; on a shorter one it simply pins and
 * the tail stays above the fold, which is the behaviour that was asked for.
 * ------------------------------------------------------------------------ */
/* Specificity note: the theme sets overflow-x:hidden on .site AND overflow-y:
 * hidden on div#page — the same element. An id beats a class, so a .site.hfeed
 * rule loses on the Y axis, and a clip-x paired with a hidden-y computes back to
 * hidden (making it a scroll container again, sticky dead). Match the id so both
 * axes are ours. */
div#page.site.hfeed {
  overflow-x: clip;
  overflow-y: visible;
}
/* <body> computes to overflow-y:auto here, which makes IT the scrollport for
 * everything inside while the viewport (html) does the actual scrolling — so
 * body.scrollTop stays 0 forever and no descendant sticky ever engages. Hand
 * scrolling back to the viewport; overflow-x:clip keeps sideways bleed blocked.
 * Selector carries a [class] to outrank whatever sets the bare element. */
html body[class] {
  overflow-x: clip;
  overflow-y: visible;
}
@media (min-width: 1025px) {
  aside.oox-single-sidebar {
    position: sticky;
    top: 24px;
    align-self: flex-start;
  }
}

/* --------------------------------------------------------------------------
 * 404 page (see notFoundRender.ts). Matches the "404" frame in the OOX Website
 * Figma: gradient numerals with the game asset in the middle zero, heading,
 * one line of copy, and the theme's own button.
 *
 * Colours are the two the site already uses for this gradient (#5B8CFF ->
 * #7B5CFF, both present in the theme CSS); type is Poppins, as everywhere else.
 * The numerals scale with the viewport so the page holds together from phone to
 * desktop without a media-query ladder.
 * ------------------------------------------------------------------------ */
.oox-404 {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: clamp(48px, 9vw, 120px) 24px clamp(64px, 10vw, 140px);
}
.oox-404__numerals {
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: "Poppins", sans-serif;
  font-weight: 800;
  font-size: clamp(120px, 22vw, 300px);
  line-height: 1;
  letter-spacing: -0.02em;
  background: linear-gradient(180deg, #5B8CFF 0%, #7B5CFF 100%);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}
/* The middle zero holds the asset, which overhangs it on both sides. */
.oox-404__zero {
  position: relative;
  display: inline-block;
}
.oox-404__asset {
  position: absolute;
  left: 50%;
  top: 52%;
  transform: translate(-50%, -50%);
  width: 118%;
  max-width: none;
  height: auto;
  pointer-events: none;
}
.oox-404__title {
  font-family: "Poppins", sans-serif;
  font-weight: 800;
  color: #111111;
  font-size: clamp(28px, 4.2vw, 52px);
  line-height: 1.15;
  letter-spacing: -0.03em;
  margin: clamp(16px, 2vw, 28px) 0 0;
}
.oox-404__text {
  font-family: "Plus Jakarta Sans", sans-serif;
  font-size: clamp(15px, 1.4vw, 18px);
  line-height: 1.7;
  color: #1f1f1f;
  margin: 14px 0 clamp(28px, 3.4vw, 44px);
  max-width: 46ch;
}

/* 404: the frozen header assumes a dark hero behind it — #EEF3FA nav text and a
 * white logo mark. This page is white, so both vanish. Darken them for this
 * page only (the shell carries .oox-404-shell, added by render404). */
.oox-404-shell header .primary-navigation a,
.oox-404-shell header .primary-navigation a .menu-title {
  color: #0F1118;
}
.oox-404-shell .hfe-site-logo-img {
  filter: brightness(0);
}

/* The "go back" button. The header's "get in touch" gets its pill shape and
 * offset shadow from Elementor CSS keyed to that one widget's element id, which
 * cannot be reused here without duplicating the id — so the same values are
 * restated: measured from the live button (14px radius, 6px/7px hard shadow,
 * Poppins 600 20px, #7B5CFF on #EEF3FA, the theme's own easing). */
.oox-404__cta .elementor-button {
  display: inline-block;
  background-color: #7B5CFF;
  color: #EEF3FA;
  font-family: "Poppins", sans-serif;
  font-size: 20px;
  font-weight: 600;
  text-transform: none;
  border-radius: 14px;
  padding: 14px 34px;
  box-shadow: rgba(0, 0, 0, 0.25) 6px 7px 0px 0px;
  transition: box-shadow 0.4s cubic-bezier(0.25, 1, 0.5, 1);
}
.oox-404__cta .elementor-button:hover,
.oox-404__cta .elementor-button:focus-visible {
  box-shadow: rgba(0, 0, 0, 0.25) 2px 3px 0px 0px;
}
.oox-404__cta .elementor-button:focus-visible {
  outline: 2px solid #7B5CFF;
  outline-offset: 4px;
}

/* --------------------------------------------------------------------------
 * Blog pagination. Client: "it's missing a 'see more' button, or an arrow, or
 * any indicator that would show more blog".
 *
 * The control was already there — bare theme links reading "1 2 Next" that read
 * as body text, below the fold. This is the pill treatment from the Figma blog
 * frame: current page in the brand purple, the rest on white, all carrying the
 * same hard offset shadow the site's buttons use, arrows instead of the word
 * "Next". Targets are 44px so they are comfortably tappable.
 * ------------------------------------------------------------------------ */
.pagination .nav-links {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  flex-wrap: wrap;
}
.pagination .page-numbers {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 44px;
  height: 44px;
  padding: 0 12px;
  border-radius: 14px;
  background: #FFFFFF;
  color: #0F1118;
  font-family: "Poppins", sans-serif;
  font-size: 16px;
  font-weight: 600;
  line-height: 1;
  text-decoration: none;
  border: 0;
  box-shadow: rgba(0, 0, 0, 0.25) 3px 4px 0 0;
  transition: box-shadow 0.4s cubic-bezier(0.25, 1, 0.5, 1), background-color 200ms ease-out, color 200ms ease-out;
}
.pagination .page-numbers.current {
  background: #7B5CFF;
  color: #EEF3FA;
}
/* The theme's own :hover turns the text white and adds a coloured border —
 * white on this near-white pill is unreadable, and the border is not in the
 * design. Both are overridden here; !important because the theme rule is keyed
 * to the widget id and outranks a class selector. */
.pagination a.page-numbers:hover,
.pagination a.page-numbers:focus-visible {
  background: #EEF3FA;
  color: #7B5CFF !important;
  border: 0 !important;
  box-shadow: rgba(0, 0, 0, 0.25) 1px 2px 0 0;
}
.pagination .page-numbers.current,
.pagination .page-numbers.current:hover {
  border: 0 !important;
}
.pagination a.page-numbers:focus-visible {
  outline: 2px solid #7B5CFF;
  outline-offset: 3px;
}
.pagination .page-numbers.dots {
  box-shadow: none;
  background: none;
  min-width: 0;
  padding: 0 2px;
}
@media (prefers-reduced-motion: reduce) {
  .pagination .page-numbers { transition: none; }
}

/* The theme's chip row is a single non-wrapping flex line, fine for the two
 * categories the demo content had and not for the five a real post carries.
 * Cards cap the list at three (blogRender.ts); this lets what is left wrap
 * instead of running out of the card. */
ul.omero-baf__tags {
  flex-wrap: wrap;
}
/* The chip's padding sat on the <li>, so only the text run inside it was a
 * link: on a 112px-wide chip the outer 40px did nothing and showed no pointer.
 * The anchor fills the chip now, and a transparent ::after pad brings the tap
 * area to 44px without changing how the chip looks. The 18px gap between chips
 * leaves 8px clear between neighbouring hit areas. */
/* Selectors carry the <ul> as well: React hoists the frozen stylesheet links
 * into <head>, and the theme's own li.omero-baf__tag rule ends up winning the
 * tie on document order, so a bare match here would lose. */
ul.omero-baf__tags li.omero-baf__tag {
  position: relative;
  padding: 0;
}
ul.omero-baf__tags li.omero-baf__tag a {
  display: flex;
  align-items: center;
  padding: 10px 20px;
  border-radius: 14px;
  text-decoration: none;
}
ul.omero-baf__tags li.omero-baf__tag a::after {
  content: "";
  position: absolute;
  inset: -5px;
}
ul.omero-baf__tags li.omero-baf__tag a:focus-visible {
  outline: 2px solid #0F1118;
  outline-offset: 3px;
}

/* --------------------------------------------------------------------------
 * Category archive + blog sidebar (archiveRender.ts) — the two "Blog" frames in
 * the Nove stranice na sajtu Figma.
 *
 * Everything here is built from tokens already in the theme: #7B5CFF brand
 * purple, #EEF3FA panel, #0F1118 ink, #FF8A5B for the active/checked state the
 * annotation on the design asks for ("neka postane narandzast"), Poppins for
 * headings and Plus Jakarta Sans for body, 14px radii, and the same hard offset
 * shadow the buttons use.
 * ------------------------------------------------------------------------ */
.oox-arch,
.oox-blogtop {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 300px;
  gap: 48px;
  align-items: start;
}
.oox-arch__heading {
  font-family: "Poppins", sans-serif;
  font-weight: 800;
  font-size: clamp(26px, 3vw, 38px);
  letter-spacing: -0.03em;
  color: #0F1118;
  margin: 0 0 28px;
}
/* --- one result row: image left, copy right --- */
.oox-arch__row {
  display: grid;
  grid-template-columns: 300px minmax(0, 1fr);
  gap: 32px;
  align-items: start;
  padding: 0 0 44px;
}
.oox-arch__thumb { display: block; }
/* The theme's fill rule is scoped to .omero-baf__card, so the borrowed thumb
 * markup keeps the image's intrinsic width here (measured 200px inside a 300px
 * shape) and leaves a gap beside it. Restate it for the archive's own rows. */
.oox-arch__row .omero-baf__thumb-shape img,
.oox-arch__recent-item .omero-baf__thumb-shape img {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.oox-arch__recent-thumb .omero-baf__thumb-shape { width: 76px; }
/* Chips on their own line, the date/author line always under them — a row that
 * flowed both onto one line read as a single run-on strip of metadata. */
.oox-arch__head {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 14px;
}
/* The theme's chip row does not wrap; here it must, or a post with several
 * categories runs out of the column and under the sidebar. */
.oox-arch__tags { margin: 0; padding: 0; flex-wrap: wrap; }
.oox-arch__title {
  font-family: "Poppins", sans-serif;
  font-weight: 800;
  font-size: clamp(22px, 2.4vw, 32px);
  line-height: 1.15;
  letter-spacing: -0.03em;
  margin: 0 0 12px;
}
.oox-arch__title a { color: #0F1118; text-decoration: none; }
.oox-arch__title a:hover { color: #7B5CFF; }
.oox-arch__title a:focus-visible,
.oox-arch__more:focus-visible,
.oox-arch__recent-title a:focus-visible {
  outline: 2px solid #7B5CFF;
  outline-offset: 3px;
}
.oox-arch__excerpt {
  font-family: "Plus Jakarta Sans", sans-serif;
  font-size: 16px;
  line-height: 1.7;
  color: #1f1f1f;
  margin: 0 0 16px;
  max-width: 60ch;
}
.oox-arch__empty {
  font-family: "Plus Jakarta Sans", sans-serif;
  font-size: 17px;
  color: #1f1f1f;
  padding: 32px 0 56px;
}
.oox-arch__empty a { color: #7B5CFF; font-weight: 600; }

/* --- sidebar --- */
.oox-arch__side { position: relative; }
.oox-arch__block { margin-bottom: 36px; }
.oox-arch__block-title {
  font-family: "Poppins", sans-serif;
  font-weight: 800;
  font-size: 20px;
  letter-spacing: -0.02em;
  color: #0F1118;
  margin: 0 0 16px;
}
.oox-arch__search { position: relative; margin-bottom: 32px; }
.oox-arch__search input {
  width: 100%;
  height: 44px;
  padding: 0 44px 0 16px;
  border: 1px solid #DDE6F5;
  border-radius: 14px;
  background: #EEF3FA;
  font-family: "Plus Jakarta Sans", sans-serif;
  font-size: 15px;
  color: #0F1118;
}
.oox-arch__search input::placeholder { color: #606060; }
.oox-arch__search input:focus-visible {
  outline: 2px solid #7B5CFF;
  outline-offset: 2px;
}
.oox-arch__search-go {
  position: absolute;
  right: 6px;
  top: 6px;
  width: 32px;
  height: 32px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 0;
  border-radius: 10px;
  background: none;
  color: #0F1118;
  cursor: pointer;
}
.oox-arch__search-go:hover { color: #7B5CFF; }

.oox-arch__pills {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin: 0;
  padding: 0;
  list-style: none;
}
.oox-arch__pill {
  display: inline-block;
  padding: 7px 14px;
  border-radius: 999px;
  background: #7B5CFF;
  color: #FFFFFF;
  font-family: "Plus Jakarta Sans", sans-serif;
  font-size: 13px;
  font-weight: 600;
  line-height: 1.2;
  text-decoration: none;
  transition: background-color 200ms ease-out;
}
.oox-arch__pill:hover { background: #6B46C1; color: #FFFFFF; }
.oox-arch__pill:focus-visible {
  outline: 2px solid #0F1118;
  outline-offset: 3px;
}
/* White on this orange is 2.3:1 — unreadable. The ink the rest of the page uses
 * reads 8.1:1 on it and keeps the orange the design asks for. */
.oox-arch__pill.is-active,
.oox-arch__pill.is-active:hover { background: #FF8A5B; color: #0F1118; }

.oox-arch__recent { list-style: none; margin: 0; padding: 0; }
.oox-arch__recent-item {
  display: grid;
  grid-template-columns: 76px minmax(0, 1fr);
  gap: 14px;
  align-items: start;
  padding: 14px 0;
  border-bottom: 1px solid #DDE6F5;
}
.oox-arch__recent-item:last-child { border-bottom: 0; }
/* The sidebar column is ~200px wide next to the thumb; the theme's meta size
 * wraps "by <author>" onto its own line there. */
.oox-arch__recent-item .omero-baf__meta { font-size: 12px; line-height: 1.4; }
.oox-arch__recent-title {
  font-family: "Poppins", sans-serif;
  font-weight: 800;
  font-size: 15px;
  line-height: 1.25;
  letter-spacing: -0.02em;
  margin: 4px 0 6px;
}
.oox-arch__recent-title a { color: #0F1118; text-decoration: none; }
.oox-arch__recent-title a:hover { color: #7B5CFF; }

.oox-arch__cats { list-style: none; margin: 0; padding: 0; }
.oox-arch__cat label {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 9px 0;
  cursor: pointer;
  font-family: "Plus Jakarta Sans", sans-serif;
  font-size: 15px;
  color: #0F1118;
}
/* The native box is hidden but still focusable — the visible box mirrors it. */
.oox-arch__cat input {
  position: absolute;
  width: 1px;
  height: 1px;
  opacity: 0;
  pointer-events: none;
}
.oox-arch__cat-box {
  flex: none;
  width: 20px;
  height: 20px;
  border: 1px solid #DDE6F5;
  border-radius: 6px;
  background: #EEF3FA;
  transition: background-color 200ms ease-out, border-color 200ms ease-out;
}
.oox-arch__cat input:checked + .oox-arch__cat-box {
  background: #FF8A5B;
  border-color: #FF8A5B;
}
.oox-arch__cat input:focus-visible + .oox-arch__cat-box {
  outline: 2px solid #7B5CFF;
  outline-offset: 2px;
}
.oox-arch__cat-name { flex: 1 1 auto; }
.oox-arch__cat input:checked ~ .oox-arch__cat-name { font-weight: 700; }
.oox-arch__cat-count { color: #606060; font-size: 14px; }
.oox-arch__apply {
  margin-top: 12px;
  padding: 10px 18px;
  border: 0;
  border-radius: 14px;
  background: #7B5CFF;
  color: #EEF3FA;
  font-family: "Poppins", sans-serif;
  font-weight: 600;
  cursor: pointer;
}

/* The stack point is 1200, not 1024. Between those two the sidebar still took
 * its fixed 300px plus the 48px gap while a row kept its 300px thumb, leaving
 * the title roughly 330px: long headlines broke mid-word ("Developm / ent",
 * "Outsourci / ng") and the whole archive read as compacted. Stacking the
 * sidebar and narrowing the thumb through the whole tablet band gives the
 * title the full measure back. */
@media (max-width: 1280px) {
  .oox-arch,
  .oox-blogtop { grid-template-columns: minmax(0, 1fr); gap: 32px; }
  .oox-arch__row { grid-template-columns: 200px minmax(0, 1fr); gap: 20px; }
}
@media (max-width: 640px) {
  .oox-arch__row { grid-template-columns: minmax(0, 1fr); }
}
@media (prefers-reduced-motion: reduce) {
  .oox-arch__pill,
  .oox-arch__cat-box { transition: none; }
}

/* --------------------------------------------------------------------------
 * Blog + archive: make room for the sidebar inside the theme's own width.
 *
 * The container stays at the theme's 1410px. What the sidebar needed was the
 * 105px side padding the blog widget carries: with the sidebar taking 348px,
 * that gutter left only an 852px reading column, which squeezed the rows.
 * Trimming the gutter recovers the space without widening the site.
 * ------------------------------------------------------------------------ */
/* The 1200px the blog widget actually gets is its container's 105px side
 * padding, not a width or max-width — that gutter, minus the sidebar's 348px,
 * is what squeezed the featured row. Trim the gutter where the sidebar is
 * present; the page's own 15px edge padding still applies outside this. */
/* !important is deliberate here. The 105px comes from an Elementor per-element
 * rule that outranks a plain class selector (and is in a stylesheet the page
 * cannot enumerate, so it cannot be matched precisely). This is the one place
 * in this file that escalates; keep it scoped to :has() so it can only ever
 * affect the two page types that carry the sidebar. */
.elementor-widget-container:has(> .omero-baf .oox-blogtop),
.elementor-widget-container:has(> .oox-arch) {
  padding-left: 30px !important;
  padding-right: 30px !important;
}

/* --------------------------------------------------------------------------
 * Home "what we offer": hover the whole service button, not a box inside it.
 * Client: "this hover is not working properly, it should be like this" (Figma).
 *
 * Each service is an Elementor button widget (.oox-service-btn) that carries the
 * dark translucent pill — 229x78, 16px padding, 16px radius — with the <a>
 * inside it at 197x46 and transparent. The theme puts the hover background on
 * that inner anchor, so hovering paints a 197x46 panel inside the 229x78 pill:
 * a box in a box, with the padding showing as a frame around it.
 *
 * Move the hover to the wrapper so the whole pill fills, and keep the anchor
 * transparent. Same colour the theme already used, so nothing new enters the
 * design; :focus-within gives keyboard users the same feedback.
 * ------------------------------------------------------------------------ */
.oox-service-btn {
  transition: background-color 240ms cubic-bezier(0.25, 1, 0.5, 1);
}
.oox-service-btn:hover,
.oox-service-btn:focus-within {
  background-color: rgba(255, 255, 255, 0.4);
}
/* !important: the inner hover comes from an Elementor per-element rule keyed to
 * this widget's generated id, which outranks any class selector. Without it the
 * anchor keeps painting its own 40% white ON TOP of the wrapper's 40% — the two
 * translucent layers compose to ~64% and the inner box reappears. */
.oox-service-btn:hover .elementor-button,
.oox-service-btn:focus-within .elementor-button,
.oox-service-btn .elementor-button:hover,
.oox-service-btn .elementor-button:focus {
  background-color: transparent !important;
}
@media (prefers-reduced-motion: reduce) {
  .oox-service-btn { transition: none; }
}


/* --------------------------------------------------------------------------
 * Reviews slider: soften the slide transition.
 * Client: "could these transitions be more smooth? I dont know how..."
 *
 * The slider is a Swiper running a 500ms slide with the browser default easing,
 * which starts and stops abruptly. Only the timing function is changed, to the
 * same curve the site's buttons already use — the duration is left alone
 * because Swiper writes it inline per gesture (including 0ms while dragging),
 * and overriding that would make dragging feel broken.
 * ------------------------------------------------------------------------ */
.elementor-main-swiper .swiper-wrapper {
  transition-timing-function: cubic-bezier(0.25, 1, 0.5, 1);
}
@media (prefers-reduced-motion: reduce) {
  .elementor-main-swiper .swiper-wrapper { transition-duration: 1ms !important; }
}

/* --------------------------------------------------------------------------
 * iPad portrait (768-880px): give mobile-only widgets their inset back.
 * Client: "when scaled to these weird sizes, the design really breaks".
 *
 * Elementor switches VISIBILITY and SPACING at different widths on this theme.
 * A widget marked hidden-desktop/laptop/tablet_extra/tablet keeps showing until
 * 880px, but the padding that positions it is inside a (max-width:767px) query.
 * Between those two numbers the widget is on screen with no inset at all: on the
 * service pages the intro paragraph sat flush against the left edge of the
 * screen, 588px wide in an 834px viewport, while the heading above it was
 * properly inset. Below 768 and above 880 the same page is fine, which is
 * exactly the "weird sizes" band the client was hitting - an iPad in portrait.
 *
 * Restores the 20px inset the mobile rule uses, and centres the block so it sits
 * under the heading instead of hugging the edge; at 834px that lands the text
 * within ~40px of the heading's own inset and keeps the line length readable.
 * ------------------------------------------------------------------------ */
@media (min-width: 768px) and (max-width: 880px) {
  .elementor-widget.elementor-hidden-desktop.elementor-hidden-tablet {
    padding-inline: 20px;
    margin-inline: auto;
  }
}

/* --------------------------------------------------------------------------
 * Carousel slides (768-1200px): stack the row instead of crushing the text.
 * Client: "when scaled to these weird sizes, the design really breaks";
 * reported again as "from 768 to 1200px site totally off".
 *
 * The reviews slide is a flex row: a portrait image at a fixed 486px with
 * flex:1 0 auto (it may NOT shrink), a 127px gap, then the quote column at
 * flex:0 1 auto (it absorbs every shortfall). Once the row is narrower than
 * 486+127 the quote column is handed whatever is left - which at 834px is
 * exactly 0px. A zero-width paragraph wraps one character per line, so the
 * slide grew to 8,589px tall and the home page went from 9,148px to 16,236px.
 * Measured: quote column 0px @834, 171px @1024, 347px @1200, 427px @1280.
 *
 * The fix is to let the row wrap, and to give the text a floor so it wraps
 * rather than shrinks to nothing. Above 1200 nothing changes: the row fits, so
 * wrap never triggers and the design is untouched. The smaller gap applies only
 * in the band where the columns end up stacked, where 127px would be a chasm.
 * ------------------------------------------------------------------------ */
@media (max-width: 1200px) {
  .elementor-widget-omero-nested-carousel .swiper-slide .e-con.e-flex {
    flex-wrap: wrap;
    gap: 40px;
    justify-content: center;
  }
  .elementor-widget-omero-nested-carousel .swiper-slide .e-con.e-flex > .e-con {
    min-width: 260px;
  }
}

/* --------------------------------------------------------------------------
 * Stacked band (<=1200px): keep list markers with their text.
 *
 * Below the desktop breakpoint these sections stack and the theme centres the
 * copy, but the lists keep list-style-position:outside. The marker is then laid
 * against the far-left edge of a full-width block while the text it belongs to
 * sits centred hundreds of pixels away - on the home page the bullets sat at
 * x=63 with their text starting around x=380. Moving the marker inside keeps
 * each bullet attached to its own line.
 * ------------------------------------------------------------------------ */
@media (max-width: 1200px) {
  .elementor-widget-text-editor ul,
  .elementor-widget-text-editor ol {
    list-style-position: inside;
  }
}

/* --------------------------------------------------------------------------
 * Stacked band (<=1200px): the "about us" button had no alignment class.
 *
 * NOTE for anyone re-measuring this: there are two "about us" links on the
 * home page. The one in the header nav measures 72x40 at y=56 and is always
 * fine - measuring that one is why this was previously written off as "not
 * reproducible". The broken one is the section CTA, a 182x56 button.
 *
 * The widget carries 'elementor-align-right' plus
 * 'elementor-tablet_extra-align-center'. Elementor implements both as plain
 * text-align, which only bites once the widget has width to align inside. As a
 * flex item this widget shrink-wraps to the button's own 182px, so text-align
 * has nothing to work with and it sits at the container's start edge: measured
 * x=15 at 1194px, hard against the viewport edge.
 *
 * The fix is width, not display. An earlier version of this block used
 * 'display: flex; justify-content: center', which quietly broke the hero:
 * Elementor hides responsive widgets with '.elementor .elementor-hidden-tablet
 * { display: none }' - specificity 0,2,0, exactly the same as
 * '.oox-btn.elementor-align-right'. Because this sheet loads last to win ties,
 * the 'display: flex' beat 'display: none' and un-hid the mobile-only "get in
 * touch" button across 881-1200, where it collided with the targets.png
 * artwork that is visible from 881 up. Never set 'display' in a rule matching
 * a widget that carries an elementor-hidden-* class.
 * ------------------------------------------------------------------------ */
@media (max-width: 1200px) {
  .oox-btn.elementor-align-right {
    width: 100%;
    text-align: center;
  }
}

/* --------------------------------------------------------------------------
 * 1025-1366px: the 'about us' button was clipped by the section below it.
 *
 * The purple 'what we offer' section (e05a78d) is pulled up over the tail of
 * the white section with margin-top: -235px - that negative pull is what cuts
 * the notch silhouette, and it is the same at every width. What changes is how
 * much room the white section leaves below the button, and across 1060-1366 it
 * leaves too little: measured button bottom 1528 against a purple top of 1509
 * at 1194px, so the lower third of the button was painted over. The band edge
 * is 1025 rather than a rounder number because that is Elementor's
 * tablet_extra breakpoint, where this section's padding changes: 1024px
 * clears by 30px, 1025px overlaps by 19px. Below 1025 and at 1440+ there is a
 * natural 30-61px of clearance and nothing is wrong.
 *
 * Scoped to the button's own widget id so the header 'get in touch' button,
 * which shares the .oox-btn.elementor-align-right selector, is not moved.
 *
 * The doubled class is not a typo. Elementor's generated post CSS carries
 * '.elementor-22 .elementor-element.elementor-element-7538fbc { margin: 30px 0
 * calc(...) 0 }' - three classes, and a shorthand, so a single-class rule here
 * is outranked and silently loses the bottom margin. Matching its 0,3,0
 * specificity lets this sheet's later position win the tie.
 * ------------------------------------------------------------------------ */
@media (min-width: 1025px) and (max-width: 1366px) {
  .elementor .elementor-element.elementor-element-7538fbc {
    margin-bottom: 56px;
  }
}

/* The same collision recurs at 768-880px, where the stacked layout leaves the
 * button 10px inside the notch (measured button bottom 1585 against a purple
 * top of 1575 at 768px). 40px of clearance here lands it on the same 30px gap
 * the untouched widths already have. 881-1024 needs nothing: it clears by 30px
 * on its own. */
@media (min-width: 768px) and (max-width: 880px) {
  .elementor .elementor-element.elementor-element-7538fbc {
    margin-bottom: 40px;
  }
}

/* --------------------------------------------------------------------------
 * Wrapped carousel slide (<=1200px): align the stack, cap the measure.
 *
 * The wrap fix above stopped the quote column collapsing to 0px, but left the
 * result visually off: 'justify-content: center' centres the 486px portrait on
 * its own line while the quote below it stays hard against the column's left
 * padding edge (measured image x=358, text x=122 at 1194px), so the two halves
 * of one testimonial share no edge and a wide band of dead space opens to the
 * right of the text.
 *
 * Aligning the wrapped line to the start gives the portrait and the quote the
 * same left edge, and the measure cap keeps the quote from running the full
 * column width once it is no longer being squeezed. Both are scoped to the
 * same band as the wrap, so the desktop row is untouched.
 * ------------------------------------------------------------------------ */
@media (max-width: 1200px) {
  .elementor-widget-omero-nested-carousel .swiper-slide .e-con.e-flex {
    justify-content: flex-start;
  }
  .elementor-widget-omero-nested-carousel .swiper-slide .e-con.e-flex > .e-con p {
    max-width: 65ch;
  }
}

/* --------------------------------------------------------------------------
 * 881-1200px: centre the hero 'targets' artwork and drop it clear of the notch.
 *
 * The widget is absolutely positioned and anchored to the right edge
 * (right: 97px; bottom: 137px). That reads correctly on desktop, where a much
 * larger 853px render deliberately bleeds off the left edge, but in this band
 * the image is capped at 560px and the right anchor parks it hard against the
 * container's right side - measured x=522 in a 1164px container at 1194px,
 * with a wide dead area to its left and its top tucked under the purple notch.
 *
 * Centring is done by solving the right offset rather than with a transform:
 * this widget carries an Elementor motion_fx translateX scroll effect, which
 * writes to 'transform' from JS on every scroll frame, so a translateX(-50%)
 * used for layout would be overwritten as soon as the page moves. For a 560px
 * widget, right: calc(50% - 280px) puts the box dead centre at any container
 * width and leaves 'transform' free for the scroll effect.
 *
 * Both selectors mirror the specificity of the generated rules they override -
 * Elementor anchors the offset from 'body:not(.rtl) .elementor-22
 * .elementor-element.elementor-element-d77da91', so a weaker selector here is
 * ignored outright.
 * ------------------------------------------------------------------------ */
@media (min-width: 881px) and (max-width: 1200px) {
  body:not(.rtl) .elementor .elementor-element.elementor-element-d77da91 {
    right: calc(50% - 280px);
  }
  .elementor .elementor-element.elementor-element-d77da91 {
    bottom: 27px;
  }
  /* The motion_fx translateX effect drifts the widget sideways as the page
   * scrolls, so the centred box above is only centred at one scroll offset -
   * the artwork visibly sits right of centre by the time it is in view. Motion
   * fx writes transform as an inline style, so !important is the only way to
   * hold it. This trades the parallax drift for staying centred, in this band
   * only; desktop keeps the effect. */
  .elementor .elementor-element.elementor-element-d77da91 {
    transform: none !important;
  }
}

/* --------------------------------------------------------------------------
 * 881-1200px: line the review quote up with the portrait above it.
 *
 * Inside a review slide the portrait is a constant 486px and is centred by the
 * column (at 1024px: a 784px slide, so the image starts 149px in). The name
 * below it stretches the full 784px and the quote widget carries its own
 * width-initial setting that resolves to 386px pinned to the slide's left
 * edge. The result is three different left edges in one card, with the quote
 * starting well to the left of the portrait and wrapping long before its right
 * edge.
 *
 * Constraining the text column to the portrait's width and centring it gives
 * the portrait, the name and the quote one shared left and right edge. 486px
 * is the portrait's rendered width at every width in this band, so it holds
 * across the whole range rather than at one measurement point.
 * ------------------------------------------------------------------------ */
@media (min-width: 768px) and (max-width: 1200px) {
  .elementor .elementor-element.elementor-element-ec1f036 {
    max-width: 486px;
    margin-left: auto;
    margin-right: auto;
  }
  .elementor .elementor-element.elementor-element-366b5f0 {
    width: 100%;
  }
}

/* --------------------------------------------------------------------------
 * Team list: three tiers, because the row needs width the band does not have.
 *
 * A row is 'NN | name | role' on one line. Below roughly 1466px the column is
 * too narrow for both the name and the role to stay unwrapped, and they wrap
 * independently while the row centres them separately - 'Vukasin Despotovic'
 * breaking after the first word beside a 'Business Developer, Co-Founder'
 * breaking after the comma, the number centred against the pair. Narrower
 * still and even the name alone will not fit.
 *
 *   <=1000px      heading, intro and the 'see all' link only. Elementor
 *                 already hides the list at <=880 via its mobile_extra and
 *                 mobile classes; this carries that same fallback up to 1000.
 *   1001-1465px   the list, names only. Dropping the role column gives every
 *                 name a single line, which puts the numbers back on a regular
 *                 baseline.
 *   >=1466px      untouched - the full row, role column included, has the
 *                 width it was designed for.
 *
 * Nothing is lost at the narrower tiers: the 'see all' link leads to the team
 * page, which carries every name and role in full.
 * ------------------------------------------------------------------------ */
@media (max-width: 1000px) {
  .elementor .elementor-element.elementor-element-9dd5326 {
    display: none;
  }
}

@media (min-width: 1001px) and (max-width: 1465px) {
  .omero-team-list-titles .team-position {
    display: none;
  }
  .omero-team-list-titles .team-button {
    width: 100%;
  }
}

/* --------------------------------------------------------------------------
 * Newsletter: keep the wizard and the chest off the copy.
 *
 * Both decorations are elementor-absolute widgets sitting in a section only
 * 319px tall - the chest is a 400x400 Lottie player (5565060) and the wizard a
 * 308x308 hosted video (3a1ea38). Both declare z-index: 1, and so do the
 * heading and paragraph they sit over. A tie in z-index falls back to document
 * order, and the decorations come later in the markup, so they paint on top of
 * the copy rather than behind it. Dropping them one layer restores the
 * stacking the text's own z-index: 1 was always asking for.
 *
 * They overlap the text at every width, but how much matters: at 1440px the
 * art clips roughly 120px off each end of a 622px measure, while at 1024px the
 * chest covers 201-492 and the wizard 562-823 of a 201-823 text - close to
 * half the paragraph behind opaque artwork, which is what makes it unreadable
 * rather than merely busy. So the band that already hides both on mobile and
 * mobile_extra is extended up through tablet and tablet_extra, and the wider
 * viewports keep the art with the corrected stacking.
 * ------------------------------------------------------------------------ */
.elementor .elementor-element.elementor-element-5565060,
.elementor .elementor-element.elementor-element-3a1ea38 {
  z-index: 0;
}

@media (min-width: 881px) and (max-width: 1200px) {
  .elementor .elementor-element.elementor-element-5565060,
  .elementor .elementor-element.elementor-element-3a1ea38 {
    display: none;
  }
}

/* --------------------------------------------------------------------------
 * about-us <=1200px: let the timeline photo fill the card.
 *
 * The image widget carries 'aspect-ratio: 1' as Elementor custom CSS, and the
 * img inside it is pinned to a fixed square - 196x196 at base, 300x300 below
 * 1024 - with object-fit: cover over a wide landscape source (929x417 at
 * 930px). That pairing works in the desktop row layout, but below 1200 the
 * card switches to --flex-direction: column and the widget stretches to the
 * card's full width. aspect-ratio: 1 then forces a box as tall as the card is
 * wide, holding an image less than half that size: measured a 690x879 card
 * with a 300x300 photo at 930px, and a 496x701 card with a 196x196 photo at
 * 1194px. That is where the tall blank area under the photo comes from.
 *
 * Releasing the forced square and letting each photo take the card's full
 * width makes them larger and undistorted, and the cards collapse to the
 * height they actually need. The mask that cuts the notch is on the img and
 * follows the new size. Above 1200 the row layout is untouched.
 *
 * Scoped to the timeline column rather than to one widget id: every card has
 * its own image widget (058d8b0, 16205ce, c68ecc5, 9bc44d8, 3e28200, 5df1aed
 * alongside db8287b), and an id-specific rule fixed exactly one of the seven
 * while the rest stayed 196x196 in a 413px card. A shared 16/9 box with
 * object-fit: cover keeps the row rhythm even, since the sources run from
 * 1035x464 landscape to 383x692 portrait and letting each keep its own ratio
 * would make the portraits nearly twice the card's width in height.
 * ------------------------------------------------------------------------ */
@media (max-width: 1200px) {
  .elementor .elementor-element-c278db6 .elementor-widget-image {
    aspect-ratio: auto;
  }
  .elementor .elementor-element-c278db6 .elementor-widget-image img {
    width: 100%;
    height: auto;
    aspect-ratio: 16 / 9;
    object-fit: cover;
  }
}

/* --------------------------------------------------------------------------
 * about-us 1025-1219px: stop pinning the intro while the cards scroll past it.
 *
 * The story section is meant to be two columns: the intro pins on the left
 * (b4481b3 is an elementor-sticky widget) while the year cards scroll up the
 * right. Elementor pins it by writing 'position: fixed; width: ...; top: 100px'
 * inline once the widget scrolls in.
 *
 * That only works while the two are actually side by side. The wrapper switches
 * from --flex-direction: row to column at 1200/1201, and the pinned intro then
 * takes the full content width with the card column centred inside it: at
 * 1100px the pinned block spans x120-980 and the cards x326-774, so every card
 * passes straight through the pinned heading and paragraph. Both carry
 * z-index: 3 and the cards come later in the markup, so they paint on top -
 * the heading and its paragraph end up sliced by opaque white cards.
 *
 * Below 1025 Elementor already leaves the widget unpinned (measured
 * position: relative), and from 1201 up the row layout separates the columns
 * cleanly, so this is scoped to the band in between. The upper bound has to
 * stop at 1200 exactly: by 1219 the wrapper is already a row, and unpinning
 * there stretches the widget to 2231px instead of its 590px column. The spacer Elementor
 * inserts to reserve the pinned element's height has to go with it, or it
 * leaves an empty gap where the intro used to be. !important is required
 * because the values it overrides are inline styles written by Elementor's
 * sticky script.
 * ------------------------------------------------------------------------ */
@media (min-width: 1025px) and (max-width: 1200px) {
  .elementor .elementor-element.elementor-element-b4481b3.elementor-sticky {
    position: relative !important;
    top: auto !important;
    width: auto !important;
    inset-inline-start: auto !important;
    margin-top: 0 !important;
    margin-bottom: 0 !important;
  }
  .elementor-element-b4481b3.elementor-sticky__spacer {
    display: none !important;
  }
}

/* --------------------------------------------------------------------------
 * about-us 768-1200px: run the timeline two cards across.
 *
 * In the stacked layout the card column keeps the width it has in the desktop
 * two-column design - measured 413px inside a ~1000px content area - so the
 * timeline reads as a narrow ribbon with a wide empty margin either side and
 * runs to seven full-height cards. Two across uses the width that is already
 * there and halves the scroll.
 *
 * The column is a flex column by default; a two-track grid reflows the cards
 * without touching their internal markup. align-items: start keeps each card
 * its own natural height instead of stretching the shorter one in a pair.
 * ------------------------------------------------------------------------ */
@media (min-width: 768px) and (max-width: 1200px) {
  .elementor .elementor-element.elementor-element-c278db6 {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    align-items: start;
    gap: 32px;
    width: 100%;
    max-width: none;
  }
}
`;
