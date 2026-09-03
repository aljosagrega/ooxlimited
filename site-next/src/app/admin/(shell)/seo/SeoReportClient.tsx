"use client";

import { useMemo, useState } from "react";
import Link from "next/link";
import { seoScoreColor, seoBucket } from "@/lib/seoScore";
import type { SeoRow } from "@/lib/adminStats";
import { SegmentedBar } from "@/components/admin/charts";
import { STATUS } from "@/lib/chartPalette";
import { usePagination } from "@/components/admin/Pagination";
import Select from "@/components/admin/fields/Select";
import { routeKey } from "@/lib/routeKey";

type Filter = "all" | "poor" | "thin" | "no-title" | "no-description" | "noindex";
type Sort = "score" | "issues" | "words" | "title";
type Kind = SeoRow["kind"];

const FILTERS: { key: Filter; label: string }[] = [
  { key: "all", label: "All" },
  { key: "poor", label: "Poor score" },
  { key: "thin", label: "Thin (<600 words)" },
  { key: "no-title", label: "No title" },
  { key: "no-description", label: "No description" },
  { key: "noindex", label: "Noindexed" },
];

function editHref(r: SeoRow) {
  if (r.kind === "page") return `/admin/pages/${routeKey(r.url)}/edit`;
  return `/admin/${r.kind === "post" ? "posts" : "services"}/${r.id}/edit`;
}

function recommendations(r: SeoRow): string[] {
  const out: string[] = [];
  if (!r.hasTitle) out.push("Add a title");
  if (!r.hasDescription) out.push(r.kind === "page" ? "Add a meta description" : "Add an excerpt / meta description");
  if (r.words === 0 && r.kind === "post") out.push("Write the body");
  else if (r.words < 600 && r.kind === "post") out.push(`Expand the body (${r.words} words → aim 800+)`);
  return out;
}

