"use client";

import { useMemo, useState } from "react";
import { toast } from "sonner";
import { Save, ExternalLink, Search } from "lucide-react";
import type { PagemapEntry } from "@/lib/fieldMap";
import { Field, FocusInput, FocusTextarea } from "./fields/FormPrimitives";
import ImageUploadField from "./fields/ImageUploadField";
import RichTextEditor from "./RichTextEditor";

/** SEO overrides ride in the same edits map under reserved keys. */
const SEO_KEYS = { title: "__seoTitle", desc: "__seoDesc", noindex: "__seoNoindex" } as const;

export default function PageEditor({
  routeKey,
  routePath,
  title,
  pagemap,
  edits: initialEdits,
  hideSeo = false,
  hideHeader = false,
}: {
  routeKey: string;
  routePath: string;
  title: string;
  pagemap: PagemapEntry[];
  edits: Record<string, string>;
  /** hide the "Search appearance" tab — for hosts that own SEO themselves (Services) */
  hideSeo?: boolean;
  /** hide the title / "View page" row — for when it sits inside another editor */
  hideHeader?: boolean;
}) {
  const contentEdits0 = Object.fromEntries(
    Object.entries(initialEdits).filter(([k]) => !k.startsWith("__seo")),
  );
  const [edits, setEdits] = useState<Record<string, string>>(contentEdits0);
  const [meta, setMeta] = useState({
    metaTitle: initialEdits[SEO_KEYS.title] ?? "",
    metaDescription: initialEdits[SEO_KEYS.desc] ?? "",
    noindex: initialEdits[SEO_KEYS.noindex] === "1",
  });
  const [saving, setSaving] = useState(false);
  const [q, setQ] = useState("");
  const [tab, setTab] = useState<"content" | "seo">("content");

  const groups = useMemo(() => {
    const g = new Map<string, PagemapEntry[]>();
    for (const e of pagemap) {
      if (q && !`${e.label} ${e.value}`.toLowerCase().includes(q.toLowerCase())) continue;
      (g.get(e.group) ?? g.set(e.group, []).get(e.group)!).push(e);
    }
    return [...g.entries()];
  }, [pagemap, q]);

  const val = (e: PagemapEntry) => edits[e.id] ?? e.value;
  const setVal = (id: string, v: string, original: string) =>
    setEdits((prev) => {
      const next = { ...prev };
      if (v === original) delete next[id];
      else next[id] = v;
      return next;
    });

  const dirty =
    JSON.stringify(edits) !== JSON.stringify(contentEdits0) ||
    meta.metaTitle !== (initialEdits[SEO_KEYS.title] ?? "") ||
    meta.metaDescription !== (initialEdits[SEO_KEYS.desc] ?? "") ||
    meta.noindex !== (initialEdits[SEO_KEYS.noindex] === "1");

  async function save(e: React.FormEvent) {
    e.preventDefault();
    setSaving(true);
    try {
      const res = await fetch(`/api/admin/page-edits/${encodeURIComponent(routeKey)}/`, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          edits,
          seo: {
            metaTitle: meta.metaTitle,
            metaDescription: meta.metaDescription,
            noindex: meta.noindex,
          },
          routePath,
        }),
      });
      if (res.ok) toast.success("Page saved — live now");
      else toast.error((await res.json()).error ?? "Save failed");
    } catch {
      toast.error("Connection error");
    } finally {
      setSaving(false);
    }
  }

  const changedCount = Object.keys(edits).length;

  return (
    <form onSubmit={save} className={hideHeader ? "" : "admin-content-pad"} style={{ maxWidth: 780, display: "flex", flexDirection: "column", gap: 18 }}>
      {!hideHeader && (
        <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", flexWrap: "wrap", gap: 12 }}>
          <div>
            <h1 style={{ fontSize: 20, fontWeight: 600, color: "var(--at-text)", margin: 0 }}>
              {title}
              <span style={{ color: "var(--at-faint)", fontWeight: 400, fontSize: 13 }}> — {routePath}</span>
            </h1>
            <p style={{ fontSize: 12.5, color: "var(--at-muted)", marginTop: 4 }}>
              {pagemap.length} editable text &amp; image fields. Layout is fixed; only content changes.
            </p>
          </div>
          <a href={routePath} target="_blank" rel="noreferrer" className="btn btn-secondary btn-sm">
            <ExternalLink size={13} /> View page
          </a>
        </div>
      )}

      {!hideSeo && (
        <div className="at-tabs">
          <button type="button" className={`at-tab${tab === "content" ? " active" : ""}`} onClick={() => setTab("content")}>Content</button>
          <button type="button" className={`at-tab${tab === "seo" ? " active" : ""}`} onClick={() => setTab("seo")}>Search appearance</button>
        </div>
      )}

      {!hideSeo && tab === "seo" ? (
        <div className="at-card" style={{ display: "flex", flexDirection: "column", gap: 16 }}>
          <Field label="Meta title"><FocusInput value={meta.metaTitle} onChange={(v) => setMeta({ ...meta, metaTitle: v })} placeholder={title} /></Field>
          <Field label="Meta description"><FocusTextarea value={meta.metaDescription} onChange={(v) => setMeta({ ...meta, metaDescription: v })} rows={2} /></Field>
          <label style={{ display: "flex", alignItems: "center", gap: 8, fontSize: 13, color: "var(--at-text)" }}>
            <input type="checkbox" checked={meta.noindex} onChange={(e) => setMeta({ ...meta, noindex: e.target.checked })} />
            Hide from search engines (noindex)
          </label>
        </div>
      ) : (
        <>
          <div style={{ position: "relative" }}>
            <Search size={14} style={{ position: "absolute", left: 12, top: 12, color: "var(--at-faint)" }} />
            <input
              value={q}
              onChange={(e) => setQ(e.target.value)}
              placeholder="Filter fields…"
              style={{ width: "100%", padding: "9px 12px 9px 34px", borderRadius: 10, fontSize: 13, border: "1px solid var(--at-border-input)", background: "var(--at-input)", color: "var(--at-text)", outline: "none", fontFamily: "inherit" }}
            />
          </div>

          {groups.length === 0 && <p style={{ color: "var(--at-muted)", fontSize: 13 }}>No fields match.</p>}

          {groups.map(([group, items]) => (
            <div key={group} className="at-card" style={{ display: "flex", flexDirection: "column", gap: 14 }}>
              <div style={{ fontSize: 11, fontWeight: 600, textTransform: "uppercase", letterSpacing: "0.08em", color: "var(--at-faint)" }}>{group}</div>
              {items.map((e) => (
                <Field key={e.id} label={e.label}>
                  {e.kind === "image" ? (
                    <ImageUploadField value={val(e)} onChange={(v) => setVal(e.id, v, e.value)} />
                  ) : e.kind === "html" ? (
                    <RichTextEditor value={val(e)} onChange={(v) => setVal(e.id, v, e.value)} />
                  ) : e.value.length > 90 ? (
                    <FocusTextarea value={val(e)} onChange={(v) => setVal(e.id, v, e.value)} rows={3} />
                  ) : (
                    <FocusInput value={val(e)} onChange={(v) => setVal(e.id, v, e.value)} />
                  )}
                  {edits[e.id] != null && (
                    <button type="button" onClick={() => setVal(e.id, e.value, e.value)} style={{ marginTop: 4, fontSize: 11, color: "var(--at-accent)", background: "none", border: "none", cursor: "pointer" }}>
                      Reset to original
                    </button>
                  )}
                </Field>
              ))}
            </div>
          ))}
        </>
      )}

      <div style={{ display: "flex", alignItems: "center", gap: 12, borderTop: "1px solid var(--at-border)", paddingTop: 16, position: "sticky", bottom: 0, background: "var(--at-bg)", paddingBottom: 12 }}>
        <button type="submit" disabled={saving || !dirty} className="btn btn-primary">
          <Save size={13} /> {saving ? "Saving…" : "Save page"}
        </button>
        {dirty && !saving && <span style={{ fontSize: 12, color: "var(--at-muted)" }}>{changedCount} field(s) changed</span>}
      </div>
    </form>
  );
}
