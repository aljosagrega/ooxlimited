import "server-only";
import { readArray, readObject, writeData } from "./jsonStore";
import type {
  Author, Menus, Page, Post, Redirect, Service, SiteSettings, TeamMember,
} from "./types";

/* --------------------------------------------------------------- posts ---- */

/** A post is public unless explicitly unpublished. Migrated posts carry no
 *  `published` field and are treated as live. */
export function isPostLive(p: Post): boolean {
  return p.published !== false;
}

/** Public post list. Drafts (`published: false`) are excluded unless asked for. */
export function getAllPosts(includeDrafts = false): Post[] {
  const rows = readArray<Post>("posts").sort((a, b) => +new Date(b.date) - +new Date(a.date));
  return includeDrafts ? rows : rows.filter(isPostLive);
}
export function getPost(slug: string): Post | null {
  return getAllPosts().find((p) => p.slug === slug) ?? null;
}
export function getPostByOldSlug(slug: string): Post | null {
  return getAllPosts().find((p) => p.oldSlugs?.includes(slug)) ?? null;
}
export function savePosts(rows: Post[]): void {
  writeData("posts", rows);
}

/** Display byline for a post: the linked Author record, else the legacy name. */
export function postAuthorName(p: Post): string {
  if (p.authorId) return getAuthor(p.authorId)?.name || p.author || "";
  return p.author || "";
}

/* ------------------------------------------------------------- authors ---- */

export function getAuthors(): Author[] {
  return readArray<Author>("authors");
}
export function getAuthor(id: number): Author | null {
  return getAuthors().find((a) => Number(a.id) === Number(id)) ?? null;
}
export function saveAuthors(rows: Author[]): void {
  writeData("authors", rows);
}

/* ---------------------------------------------------------------- team ---- */

export function getTeam(): TeamMember[] {
  return readArray<TeamMember>("team").sort((a, b) => a.ord - b.ord);
}
export function getTeamMember(slug: string): TeamMember | null {
  return getTeam().find((t) => t.slug === slug) ?? null;
}
export function saveTeam(rows: TeamMember[]): void {
  writeData("team", rows);
}

/* ------------------------------------------------------------ services ---- */

export function getServices(): Service[] {
  return readArray<Service>("services").sort((a, b) => a.ord - b.ord);
}
export function getService(slug: string): Service | null {
  return getServices().find((s) => s.slug === slug) ?? null;
}
export function saveServices(rows: Service[]): void {
  writeData("services", rows);
}

/* --------------------------------------------------------------- pages ---- */

export function getAllPages(): Page[] {
  return readArray<Page>("pages");
}
export function getPageByPath(path: string): Page | null {
  const norm = path === "/" ? "/" : path.endsWith("/") ? path : `${path}/`;
  return getAllPages().find((p) => p.path === norm) ?? null;
}
export function savePages(rows: Page[]): void {
  writeData("pages", rows);
}

/* --------------------------------------------------------------- menus ---- */

export function getMenus(): Menus {
  return readObject<Menus>("menus", { main: [], footer: [] });
}
export function saveMenus(m: Menus): void {
  writeData("menus", m);
}

/* ------------------------------------------------------------ settings ---- */

const SETTINGS_DEFAULTS: SiteSettings = {
  title: "OOX Limited",
  tagline: "Game & App Development Studio",
  contactEmail: "info@ooxlimited.com",
  contactRecipients: ["info@ooxlimited.com"],
  mailFromName: "OOX Limited",
  mailchimpListId: "",
  gaId: "",
  headScripts: "",
  socialLinks: [],
};

export function getSiteSettings(): SiteSettings {
  return readObject<SiteSettings>("siteSettings", SETTINGS_DEFAULTS);
}
export function saveSiteSettings(partial: Partial<SiteSettings>): SiteSettings {
  const merged = { ...getSiteSettings(), ...partial };
  writeData("siteSettings", merged);
  return merged;
}

/* ----------------------------------------------------------- redirects ---- */

export function getRedirects(): Redirect[] {
  return readArray<Redirect>("redirects");
}

/* --------------------------------------------------------- submissions ---- */

export interface Submission {
  id: number;
  createdAt: string;
  kind: "contact" | "newsletter";
  handled?: boolean;
  email: string;
  name?: string;
  message?: string;
  optIn?: boolean;
  emailStatus?: "sent" | "failed" | "skipped";
  ip?: string;
  note?: string;
}

export function getSubmissions(): Submission[] {
  return readArray<Submission>("submissions");
}
export function saveSubmissions(rows: Submission[]): void {
  writeData("submissions", rows);
}
export function recordSubmission(
  data: Omit<Submission, "id" | "createdAt">,
): Submission {
  const rows = getSubmissions();
  const id = rows.reduce((max, r) => Math.max(max, Number(r.id) || 0), 0) + 1;
  const row: Submission = { id, createdAt: new Date().toISOString(), ...data };
  rows.push(row);
  saveSubmissions(rows);
  return row;
}
export function setSubmissionEmailStatus(id: number, emailStatus: Submission["emailStatus"]): void {
  const rows = getSubmissions();
  const row = rows.find((r) => Number(r.id) === id);
  if (!row) return;
  row.emailStatus = emailStatus;
  saveSubmissions(rows);
}
