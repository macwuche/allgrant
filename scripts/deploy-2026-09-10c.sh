#!/bin/bash
# Deploys the inbox-list unread/read/thread-count visibility fix (see
# email.md section 11 progress log, 2026-09-10). One file, CSS/markup only --
# no schema change, no new settings, no new permissions.
#
# Run this from the app's docroot on each host.
# Usage: bash deploy-2026-09-10c.sh
set -e
BASE="https://raw.githubusercontent.com/macwuche/allgrant/main"
FAIL=0

pull() {
  mkdir -p "$(dirname "$1")"
  curl -sf -o "$1" "$BASE/$1" || { echo "FAILED: $1"; FAIL=1; }
}

echo "== Pulling changed files =="
pull "resources/views/backend/email_inbox/index.blade.php"
pull "email.md"

if [ "$FAIL" -ne 0 ]; then
  echo "== One or more pulls FAILED — stopping before optimize:clear. Fix and re-run. =="
  exit 1
fi

echo "== All 2 files pulled clean =="
echo "== Clearing caches (view cache included) =="
php artisan optimize:clear

echo "== Done. Code is live. =="
