import type { Metadata } from "next";
import Script from "next/script";
import { Inter, Outfit, Plus_Jakarta_Sans, Poppins, Space_Grotesk } from "next/font/google";
import AdminThemeProvider from "@/components/admin/AdminThemeProvider";

export const metadata: Metadata = {
  title: "OOX Admin",
  robots: { index: false, follow: false },
};

const poppins = Poppins({ subsets: ["latin"], weight: ["400", "500", "600", "700"], display: "swap", variable: "--font-poppins" });
const inter = Inter({ subsets: ["latin"], weight: ["300", "400", "500", "600", "700"], display: "swap", variable: "--font-inter" });
const jakarta = Plus_Jakarta_Sans({ subsets: ["latin"], weight: ["300", "400", "500", "600", "700"], display: "swap", variable: "--font-jakarta" });
const spaceGrotesk = Space_Grotesk({ subsets: ["latin"], weight: ["300", "400", "500", "600", "700"], display: "swap", variable: "--font-space-grotesk" });
const outfit = Outfit({ subsets: ["latin"], weight: ["300", "400", "500", "600", "700"], display: "swap", variable: "--font-outfit" });

const LIGHT_PROPS: [string, string][] = [
  ["--at-bg", "#f5f5f5"], ["--at-card", "#ffffff"], ["--at-sidebar", "#fafafa"],
  ["--at-border", "rgba(0,0,0,0.12)"], ["--at-border-header", "rgba(0,0,0,0.06)"],
  ["--at-border-row", "rgba(0,0,0,0.04)"], ["--at-border-input", "rgba(0,0,0,0.15)"],
  ["--at-border-input-alt", "rgba(0,0,0,0.1)"], ["--at-input", "rgba(0,0,0,0.04)"],
  ["--at-input-alt", "rgba(0,0,0,0.06)"], ["--at-text", "#111111"], ["--at-muted", "#666666"],
  ["--at-faint", "#aaaaaa"], ["--at-hover", "rgba(0,0,0,0.03)"], ["--at-row-even", "rgba(0,0,0,0.02)"],
  ["--at-row-odd", "transparent"], ["--at-placeholder", "#aaaaaa"], ["--at-prose-p", "rgba(0,0,0,0.75)"],
  ["--at-prose-strong", "#111111"], ["--at-prose-code", "#475569"], ["--at-prose-code-bg", "rgba(0,0,0,0.05)"],
  ["--at-prose-pre-bg", "rgba(0,0,0,0.04)"], ["--at-prose-pre-border", "rgba(0,0,0,0.08)"],
  ["--at-toolbar-hover-bg", "rgba(0,0,0,0.05)"], ["--at-toolbar-hover-fg", "#333333"],
  ["--at-toolbar-divider", "rgba(0,0,0,0.08)"], ["--at-accent", "#6366f1"], ["--at-accent-muted", "rgba(99,102,241,0.12)"],
  ["--at-ok", "#15803d"], ["--at-ok-bg", "rgba(34,197,94,0.14)"],
  ["--at-shadow-card", "0 10px 30px -14px rgba(15,23,42,0.16), 0 2px 6px -2px rgba(15,23,42,0.05)"],
  ["--at-shadow-hover", "0 20px 44px -14px rgba(15,23,42,0.22), 0 4px 10px -2px rgba(15,23,42,0.08)"],
  ["--at-track", "rgba(0,0,0,0.06)"],
  ["--at-card-glow", "rgba(99,102,241,0.07)"],
  ["--at-card-glow-2", "rgba(168,85,247,0.055)"],
  ["--at-glass-bg", "rgba(255,255,255,0.66)"],
  ["--at-glass-border", "rgba(15,23,42,0.08)"],
  ["--at-glass-hairline", "rgba(15,23,42,0.07)"],
  ["--at-glass-inset", "rgba(255,255,255,0.6)"],
  ["--at-mesh-1", "rgba(99,102,241,0.10)"],
  ["--at-mesh-2", "rgba(168,85,247,0.07)"],
];

