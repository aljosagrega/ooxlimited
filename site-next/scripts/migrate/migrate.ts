/**
 * One-off migration: the local ooxlimited WordPress DB -> src/data/*.json
 *
 * Prereq: `docker compose up -d` in the repo root (MariaDB on 127.0.0.1:3307).
 * Run:    `npm run migrate`   (from site-next/)
 *
 * Reads wp_posts / wp_postmeta / wp_terms / wp_options and writes the flat JSON
 * content store the Next app serves. Re-runnable — it overwrites the JSON files.
 * `pages.json` field maps and `frozen/` HTML are produced separately by
 * scripts/snapshot/freeze.ts and are NOT touched here.
 */
import fs from "fs";
import path from "path";
import mysql from "mysql2/promise";
import phpUnserialize from "./php-unserialize";

const OUT = path.join(__dirname, "../../src/data");

const DB = {
  host: process.env.OOX_DB_HOST || "127.0.0.1",
  port: Number(process.env.OOX_DB_PORT || 3307),
  user: process.env.OOX_DB_USER || "wp",
  password: process.env.OOX_DB_PASSWORD || "wp",
  database: process.env.OOX_DB_NAME || "ooxlimited",
  // keep DATETIME columns as "YYYY-MM-DD HH:MM:SS" strings, not JS Date objects
  dateStrings: true as const,
};