export default function SeoReportClient({ rows }: { rows: SeoRow[] }) {
  const [filter, setFilter] = useState<Filter>("all");
  const [sort, setSort] = useState<Sort>("score");
  const [q, setQ] = useState("");
  const [kind, setKind] = useState<"all" | Kind>("all");

  const buckets = useMemo(() => {
    const b = { good: 0, ok: 0, poor: 0 };
    for (const r of rows) b[seoBucket(r.good, r.total)]++;
    return b;
  }, [rows]);

  const view = useMemo(() => {
    let v = rows.filter((r) => {
      if (kind !== "all" && r.kind !== kind) return false;
      if (q && !r.title.toLowerCase().includes(q.toLowerCase())) return false;
      switch (filter) {
        case "poor": return seoBucket(r.good, r.total) === "poor";
        case "thin": return r.words < 600;
        case "no-title": return !r.hasTitle;
        case "no-description": return !r.hasDescription;
        case "noindex": return r.noindex;
        default: return true;
      }
    });
    v = [...v].sort((a, b) => {
      if (sort === "title") return a.title.localeCompare(b.title);
      if (sort === "words") return a.words - b.words;
      if (sort === "issues") return b.issues - a.issues;
      return a.good / a.total - b.good / b.total;
    });
    return v;
  }, [rows, filter, sort, q, kind]);

  const { pageItems, control } = usePagination(view, 40);

  return (
    <div className="admin-content-pad" style={{ display: "flex", flexDirection: "column", gap: 20, maxWidth: 1000 }}>
      <div>
        <h1 style={{ fontSize: 22, fontWeight: 600, color: "var(--at-text)", margin: 0 }}>SEO report</h1>
        <p style={{ fontSize: 13, color: "var(--at-muted)", marginTop: 4 }}>
          On-page score for every page, blog post and service — title, meta description, URL, word count, image.
        </p>
      </div>

      <div style={{ borderRadius: 14, border: "1px solid var(--at-border)", background: "var(--at-card)", padding: "16px 18px" }}>
        <SegmentedBar segments={[
          { label: "Good", value: buckets.good, color: STATUS.good },
          { label: "Needs work", value: buckets.ok, color: STATUS.warn },
          { label: "Poor", value: buckets.poor, color: STATUS.poor },
        ]} />
      </div>

      <div style={{ display: "flex", gap: 8, flexWrap: "wrap", alignItems: "center" }}>
        {FILTERS.map((f) => (
          <button key={f.key} type="button" onClick={() => setFilter(f.key)} className={`btn-pill ${filter === f.key ? "active" : ""}`}>
            {f.label}
          </button>
        ))}
        <span style={{ width: 1, height: 20, background: "var(--at-border)" }} />
        {(["all", "page", "post", "service"] as const).map((k) => (
          <button key={k} type="button" onClick={() => setKind(k)} className={`btn-pill ${kind === k ? "active" : ""}`} style={{ textTransform: "capitalize" }}>
            {k === "all" ? "All types" : k + "s"}
          </button>
        ))}
      </div>

      <div style={{ display: "flex", gap: 10, flexWrap: "wrap", alignItems: "center" }}>
        <input value={q} onChange={(e) => setQ(e.target.value)} placeholder="Search title…"
          style={{ flex: 1, minWidth: 200, maxWidth: 320, padding: "8px 12px", borderRadius: 9, fontSize: 13, border: "1px solid var(--at-border-input)", background: "var(--at-input)", color: "var(--at-text)", outline: "none", fontFamily: "inherit" }} />
        <Select
          size="sm"
          ariaLabel="Sort"
          minWidth={190}
          value={sort}
          onChange={(v) => setSort(v as Sort)}
          options={[
            { value: "score", label: "Sort: worst score first" },
            { value: "issues", label: "Sort: most issues" },
            { value: "words", label: "Sort: fewest words" },
            { value: "title", label: "Sort: title A–Z" },
          ]}
        />
        <span style={{ fontSize: 12, color: "var(--at-faint)" }}>{view.length} of {rows.length}</span>
      </div>

      <div style={{ borderRadius: 14, border: "1px solid var(--at-border)", overflow: "hidden" }}>
        <div className="news-tbl-head" style={{ display: "grid", gridTemplateColumns: "1fr 60px 70px 1.2fr 80px", gap: 12, padding: "10px 16px", background: "var(--at-row-even)", borderBottom: "1px solid var(--at-border)", fontSize: 11, fontWeight: 600, textTransform: "uppercase", letterSpacing: "0.06em", color: "var(--at-faint)" }}>
          <span>Title</span><span>Score</span><span>Words</span><span>Recommendations</span><span />
        </div>
        {view.length === 0 && <div style={{ padding: 40, textAlign: "center", color: "var(--at-muted)", fontSize: 13 }}>Nothing matches.</div>}
        {pageItems.map((r) => {
          const color = seoScoreColor(r.good, r.total);
          const recs = recommendations(r);
          return (
            <div key={`${r.kind}-${r.id}`} className="news-tbl-row" style={{ display: "grid", gridTemplateColumns: "1fr 60px 70px 1.2fr 80px", gap: 12, padding: "12px 16px", borderBottom: "1px solid var(--at-border-row)", alignItems: "center" }}>
              <div className="news-col-title" style={{ minWidth: 0 }}>
                <div style={{ fontSize: 13, fontWeight: 500, color: "var(--at-text)", overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>
                  {r.title || <em style={{ color: "var(--at-faint)" }}>Untitled</em>}
                  {r.noindex && <span style={{ marginLeft: 6, fontSize: 10, fontWeight: 600, padding: "1px 6px", borderRadius: 10, background: "var(--at-input)", color: "var(--at-faint)" }}>noindex</span>}
                </div>
                <div style={{ fontSize: 11, color: "var(--at-faint)", overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>{r.kind} · {r.url}</div>
              </div>
              <span className="news-col-cat">
                <span style={{ fontSize: 11, fontWeight: 700, padding: "2px 8px", borderRadius: 20, background: `${color}18`, color, border: `1px solid ${color}35` }}>{r.good}/{r.total}</span>
              </span>
              <span className="news-col-cat" style={{ fontSize: 12, color: r.words < 600 ? "#f59e0b" : "var(--at-muted)" }}>{r.words}</span>
              <span className="news-col-cat" style={{ fontSize: 12, color: "var(--at-muted)" }}>
                {recs.length ? recs.join(" · ") : <span style={{ color: "#4ade80" }}>Looks good</span>}
              </span>
              <div className="news-col-edit" style={{ textAlign: "right" }}>
                <Link href={editHref(r)} className="btn btn-ghost btn-sm">Fix</Link>
              </div>
            </div>
          );
        })}
      </div>
      {control}
    </div>
  );
}
