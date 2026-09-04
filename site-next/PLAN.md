# ooxlimited.com WordPress → Next.js — plan, status & everything learned

## Goal

Replace WordPress + Elementor with this Next.js 16 app + a custom admin, whose
schema-driven CMS was ported from a sibling Next.js project.
**The public design must stay pixel-identical** — it is
the exact HTML + CSS WordPress renders, frozen and served through the app. Only
the framework and administration are new. **All text and images must be
editable in the admin.**

## Architecture — "freeze the design, rebuild the plumbing"

| Piece | What |
|---|---|
| `scripts/migrate/migrate.ts` | local MariaDB (`127.0.0.1:3307`) → `src/data/*.json`. `dateStrings:true` on the mysql2 conn (it returns Date objects otherwise → blank dates). |
| `scripts/snapshot/freeze.ts` | fetch each route's rendered HTML from `:8080`, split head/body, strip the WP + live origins. Rewrites any linked CSS that carries an absolute origin into `public/_css/<path>` (see "CSS mask bug" below) and repoints the `<link href>`. |
| `scripts/snapshot/build-pagemaps.ts` | tags every editable text node + image in the frozen marketing pages with `data-oox-e="<id>"` (written back into the frozen HTML) and emits `src/data/pagemaps/<key>.json` = `[{id,kind,label,group,value}]`. Chrome (header/footer/nav) is excluded. Run AFTER freeze. |
| `public/wp-content`, `public/wp-includes` | symlinks to `../site/…` so frozen asset refs resolve. `public/_css/` + `src/data/frozen/` + `src/data/pagemaps/` are build products (gitignored). |
| `src/app/(public)/[[...slug]]/page.tsx` | serves the frozen page: `applyChromePatch` (nav/social/contact from JSON) → `applySingleContent` / `applyBlogIndex` / `applyPageEdits` (pagemap edits from `pages.json`) → `<FrozenView>`. |
| `src/lib/blogRender.ts` | blog is dynamic on top of the snapshot. `applyBlogIndex` rebuilds the `/blog/` + `/blog/page/N/` card grid from `posts.json` (8/page) and injects `.pagination` nav (theme already styles it); pages ≥3 borrow the `blog__page__2` shell. `renderTemplatedPost` fills the shared `_post-template` shell (an emptied copy of a frozen post) for CMS-created posts that have no snapshot of their own. Drafts (`published:false`) drop out of `getAllPosts()` and are rejected by `resolveRoute()` → 404 (see "Why `dynamicParams` must stay `true`" below). |
| `src/components/FrozenView.tsx` | server: hoists the frozen stylesheet stack, SSRs a `.elementor-invisible{visibility:visible!important}` style (kills FOUC), renders the body via dangerouslySetInnerHTML **with `<script>` stripped**. |
| `src/components/FrozenScripts.tsx` | client: replays the frozen `<script>` stack **once** (`window.__frozenScriptsRun` guard), sequential, then `kickLegacyRuntime()` → `elementorFrontend.init()` + GSAP `ScrollTrigger.refresh()`. |
| `src/lib/fieldMap.ts` | `getPagemap`, `applyPageEdits` (cheerio patch by `data-oox-e`). |
| `src/lib/chromePatch.ts` | rewrites nav menu items + footer social hrefs from `menus.json` / `siteSettings.json` onto every frozen page. Footer email left frozen (`admin@ooxcit.com`, not an admin field). |
| Admin | schema-driven CMS ported from a sibling project — `posts`/`team`/`services`/`pages`/`submissions`. `pages` uses `<PageEditor>` (pagemap form), the rest use `<SchemaForm>`. `next.config.ts` has `reactStrictMode:false` (frozen scripts aren't idempotent). |

## HARD-WON LESSONS (bugs found while the user reviewed live)

1. **Frozen `<script>` tags MUST be stripped from the SSR'd body.** cheerio
   (`applyChromePatch`) re-serialises them; a `<script>` in the initial HTML
   response *executes during parse*, out of order, before jQuery → 20×
   `jQuery is not defined` + then double-run `already declared`. Fix: `stripScripts()`
   in `FrozenView` on whatever body it renders.
2. **Duplicate script enqueues.** WP enqueues some scripts in `<head>` AND the
   footer; the omero addon scripts declare top-level `let` → "already declared".
   `frozen.ts` `getFrozenAssets` dedupes external scripts by URL.
3. **StrictMode double-invokes effects** → FrozenScripts ran twice. `reactStrictMode:false`
   + the `__frozenScriptsRun` guard (no teardown — legacy globals can't be undone).
4. **CSS `mask-image` with an absolute origin = INVISIBLE elements.** omero-child's
   `style.css` has `.…omero-teams-list … .post-thumbnail{ mask-image:url("https://ooxlimited.com/…/maskk.svg") }`.
   Cross-origin from `:3000` → mask fetch fails → **the whole masked element
   renders nothing** (this is why team photos / history-timeline images
   "disappeared"). Also affected the LOCAL WP (fetches the live SVG cross-origin).
   Fix: `freeze.ts` copies any CSS with an absolute origin to `public/_css/` rewritten.
5. **FOUC**: Elementor entrance animations mark elements `elementor-invisible`
   (`visibility:hidden`) until `frontend.js` reveals them — which runs a beat
   later here. SSR'd override forces them visible; the animation still replays.
6. **mysql2 returns DATETIME as Date objects** → `isoDate()` broke → all post
   dates blank. `dateStrings:true` + handle both in `isoDate`.
7. `robots.txt` / `public/` static files aren't served by `next start`/standalone
   — nginx serves them in prod (DEPLOYMENT.md); dev serves `/robots.txt` via middleware.
8. Route groups: `(public)` owns `<html>`, `admin/` owns its own, `app/layout.tsx`
   is a passthrough — two `<html>` from nested layouts = hydration error.

## Done

- Migration + freeze + pagemaps (303 editable fields across 6 marketing pages:
  home 115, about 97, team 51, services 25, contact 13, newsletter 2).
- `next build` green. Pixel parity verified band-by-band on home / blog / about /
  services / team / contact / service pages / team-member pages / posts / 390px —
  **all match WordPress** including team photos, history timeline, carousels,
  hover states, GSAP scroll reveals (2 ScrollTriggers, matches WP).
- Forms: contact → Resend, newsletter → Mailchimp API. Submissions inbox.
- Admin: dashboard (charts), `/admin/seo` report, settings (General / Contact &
  newsletter / Analytics & scripts [gaId + custom scripts] / Social links / Account),
  GA4 injection, SERP preview → ooxlimited.com.
- **Live preview** (posts only, `schema.preview`): docked-right panel, form
  pushed left, iframe of `bodyHtml` in `<article class="oox-blog-article">` with
  the real omero CSS — matches the frontend, no chrome. `sanitize.ts` keeps
  `<style>` / `id` / inline styles (blog posts scope their own typography inline).
- **Image fields**: `imageObject` type (`{url,alt}`) for team.photo /
  posts.featuredImage / services.thumbnail — proper upload + preview + alt.
- **PageEditor**: `/admin/pages/<id>/edit` renders the pagemap as a grouped,
  searchable form (text inputs / rich text for markup / image pickers), saves
  `edits: {id: value}` to `pages.json` via `/api/admin/pages/[id]`, revalidates.

## Left to do

- [ ] PageEditor polish: `html`-kind fields render a full RichTextEditor even for
      a one-line `<span>` — use a plain input unless the markup is block-level.
- [ ] Pagemap noise: a few stray entries ("Login", "oox menu" from hidden widgets)
      — tighten the `CHROME_SEL` / skip list in build-pagemaps.ts.
- [ ] `applyPageEdits` runs on every request (cheerio parse of ~200KB) — only when
      `page.edits` is non-empty (already gated) but consider caching per (route, edits-hash).
- [ ] Menus admin UI (removed from sidebar; `menus.json` hand-editable).
- [ ] Newsletter: live site uses the Newsletter (tnp) plugin, not Mailchimp
      (mc4wp form 283 has no list). Decide.
- [ ] Sidebar brand glyph still "W" → OOX mark.
- [ ] `SITE_URL` baked into sitemap at build — set on the server, rebuild.
- [ ] Decide whether to git-commit `site-next/` (currently untracked).
- [ ] Skeleton loaders: `/admin/seo` + settings skeletons don't match the new layouts.

## Commands

```
docker compose up -d                       # WP at :8080, MariaDB :3307   (repo root)
cd site-next && npm run dev                 # :3000 · admin /admin  (admin / ooxlimited-dev)
npm run migrate                             # WP DB -> src/data/*.json
npm run freeze                              # WP HTML -> src/data/frozen/  (+ public/_css/)
npx tsx scripts/snapshot/build-pagemaps.ts  # -> src/data/pagemaps/   (run AFTER freeze)
npm run build
node scripts/snapshot/audit.mjs /about-us/ --w 1280        # WP|Next band-by-band tiles
node scripts/snapshot/settled.mjs /game-development-team/   # settled full-page side-by-side
```

## Why `dynamicParams` must stay `true` on `[[...slug]]`

`generateStaticParams` knows every valid route, so `export const dynamicParams = false`
looks correct — and it breaks the whole site.

Every admin write calls `revalidatePath("/", "layout")`, which drops the prerendered
pages for that whole segment. With `dynamicParams:false` there is no way to render them
again on demand, so Next raises `Error: Internal: NoFallbackError` and **every public
page 404s** until the next deploy re-prerenders them. Symptom: the site is fine after a
deploy, then goes entirely 404 the moment anyone saves anything in `/admin`.

Left at the default (`true`), an invalidated page simply re-renders on demand. The cost
is that unknown paths reach the component, so `resolveRoute()` in the route owns every
404 — including draft posts (whose frozen snapshots are still on disk) and archive pages
past the last one with content. Bonus: posts created in the CMS go live immediately,
no redeploy.
