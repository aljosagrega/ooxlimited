import "server-only";

/**
 * Per-page corrections to frozen Elementor markup that can't be fixed upstream
 * without re-authoring the WordPress page, and would return on the next
 * `npm run freeze`. Applied at render time (see the catch-all route).
 *
 * Keep these surgical: match an exact frozen string, change one thing, and be a
 * no-op for every page that doesn't contain it. Nothing here may change how the
 * page looks — only its markup semantics.
 */

interface Fixup {
  /** route path this applies to, or null for "any" */
  path: string | null;
  find: string;
  replace: string;
  /** why this exists */
  reason: string;
}

const FIXUPS: Fixup[] = [
  {
    // Every other /service/ hero uses <h1> for the page title; this one was
    // authored as <h2>, leaving the page with no H1. The Elementor heading
    // widget sets all typography by class, so h1/h2 render identically here
    // (verified: same computed font-size/line-height/weight/colour).
    path: "/service/art-design-art-direction/",
    find: '<h2 class="elementor-heading-title elementor-size-default" data-oox-e="e7">Art Design &amp; Direction </h2>',
    replace: '<h1 class="elementor-heading-title elementor-size-default" data-oox-e="e7">Art Design &amp; Direction </h1>',
    reason: "restore missing H1 on the art-design service page",
  },
];

export function applyFrozenFixups(routePath: string, bodyHtml: string): string {
  let out = bodyHtml;
  for (const f of FIXUPS) {
    if (f.path && f.path !== routePath) continue;
    out = out.replace(f.find, f.replace);
  }
  return out;
}
