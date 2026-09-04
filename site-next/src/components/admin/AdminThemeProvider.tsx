"use client";

import { createContext, useContext, useEffect, useState } from "react";
import { Toaster } from "sonner";
import { ConfirmDialogProvider } from "./ConfirmDialog";

export type AdminTheme = "light" | "dark" | "auto";
export type AdminFont = "inter" | "poppins" | "jakarta" | "space-grotesk" | "outfit";

const FONT_CSS_VAR: Record<AdminFont, string> = {
  poppins: "var(--font-poppins)",
  inter: "var(--font-inter)",
  jakarta: "var(--font-jakarta)",
  "space-grotesk": "var(--font-space-grotesk)",
  outfit: "var(--font-outfit)",
};

interface ThemeContextValue {
  theme: AdminTheme;
  setTheme: (t: AdminTheme) => void;
  font: AdminFont;
  setFont: (f: AdminFont) => void;
}

const ThemeContext = createContext<ThemeContextValue>({
  theme: "dark",
  setTheme: () => {},
  font: "inter",
  setFont: () => {},
});

export function useAdminTheme() {
  return useContext(ThemeContext);
}

function resolveTheme(t: AdminTheme): "light" | "dark" {
  if (t === "auto") {
    const h = new Date().getHours();
    return h >= 7 && h < 19 ? "light" : "dark";
  }
  return t;
}

/* Keep these in sync with :root / LIGHT_PROPS in src/app/admin/layout.tsx —
   this runtime pass re-applies every managed token on a theme switch. */
const DARK_VARS = `
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
  --at-glass-bg: rgba(24,24,29,0.55); --at-glass-border: rgba(255,255,255,0.1);
  --at-glass-hairline: rgba(255,255,255,0.1); --at-glass-inset: rgba(255,255,255,0.07);
  --at-mesh-1: rgba(99,102,241,0.13); --at-mesh-2: rgba(168,85,247,0.1);
`;

const LIGHT_VARS = `
  --at-bg: #f5f5f5; --at-card: #ffffff; --at-sidebar: #fafafa;
  --at-border: rgba(0,0,0,0.12); --at-border-header: rgba(0,0,0,0.06);
  --at-border-row: rgba(0,0,0,0.04); --at-border-input: rgba(0,0,0,0.15);
  --at-border-input-alt: rgba(0,0,0,0.1); --at-input: rgba(0,0,0,0.04);
  --at-input-alt: rgba(0,0,0,0.06); --at-text: #111111; --at-muted: #666666;
  --at-faint: #aaaaaa; --at-hover: rgba(0,0,0,0.03); --at-row-even: rgba(0,0,0,0.02);
  --at-row-odd: transparent; --at-placeholder: #aaaaaa; --at-prose-p: rgba(0,0,0,0.75);
  --at-prose-strong: #111111; --at-prose-code: #475569; --at-prose-code-bg: rgba(0,0,0,0.05);
  --at-prose-pre-bg: rgba(0,0,0,0.04); --at-prose-pre-border: rgba(0,0,0,0.08);
  --at-toolbar-hover-bg: rgba(0,0,0,0.05); --at-toolbar-hover-fg: #333333;
  --at-toolbar-divider: rgba(0,0,0,0.08); --at-accent: #6366f1; --at-accent-muted: rgba(99,102,241,0.12);
  --at-ok: #15803d; --at-ok-bg: rgba(34,197,94,0.14);
  --at-shadow-card: 0 10px 30px -14px rgba(15,23,42,0.16), 0 2px 6px -2px rgba(15,23,42,0.05);
  --at-shadow-hover: 0 20px 44px -14px rgba(15,23,42,0.22), 0 4px 10px -2px rgba(15,23,42,0.08);
  --at-track: rgba(0,0,0,0.06);
  --at-card-glow: rgba(99,102,241,0.07); --at-card-glow-2: rgba(168,85,247,0.055);
  --at-glass-bg: rgba(255,255,255,0.66); --at-glass-border: rgba(15,23,42,0.08);
  --at-glass-hairline: rgba(15,23,42,0.07); --at-glass-inset: rgba(255,255,255,0.6);
  --at-mesh-1: rgba(99,102,241,0.1); --at-mesh-2: rgba(168,85,247,0.07);
`;

export default function AdminThemeProvider({ children }: { children: React.ReactNode }) {
  const [theme, setThemeState] = useState<AdminTheme>("dark");
  const [font, setFontState] = useState<AdminFont>("inter");
  const resolved = resolveTheme(theme);

  useEffect(() => {
    setThemeState((localStorage.getItem("oox-admin-theme") as AdminTheme) ?? "dark");
    setFontState((localStorage.getItem("oox-admin-font") as AdminFont) ?? "inter");
  }, []);

  useEffect(() => {
    const vars = resolved === "dark" ? DARK_VARS : LIGHT_VARS;
    const root = document.getElementById("admin-shell");
    if (!root) return;
    root.style.colorScheme = resolved;
    vars.split(";").forEach((decl) => {
      const idx = decl.indexOf(":");
      if (idx === -1) return;
      const prop = decl.slice(0, idx).trim();
      const val = decl.slice(idx + 1).trim();
      if (prop.startsWith("--at-")) root.style.setProperty(prop, val);
    });
  }, [resolved]);

  useEffect(() => {
    document.getElementById("admin-shell")?.style.setProperty("--at-font-body", FONT_CSS_VAR[font]);
  }, [font]);

  function setTheme(t: AdminTheme) {
    setThemeState(t);
    localStorage.setItem("oox-admin-theme", t);
  }
  function setFont(f: AdminFont) {
    setFontState(f);
    localStorage.setItem("oox-admin-font", f);
  }

  return (
    <ThemeContext.Provider value={{ theme, setTheme, font, setFont }}>
      <ConfirmDialogProvider>
        <Toaster
          position="bottom-right"
          theme={resolved}
          toastOptions={{ style: { fontFamily: "var(--at-font-body), system-ui, sans-serif", fontSize: 13 } }}
        />
        {children}
      </ConfirmDialogProvider>
    </ThemeContext.Provider>
  );
}
