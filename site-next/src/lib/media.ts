/** Client-safe media path helpers. ooxlimited image values are already
 *  root-absolute (`/wp-content/uploads/…`) or full URLs; nothing to rewrite. */
export function mediaUrl(_folder: string, file: string): string {
  if (!file) return "";
  return file;
}

export function gfxUrl(file: string): string {
  return file || "";
}

export function decodeEntities(s: string): string {
  return (s ?? "")
    .replace(/&amp;/g, "&")
    .replace(/&lt;/g, "<")
    .replace(/&gt;/g, ">")
    .replace(/&quot;/g, '"')
    .replace(/&#0?39;|&apos;|&rsquo;/g, "’")
    .replace(/&nbsp;/g, " ");
}

export function youtubeId(url: string): string | null {
  const m = (url ?? "").match(
    /(?:youtu\.be\/|youtube\.com\/(?:embed\/|watch\?v=|v\/))([\w-]{11})/,
  );
  return m ? m[1] : null;
}
