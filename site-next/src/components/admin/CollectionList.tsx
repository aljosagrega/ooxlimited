"use client";

import { useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { Plus, Pencil, Trash2 } from "lucide-react";
import { toast } from "sonner";
import { useConfirm } from "./ConfirmDialog";
import { usePagination } from "./Pagination";
import { SeoScoreBadge, wordCount } from "./fields/SeoPanel";
import Select from "./fields/Select";
import type { CollectionSchema } from "@/lib/adminSchema";

type Row = Record<string, unknown>;

function cell(v: unknown): string {
  if (v == null || v === "") return "—";
  if (Array.isArray(v)) return v.join(", ");
  const s = String(v);
  if (/^\d{4}-\d{2}-\d{2}T[\d:.]+Z?$/.test(s)) return s.slice(0, 10); // ISO datetime → date
  return s.length > 48 ? s.slice(0, 48) + "…" : s;
}

/** Stable pastel pill colour derived from the value — same string → same hue. */
function BadgeCell({ value }: { value: unknown }) {
  const s = cell(value);
  if (s === "—") return <span style={{ fontSize: 12, color: "var(--at-faint)" }}>—</span>;
  let h = 0;
  for (let i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) >>> 0;
  const hue = h % 360;
  return (
    <span style={{
      fontSize: 11, fontWeight: 600, padding: "2px 9px", borderRadius: 20, textTransform: "capitalize",
      background: `hsl(${hue} 65% 50% / 0.15)`, color: `hsl(${hue} 65% 55%)`,
      whiteSpace: "nowrap",
    }}>
      {s}
    </span>
  );
}

function isPublished(schema: CollectionSchema, row: Row): boolean | null {
  if (!schema.publishedField) return null;
  return schema.publishedField === "hidden" ? !row[schema.publishedField] : !!row[schema.publishedField];
}

export default function CollectionList({ schema, rows }: { schema: CollectionSchema; rows: Row[] }) {
  const router = useRouter();
  const confirm = useConfirm();
  const [search, setSearch] = useState("");
  const [colFilter, setColFilter] = useState<Record<string, string>>({});
  const [deleting, setDeleting] = useState<number | null>(null);

  const cols = schema.columns ?? [];

  // Dropdown filters for columns flagged `filter` — options come from the
  // matching field's `select` options, falling back to distinct row values.
  const filterCols = cols
    .filter((c) => c.filter)
    .map((c) => {
      const field = schema.fields.find((f) => f.key === c.key);
      const options = field?.options
        ? field.options.map((o) => ({ value: String(o.value), label: o.label }))
        : [...new Set(rows.map((r) => String(r[c.key] ?? "")).filter(Boolean))]
            .sort()
            .map((v) => ({ value: v, label: v }));
      return { key: c.key, label: c.label, options };
    })
    .filter((c) => c.options.length > 1);

  const sorted = [...rows].sort((a, b) => {
    if (schema.orderField) return (Number(a[schema.orderField]) || 0) - (Number(b[schema.orderField]) || 0);
    if (schema.newestFirst) return (Number(b.id) || 0) - (Number(a.id) || 0);
    return 0;
  });
  const filtered = sorted.filter((r) => {
    if (search && !String(r[schema.titleField] ?? "").toLowerCase().includes(search.toLowerCase())) return false;
    return filterCols.every((c) => !colFilter[c.key] || String(r[c.key] ?? "") === colFilter[c.key]);
  });
  const { pageItems, control } = usePagination(filtered, 25);

  const seo = schema.seo;
  const gridCols = `1fr ${cols.map(() => "120px").join(" ")} ${seo ? "64px" : ""} ${schema.publishedField ? "90px" : ""} 90px`;

  function seoInput(r: Row) {
    const str = (k?: string) => (k && r[k] != null ? String(r[k]) : "");
    // The list page ships a precomputed word count (VIEW_WORD_COUNT) so the
    // large html body never reaches the client; fall back to counting locally.
    const wc = r.__wordCount;
    return {
      effectiveTitle: str(seo!.titleField),
      effectiveDescription: str(seo!.descriptionField) || str(seo!.descriptionFallbackField),
      slug: str(seo!.slugField),
      bodyWordCount: typeof wc === "number" ? wc : wordCount(str(seo!.bodyField)),
      hasImage: seo!.imageField ? !!str(seo!.imageField) : undefined,
      imageLabel: seo!.imageLabel,
    };
  }

  async function handleDelete(row: Row) {
    const ok = await confirm({
      title: `Delete "${cell(row[schema.titleField])}"?`,
      message: "This removes it from the JSON store and rebuilds affected pages.",
      danger: true,
    });
    if (!ok) return;
    setDeleting(Number(row.id));
    try {
      const res = await fetch(`/api/admin/${schema.slug}/${row.id}`, { method: "DELETE" });
      if (res.ok) { toast.success("Deleted"); router.refresh(); }
      else toast.error("Delete failed");
    } finally {
      setDeleting(null);
    }
  }

  return (
    <div className="admin-content-pad">
      <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: 24, flexWrap: "wrap", gap: 12 }}>
        <div>
          <h1 style={{ fontSize: 22, fontWeight: 600, color: "var(--at-text)", margin: 0 }}>{schema.label}</h1>
          <p style={{ fontSize: 13, color: "var(--at-muted)", marginTop: 4 }}>
            {filtered.length === rows.length ? `${rows.length} total` : `${filtered.length} of ${rows.length}`}
          </p>
        </div>
        {!schema.noCreate && (
          <Link href={`/admin/${schema.slug}/new`} className="btn btn-primary"><Plus size={14} /> New {schema.singular}</Link>
        )}
      </div>

      <div style={{ display: "flex", gap: 10, flexWrap: "wrap", marginBottom: 20 }}>
        <input
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          placeholder={`Search ${schema.label.toLowerCase()}…`}
          style={{ flex: "1 1 240px", maxWidth: 340, padding: "9px 14px", borderRadius: 10, fontSize: 13, border: "1px solid var(--at-border-input)", background: "var(--at-input)", color: "var(--at-text)", outline: "none", fontFamily: "inherit" }}
        />
        {filterCols.map((c) => (
          <Select
            key={c.key}
            size="sm"
            highlightWhenSet
            ariaLabel={`Filter by ${c.label}`}
            minWidth={168}
            value={colFilter[c.key] ?? ""}
            onChange={(v) => setColFilter((f) => ({ ...f, [c.key]: v }))}
            options={[{ value: "", label: `All ${c.label.toLowerCase()}` }, ...c.options]}
          />
        ))}
      </div>

      <div style={{ borderRadius: 14, border: "1px solid var(--at-border)", overflow: "hidden" }}>
        <div className="news-tbl-head" style={{ display: "grid", gridTemplateColumns: gridCols, gap: 12, padding: "10px 16px", background: "var(--at-row-even)", borderBottom: "1px solid var(--at-border)", fontSize: 11, fontWeight: 600, textTransform: "uppercase", letterSpacing: "0.06em", color: "var(--at-faint)" }}>
          <span>{schema.fields.find((f) => f.key === schema.titleField)?.label ?? "Title"}</span>
          {cols.map((c) => <span key={c.key} className="news-col-cat">{c.label}</span>)}
          {seo && <span className="news-col-cat">SEO</span>}
          {schema.publishedField && <span>Status</span>}
          <span />
        </div>
        {filtered.length === 0 && (
          <div style={{ padding: 40, textAlign: "center", color: "var(--at-muted)", fontSize: 13 }}>Nothing found.</div>
        )}
        {pageItems.map((r) => {
          const pub = isPublished(schema, r);
          return (
            <div key={String(r.id)} className="news-tbl-row" style={{ display: "grid", gridTemplateColumns: gridCols, gap: 12, padding: "12px 16px", borderBottom: "1px solid var(--at-border-row)", alignItems: "center" }}>
              <span className="news-col-title" title={String(r[schema.titleField] ?? "")} style={{ fontSize: 13, fontWeight: 500, color: "var(--at-text)", overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>
                {/* full string — the 1fr column + CSS ellipsis truncate to fit, so wide screens show more */}
                {r[schema.titleField] ? String(r[schema.titleField]) : <em style={{ color: "var(--at-faint)" }}>Untitled</em>}
              </span>
              {cols.map((c) => (
                <span key={c.key} className="news-col-cat" style={{ fontSize: 12, color: "var(--at-muted)", overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>
                  {c.badge ? <BadgeCell value={r[c.key]} /> : cell(r[c.key])}
                </span>
              ))}
              {seo && <span className="news-col-cat"><SeoScoreBadge {...seoInput(r)} /></span>}
              {schema.publishedField && (
                <span>
                  <span style={{ fontSize: 11, fontWeight: 600, padding: "2px 8px", borderRadius: 20, background: pub ? "var(--at-ok-bg)" : "var(--at-input)", color: pub ? "var(--at-ok)" : "var(--at-muted)" }}>
                    {schema.statusLabels ? schema.statusLabels[pub ? 0 : 1] : pub ? "Live" : "Hidden"}
                  </span>
                </span>
              )}
              <div className="news-col-edit" style={{ display: "flex", gap: 4, justifyContent: "flex-end" }}>
                <Link href={`/admin/${schema.slug}/${r.id}/edit`} className="btn btn-ghost btn-sm" title="Edit"><Pencil size={13} /></Link>
                <button onClick={() => handleDelete(r)} disabled={deleting === Number(r.id)} className="btn btn-ghost btn-sm btn-ghost-delete" title="Delete"><Trash2 size={13} /></button>
              </div>
            </div>
          );
        })}
      </div>
      {control}
    </div>
  );
}
