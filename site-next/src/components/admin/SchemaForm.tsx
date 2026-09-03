"use client";

import { useCallback, useEffect, useMemo, useState } from "react";
import { useRouter } from "next/navigation";
import dynamic from "next/dynamic";
import { toast } from "sonner";
import { Eye, EyeOff } from "lucide-react";
import { Field, FocusInput, FocusTextarea, inputStyle } from "./fields/FormPrimitives";
import Select from "./fields/Select";
import ImageUploadField from "./fields/ImageUploadField";
import ColorField from "./fields/ColorField";
import StringListField from "./fields/StringListField";
import { SeoChecklist, SerpPreview, wordCount } from "./fields/SeoPanel";
import { youtubeId, mediaUrl } from "@/lib/media";
import PreviewPanel from "./PreviewPanel";
import { EDIT_LOCALES, type CollectionSchema, type FieldDef } from "@/lib/adminSchema";

const RichTextEditor = dynamic(() => import("./RichTextEditor"), {
  ssr: false,
  loading: () => (
    <div style={{ borderRadius: 12, height: 192, display: "flex", alignItems: "center", justifyContent: "center", background: "var(--at-row-even)", border: "1px solid var(--at-border-input-alt)", color: "var(--at-muted)", fontSize: 14 }}>
      Loading editor…
    </div>
  ),
});

type Row = Record<string, unknown>;
type RefOption = { value: number; label: string };

function slugify(s: string) {
  return s.toLowerCase().trim().replace(/[^a-z0-9]+/g, "-").replace(/(^-|-$)/g, "");
}

/** ISO string ⇄ value for <input type="datetime-local"> (browser-local time). */
function isoToLocalInput(iso: string): string {
  if (!iso) return "";
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return "";
  const pad = (n: number) => String(n).padStart(2, "0");
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}
function localInputToIso(v: string): string {
  if (!v) return "";
  const d = new Date(v);
  return Number.isNaN(d.getTime()) ? "" : d.toISOString();
}

