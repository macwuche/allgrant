#!/bin/bash
# Deploys fdffc98: admin-editable Application/Commission Charge description
# text on grant plans (previously hardcoded, identical on every plan/theme).
# Adds 2 nullable columns to grant_plans (application_charge_text,
# commission_charge_text) -- existing plans render the exact same wording
# as before until an admin actually edits them.
#
# IMPORTANT -- this app's bare `php artisan migrate` is broken on both live
# hosts (the ~70 baseline schema migrations were never recorded in the
# migrations ledger -- see work.md 2026-08-17 #9 / email.md section 9). Do
# NOT run a bare `php artisan migrate --force`. Use the --path-scoped
# command in Step 2 below (the same technique that worked cleanly for the
# 2026-08-21 grant redesign migrations, work.md 2026-08-22 finding #2). If
# that ever fails for some other reason, the fallback raw SQL is at the
# bottom of this file's comments.
#
# Run this from the app's docroot on each host.
# Usage: bash deploy-grant-charge-text-2026-09-13.sh
set -e
BASE="https://raw.githubusercontent.com/macwuche/allgrant/main"
FAIL=0

pull() {
  mkdir -p "$(dirname "$1")"
  curl -sf -o "$1" "$BASE/$1" || { echo "FAILED: $1"; FAIL=1; }
}

echo "== Pulling changed/added files =="
pull "app/Http/Controllers/Backend/GrantPlanController.php"
pull "app/Http/Resources/GrantPlanResource.php"
pull "app/Models/GrantPlan.php"
pull "database/migrations/2026_09_13_000000_add_charge_text_to_grant_plans.php"
pull "resources/views/backend/plan/grant/create.blade.php"
pull "resources/views/backend/plan/grant/edit.blade.php"
pull "resources/views/frontend/corporate/grant/index.blade.php"
pull "resources/views/frontend/default/grant/index.blade.php"
pull "resources/views/frontend/digi_vault/grant/index.blade.php"

if [ "$FAIL" -ne 0 ]; then
  echo "== One or more pulls FAILED — stopping before optimize:clear. Fix and re-run. =="
  exit 1
fi

echo "== All 9 files pulled clean =="
echo "== Clearing caches (view cache included) =="
php artisan optimize:clear

echo "== Code is live. RUN STEP 2 NEXT, IMMEDIATELY: =="
echo "php artisan migrate --force --path=database/migrations/2026_09_13_000000_add_charge_text_to_grant_plans.php"
echo ""
echo "== If that command errors, do NOT retry with a bare 'php artisan migrate' -- =="
echo "== apply this raw SQL instead and record the ledger row by hand: =="
cat <<'SQL'
ALTER TABLE grant_plans ADD COLUMN application_charge_text TEXT NULL;
ALTER TABLE grant_plans ADD COLUMN commission_charge_text TEXT NULL;
INSERT INTO migrations (migration, batch)
  VALUES ('2026_09_13_000000_add_charge_text_to_grant_plans', (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations));
SQL
