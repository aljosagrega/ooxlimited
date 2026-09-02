"use client";

/**
 * Hand-rolled inline-SVG charts for the admin dashboard. No chart lib.
 * Design system: --at-* CSS vars. Palette validated against the dataviz skill —
 * status trio (good/warn/poor) always ships with count labels + a legend;
 * the categorical trio (es/ru/pt) uses validated slots 1–3.
 */
import { useState } from "react";
import Link from "next/link";
import { ChevronRight } from "lucide-react";

import { STATUS, CAT3 } from "@/lib/chartPalette";
export { STATUS, CAT3 };

const INK = "var(--at-text)";
const MUTED = "var(--at-muted)";
const FAINT = "var(--at-faint)";
const GRID = "var(--at-glass-hairline)";
const TRACK = "var(--at-track)";
const ACCENT_GRAD = "linear-gradient(90deg, var(--at-accent), #a78bfa)";

/** #rrggbb -> rgba(r,g,b,a) — for translucent tint fills from the status hexes */
function tint(hex: string | undefined, a: number) {
  const h = (hex || "#6366f1").replace("#", "");
  const r = parseInt(h.slice(0, 2), 16) || 99;
  const g = parseInt(h.slice(2, 4), 16) || 102;
  const b = parseInt(h.slice(4, 6), 16) || 241;
  return `rgba(${r}, ${g}, ${b}, ${a})`;
}

const LABEL_CAP = {
  fontSize: 11, fontWeight: 600 as const, textTransform: "uppercase" as const,
  letterSpacing: "0.07em", color: FAINT,
};

export function ChartCard({ title, subtitle, children, right }: {
  title: string; subtitle?: string; children: React.ReactNode; right?: React.ReactNode;
}) {
  return (
    <div className="dash-card" style={{ padding: 20, display: "flex", flexDirection: "column" }}>
      <div style={{ display: "flex", alignItems: "flex-start", justifyContent: "space-between", gap: 12, paddingBottom: 14, marginBottom: 16, borderBottom: `1px solid ${GRID}` }}>
        <div>
          <div style={{ fontSize: 13.5, fontWeight: 600, color: INK, letterSpacing: "-0.01em" }}>{title}</div>
          {subtitle && <div style={{ fontSize: 12, color: MUTED, marginTop: 3 }}>{subtitle}</div>}
        </div>
        {right}
      </div>
      <div style={{ flex: 1, minHeight: 0 }}>{children}</div>
    </div>
  );
}

/* ------------------------------------------------------------ SectionLabel -- */

export function SectionLabel({ children }: { children: React.ReactNode }) {
  return <div style={{ ...LABEL_CAP, marginBottom: 8 }}>{children}</div>;
}

/* ---------------------------------------------------------------- BarChart -- */

