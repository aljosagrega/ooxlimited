import "server-only";
import fs from "fs";
import path from "path";
import { getAllPages } from "./content";
import { getAllPageEdits } from "./pageEdits";

export interface EditablePage {
  key: string;        // pagemap key / route key
  routePath: string;  // "/about-us/"
  title: string;
  group: "Marketing";
  fields: number;     // pagemap field count
  edited: number;     // how many have overrides
}

const PAGEMAP_DIR = path.join(process.cwd(), "src/data/pagemaps");

function fieldCount(key: string): number {
  try {
    return (JSON.parse(fs.readFileSync(path.join(PAGEMAP_DIR, `${key}.json`), "utf-8")).entries ?? []).length;
  } catch {
    return 0;
  }
}
function routeKey(p: string): string {
  if (p === "/") return "home";
  return p.replace(/^\/|\/$/g, "").replace(/\//g, "__") || "home";
}

/**
 * Pages editable under /admin/pages = the marketing pages only. Everything else
 * has its own section:
 *   - service page copy  -> Services  (structured fields + Page content tab)
 *   - blog article bodies -> Blog posts
 *   - team profiles       -> Team
 * — swapped into the frozen pages by singleContent.ts / the service editor.
 */
export function listEditablePages(): EditablePage[] {
  const edits = getAllPageEdits();
  const editedCount = (k: string) =>
    Object.keys(edits[k] ?? {}).filter((id) => !id.startsWith("__seo")).length;

  const out: EditablePage[] = [];
  for (const p of getAllPages()) {
    const key = routeKey(p.path);
    const fields = fieldCount(key);
    if (fields === 0) continue;
    out.push({ key, routePath: p.path, title: p.title, group: "Marketing", fields, edited: editedCount(key) });
  }
  return out;
}
