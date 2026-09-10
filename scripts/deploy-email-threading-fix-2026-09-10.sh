#!/bin/bash
# Deploys the email-inbox threading/UI fixes on top of the already-live
# Email Inbox system (see email.md section 11 progress log, 2026-09-10):
#
#   1. Removed the "Manage Addresses" button from the Mailboxes widget on
#      the inbox index page (still reachable via Settings > Email Inbox).
#   2. Fixed emails.message_id on our own sent mail: it was storing
#      Resend's send-response id instead of the real RFC Message-ID header,
#      which is what a recipient's reply echoes back in In-Reply-To -- this
#      is why a recipient's reply to our mail came in as a new, ungrouped
#      message instead of threading.
#   3. Fixed reply() addressing itself back to us when a reply was composed
#      from one of our own sent messages (reachable because the inbox used
#      to list every individual message, ours included, as its own row).
#   4. Grouped the inbox index by thread instead of listing every message
#      -- one row per conversation, with a message-count badge, bolded when
#      any message in the thread is unread (fixes the root discoverability
#      cause behind #3).
#
# No new database tables/columns, no new permissions -- code-only. Feature
# flag/behavior otherwise unchanged.
#
# Run this from the app's docroot on each host.
# Usage: bash deploy-email-threading-fix-2026-09-10.sh
set -e
BASE="https://raw.githubusercontent.com/macwuche/allgrant/main"
FAIL=0

pull() {
  mkdir -p "$(dirname "$1")"
  curl -sf -o "$1" "$BASE/$1" || { echo "FAILED: $1"; FAIL=1; }
}

echo "== Pulling changed files =="
pull "app/Http/Controllers/Backend/EmailInboxController.php"
pull "app/Models/Email.php"
pull "app/Services/ResendMailService.php"
pull "resources/views/backend/email_inbox/index.blade.php"
pull "resources/views/backend/email_inbox/show.blade.php"
pull "email.md"

if [ "$FAIL" -ne 0 ]; then
  echo "== One or more pulls FAILED — stopping before optimize:clear. Fix and re-run. =="
  exit 1
fi

echo "== All 6 files pulled clean =="
echo "== Clearing caches (view cache included) =="
php artisan optimize:clear

echo "== Done. Code is live. =="
echo "== Worth a real send -> external reply -> confirm-it-grouped test now that this is deployed. =="
