# ooxlimited.com — Next.js rebuild

Replaces the WordPress + Elementor site with a Next.js 16 app + a custom admin.
**The public design is identical** — it is the exact HTML + CSS WordPress renders,
frozen into `src/data/frozen/` and served through the app. Only the framework and
the administration are new.

```
site/        ← the WordPress docroot (still the design source of record — do not delete)
site-next/   ← this app
```

## How it works

| Piece | What |
|---|---|
| `src/data/frozen/<key>.{html,head.html,meta.json}` | the rendered `<body>` + `<head>` resource stack per route, captured from the local WordPress by `npm run freeze`. **Committed** — the app carries its own content. |
| `public/wp-content`, `public/wp-includes` | the exact theme/plugin asset files the frozen pages load (24 MB), copied from the WP docroot by `npm run collect-assets`. **Committed** — the deploy is self-contained. Only `public/wp-content/uploads/` (the media library) stays out: a local symlink, rsynced by the server. |
| `src/data/pagemaps/<key>.json` | per-page list of editable text/image fields (`npm run build:maps`), driving the `/admin/pages` editor. Committed. |
| `src/components/FrozenView.tsx` | server component: hoists the frozen stylesheet stack into `<head>`, renders the frozen body, mounts the script replayer |
| `src/components/FrozenScripts.tsx` | client: replays the frozen `<script>` stack in order, then kicks Elementor / GSAP so carousels, galleries and scroll reveals attach |
| `src/components/FrozenForms.tsx` | client: rebinds the frozen contact + newsletter forms to the API routes |
| `src/data/*.json` | the editable content store (`posts`, `team`, `services`, `pages`, `menus`, `siteSettings`, `redirects`) — migrated from the WP DB by `npm run migrate` |
| `src/app/[[...slug]]` | one catch-all that serves the frozen page for a route, applying any `pages.json` field-map edits |
| `src/app/admin` | the CMS — schema-driven, ported from a sibling project, no database |
| `src/middleware.ts` | redirects: `/wp-admin` → `/admin`, old slugs → canonical, legacy sitemaps, `robots.txt` |

## Local setup

Everything except the media library is committed, so a plain clone runs:

```bash
cd site-next
npm install
ln -s ../../site/wp-content/uploads public/wp-content/uploads   # once — media library (gitignored)
cp .env.example .env.local && edit                              # ADMIN_*, SESSION_SECRET at minimum
npm run dev         # http://localhost:3000  (admin at /admin, admin / ooxlimited-dev)
```

If you don't have the WordPress `site/` copy locally, point the uploads symlink
at wherever the media lives, or set `UPLOADS_ORIGIN` in `.env.local` to proxy
them from the live domain.

### Re-capturing from WordPress

Only when the design or content in the WP copy changes. Needs it running
(`cd .. && docker compose up -d` → `localhost:8080`, DB on `:3307`):

```bash
npm run migrate         # WP DB  -> src/data/*.json  (content)
npm run snapshot         # WP HTML -> src/data/frozen/ + pagemaps + public/wp-content
```

`npm run snapshot` = `freeze` + `build:maps` + `collect-assets`. Commit the result.

## Verifying design parity

```bash
npm run build && (cd .next/standalone && PORT=3100 node server.js)   # or npm start
PLAYWRIGHT_BROWSERS_PATH=~/Library/Caches/ms-playwright \
  NEXT_ORIGIN=http://localhost:3100 \
  node scripts/snapshot/shots.mjs / /about-us/ /services/ /blog/ --w 1280 390 --full
# -> scratchpad/shots/<route>__<width>__{wp,next}.png  — compare side by side
```

## Editing content

`/admin` edits `src/data/*.json` live (no rebuild — API routes `revalidatePath`).
Marketing-page copy is edited through `pages.json` **field maps**: a list of
`{ selector, kind, label, value }` patches applied to the frozen HTML at render.
Blog posts, team members and services are full React-form records.
