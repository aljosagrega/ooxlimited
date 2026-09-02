import "server-only";

/**
 * Frozen Elementor markup ships plenty of <img> tags with no `alt` attribute
 * (decorative arrows, background shapes, card flourishes). A missing `alt` is
 * an accessibility and SEO defect; an empty `alt=""` marks the image as
 * decorative, which is the correct default for these.
 *
 * This only adds `alt=""` where the attribute is absent entirely — it never
 * touches an existing `alt` (even an empty one) and never changes anything
 * else, so a page with no bare <img> comes back byte-identical.
 */
export function applyImageAlt(bodyHtml: string): string {
  if (!bodyHtml.includes("<img")) return bodyHtml;
  return bodyHtml.replace(/<img\b[^>]*>/gi, (tag) =>
    /\balt\s*=/i.test(tag) ? tag : tag.replace(/^<img\b/i, '<img alt=""'),
  );
}
