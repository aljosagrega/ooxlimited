import AdminShell from "./AdminShell";

/**
 * Route-level loading state for /admin. Every admin page is dynamic (session
 * cookie) and some do real per-request work (SEO scoring, the 5.7 MB wikibrain
 * parse), so without this a link click freezes the current page until the
 * response lands. Rendered inside <AdminShell> so the sidebar stays put and only
 * the content area shimmers — no layout shift when the real page swaps in.
 *
 * Ported from paynura-front's AdminPageSkeleton: a wave sweep across
 * color-mix-derived bars, staggered per row, with a reduced-motion pulse
 * fallback.
 */

const CSS = `
  @keyframes sk-wave { 0% { transform: translateX(-100%); } 100% { transform: translateX(300%); } }
  @keyframes sk-pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.45; } }

  .skb {
    position: relative;
    overflow: hidden;
    border-radius: 5px;
    flex-shrink: 0;
    background: color-mix(in srgb, var(--at-text) 9%, transparent);
  }
  @media (prefers-reduced-motion: no-preference) {
    .skb::after {
      content: '';
      position: absolute;
      inset: 0;
      transform: translateX(-100%);
      background: linear-gradient(90deg,
        transparent 0%,
        color-mix(in srgb, var(--at-text) 6%, transparent) 35%,
        color-mix(in srgb, var(--at-text) 11%, transparent) 50%,
        color-mix(in srgb, var(--at-text) 6%, transparent) 65%,
        transparent 100%);
      animation: sk-wave 1.8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
  }
  @media (prefers-reduced-motion: reduce) {
    .skb { animation: sk-pulse 2s ease-in-out infinite; }
  }

  .sk-r1 .skb::after { animation-delay: 0s; }
  .sk-r2 .skb::after { animation-delay: 0.08s; }
  .sk-r3 .skb::after { animation-delay: 0.16s; }
  .sk-r4 .skb::after { animation-delay: 0.24s; }
  .sk-r5 .skb::after { animation-delay: 0.32s; }
  .sk-r6 .skb::after { animation-delay: 0.4s; }
  .sk-r7 .skb::after { animation-delay: 0.48s; }
  .sk-r8 .skb::after { animation-delay: 0.56s; }

  .sk-card { background: var(--at-card); border: 1px solid var(--at-border); border-radius: 14px; overflow: hidden; }
`;

function B({ w = "100%", h, r = 5, style, className }: {
  w?: number | string;
  h: number;
  r?: number | string;
  style?: React.CSSProperties;
  className?: string;
}) {
  return <div className={`skb${className ? ` ${className}` : ""}`} style={{ width: w, height: h, borderRadius: r, ...style }} />;
}

function Header({ titleW = 200, subW = 150 }: { titleW?: number; subW?: number }) {
  return (
    <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start", marginBottom: 28, flexWrap: "wrap", gap: 12 }}>
      <div style={{ display: "flex", flexDirection: "column", gap: 9 }}>
        <B w={titleW} h={22} r={6} />
        <B w={subW} h={12} r={4} />
      </div>
      <B w={140} h={34} r={10} />
    </div>
  );
}

/* ── Dashboard ────────────────────────────────────────────────────────── */

function DashboardSkeleton() {
  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 28 }}>
      <Header titleW={220} subW={260} />

      {/* count cards */}
      <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill, minmax(190px, 1fr))", gap: 14 }}>
        {Array.from({ length: 9 }).map((_, i) => (
          <div key={i} className={`sk-card sk-r${(i % 8) + 1}`} style={{ padding: "15px 17px", display: "flex", flexDirection: "column", gap: 10 }}>
            <B w={70} h={10} r={4} />
            <B w={44} h={22} r={6} />
            <B w={90} h={9} r={3} style={{ opacity: 0.6 }} />
          </div>
        ))}
      </div>

      {/* chart masonry */}
      <div className="dash-masonry">
        {[220, 300, 190, 240, 210].map((h, i) => (
          <div key={i} className={`sk-card sk-r${(i % 8) + 1}`} style={{ padding: 20 }}>
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", paddingBottom: 14, marginBottom: 16, borderBottom: "1px solid var(--at-border)" }}>
              <div style={{ display: "flex", flexDirection: "column", gap: 6 }}>
                <B w={150} h={12} r={4} />
                <B w={100} h={9} r={3} style={{ opacity: 0.6 }} />
              </div>
              <B w={80} h={26} r={8} />
            </div>
            <B h={h - 90} r={8} style={{ opacity: 0.5 }} />
          </div>
        ))}
      </div>
    </div>
  );
}