const lightScript = LIGHT_PROPS.map(([k, v]) => `r.setProperty('${k}','${v}');`).join("");
const fontScript = `var fm={'inter':'var(--font-inter)','poppins':'var(--font-poppins)','jakarta':'var(--font-jakarta)','space-grotesk':'var(--font-space-grotesk)','outfit':'var(--font-outfit)'};var af=localStorage.getItem('oox-admin-font')||'inter';var el=document.getElementById('admin-shell');if(el)el.style.setProperty('--at-font-body',fm[af]||fm['inter']);`;
const blockingScript = `(function(){try{var el=document.getElementById('admin-shell');var t=localStorage.getItem('oox-admin-theme')||'dark';var h=new Date().getHours();if(t==='auto')t=(h>=7&&h<19)?'light':'dark';if(t==='light'&&el){var r=el.style;${lightScript}}${fontScript}}catch(e){}})();`;

export default function AdminLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en" suppressHydrationWarning>
      <body style={{ margin: 0 }}>
        <style>{`
          :root {
            --at-bg: #0a0a0a; --at-card: #141414; --at-sidebar: #0f0f0f;
            --at-border: rgba(255,255,255,0.06); --at-border-header: rgba(255,255,255,0.05);
            --at-border-row: rgba(255,255,255,0.04); --at-border-input: rgba(255,255,255,0.1);
            --at-border-input-alt: rgba(255,255,255,0.08); --at-input: rgba(255,255,255,0.04);
            --at-input-alt: rgba(255,255,255,0.06); --at-text: #ffffff; --at-muted: #888888;
            --at-faint: #555555; --at-hover: rgba(255,255,255,0.04); --at-row-even: rgba(255,255,255,0.02);
            --at-row-odd: transparent; --at-placeholder: #555555; --at-prose-p: rgba(255,255,255,0.8);
            --at-prose-strong: #ffffff; --at-prose-code: #94a3b8; --at-prose-code-bg: rgba(255,255,255,0.06);
            --at-prose-pre-bg: rgba(0,0,0,0.3); --at-prose-pre-border: rgba(255,255,255,0.08);
            --at-toolbar-hover-bg: rgba(255,255,255,0.06); --at-toolbar-hover-fg: #cccccc;
            --at-toolbar-divider: rgba(255,255,255,0.08); --at-accent: #6366f1; --at-accent-muted: rgba(99,102,241,0.12);
            --at-ok: #4ade80; --at-ok-bg: rgba(34,197,94,0.16);
            --at-shadow-card: 0 14px 40px -18px rgba(0,0,0,0.7), 0 2px 8px -2px rgba(0,0,0,0.4);
            --at-shadow-hover: 0 24px 60px -16px rgba(0,0,0,0.75), 0 6px 16px -4px rgba(0,0,0,0.5);
            --at-track: rgba(255,255,255,0.07);
            --at-card-glow: rgba(99,102,241,0.16); --at-card-glow-2: rgba(168,85,247,0.12);
            --at-glass-bg: rgba(24,24,29,0.55); --at-glass-border: rgba(255,255,255,0.10);
            --at-glass-hairline: rgba(255,255,255,0.10); --at-glass-inset: rgba(255,255,255,0.07);
            --at-mesh-1: rgba(99,102,241,0.13); --at-mesh-2: rgba(168,85,247,0.10);
            --at-font-body: var(--font-inter);
          }
          html, body { background: var(--at-bg); }
          /* keep the scrollbar gutter reserved so a short skeleton -> tall page
             swap never nudges the layout sideways */
          html { scrollbar-gutter: stable; }
          .admin-shell, .admin-shell *, .admin-shell *::before, .admin-shell *::after { box-sizing: border-box; }
          .admin-shell { font-family: var(--at-font-body), system-ui, sans-serif; -webkit-font-smoothing: antialiased; color: var(--at-text); }
          .admin-shell button, .admin-shell input, .admin-shell textarea, .admin-shell select { font-family: inherit; }
          .admin-shell [hidden] { display: none !important; }
          .admin-shell main {
            background-image:
              radial-gradient(46rem 30rem at 8% -8%, var(--at-mesh-1), transparent 60%),
              radial-gradient(40rem 32rem at 108% 4%, var(--at-mesh-2), transparent 55%);
            background-repeat: no-repeat;
            background-attachment: fixed;
          }

          .btn { display: inline-flex; align-items: center; justify-content: center; gap: 7px; font-family: inherit; font-size: 13px; font-weight: 500; line-height: 1; border-radius: 10px; padding: 8px 16px; border: none; cursor: pointer; text-decoration: none; white-space: nowrap; position: relative; transition: background 0.15s, box-shadow 0.15s, border-color 0.15s, color 0.15s, opacity 0.15s, transform 0.1s; outline: none; }
          .btn:focus-visible { outline: 2px solid rgba(99,102,241,0.6); outline-offset: 2px; }
          .btn:disabled { opacity: 0.45; cursor: not-allowed; pointer-events: none; }
          .btn-primary { background: #6366f1; color: #fff; box-shadow: 0 1px 2px rgba(99,102,241,0.2), 0 2px 8px rgba(99,102,241,0.12); }
          .btn-primary:hover { background: #4f52d1; }
          .btn-primary:active { transform: scale(0.97); }
          .btn-secondary { background: transparent; color: var(--at-muted); border: 1px solid var(--at-border-input); }
          .btn-secondary:hover { background: var(--at-hover); color: var(--at-text); border-color: rgba(99,102,241,0.3); }
          .btn-secondary:active { transform: scale(0.97); }
          .btn-danger { background: transparent; color: #f87171; border: 1px solid rgba(239,68,68,0.3); }
          .btn-danger:hover { background: rgba(239,68,68,0.08); border-color: rgba(239,68,68,0.5); }
          .btn-ghost { background: transparent; color: var(--at-muted); border: none; padding: 7px; border-radius: 8px; }
          .btn-ghost:hover { background: var(--at-hover); color: var(--at-text); }
          .btn-ghost:active { transform: scale(0.93); }
          .btn-ghost-delete:hover { background: rgba(239,68,68,0.1) !important; color: #f87171 !important; }
          .btn-pill { border-radius: 999px; padding: 5px 14px; font-size: 12px; border: 1px solid var(--at-border-input); background: transparent; color: var(--at-muted); }
          .btn-pill:hover { border-color: rgba(99,102,241,0.4); color: #818cf8; background: rgba(99,102,241,0.06); }
          .btn-pill.active { background: rgba(99,102,241,0.15); border-color: rgba(99,102,241,0.4); color: #818cf8; font-weight: 600; }
          .btn-sm { font-size: 12px; padding: 6px 12px; border-radius: 8px; gap: 5px; }

          .at-tabs { display: flex; flex-wrap: wrap; gap: 4px; border-bottom: 1px solid var(--at-border); }
          .at-tab { display: inline-flex; align-items: center; gap: 7px; padding: 9px 13px 11px; font-size: 13px; font-weight: 500; font-family: inherit; color: var(--at-muted); background: transparent; border: none; border-radius: 8px 8px 0 0; cursor: pointer; white-space: nowrap; position: relative; transition: color 0.15s, background 0.15s; }
          .at-tab::after { content: ""; position: absolute; left: 7px; right: 7px; bottom: -1px; height: 2px; border-radius: 2px 2px 0 0; background: transparent; transition: background 0.15s; }
          .at-tab svg { color: var(--at-faint); transition: color 0.15s; }
          .at-tab:hover { color: var(--at-text); background: var(--at-hover); }
          .at-tab:hover svg { color: var(--at-muted); }
          .at-tab:focus-visible { outline: 2px solid rgba(99,102,241,0.6); outline-offset: -2px; }
          .at-tab.active { color: var(--at-text); font-weight: 600; }
          .at-tab.active svg { color: var(--at-accent); }
          .at-tab.active::after { background: var(--at-accent); }

          .at-card { background: var(--at-card); border: 1px solid var(--at-border); border-radius: 14px; padding: 22px 24px; }
          .at-card + .at-card { margin-top: 16px; }
          @media (max-width: 767px) { .at-card { padding: 18px 16px; } }

          .dash-masonry { columns: 400px 2; column-gap: 16px; }
          .dash-masonry > * { width: 100%; margin-bottom: 16px; break-inside: avoid; }
          @media (max-width: 640px) { .dash-masonry { columns: 1; } }

          .dash-card {
            position: relative;
            background-color: var(--at-glass-bg);
            background-image:
              radial-gradient(135% 115% at 0% 0%, var(--at-card-glow) 0%, transparent 46%),
              radial-gradient(120% 120% at 100% 0%, var(--at-card-glow-2) 0%, transparent 42%);
            border: 1px solid var(--at-glass-border);
            border-radius: 16px;
            box-shadow: var(--at-shadow-card), inset 0 1px 0 var(--at-glass-inset);
            backdrop-filter: blur(18px) saturate(155%);
            -webkit-backdrop-filter: blur(18px) saturate(155%);
          }
          .dash-card::before {
            content: ""; position: absolute; left: 18px; right: 18px; top: -1px; height: 2px; border-radius: 2px;
            background: linear-gradient(90deg, transparent, var(--at-accent), transparent);
            opacity: 0.3; pointer-events: none;
          }
          .dash-link-card { transition: transform 0.15s, box-shadow 0.15s, border-color 0.15s; }
          .dash-link-card:hover { transform: translateY(-3px); box-shadow: var(--at-shadow-hover); border-color: rgba(99,102,241,0.45); }
          .dash-link-card:hover::before { opacity: 0.9; }
          .dash-link-card:hover .dash-arrow { transform: translateX(2px); color: var(--at-accent); }
          .dash-arrow { transition: transform 0.15s, color 0.15s; }
          .dash-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 8px; margin: 0 -8px; border-radius: 9px; text-decoration: none; transition: background 0.12s; }
          .dash-row:hover { background: var(--at-hover); }
          .dash-fill { position: relative; overflow: hidden; }
          .dash-fill::after { content: ""; position: absolute; inset: 0; background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.28) 100%); }

          .admin-mobile-bar { display: none; }
          @media (max-width: 767px) {
            .admin-sidebar { display: none !important; width: 0 !important; min-width: 0 !important; }
            .admin-mobile-bar { display: flex !important; }
            .admin-shell main { padding: 76px 16px 84px !important; overflow-x: hidden !important; width: 100% !important; max-width: 100% !important; min-width: 0 !important; }
            .news-tbl-head { display: none !important; }
            .news-tbl-row { display: flex !important; align-items: center !important; gap: 8px !important; padding: 12px 16px !important; }
            .news-col-cat { display: none !important; }
            .news-col-title { flex: 1 !important; min-width: 0 !important; }
          }
        `}</style>
        <div
          id="admin-shell"
          className={`admin-shell ${poppins.variable} ${inter.variable} ${jakarta.variable} ${spaceGrotesk.variable} ${outfit.variable}`}
          suppressHydrationWarning
        >
          <Script id="admin-theme-boot" strategy="beforeInteractive" dangerouslySetInnerHTML={{ __html: blockingScript }} />
          {/* The sidebar shell is in admin/(shell)/layout.tsx — login sits
              outside it. This layer only owns the theme + fonts + <html>/<body>. */}
          <AdminThemeProvider>{children}</AdminThemeProvider>
        </div>
      </body>
    </html>
  );
}
