import type { Metadata } from "next";
import "./admin.css";
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
const blockingScript = `(function(){try{var el=document.getElementById('admin-shell');var t=localStorage.getItem('oox-admin-theme')||'dark';var h=new Date().getHours();if(t==='auto')t=(h>=7&&h<19)?'light':'dark';if(el)el.style.colorScheme=t;if(t==='light'&&el){var r=el.style;${lightScript}}${fontScript}}catch(e){}})();`;

export default function AdminLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en" suppressHydrationWarning>
      <body style={{ margin: 0 }}>
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
