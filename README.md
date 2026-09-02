# ooxlimited.com

A local, runnable copy of the `ooxlimited.com` WordPress site — the docroot plus a Docker
setup that serves it at <http://localhost:8080>. WordPress 7.0.4 on Elementor, PHP 8.3 +
Apache and MariaDB 11.8, matching the live host.

The database is **not** in this repo (see `CLAUDE.md` for why), so a fresh clone needs one
before it will do anything. That is the only manual step.

## Getting a database

Ask whoever set this up for a dump — it is distributed privately, not through this repo.
Drop it in as `db/ooxlimited.sql.gz` and the first boot imports it automatically:

```
mkdir -p db && cp /path/to/ooxlimited.sql.gz db/
```

To produce one from the live host, run this on the host (not locally — the site's own
`wp db export` exits 255 with no message, so use `mysqldump` directly). Credentials go in
a `600` defaults file so they never reach argv or shell history:

```bash
cd <docroot>
umask 077
{ printf '[client]\nhost=%s\nuser=%s\npassword="' "$(wp config get DB_HOST)" "$(wp config get DB_USER)"
  wp config get DB_PASSWORD | tr -d '\n'; printf '"\n'; } > ~/.dump.cnf
mysqldump --defaults-file=~/.dump.cnf --single-transaction --quick --no-tablespaces \
  --default-character-set=utf8mb4 --add-drop-table "$(wp config get DB_NAME)" > ~/oox.sql
shred -u ~/.dump.cnf
gzip ~/oox.sql          # then copy it down, and delete it from the host
```

## Running it

```
docker compose up -d      # first boot imports the dump; subsequent boots are quick
docker compose down       # stop
docker compose down -v    # stop and discard the database, forcing a fresh import
```

- Site: <http://localhost:8080>
- MariaDB on `localhost:3307` — database `ooxlimited`, user `wp`, password `wp`

The dump carries the live site's user accounts, whose passwords nobody here knows. Make
yourself an administrator:

```
docker compose exec wp wp --allow-root user create localadmin you@localhost.test \
  --role=administrator --user_pass='<pick one>'
```

Then rewrite the URLs, which still point at the live domain throughout the database.
Elementor stores page data as JSON, so the escaped form needs its own pass:

```
docker compose exec wp wp --allow-root search-replace 'https://ooxlimited.com' \
  'http://localhost:8080' --all-tables --skip-columns=guid --precise
docker compose exec wp wp --allow-root search-replace 'https:\/\/ooxlimited.com' \
  'http:\/\/localhost:8080' --all-tables --skip-columns=guid --precise
```

Finally, deactivate the plugins that are host-coupled or make outbound calls:

```
docker compose exec wp wp --allow-root plugin deactivate litespeed-cache hostinger \
  hostinger-ai-assistant hostinger-easy-onboarding hostinger-reach google-site-kit \
  broken-link-checker
```

## Layout

| Path | What |
|------|------|
| `site/` | the docroot, as served |
| `docker/Dockerfile` | PHP 8.3 + Apache with `mysqli gd zip intl exif opcache`, plus wp-cli |
| `pull-files.sh` | re-sync the docroot from the live host; incremental, **overwrites `site/`** |
| `.env.example` | shape of the host access config; copy to `.env` and fill in privately |
| `db/` | where the dump goes — gitignored |

## Re-syncing from the live host

`cp .env.example .env`, fill in the values from the hosting control panel, then
`./pull-files.sh`. It excludes uploads, caches and logs, and it overwrites `site/` — so
commit anything local first.

Media under `wp-content/uploads/` is host state and is neither synced nor tracked. If you
need images locally, copy them down separately.
