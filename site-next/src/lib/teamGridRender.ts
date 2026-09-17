import "server-only";
import * as cheerio from "cheerio";
import { getTeam } from "./content";
import type { TeamMember } from "./types";

/* eslint-disable @typescript-eslint/no-explicit-any */

/**
 * The "Our Team" roster grid ("Meet our development team", /game-development-team/)
 * shipped from the WordPress migration as a frozen, static list — every member's
 * name, photo and link baked into the HTML. The admin's Team section (team.json)
 * only ever drove each member's own /team/<slug>/ profile page, never this grid,
 * so adding or removing someone in the CMS silently did nothing here. Reported by
 * the client: "deleted Ozren and added Čudić — the change didn't go through."
 *
 * This rebuilds the grid from team.json at render time, the same way
 * applyBlogIndex() rebuilds the blog card grid from posts.json: clone the first
 * frozen <li> as a template, empty the list, refill it from live data. Layout,
 * classes and styling are untouched — only membership, text, links and photos
 * change.
 *
 * The theme's card markup only ever renders a LinkedIn icon (no fb/x/ig glyphs
 * exist in this widget's CSS, even though the socials JSON shape has room for
 * them) — see socials.in below.
 */

const TEAM_ROSTER_PATH = "/game-development-team/";
const FALLBACK_PHOTO = "/og-default.png";

function esc(s: unknown): string {
  return String(s ?? "").replace(/[&<>"]/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[c] as string));
}

function fillTeamCard($: any, card: any, m: TeamMember) {
  const href = `/team/${m.slug}/`;

  const thumbLink = card.find(".team-avatar-wrapper figure a").first();
  thumbLink.attr("href", href).attr("title", m.name);
  const img = thumbLink.find("img").first();
  if (img.length) {
    img
      .attr("src", m.photo?.url || FALLBACK_PHOTO)
      .attr("alt", m.photo?.alt || m.name)
      .removeAttr("srcset")
      .removeAttr("sizes");
  }

  // Only LinkedIn ever renders here — see file header.
  const linkedin = m.socials?.in;
  const socialsList = card.find(".team_socials").first();
  if (linkedin) {
    const item =
      `<li><a class="omero-icon-socical omero-path-border omero-path-wrapper" href="${esc(linkedin)}" target="_blank">` +
      `<i class="omero-icon-linkedin"></i></a></li>`;
    if (socialsList.length) socialsList.html(item);
    else card.find(".team-avatar-wrapper").append(`<ol class="team_socials">${item}</ol>`);
  } else if (socialsList.length) {
    socialsList.remove();
  }

  card.find(".team-loop-title a").first().text(m.name).attr("href", href);
  card.find(".team-position").first().text(m.position || "");
}

/**
 * Rebuild the roster grid for /game-development-team/ from live team.json.
 * No-op for any other route, or if the frozen markup lacks the expected
 * list/card containers.
 */
export function applyTeamGrid(routePath: string, body: string): string {
  if (routePath !== TEAM_ROSTER_PATH) return body;

  const members = getTeam();
  if (!members.length) return body;

  const $ = cheerio.load(body, {}, false);
  const list = $("ul.omero-team.omero-list-wrapper").first();
  const cardProto = list.find("li.omero-item").first();
  if (!list.length || !cardProto.length) return body;

  const proto = cardProto.clone();
  list.empty();
  for (const m of members) {
    const c = proto.clone();
    fillTeamCard($, c, m);
    list.append(c);
  }

  return $.html();
}
