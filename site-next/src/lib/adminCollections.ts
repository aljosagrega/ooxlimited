import "server-only";
import { readArray } from "./jsonStore";
import { sanitizeBodyHtml } from "./sanitize";
import { wordCount } from "./seoScore";
import * as content from "./content";
import { SCHEMAS, type CollectionSchema } from "./adminSchema";

type Row = Record<string, unknown>;
type Saver = (rows: Row[]) => void;
const saver = (fn: (rows: never[]) => void): Saver => (rows) => fn(rows as never[]);

interface CollectionConfig {
  file: string;
  save: Saver;
  /** top-level string fields whose value is HTML and must be sanitised on write */
  htmlFields: string[];
}

const CONFIG: Record<string, CollectionConfig> = {
  posts: { file: "posts", save: saver(content.savePosts), htmlFields: ["bodyHtml"] },
  authors: { file: "authors", save: saver(content.saveAuthors), htmlFields: [] },
  team: { file: "team", save: saver(content.saveTeam), htmlFields: [] },
  services: { file: "services", save: saver(content.saveServices), htmlFields: [] },
  submissions: { file: "submissions", save: saver(content.saveSubmissions), htmlFields: [] },
};

export function isCollection(slug: string): boolean {
  return slug in CONFIG;
}

export function listRows(slug: string): Row[] {
  const cfg = CONFIG[slug];
  return cfg ? readArray<Row>(cfg.file) : [];
}

export function getRow(slug: string, id: number): Row | null {
  return listRows(slug).find((r) => Number(r.id) === id) ?? null;
}

export const VIEW_WORD_COUNT = "__wordCount";

const dotGet = (row: Row, key: string): unknown =>
  key.split(".").reduce<unknown>((acc, k) => (acc && typeof acc === "object" ? (acc as Row)[k] : undefined), row);

export function listRowsForView(schema: CollectionSchema): Row[] {
  const keep = new Set<string>(["id", schema.titleField]);
  if (schema.publishedField) keep.add(schema.publishedField);
  if (schema.orderField) keep.add(schema.orderField);
  for (const c of schema.columns ?? []) keep.add(c.key);

  const seo = schema.seo;
  if (seo) {
    for (const k of [seo.titleField, seo.descriptionField, seo.descriptionFallbackField, seo.slugField, seo.imageField, "metaTitle", "noindex"]) {
      if (k) keep.add(k);
    }
  }

  return listRows(schema.slug).map((r) => {
    const out: Row = {};
    for (const k of keep) {
      const v = k.includes(".") ? dotGet(r, k) : r[k];
      if (v !== undefined) out[k] = v;
    }
    if (seo?.bodyField) {
      const body = typeof r[seo.bodyField] === "string" ? (r[seo.bodyField] as string) : "";
      out[VIEW_WORD_COUNT] = wordCount(body);
    }
    return out;
  });
}

function nextId(rows: Row[]): number {
  return rows.reduce((max, r) => Math.max(max, Number(r.id) || 0), 0) + 1;
}

function sanitizeRow(slug: string, row: Row): Row {
  const cfg = CONFIG[slug];
  if (!cfg) return row;
  const out: Row = { ...row };
  for (const field of cfg.htmlFields) {
    if (typeof out[field] === "string") out[field] = sanitizeBodyHtml(out[field] as string);
  }
  for (const k of ["metaTitle", "canonicalUrl", "ogImage", "metaDescription"] as const) {
    if (out[k] === "" || out[k] == null) delete out[k];
  }
  if (out.noindex === false) delete out.noindex;
  return out;
}

/** Per-collection defaults applied only on create, when the field is left blank. */
function withCreateDefaults(slug: string, data: Row): Row {
  if (slug !== "posts") return data;
  const out: Row = { ...data };
  // A new article always gets a publish date — the client shouldn't have to
  // remember to set one.
  if (!out.date) out.date = new Date().toISOString();
  // New articles start as drafts; the editor flips "Published" on when ready.
  if (out.published === undefined) out.published = false;
  return out;
}

export function createRow(slug: string, data: Row): Row {
  const cfg = CONFIG[slug];
  if (!cfg) throw new Error(`Unknown collection: ${slug}`);
  const rows = listRows(slug);
  const id = nextId(rows);
  const row = sanitizeRow(slug, { ...withCreateDefaults(slug, data), id });
  rows.push(row);
  cfg.save(rows);
  return row;
}

export function updateRow(slug: string, id: number, data: Row): Row | null {
  const cfg = CONFIG[slug];
  if (!cfg) throw new Error(`Unknown collection: ${slug}`);
  const rows = listRows(slug);
  const idx = rows.findIndex((r) => Number(r.id) === id);
  if (idx === -1) return null;
  const merged = sanitizeRow(slug, { ...rows[idx], ...data, id });
  // A saved post's body now drives the live page (see singleContent.ts) —
  // before this it renders the exact frozen markup.
  if (slug === "posts" && typeof data.bodyHtml === "string") merged.bodyDirty = true;
  // Once a team profile is edited in the admin, its frozen page is rebuilt from
  // team.json on every render (see singleContent.ts). Until then it stays frozen.
  if (slug === "team") merged.teamDirty = true;
  rows[idx] = merged;
  cfg.save(rows);
  return merged;
}

export function deleteRow(slug: string, id: number): boolean {
  const cfg = CONFIG[slug];
  if (!cfg) throw new Error(`Unknown collection: ${slug}`);
  const rows = listRows(slug);
  const next = rows.filter((r) => Number(r.id) !== id);
  if (next.length === rows.length) return false;
  cfg.save(next);
  return true;
}

export type RefOption = { value: number; label: string };

export function getRefOptions(schema: CollectionSchema): Record<string, RefOption[]> {
  const out: Record<string, RefOption[]> = {};
  for (const f of schema.fields) {
    if (!f.ref) continue;
    const labelField = f.ref.labelField ?? "title";
    out[f.key] = listRows(f.ref.collection)
      .map((r) => ({ value: Number(r.id), label: String(r[labelField] ?? `#${r.id}`) }))
      .sort((a, b) => a.label.localeCompare(b.label));
  }
  return out;
}

export function collectionCounts(): Record<string, { total: number; published: number }> {
  const out: Record<string, { total: number; published: number }> = {};
  for (const [slug, schema] of Object.entries(SCHEMAS)) {
    const rows = listRows(slug);
    const pf = schema.publishedField;
    const published = pf ? rows.filter((r) => !!r[pf]).length : rows.length;
    out[slug] = { total: rows.length, published };
  }
  return out;
}
