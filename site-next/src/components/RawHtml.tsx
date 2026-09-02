/** Renders CMS prose that was sanitised on the write path. Server component.
 *  - <p> fallback to <div> when the html itself contains block markup (avoids
 *    invalid <p><p></p></p> nesting → hydration break).
 *  - normalises the migrated CMS bug where in-body paths lost their leading
 *    slash: `src="uploads/…"`, `src="template/original/gfx/…"`, `href="en/…"`
 *    → rooted. (Backfilled at rest by fix-body-image-paths.ts + guarded in
 *    sanitize.ts; this stays as read-path defence.)
 *  - adds `loading="lazy" decoding="async"` to in-body <img> that don't already
 *    set `loading` (article bodies carry ~20 images; eager-loading them all
 *    hurts LCP and wastes bandwidth). */
const BLOCK = /<(p|div|ul|ol|h[1-6]|table|section|article|blockquote|figure)[\s>]/i;

function fix(html: string): string {
  return html
    .replace(/(\s(?:src|href))=(["'])uploads\//gi, '$1=$2/uploads/')
    .replace(/(\s(?:src|href))=(["'])template\/original\/gfx\//gi, '$1=$2/gfx/')
    .replace(/(\shref)=(["'])((?:en|es|ru|pt)\/)/gi, '$1=$2/$3')
    .replace(/<img(?![^>]*\sloading=)/gi, '<img loading="lazy" decoding="async"');
}

export default function RawHtml({
  html,
  className,
  as: Tag = "div",
}: {
  html: string;
  className?: string;
  as?: React.ElementType;
}) {
  if (!html) return null;
  const El = Tag === "p" && BLOCK.test(html) ? "div" : Tag;
  return <El className={className} dangerouslySetInnerHTML={{ __html: fix(html) }} />;
}
