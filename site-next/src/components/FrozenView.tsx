import { getFrozenAssetsByKey } from "@/lib/frozen";
import FrozenScripts from "./FrozenScripts";
import FrozenForms from "./FrozenForms";

/**
 * Server component that reproduces a frozen WordPress page: its stylesheet +
 * inline-style stack (SSR'd into <head> by React's resource hoisting), the body
 * markup, and — via <FrozenScripts> — its script stack replayed on the client.
 *
 * `patchedBody`, when given, replaces the frozen body markup (field-map edits
 * applied server-side). Otherwise the untouched frozen markup is used.
 */
export default function FrozenView({
  frozenKey,
  patchedBody,
}: {
  /** frozen file key — the route key, or a shared shell like `_post-template` */
  frozenKey: string;
  patchedBody?: string;
}) {
  const a = getFrozenAssetsByKey(frozenKey);
  if (!a) return null;

  // Scripts are replayed by <FrozenScripts> in order. Any <script> left in the
  // SSR'd markup (patchedBody comes from cheerio, which keeps them) would run
  // during the initial HTML parse — out of order, before jQuery — throwing
  // "jQuery is not defined" and then double-executing. Strip them here.
  const bodyMarkup = stripScripts(patchedBody ?? a.bodyHtml);

  return (
    <>
      {a.headLinks.map((tag, i) => (
        <PassthroughLink key={i} tag={tag} />
      ))}
      {a.stylesheets.map((href) => (
        // React 19 hoists <link rel="stylesheet"> to <head> and dedupes by href.
        <link key={href} rel="stylesheet" href={href} precedence="frozen" />
      ))}
      {a.styles.map((css, i) => (
        <style key={i} dangerouslySetInnerHTML={{ __html: css }} />
      ))}

      {/* SSR'd before any JS: Elementor marks entrance-animated elements
          `elementor-invisible` (visibility:hidden) and only reveals them once
          frontend.js runs. The frozen script stack runs a beat later than it
          does in WordPress, so without this the hero headline / sections flash
          blank on load. Force them visible immediately; the entrance animation
          still replays when <FrozenScripts> kicks Elementor. */}
      <style
        dangerouslySetInnerHTML={{
          __html: `.elementor-invisible{visibility:visible!important;}
.elementor-widget.elementor-invisible,.elementor-element.elementor-invisible{opacity:1!important;}`,
        }}
      />


      <div
        id="frozen-root"
        className={a.bodyClass}
        dangerouslySetInnerHTML={{ __html: bodyMarkup }}
      />

      <FrozenScripts scripts={a.scripts} />
      <FrozenForms />
    </>
  );
}

function stripScripts(html: string): string {
  return html
    .replace(/<script\b[^>]*>[\s\S]*?<\/script>/gi, "")
    .replace(/<script\b[^>]*\/>/gi, "");
}

/** Render a raw <link ...> tag (preconnect / dns-prefetch / preload) as JSX. */
function PassthroughLink({ tag }: { tag: string }) {
  const attrs: Record<string, string> = {};
  for (const m of tag.matchAll(/([a-zA-Z-]+)=["']([^"']*)["']/g)) {
    const name = m[1] === "crossorigin" ? "crossOrigin" : m[1];
    attrs[name] = m[2];
  }
  return <link {...attrs} />;
}
