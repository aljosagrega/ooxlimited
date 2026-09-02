"use client";

import { useEffect } from "react";

export interface FrozenScript {
  src?: string;
  code?: string;
  module?: boolean;
}

/**
 * Re-executes the frozen page's <script> stack in document order after the
 * markup has mounted. `dangerouslySetInnerHTML` never runs scripts, so the
 * legacy WordPress/Elementor/theme JS (jQuery → elementor-frontend → theme
 * frontend, plus inline Elementor config) is replayed here instead.
 */
export default function FrozenScripts({ scripts }: { scripts: FrozenScript[] }) {
  useEffect(() => {
    // The legacy scripts declare top-level `let`/`const` globals and are not
    // safe to run twice (client nav, StrictMode). Run the replay once per full
    // page load; a real navigation reloads the document via the frozen <a>s.
    const w = window as unknown as { __frozenScriptsRun?: boolean };
    if (w.__frozenScriptsRun) return;
    w.__frozenScriptsRun = true;

    let cancelled = false;
    const added: HTMLScriptElement[] = [];

    const loadOne = (s: FrozenScript) =>
      new Promise<void>((resolve) => {
        if (cancelled) return resolve();
        const el = document.createElement("script");
        if (s.module) el.type = "module";
        if (s.src) {
          el.src = s.src;
          el.async = false;
          el.onload = () => resolve();
          el.onerror = () => resolve();
          document.body.appendChild(el);
          added.push(el);
        } else if (s.code) {
          // Isolate a bad inline script so it can't abort the rest of the chain.
          try {
            new Function(s.code); // parse-check
            el.textContent = s.code;
            document.body.appendChild(el);
            added.push(el);
          } catch {
            /* skip unparseable inline script */
          }
          resolve();
        } else {
          resolve();
        }
      });

    (async () => {
      for (const s of scripts) {
        if (cancelled) break;
        if (typeof window !== "undefined" && (window as unknown as { __fsdbg?: boolean }).__fsdbg) {
          console.log("[fs]", s.src ? s.src.split("?")[0] : "inline:" + (s.code || "").slice(0, 40));
        }
        await loadOne(s);
      }
      // We're past DOMContentLoaded / window.load by the time these run, so the
      // libraries that self-init on those events never fire. Kick them manually.
      if (!cancelled) kickLegacyRuntime();
    })();

    return () => {
      cancelled = true;
      // Intentionally do NOT remove the injected <script> elements or reset the
      // run flag — the legacy globals they defined can't be cleanly torn down,
      // and removing the tags doesn't undo them anyway.
    };
  }, [scripts]);

  return null;
}

/* eslint-disable @typescript-eslint/no-explicit-any */
function kickLegacyRuntime() {
  const w = window as any;
  const $ = w.jQuery;

  try {
    document.dispatchEvent(new Event("DOMContentLoaded", { bubbles: true }));
    window.dispatchEvent(new Event("load"));
  } catch { /* ignore */ }

  try { $?.(document).trigger("ready"); } catch { /* ignore */ }

  // Elementor: `elementorFrontend.init()` walks the DOM and attaches every
  // widget handler + registers motion-fx / animation ScrollTriggers itself.
  // It normally runs on document.ready (already fired), so call it once here.
  // Do NOT also iterate widgets manually — that double-registers ScrollTriggers.
  try {
    const ef = w.elementorFrontend;
    if (ef && !ef.isEditMode?.()) {
      $?.(w).trigger("elementor/frontend/init");
      if (!ef.__ooxInited && ef.init) { ef.init(); ef.__ooxInited = true; }
    }
  } catch { /* ignore */ }

  // GSAP ScrollTrigger (omero timeline / scroll-galleries / pinned sections)
  // computes trigger positions at init — before the frozen DOM has settled.
  // Refresh once layout is stable, and again after fonts/images land.
  const refreshST = () => {
    try {
      (w.ScrollTrigger || w.gsap?.core?.globals?.().ScrollTrigger)?.refresh?.();
      w.gsap?.ScrollTrigger?.refresh?.();
    } catch { /* ignore */ }
  };

  // A delayed second pass — some handlers register a tick later.
  setTimeout(() => {
    try {
      w.jQuery?.(w).trigger("resize");
      w.elementorFrontend?.elements?.$window?.trigger?.("resize");
    } catch { /* ignore */ }
    refreshST();
  }, 400);
  setTimeout(refreshST, 1200);
  try {
    if (document.fonts?.ready) document.fonts.ready.then(refreshST);
    window.addEventListener("load", refreshST, { once: true });
  } catch { /* ignore */ }
}
/* eslint-enable @typescript-eslint/no-explicit-any */
