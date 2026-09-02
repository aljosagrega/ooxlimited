import "server-only";
import fs from "fs";
import path from "path";
import * as cheerio from "cheerio";

export interface PagemapEntry {
  id: string;
  kind: "text" | "html" | "image" | "imageAlt" | "href";
  label: string;
  group: string;
  value: string;
}

const PAGEMAP_DIR = path.join(process.cwd(), "src/data/pagemaps");

export function getPagemap(routeKey: string): PagemapEntry[] {
  try {
    const raw = fs.readFileSync(path.join(PAGEMAP_DIR, `${routeKey}.json`), "utf-8");
    return (JSON.parse(raw).entries ?? []) as PagemapEntry[];
  } catch {
    return [];
  }
}

export function hasPagemap(routeKey: string): boolean {
  return fs.existsSync(path.join(PAGEMAP_DIR, `${routeKey}.json`));
}

/**
 * Apply a page's admin edits to its frozen body HTML. `edits` is `{ id: value }`
 * keyed by the `data-oox-e` id from the pagemap. Unedited ids are left as frozen.
 * Layout / classes / structure are never touched — only leaf text, image src,
 * alt text and link targets.
 */
export function applyPageEdits(
  bodyHtml: string,
  pagemap: PagemapEntry[],
  edits: Record<string, string> | undefined | null,
): string {
  if (!edits || Object.keys(edits).length === 0) return bodyHtml;
  const byId = new Map(pagemap.map((e) => [e.id, e]));

  const $ = cheerio.load(bodyHtml, {}, false);
  for (const [id, value] of Object.entries(edits)) {
    const entry = byId.get(id);
    if (!entry || value == null) continue;

    if (entry.kind === "imageAlt") {
      $(`[data-oox-e-alt="${id}"]`).attr("alt", value);
      continue;
    }
    if (entry.kind === "href") {
      $(`[data-oox-e-href="${id}"]`).attr("href", value);
      continue;
    }
    const node = $(`[data-oox-e="${id}"]`).first();
    if (!node.length) continue;
    if (entry.kind === "image") {
      node.attr("src", value);
      node.removeAttr("srcset");
    } else if (entry.kind === "html") {
      node.html(value);
    } else {
      node.text(value);
    }
  }
  return $.html();
}