export function BarChart({ data, height = 150, valueLabel = "" }: {
  data: { label: string; value: number; sublabel?: string }[];
  height?: number;
  valueLabel?: string;
}) {
  const [hover, setHover] = useState<number | null>(null);
  const max = Math.max(1, ...data.map((d) => d.value));
  const W = 100; // viewBox units, scaled by CSS
  const n = data.length;
  const band = W / n;
  const barW = Math.min(band * 0.58, 5.5);
  const padTop = 6;
  const plotH = height - 20;

  return (
    <div style={{ position: "relative" }}>
      <div style={{ display: "flex", justifyContent: "space-between", fontSize: 10, color: FAINT, marginBottom: 2 }}>
        <span>{max}{valueLabel ? ` ${valueLabel}` : ""}</span>
        <span>0</span>
      </div>
      <svg viewBox={`0 0 ${W} ${height}`} preserveAspectRatio="none" style={{ width: "100%", height, display: "block", overflow: "visible" }}>
        <defs>
          <linearGradient id="dash-bar" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stopColor="#a78bfa" />
            <stop offset="100%" stopColor="var(--at-accent)" />
          </linearGradient>
          <linearGradient id="dash-bar-hi" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stopColor="#c4b5fd" />
            <stop offset="100%" stopColor="#818cf8" />
          </linearGradient>
        </defs>
        {[0, 0.5, 1].map((t) => (
          <line key={t} x1={0} x2={W} y1={padTop + plotH * t} y2={padTop + plotH * t}
            stroke={GRID} strokeWidth={t === 1 ? 0.6 : 0.3} strokeDasharray={t === 1 ? "0" : "1 1.5"} vectorEffect="non-scaling-stroke" />
        ))}
        {data.map((d, i) => {
          const h = (d.value / max) * plotH;
          const x = band * i + (band - barW) / 2;
          const y = padTop + plotH - h;
          const active = hover === i;
          return (
            <g key={i} onMouseEnter={() => setHover(i)} onMouseLeave={() => setHover(null)}>
              <rect x={band * i} y={0} width={band} height={height} fill="transparent" />
              <rect x={x} y={d.value > 0 ? y : padTop + plotH - 0.6} width={barW} height={d.value > 0 ? Math.max(h, 0.6) : 0.6} rx={1.4}
                fill={active ? "url(#dash-bar-hi)" : "url(#dash-bar)"} opacity={hover === null || active ? 1 : 0.45}
                style={{ transition: "opacity 0.12s" }} />
            </g>
          );
        })}
      </svg>
      <div style={{ display: "flex", marginTop: 5 }}>
        {data.map((d, i) => (
          <div key={i} style={{ flex: 1, textAlign: "center", fontSize: 9, fontWeight: hover === i ? 700 : 400, color: hover === i ? MUTED : FAINT, overflow: "hidden", whiteSpace: "nowrap", textOverflow: "ellipsis", transition: "color 0.12s" }}>
            {d.label}
          </div>
        ))}
      </div>
      {hover !== null && (
        <div style={{
          position: "absolute", top: 10, left: `${((hover + 0.5) / n) * 100}%`, transform: "translate(-50%,-100%)",
          background: "var(--at-bg)", border: "1px solid var(--at-border)", borderRadius: 8, padding: "5px 9px",
          fontSize: 11, color: INK, whiteSpace: "nowrap", pointerEvents: "none", zIndex: 5,
          boxShadow: "var(--at-shadow-hover)",
        }}>
          <strong>{data[hover].value}</strong> {valueLabel}
          <span style={{ color: MUTED }}> · {data[hover].sublabel ?? data[hover].label}</span>
        </div>
      )}
    </div>
  );
}

/* ------------------------------------------------------------ SegmentedBar -- */

export function SegmentedBar({ segments }: {
  segments: { label: string; value: number; color: string }[];
}) {
  const total = Math.max(1, segments.reduce((s, x) => s + x.value, 0));
  return (
    <div>
      <div style={{ display: "flex", gap: 8, marginBottom: 12 }}>
        {segments.map((s, i) => {
          const pct = Math.round((s.value / total) * 100);
          return (
            <div key={i} style={{
              flex: 1, borderRadius: 12, padding: "10px 12px",
              border: `1px solid ${tint(s.color, 0.28)}`,
              background: `linear-gradient(155deg, ${tint(s.color, 0.16)}, ${tint(s.color, 0.03)})`,
            }}>
              <div style={{ display: "flex", alignItems: "center", gap: 6, fontSize: 11.5, color: MUTED }}>
                <span style={{ width: 8, height: 8, borderRadius: 3, background: s.color, flexShrink: 0, boxShadow: `0 0 8px ${tint(s.color, 0.6)}` }} />
                {s.label}
              </div>
              <div style={{ display: "flex", alignItems: "baseline", gap: 5, marginTop: 6 }}>
                <span style={{ fontSize: 20, fontWeight: 700, color: INK, lineHeight: 1 }}>{s.value}</span>
                <span style={{ fontSize: 11, color: FAINT }}>{pct}%</span>
              </div>
            </div>
          );
        })}
      </div>
      <div style={{ display: "flex", height: 8, borderRadius: 999, overflow: "hidden", background: TRACK, gap: 2 }}>
        {segments.map((s, i) => s.value > 0 && (
          <div key={i} className="dash-fill" title={`${s.label}: ${s.value}`}
            style={{ width: `${(s.value / total) * 100}%`, background: `linear-gradient(180deg, ${tint(s.color, 1)}, ${tint(s.color, 0.8)})`, minWidth: 2 }} />
        ))}
      </div>
    </div>
  );
}

/* --------------------------------------------------------------- HBars ------ */