export default function SchemaForm({ schema, record, locales, refOptions = {}, embedded = false }: {
  schema: CollectionSchema;
  record?: Row;
  /** editable locale codes (from languages.json); falls back to EDIT_LOCALES */
  locales?: string[];
  /** option lists for `ref` / `refList` fields, keyed by field key */
  refOptions?: Record<string, RefOption[]>;
  /** rendered inside another editor — drop the page heading + outer padding */
  embedded?: boolean;
}) {
  const router = useRouter();
  const isEdit = !!record;
  const editLocales = locales && locales.length ? locales : (EDIT_LOCALES as readonly string[]);

  const [data, setData] = useState<Row>(() => ({ ...(record ?? {}) }));
  const [locale, setLocale] = useState<string>("en");
  // Keep the slug in sync with the title until the user edits the slug by hand
  // (or on an existing record, where the slug is already meaningful).
  const [slugTouched, setSlugTouched] = useState(isEdit);
  const [showJson, setShowJson] = useState(false);
  const [jsonText, setJsonText] = useState(() => JSON.stringify(record ?? {}, null, 2));
  const [jsonError, setJsonError] = useState("");
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");

  const hasPreview = !!schema.preview;
  const [showPreview, setShowPreview] = useState(false);
  const [panelClosing, setPanelClosing] = useState(false);

  // Open the live preview by default on wide screens. Done in an effect (not a
  // lazy initializer) so server and first client render agree on `false`, then
  // the panel slides in once we know the viewport is wide enough for both.
  /* eslint-disable react-hooks/set-state-in-effect, react-hooks/exhaustive-deps */
  useEffect(() => {
    if (hasPreview && !showJson && window.innerWidth >= 1100) setShowPreview(true);
  }, []);
  /* eslint-enable react-hooks/set-state-in-effect, react-hooks/exhaustive-deps */

  const closePreview = useCallback(() => {
    setPanelClosing(true);
    setTimeout(() => { setShowPreview(false); setPanelClosing(false); }, 260);
  }, []);

  // Push the editor left while the preview panel is open
  useEffect(() => {
    const main = document.querySelector(".admin-shell main") as HTMLElement | null;
    if (!main) return;
    const EASE = "padding-right 0.28s cubic-bezier(0.4,0,0.2,1)";
    if (showPreview && !panelClosing) {
      main.style.transition = EASE;
      main.style.paddingRight = `${Math.min(window.innerWidth * 0.46, 620) + 48}px`;
      return;
    }
    main.style.transition = EASE;
    main.style.paddingRight = "";
    const t = setTimeout(() => { main.style.transition = ""; }, 320);
    return () => clearTimeout(t);
  }, [showPreview, panelClosing]);

  useEffect(() => () => {
    const main = document.querySelector(".admin-shell main") as HTMLElement | null;
    if (main) { main.style.paddingRight = ""; main.style.transition = ""; }
  }, []);

  const translations = useMemo(
    () => (data.translations ?? {}) as Record<string, Record<string, string>>,
    [data.translations],
  );

  function setBase(key: string, value: unknown) {
    setData((d) => {
      const next = { ...d, [key]: value };
      if (key === schema.titleField && !slugTouched) {
        const hasSlug = schema.fields.some((f) => f.key === "slug");
        if (hasSlug && typeof value === "string") next.slug = slugify(value);
      }
      return next;
    });
  }

  function setTranslated(key: string, value: string) {
    setData((d) => {
      const t = { ...((d.translations ?? {}) as Record<string, Record<string, string>>) };
      t[locale] = { ...(t[locale] ?? {}) };
      if (value === "") delete t[locale][key];
      else t[locale][key] = value;
      if (Object.keys(t[locale]).length === 0) delete t[locale];
      return { ...d, translations: t };
    });
  }

  function fieldValue(f: FieldDef): string {
    if (locale !== "en" && f.i18n) return translations[locale]?.[f.key] ?? "";
    const v = data[f.key];
    return v == null ? "" : String(v);
  }

  function onFieldChange(f: FieldDef, raw: string | boolean | string[]) {
    if (locale !== "en" && f.i18n) {
      setTranslated(f.key, String(raw));
      return;
    }
    // Manual edit of the base slug stops the title→slug sync; emptying it resumes.
    if (f.key === "slug") setSlugTouched(String(raw).trim() !== "");
    if (f.type === "number") setBase(f.key, raw === "" ? null : Number(raw));
    else if (f.type === "boolean") setBase(f.key, !!raw);
    else if (f.type === "stringList") setBase(f.key, raw);
    else setBase(f.key, raw);
  }

  const enPlaceholder = (f: FieldDef) =>
    locale !== "en" && f.i18n ? (data[f.key] ? `EN: ${String(data[f.key]).slice(0, 60)}` : f.placeholder) : f.placeholder;

  const visibleFields = useMemo(
    () => schema.fields.filter((f) => f.type !== "json" && f.group !== "seo" && (locale === "en" || f.i18n)),
    [schema.fields, locale],
  );

  const seoGroupFields = useMemo(
    () => schema.fields.filter((f) => f.group === "seo" && (locale === "en" || f.i18n)),
    [schema.fields, locale],
  );

  // Locale-aware value for the live preview: translated value if present, else English
  const localized = useCallback((key?: string): string => {
    if (!key) return "";
    if (locale !== "en" && translations[locale]?.[key]) return translations[locale][key];
    const v = data[key];
    return v == null ? "" : String(v);
  }, [locale, translations, data]);

  const previewUrl = useMemo(() => {
    const s = schema.seo;
    const slug = String(data.slug ?? "");
    if (typeof data.path === "string" && data.path) return data.path;
    if (s?.urlPattern && slug) return s.urlPattern.replace("{slug}", slug);
    if (slug) return `/${slug}/`;
    return "";
  }, [schema, data]);

  const previewData = useMemo(() => {
    const s = schema.seo;
    if (!s) return null;
    const imgField = s.imageField ? schema.fields.find((f) => f.key === s.imageField) : undefined;
    const rawImage = localized(s.imageField);
    void imgField;
    const image = rawImage; // ooxlimited image values are already root-absolute / full URLs
    return {
      title: localized(s.titleField),
      description: localized(s.descriptionField) || localized(s.descriptionFallbackField),
      image,
      bodyHtml: localized(s.bodyField),
      author: typeof data.author === "string" ? data.author : undefined,
    };
  }, [schema, localized, data.author]);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError("");

    let payload: Row;
    if (showJson) {
      try {
        payload = JSON.parse(jsonText);
      } catch {
        setJsonError("Invalid JSON");
        return;
      }
    } else {
      payload = data;
    }

    setSaving(true);
    try {
      const url = isEdit ? `/api/admin/${schema.slug}/${record!.id}` : `/api/admin/${schema.slug}`;
      const res = await fetch(url, {
        method: isEdit ? "PUT" : "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      if (res.ok) {
        toast.success(isEdit ? "Saved" : "Created");
        router.push(`/admin/${schema.slug}`);
        router.refresh();
      } else {
        const d = await res.json().catch(() => ({}));
        const msg = res.status === 401 ? "Session expired — log in again" : (d.error ?? "Failed to save");
        setError(msg);
        toast.error(msg);
      }
    } catch {
      setError("Connection error");
      toast.error("Connection error");
    } finally {
      setSaving(false);
    }
  }

  const heading = isEdit
    ? `Edit ${schema.singular}`
    : `New ${schema.singular}`;

  function renderField(f: FieldDef) {
    const wide = f.full || f.type === "html" || f.type === "textarea" || f.type === "stringList" || f.type === "image" || f.type === "imageObject" || f.type === "refList";
    return (
      <div key={f.key} style={{ gridColumn: wide ? "1 / -1" : undefined }}>
        <Field label={f.i18n && locale !== "en" ? `${f.label} (${locale.toUpperCase()})` : f.label}>
          {f.type === "html" ? (
            <RichTextEditor value={fieldValue(f)} onChange={(html) => onFieldChange(f, html)} />
          ) : f.type === "textarea" ? (
            <FocusTextarea value={fieldValue(f)} onChange={(v) => onFieldChange(f, v)} placeholder={enPlaceholder(f)} rows={f.rows ?? 3} />
          ) : f.type === "boolean" ? (
            <label style={{ display: "flex", alignItems: "center", gap: 8, height: 44, cursor: "pointer" }}>
              <input type="checkbox" checked={!!data[f.key]} onChange={(e) => onFieldChange(f, e.target.checked)} style={{ width: 16, height: 16 }} />
              <span style={{ fontSize: 13, color: "var(--at-text)" }}>{data[f.key] ? "Yes" : "No"}</span>
            </label>
          ) : f.type === "date" ? (
            <input
              type="datetime-local"
              value={isoToLocalInput(String(data[f.key] ?? ""))}
              onChange={(e) => setBase(f.key, localInputToIso(e.target.value))}
              style={inputStyle()}
            />
          ) : f.type === "ref" ? (
            <Select
              ariaLabel={f.label}
              value={data[f.key] == null || data[f.key] === "" ? "" : String(data[f.key])}
              onChange={(v) => setBase(f.key, v === "" ? null : Number(v))}
              options={[{ value: "", label: "—" }, ...(refOptions[f.key] ?? []).map((o) => ({ value: String(o.value), label: o.label }))]}
            />
          ) : f.type === "refList" ? (
            (() => {
              const selected = Array.isArray(data[f.key]) ? (data[f.key] as unknown[]).map(Number) : [];
              const toggle = (v: number) => setBase(
                f.key,
                selected.includes(v) ? selected.filter((x) => x !== v) : [...selected, v],
              );
              return (
                <div style={{ display: "flex", flexWrap: "wrap", gap: 6 }}>
                  {(refOptions[f.key] ?? []).map((o) => (
                    <button
                      key={o.value}
                      type="button"
                      onClick={() => toggle(o.value)}
                      className={`btn-pill ${selected.includes(o.value) ? "active" : ""}`}
                    >
                      {o.label}
                    </button>
                  ))}
                  {!(refOptions[f.key] ?? []).length && (
                    <span style={{ fontSize: 12, color: "var(--at-faint)" }}>No options available</span>
                  )}
                </div>
              );
            })()
          ) : f.type === "image" ? (
            <ImageUploadField
              value={fieldValue(f)}
              onChange={(v) => onFieldChange(f, v)}
              folder={f.folder}
              fallbackSrc={(() => {
                if (f.youtubeThumbFrom) {
                  const id = youtubeId(String(data[f.youtubeThumbFrom] ?? ""));
                  return id ? `https://i.ytimg.com/vi/${id}/hqdefault.jpg` : undefined;
                }
                if (f.fallbackFrom) {
                  const sib = schema.fields.find((x) => x.key === f.fallbackFrom);
                  const raw = String(data[f.fallbackFrom] ?? "").trim();
                  return raw ? mediaUrl(sib?.folder ?? "", raw) : undefined;
                }
                return undefined;
              })()}
            />
          ) : f.type === "imageObject" ? (
            (() => {
              const obj = (data[f.key] && typeof data[f.key] === "object"
                ? data[f.key]
                : { url: "", alt: "" }) as { url?: string; alt?: string };
              const setObj = (next: { url?: string; alt?: string }) =>
                setBase(f.key, next.url ? next : null);
              return (
                <div style={{ display: "flex", flexDirection: "column", gap: 8 }}>
                  <ImageUploadField
                    value={obj.url ?? ""}
                    onChange={(v) => setObj({ url: v, alt: obj.alt ?? "" })}
                  />
                  <FocusInput
                    value={obj.alt ?? ""}
                    onChange={(v) => setObj({ url: obj.url ?? "", alt: v })}
                    placeholder="Alt text (describe the image)"
                  />
                </div>
              );
            })()
          ) : f.type === "color" ? (
            <ColorField value={fieldValue(f)} onChange={(v) => onFieldChange(f, v)} />
          ) : f.type === "stringList" ? (
            <StringListField
              value={Array.isArray(data[f.key]) ? (data[f.key] as unknown[]).map(String) : []}
              onChange={(v) => onFieldChange(f, v)}
              placeholder={f.placeholder}
            />
          ) : f.type === "select" ? (
            <Select
              ariaLabel={f.label}
              value={fieldValue(f)}
              onChange={(v) => onFieldChange(f, v)}
              options={[{ value: "", label: "—" }, ...(f.options ?? []).map((o) => ({ value: String(o.value), label: o.label }))]}
            />
          ) : (
            <FocusInput
              value={fieldValue(f)}
              onChange={(v) => onFieldChange(f, v)}
              placeholder={enPlaceholder(f)}
              type={f.type === "number" ? "number" : "text"}
            />
          )}
          {f.help && <p style={{ margin: "5px 0 0", fontSize: 11, color: "var(--at-faint)" }}>{f.help}</p>}
        </Field>
      </div>
    );
  }

  return (
    <>
    {hasPreview && showPreview && previewData && (
      <PreviewPanel
        data={previewData}
        url={previewUrl}
        label={schema.singular}
        onClose={closePreview}
        closing={panelClosing}
      />
    )}
    <form onSubmit={handleSubmit} style={{ display: "flex", flexDirection: "column", gap: 20, maxWidth: 860 }}>
      <div style={{ display: "flex", alignItems: "center", justifyContent: embedded ? "flex-end" : "space-between", flexWrap: "wrap", gap: 12 }}>
        {!embedded && (
          <h1 style={{ fontSize: 20, fontWeight: 600, color: "var(--at-text)", margin: 0 }}>
            {heading}
            {isEdit && data[schema.titleField] ? <span style={{ color: "var(--at-faint)", fontWeight: 400 }}> — {String(data[schema.titleField])}</span> : null}
          </h1>
        )}
        <button type="button" onClick={() => { setShowJson((s) => !s); setJsonText(JSON.stringify(data, null, 2)); setJsonError(""); if (showPreview) closePreview(); }} className="btn btn-secondary btn-sm">
          {showJson ? "Form view" : "Edit raw JSON"}
        </button>
      </div>

      {!showJson && schema.fields.some((f) => f.i18n) && (
        <div style={{ display: "flex", gap: 6, flexWrap: "wrap" }}>
          {editLocales.map((l) => (
            <button
              key={l}
              type="button"
              onClick={() => setLocale(l)}
              className={`btn-pill ${locale === l ? "active" : ""}`}
              style={{ textTransform: "uppercase" }}
            >
              {l}
            </button>
          ))}
          {locale !== "en" && (
            <span style={{ fontSize: 12, color: "var(--at-faint)", alignSelf: "center", marginLeft: 4 }}>
              Only translated fields shown — blank falls back to English
            </span>
          )}
        </div>
      )}

      {showJson ? (
        <div>
          <textarea
            value={jsonText}
            onChange={(e) => { setJsonText(e.target.value); setJsonError(""); }}
            spellCheck={false}
            style={{
              width: "100%", minHeight: 460, padding: "12px 14px", borderRadius: 12,
              border: `1px solid ${jsonError ? "#f87171" : "var(--at-border-input)"}`, background: "var(--at-input)",
              color: "var(--at-prose-p)", fontSize: 12, fontFamily: "monospace", lineHeight: 1.6,
              resize: "vertical", outline: "none", boxSizing: "border-box",
            }}
          />
          {jsonError && <p style={{ margin: "6px 0 0", fontSize: 12, color: "#f87171" }}>{jsonError}</p>}
        </div>
      ) : (
        <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 16 }}>
          {visibleFields.map((f) => renderField(f))}
        </div>
      )}

      {!showJson && schema.seo && (() => {
        const s = schema.seo!;
        const str = (k?: string) => (k && data[k] != null ? String(data[k]) : "");
        const title = str(s.titleField);
        const metaTitle = str("metaTitle") || title;
        const desc = str(s.descriptionField) || str(s.descriptionFallbackField);
        const path =
          str("path") ||
          (s.urlPattern
            ? s.urlPattern.replace("{slug}", str("slug"))
            : `/${str("slug")}/`);
        return (
          <>
            {seoGroupFields.length > 0 && (
              <div style={{ borderRadius: 12, border: "1px solid var(--at-border)", padding: 16 }}>
                <div style={{ fontSize: 11, fontWeight: 600, textTransform: "uppercase", letterSpacing: "0.08em", color: "var(--at-faint)", marginBottom: 14 }}>
                  Search appearance
                </div>
                <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 16 }}>
                  {seoGroupFields.map((f) => renderField(f))}
                </div>
              </div>
            )}
            <SerpPreview
              url={`ooxlimited.com${path}`}
              title={metaTitle}
              description={desc}
            />
            <SeoChecklist
              effectiveTitle={metaTitle}
              effectiveDescription={desc}
              slug={str(s.slugField)}
              bodyWordCount={wordCount(str(s.bodyField))}
              hasImage={s.imageField ? !!str(s.imageField) : undefined}
              imageLabel={s.imageLabel}
            />
          </>
        );
      })()}

      {error && (
        <div style={{ borderRadius: 10, padding: "10px 14px", fontSize: 14, background: "rgba(239,68,68,0.08)", border: "1px solid rgba(239,68,68,0.2)", color: "#f87171" }}>
          {error}
        </div>
      )}

      <div style={{ display: "flex", gap: 10, flexWrap: "wrap" }}>
        <button type="submit" disabled={saving} className="btn btn-primary">
          {saving ? "Saving…" : isEdit ? "Save changes" : `Create ${schema.singular}`}
        </button>
        <button type="button" onClick={() => router.push(`/admin/${schema.slug}`)} className="btn btn-secondary">
          Cancel
        </button>
        {hasPreview && !showJson && (
          <button
            type="button"
            onClick={() => (showPreview ? closePreview() : setShowPreview(true))}
            className={`btn btn-pill${showPreview ? " active" : ""}`}
          >
            {showPreview ? <EyeOff size={14} /> : <Eye size={14} />}
            {showPreview ? "Close preview" : "Live preview"}
          </button>
        )}
      </div>
    </form>
    </>
  );
}
