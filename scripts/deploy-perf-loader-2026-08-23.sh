#!/bin/bash
# Deploys today's two changes (merged to main: b6edf0d dashboard query/connection
# fixes, 77f0809 skeleton + Lottie loading screens) onto a live cPanel host.
# Run this from the app's docroot — for novabridgegrant that's
# /home/novabrid/app.novabridgegrant.org, for futurenestfund it's
# /home/portvina/app.futurenestfund.org (per work.md 2026-08-19 #1). Same
# script, run once per host.
# Usage: bash deploy-perf-loader-2026-08-23.sh
set -e
BASE="https://raw.githubusercontent.com/macwuche/allgrant/main"
FAIL=0

echo "== Pulling changed/added files =="
U="$BASE/app/Http/Controllers/Frontend/DashboardController.php"
curl -sf -o "app/Http/Controllers/Frontend/DashboardController.php" "$U" || { echo "FAILED: app/Http/Controllers/Frontend/DashboardController.php"; FAIL=1; }
U="$BASE/app/Models/User.php"
curl -sf -o "app/Models/User.php" "$U" || { echo "FAILED: app/Models/User.php"; FAIL=1; }
U="$BASE/config/database.php"
curl -sf -o "config/database.php" "$U" || { echo "FAILED: config/database.php"; FAIL=1; }
U="$BASE/resources/views/backend/auth/index.blade.php"
curl -sf -o "resources/views/backend/auth/index.blade.php" "$U" || { echo "FAILED: resources/views/backend/auth/index.blade.php"; FAIL=1; }
U="$BASE/resources/views/backend/layouts/app.blade.php"
curl -sf -o "resources/views/backend/layouts/app.blade.php" "$U" || { echo "FAILED: resources/views/backend/layouts/app.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/corporate/layouts/auth.blade.php"
curl -sf -o "resources/views/frontend/corporate/layouts/auth.blade.php" "$U" || { echo "FAILED: resources/views/frontend/corporate/layouts/auth.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/corporate/layouts/user.blade.php"
curl -sf -o "resources/views/frontend/corporate/layouts/user.blade.php" "$U" || { echo "FAILED: resources/views/frontend/corporate/layouts/user.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/default/layouts/auth.blade.php"
curl -sf -o "resources/views/frontend/default/layouts/auth.blade.php" "$U" || { echo "FAILED: resources/views/frontend/default/layouts/auth.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/default/layouts/user.blade.php"
curl -sf -o "resources/views/frontend/default/layouts/user.blade.php" "$U" || { echo "FAILED: resources/views/frontend/default/layouts/user.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/default/user/dashboard.blade.php"
curl -sf -o "resources/views/frontend/default/user/dashboard.blade.php" "$U" || { echo "FAILED: resources/views/frontend/default/user/dashboard.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/digi_vault/layouts/auth.blade.php"
curl -sf -o "resources/views/frontend/digi_vault/layouts/auth.blade.php" "$U" || { echo "FAILED: resources/views/frontend/digi_vault/layouts/auth.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/digi_vault/layouts/user.blade.php"
curl -sf -o "resources/views/frontend/digi_vault/layouts/user.blade.php" "$U" || { echo "FAILED: resources/views/frontend/digi_vault/layouts/user.blade.php"; FAIL=1; }

echo "== Pulling new files (skeleton/Lottie loader) =="
mkdir -p resources/views/global assets/global/js assets/global/lottie
U="$BASE/resources/views/global/_skeleton_loader.blade.php"
curl -sf -o "resources/views/global/_skeleton_loader.blade.php" "$U" || { echo "FAILED: resources/views/global/_skeleton_loader.blade.php"; FAIL=1; }
U="$BASE/assets/global/js/lottie-light.min.js"
curl -sf -o "assets/global/js/lottie-light.min.js" "$U" || { echo "FAILED: assets/global/js/lottie-light.min.js"; FAIL=1; }
U="$BASE/assets/global/lottie/loading.json"
curl -sf -o "assets/global/lottie/loading.json" "$U" || { echo "FAILED: assets/global/lottie/loading.json"; FAIL=1; }

if [ "$FAIL" -ne 0 ]; then
  echo "== One or more pulls FAILED — stopping before touching .env/cache. Fix and re-run. =="
  exit 1
fi

echo "== All files pulled clean =="

echo "== Adding DB_PERSISTENT=true to .env if not already present =="
grep -q '^DB_PERSISTENT=' .env || echo 'DB_PERSISTENT=true' >> .env

echo "== Clearing caches (view cache included — the loader is in the layouts) =="
php artisan optimize:clear

echo "== Done. Code is now live. =="