export function HBars({ data, valueLabel = "" }: {
  data: { label: string; value: number }[];
  valueLabel?: string;
}) {
  const max = Math.max(1, ...data.map((d) => d.value));
  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 9 }}>
      {data.map((d, i) => (
        <div key={i} style={{ display: "flex", alignItems: "center", gap: 12 }}>
          <div style={{ width: 84, flexShrink: 0, fontSize: 12, color: MUTED, textAlign: "right", overflow: "hidden", whiteSpace: "nowrap", textOverflow: "ellipsis" }}>
            {d.label}
          </div>
          <div style={{ flex: 1, height: 18, background: TRACK, borderRadius: 6, overflow: "hidden" }}>
            <div className="dash-fill" style={{ width: `${Math.max((d.value / max) * 100, d.value > 0 ? 3 : 0)}%`, height: "100%", background: ACCENT_GRAD, borderRadius: 6, transition: "width 0.3s ease" }} />
          </div>
          <div style={{ width: 42, flexShrink: 0, fontSize: 12.5, fontWeight: 600, color: INK, textAlign: "right" }}>
            {d.value}<span style={{ color: FAINT, fontWeight: 400 }}>{valueLabel}</span>
          </div>
        </div>
      ))}
    </div>
  );
}

/* ------------------------------------------------------------ CoverageMeter - */

export function CoverageMeter({ items }: {
  items: { label: string; pct: number; color: string }[];
}) {
  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
      {items.map((it, i) => (
        <div key={i}>
          <div style={{ display: "flex", justifyContent: "space-between", alignItems: "baseline", fontSize: 12, marginBottom: 6 }}>
            <span style={{ color: MUTED }}>{it.label}</span>
            <span style={{ color: INK, fontWeight: 700, fontSize: 13 }}>{Math.round(it.pct)}%</span>
          </div>
          <div style={{ height: 10, background: TRACK, borderRadius: 999, overflow: "hidden" }}>
            <div className="dash-fill" style={{ width: `${Math.max(0, Math.min(100, it.pct))}%`, height: "100%", background: `linear-gradient(90deg, ${it.color}, ${tint(it.color, 0.7)})`, borderRadius: 999, transition: "width 0.3s ease" }} />
          </div>
        </div>
      ))}
    </div>
  );
}

/* ---------------------------------------------------------------- StatList -- */

export function StatList({ rows }: {
  rows: { key: string; title: string; href?: string; value: React.ReactNode; tone?: "danger" | "muted" }[];
}) {
  return (
    <div style={{ display: "flex", flexDirection: "column" }}>
      {rows.map((r, i) => {
        const inner = (
          <>
            <span style={{ overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap", fontSize: 12.5, color: MUTED }}>{r.title}</span>
            <span style={{
              flexShrink: 0, fontSize: 12, fontWeight: 600,
              color: r.tone === "danger" ? STATUS.poor : INK,
              background: r.tone === "danger" ? "rgba(248,113,113,0.12)" : "transparent",
              borderRadius: 6, padding: r.tone === "danger" ? "2px 7px" : 0,
            }}>{r.value}</span>
          </>
        );
        return r.href ? (
          <Link key={r.key} href={r.href} className="dash-row" style={i > 0 ? { borderTop: `1px solid ${GRID}` } : undefined}>{inner}</Link>
        ) : (
          <div key={r.key} className="dash-row" style={i > 0 ? { borderTop: `1px solid ${GRID}` } : undefined}>{inner}</div>
        );
      })}
    </div>
  );
}

/* ------------------------------------------------------------- CountCard ---- */

export function CountCard({ href, label, total, sub }: {
  href: string; label: string; total: number; sub?: string;
}) {
  return (
    <Link href={href} className="dash-card dash-link-card" style={{ display: "block", padding: "15px 17px", textDecoration: "none" }}>
      <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: 10 }}>
        <span style={LABEL_CAP}>{label}</span>
        <ChevronRight size={13} className="dash-arrow" color={FAINT} />
      </div>
      <div style={{ fontSize: 25, fontWeight: 700, color: INK, lineHeight: 1, letterSpacing: "-0.02em" }}>{total}</div>
      {sub && <div style={{ fontSize: 12, color: STATUS.good, marginTop: 6, fontWeight: 500 }}>{sub}</div>}
    </Link>
  );
}
