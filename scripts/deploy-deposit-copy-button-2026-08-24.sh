#!/bin/bash
# Deploys e64e8f7 + 042009c: click-to-copy buttons for the manual
# deposit method's "Payment Details:" field (admin Summernote toolbar
# button + frontend copy handler, all 3 themes), plus the follow-up
# purifier fix that keeps the button's type="button" attribute intact
# through Purifier::clean() on save.
# Run this from the app's docroot on each host.
# Usage: bash deploy-deposit-copy-button-2026-08-24.sh
set -e
BASE="https://raw.githubusercontent.com/macwuche/allgrant/main"
FAIL=0

echo "== Pulling changed files =="
U="$BASE/config/purifier.php"
curl -sf -o "config/purifier.php" "$U" || { echo "FAILED: config/purifier.php"; FAIL=1; }
U="$BASE/resources/views/backend/deposit/create_method.blade.php"
curl -sf -o "resources/views/backend/deposit/create_method.blade.php" "$U" || { echo "FAILED: resources/views/backend/deposit/create_method.blade.php"; FAIL=1; }
U="$BASE/resources/views/backend/deposit/edit_method.blade.php"
curl -sf -o "resources/views/backend/deposit/edit_method.blade.php" "$U" || { echo "FAILED: resources/views/backend/deposit/edit_method.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/corporate/deposit/now.blade.php"
curl -sf -o "resources/views/frontend/corporate/deposit/now.blade.php" "$U" || { echo "FAILED: resources/views/frontend/corporate/deposit/now.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/default/deposit/now.blade.php"
curl -sf -o "resources/views/frontend/default/deposit/now.blade.php" "$U" || { echo "FAILED: resources/views/frontend/default/deposit/now.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/digi_vault/deposit/now.blade.php"
curl -sf -o "resources/views/frontend/digi_vault/deposit/now.blade.php" "$U" || { echo "FAILED: resources/views/frontend/digi_vault/deposit/now.blade.php"; FAIL=1; }

if [ "$FAIL" -ne 0 ]; then
  echo "== One or more pulls FAILED — stopping before optimize:clear. Fix and re-run. =="
  exit 1
fi

echo "== All 6 files pulled clean =="
echo "== Clearing caches (view cache included, and purifier's own HTML.Allowed config cache) =="
php artisan optimize:clear

echo "== Done. Code is now live. =="
