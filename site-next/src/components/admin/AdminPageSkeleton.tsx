/**
 * Route-level loading states for /admin. Every admin page is dynamic (session
 * cookie) and some do real per-request work (SEO scoring), so without this a
 * link click freezes the current page until the response lands. The sidebar
 * lives in admin/(shell)/layout.tsx and never unmounts — only this content
 * shimmers in its place, so nothing in the nav shifts on navigation.
 *
 * One variant per distinct page shape. Each mirrors the real screen's outer
 * padding, max-width and block rhythm so the swap to live content doesn't jump —
 * keep them in step when a page's layout changes. A wave sweep across
 * color-mix-derived bars, staggered per row, with a reduced-motion pulse
 * fallback (both defined in admin.css).
 */

type CSSProps = React.CSSProperties;

function B({ w = "100%", h, r = 5, style, className }: {
  w?: number | string;
  h: number;
  r?: number | string;
  style?: CSSProps;
  className?: string;
}) {
  return <div className={`skb${className ? ` ${className}` : ""}`} style={{ width: w, height: h, borderRadius: r, ...style }} />;
}

/** `admin-content-pad` wrapper matching the real pages' outer column. */
function Pad({ maxWidth, gap = 24, children }: { maxWidth?: number; gap?: number; children: React.ReactNode }) {
  return (
    <div className="admin-content-pad" style={{ maxWidth, display: "flex", flexDirection: "column", gap }}>
      {children}
    </div>
  );
}

/** Page title, optional subtitle lines, optional top-right action button. */
function PageHead({ titleW = 150, titleH = 22, lines = [90], action = false }: {
  titleW?: number;
  titleH?: number;
  lines?: number[];
  action?: boolean;
}) {
  return (
    <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start", flexWrap: "wrap", gap: 12 }}>
      <div style={{ display: "flex", flexDirection: "column", gap: 8 }}>
        <B w={titleW} h={titleH} r={6} />
        {lines.map((w, i) => <B key={i} w={w} h={11} r={4} style={{ opacity: 0.6 }} />)}
      </div>
      {action && <B w={130} h={34} r={10} />}
    </div>
  );
}

/** Vertical stack of "label + input" field rows, staggered per row. */
function FieldRows({ heights }: { heights: number[] }) {
  return (
    <>
      {heights.map((h, i) => (
        <div key={i} className={`sk-r${(i % 8) + 1}`} style={{ display: "flex", flexDirection: "column", gap: 8 }}>
          <B w={100} h={9} r={3} style={{ opacity: 0.6 }} />
          <B h={h} r={12} />
        </div>
      ))}
    </>
  );
}

/* ── Dashboard ────────────────────────────────────────────────────────── */

function DashboardSkeleton() {
  return (
    <Pad gap={28}>
      <PageHead titleW={240} titleH={26} lines={[300]} />

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
        {[250, 320, 230, 220].map((h, i) => (
          <div key={i} className={`sk-card sk-r${(i % 8) + 1}`} style={{ padding: 20 }}>
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", paddingBottom: 14, marginBottom: 16, borderBottom: "1px solid var(--at-border)" }}>
              <div style={{ display: "flex", flexDirection: "column", gap: 6 }}>
                <B w={150} h={12} r={4} />
                <B w={100} h={9} r={3} style={{ opacity: 0.6 }} />
              </div>
              <B w={90} h={26} r={8} />
            </div>
            <B h={h - 90} r={8} style={{ opacity: 0.5 }} />
          </div>
        ))}
      </div>

      {/* quick actions */}
      <div>
        <B w={90} h={9} r={3} style={{ opacity: 0.5, marginBottom: 12 }} />
        <div style={{ display: "flex", gap: 10, flexWrap: "wrap" }}>
          {[130, 150, 110, 90].map((w, i) => <B key={i} w={w} h={34} r={10} />)}
        </div>
      </div>
    </Pad>
  );
}

/* ── Collection list ──────────────────────────────────────────────────── */

const LIST_GRID = "1fr 120px 64px 90px 90px";
const LIST_ROW_W = [190, 155, 215, 170, 200, 160, 225, 168, 205, 148];

