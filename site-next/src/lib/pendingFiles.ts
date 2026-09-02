import "server-only";
import fs from "fs";
import path from "path";

export type PendingSection =
  | "pages"
  | "wikibrain"
  | "wikibrainCategories"
  | "ewallets"
  | "authors"
  | "reviews"
  | "videos"
  | "banners";

export const PENDING_SECTIONS: PendingSection[] = [
  "pages",
  "wikibrain",
  "wikibrainCategories",
  "ewallets",
  "authors",
  "reviews",
  "videos",
  "banners",
];

function pendingDir(section: PendingSection) {
  return path.join(process.cwd(), "src/data/pending", section);
}

export function readPendingFiles(section: PendingSection): Record<string, unknown>[] {
  const dir = pendingDir(section);
  if (!fs.existsSync(dir)) return [];
  return fs
    .readdirSync(dir)
    .filter((f) => f.endsWith(".json"))
    .map((f) => {
      try {
        return JSON.parse(fs.readFileSync(path.join(dir, f), "utf-8")) as Record<string, unknown>;
      } catch {
        return null;
      }
    })
    .filter((v): v is Record<string, unknown> => v !== null);
}

export function writePendingFile(section: PendingSection, key: string, payload: unknown) {
  const dir = pendingDir(section);
  fs.mkdirSync(dir, { recursive: true });
  const filePath = path.join(dir, `${key}.json`);
  fs.writeFileSync(filePath, JSON.stringify(payload, null, 2), "utf-8");
  return path.relative(process.cwd(), filePath);
}
