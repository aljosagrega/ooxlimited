/**
 * Client-safe schema describing every editable collection: which fields the
 * admin form renders and which list columns show. The server write layer
 * (`adminCollections.ts`) keys off the same slugs and owns HTML sanitisation.
 *
 * Adapted from a sibling project for the ooxlimited content model. There is no
 * per-locale editing here (single-locale site) so the `i18n` machinery is unused.
 */

export type FieldType =
  | "text"
  | "textarea"
  | "html"
  | "number"
  | "boolean"
  | "date"
  | "postStatus"
  | "image"
  | "imageObject"
  | "stringList"
  | "qaList"
  | "tagList"
  | "select"
  | "json"
  // accepted by the ported SchemaForm but unused in the ooxlimited schema
  | "color"
  | "colorToken"
  | "ref"
  | "refList";

export interface FieldDef {
  key: string;
  label: string;
  type: FieldType;
  i18n?: boolean;
  placeholder?: string;
  help?: string;
  options?: { value: string; label: string }[];
  full?: boolean;
  rows?: number;
  folder?: string;
  youtubeThumbFrom?: string;
  fallbackFrom?: string;
  ref?: { collection: string; labelField?: string };
  group?: "seo";
}

export interface CollectionSchema {
  slug: string;
  file: string;
  label: string;
  singular: string;
  icon: string;
  titleField: string;
  columns?: { key: string; label: string; badge?: boolean; filter?: boolean }[];
  publishedField?: string;
  /** date field that, when set in the future on a published row, means
   *  "scheduled" — shown as its own status in the list (see CollectionList). */
  scheduleField?: string;
  statusLabels?: [string, string];
  fields: FieldDef[];
  orderField?: string;
  noCreate?: boolean;
  newestFirst?: boolean;
  /** show the side-by-side live preview panel in the edit form */
  preview?: boolean;
  seo?: {
    titleField: string;
    descriptionField: string;
    descriptionFallbackField?: string;
    bodyField: string;
    slugField?: string;
    imageField?: string;
    imageLabel?: string;
    /** URL shape for the SERP preview, `{slug}` substituted. Ignored when a
     *  `path` field is present (pages). Default `/{slug}/`. */
    urlPattern?: string;
  };
}

const SEO_FIELDS: FieldDef[] = [
  { key: "metaTitle", label: "Meta title", type: "text", group: "seo", help: "Overrides the <title>; falls back to the item title" },
  { key: "metaDescription", label: "Meta description", type: "textarea", rows: 2, full: true, group: "seo" },
  { key: "canonicalUrl", label: "Canonical URL", type: "text", full: true, group: "seo", help: "Only set to point at a different canonical page" },
  { key: "ogImage", label: "Social share image", type: "text", full: true, group: "seo" },
  { key: "noindex", label: "Hide from search engines (noindex)", type: "boolean", group: "seo" },
];

