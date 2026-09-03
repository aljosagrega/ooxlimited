# ooxlimited.com — deployment

A Next.js 16 app running as a long-lived Node process behind nginx. No PHP, no
database. Content is flat JSON in `src/data/*.json`, edited live via `/admin`.

## 1. What runs where

| Piece | Where |
|---|---|
| App server | `node server.js` from `next build`'s `standalone` output — one systemd process, listening on `127.0.0.1:<port>` |
| Reverse proxy / TLS | nginx → `proxy_pass http://127.0.0.1:<port>` |
| Static assets | nginx serves `public/` and `.next/static` **directly** — the standalone server does not serve `public/` reliably |
| Theme / plugin assets | `public/wp-content/` + `public/wp-includes/` — **committed** to the repo (24 MB, `npm run collect-assets`). Nothing to fetch at deploy. |
| Media library | `public/wp-content/uploads/` — symlinked to `shared/uploads`, seeded by hand once from the old server (§4). Client host state; not in git, in no release. |
| Content | `src/data/*.json` (frozen page markup + JSON store), symlinked to persistent `shared/` state so admin edits survive redeploys |
| Email | Resend (contact form); Mailchimp API (newsletter) |

## 2. Requirements

- Node.js 20.9+ or 22 LTS.
- `npm ci` on a platform matching the target (native deps: `sharp`, `@swc/core`,
  `cheerio`'s `undici`). Build on a Linux x64 runner — **not** on the server (§4).
- Outbound HTTPS to `api.resend.com` and `*.api.mailchimp.com`.
- Persistent disk for `shared/` (JSON content + `adminAuth.json` + `submissions.json`).
- ~500 MB for `node_modules` + build; +24 MB committed asset mirror; + the media
  library (`wp-content/uploads/`, hundreds of MB) seeded once into `shared/uploads/`.

## 3. Environment

Set in `shared/.env` on the server, which systemd reads via `EnvironmentFile=` and the
workflow symlinks into each release. Never committed. Template: `.env.example`.

| Var | Required | Notes |
|---|---|---|
| `SESSION_SECRET` | yes | ≥32 chars, `openssl rand -hex 32`. Signs the admin cookie. |
| `ADMIN_USERNAME` / `ADMIN_PASSWORD` | yes (first boot) | Bootstraps `src/data/adminAuth.json`. Change from the dev defaults. |
| `SITE_URL` | yes | Canonical URLs, sitemap, OG — `https://ooxlimited.com` in production. Also a CI variable, because it is baked into the sitemap at build: **rebuild after changing**, a restart is not enough. |
| `RESEND_API_KEY` | for contact email | Blank = submissions stored, no email sent. |
| `RESEND_FROM_EMAIL` | for contact email | `OOX Limited <hello@ooxlimited.com>` — domain verified in Resend. |
| `MAILCHIMP_API_KEY` | for newsletter | `xxxxxxxx-usXX`. Blank = signups stored only. |
| `MAILCHIMP_LIST_ID` | for newsletter | Audience id. Also settable in `/admin/settings`. |

## 4. Build & release

Deploys are CI-driven: `.github/workflows/deploy.yml` builds on a GitHub runner
and ships the result. **Nothing builds on the server** — it is a small shared box,
and an unbounded Next build there can starve a neighbouring service.

The server holds no working tree. `$BASE` (a repo variable, `DEPLOY_PATH`) contains
only:

```
$BASE/releases/<sha>/   one assembled build per deploy, pruned to the last 5
$BASE/current           symlink to the live release
$BASE/shared/           persistent state — never shipped, never overwritten
```

`shared/` holds four trees, all of them **host state**:

| Path | What | Linked into each release as |
|---|---|---|
| `shared/.env` | runtime environment (§3) | `.env` |
| `shared/data/` | live content JSON, `adminAuth.json`, `submissions.json`, `pending/` | `src/data/<file>` |
| `shared/media/uploads/` | admin-uploaded images | `public/media/uploads` |
| `shared/uploads/` | the client's WordPress media library (hundreds of MB) | `public/wp-content/uploads` |

The workflow assembles a release from `standalone` output — `output: "standalone"`
emits a minimal server but not `.next/static` or the full `public/` tree, so both are
stitched back in. `src/data` is **not** copied separately: `outputFileTracingIncludes`
in `next.config.ts` already places the whole tree inside standalone, and copying it
again nests it at `src/data/data/`, where the server (which reads
`process.cwd()/src/data`) silently serves the seed instead of live content.

Then it flips `current`, restarts the service over a passwordless `systemctl restart`,
health-checks the app **on its own port from inside the box**, rolls back to the previous
release on failure, and prunes.

**Seeding is one-way.** Each top-level `src/data/*.json` is copied into `shared/data/`
the first time it is seen and symlinked from then on. Once seeded, `shared/` wins: a later
release that re-freezes those files (`npm run snapshot`) will **not** take effect on the
server, because the client's live edits are the thing being protected. Landing re-captured
content means replacing the file in `shared/data/` deliberately — fetch it down, edit
locally, stage the result beside the target and `mv` it into place, and back up first.

`shared/uploads/` is the one tree that no build can recreate: it is the client's media,
in no repo, seeded by hand once. Never overwrite a newer live copy with an older one.

`npm run migrate` / `npm run snapshot` re-capture from a running WordPress copy
(content refresh, design tweak). They are not part of a deploy; their output is
committed.

## 5. nginx

The app runs behind a reverse proxy. `SITE_URL` is read at **build** time as well as at
runtime — `sitemap.ts` bakes it into `/sitemap.xml` — so changing the canonical host needs
a rebuild, not just a restart.

Static files are served straight off disk, because the standalone Node server does not
serve `public/` reliably. Each rule aliases through `current`, so it tracks every release
without editing:

```nginx
location = /favicon.ico { alias $BASE/current/public/favicon.ico; expires 30d; access_log off; }
location /_next/static/ { alias $BASE/current/.next/static/;      expires 1y;  access_log off; }
location /wp-content/   { alias $BASE/current/public/wp-content/; expires 1y;  access_log off; try_files $uri =404; }
location /wp-includes/  { alias $BASE/current/public/wp-includes/; expires 1y; access_log off; try_files $uri =404; }
location /media/        { alias $BASE/current/public/media/;      expires 7d;  access_log off; try_files $uri =404; }

location / { proxy_pass http://127.0.0.1:<port>; proxy_set_header Host $host; proxy_set_header X-Forwarded-Proto $scheme; }
```

`/wp-content/` matters twice over: it carries both the committed theme/plugin assets and,
through the `uploads` symlink, the whole media library — so nginx serves those hundreds of
megabytes rather than Node. `/media/` is not optional either: Next standalone will not
serve files written into `public/media/uploads` after the build, so without the alias every
admin image upload 404s. Writing works through the symlinks alone; **serving** is what needs
the alias, and a health check on `/` will not catch its absence.

`/robots.txt` and `/sitemap.xml` come from the app (`src/middleware.ts` and `sitemap.ts`),
both following `SITE_URL`. A gated review deployment should override `/robots.txt` at the
proxy with a flat `Disallow: /` rather than serve the production one.

Do **not** let the proxy add its own trailing-slash or redirect rules — the app owns
those (`trailingSlash: true`, `src/middleware.ts`).

## 6. CI configuration

`.github/workflows/deploy.yml` runs on push to `main` under `site-next/**`, and on
`workflow_dispatch`. It reads repo **variables** `SSH_USER`, `SSH_HOST`, `SSH_PORT`,
`DEPLOY_PATH`, `SERVICE_NAME`, `APP_PORT`, `SITE_URL`, and the secret `SSH_PRIVATE_KEY`.

Set `SSH_HOST` to the server's IP rather than the site's hostname, so CI's SSH is
independent of the site's DNS and certificate state and the first deploy can run before
either exists.

The host must provide: the system user and its `$BASE`, the four `shared/` trees owned by
that user, a systemd unit running `node server.js` from `$BASE/current` with
`EnvironmentFile=$BASE/shared/.env`, a sudoers drop-in granting that user
`NOPASSWD: /bin/systemctl restart <service>`, the deploy public key in its
`authorized_keys`, and the nginx rules in §5. The service will not start cleanly until the
first deploy creates `current/`.

## 7. Cutover

1. Deploy to the review host and smoke-test there.
2. Run the parity screenshots (README §"Verifying design parity") against it.
3. Rebuild with `SITE_URL` set to the production host — the canonical tags and the
   sitemap follow it, so this is a rebuild, not a restart — and point DNS at the app.
4. Keep the WordPress install reachable internally for one release cycle so
   `npm run snapshot` / `npm run migrate` can be re-run if something was missed.
5. `wp-content/uploads/` (media) is host state. It is never in the repo; never overwrite
   a newer live copy with an older one.
