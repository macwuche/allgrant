#!/bin/bash
# Deploys eb712cd / 8ea736f: lets a user withdraw without first
# visiting the separate "Add New Withdraw Account" page. The withdraw
# form (user/withdraw) can now create the withdraw account and submit
# the withdrawal in one request when the user has no saved account yet
# for the method they pick, via a new methodDetails() AJAX endpoint.
# Also closes an ownership gap: the withdraw_account lookup in
# withdrawNow() previously had no where('user_id', ...) check.
# Run this from the app's docroot on each host.
# Usage: bash deploy-withdraw-inline-account-2026-09-02.sh
set -e
BASE="https://raw.githubusercontent.com/macwuche/allgrant/main"
FAIL=0

echo "== Pulling changed files =="
U="$BASE/app/Http/Controllers/Frontend/WithdrawController.php"
curl -sf -o "app/Http/Controllers/Frontend/WithdrawController.php" "$U" || { echo "FAILED: app/Http/Controllers/Frontend/WithdrawController.php"; FAIL=1; }
U="$BASE/routes/web.php"
curl -sf -o "routes/web.php" "$U" || { echo "FAILED: routes/web.php"; FAIL=1; }
U="$BASE/resources/views/frontend/corporate/withdraw/now.blade.php"
curl -sf -o "resources/views/frontend/corporate/withdraw/now.blade.php" "$U" || { echo "FAILED: resources/views/frontend/corporate/withdraw/now.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/default/withdraw/now.blade.php"
curl -sf -o "resources/views/frontend/default/withdraw/now.blade.php" "$U" || { echo "FAILED: resources/views/frontend/default/withdraw/now.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/digi_vault/withdraw/now.blade.php"
curl -sf -o "resources/views/frontend/digi_vault/withdraw/now.blade.php" "$U" || { echo "FAILED: resources/views/frontend/digi_vault/withdraw/now.blade.php"; FAIL=1; }

if [ "$FAIL" -ne 0 ]; then
  echo "== One or more pulls FAILED — stopping before optimize:clear. Fix and re-run. =="
  exit 1
fi

echo "== All 5 files pulled clean =="
echo "== Clearing caches (route cache included, since a route was added) =="
php artisan optimize:clear

echo "== Done. Code is now live. =="
