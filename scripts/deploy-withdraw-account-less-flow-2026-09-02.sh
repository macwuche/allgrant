#!/bin/bash
# Deploys 47a3aab / 5cd22c4: redesigns user/withdraw as a single
# account-less flow -- no "Withdraw Account" link, no saved-account
# dropdown; pick a method, fill its destination fields fresh, see a
# loading screen then a concrete "your money is on its way to
# <address>" success message. Supersedes eb712cd's inline-account
# version (still kept a save/reuse/select-existing account model).
# Run this from the app's docroot on each host.
# Usage: bash deploy-withdraw-account-less-flow-2026-09-02.sh
set -e
BASE="https://raw.githubusercontent.com/macwuche/allgrant/main"
FAIL=0

echo "== Pulling changed files =="
U="$BASE/app/Http/Controllers/Frontend/WithdrawController.php"
curl -sf -o "app/Http/Controllers/Frontend/WithdrawController.php" "$U" || { echo "FAILED: app/Http/Controllers/Frontend/WithdrawController.php"; FAIL=1; }
U="$BASE/app/Traits/Payment.php"
curl -sf -o "app/Traits/Payment.php" "$U" || { echo "FAILED: app/Traits/Payment.php"; FAIL=1; }
U="$BASE/resources/views/frontend/corporate/withdraw/include/__account.blade.php"
curl -sf -o "resources/views/frontend/corporate/withdraw/include/__account.blade.php" "$U" || { echo "FAILED: resources/views/frontend/corporate/withdraw/include/__account.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/corporate/withdraw/now.blade.php"
curl -sf -o "resources/views/frontend/corporate/withdraw/now.blade.php" "$U" || { echo "FAILED: resources/views/frontend/corporate/withdraw/now.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/default/withdraw/include/__account.blade.php"
curl -sf -o "resources/views/frontend/default/withdraw/include/__account.blade.php" "$U" || { echo "FAILED: resources/views/frontend/default/withdraw/include/__account.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/default/withdraw/now.blade.php"
curl -sf -o "resources/views/frontend/default/withdraw/now.blade.php" "$U" || { echo "FAILED: resources/views/frontend/default/withdraw/now.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/digi_vault/withdraw/include/__account.blade.php"
curl -sf -o "resources/views/frontend/digi_vault/withdraw/include/__account.blade.php" "$U" || { echo "FAILED: resources/views/frontend/digi_vault/withdraw/include/__account.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/digi_vault/withdraw/now.blade.php"
curl -sf -o "resources/views/frontend/digi_vault/withdraw/now.blade.php" "$U" || { echo "FAILED: resources/views/frontend/digi_vault/withdraw/now.blade.php"; FAIL=1; }

if [ "$FAIL" -ne 0 ]; then
  echo "== One or more pulls FAILED — stopping before optimize:clear. Fix and re-run. =="
  exit 1
fi

echo "== All 8 files pulled clean =="
echo "== Clearing caches (view cache included) =="
php artisan optimize:clear

echo "== Done. Code is now live. =="
