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
| `src/data/frozen/<key>.{html,head.html,meta.json}` | the rendered `<body>` + `<head>` resource stack per route, captured from the local WordPress by `npm run freeze` |
| `public/wp-content`, `public/wp-includes` | **symlinks** to `../site/…` so every frozen `/wp-content/*` asset ref resolves. In production these are real rsynced directories (see DEPLOYMENT.md) |
| `src/components/FrozenView.tsx` | server component: hoists the frozen stylesheet stack into `<head>`, renders the frozen body, mounts the script replayer |
| `src/components/FrozenScripts.tsx` | client: replays the frozen `<script>` stack in order, then kicks Elementor / GSAP so carousels, galleries and scroll reveals attach |
| `src/components/FrozenForms.tsx` | client: rebinds the frozen contact + newsletter forms to the API routes |
| `src/data/*.json` | the editable content store (`posts`, `team`, `services`, `pages`, `menus`, `siteSettings`, `redirects`) — migrated from the WP DB by `npm run migrate` |
| `src/app/[[...slug]]` | one catch-all that serves the frozen page for a route, applying any `pages.json` field-map edits |
| `src/app/admin` | the CMS — schema-driven, ported from a sibling project, no database |
| `src/middleware.ts` | redirects: `/wp-admin` → `/admin`, old slugs → canonical, legacy sitemaps, `robots.txt` |

## Local setup

Needs the WordPress copy running first (`cd .. && docker compose up -d` → `localhost:8080`).

```bash
cd site-next
npm install
npm run migrate     # WP DB (localhost:3307) -> src/data/*.json
npm run freeze      # localhost:8080 rendered HTML -> src/data/frozen/
ln -s ../site/wp-content  public/wp-content     # once
ln -s ../site/wp-includes public/wp-includes    # once
cp .env.example .env.local && edit              # ADMIN_*, SESSION_SECRET at minimum
npm run dev         # http://localhost:3000  (admin at /admin, admin / ooxlimited-dev)
```

Re-run `npm run migrate` / `npm run freeze` whenever the WordPress copy changes.

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