function ListSkeleton() {
  return (
    <Pad>
      <PageHead titleW={150} lines={[80]} action />

      {/* search + filter row */}
      <div style={{ display: "flex", gap: 10, flexWrap: "wrap" }}>
        <B w={320} h={38} r={10} />
        <B w={168} h={38} r={10} />
      </div>

      <div style={{ borderRadius: 14, border: "1px solid var(--at-border)", overflow: "hidden" }}>
        <div className="news-tbl-head" style={{ display: "grid", gridTemplateColumns: LIST_GRID, gap: 12, padding: "10px 16px", background: "var(--at-row-even)", borderBottom: "1px solid var(--at-border)" }}>
          {[40, 44, 26, 40, 0].map((w, i) => (
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
    </Pad>
  );
}

/* ── Pages list (grouped, no search) ──────────────────────────────────── */

function PagesSkeleton() {
  return (
    <Pad maxWidth={900}>
      <div style={{ display: "flex", flexDirection: "column", gap: 8 }}>
        <B w={90} h={22} r={6} />
        <B w={520} h={11} r={4} style={{ opacity: 0.6 }} />
        <B w={380} h={11} r={4} style={{ opacity: 0.6 }} />
      </div>

      <div>
        <B w={140} h={10} r={3} style={{ opacity: 0.5, marginBottom: 10 }} />
        <div style={{ border: "1px solid var(--at-border)", borderRadius: 14, overflow: "hidden" }}>
          {[210, 170, 240, 190, 220, 180].map((tw, i, arr) => (
            <div
              key={i}
              className={`news-tbl-row sk-r${(i % 8) + 1}`}
              style={{
                display: "grid", gridTemplateColumns: "1fr auto auto", gap: 14, alignItems: "center",
                padding: "12px 16px",
                borderBottom: i < arr.length - 1 ? "1px solid var(--at-border-row)" : "none",
                background: i % 2 === 0 ? "var(--at-row-even)" : "transparent",
              }}
            >
              <div className="news-col-title" style={{ minWidth: 0, display: "flex", flexDirection: "column", gap: 6 }}>
                <B w={tw} h={13} r={4} />
                <B w={Math.round(tw * 0.5)} h={10} r={3} style={{ opacity: 0.55 }} />
              </div>
              <div className="news-col-cat"><B w={60} h={11} r={4} /></div>
              <div style={{ display: "flex", gap: 6 }}>
                <B w={30} h={28} r={8} />
                <B w={52} h={28} r={8} />
              </div>
            </div>
          ))}
        </div>
      </div>
    </Pad>
  );
}

/* ── Record form — new / edit collection (SchemaForm) ─────────────────── */

function FormSkeleton() {
  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 20, maxWidth: 860 }}>
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", flexWrap: "wrap", gap: 12 }}>
        <B w={240} h={20} r={6} />
        <B w={110} h={30} r={8} />
      </div>

      {/* locale pills */}
      <div style={{ display: "flex", gap: 6 }}>
        {[38, 38, 38, 38].map((w, i) => <B key={i} w={w} h={28} r={999} />)}
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

      {/* rich text block */}
      <div className="sk-r5" style={{ display: "flex", flexDirection: "column", gap: 8 }}>
        <B w={60} h={9} r={3} style={{ opacity: 0.6 }} />
        <B h={260} r={12} />
      </div>

      <div style={{ display: "flex", gap: 10, borderTop: "1px solid var(--at-border)", paddingTop: 16 }}>
        <B w={130} h={38} r={10} />
      </div>
    </div>
  );
}

/* ── Record + tabs — service editor (details / page content) ──────────── */

function RecordTabsSkeleton() {
  return (
    <div className="admin-content-pad" style={{ maxWidth: 860, display: "flex", flexDirection: "column", gap: 18 }}>
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", flexWrap: "wrap", gap: 12 }}>
        <B w={230} h={20} r={6} />
        <B w={110} h={30} r={8} />
      </div>

      <div style={{ display: "flex", gap: 8, borderBottom: "1px solid var(--at-border)", paddingBottom: 11 }}>
        <B w={90} h={14} r={4} />
        <B w={140} h={14} r={4} />
      </div>

      <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 16 }}>
        {Array.from({ length: 6 }).map((_, i) => {
          const wide = i >= 4;
          return (
            <div key={i} className={`sk-r${(i % 8) + 1}`} style={{ gridColumn: wide ? "1 / -1" : undefined, display: "flex", flexDirection: "column", gap: 8 }}>
              <B w={90} h={9} r={3} style={{ opacity: 0.6 }} />
              <B h={wide ? 120 : 44} r={12} />
            </div>
          );
        })}
      </div>

      <B w={90} h={11} r={4} style={{ opacity: 0.5 }} />
    </div>
  );
}

/* ── Page editor — field-by-field, tabbed (PageEditor) ────────────────── */

