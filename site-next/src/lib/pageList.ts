import "server-only";
import fs from "fs";
import path from "path";
import { getAllPages, getAllPosts, getServices, getTeam } from "./content";
import { getAllPageEdits } from "./pageEdits";

export interface EditablePage {
  key: string;        // pagemap key / route key
  routePath: string;  // "/about-us/"
  title: string;
  group: "Marketing" | "Blog posts" | "Team profiles" | "Service pages";
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

export function listEditablePages(): EditablePage[] {
  const edits = getAllPageEdits();
  const editedCount = (k: string) =>
    Object.keys(edits[k] ?? {}).filter((id) => !id.startsWith("__seo")).length;

  const out: EditablePage[] = [];
  const push = (routePath: string, title: string, group: EditablePage["group"]) => {
    const key = routeKey(routePath);
    const fields = fieldCount(key);
    if (fields === 0) return;
    out.push({ key, routePath, title, group, fields, edited: editedCount(key) });
  };

  for (const p of getAllPages()) push(p.path, p.title, "Marketing");
  for (const p of getAllPosts()) push(`/${p.slug}/`, p.title, "Blog posts");
  for (const t of getTeam()) push(`/team/${t.slug}/`, t.name, "Team profiles");
  for (const s of getServices()) push(`/service/${s.slug}/`, s.title, "Service pages");

  return out;
}
