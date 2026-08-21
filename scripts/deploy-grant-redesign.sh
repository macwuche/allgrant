#!/bin/bash
# Deploys branch fix/grant-redesign-non-repayable (merged to main, c5f2f4e) onto
# app.novabridgegrant.org. Run from the docroot: /home/novabrid/app.novabridgegrant.org
# Usage: bash deploy-grant-redesign.sh
set -e
BASE="https://raw.githubusercontent.com/macwuche/allgrant/main"
FAIL=0

echo "== Removing files deleted in this change =="
rm -f "app/Http/Resources/GrantTransactionResource.php"
rm -f "app/Models/GrantTransaction.php"

echo "== Pulling changed/added files =="
U="$BASE/app/Enums/GrantStatus.php"
curl -sf -o "app/Enums/GrantStatus.php" "$U" || { echo "FAILED: app/Enums/GrantStatus.php"; FAIL=1; }
U="$BASE/app/Enums/TxnType.php"
curl -sf -o "app/Enums/TxnType.php" "$U" || { echo "FAILED: app/Enums/TxnType.php"; FAIL=1; }
U="$BASE/app/helpers.php"
curl -sf -o "app/helpers.php" "$U" || { echo "FAILED: app/helpers.php"; FAIL=1; }
U="$BASE/app/Http/Controllers/Api/DashboardController.php"
curl -sf -o "app/Http/Controllers/Api/DashboardController.php" "$U" || { echo "FAILED: app/Http/Controllers/Api/DashboardController.php"; FAIL=1; }
U="$BASE/app/Http/Controllers/Api/GrantController.php"
curl -sf -o "app/Http/Controllers/Api/GrantController.php" "$U" || { echo "FAILED: app/Http/Controllers/Api/GrantController.php"; FAIL=1; }
U="$BASE/app/Http/Controllers/Backend/GrantController.php"
curl -sf -o "app/Http/Controllers/Backend/GrantController.php" "$U" || { echo "FAILED: app/Http/Controllers/Backend/GrantController.php"; FAIL=1; }
U="$BASE/app/Http/Controllers/Backend/GrantPlanController.php"
curl -sf -o "app/Http/Controllers/Backend/GrantPlanController.php" "$U" || { echo "FAILED: app/Http/Controllers/Backend/GrantPlanController.php"; FAIL=1; }
U="$BASE/app/Http/Controllers/CronJobController.php"
curl -sf -o "app/Http/Controllers/CronJobController.php" "$U" || { echo "FAILED: app/Http/Controllers/CronJobController.php"; FAIL=1; }
U="$BASE/app/Http/Controllers/Frontend/DashboardController.php"
curl -sf -o "app/Http/Controllers/Frontend/DashboardController.php" "$U" || { echo "FAILED: app/Http/Controllers/Frontend/DashboardController.php"; FAIL=1; }
U="$BASE/app/Http/Controllers/Frontend/GrantController.php"
curl -sf -o "app/Http/Controllers/Frontend/GrantController.php" "$U" || { echo "FAILED: app/Http/Controllers/Frontend/GrantController.php"; FAIL=1; }
U="$BASE/app/Http/Controllers/Frontend/TransactionController.php"
curl -sf -o "app/Http/Controllers/Frontend/TransactionController.php" "$U" || { echo "FAILED: app/Http/Controllers/Frontend/TransactionController.php"; FAIL=1; }
U="$BASE/app/Http/Resources/GrantDetailsResource.php"
curl -sf -o "app/Http/Resources/GrantDetailsResource.php" "$U" || { echo "FAILED: app/Http/Resources/GrantDetailsResource.php"; FAIL=1; }
U="$BASE/app/Http/Resources/GrantPlanResource.php"
curl -sf -o "app/Http/Resources/GrantPlanResource.php" "$U" || { echo "FAILED: app/Http/Resources/GrantPlanResource.php"; FAIL=1; }
U="$BASE/app/Models/Grant.php"
curl -sf -o "app/Models/Grant.php" "$U" || { echo "FAILED: app/Models/Grant.php"; FAIL=1; }
U="$BASE/app/Models/GrantPlan.php"
curl -sf -o "app/Models/GrantPlan.php" "$U" || { echo "FAILED: app/Models/GrantPlan.php"; FAIL=1; }
U="$BASE/app/Services/GrantService.php"
curl -sf -o "app/Services/GrantService.php" "$U" || { echo "FAILED: app/Services/GrantService.php"; FAIL=1; }
U="$BASE/assets/front/css/grant-plan.css"
curl -sf -o "assets/front/css/grant-plan.css" "$U" || { echo "FAILED: assets/front/css/grant-plan.css"; FAIL=1; }
U="$BASE/database/migrations/2026_08_21_000001_redesign_grant_plans_for_non_repayable_grants.php"
curl -sf -o "database/migrations/2026_08_21_000001_redesign_grant_plans_for_non_repayable_grants.php" "$U" || { echo "FAILED: database/migrations/2026_08_21_000001_redesign_grant_plans_for_non_repayable_grants.php"; FAIL=1; }
U="$BASE/database/migrations/2026_08_21_000002_redesign_grants_for_non_repayable_grants.php"
curl -sf -o "database/migrations/2026_08_21_000002_redesign_grants_for_non_repayable_grants.php" "$U" || { echo "FAILED: database/migrations/2026_08_21_000002_redesign_grants_for_non_repayable_grants.php"; FAIL=1; }
U="$BASE/database/migrations/2026_08_21_000003_drop_grant_transactions_table.php"
curl -sf -o "database/migrations/2026_08_21_000003_drop_grant_transactions_table.php" "$U" || { echo "FAILED: database/migrations/2026_08_21_000003_drop_grant_transactions_table.php"; FAIL=1; }
U="$BASE/database/migrations/2026_08_21_000004_remove_grant_installment_cron_job.php"
curl -sf -o "database/migrations/2026_08_21_000004_remove_grant_installment_cron_job.php" "$U" || { echo "FAILED: database/migrations/2026_08_21_000004_remove_grant_installment_cron_job.php"; FAIL=1; }
U="$BASE/database/seeders/CronJobSeeder.php"
curl -sf -o "database/seeders/CronJobSeeder.php" "$U" || { echo "FAILED: database/seeders/CronJobSeeder.php"; FAIL=1; }
U="$BASE/database/seeders/PermissionSeeder.php"
curl -sf -o "database/seeders/PermissionSeeder.php" "$U" || { echo "FAILED: database/seeders/PermissionSeeder.php"; FAIL=1; }
U="$BASE/database/seeders/PushNotificationSeeder.php"
curl -sf -o "database/seeders/PushNotificationSeeder.php" "$U" || { echo "FAILED: database/seeders/PushNotificationSeeder.php"; FAIL=1; }
U="$BASE/resources/views/backend/grant/details.blade.php"
curl -sf -o "resources/views/backend/grant/details.blade.php" "$U" || { echo "FAILED: resources/views/backend/grant/details.blade.php"; FAIL=1; }
U="$BASE/resources/views/backend/grant/include/__grant_status.blade.php"
curl -sf -o "resources/views/backend/grant/include/__grant_status.blade.php" "$U" || { echo "FAILED: resources/views/backend/grant/include/__grant_status.blade.php"; FAIL=1; }
U="$BASE/resources/views/backend/grant/index.blade.php"
curl -sf -o "resources/views/backend/grant/index.blade.php" "$U" || { echo "FAILED: resources/views/backend/grant/index.blade.php"; FAIL=1; }
U="$BASE/resources/views/backend/grant/subscribe.blade.php"
curl -sf -o "resources/views/backend/grant/subscribe.blade.php" "$U" || { echo "FAILED: resources/views/backend/grant/subscribe.blade.php"; FAIL=1; }
U="$BASE/resources/views/backend/include/__side_nav.blade.php"
curl -sf -o "resources/views/backend/include/__side_nav.blade.php" "$U" || { echo "FAILED: resources/views/backend/include/__side_nav.blade.php"; FAIL=1; }
U="$BASE/resources/views/backend/plan/grant/create.blade.php"
curl -sf -o "resources/views/backend/plan/grant/create.blade.php" "$U" || { echo "FAILED: resources/views/backend/plan/grant/create.blade.php"; FAIL=1; }
U="$BASE/resources/views/backend/plan/grant/edit.blade.php"
curl -sf -o "resources/views/backend/plan/grant/edit.blade.php" "$U" || { echo "FAILED: resources/views/backend/plan/grant/edit.blade.php"; FAIL=1; }
U="$BASE/resources/views/backend/plan/grant/index.blade.php"
curl -sf -o "resources/views/backend/plan/grant/index.blade.php" "$U" || { echo "FAILED: resources/views/backend/plan/grant/index.blade.php"; FAIL=1; }
U="$BASE/resources/views/backend/user/include/__grant.blade.php"
curl -sf -o "resources/views/backend/user/include/__grant.blade.php" "$U" || { echo "FAILED: resources/views/backend/user/include/__grant.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/corporate/grant/application.blade.php"
curl -sf -o "resources/views/frontend/corporate/grant/application.blade.php" "$U" || { echo "FAILED: resources/views/frontend/corporate/grant/application.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/corporate/grant/details.blade.php"
curl -sf -o "resources/views/frontend/corporate/grant/details.blade.php" "$U" || { echo "FAILED: resources/views/frontend/corporate/grant/details.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/corporate/grant/history.blade.php"
curl -sf -o "resources/views/frontend/corporate/grant/history.blade.php" "$U" || { echo "FAILED: resources/views/frontend/corporate/grant/history.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/corporate/grant/index.blade.php"
curl -sf -o "resources/views/frontend/corporate/grant/index.blade.php" "$U" || { echo "FAILED: resources/views/frontend/corporate/grant/index.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/corporate/home/include/__grantcalculatorsection.blade.php"
curl -sf -o "resources/views/frontend/corporate/home/include/__grantcalculatorsection.blade.php" "$U" || { echo "FAILED: resources/views/frontend/corporate/home/include/__grantcalculatorsection.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/corporate/home/include/__plansection.blade.php"
curl -sf -o "resources/views/frontend/corporate/home/include/__plansection.blade.php" "$U" || { echo "FAILED: resources/views/frontend/corporate/home/include/__plansection.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/corporate/user/dashboard.blade.php"
curl -sf -o "resources/views/frontend/corporate/user/dashboard.blade.php" "$U" || { echo "FAILED: resources/views/frontend/corporate/user/dashboard.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/default/grant/application.blade.php"
curl -sf -o "resources/views/frontend/default/grant/application.blade.php" "$U" || { echo "FAILED: resources/views/frontend/default/grant/application.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/default/grant/details.blade.php"
curl -sf -o "resources/views/frontend/default/grant/details.blade.php" "$U" || { echo "FAILED: resources/views/frontend/default/grant/details.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/default/grant/history.blade.php"
curl -sf -o "resources/views/frontend/default/grant/history.blade.php" "$U" || { echo "FAILED: resources/views/frontend/default/grant/history.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/default/grant/index.blade.php"
curl -sf -o "resources/views/frontend/default/grant/index.blade.php" "$U" || { echo "FAILED: resources/views/frontend/default/grant/index.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/default/home/include/__grantcalculatorsection.blade.php"
curl -sf -o "resources/views/frontend/default/home/include/__grantcalculatorsection.blade.php" "$U" || { echo "FAILED: resources/views/frontend/default/home/include/__grantcalculatorsection.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/default/home/include/__plansection.blade.php"
curl -sf -o "resources/views/frontend/default/home/include/__plansection.blade.php" "$U" || { echo "FAILED: resources/views/frontend/default/home/include/__plansection.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/default/user/dashboard.blade.php"
curl -sf -o "resources/views/frontend/default/user/dashboard.blade.php" "$U" || { echo "FAILED: resources/views/frontend/default/user/dashboard.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/digi_vault/grant/application.blade.php"
curl -sf -o "resources/views/frontend/digi_vault/grant/application.blade.php" "$U" || { echo "FAILED: resources/views/frontend/digi_vault/grant/application.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/digi_vault/grant/details.blade.php"
curl -sf -o "resources/views/frontend/digi_vault/grant/details.blade.php" "$U" || { echo "FAILED: resources/views/frontend/digi_vault/grant/details.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/digi_vault/grant/history.blade.php"
curl -sf -o "resources/views/frontend/digi_vault/grant/history.blade.php" "$U" || { echo "FAILED: resources/views/frontend/digi_vault/grant/history.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/digi_vault/grant/index.blade.php"
curl -sf -o "resources/views/frontend/digi_vault/grant/index.blade.php" "$U" || { echo "FAILED: resources/views/frontend/digi_vault/grant/index.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/digi_vault/home/include/__grantcalculatorsection.blade.php"
curl -sf -o "resources/views/frontend/digi_vault/home/include/__grantcalculatorsection.blade.php" "$U" || { echo "FAILED: resources/views/frontend/digi_vault/home/include/__grantcalculatorsection.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/digi_vault/home/include/__plansection.blade.php"
curl -sf -o "resources/views/frontend/digi_vault/home/include/__plansection.blade.php" "$U" || { echo "FAILED: resources/views/frontend/digi_vault/home/include/__plansection.blade.php"; FAIL=1; }
U="$BASE/resources/views/frontend/digi_vault/user/dashboard.blade.php"
curl -sf -o "resources/views/frontend/digi_vault/user/dashboard.blade.php" "$U" || { echo "FAILED: resources/views/frontend/digi_vault/user/dashboard.blade.php"; FAIL=1; }
U="$BASE/routes/admin.php"
curl -sf -o "routes/admin.php" "$U" || { echo "FAILED: routes/admin.php"; FAIL=1; }
U="$BASE/routes/api.php"
curl -sf -o "routes/api.php" "$U" || { echo "FAILED: routes/api.php"; FAIL=1; }
U="$BASE/routes/web.php"
curl -sf -o "routes/web.php" "$U" || { echo "FAILED: routes/web.php"; FAIL=1; }

if [ "$FAIL" -ne 0 ]; then
  echo "== One or more pulls FAILED — stopping before optimize:clear. Fix and re-run. =="
  exit 1
fi

echo "== All 57 files pulled, 2 files removed. Running optimize:clear =="
php artisan optimize:clear

echo "== Done. Code is now live. RUN THE MIGRATIONS NEXT, IMMEDIATELY. =="
