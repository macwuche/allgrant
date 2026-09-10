#!/bin/bash
# Deploys d51c54c: adds the admin Email Inbox system (send/receive via
# Resend), feature-flagged off by default at Settings > Email Inbox.
# Adds new files only plus small, additive edits to existing routes/
# settings/middleware files -- nothing pre-existing is removed.
#
# IMPORTANT -- this feature also needs its 4 new database tables. That is
# NOT done by this script (this app's `php artisan migrate` is broken on
# both live hosts by a pre-existing, unrelated ledger issue -- see
# email.md section 9). Apply
# database/migrations/2026_09_10_000000_create_email_inbox_tables.php's
# schema directly (see the companion SQL this session produced) BEFORE or
# AFTER running this script -- the code is inert until the feature flag is
# turned on in Settings > Email Inbox regardless, so order between the two
# doesn't matter, but do both before enabling the flag.
#
# Run this from the app's docroot on each host.
# Usage: bash deploy-email-inbox-system-2026-09-10.sh
set -e
BASE="https://raw.githubusercontent.com/macwuche/allgrant/main"
FAIL=0

pull() {
  mkdir -p "$(dirname "$1")"
  curl -sf -o "$1" "$BASE/$1" || { echo "FAILED: $1"; FAIL=1; }
}

echo "== Pulling changed/added files =="
pull "app/Http/Controllers/Backend/EmailAddressController.php"
pull "app/Http/Controllers/Backend/EmailInboxController.php"
pull "app/Http/Controllers/Backend/SettingController.php"
pull "app/Http/Controllers/ResendInboundWebhookController.php"
pull "app/Http/Kernel.php"
pull "app/Http/Middleware/EnsureEmailInboxEnabled.php"
pull "app/Http/Middleware/VerifyCsrfToken.php"
pull "app/Models/Email.php"
pull "app/Models/EmailAddress.php"
pull "app/Models/EmailAttachment.php"
pull "app/Models/EmailWebhookEvent.php"
pull "app/Services/ResendMailService.php"
pull "config/purifier.php"
pull "config/setting.php"
pull "database/migrations/2026_09_10_000000_create_email_inbox_tables.php"
pull "database/seeders/PermissionSeeder.php"
pull "email.md"
pull "resources/views/backend/email_inbox/compose.blade.php"
pull "resources/views/backend/email_inbox/include/__compose.blade.php"
pull "resources/views/backend/email_inbox/include/__compose_form.blade.php"
pull "resources/views/backend/email_inbox/index.blade.php"
pull "resources/views/backend/email_inbox/show.blade.php"
pull "resources/views/backend/include/__side_nav.blade.php"
pull "resources/views/backend/setting/email_inbox.blade.php"
pull "resources/views/backend/setting/index.blade.php"
pull "routes/admin.php"
pull "routes/web.php"

if [ "$FAIL" -ne 0 ]; then
  echo "== One or more pulls FAILED — stopping before optimize:clear. Fix and re-run. =="
  exit 1
fi

echo "== All 26 files pulled clean =="
echo "== Clearing caches (view cache included) =="
php artisan optimize:clear

echo "== Done. Code is live. Feature flag is OFF by default (Settings > Email Inbox) =="
echo "== until the DB tables exist AND you've completed the Resend/Cloudflare runbook (email.md section 7). =="
