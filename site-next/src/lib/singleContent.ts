import "server-only";
import * as cheerio from "cheerio";
import { sanitizeBodyHtml, sanitizeInline } from "./sanitize";
import { getPost, getTeamMember } from "./content";
import type { TeamMember } from "./types";

/**
 * The blog-post / team-member / service SINGLE pages are frozen and render
 * pixel-perfect (the frozen markup carries its own scoped inline <style>).
 *
 * Blog posts and team profiles are NOT edited under /admin/pages — they have
 * their own sections (Blog posts / Team). This swaps their structured data
 * (posts.json / team.json) into the frozen markup so those edits go live.
 * Layout, classes and styling are never touched.
 *
 * Service single pages keep pagemap editing (their body is bespoke Elementor
 * content that isn't captured by the services collection).
 */
export function applySingleContent(routePath: string, bodyHtml: string): string {
  const teamMatch = routePath.match(/^\/team\/([^/]+)\/$/);
  if (teamMatch) return patchTeam(teamMatch[1], bodyHtml);

  const slug = routePath.replace(/^\/|\/$/g, "");
  if (slug && !slug.includes("/")) return patchPost(slug, bodyHtml);

  return bodyHtml;
}

/* ------------------------------------------------------------- blog post ---- */

function patchPost(slug: string, bodyHtml: string): string {
  const post = getPost(slug) as (ReturnType<typeof getPost> & { bodyDirty?: boolean }) | null;
  if (!post?.bodyDirty || !post.bodyHtml) return bodyHtml;

  const $ = cheerio.load(bodyHtml, {}, false);
  const art = $("article.oox-blog-article").first();
  if (!art.length) return bodyHtml;
  art.html(sanitizeBodyHtml(post.bodyHtml));
  return $.html();
}

/* ----------------------------------------------------------- team member ---- */

function patchTeam(slug: string, bodyHtml: string): string {
  const m = getTeamMember(slug) as (ReturnType<typeof getTeamMember> & { teamDirty?: boolean }) | null;
  if (!m?.teamDirty) return bodyHtml;

  const $ = cheerio.load(bodyHtml, {}, false);
  fillTeamMember($, m);
  return $.html();
}

/**
 * Fill a team single page's frozen slots (name, photo, bio, skills, programs,
 * Q&A) from a team.json record. Shared by patchTeam() above — an already-
 * live member's own frozen snapshot, only once edited in the admin — and
 * renderTemplatedTeamMember() in teamGridRender.ts, which fills the same
 * slots on the shared `_team-template` shell for a member created purely in
 * the CMS, with no snapshot of their own.
 */
export function fillTeamMember($: cheerio.CheerioAPI, m: TeamMember) {
  // name / role
  setText($, ".single-team-hero .entry-title, h1.entry-title, h2.entry-title", m.name);
  setText($, ".team-position", m.position);

  // photo
  if (m.photo?.url) {
    const img = $(".single-team-hero .post-thumbnail img, .single-team-hero img.wp-post-image").first();
    if (img.length) {
      img.attr("src", m.photo.url).removeAttr("srcset").removeAttr("sizes");
      if (m.photo.alt) img.attr("alt", m.photo.alt);
    }
  }

  // bio — one <p> per blank-line-separated paragraph, reusing the frozen <p>
  // slots. The frozen markup carries TWO .team-content blocks — a hidden
  // mobile-inline copy near the photo and the visible desktop copy in the
  // main column, swapped by a breakpoint — each with its own <p> slots. A
  // plain `.team-content p` selector pools both into one list, so filling 2
  // paragraphs drains the FIRST block's slots and deletes the SECOND block's
  // (frequently the visible one) as "surplus". Reflow each block separately.
  const bioParas = m.bio ? m.bio.split(/\n{2,}/).map((p) => p.trim()).filter(Boolean) : [];
  $(".team-content").each((_, container) => {
    reflowNodes($, $(container).find("p").toArray(), bioParas, (el, text) => {
      el.html(`<span style="font-weight: 400;">${escapeHtml(text)}</span>`);
    });
  });

  // skills / programs
  reflowList($, ".team-skills li", m.skills, (el, text) => { el.text(text); });
  reflowList($, ".team-programs li", m.programs, (el, text) => { el.text(text); });

  // Q&A accordion
  const qaItems = $(".team-qa-item").toArray();
  qaItems.forEach((li, i) => {
    const qa = m.qa[i];
    if (!qa) {
      $(li).remove();
      return;
    }
    $(li).find(".team-qa-question").first().text(qa.question);
    const ans = $(li).find(".team-qa-answer").first();
    if (ans.length) ans.html(sanitizeInline(qa.answer));
  });
}

/* --------------------------------------------------------------- helpers ---- */

function setText($: cheerio.CheerioAPI, sel: string, value: string) {
  if (!value) return;
  $(sel).each((_, el) => {
    $(el).text(value);
  });
}

/** Rewrite the text of existing `sel` nodes from `values`, position by position.
 *  Extra frozen nodes beyond `values.length` are removed; if `values` is longer
 *  than the frozen slots the surplus is dropped (the frozen layout has no room). */
/* eslint-disable @typescript-eslint/no-explicit-any */
function reflowList(
  $: cheerio.CheerioAPI,
  sel: string,
  values: string[],
  apply: (el: any, text: string) => void,
) {
  reflowNodes($, $(sel).toArray(), values, apply);
}

/** Same as reflowList, but against an already-collected node array — for a
 *  slot that must be reflowed once per duplicate container (see the bio
 *  blocks in fillTeamMember) rather than pooled across all of them. */
function reflowNodes(
  $: cheerio.CheerioAPI,
  nodes: any[],
  values: string[],
  apply: (el: any, text: string) => void,
) {
  if (!nodes.length || !values.length) return;
  nodes.forEach((node, i) => {
    if (i < values.length) {
      apply($(node), values[i]);
    } else {
      $(node).remove();
    }
  });
}
/* eslint-enable @typescript-eslint/no-explicit-any */

function escapeHtml(s: string): string {
  return s.replace(/[&<>]/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;" }[c]!));
}
