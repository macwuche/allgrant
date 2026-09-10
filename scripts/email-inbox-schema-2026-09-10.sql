-- Schema for the admin Email Inbox system (see email.md at the repo root).
-- Mirrors database/migrations/2026_09_10_000000_create_email_inbox_tables.php
-- exactly -- run this directly against a host's Supabase/Postgres database
-- since `php artisan migrate` is broken on both live hosts (pre-existing
-- ledger drift, see email.md section 9). Purely additive: 4 new tables,
-- nothing existing is touched. Safe to re-run (IF NOT EXISTS guards).
--
-- After running this block, also run the two INSERT blocks below once:
-- the migrations-ledger row (keeps the ledger honest if `migrate` is ever
-- fixed) and the new permission rows (so the feature's permissions exist --
-- the "Super-Admin" role bypasses per-permission checks entirely, so the
-- primary admin account works immediately either way; only needed if you
-- want to grant a *different* role access to this feature).

BEGIN;

CREATE TABLE IF NOT EXISTS email_addresses (
    id bigserial PRIMARY KEY,
    email varchar(255) NOT NULL UNIQUE,
    label varchar(255),
    is_default boolean NOT NULL DEFAULT false,
    status boolean NOT NULL DEFAULT true,
    created_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);

CREATE TABLE IF NOT EXISTS emails (
    id bigserial PRIMARY KEY,
    resend_id varchar(255) UNIQUE,
    direction varchar(255) NOT NULL CHECK (direction IN ('inbound', 'outbound')),
    email_address_id bigint REFERENCES email_addresses (id) ON DELETE SET NULL,
    from_address varchar(255) NOT NULL,
    to_addresses json NOT NULL,
    cc_addresses json,
    bcc_addresses json,
    subject varchar(255),
    html_body text,
    text_body text,
    snippet varchar(255),
    message_id varchar(255),
    in_reply_to varchar(255),
    thread_key varchar(255),
    status varchar(255) NOT NULL DEFAULT 'received',
    error text,
    is_read boolean NOT NULL DEFAULT false,
    raw_headers json,
    admin_id bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);
CREATE INDEX IF NOT EXISTS emails_message_id_index ON emails (message_id);
CREATE INDEX IF NOT EXISTS emails_in_reply_to_index ON emails (in_reply_to);
CREATE INDEX IF NOT EXISTS emails_thread_key_index ON emails (thread_key);

CREATE TABLE IF NOT EXISTS email_attachments (
    id bigserial PRIMARY KEY,
    email_id bigint NOT NULL REFERENCES emails (id) ON DELETE CASCADE,
    filename varchar(255) NOT NULL,
    mime_type varchar(255),
    size bigint NOT NULL DEFAULT 0,
    disk_path varchar(255) NOT NULL,
    content_id varchar(255),
    is_inline boolean NOT NULL DEFAULT false,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);

CREATE TABLE IF NOT EXISTS email_webhook_events (
    id bigserial PRIMARY KEY,
    svix_id varchar(255) NOT NULL UNIQUE,
    event_type varchar(255),
    payload json,
    processed_at timestamp(0) without time zone,
    error text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);

COMMIT;

-- Migrations ledger row (run once; matches the ad_sliders precedent).
-- Adjust the batch number to (SELECT MAX(batch)+1 FROM migrations) for your DB.
INSERT INTO migrations (migration, batch)
SELECT '2026_09_10_000000_create_email_inbox_tables', (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations)
WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_09_10_000000_create_email_inbox_tables');

-- New permissions (guard 'admin', matching database/seeders/PermissionSeeder.php).
-- Idempotent -- safe to re-run.
INSERT INTO permissions (name, guard_name, category, created_at, updated_at)
SELECT v.name, 'admin', v.category, now(), now()
FROM (VALUES
    ('email-inbox-setting', 'Setting Management'),
    ('email-inbox-view', 'Email Inbox Management'),
    ('email-inbox-send', 'Email Inbox Management'),
    ('email-inbox-manage-addresses', 'Email Inbox Management')
) AS v(name, category)
WHERE NOT EXISTS (
    SELECT 1 FROM permissions WHERE name = v.name AND guard_name = 'admin'
);
