"use client";

import { useState } from "react";
import {
  computeSeoItems, computeSeoScore, seoScoreColor, wordCount, stripHtmlText,
  type SeoScoreInput, type SeoStatus,
} from "@/lib/seoScore";

export { wordCount, stripHtmlText, computeSeoScore, seoScoreColor };
export type { SeoScoreInput };

export function SeoScoreBadge(props: SeoScoreInput) {
  const { good, total } = computeSeoScore(props);
  const color = seoScoreColor(good, total);
  return (
    <span style={{ fontSize: 11, fontWeight: 700, padding: "2px 8px", borderRadius: 20, background: `${color}18`, color, border: `1px solid ${color}35` }}>
      {good}/{total}
    </span>
  );
}

export function SeoChecklist(props: SeoScoreInput) {
  const [open, setOpen] = useState(true);
  const items = computeSeoItems(props);
  const good = items.filter((i) => i.status === "good").length;
  const sc = seoScoreColor(good, items.length);
  const C: Record<SeoStatus, string> = { good: "#10b981", warn: "#f59e0b", bad: "#f87171", empty: "#9ca3af" };
  const I: Record<SeoStatus, string> = { good: "✓", warn: "!", bad: "✗", empty: "·" };

  return (
    <div style={{ borderRadius: 12, border: "1px solid var(--at-border)", background: "var(--at-row-even)", overflow: "hidden" }}>
      <button type="button" onClick={() => setOpen((o) => !o)}
        style={{ display: "flex", alignItems: "center", justifyContent: "space-between", width: "100%", padding: "10px 14px", background: "transparent", border: "none", cursor: "pointer", fontFamily: "inherit" }}>
        <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
          <span style={{ fontSize: 11, fontWeight: 600, textTransform: "uppercase", letterSpacing: "0.08em", color: "var(--at-faint)" }}>SEO checklist</span>
          <span style={{ fontSize: 11, fontWeight: 700, padding: "1px 7px", borderRadius: 20, background: `${sc}18`, color: sc, border: `1px solid ${sc}35` }}>
            {good}/{items.length}
          </span>
        </div>
        <svg width="12" height="12" viewBox="0 0 12 12" fill="none" style={{ color: "var(--at-faint)", transform: open ? "rotate(180deg)" : "none", transition: "transform 0.15s" }}>
          <path d="M2 4L6 8L10 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
      </button>
      {open && (
        <div style={{ borderTop: "1px solid var(--at-border)", padding: "10px 14px", display: "flex", flexDirection: "column", gap: 8 }}>
          {items.map((item) => (
            <div key={item.label} style={{ display: "flex", alignItems: "baseline", gap: 8 }}>
              <span style={{ width: 14, textAlign: "center", flexShrink: 0, fontSize: 13, fontWeight: 700, color: C[item.status] }}>{I[item.status]}</span>
              <span style={{ fontSize: 13, fontWeight: 500, color: "var(--at-text)", flexShrink: 0 }}>{item.label}</span>
              {item.detail && <span style={{ fontSize: 12, color: C[item.status] }}>{item.detail}</span>}
              <span style={{ fontSize: 12, color: "var(--at-muted)" }}>— {item.hint}</span>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

/* ---- Google SERP snippet preview -------------------------------------------- */

export function SerpPreview({ url, title, description }: { url: string; title: string; description: string }) {
  const tShown = title.length > 60 ? title.slice(0, 59).trimEnd() + "…" : title || "Untitled page";
  const dShown = description.length > 160 ? description.slice(0, 159).trimEnd() + "…" : description;
  return (
    <div style={{ borderRadius: 12, border: "1px solid var(--at-border)", padding: 16, background: "var(--at-card)" }}>
      <div style={{ fontSize: 11, fontWeight: 600, textTransform: "uppercase", letterSpacing: "0.08em", color: "var(--at-faint)", marginBottom: 10 }}>
        Search result preview
      </div>
      <div style={{ fontSize: 12, color: "var(--at-muted)", marginBottom: 2 }}>{url}</div>
      <div style={{ fontSize: 18, color: "#8ab4f8", lineHeight: 1.3, marginBottom: 3 }}>{tShown}</div>
      <div style={{ fontSize: 13, color: "var(--at-prose-p)", lineHeight: 1.5 }}>
        {dShown || <span style={{ color: "var(--at-faint)", fontStyle: "italic" }}>No description — Google will pull text from the page.</span>}
      </div>
    </div>
  );
}
