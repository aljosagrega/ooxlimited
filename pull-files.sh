#!/bin/bash
# Re-sync the docroot from the live host. Incremental; safe to re-run.
# Overwrites local edits under site/ — see CLAUDE.md before running.
set -euo pipefail
cd "$(dirname "$0")"

[ -f .env ] || { echo "No .env — copy .env.example and fill it in."; exit 1; }
set -a; . ./.env; set +a
: "${OOX_SSH_HOST:?}" "${OOX_SSH_USER:?}" "${OOX_SSH_KEY:?}" "${OOX_REMOTE_DOCROOT:?}"

mkdir -p site
# Excludes are ANCHORED paths, deliberately. A bare name like 'upgrade' matches
# by basename under macOS openrsync and silently strips elementor/core/upgrade,
# which is a fatal error on every page.
exec rsync -rltvz --partial --progress --stats \
  -e "ssh -i $OOX_SSH_KEY -p ${OOX_SSH_PORT:-65002} -o ConnectTimeout=15" \
  --exclude '/wp-content/uploads/' \
  --exclude '/wp-content/wflogs/' \
  --exclude '/wp-content/litespeed/' \
  --exclude '/wp-content/upgrade/' \
  --exclude '/wp-content/upgrade-temp-backup/' \
  --exclude '/wp-content/debug.log' \
  --exclude '/wp-content/Archive.zip' \
  --exclude '/wp-content/plugins/Archive.zip' \
  --exclude '/wp-content/plugins/file-manager-advanced/application/library/php/.tmp/' \
  --exclude '/.tmb/' \
  "$OOX_SSH_USER@$OOX_SSH_HOST:$OOX_REMOTE_DOCROOT/" ./site/
