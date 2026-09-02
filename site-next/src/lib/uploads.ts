import "server-only";
import fs from "fs";
import path from "path";

const UPLOAD_DIR = path.join(process.cwd(), "public", "media", "uploads");
export const MAX_UPLOAD_SIZE = 5 * 1024 * 1024; // 5 MB

export function saveUpload(buffer: Buffer, originalName: string): string {
  const ext = path.extname(originalName) || ".jpg";
  const base = path.basename(originalName, ext)
    .replace(/[^a-z0-9]/gi, "-")
    .toLowerCase()
    .slice(0, 40);
  const filename = `${Date.now()}-${base}${ext}`;

  fs.mkdirSync(UPLOAD_DIR, { recursive: true });
  fs.writeFileSync(path.join(UPLOAD_DIR, filename), buffer);

  return `/media/uploads/${filename}`;
}
