import "server-only";
import { readObject, writeData } from "./jsonStore";

/**
 * Content edits for every frozen public page, keyed by route key
 * (`home`, `about-us`, `service__co-development`, `team__debela`, `<post-slug>`…).
 * Each value is `{ pagemapId: newValue }`. Applied by `applyPageEdits` at render.
 * Also holds optional per-page SEO overrides under `__seo`.
 */
export type PageEditsFile = Record<string, Record<string, string>>;

export function getAllPageEdits(): PageEditsFile {
  return readObject<PageEditsFile>("pageEdits", {});
}

export function getPageEdits(routeKey: string): Record<string, string> {
  return getAllPageEdits()[routeKey] ?? {};
}

export function savePageEdits(routeKey: string, edits: Record<string, string>): void {
  const all = getAllPageEdits();
  if (Object.keys(edits).length === 0) delete all[routeKey];
  else all[routeKey] = edits;
  writeData("pageEdits", all);
}