function PageEditorSkeleton() {
  return (
    <div className="admin-content-pad" style={{ maxWidth: 780, display: "flex", flexDirection: "column", gap: 18 }}>
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start", flexWrap: "wrap", gap: 12 }}>
        <div style={{ display: "flex", flexDirection: "column", gap: 8 }}>
          <B w={240} h={20} r={6} />
          <B w={300} h={11} r={4} style={{ opacity: 0.6 }} />
        </div>
        <B w={110} h={30} r={8} />
      </div>

      {/* tabs */}
      <div style={{ display: "flex", gap: 8, borderBottom: "1px solid var(--at-border)", paddingBottom: 11 }}>
        <B w={72} h={14} r={4} />
        <B w={130} h={14} r={4} />
      </div>

      {/* filter fields */}
      <B h={38} r={10} />

      {/* card groups */}
      {[3, 4].map((n, gi) => (
        <div key={gi} className="sk-card" style={{ padding: "22px 24px", display: "flex", flexDirection: "column", gap: 14 }}>
          <B w={90} h={9} r={3} style={{ opacity: 0.6 }} />
          {Array.from({ length: n }).map((_, i) => (
            <div key={i} className={`sk-r${(i % 8) + 1}`} style={{ display: "flex", flexDirection: "column", gap: 8 }}>
              <B w={100} h={9} r={3} style={{ opacity: 0.6 }} />
              <B h={i === n - 1 ? 120 : 44} r={10} />
            </div>
          ))}
        </div>
      ))}

      <div style={{ display: "flex", gap: 12, borderTop: "1px solid var(--at-border)", paddingTop: 16 }}>
        <B w={130} h={38} r={10} />
      </div>
    </div>
  );
}

/* ── Menus editor — two link-list cards ──────────────────────────────── */

function MenusSkeleton() {
  return (
    <div className="admin-content-pad" style={{ maxWidth: 760, display: "flex", flexDirection: "column", gap: 18 }}>
      <div style={{ display: "flex", flexDirection: "column", gap: 8 }}>
        <B w={90} h={22} r={6} />
        <B w={340} h={11} r={4} style={{ opacity: 0.6 }} />
      </div>

      {[5, 3].map((rows, bi) => (
        <div key={bi} className="sk-card" style={{ padding: "22px 24px", display: "flex", flexDirection: "column", gap: 12 }}>
          <div style={{ display: "flex", flexDirection: "column", gap: 6 }}>
            <B w={150} h={13} r={5} />
            <B w={320} h={10} r={3} style={{ opacity: 0.6 }} />
          </div>
          {Array.from({ length: rows }).map((_, i) => (
            <div key={i} className={`sk-r${(i % 8) + 1}`} style={{ display: "flex", alignItems: "center", gap: 8 }}>
              <B w={170} h={34} r={8} />
              <B h={34} r={8} style={{ flex: 1 }} />
              <div style={{ display: "flex", gap: 2 }}>
                <B w={28} h={28} r={8} />
                <B w={28} h={28} r={8} />
                <B w={28} h={28} r={8} />
              </div>
            </div>
          ))}
          <B w={90} h={30} r={8} />
        </div>
      ))}

      <div style={{ display: "flex", gap: 12, borderTop: "1px solid var(--at-border)", paddingTop: 16 }}>
        <B w={120} h={38} r={10} />
      </div>
    </div>
  );
}

/* ── Settings — tabbed panel form ────────────────────────────────────── */

function SettingsSkeleton() {
  return (
    <div className="admin-content-pad" style={{ maxWidth: 720 }}>
      <div style={{ display: "flex", flexDirection: "column", gap: 8, marginBottom: 22 }}>
        <B w={90} h={22} r={6} />
        <B w={200} h={11} r={4} style={{ opacity: 0.6 }} />
      </div>

      {/* tab bar */}
      <div style={{ display: "flex", gap: 10, borderBottom: "1px solid var(--at-border)", paddingBottom: 11, marginBottom: 24 }}>
        {[90, 150, 150, 100, 90].map((w, i) => <B key={i} w={w} h={16} r={4} />)}
      </div>

      <div className="sk-card" style={{ padding: "22px 24px", maxWidth: 620, display: "flex", flexDirection: "column", gap: 18 }}>
        <div style={{ display: "flex", flexDirection: "column", gap: 6 }}>
          <B w={140} h={13} r={5} />
          <B w={380} h={10} r={3} style={{ opacity: 0.6 }} />
        </div>
        <FieldRows heights={[44, 44, 44, 120]} />
        <div style={{ borderTop: "1px solid var(--at-border)", paddingTop: 18, marginTop: 4 }}>
          <B w={150} h={38} r={10} />
        </div>
      </div>
    </div>
  );
}

/* ── SEO report — score bar + filters + table ────────────────────────── */