export const SCHEMAS: Record<string, CollectionSchema> = {
  posts: {
    slug: "posts",
    file: "posts",
    label: "Blog posts",
    singular: "post",
    icon: "FileText",
    titleField: "title",
    newestFirst: true,
    preview: true,
    publishedField: "published",
    scheduleField: "date",
    statusLabels: ["Live", "Draft"],
    columns: [{ key: "date", label: "Published" }],
    seo: {
      titleField: "title", descriptionField: "metaDescription", descriptionFallbackField: "excerpt",
      bodyField: "bodyHtml", slugField: "slug", imageField: "featuredImage.url", imageLabel: "Featured image",
      urlPattern: "/{slug}/",
    },
    fields: [
      { key: "title", label: "Title", type: "text" },
      // Composite control over `published` + `date` together — see the
      // `postStatus` case in SchemaForm. A lone "Published" checkbox plus a
      // separately-set future date read, to a client, as two unrelated
      // switches: someone set a future date expecting that alone to schedule
      // the post, left "Published" off (it reads as "not yet", which is
      // literally what they wanted), and the post silently never went out.
      { key: "published", label: "Status", type: "postStatus", help: "Draft stays off the site. Scheduled goes live automatically at the date below. Published is live now." },
      { key: "slug", label: "Slug", type: "text", help: "URL segment; the post lives at /<slug>/" },
      { key: "date", label: "Publish date", type: "date", help: "Sets when Scheduled (above) goes live, and the post's sort order / byline date. Ignored while Status is Draft." },
      { key: "authorId", label: "Author", type: "ref", ref: { collection: "authors", labelField: "name" }, help: "Manage the list under Authors" },
      { key: "excerpt", label: "Excerpt", type: "textarea", rows: 3, full: true },
      { key: "featuredImage", label: "Featured image", type: "imageObject" },
      { key: "bodyHtml", label: "Body", type: "html", full: true },
      // Was `type: "json"` — filtered out of the form entirely (SchemaForm
      // only shows it via the advanced "raw JSON" toggle), which is what the
      // client meant by "no option to select tags for a blog post": the
      // theme calls categories "tags" on the cards and in "Popular tags";
      // see taxonomy.ts. Typed names are resolved against the existing set
      // on save (adminCollections.ts resolveCategories), so reusing a name
      // reuses that tag rather than splintering into a near-duplicate.
      { key: "categories", label: "Tags", type: "tagList", full: true, help: "Reuses an existing tag if the name matches one (case-insensitive); otherwise creates a new one." },
      ...SEO_FIELDS,
    ],
  },

  team: {
    slug: "team",
    file: "team",
    label: "Team",
    singular: "team member",
    icon: "Users",
    titleField: "name",
    orderField: "ord",
    columns: [{ key: "position", label: "Role" }],
    fields: [
      { key: "name", label: "Name", type: "text" },
      { key: "slug", label: "Slug", type: "text", help: "URL segment; /team/<slug>/" },
      { key: "ord", label: "Order", type: "number" },
      { key: "position", label: "Position", type: "text" },
      { key: "job", label: "Job title", type: "text" },
      { key: "experience", label: "Experience", type: "text" },
      { key: "responsibility", label: "Responsibility", type: "text" },
      { key: "email", label: "Email", type: "text" },
      { key: "photo", label: "Photo", type: "imageObject" },
      { key: "bio", label: "Bio", type: "textarea", rows: 5, full: true },
      { key: "skills", label: "Skills", type: "stringList", full: true },
      { key: "programs", label: "Programs / tools", type: "stringList", full: true },
      { key: "socials", label: "Social links", type: "json", full: true, help: '{ "fb": "…", "x": "…", "ig": "…", "in": "…" }' },
      // Was `type: "json"` — that type is filtered out of the form entirely
      // (SchemaForm only shows it via the advanced "raw JSON" toggle), which is
      // what the client meant by "no section for questions and answers": the
      // field existed, but never appeared. This renders an actual repeater.
      { key: "qa", label: "Q&A", type: "qaList", full: true, help: "Shown as an accordion on the member's profile page." },
    ],
  },

  services: {
    slug: "services",
    file: "services",
    label: "Services",
    singular: "service",
    icon: "Layers",
    titleField: "title",
    orderField: "ord",
    seo: {
      titleField: "title", descriptionField: "metaDescription", descriptionFallbackField: "excerpt",
      bodyField: "excerpt", slugField: "slug", imageField: "thumbnail.url", imageLabel: "Thumbnail",
      urlPattern: "/service/{slug}/",
    },
    fields: [
      { key: "title", label: "Title", type: "text" },
      { key: "slug", label: "Slug", type: "text", help: "URL segment; /service/<slug>/" },
      { key: "ord", label: "Order", type: "number" },
      { key: "excerpt", label: "Excerpt", type: "textarea", rows: 3, full: true },
      { key: "thumbnail", label: "Thumbnail", type: "imageObject" },
      ...SEO_FIELDS,
    ],
    columns: [],
  },

  authors: {
    slug: "authors",
    file: "authors",
    label: "Authors",
    singular: "author",
    icon: "PenLine",
    titleField: "name",
    columns: [{ key: "role", label: "Role" }],
    fields: [
      { key: "name", label: "Name", type: "text" },
      { key: "slug", label: "Slug", type: "text", help: "URL-safe identifier" },
      { key: "role", label: "Role / title", type: "text" },
      { key: "email", label: "Email", type: "text" },
      { key: "photo", label: "Photo", type: "imageObject" },
      { key: "bio", label: "Bio", type: "textarea", rows: 5, full: true },
    ],
  },

  // "pages" is NOT a generic collection — the frozen public pages (marketing,
  // team, service, blog) are content-edited via /admin/pages + <PageEditor>,
  // which write per-route to src/data/pageEdits.json.

  submissions: {
    slug: "submissions",
    file: "submissions",
    label: "Form submissions",
    singular: "submission",
    icon: "Inbox",
    titleField: "email",
    noCreate: true,
    newestFirst: true,
    publishedField: "handled",
    statusLabels: ["Handled", "New"],
    columns: [
      { key: "kind", label: "Form", badge: true, filter: true },
      { key: "createdAt", label: "Received" },
      { key: "emailStatus", label: "Email" },
    ],
    fields: [
      { key: "kind", label: "Form", type: "select", options: [
        { value: "contact", label: "Contact" },
        { value: "newsletter", label: "Newsletter" },
      ] },
      { key: "createdAt", label: "Received", type: "date" },
      { key: "handled", label: "Handled", type: "boolean" },
      { key: "note", label: "Internal note", type: "textarea", rows: 3, full: true },
      { key: "email", label: "Email", type: "text" },
      { key: "name", label: "Name", type: "text" },
      { key: "message", label: "Message", type: "textarea", rows: 5, full: true },
      { key: "optIn", label: "Newsletter opt-in", type: "boolean" },
      { key: "emailStatus", label: "Email delivery", type: "select", options: [
        { value: "sent", label: "Sent" },
        { value: "failed", label: "Failed" },
        { value: "skipped", label: "Skipped (mail off)" },
      ] },
      { key: "ip", label: "IP", type: "text" },
    ],
  },
};

export const COLLECTION_SLUGS = Object.keys(SCHEMAS);

export function getSchema(slug: string): CollectionSchema | null {
  return SCHEMAS[slug] ?? null;
}

export const EDIT_LOCALES = ["en"] as const;
export type EditLocale = (typeof EDIT_LOCALES)[number];
