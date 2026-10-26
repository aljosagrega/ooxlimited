import redirectsJson from "@/data/redirects.json";
import postsJson from "@/data/posts.json";

type Redirect = { from: string; to: string; type: number };
export type RedirectHit = { to: string; type: number };

export const normalize = (p: string) => {
  if (!p) return "/";
  let out = p.startsWith("/") ? p : `/${p}`;
  out = out.replace(/\/{2,}/g, "/");
  if (!out.endsWith("/") && !/\.[a-z0-9]+$/i.test(out)) out += "/";
  return out;
};

export function buildRedirectTable(
  redirects: Redirect[],
  posts: { slug: string; oldSlugs?: string[] }[],
): Map<string, RedirectHit> {
  const table = new Map<string, RedirectHit>();
  // Explicit redirect table (Yoast Premium export + slug changes), keyed by
  // normalized source path.
  for (const r of redirects) {
    table.set(normalize(r.from), {
      to: r.to.startsWith("http") ? r.to : normalize(r.to),
      type: r.type === 302 || r.type === 307 ? 307 : 308,
    });
  }
  // Every post's historical slugs -> its current canonical URL.
  for (const p of posts) {
    for (const old of p.oldSlugs ?? []) {
      table.set(normalize(old), { to: `/${p.slug}/`, type: 308 });
    }
  }
  return table;
}

// Built from the JSON bundled at build time, not the server's live content: the
// middleware can't read the disk. The sitemap consults the same table, so it
// never lists a URL the middleware redirects.
const TABLE = buildRedirectTable(redirectsJson as Redirect[], postsJson);

export function redirectFor(pathname: string): RedirectHit | undefined {
  return TABLE.get(normalize(pathname));
}