function SeoSkeleton() {
  const GRID = "1fr 60px 70px 1.2fr 80px";
  return (
    <Pad maxWidth={1000} gap={20}>
      <div style={{ display: "flex", flexDirection: "column", gap: 8 }}>
        <B w={110} h={22} r={6} />
        <B w={480} h={11} r={4} style={{ opacity: 0.6 }} />
      </div>

      {/* segmented score bar */}
      <div style={{ borderRadius: 14, border: "1px solid var(--at-border)", background: "var(--at-card)", padding: "16px 18px", display: "flex", flexDirection: "column", gap: 10 }}>
        <B h={12} r={6} />
        <div style={{ display: "flex", gap: 14 }}>
          {[60, 90, 60].map((w, i) => <B key={i} w={w} h={9} r={3} style={{ opacity: 0.6 }} />)}
        </div>
      </div>

      {/* filter + type pills */}
      <div style={{ display: "flex", gap: 8, flexWrap: "wrap", alignItems: "center" }}>
        {[40, 90, 150, 80, 130, 90].map((w, i) => <B key={i} w={w} h={28} r={999} />)}
        <B w={1} h={20} r={0} />
        {[70, 60, 60, 70].map((w, i) => <B key={`k${i}`} w={w} h={28} r={999} />)}
      </div>

      {/* search + sort */}
      <div style={{ display: "flex", gap: 10, flexWrap: "wrap", alignItems: "center" }}>
        <B w={300} h={36} r={9} />
        <B w={190} h={36} r={9} />
        <B w={60} h={10} r={3} style={{ opacity: 0.6 }} />
      </div>

      {/* table */}
      <div style={{ borderRadius: 14, border: "1px solid var(--at-border)", overflow: "hidden" }}>
        <div className="news-tbl-head" style={{ display: "grid", gridTemplateColumns: GRID, gap: 12, padding: "10px 16px", background: "var(--at-row-even)", borderBottom: "1px solid var(--at-border)" }}>
          {[40, 40, 44, 120, 0].map((w, i) => (
            <div key={i}>{w > 0 && <B w={w} h={9} r={3} style={{ opacity: 0.5 }} />}</div>
          ))}
        </div>
        {Array.from({ length: 10 }).map((_, i) => (
          <div
            key={i}
            className={`news-tbl-row sk-r${(i % 8) + 1}`}
            style={{
              display: "grid", gridTemplateColumns: GRID, gap: 12, padding: "12px 16px", alignItems: "center",
              borderBottom: i < 9 ? "1px solid var(--at-border-row)" : "none",
            }}
          >
            <div className="news-col-title" style={{ minWidth: 0, display: "flex", flexDirection: "column", gap: 6 }}>
              <B w={[200, 160, 240, 180][i % 4]} h={13} r={4} />
              <B w={[120, 150, 110][i % 3]} h={10} r={3} style={{ opacity: 0.55 }} />
            </div>
            <div className="news-col-cat"><B w={40} h={20} r={20} /></div>
            <div className="news-col-cat"><B w={28} h={11} r={4} /></div>
            <div className="news-col-cat"><B w={[180, 120, 200][i % 3]} h={11} r={4} /></div>
            <div className="news-col-edit" style={{ display: "flex", justifyContent: "flex-end" }}><B w={36} h={26} r={7} /></div>
          </div>
        ))}
      </div>
    </Pad>
  );
}

/* ── Generic ─────────────────────────────────────────────────────────────

   Neutral fallback for admin routes without their own loading.tsx. Must not
   resemble any specific page — a header plus a few plain blocks, nothing that
   reads as "dashboard" or "table" before the real page swaps in. */

function GenericSkeleton() {
  return (
    <Pad maxWidth={900}>
      <div style={{ display: "flex", flexDirection: "column", gap: 8 }}>
        <B w={190} h={22} r={6} />
        <B w={360} h={11} r={4} style={{ opacity: 0.6 }} />
      </div>
      <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
        {[160, 220, 140, 190, 170].map((labelW, i) => (
          <div key={i} className={`sk-card sk-r${(i % 8) + 1}`} style={{ padding: "18px 20px", display: "flex", flexDirection: "column", gap: 10 }}>
            <B w={labelW} h={13} r={4} />
            <B h={i % 2 ? 56 : 40} r={8} style={{ opacity: 0.5 }} />
          </div>
        ))}
      </div>
    </Pad>
  );
}

/* ── Root ─────────────────────────────────────────────────────────────── */

export type AdminSkeletonVariant =
  | "generic" | "dashboard" | "list" | "pages" | "form"
  | "recordTabs" | "pageEditor" | "menus" | "settings" | "seo";

export default function AdminPageSkeleton({ variant = "generic" }: { variant?: AdminSkeletonVariant }) {
  switch (variant) {
    case "dashboard": return <DashboardSkeleton />;
    case "list": return <ListSkeleton />;
    case "pages": return <PagesSkeleton />;
    case "form": return <FormSkeleton />;
    case "recordTabs": return <RecordTabsSkeleton />;
    case "pageEditor": return <PageEditorSkeleton />;
    case "menus": return <MenusSkeleton />;
    case "settings": return <SettingsSkeleton />;
    case "seo": return <SeoSkeleton />;
    default: return <GenericSkeleton />;
  }
}
