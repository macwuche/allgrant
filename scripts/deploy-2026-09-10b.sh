#!/bin/bash
# Deploys two more email-inbox fixes on top of the already-live threading fix
# (see email.md section 11 progress log, 2026-09-10):
#
#   1. Double-submit fix: clicking Send/Send Reply multiple times while the
#      body field has text sent the same email multiple times. Fixed with a
#      small opt-in guard in assets/backend/js/main.js (form.js-single-submit)
#      applied to all three email-inbox forms.
#   2. Outbound email template: compose/reply mail now goes out wrapped in a
#      branded, responsive header/footer (new
#      resources/views/backend/email_inbox/mail/wrapper.blade.php) instead of
#      as a bare body. Two new Settings fields (accent color, footer text) --
#      see email.md section 12 for the full build plan and reasoning.
#
# No new database tables/columns, no new permissions -- code + two new
# Settings fields (which work via config defaults immediately, no DB seeding
# needed -- see Setting::getDefaultValueForField).
#
# Run this from the app's docroot on each host.
# Usage: bash deploy-2026-09-10b.sh
# (renamed short from deploy-email-template-and-double-submit-fix-2026-09-10.sh
# after a long curl command got mis-split when pasted into the futurenestfund
# terminal -- see email.md's terminal-paste lesson, section 11)
set -e
BASE="https://raw.githubusercontent.com/macwuche/allgrant/main"
FAIL=0

pull() {
  mkdir -p "$(dirname "$1")"
  curl -sf -o "$1" "$BASE/$1" || { echo "FAILED: $1"; FAIL=1; }
}

echo "== Pulling changed/added files =="
pull "app/Http/Controllers/Backend/EmailInboxController.php"
pull "assets/backend/js/main.js"
pull "config/setting.php"
pull "resources/views/backend/email_inbox/compose.blade.php"
pull "resources/views/backend/email_inbox/include/__compose.blade.php"
pull "resources/views/backend/email_inbox/mail/wrapper.blade.php"
pull "resources/views/backend/email_inbox/show.blade.php"
pull "resources/views/backend/setting/email_inbox.blade.php"
pull "email.md"

if [ "$FAIL" -ne 0 ]; then
  echo "== One or more pulls FAILED — stopping before optimize:clear. Fix and re-run. =="
  exit 1
fi

echo "== All 9 files pulled clean =="
echo "== Clearing caches (view cache included) =="
php artisan optimize:clear

echo "== Done. Code is live. =="
echo "== New Settings > Email Inbox fields (Accent Color, Footer Text) work with their =="
echo "== config defaults immediately -- visit that page and Save once you want to override them. =="
echo "== Worth sending a real test email now and checking it renders correctly on desktop + a phone. =="
