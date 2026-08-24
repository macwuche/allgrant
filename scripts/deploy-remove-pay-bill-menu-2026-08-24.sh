#!/bin/bash
# Deploys 9ef8294: removes "Pay Bill" from the user sidebar (all 3
# themes) and the "Bill Management"/"Bill History" section from the
# admin sidebar. Menu-only — routes/controllers/pages untouched.
# Run this from the app's docroot on each host.
# Usage: bash deploy-remove-pay-bill-menu-2026-08-24.sh
set -e
BASE="https://raw.githubusercontent.com/macwuche/allgrant/main"
FAIL=0

echo "== Pulling changed files =="
U="$BASE/resources/views/backend/include/__side_nav.blade.php"
curl -sf -o "resources/views/backend/include/__side_nav.blade.php" "$U" || { echo "FAILED: resources/views/backend/include/__side_nav.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/corporate/include/__user_side_nav.blade.php"
curl -sf -o "resources/views/frontend/corporate/include/__user_side_nav.blade.php" "$U" || { echo "FAILED: resources/views/frontend/corporate/include/__user_side_nav.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/default/include/__user_side_nav.blade.php"
curl -sf -o "resources/views/frontend/default/include/__user_side_nav.blade.php" "$U" || { echo "FAILED: resources/views/frontend/default/include/__user_side_nav.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/digi_vault/include/__user_side_nav.blade.php"
curl -sf -o "resources/views/frontend/digi_vault/include/__user_side_nav.blade.php" "$U" || { echo "FAILED: resources/views/frontend/digi_vault/include/__user_side_nav.blade.php"; FAIL=1; }

if [ "$FAIL" -ne 0 ]; then
  echo "== One or more pulls FAILED — stopping before optimize:clear. Fix and re-run. =="
  exit 1
fi

echo "== All 4 files pulled clean =="
echo "== Clearing caches (view cache included) =="
php artisan optimize:clear

echo "== Done. Code is now live. =="
