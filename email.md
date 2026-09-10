# Admin Email Inbox System

Living design + progress doc for the Gmail/Yahoo-style email inbox built into the admin
dashboard, relayed through Resend. Update this file as work progresses — treat it as the
source of truth for this feature, separate from the general session log in `work.md`.

## 1. Spec, as given by the user (2026-09-10)

1. A new "Emails" menu item in the admin sidebar, with its own dashboard page — a real
   inbox: admin can **send and receive** email from inside the app.
2. Gated behind a **feature flag on the admin settings page** — when off, the feature (menu
   item + routes) doesn't appear/work at all.
3. Relay provider: **Resend**, using one API key for both sending and receiving.
4. Inbound email must show **images** (a sender's embedded/inline images render) and
   **attachments** (documents) must be downloadable.
5. New mail **shows up automatically** (no full page reload needed to see it) — plus a
   manual **Refresh** button as a fallback/complement.
6. The inbox shows **our own address(es)** clearly (which mailbox you're looking at).
7. Admin can **add multiple email addresses**, the way Gmail lets you add "send mail as"
   aliases — chosen shape (see §2 decisions): **one shared inbox**, filterable by which of
   our addresses received/sent each message, not separate mailboxes per address.
8. Domain is on **Cloudflare** DNS. Use a **`mail.` subdomain** (not the bare root domain)
   for this system, on both live hosts: `mail.novabridgegrant.org` and
   `mail.futurenestfund.org`.

## 2. Decisions made with the user before building

Asked via clarifying questions on 2026-09-10; answers below are binding unless the user
overrides them later.

| Question | Answer |
|---|---|
| Which domain(s) get the `mail.` subdomain | **Both** — `mail.novabridgegrant.org` *and* `mail.futurenestfund.org`. Each live host gets its own verified Resend domain, its own webhook, its own set of addresses — they are two independent deployments with two independent databases (per `work.md` 2026-08-18 #2), so the email system is configured per-host through its own admin settings, not shared. |
| Who sets up Cloudflare DNS + Resend domain/webhook | **User does it manually** — they don't want to hand over Cloudflare/Resend API tokens. This doc includes the exact runbook (§7) they follow themselves; nothing in the app auto-creates DNS records or Resend domains. |
| Shape of "multiple addresses" | **One shared inbox, filterable by address** — every address the admin adds (e.g. `support@mail.novabridgegrant.org`, `billing@mail.novabridgegrant.org`) lands in the same inbox list; a dropdown/tab filters by "To" address. Matches Gmail's "Send mail as" aliases, not Yahoo's separate-account switcher. |

## 3. How Resend receiving actually works (verified against live Resend docs, 2026-09-10 —
do not rely on model memory for this, the product has changed over time)

This is the part most likely to be gotten wrong from memory, so every claim below was
pulled from `resend.com/docs` directly this session:

- **Setup is per-domain, two separate steps.** First verify a domain for *sending*
  (SPF/DKIM TXT records, standard). Receiving is a second, optional step on the same
  domain: Resend's Domains page gives you an **MX record** to copy into your DNS provider;
  until you add it and Resend confirms it, no inbound mail arrives. Resend explicitly
  recommends doing this on a **subdomain** (exactly what we're doing with `mail.`) to avoid
  colliding with any existing MX record on the root domain — "emails will usually only be
  delivered to the MX record with the lowest priority value."
- **Inbound webhook event is `email.received`.** You register it like any other Resend
  webhook (Dashboard → Webhooks → Add Webhook → pick `email.received` as the event type →
  give it our endpoint URL). **Critical:** the webhook payload is metadata only —
  ```json
  {
    "type": "email.received",
    "created_at": "2026-02-22T23:41:12.126Z",
    "data": {
      "email_id": "56761188-7520-42d8-8898-ff6fc54ce618",
      "created_at": "2026-02-22T23:41:11.894Z",
      "from": "onboarding@resend.dev",
      "to": ["delivered@resend.dev"],
      "bcc": [], "cc": [],
      "received_for": ["forwarded@example.com"],
      "message_id": "<111-222-333@email.example.com>",
      "subject": "Sending this example",
      "attachments": [
        {"id": "2a0c9ce0-...", "filename": "avatar.png", "content_type": "image/png",
         "content_disposition": "inline", "content_id": "img001"}
      ]
    }
  }
  ```
  **No `html`/`text`/`headers`/attachment bytes are in this payload.** We must call back
  into the Receiving API to get the body.
- **Fetching the full message:** `GET https://api.resend.com/emails/receiving/{email_id}`
  (Bearer auth). Response includes `html`, `text`, `headers`, `bcc`, `cc`, `reply_to`,
  `received_for`, `message_id`, a `raw.download_url` (signed URL to the original raw MIME,
  expires in an hour), and full `attachments[]` metadata (`id`, `filename`, `content_type`,
  `content_disposition` — `"inline"` vs `null`, `content_id`, `size`).
  - **Inline images solve themselves**: pass `?html_format=data_uri` (this is actually the
    *default*) and Resend rewrites every `cid:` reference in the returned `html` into a
    `data:` URI already carrying the image bytes — so a sender's inline images render with
    **zero cid-rewriting work on our side**. We just store/render `html` as-is.
- **Fetching attachment bytes** (for the ones that are real attachments, not inline
  images — or for saving inline images to disk too): `GET
  https://api.resend.com/emails/receiving/{email_id}/attachments` lists them with a
  `download_url` (**valid only 1 hour**) per attachment; fetch that URL immediately during
  webhook processing and persist the bytes to our own disk — we cannot rely on Resend's URL
  still being valid whenever an admin later opens the thread.
- **Sending (`POST https://api.resend.com/emails`)**: Bearer auth, JSON body — `from`,
  `to`, `subject`, `html`/`text`, `cc`/`bcc`, `reply_to`, `headers` (custom headers object),
  `attachments[]` (`content` base64 or `path` URL, `filename`, `content_type`,
  `content_id` for embedding inline images the *other* way, i.e. when we reply). Max **40MB
  per email after base64**. Returns `{ id }`.
- **Threading a reply**: no special "reply" endpoint — just send normally but set
  `headers: { "In-Reply-To": "<original message_id>", "References": "<all prior ids
  space-separated>" }` and prefix the subject with `Re: `. Standard RFC 2822 threading;
  email clients (and our own thread view) group on this.
- **Signature verification is Svix**, not a Resend-proprietary scheme. Headers:
  `svix-id`, `svix-timestamp`, `svix-signature`. Algorithm (verified against Svix's own
  docs, worked example matched exactly):
  1. Secret looks like `whsec_XXXX...`; base64-decode the part after `whsec_` to get raw
     key bytes.
  2. Signed content = `"{svix-id}.{svix-timestamp}.{raw request body}"` (raw bytes, not
     re-encoded JSON).
  3. `HMAC-SHA256(key_bytes, signed_content)`, base64-encoded = our computed signature.
  4. `svix-signature` header value is space-delimited `v1,<base64sig>` entries (Svix may
     rotate secrets, hence possibly multiple) — strip the `v1,` prefix from each and
     constant-time-compare (`hash_equals`) against ours; valid if **any** match.
  5. Defensively reject if `svix-timestamp` is outside a ~5 minute tolerance (replay
     protection).

## 4. Architecture decisions for this codebase

- **No new Composer package.** This sandbox has no `php`/`composer` binary (confirmed
  2026-09-10, consistent with every prior session per `work.md`), so nothing that requires
  `composer require` and a lockfile update is safely deployable/verifiable here. We call
  Resend's HTTP API directly with Laravel's `Http` facade (Guzzle, already a framework
  dependency) — no SDK needed for `POST /emails`, `GET /emails/receiving/{id}`, `GET
  /emails/receiving/{id}/attachments`.
- **Not reusing `spatie/laravel-webhook-client`.** It's present in `vendor/` (a transitive
  dependency, its service provider is auto-discovered) but its migration
  (`create_webhook_calls_table`) and `config/webhook-client.php` were never published — it's
  dormant, not actually wired into this app. `stripe-webhook` uses the separate, dedicated
  `spatie/laravel-stripe-webhooks` package instead. Rather than newly wiring up a generic,
  unused package (and being unable to test that plumbing with no local PHP), we build one
  small purpose-built `ResendInboundWebhookController` — easier to fully trace by reading.
- **Feature flag + settings**: follows the existing `Setting`/`config/setting.php`
  key-value pattern used by every other settings page (`site_maintenance`, `mail`, etc — see
  `app/Models/Setting.php`, `config/setting.php`). New section `email_inbox` with a
  `checkbox`/`boolean` `email_inbox_enabled` field as the feature flag, plus the Resend API
  key, webhook signing secret, and the verified `mail.` domain — all editable from one new
  admin settings page, separate from the existing SMTP-only `mail` section (that one
  configures outbound transactional mail via SMTP/`Mail` facade and is unrelated).
- **CSRF**: the inbound webhook route must NOT sit inside the `web` group's CSRF
  protection (Resend isn't sending a CSRF token). Match the exact existing pattern for
  `stripe-webhook` — add our route's URI to `App\Http\Middleware\VerifyCsrfToken::$except`
  (`app/Http/Middleware/VerifyCsrfToken.php`), and register the route at the top level of
  `routes/web.php` (outside the `auth`-protected `user` group, same as `stripe-webhook`).
  It also must not sit under `routes/admin.php` (that's wrapped in `auth:admin`).
- **Auto-refresh without a reload**: short client-side polling (e.g. every 15–20s, `fetch`
  a lightweight "new since X" JSON endpoint) plus the explicit Refresh button doing the
  same fetch on demand. We are *not* standing up websockets/Pusher for this — the app
  already uses Pusher for something else (`vendor/pusher`) but adding a new broadcast
  channel is more moving parts than this needs; polling is simple, testable without a
  browser, and matches "automatically show + a refresh button" without overbuilding.
- **Storage**: attachments (and inline images we choose to persist) saved to local disk
  under `storage/app/public/email-attachments/{email_id}/...` (this app's `FILESYSTEM_DISK`
  is `local`/`public`, no S3 configured — see `config/filesystems.php`), served through the
  existing public storage symlink like every other upload in this app.
- **Per-host configuration**: because `novabridgegrant` and `futurenestfund` are separate
  codebases-pulled-to-separate-servers with separate databases, all of §3's setup (Resend
  domain, webhook secret, API key, addresses) is stored in each host's own `settings` table
  via the admin UI — not in `.env`/code — so the same deployed code works identically on
  both hosts once each admin fills in their own host's values.

## 5. Data model

New tables (new migration(s), additive only — nothing existing touched):

**`email_addresses`** — the "add multiple email addresses" list (Gmail-alias style)
- `id`, `email` (unique), `label` (nullable, e.g. "Support"), `is_default` (bool),
  `status` (bool, active/inactive), `created_by` (admin id), timestamps

**`emails`** — one row per message, inbound or outbound
- `id`, `resend_id` (nullable — Resend's email id, unique when present), `direction`
  (`inbound`|`outbound`), `email_address_id` (FK → `email_addresses`, which of our
  addresses this belongs to), `from_address`, `to_addresses` (json), `cc_addresses` (json,
  nullable), `bcc_addresses` (json, nullable), `subject`, `html_body` (longText, nullable),
  `text_body` (longText, nullable), `snippet` (short plain-text preview for the list view),
  `message_id` (the RFC message-id string, indexed — used for threading), `in_reply_to`
  (nullable), `thread_key` (indexed — normalized root message-id a thread groups on),
  `status` (`received`|`queued`|`sent`|`failed`), `error` (nullable, failure reason),
  `is_read` (bool), `raw_headers` (json, nullable), `admin_id` (nullable FK — which admin
  sent it, for outbound), timestamps

**`email_attachments`**
- `id`, `email_id` (FK), `filename`, `mime_type`, `size` (bytes), `disk_path`, `content_id`
  (nullable — set when it's the inline-image kind, kept for reference even though inline
  images already render via `data:` URIs in `html_body`), `is_inline` (bool), timestamps

**`email_webhook_events`** — idempotency log so a Resend retry doesn't double-process
- `id`, `svix_id` (unique), `event_type`, `payload` (json), `processed_at` (nullable),
  `error` (nullable), timestamps

## 6. Backend plan

- `app/Services/ResendMailService.php` — thin wrapper: `send(array $params): array`,
  `getReceivedEmail(string $emailId): array`, `listAttachments(string $emailId): array`,
  each reading the API key from `Setting::get('email_inbox_api_key', 'email_inbox')` and
  issuing the `Http::withToken(...)` calls documented in §3. One place all Resend HTTP
  calls live, so it's the one place to point at Resend's actual current docs if their API
  ever changes.
- `app/Http/Controllers/ResendInboundWebhookController.php` (top-level, not under
  `Backend/`, since it's an unauthenticated public endpoint) — verifies the Svix signature
  by hand (§3 algorithm, no new package), logs into `email_webhook_events` keyed on
  `svix-id` (skip if already processed — Resend/Svix is at-least-once delivery), then for
  `email.received`: resolve which `email_addresses` row matches `data.to[0]` (skip/ignore if
  it's not one of our configured addresses — protects against stray mail hitting a
  catch-all), call `ResendMailService::getReceivedEmail()` for the full body, download each
  attachment's bytes immediately (`listAttachments` → fetch `download_url` → store to
  disk), then create the `emails` + `email_attachments` rows. Always returns `200` quickly
  (store-then-respond, no slow work blocking the HTTP response back to Resend, but note our
  queue is `sync` — see the deploy note in §9 about pull latency this implies).
- `app/Http/Controllers/Backend/EmailInboxController.php` — `index` (paginated list,
  filterable by address/read-unread/search), `show` (thread view, marks read), `compose`,
  `send` (validates, calls `ResendMailService::send`, stores an outbound row), `reply`
  (same, with `In-Reply-To`/`References` wired from the thread), `poll` (lightweight JSON
  "anything new since timestamp/id" for the auto-refresh + explicit Refresh button — same
  endpoint serves both), `addresses` CRUD (the alias list), `download` (streams an
  attachment).
- New permission(s) in `database/seeders/PermissionSeeder.php`, following the existing
  `'category' => 'Email Inbox Management'` convention seen for every other module — e.g.
  `email-inbox-view`, `email-inbox-send`, `email-inbox-manage-addresses`. Gate routes with
  `permission:` middleware and the sidebar entry with `@can(...)`, same as every existing
  menu item (`resources/views/backend/include/__side_nav.blade.php`).
- **Feature-flag gating, in two places**: (1) the sidebar `Emails` `<li>` is wrapped in
  `@if(setting('email_inbox_enabled', 'email_inbox')) ... @endif` in addition to its
  `@can(...)` permission check; (2) `EmailInboxController`'s routes are also guarded by a
  small middleware/constructor check that 403s if the flag is off — so toggling the setting
  fully disables the feature, not just hides the link.
- New `config/setting.php` section `email_inbox`, modeled exactly on the existing `mail`
  section (see `config/setting.php:620`), fields: `email_inbox_enabled` (checkbox/boolean),
  `email_inbox_api_key` (text — the Resend API key), `email_inbox_webhook_secret` (text —
  the `whsec_...` signing secret from the Resend webhook), `email_inbox_domain` (text —
  e.g. `mail.novabridgegrant.org`, display/reference only).

## 7. Manual setup runbook (user does this per host — nothing here is automated by us)

Repeat for **both** `novabridgegrant` and `futurenestfund`, once the code is deployed:

1. **Resend → Domains → Add Domain** → enter `mail.novabridgegrant.org` (or the
   futurenestfund equivalent). Resend gives you SPF/DKIM TXT records — add those in
   Cloudflare DNS for that domain, on the `mail` subdomain. Wait for Resend to show it
   verified.
2. Still on that domain's page in Resend, find **Receiving** → enable it → copy the **MX
   record** it gives you → add it in Cloudflare DNS (same `mail.` subdomain). This is a
   second record, separate from the SPF/DKIM ones from step 1. Wait for Resend to confirm.
3. **Resend → Webhooks → Add Webhook**:
   - Endpoint URL: `https://app.novabridgegrant.org/webhook/resend/inbound` (exact path
     TBD as it's built — this doc will be updated with the final route once it exists).
   - Event: `email.received` only.
   - Save, then copy the **signing secret** (`whsec_...`) shown — this goes into the app's
     new admin setting page (`email_inbox_webhook_secret`), not anywhere in Cloudflare.
4. **Resend → API Keys** → create (or reuse) a key with send + receiving-read permission.
   Paste it into the app's new admin setting page (`email_inbox_api_key`).
5. In the app's new **Emails settings** page: turn the feature flag on, paste the API key
   + webhook secret from steps 3–4, add at least one address under
   `@mail.novabridgegrant.org` (e.g. `support@mail.novabridgegrant.org`) via the "Add
   Address" flow.
6. Send a real test email to that address from an outside mailbox and confirm it shows up
   in the admin inbox; send one from the admin inbox back out and confirm it arrives.

## 8. UI plan

- Sidebar: new top-level `Emails` item (own icon, e.g. `mail` from Lucide — matches the
  existing icon set already used throughout `__side_nav.blade.php`), positioned near
  "Notifications"/"Customers" since it's a comms tool, not folded under Settings.
  Feature-flag + permission gated per §6.
- Inbox list page: left rail listing our configured addresses (a "which mailbox" filter —
  satisfies "show our email on inbox"), a message list (sender, subject, snippet, time,
  unread bolding, small icon for has-attachment), a **Refresh** button top-right, a
  **Compose** button. Auto-refresh polling merges new rows in without discarding scroll
  position/selection.
- Thread/detail view: rendered `html_body` in a sandboxed container (inline images already
  work via the `data:` URI rewrite from §3), an attachments strip below the body for
  non-inline files with a download link each, Reply/Reply All/Forward actions.
- Compose/reply modal or panel: From (a `<select>` of our addresses when more than one is
  configured), To/Cc/Bcc, subject, a rich-text/HTML body field, file attach input.
- Settings page (new, under the existing Settings area): the feature flag checkbox, API
  key, webhook secret, domain field, and the address-management list (add/remove/set
  default) — this is the Gmail-"Send mail as"-style list from §1 point 7.

## 9. Known constraints carried over from every other change in this codebase

- No `php`/`composer` here — every backend change is verified by careful tracing/reading,
  not by running it, then shipped via a `scripts/deploy-*.sh` (curl raw files from GitHub +
  `php artisan optimize:clear`) that the user runs on each live host, matching every prior
  deploy in `work.md`. **Correction from an earlier draft of this doc:** schema changes are
  *not* applied via `php artisan migrate --force` in this project's actual practice —
  `work.md` (2026-08-17 #9) records that `php artisan migrate` is already broken on both
  live hosts by a pre-existing, unrelated migration-ledger/DB drift. New tables have instead
  been applied as raw SQL directly against the target database (this workspace has live
  `psql` credentials for `novabridgegrant`'s Supabase project only, per `.env` — confirmed
  again this session; still no credentials for `futurenestfund`'s separate project). This
  feature's migration file
  (`database/migrations/2026_09_10_000000_create_email_inbox_tables.php`) exists as the
  canonical schema definition, but shipping it to either live host means running its
  equivalent `CREATE TABLE` statements by hand (directly via `psql` on novabridgegrant; the
  user runs the equivalent SQL themselves on futurenestfund) and recording a matching
  `migrations` ledger row, the same way `ad_sliders` was done — not by expecting
  `migrate` to work.
- `QUEUE_CONNECTION=sync` on both hosts — webhook processing (fetching the full email +
  attachment bytes from Resend) happens inline during the webhook request, not backgrounded.
  Keep this fast/bounded (a handful of small HTTP calls); if a host ever moves to a real
  queue this can be pushed to a job with no schema change.
- Real click-through verification is blocked on `novabridgegrant` needing a working
  registration flow or DB workaround (open item #1 in `work.md`, still unresolved) and
  fully blocked on `futurenestfund` (no DB credentials for that host's separate Supabase
  project from this workspace) — this feature's live verification will face the exact same
  limits flagged there. Admin-login-based testing (not registration-based) should work
  around most of it since this is an admin-only feature.

## 10. Open questions / assumptions to revisit

- Resend's exact REST response shapes above were paraphrased by a fetch tool against live
  docs, not read as raw JSON in every case (the `email.received` webhook payload and the
  `GET /emails/receiving/{id}` response *were* captured verbatim with a real example; the
  attachment-list response shape was described but not shown verbatim) — first real
  inbound test email on either host should have its actual webhook + API responses logged
  and diffed against §3 before removing this caveat.
- Reply-All / Forward are UI affordances only for now — same `send` plumbing as new
  compose, no separate backend path planned.
- Standalone per-message search is out of scope for the first pass; the list filters by
  address/read-state only initially.
- **Threading edge case — fixed 2026-09-10 (see §11).** Confirmed via Resend's live docs
  (`GET /emails/{id}` retrieve-email reference) that `POST /emails` really does return only
  its own internal id, not the RFC `Message-ID` header — exactly the suspected mismatch.
  Fixed by calling `GET /emails/{id}` right after send to fetch the real `message_id` and
  storing *that* instead. Still wants a real send → external reply → confirm-it-grouped
  test on a live host to fully close out (unverified live, code-traced only, per the usual
  no-`php`-here caveat).
- Rich HTML email rendering uses a dedicated, more permissive Purifier profile
  (`email_inbox` in `config/purifier.php`) than the rest of the app's `default` profile —
  allows table-based layouts (near-universal in real email) and a few more inline-style
  properties, while still stripping scripts/iframes/style blocks/event handlers entirely.
  Inbound HTML always goes through this before it's stored or rendered. Complex marketing
  emails may still render imperfectly (no `<style>` blocks, limited CSS) — acceptable
  starting point, not pixel-perfect.

## 11. Progress log

- **2026-09-10** — Spec gathered, clarifying questions asked and answered (§2), Resend's
  actual current API/webhook contract verified against live docs rather than assumed (§3),
  architecture decided (§4), this doc written.
- **2026-09-10** — First implementation pass built (code only — nothing deployed or run
  live yet; this sandbox still has no `php`, verified by tracing/reading as usual):
  - Migration `database/migrations/2026_09_10_000000_create_email_inbox_tables.php` —
    `email_addresses`, `emails`, `email_attachments`, `email_webhook_events`.
  - Models: `app/Models/{EmailAddress,Email,EmailAttachment,EmailWebhookEvent}.php`.
  - `config/setting.php` — new `email_inbox` section (feature flag + domain + API key +
    webhook secret), modeled on the existing `mail` section.
  - `config/purifier.php` — new `email_inbox` profile (§10, table-permissive but still
    strips scripts/handlers) alongside the app's existing `default` one.
  - `app/Services/ResendMailService.php` — all Resend HTTP calls (`send`,
    `getReceivedEmail`, `listAttachments`, `downloadAttachment`) plus
    `verifyWebhookSignature()` (Svix, hand-implemented per §3, no new dependency).
  - `app/Http/Controllers/ResendInboundWebhookController.php` — the public `email.received`
    webhook endpoint: verifies signature, idempotency-logs via `email_webhook_events`,
    resolves which configured `EmailAddress` it's for (ignores anything addressed to a
    catch-all address we haven't configured), fetches full content, Purifier-sanitizes the
    HTML, resolves thread grouping, downloads and stores attachments.
  - `app/Http/Middleware/EnsureEmailInboxEnabled.php` (alias `email-inbox-enabled` in
    `app/Http/Kernel.php`) — enforces the feature flag at the route level, not just the
    sidebar link.
  - `app/Http/Controllers/Backend/EmailInboxController.php` (index/poll/show/compose/
    send/reply/download) and `EmailAddressController.php` (the address alias CRUD).
  - Routes added to `routes/admin.php` (`admin.email-inbox.*`, `admin.email-addresses.*`,
    `admin.settings.email-inbox`) and the public webhook to `routes/web.php`
    (`webhook/resend/inbound`, named `webhook.resend.inbound`) — added to
    `App\Http\Middleware\VerifyCsrfToken::$except` the same way `stripe-webhook` already is.
  - Permissions added to `database/seeders/PermissionSeeder.php`: `email-inbox-setting`
    (Setting Management), `email-inbox-view` / `email-inbox-send` /
    `email-inbox-manage-addresses` (new "Email Inbox Management" category). Not yet applied
    to either live database — the "Super Admin" role bypasses per-permission checks
    entirely (`Gate::before` in `AuthServiceProvider`), so the primary admin account works
    immediately once the code ships; any other custom role needs these attached explicitly
    through the existing Manage Roles screen.
  - Sidebar entry in `resources/views/backend/include/__side_nav.blade.php` (feature-flag
    + `@can('email-inbox-view')` gated) and a new "Email Inbox" tab in
    `resources/views/backend/setting/index.blade.php`.
  - Views: `backend/setting/email_inbox.blade.php` (flag + credentials + the address list,
    including a read-only copyable Webhook URL field for the §7 runbook),
    `backend/email_inbox/index.blade.php` (list + address filter rail + search + Refresh
    button + 20s poll, reusing the theme's existing `.notification-list` styling — no new
    CSS needed), `show.blade.php` (thread view + reply, sanitized HTML rendered inline),
    `compose.blade.php` + `include/__compose.blade.php` (modal) sharing one
    `include/__compose_form.blade.php` partial, body field using the theme's existing
    Summernote integration (`class="summernote"`, auto-initialized by
    `assets/backend/js/main.js` — no new JS library needed).
- **2026-09-10** — Deployed to **futurenestfund**, user-driven from cPanel Terminal
  (this workspace has no shell/DB access to that host):
  - Code: `scripts/deploy-email-inbox-system-2026-09-10.sh` ran clean, all 26 files pulled,
    `optimize:clear` no errors.
  - Schema: rather than the raw-SQL file (§9), used `php artisan migrate
    --path=database/migrations/2026_09_10_000000_create_email_inbox_tables.php --force` --
    this host has a working `php`/`artisan`, and scoping `migrate` to just the one new
    migration file sidesteps the pre-existing broken migration entirely. Confirmed all 4
    tables exist via `Schema::hasTable()`. **This is now the preferred method over raw SQL
    wherever a host has artisan access** (novabridgegrant included) -- more reliable than
    hand-written DDL, no drift risk.
  - Permissions: the tinker one-liner approach failed repeatedly and confusingly (flaky
    3-of-4 counts that changed which one was "missing" between checks) -- root-caused to
    the user's terminal reflowing long pasted single-line commands into multiple physical
    lines, which silently dropped `--execute=...` and left bare `php artisan tinker`
    entering the interactive shell instead. Fixed by shipping
    `scripts/email-inbox-seed-permissions.php`, a standalone idempotent script fetched via
    `curl` (sidesteps paste entirely) and run directly -- hit one more bug in it
    (`__DIR__/../vendor/autoload.php` assumed the script lives in a `scripts/` subfolder,
    but `curl -O` drops it straight into the docroot, so the path pointed one level too
    high; fixed to use `getcwd()` instead, pushed as `6ed4818`). Confirmed all 4
    permissions present via the script's own output.
  - **Lesson for the rest of this rollout and any future one**: this user's terminal does
    not reliably survive long single-line pasted commands (multi-flag, long URLs, shell
    substitutions) -- it silently reflows/splits them. Keep every instruction to short,
    single-purpose command lines; prefer a fetched script over a pasted one-liner for
    anything nontrivial.
  - **futurenestfund status: code + schema + permissions all confirmed live.** Not yet
    done: Resend domain verification/receiving/webhook for `mail.futurenestfund.org`,
    Cloudflare DNS records, turning the feature flag on, adding an address, live
    send/receive verification (§7 Steps C-E) -- all still ahead.
  - novabridgegrant: not yet started this rollout.
- **2026-09-10** — Removed the "Manage Addresses" button from the Mailboxes widget on the
  inbox index page (`backend/email_inbox/index.blade.php`) per user request. The address
  management screen itself is untouched, still reachable via Settings > Email Inbox.
- **2026-09-10** — User reported broken threading in both directions: a recipient's reply
  to our sent mail shows up as a new, ungrouped message in the admin inbox; and our reply
  to an inbound message shows up as a new, unthreaded message in the recipient's own
  mailbox. Root-caused and fixed two bugs:
  - **The §10 threading edge case, confirmed real** (verified against Resend's live
    `GET /emails/{id}` retrieve-email docs this session, not memory): `deliver()` was
    storing `POST /emails`'s response `id` (Resend's own internal id) as `emails.message_id`
    instead of the actual RFC `Message-ID` header put on the outgoing mail. A recipient's
    reply echoes back the *real* header value in `In-Reply-To`, which never matched what we
    had stored, so `resolveThreadKey()` always fell through to "start a new thread" for any
    reply to something we sent — this is what explains the reply showing up as a new
    message in the admin inbox. Fixed in `EmailInboxController::deliver()` and
    `ResendMailService` (new `getSentEmail()`): after a successful send, follow up with
    `GET /emails/{id}` and store its real `message_id` instead of the send-response id.
  - **A second, separate bug**: the flat inbox list shows every individual message —
    inbound *and* our own outbound sends — as its own row, and the Reply form on the
    thread/detail page always targets whichever specific message the admin opened, not
    necessarily the latest inbound one. If an admin opened one of *our own* sent rows and
    hit Reply, `reply()` set `to` from that row's `from_address` — which for an outbound row
    is one of *our* addresses, not the recipient's — so the reply would be addressed back to
    ourselves rather than the actual other party. Fixed in `EmailInboxController::reply()`
    to resolve "the other party" based on the opened message's `direction` (inbound →
    `from_address`, outbound → `to_addresses`); also fixed the "Replying to" label in
    `show.blade.php` which had the same mismatch.
  - Not yet live-verified (no `php` here, per §9) — needs a real cross-account send/reply
    round trip on a deployed host to fully confirm both directions now group correctly.
- **2026-09-10** — Grouped the inbox index by thread instead of listing every individual
  message as its own row (this is what made it easy to open one of *our own* sent messages
  and hit Reply, triggering the bug above). Each row is now a thread's latest message, with
  a "N messages" badge when a thread has more than one, and bolded only when *any* message
  in the thread is unread (not just the latest). `EmailInboxController`: new
  `latestPerThread()` (MAX(id)-per-`thread_key` subquery — portable across Postgres and
  MySQL, since novabridgegrant runs Postgres/Supabase and futurenestfund's cPanel host is
  unconfirmed, deliberately not using Postgres-only `DISTINCT ON`) and `attachThreadCounts()`
  shared by `index()` and `poll()`; `poll()` now returns one summary row per thread with new
  activity, keyed by `thread_key`. `Email::otherParty()` (new model helper, also used by
  `reply()`'s `to` fix and the show-page label) centralizes "who this thread is with"
  regardless of which direction the latest/opened message happens to be. Client-side poll
  merge in `index.blade.php` now keys off `data-thread-key` and replaces (rather than
  duplicates) a thread's existing row when it gets bumped by a new message. Not yet
  live-verified for the same reason as above.
- **2026-09-10** — Pushed to GitHub (`main`) and deploying to **futurenestfund** via
  `scripts/deploy-email-threading-fix-2026-09-10.sh` (code-only, no new tables/permissions —
  6 files: `EmailInboxController.php`, `Email.php`, `ResendMailService.php`,
  `email_inbox/index.blade.php`, `email_inbox/show.blade.php`, this doc). novabridgegrant not
  yet on this fix.
