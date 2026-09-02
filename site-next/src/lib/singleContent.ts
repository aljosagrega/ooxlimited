import "server-only";
import * as cheerio from "cheerio";
import { sanitizeBodyHtml } from "./sanitize";
import { getPost } from "./content";

/**
 * The blog-post / team-member / service SINGLE pages are frozen and render
 * pixel-perfect (the frozen markup carries its own scoped inline <style>).
 *
 * Their structured data (posts.json / team.json / services.json) is migrated and
 * editable in the admin, but only the blog-post BODY is wired back to the live
 * page — and only once it has actually been edited (`post.bodyDirty`), so an
 * untouched post keeps its exact frozen rendering.
 *
 * Team / service single-page content editing is done through the pagemap
 * (src/data/pagemaps/team__<slug>.json) + PageEditor, same as marketing pages.
 */
export function applySingleContent(routePath: string, bodyHtml: string): string {
  const slug = routePath.replace(/^\/|\/$/g, "");
  if (!slug || slug.includes("/")) return bodyHtml;

  const post = getPost(slug) as (ReturnType<typeof getPost> & { bodyDirty?: boolean }) | null;
  if (!post?.bodyDirty || !post.bodyHtml) return bodyHtml;

  const $ = cheerio.load(bodyHtml, {}, false);
  const art = $("article.oox-blog-article").first();
  if (!art.length) return bodyHtml;
  art.html(sanitizeBodyHtml(post.bodyHtml));
  return $.html();
}
