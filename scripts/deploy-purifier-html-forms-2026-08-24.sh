#!/bin/bash
# Deploys ccc032d: adds 'HTML.Forms' => true to config/purifier.php,
# fixing the 500 on saving a manual deposit method's Payment Details
# once it contains a copy button (button element was silently
# unrecognized by HTMLPurifier, escalating to a fatal ErrorException).
# Run this from the app's docroot on each host.
# Usage: bash deploy-purifier-html-forms-2026-08-24.sh
set -e
BASE="https://raw.githubusercontent.com/macwuche/allgrant/main"
FAIL=0

echo "== Pulling changed files =="
U="$BASE/config/purifier.php"
curl -sf -o "config/purifier.php" "$U" || { echo "FAILED: config/purifier.php"; FAIL=1; }

if [ "$FAIL" -ne 0 ]; then
  echo "== Pull FAILED — stopping before optimize:clear. Fix and re-run. =="
  exit 1
fi

echo "== File pulled clean =="
echo "== Clearing caches (config cache included) =="
php artisan optimize:clear

echo "== Done. Code is now live. =="