type Row = Record<string, unknown>;
const s = (v: unknown): string => (v == null ? "" : String(v));
const clean = (v: unknown): string => s(v).trim();
const decodeEntities = (v: string): string =>
  v
    .replace(/&#0*38;|&amp;/g, "&")
    .replace(/&#8217;|&#x2019;/g, "’")
    .replace(/&#8216;|&#x2018;/g, "‘")
    .replace(/&#8220;/g, "“")
    .replace(/&#8221;/g, "”")
    .replace(/&#8211;/g, "–")
    .replace(/&#8212;/g, "—")
    .replace(/&#8230;/g, "…")
    .replace(/&#0*39;|&#x27;|&apos;/g, "'")
    .replace(/&quot;/g, '"')
    .replace(/&nbsp;/g, " ")
    .replace(/&lt;/g, "<")
    .replace(/&gt;/g, ">");

function writeJson(name: string, data: unknown) {
  fs.mkdirSync(OUT, { recursive: true });
  fs.writeFileSync(path.join(OUT, name), JSON.stringify(data, null, 2) + "\n", "utf-8");
  const n = Array.isArray(data) ? `${data.length} records` : "object";
  console.log(`  wrote ${name} (${n})`);
}

async function main() {
  const db = await mysql.createConnection(DB);
  const q = async (sql: string, params: unknown[] = []): Promise<Row[]> => {
    const [rows] = await db.query(sql, params);
    return rows as Row[];
  };

  // ------------------------------------------------------------- options ------
  const optRows = await q(
    `SELECT option_name, option_value FROM wp_options WHERE option_name IN
      ('blogname','blogdescription','siteurl','home','page_on_front','page_for_posts',
       'sidebars_widgets','wpseo_titles','wpseo_social','googlesitekit_analytics-4_settings')`,
  );
  const opt: Record<string, string> = {};
  for (const r of optRows) opt[s(r.option_name)] = s(r.option_value);

  const LIVE_ORIGIN = "https://ooxlimited.com";

  // -------------------------------------------------- attachment url index ----
  const attRows = await q(
    `SELECT p.ID, p.guid, pm.meta_value AS file
       FROM wp_posts p
       LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_wp_attached_file'
      WHERE p.post_type = 'attachment'`,
  );
  const uploadUrl = (file: string) => `/wp-content/uploads/${file.replace(/^\/+/, "")}`;
  const attById = new Map<number, string>();
  for (const r of attRows) {
    const file = clean(r.file);
    const url = file
      ? uploadUrl(file)
      : s(r.guid).replace(LIVE_ORIGIN, "").replace(/^https?:\/\/localhost:8080/, "");
    attById.set(Number(r.ID), url);
  }
  const attMeta = await q(
    `SELECT post_id, meta_value FROM wp_postmeta WHERE meta_key = '_wp_attachment_image_alt'`,
  );
  const altById = new Map<number, string>();
  for (const r of attMeta) altById.set(Number(r.post_id), clean(r.meta_value));

  // ------------------------------------------------------------ postmeta ------
  async function metaFor(ids: number[]): Promise<Map<number, Record<string, string[]>>> {
    const out = new Map<number, Record<string, string[]>>();
    if (!ids.length) return out;
    const rows = await q(
      `SELECT post_id, meta_key, meta_value FROM wp_postmeta WHERE post_id IN (${ids.map(() => "?").join(",")})`,
      ids,
    );
    for (const r of rows) {
      const pid = Number(r.post_id);
      const m = out.get(pid) ?? {};
      (m[s(r.meta_key)] ??= []).push(s(r.meta_value));
      out.set(pid, m);
    }
    return out;
  }
  const first = (m: Record<string, string[]> | undefined, k: string) => m?.[k]?.[0] ?? "";

  // ------------------------------------------------------------- terms --------
  async function termsFor(ids: number[], taxonomy: string) {
    const out = new Map<number, { id: number; name: string; slug: string }[]>();
    if (!ids.length) return out;
    const rows = await q(
      `SELECT tr.object_id, t.term_id, t.name, t.slug
         FROM wp_term_relationships tr
         JOIN wp_term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
         JOIN wp_terms t ON t.term_id = tt.term_id
        WHERE tt.taxonomy = ? AND tr.object_id IN (${ids.map(() => "?").join(",")})`,
      [taxonomy, ...ids],
    );
    for (const r of rows) {
      const pid = Number(r.object_id);
      const list = out.get(pid) ?? [];
      list.push({ id: Number(r.term_id), name: decodeEntities(clean(r.name)), slug: s(r.slug) });
      out.set(pid, list);
    }
    return out;
  }

  // -------------------------------------------------------------- users ------
  const userRows = await q(`SELECT ID, display_name, user_nicename FROM wp_users`);
  const userById = new Map<number, { name: string; slug: string }>();
  for (const r of userRows)
    userById.set(Number(r.ID), { name: clean(r.display_name), slug: s(r.user_nicename) });

  /** Yoast per-post SEO overrides from postmeta. */
  function yoast(m: Record<string, string[]> | undefined) {
    const title = clean(first(m, "_yoast_wpseo_title"));
    const desc = clean(first(m, "_yoast_wpseo_metadesc"));
    const canonical = clean(first(m, "_yoast_wpseo_canonical"));
    const robotsNoindex = first(m, "_yoast_wpseo_meta-robots-noindex"); // "1" = noindex
    const ogTitle = clean(first(m, "_yoast_wpseo_opengraph-title"));
    const ogDesc = clean(first(m, "_yoast_wpseo_opengraph-description"));
    const ogImage = clean(first(m, "_yoast_wpseo_opengraph-image"));
    const out: Record<string, unknown> = {};
    if (title) out.metaTitle = decodeEntities(title).replace(/ ?%%.*?%%/g, "").trim() || undefined;
    if (title) out.metaTitleRaw = title;
    if (desc) out.metaDescription = decodeEntities(desc);
    if (canonical) out.canonicalUrl = canonical.replace(LIVE_ORIGIN, "");
    if (robotsNoindex === "1") out.noindex = true;
    if (ogTitle) out.ogTitle = decodeEntities(ogTitle);
    if (ogDesc) out.ogDesc = decodeEntities(ogDesc);
    if (ogImage) out.ogImage = ogImage.replace(LIVE_ORIGIN, "");
    return out;
  }

  const imgFromThumb = (m: Record<string, string[]> | undefined) => {
    const tid = Number(first(m, "_thumbnail_id"));
    return tid ? { url: attById.get(tid) ?? "", alt: altById.get(tid) ?? "" } : null;
  };

  // ============================ POSTS (blog) ================================
  {
    const rows = await q(
      `SELECT ID, post_title, post_name, post_excerpt, post_content, post_date_gmt,
              post_modified_gmt, post_author, menu_order
         FROM wp_posts WHERE post_type='post' AND post_status='publish'
        ORDER BY post_date_gmt DESC`,
    );
    const ids = rows.map((r) => Number(r.ID));
    const meta = await metaFor(ids);
    const cats = await termsFor(ids, "category");
    const tags = await termsFor(ids, "post_tag");
    const oldSlugs = await q(
      `SELECT post_id, meta_value FROM wp_postmeta WHERE meta_key='_wp_old_slug' AND post_id IN (${ids.map(() => "?").join(",")})`,
      ids,
    );
    const oldSlugById = new Map<number, string[]>();
    for (const r of oldSlugs) {
      const pid = Number(r.post_id);
      oldSlugById.set(pid, [...(oldSlugById.get(pid) ?? []), s(r.meta_value)]);
    }

    const posts = rows.map((r) => {
      const id = Number(r.ID);
      const m = meta.get(id);
      return {
        id,
        slug: s(r.post_name),
        oldSlugs: oldSlugById.get(id) ?? [],
        title: decodeEntities(clean(r.post_title)),
        excerpt: decodeEntities(clean(r.post_excerpt)),
        bodyHtml: cleanBlockComments(s(r.post_content)),
        date: isoDate(r.post_date_gmt),
        modified: isoDate(r.post_modified_gmt),
        author: userById.get(Number(r.post_author))?.name || "OOX Limited",
        featuredImage: imgFromThumb(m),
        categories: (cats.get(id) ?? []).filter((c) => c.slug !== "uncategorized"),
        tags: tags.get(id) ?? [],
        ...yoast(m),
      };
    });
    writeJson("posts.json", posts);
  }

  // ============================ TEAM ======================================
  {
    const rows = await q(
      `SELECT ID, post_title, post_name, post_content, menu_order
         FROM wp_posts WHERE post_type='team' AND post_status='publish'
        ORDER BY menu_order, ID`,
    );
    const ids = rows.map((r) => Number(r.ID));
    const meta = await metaFor(ids);

    const grp = (raw: string): Row[] => {
      if (!raw) return [];
      try {
        const v = phpUnserialize(raw);
        return Array.isArray(v) ? (v as Row[]) : Object.values(v as Record<string, Row>);
      } catch {
        return [];
      }
    };

    const team = rows.map((r, i) => {
      const id = Number(r.ID);
      const m = meta.get(id);
      const socialsRaw = grp(first(m, "_omero_socials_group"))[0] as Row | undefined;
      const socials: Record<string, string> = {};
      for (const [k, v] of Object.entries(socialsRaw ?? {})) {
        const val = clean(v);
        if (val) socials[k.replace(/^social_/, "")] = val;
      }
      return {
        id,
        slug: s(r.post_name),
        ord: Number(r.menu_order) || i,
        name: decodeEntities(clean(r.post_title)),
        position: decodeEntities(clean(first(m, "_team_position"))),
        job: decodeEntities(clean(first(m, "_team_job"))),
        experience: decodeEntities(clean(first(m, "_team_experience"))),
        responsibility: decodeEntities(clean(first(m, "_team_responsibility"))),
        email: clean(first(m, "_team_email")),
        bio: decodeEntities(clean(first(m, "_team_skill_description")) || clean(r.post_content)),
        photo: imgFromThumb(m),
        socials,
        skills: grp(first(m, "_team_skills_group")).map((x) => decodeEntities(clean(x.title))).filter(Boolean),
        programs: grp(first(m, "_team_programs_group")).map((x) => decodeEntities(clean(x.title))).filter(Boolean),
        qa: grp(first(m, "_team_qa_group"))
          .map((x) => ({
            question: decodeEntities(clean(x.question)),
            answer: sanitizeInlineHtml(s(x.answer)),
          }))
          .filter((x) => x.question || x.answer),
      };
    });
    writeJson("team.json", team);
  }

  // ============================ SERVICES ==================================
  {
    const rows = await q(
      `SELECT ID, post_title, post_name, post_excerpt, menu_order
         FROM wp_posts WHERE post_type='service' AND post_status='publish'
        ORDER BY menu_order, ID`,
    );
    const ids = rows.map((r) => Number(r.ID));
    const meta = await metaFor(ids);
    const services = rows.map((r, i) => {
      const id = Number(r.ID);
      const m = meta.get(id);
      return {
        id,
        slug: s(r.post_name),
        ord: Number(r.menu_order) || i,
        title: decodeEntities(clean(r.post_title)),
        excerpt: decodeEntities(clean(r.post_excerpt)),
        thumbnail: imgFromThumb(m),
        ...yoast(m),
      };
    });
    writeJson("services.json", services);
  }

  // ============================ PAGES =====================================
  {
    const rows = await q(
      `SELECT ID, post_title, post_name, post_parent, menu_order
         FROM wp_posts WHERE post_type='page' AND post_status='publish'
        ORDER BY menu_order, ID`,
    );
    const ids = rows.map((r) => Number(r.ID));
    const meta = await metaFor(ids);
    const bySlug = new Map(rows.map((r) => [Number(r.ID), s(r.post_name)]));
    const pathFor = (r: Row): string => {
      const segs: string[] = [];
      let cur: Row | undefined = r;
      const rowById = new Map(rows.map((x) => [Number(x.ID), x]));
      while (cur) {
        segs.unshift(s(cur.post_name));
        const pid = Number(cur.post_parent);
        cur = pid ? rowById.get(pid) : undefined;
      }
      return "/" + segs.filter(Boolean).join("/") + "/";
    };
    void bySlug;

    const frontId = Number(opt.page_on_front);
    const blogId = Number(opt.page_for_posts);

    const pages = rows.map((r) => {
      const id = Number(r.ID);
      const m = meta.get(id);
      const isFront = id === frontId;
      return {
        id,
        slug: s(r.post_name),
        path: isFront ? "/" : pathFor(r),
        title: decodeEntities(clean(r.post_title)),
        isFront,
        isBlog: id === blogId,
        // filled by freeze.ts:
        edits: {} as Record<string, string>,
        ...yoast(m),
      };
    });
    writeJson("pages.json", pages);
  }

  // ============================ MENUS =====================================
  {
    async function menuTree(termSlug: string) {
      const items = await q(
        `SELECT p.ID, p.post_title, p.menu_order,
                MAX(CASE WHEN pm.meta_key='_menu_item_object_id' THEN pm.meta_value END) AS object_id,
                MAX(CASE WHEN pm.meta_key='_menu_item_object'    THEN pm.meta_value END) AS object,
                MAX(CASE WHEN pm.meta_key='_menu_item_type'      THEN pm.meta_value END) AS type,
                MAX(CASE WHEN pm.meta_key='_menu_item_url'       THEN pm.meta_value END) AS url,
                MAX(CASE WHEN pm.meta_key='_menu_item_menu_item_parent' THEN pm.meta_value END) AS parent
           FROM wp_posts p
           JOIN wp_postmeta pm ON pm.post_id = p.ID
           JOIN wp_term_relationships tr ON tr.object_id = p.ID
           JOIN wp_term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
           JOIN wp_terms t ON t.term_id = tt.term_id
          WHERE t.slug = ? AND p.post_type = 'nav_menu_item' AND p.post_status = 'publish'
          GROUP BY p.ID
          ORDER BY p.menu_order`,
        [termSlug],
      );
      // resolve URLs
      const postSlugs = await q(
        `SELECT ID, post_name, post_type, post_parent FROM wp_posts WHERE post_status='publish'
          AND post_type IN ('page','post','service','team')`,
      );
      const rowById = new Map(postSlugs.map((r) => [Number(r.ID), r]));
      const pagePath = (pid: number): string => {
        const segs: string[] = [];
        let cur = rowById.get(pid);
        const type = cur?.post_type;
        while (cur) {
          segs.unshift(s(cur.post_name));
          const par = Number(cur.post_parent);
          cur = par ? rowById.get(par) : undefined;
        }
        const p = segs.filter(Boolean).join("/");
        if (type === "post") return `/${p}/`;
        if (type === "service") return `/service/${segs[segs.length - 1]}/`;
        if (type === "team") return `/team/${segs[segs.length - 1]}/`;
        return `/${p}/`;
      };

      type Item = { id: number; label: string; url: string; parent: number; children: Item[] };
      const flat: Item[] = items.map((r) => {
        const id = Number(r.ID);
        const objId = Number(r.object_id);
        let url = clean(r.url);
        if (s(r.type) === "post_type" && objId) {
          url = objId === Number(opt.page_on_front) ? "/" : pagePath(objId);
        }
        url = url.replace(LIVE_ORIGIN, "").replace(/^https?:\/\/localhost:8080/, "") || "/";
        return {
          id,
          label: decodeEntities(clean(r.post_title)) ||
            decodeEntities(clean(rowById.get(objId)?.post_title)),
          url,
          parent: Number(r.parent) || 0,
          children: [],
        };
      });
      // titles for items with empty post_title: pull from the target post
      const targetTitles = await q(
        `SELECT ID, post_title FROM wp_posts WHERE ID IN (${
          items.map(() => "?").join(",") || "0"
        })`,
        items.map((r) => Number(r.object_id)),
      );
      const titleById = new Map(targetTitles.map((r) => [Number(r.ID), decodeEntities(clean(r.post_title))]));
      for (const it of flat) {
        if (!it.label) {
          const src = items.find((r) => Number(r.ID) === it.id);
          it.label = titleById.get(Number(src?.object_id)) || "";
        }
      }
      const byId = new Map(flat.map((i) => [i.id, i]));
      const roots: Item[] = [];
      for (const it of flat) {
        if (it.parent && byId.has(it.parent)) byId.get(it.parent)!.children.push(it);
        else roots.push(it);
      }
      return roots;
    }

    const menus = {
      main: await menuTree("oox-menu"),
      footer: await menuTree("oox-footer-menu"),
    };
    writeJson("menus.json", menus);
  }

  // ============================ SITE SETTINGS =============================
  {
    // mc4wp list id
    const mc = await q(
      `SELECT meta_value FROM wp_postmeta WHERE meta_key='_mc4wp_settings' LIMIT 1`,
    );
    let mailchimpListId = "";
    try {
      const parsed = mc[0] ? (phpUnserialize(s(mc[0].meta_value)) as Row) : null;
      const lists = parsed?.lists;
      if (Array.isArray(lists)) mailchimpListId = s(lists[0]);
    } catch {
      /* ignore */
    }

    // GA4 measurement id from Google Site Kit
    let gaId = "";
    try {
      const gsk = phpUnserialize(s(opt["googlesitekit_analytics-4_settings"])) as Row;
      gaId = clean(gsk?.measurementID);
    } catch {
      /* ignore */
    }

    // Footer social icons — pulled from the Elementor Footer 2 template (post 39).
    const ICON_LABEL: Record<string, string> = {
      "fa-facebook-f": "Facebook", "fa-facebook": "Facebook", "fa-pinterest": "Pinterest",
      "fa-linkedin-in": "LinkedIn", "fa-linkedin": "LinkedIn", "fa-instagram": "Instagram",
      "fa-youtube": "YouTube", "fa-x-twitter": "X", "fa-twitter": "X", "fa-tiktok": "TikTok",
      "fa-discord": "Discord", "fa-telegram": "Telegram", "fa-github": "GitHub",
    };
    const socialLinks: { label: string; href: string; icon: string }[] = [];
    // Footer 2 (post 39) is the active footer template — see the rendered
    // page CSS (post-39.css loads on every route).
    const hfRows = await q(
      `SELECT meta_value FROM wp_postmeta WHERE meta_key='_elementor_data' AND post_id = 39`,
    );
    const walk = (els: unknown[]) => {
      for (const el of els as Row[]) {
        if (el.widgetType === "social-icons") {
          const list = ((el.settings as Row)?.social_icon_list as Row[]) ?? [];
          for (const it of list) {
            const icon = clean((it.social_icon as Row)?.value).replace(/^fab? /, "").trim();
            const href = clean((it.link as Row)?.url);
            if (href && !socialLinks.some((x) => x.href === href)) {
              socialLinks.push({ label: ICON_LABEL[icon] ?? icon.replace(/^fa-/, ""), href, icon });
            }
          }
        }
        if (Array.isArray(el.elements)) walk(el.elements);
      }
    };
    for (const r of hfRows) {
      try {
        walk(JSON.parse(s(r.meta_value)));
      } catch {
        /* ignore */
      }
    }

    writeJson("siteSettings.json", {
      title: decodeEntities(opt.blogname || "OOX Limited"),
      tagline: decodeEntities(opt.blogdescription || ""),
      contactEmail: "info@ooxlimited.com",
      contactRecipients: ["info@ooxlimited.com", "janbazik@live.com"],
      mailFromName: "OOX Limited",
      mailchimpListId,
      gaId,
      headScripts: "",
      socialLinks,
    });
  }

  // ============================ REDIRECTS ================================
  {
    const redirects: { from: string; to: string; type: number }[] = [];
    // Yoast Premium redirects option
    const yr = await q(
      `SELECT option_value FROM wp_options WHERE option_name IN ('wpseo-premium-redirects-base','wpseo_redirect')`,
    );
    for (const r of yr) {
      try {
        const parsed = phpUnserialize(s(r.option_value)) as Record<string, Row> | Row[];
        const list = Array.isArray(parsed) ? parsed : Object.values(parsed);
        for (const item of list) {
          const from = clean((item as Row).origin ?? (item as Row).old_url);
          const to = clean((item as Row).url ?? (item as Row).new_url);
          const type = Number((item as Row).type) || 301;
          if (from) redirects.push({ from: "/" + from.replace(/^\/+/, ""), to: to || "/", type });
        }
      } catch {
        /* ignore */
      }
    }
    // _wp_old_slug for posts already captured on the post record; still emit
    // top-level redirects for changed page/service/team slugs
    writeJson("redirects.json", redirects);
  }

  await db.end();
  console.log("migration complete.");
}

// -------------------------------------------------------------- helpers ------
function isoDate(v: unknown): string {
  if (v instanceof Date) return isNaN(+v) ? "" : v.toISOString();
  const str = s(v).trim();
  if (!str || str.startsWith("0000")) return "";
  // "YYYY-MM-DD HH:MM:SS" (WP GMT) or already ISO
  const iso = /^\d{4}-\d{2}-\d{2}[ T]/.test(str) ? str.replace(" ", "T").replace(/Z?$/, "Z") : str;
  const d = new Date(iso);
  return isNaN(+d) ? "" : d.toISOString();
}

/** Strip WP/Yoast HTML comments and Gutenberg block delimiters from post bodies. */
function cleanBlockComments(html: string): string {
  return html
    .replace(/<!--\s*Focus Keyphrase:[\s\S]*?-->/gi, "")
    .replace(/<!--\s*\/?wp:[\s\S]*?-->/gi, "")
    .replace(/<!--[\s\S]*?-->/g, "")
    .replace(/\n{3,}/g, "\n\n")
    .trim();
}

/** Very small inline-HTML allow-list for team Q&A answers. */
function sanitizeInlineHtml(html: string): string {
  return html
    .replace(/<(?!\/?(?:b|i|em|strong|br|span|p)\b)[^>]*>/gi, "")
    .replace(/\s+style="[^"]*"/gi, "")
    .replace(/<span>\s*<\/span>/gi, "")
    .trim();
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});
