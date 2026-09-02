import "server-only";
import fs from "fs";
import path from "path";

/** Shared JSON-on-disk helpers. DATA_FILE paths are built from process.cwd() at
 *  runtime so the deploy must stitch `src/data` into the standalone bundle. */
export function dataFile(name: string): string {
  return path.join(process.cwd(), "src/data", `${name}.json`);
}

/**
 * Parsed-JSON cache, keyed by file mtime. Turns the ~5.7 MB `wikibrain.json`
 * parse from once-per-call into once-per-(process, file version) — a cheap
 * `fs.statSync` guards every read so an out-of-band write (the admin UI runs in
 * a separate route-handler bundle with its own module instance, so its
 * `writeData` can't bust this map directly) is always picked up on the next read.
 */
const cache = new Map<string, { mtimeMs: number; data: unknown }>();

function readJson<T>(name: string): T | undefined {
  const file = dataFile(name);
  let mtimeMs: number;
  try {
    mtimeMs = fs.statSync(file).mtimeMs;
  } catch {
    return undefined;
  }
  const hit = cache.get(name);
  if (hit && hit.mtimeMs === mtimeMs) return hit.data as T;
  try {
    const parsed = JSON.parse(fs.readFileSync(file, "utf-8")) as T;
    cache.set(name, { mtimeMs, data: parsed });
    return parsed;
  } catch {
    return undefined;
  }
}

export function readArray<T>(name: string): T[] {
  return (readJson<T[]>(name) ?? []) as T[];
}

export function readObject<T>(name: string, fallback: T): T {
  const parsed = readJson<Partial<T>>(name);
  return parsed ? ({ ...fallback, ...parsed } as T) : fallback;
}

export function writeData(name: string, data: unknown): void {
  const file = dataFile(name);
  fs.mkdirSync(path.dirname(file), { recursive: true });
  fs.writeFileSync(file, JSON.stringify(data, null, 2) + "\n", "utf-8");
  try {
    cache.set(name, { mtimeMs: fs.statSync(file).mtimeMs, data });
  } catch {
    cache.delete(name);
  }
}
