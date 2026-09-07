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
 * Header "services" dropdown: restore the hover / selected state.
 * Client: "this hover is not working properly, it should be like this" (Figma).
 *
 * The theme ships the dropdown with a transition but no hover rule at all —
 * :hover matches and nothing changes, at any width. The panel is #EEF3FA and
 * the theme already marks the current menu item in #7B5CFF, so the pill uses
 * the next step down the same ramp (#DDE6F5) and that same purple for text —
 * no new colours enter the design.
 *
 * The pill is painted by a ::before inset behind the label rather than by
 * padding on the anchor: padding would resize every row and shift the frozen
 * dropdown. Keyboard focus gets the same treatment (the theme had no visible
 * focus state either), and the fade is skipped under reduced-motion.
 * ------------------------------------------------------------------------ */
.primary-navigation ul.sub-menu > li.menu-item > a {
  position: relative;
  z-index: 0;
  transition: color 200ms ease-out;
}
.primary-navigation ul.sub-menu > li.menu-item > a::before {
  content: "";
  position: absolute;
  inset: -6px -12px;
  border-radius: 8px;
  background: #DDE6F5;
  opacity: 0;
  z-index: -1;
  transition: opacity 200ms ease-out;
}
.primary-navigation ul.sub-menu > li.menu-item:hover > a::before,
.primary-navigation ul.sub-menu > li.menu-item > a:focus-visible::before {
  opacity: 1;
}
.primary-navigation ul.sub-menu > li.menu-item:hover > a,
.primary-navigation ul.sub-menu > li.menu-item:hover > a .menu-title,
.primary-navigation ul.sub-menu > li.menu-item > a:focus-visible,
.primary-navigation ul.sub-menu > li.menu-item > a:focus-visible .menu-title {
  color: #7B5CFF;
}
.primary-navigation ul.sub-menu > li.menu-item > a:focus-visible {
  outline: 2px solid #7B5CFF;
  outline-offset: 4px;
}
@media (prefers-reduced-motion: reduce) {
  .primary-navigation ul.sub-menu > li.menu-item > a,
  .primary-navigation ul.sub-menu > li.menu-item > a::before { transition: none; }
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
  box-shadow: rgba(0, 0, 0, 0.25) 3px 4px 0 0;
  transition: box-shadow 0.4s cubic-bezier(0.25, 1, 0.5, 1), background-color 200ms ease-out, color 200ms ease-out;
}
.pagination .page-numbers.current {
  background: #7B5CFF;
  color: #EEF3FA;
}
.pagination a.page-numbers:hover,
.pagination a.page-numbers:focus-visible {
  background: #EEF3FA;
  box-shadow: rgba(0, 0, 0, 0.25) 1px 2px 0 0;
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
.oox-arch__head {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 10px 14px;
  margin-bottom: 14px;
}
.oox-arch__tags { margin: 0; padding: 0; }
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
.oox-arch__pill.is-active { background: #FF8A5B; }

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

@media (max-width: 1024px) {
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
 * Blog + archive: widen the content container.
 *
 * The theme caps containers at 1410px. That was sized for a single column of
 * cards; with the design's 300px sidebar beside it the reading column drops to
 * ~850px, which squeezes the rows and — because the decorative building on the
 * featured post is positioned at left:17% OF THAT COLUMN — drags the building
 * out of place. Widening these two page types restores the proportions.
 *
 * Scoped with :has() so it applies only to containers that actually hold the
 * archive or the blog top block. Every other page keeps the theme's 1410px;
 * making the whole site fluid is a separate piece of work.
 * ------------------------------------------------------------------------ */
.e-con:has(.oox-arch),
.e-con:has(.oox-blogtop) {
  --container-max-width: 1560px;
}
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

/* The decorative building on the featured post (baf-featured-building) is
 * positioned at left:17% / width:416px of the featured row. That was tuned for
 * a 1200px row where the text column was wide enough to clear it; with the
 * design's sidebar the row is ~1000px, the title wraps one line further, and the
 * building lands on top of the headline (measured: it spans 170-586 while the
 * text starts at 414).
 *
 * Re-anchor it to the photo it is meant to sit on, in proportional units so the
 * relationship holds at any width. The Figma blog frame has no building at all;
 * this keeps the decoration the live site has, clear of the copy.
 */
.oox-blogtop .baf-featured-building {
  left: 4%;
  width: 34%;
  max-width: 416px;
}
`;
