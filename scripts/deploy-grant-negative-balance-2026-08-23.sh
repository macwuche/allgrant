#!/bin/bash
# Deploys e589afe: grant apply always succeeds regardless of balance (can
# go negative), "Total Grant" added to the user dashboard, Application
# Charge now displays as negative on the user side.
# Run this from the app's docroot on each host.
# Usage: bash deploy-grant-negative-balance-2026-08-23.sh
set -e
BASE="https://raw.githubusercontent.com/macwuche/allgrant/main"
FAIL=0

echo "== Pulling changed files =="
U="$BASE/app/Services/GrantService.php"
curl -sf -o "app/Services/GrantService.php" "$U" || { echo "FAILED: app/Services/GrantService.php"; FAIL=1; }
U="$BASE/assets/front/css/grant-home.css"
curl -sf -o "assets/front/css/grant-home.css" "$U" || { echo "FAILED: assets/front/css/grant-home.css"; FAIL=1; }
U="$BASE/resources/views/frontend/corporate/grant/application.blade.php"
curl -sf -o "resources/views/frontend/corporate/grant/application.blade.php" "$U" || { echo "FAILED: resources/views/frontend/corporate/grant/application.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/default/grant/application.blade.php"
curl -sf -o "resources/views/frontend/default/grant/application.blade.php" "$U" || { echo "FAILED: resources/views/frontend/default/grant/application.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/default/user/dashboard.blade.php"
curl -sf -o "resources/views/frontend/default/user/dashboard.blade.php" "$U" || { echo "FAILED: resources/views/frontend/default/user/dashboard.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/digi_vault/grant/application.blade.php"
curl -sf -o "resources/views/frontend/digi_vault/grant/application.blade.php" "$U" || { echo "FAILED: resources/views/frontend/digi_vault/grant/application.blade.php"; FAIL=1; }

if [ "$FAIL" -ne 0 ]; then
  echo "== One or more pulls FAILED — stopping before optimize:clear. Fix and re-run. =="
  exit 1
fi

echo "== All 6 files pulled clean =="
echo "== Clearing caches (view cache included) =="
php artisan optimize:clear

echo "== Done. Code is now live. =="
