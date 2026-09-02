import sanitizeHtml from "sanitize-html";

// Allow-list for CMS-authored prose (wikibrain article bodies, page prose fields,
// VIP tier tables). Enforced on the write path in every lib mutator.
export const ALLOWED_HTML: sanitizeHtml.IOptions = {
  allowedTags: [
    "p", "br", "hr", "span", "strong", "b", "em", "i", "u", "s", "sup", "sub", "mark",
    "h1", "h2", "h3", "h4", "h5", "h6",
    "ul", "ol", "li",
    "a", "blockquote", "code", "pre", "img", "figure", "figcaption", "div", "section",
    "table", "thead", "tbody", "tfoot", "tr", "th", "td", "caption", "colgroup", "col",
    // The ooxlimited blog posts scope their own typography with an inline
    // <style> block at the top of the body — keep it so admin edits don't
    // strip the article styling.
    "style",
  ],
  allowedAttributes: {
    "*": ["id", "class"],
    a: ["href", "target", "rel", "name", "class", "id"],
    img: ["src", "alt", "width", "height", "loading", "srcset", "sizes", "decoding"],
    div: ["class", "id", "style"],
    span: ["class", "id", "style"],
    p: ["class", "id", "style"],
    table: ["class", "id"],
    td: ["colspan", "rowspan", "colwidth", "class", "style"],
    th: ["colspan", "rowspan", "colwidth", "scope", "class", "style"],
    col: ["span", "width"],
    style: [],
  },
  allowedStyles: {
    "*": {
      "text-align": [/^(left|right|center|justify)$/],
      "font-weight": [/^(\d{3}|normal|bold)$/],
      color: [/^#[0-9a-fA-F]{3,8}$|^rgba?\([\d.,\s]+\)$/],
      "background-color": [/^#[0-9a-fA-F]{3,8}$|^rgba?\([\d.,\s]+\)$/],
    },
  },
  allowVulnerableTags: true, // <style> is intentional here (see note above)
  allowedSchemes: ["http", "https", "mailto", "tel"],
  transformTags: {
    a: sanitizeHtml.simpleTransform("a", { rel: "noopener" }, true),
  },
};

/** Legacy PHP prose stored root-relative paths without the leading slash
 *  (`src="uploads/…"`, `src="template/original/gfx/…"`, `href="en/betting/"`),
 *  which resolve against the current page in Next → 404. Root them on every
 *  write so the stored value is correct and an admin edit+save can't reintroduce
 *  the broken form. Mirrors `RawHtml.fix()` on the read path. (One-off backfill:
 *  scripts/migrate/fix-body-image-paths.ts.) */
function normalizeAssetPaths(html: string): string {
  return html
    .replace(/((?:src|href)=")uploads\//gi, "$1/uploads/")
    .replace(/((?:src|href)=")template\/original\/gfx\//gi, "$1/gfx/")
    .replace(/(href=")((?:en|es|ru|pt)\/)/gi, "$1/$2");
}

export function sanitizeBodyHtml(html: string): string {
  return normalizeAssetPaths(sanitizeHtml(html ?? "", ALLOWED_HTML));
}

// Allow-list for short UI strings (the `translations.json` dictionary). Migrated
// values carry `<br>`, `<strong>`, `<span style|class>` and the odd `<p class>`
// wrapper — keep those, drop everything else.
const ALLOWED_INLINE: sanitizeHtml.IOptions = {
  allowedTags: ["p", "br", "span", "strong", "b", "em", "i", "u", "sup", "sub", "a"],
  allowedAttributes: {
    span: ["class", "style"],
    p: ["class", "style"],
    a: ["href", "target", "rel", "class"],
  },
  allowedStyles: {
    "*": {
      "font-weight": [/^\d{3}$|^(normal|bold)$/],
      color: [/^#[0-9a-fA-F]{3,8}$|^rgba?\([\d.,\s]+\)$/],
      "text-align": [/^(left|right|center|justify)$/],
      "text-decoration": [/^(underline|line-through|none)$/],
    },
  },
  allowedSchemes: ["http", "https", "mailto", "tel"],
};

export function sanitizeInline(html: string): string {
  return sanitizeHtml(html ?? "", ALLOWED_INLINE);
}
