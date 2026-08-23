#!/bin/bash
# Deploys 07343b8: removes "Airtime" from the Pay Bill menu (all 3 themes)
# and adds the "Deposit" card to the default theme's user dashboard.
# Run this from the app's docroot on each host.
# Usage: bash deploy-deposit-card-remove-airtime-2026-08-23.sh
set -e
BASE="https://raw.githubusercontent.com/macwuche/allgrant/main"
FAIL=0

echo "== Pulling changed files =="
U="$BASE/assets/front/css/grant-home.css"
curl -sf -o "assets/front/css/grant-home.css" "$U" || { echo "FAILED: assets/front/css/grant-home.css"; FAIL=1; }
U="$BASE/resources/views/frontend/corporate/pay_bill/include/bill-menu.blade.php"
curl -sf -o "resources/views/frontend/corporate/pay_bill/include/bill-menu.blade.php" "$U" || { echo "FAILED: resources/views/frontend/corporate/pay_bill/include/bill-menu.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/default/pay_bill/include/bill-menu.blade.php"
curl -sf -o "resources/views/frontend/default/pay_bill/include/bill-menu.blade.php" "$U" || { echo "FAILED: resources/views/frontend/default/pay_bill/include/bill-menu.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/default/user/dashboard.blade.php"
curl -sf -o "resources/views/frontend/default/user/dashboard.blade.php" "$U" || { echo "FAILED: resources/views/frontend/default/user/dashboard.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/digi_vault/pay_bill/include/bill-menu.blade.php"
curl -sf -o "resources/views/frontend/digi_vault/pay_bill/include/bill-menu.blade.php" "$U" || { echo "FAILED: resources/views/frontend/digi_vault/pay_bill/include/bill-menu.blade.php"; FAIL=1; }

if [ "$FAIL" -ne 0 ]; then
  echo "== One or more pulls FAILED — stopping before optimize:clear. Fix and re-run. =="
  exit 1
fi

echo "== All 5 files pulled clean =="
echo "== Clearing caches (view cache included) =="
php artisan optimize:clear

echo "== Done. Code is now live. =="
