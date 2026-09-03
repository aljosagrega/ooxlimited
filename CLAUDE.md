# ooxlimited

> Git state: **remote, public** — a public GitHub repo, owned by a collaborator's account
> rather than ours. Everything tracked here, history included, is readable by anyone at all,
> so the usual rule applies without exception and with no clone-access boundary to soften it:
> no credentials, no host addresses or hostnames, no references to unrelated projects or to
> anyone's local directory layout, in code, docs or commit messages.

A local, runnable copy of the `ooxlimited.com` WordPress site: the docroot plus a Docker
setup that stands it up on `localhost:8080`. `README.md` is the operating manual — how to
start it, the layout, how a local copy is obtained and how it differs from the live site.

`site-next/` is the Next.js rebuild that replaces that install — a standalone app with a
flat-JSON content store and an admin CMS, deployed from CI by `.github/workflows/deploy.yml`
(see `site-next/DEPLOYMENT.md`). It ships to a gated review deployment for client approval;
the `ooxlimited.com` apex is still the WordPress site, which remains upstream for
`npm run snapshot` / `npm run migrate` until the cutover.

## What is deliberately not in this repo

Four things are excluded, and all four are excluded on purpose. Don't "fix" their absence
by committing them.

- **`.env`** — SSH host, user and key path for the live server. Copy `.env.example` and
  fill it in from the hosting control panel. Nothing else in the tree may hardcode these.
- **`db/`** — database dumps. A database is content, not code: it changes constantly, the
  live site is always the authority, and committing 40 MB per dump would bloat history
  permanently. Obtain one out of band; `README.md` says how.
- **`site/wp-content/uploads/`** — client media, hundreds of MB. This is **host state**.
  A deploy must preserve it, never ship or overwrite it.
- **`_hostinger-original/`** — the live server's own `wp-config.php` and related notes,
  kept only on the machine that pulled them. It contains the **production database
  password**. It is gitignored; keep it that way, and don't copy it elsewhere.

The database and uploads being host state is what makes automated deploys possible later.
If media lived in git, every deploy would overwrite whatever the client had uploaded since.

## Secrets and host access

Real credentials live in `.env` (gitignored) and in the local `_hostinger-original/`
directory (gitignored). `.env.example` documents the shape without the values. Anyone
setting up fresh needs the values relayed to them privately — never through this repo,
an issue, or a commit.

One landmine worth knowing: the File Manager plugin's temp directory,
`site/wp-content/plugins/file-manager-advanced/application/library/php/.tmp/`, accumulates
thousands of opaquely-named scratch files, and one of them was a **complete copy of the
production `wp-config.php`** — password and auth salts included. Nothing about the
filename suggested it. That directory is gitignored; if you ever move or re-sync the
docroot by other means, exclude it explicitly and grep before committing.

## Gotchas that already cost time

- **rsync excludes must be anchored paths.** macOS ships openrsync, where a bare
  `--exclude 'upgrade'` matches by basename — it silently strips
  `elementor/core/upgrade`, which is a fatal error on every page load. `pull-files.sh`
  carries the working set; extend it in the same anchored style.
- **openrsync rejects GNU-only flags by printing its usage and exiting `0`.** A sync that
  transferred nothing is indistinguishable from a successful one by exit code alone. Read
  the output.
- **`wp db export` fails silently on the live host** — exit 255, no message, no file,
  while `wp db size` and `wp db tables` work normally. Use `mysqldump` directly, with
  credentials in a `600` defaults file so no password reaches argv or shell history.
- **`wp db tables` under-reports.** It lists only the 16 tables WordPress registers; the
  database has 147. Anything operating database-wide needs `--all-tables`.
- **`pull-files.sh` overwrites `site/`.** It re-syncs from the live host. Commit local
  work before running it.
