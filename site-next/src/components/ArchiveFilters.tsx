"use client";

import { useEffect } from "react";

/**
 * Auto-submit for the archive sidebar (see archiveRender.sidebarHtml).
 *
 * The sidebar is a plain GET form, so search and the category multi-select
 * already work without JavaScript — this only removes the extra click by
 * submitting when a checkbox changes. Nothing here is required for the filters
 * to function; the <noscript> Apply button covers the no-JS path.
 */
export default function ArchiveFilters() {
  useEffect(() => {
    const form = document.querySelector<HTMLFormElement>(".oox-arch__filters");
    if (!form) return;

    const onChange = (e: Event) => {
      const t = e.target as HTMLInputElement | null;
      if (t?.type !== "checkbox") return;
      // Changing a filter starts a new result set — never keep the old page.
      form.querySelector<HTMLInputElement>('input[name="page"]')?.remove();
      // An empty search box would submit as "?q=" and sit in the URL forever;
      // a disabled control is simply not submitted. Re-enabled straight after
      // so the field stays usable if the navigation is slow or cancelled.
      const q = form.querySelector<HTMLInputElement>('input[name="q"]');
      const blank = !!q && q.value.trim() === "";
      if (blank && q) q.disabled = true;
      form.requestSubmit();
      if (blank && q) q.disabled = false;
    };

    form.addEventListener("change", onChange);
    return () => form.removeEventListener("change", onChange);
  }, []);

  return null;
}