/* ── Collection list ──────────────────────────────────────────────────── */

const LIST_GRID = "1fr 120px 64px 90px 90px";
const LIST_ROW_W = [190, 155, 215, 170, 200, 160, 225, 168, 205, 148, 182, 196];

function ListSkeleton() {
  return (
    <div className="admin-content-pad">
      <Header titleW={160} subW={90} />

      <B w={340} h={38} r={10} style={{ marginBottom: 20 }} />

      <div style={{ borderRadius: 14, border: "1px solid var(--at-border)", overflow: "hidden" }}>
        <div className="news-tbl-head" style={{ display: "grid", gridTemplateColumns: LIST_GRID, gap: 12, padding: "10px 16px", background: "var(--at-row-even)", borderBottom: "1px solid var(--at-border)" }}>
          {[34, 44, 26, 40, 0].map((w, i) => (
            <div key={i} style={{ display: "flex", justifyContent: i === 4 ? "flex-end" : "flex-start" }}>
              {w > 0 && <B w={w} h={9} r={3} style={{ opacity: 0.5 }} />}
            </div>
          ))}
        </div>
        {LIST_ROW_W.map((tw, i, arr) => (
          <div
            key={i}
            className={`news-tbl-row sk-r${(i % 8) + 1}`}
            style={{
              display: "grid", gridTemplateColumns: LIST_GRID, gap: 12, padding: "12px 16px",
              alignItems: "center",
              borderBottom: i < arr.length - 1 ? "1px solid var(--at-border-row)" : "none",
              background: i % 2 === 0 ? "var(--at-row-even)" : "transparent",
            }}
          >
            <div className="news-col-title" style={{ minWidth: 0 }}><B w={tw} h={13} r={4} /></div>
            <div className="news-col-cat"><B w={90} h={11} r={4} /></div>
            <div className="news-col-cat"><B w={34} h={20} r={20} /></div>
            <div><B w={52} h={20} r={20} /></div>
            <div className="news-col-edit" style={{ display: "flex", justifyContent: "flex-end", gap: 4 }}>
              <B w={26} h={26} r={7} />
              <B w={26} h={26} r={7} />
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

/* ── Record form (new / edit) ─────────────────────────────────────────── */

function FormSkeleton() {
  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 20, maxWidth: 900 }}>
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", flexWrap: "wrap", gap: 12 }}>
        <B w={280} h={22} r={6} />
        <B w={110} h={30} r={8} />
      </div>

      {/* locale pills */}
      <div style={{ display: "flex", gap: 6 }}>
        {[40, 40, 40, 40].map((w, i) => <B key={i} w={w} h={28} r={999} />)}
      </div>

      {/* field grid */}
      <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 16 }}>
        {Array.from({ length: 6 }).map((_, i) => {
          const wide = i >= 4;
          return (
            <div key={i} className={`sk-r${(i % 8) + 1}`} style={{ gridColumn: wide ? "1 / -1" : undefined, display: "flex", flexDirection: "column", gap: 8 }}>
              <B w={90} h={9} r={3} style={{ opacity: 0.6 }} />
              <B h={wide ? 72 : 44} r={12} />
            </div>
          );
        })}
      </div>

      {/* editor block */}
      <div className="sk-r5" style={{ display: "flex", flexDirection: "column", gap: 8 }}>
        <B w={60} h={9} r={3} style={{ opacity: 0.6 }} />
        <B h={280} r={12} />
      </div>

      <div style={{ display: "flex", gap: 10 }}>
        <B w={130} h={38} r={10} />
        <B w={90} h={38} r={10} />
      </div>
    </div>
  );
}

/* ── Translations workbench ───────────────────────────────────────────── */

function TranslationsSkeleton() {
  return (
    <div className="admin-content-pad" style={{ maxWidth: 1180 }}>
      <B w={180} h={22} r={6} />
      <B w={520} h={11} r={4} style={{ marginTop: 10, opacity: 0.6 }} />
      <B w={330} h={11} r={4} style={{ marginTop: 6, opacity: 0.6 }} />

      {/* "Translating into" language switch */}
      <div style={{ display: "flex", alignItems: "center", gap: 10, margin: "20px 0 18px" }}>
        <B w={100} h={12} r={4} style={{ opacity: 0.6 }} />
        <B w={260} h={36} r={10} />
      </div>

      {/* tab bar */}
      <div style={{ display: "flex", gap: 18, borderBottom: "1px solid var(--at-border)", paddingBottom: 12, marginBottom: 16 }}>
        {[92, 110, 80].map((w, i) => <B key={i} w={w} h={13} r={4} />)}
      </div>

      {/* workbench: left area nav + right cards */}
      <div style={{ display: "flex", border: "1px solid var(--at-border)", borderRadius: 14, overflow: "hidden", minHeight: 520 }}>
        <div style={{ width: 264, flexShrink: 0, borderRight: "1px solid var(--at-border)" }}>
          {Array.from({ length: 7 }).map((_, i) => (
            <div key={i} className={`sk-r${(i % 8) + 1}`} style={{ display: "flex", alignItems: "center", gap: 11, padding: "11px 14px", borderBottom: "1px solid var(--at-border)" }}>
              <B w={34} h={34} r={999} />
              <div style={{ flex: 1, display: "flex", flexDirection: "column", gap: 6 }}>
                <B w={i % 2 ? 150 : 110} h={12} r={4} />
                <B w={70} h={9} r={3} style={{ opacity: 0.6 }} />
              </div>
            </div>
          ))}
        </div>
        <div style={{ flex: 1, minWidth: 0, display: "flex", flexDirection: "column" }}>
          <div style={{ padding: "12px 18px", borderBottom: "1px solid var(--at-border)", display: "flex", justifyContent: "flex-end", gap: 8 }}>
            <B w={170} h={30} r={9} />
            <B w={96} h={30} r={9} />
          </div>
          <div style={{ padding: "18px 20px", display: "flex", flexDirection: "column", gap: 12 }}>
            <B w={200} h={14} r={4} style={{ marginBottom: 2 }} />
            {Array.from({ length: 4 }).map((_, i) => (
              <div key={i} className={`sk-r${(i % 8) + 1}`} style={{ border: "1px solid var(--at-border)", borderRadius: 14, padding: "16px 18px", display: "flex", flexDirection: "column", gap: 10 }}>
                <B w={150} h={12} r={4} />
                <B h={40} r={8} style={{ opacity: 0.55 }} />
                <B h={56} r={9} />
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}

/* ── Tabbed panel form (settings) ─────────────────────────────────────── */

function PanelFormSkeleton() {
  return (
    <div className="admin-content-pad" style={{ maxWidth: 720 }}>
      <B w={150} h={22} r={6} />
      <B w={420} h={11} r={4} style={{ marginTop: 10, opacity: 0.6 }} />

      {/* tab bar */}
      <div style={{ display: "flex", gap: 8, margin: "22px 0 26px" }}>
        {[90, 120, 110, 96, 90].map((w, i) => <B key={i} w={w} h={32} r={9} />)}
      </div>

      <B w={160} h={15} r={5} />
      <B w={340} h={10} r={4} style={{ marginTop: 8, marginBottom: 20, opacity: 0.6 }} />

      <div style={{ display: "flex", flexDirection: "column", gap: 18 }}>
        {Array.from({ length: 5 }).map((_, i) => (
          <div key={i} className={`sk-r${(i % 8) + 1}`} style={{ display: "flex", flexDirection: "column", gap: 8 }}>
            <B w={100} h={9} r={3} style={{ opacity: 0.6 }} />
            <B h={i === 3 ? 76 : 44} r={12} />
          </div>
        ))}
      </div>

      <B w={130} h={38} r={10} style={{ marginTop: 24 }} />
    </div>
  );
}

/* ── Stats + list (sync, seo) ─────────────────────────────────────────── */

function StatsListSkeleton({ statCards = 2, maxWidth = 860 }: { statCards?: number; maxWidth?: number }) {
  return (
    <div className="admin-content-pad" style={{ maxWidth }}>
      <B w={170} h={22} r={6} />
      <B w={480} h={11} r={4} style={{ marginTop: 10, marginBottom: 24, opacity: 0.6 }} />

      <div style={{ display: "flex", gap: 12, marginBottom: 24 }}>
        {Array.from({ length: statCards }).map((_, i) => (
          <div key={i} className={`sk-r${(i % 8) + 1}`} style={{ flex: 1, padding: "16px 20px", borderRadius: 12, border: "1px solid var(--at-border)", display: "flex", flexDirection: "column", gap: 8 }}>
            <B w={54} h={26} r={6} />
            <B w={90} h={9} r={3} style={{ opacity: 0.6 }} />
          </div>
        ))}
      </div>

      <div style={{ display: "flex", gap: 8, marginBottom: 18, flexWrap: "wrap" }}>
        {[60, 90, 130, 80, 110, 90].map((w, i) => <B key={i} w={w} h={28} r={999} />)}
      </div>

      <div style={{ borderRadius: 14, border: "1px solid var(--at-border)", overflow: "hidden" }}>
        {Array.from({ length: 8 }).map((_, i) => (
          <div key={i} className={`sk-r${(i % 8) + 1}`} style={{ display: "flex", alignItems: "center", gap: 12, padding: "13px 16px", borderBottom: i < 7 ? "1px solid var(--at-border-row)" : "none", background: i % 2 === 0 ? "var(--at-row-even)" : "transparent" }}>
            <B w={[220, 180, 260, 200, 240][i % 5]} h={13} r={4} style={{ flex: 1 }} />
            <B w={44} h={20} r={20} />
            <B w={60} h={20} r={20} />
            <B w={26} h={26} r={7} />
          </div>
        ))}
      </div>
    </div>
  );
}

/* ── Generic ─────────────────────────────────────────────────────────────

   Neutral fallback for admin routes without their own loading.tsx. Must not
   resemble any specific page — a header plus a few plain blocks, nothing that
   reads as "dashboard" or "table" before the real page swaps in. */

function GenericSkeleton() {
  return (
    <div className="admin-content-pad" style={{ maxWidth: 900 }}>
      <B w={190} h={22} r={6} />
      <B w={360} h={11} r={4} style={{ marginTop: 10, marginBottom: 28, opacity: 0.6 }} />

      <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
        {Array.from({ length: 5 }).map((_, i) => (
          <div key={i} className={`sk-card sk-r${(i % 8) + 1}`} style={{ padding: "18px 20px", display: "flex", flexDirection: "column", gap: 10 }}>
            <B w={[160, 220, 140, 190, 170][i]} h={13} r={4} />
            <B h={i % 2 ? 56 : 40} r={8} style={{ opacity: 0.5 }} />
          </div>
        ))}
      </div>
    </div>
  );
}

/* ── Root ─────────────────────────────────────────────────────────────── */

export default function AdminPageSkeleton({ variant = "generic" }: {
  variant?: "generic" | "dashboard" | "list" | "form" | "translations" | "settings" | "sync" | "seo";
}) {
  return (
    <AdminShell>
      <style>{CSS}</style>
      {variant === "dashboard" ? <DashboardSkeleton />
        : variant === "list" ? <ListSkeleton />
        : variant === "form" ? <FormSkeleton />
        : variant === "translations" ? <TranslationsSkeleton />
        : variant === "settings" ? <PanelFormSkeleton />
        : variant === "sync" ? <StatsListSkeleton statCards={2} maxWidth={860} />
        : variant === "seo" ? <StatsListSkeleton statCards={3} maxWidth={1000} />
        : <GenericSkeleton />}
    </AdminShell>
  );
}
