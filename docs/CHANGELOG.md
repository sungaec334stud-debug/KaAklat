# Change Log

Add one entry every time an agent changes anything in the project. Put the newest entry first. Use this format:

## YYYY-MM-DD — Agent name — Module
- **Summary:** What changed.
- **Files:** Files created or modified.
- **Database migrations:** Migration files added, or `None`.
- **Testing:** Checks run and their result.
- **Known issues or follow-ups:** Remaining issues, or `None`.

## Entries

## 2026-10-24 — Cursor Grok 4.6 — Core Circulation
- **Summary:** Added MySQL stored generated columns and unique indexes on the unapplied `holds` and `loans` create migrations: one Waiting/Ready hold per user and book; one unique `queue_position` per book among Waiting holds; one Active loan per book copy. Inactive historical rows generate NULL and are not blocked. No unique constraint was added for pending borrow requests. Reserved-copy foreign keys on `borrow_requests.book_copy_id` and `holds.book_copy_id` remain `restrictOnDelete`.
- **Files:** `database/migrations/2026_10_24_000006_create_loans_table.php`; `database/migrations/2026_10_24_000007_create_holds_table.php`; `docs/CHANGELOG.md`.
- **Database migrations:** Edited the unapplied create migrations listed above. Migrations were not run.
- **Testing:** `php -l` on the edited migration files. PHPUnit was not run: `phpunit.xml` still uses SQLite `:memory:`, which cannot create the books `FULLTEXT` index or these MySQL generated-column uniques. Schema tests need a dedicated MySQL test database (see Known issues).
- **Known issues or follow-ups:** (1) Configure PHPUnit to use a dedicated MySQL test database before running schema tests. (2) Migrations still not applied. (3) Application-layer checks should still run inside transactions in addition to these constraints.

## 2026-10-24 — Cursor Grok 4.6 — Cataloging
- **Summary:** Reconciled `AGENTS.md` with the approved catalog decision: author and category are free-text fields on `books`, not lookup tables.
- **Files:** `AGENTS.md`; `docs/CHANGELOG.md`.
- **Database migrations:** None.
- **Testing:** Documentation-only change; no application tests run.
- **Known issues or follow-ups:** Author/category lookup-table normalisation remains out of the current approved schema.

## 2026-10-24 — Cursor Grok 4.6 — Core Circulation / System Administration
- **Summary:** Pre-migration schema repairs: renamed the unapplied email delivery table to `email_delivery_logs` so it matches Eloquent’s default for `EmailDeliveryLog`; changed reserved-copy foreign keys on `borrow_requests.book_copy_id` and `holds.book_copy_id` from `nullOnDelete` to `restrictOnDelete`; renamed `Setting::get` to `Setting::getValue` (no existing callers); documented that custom in-app notices must use `User::appNotifications()`, not Laravel’s `$user->notifications` relation. Unique constraints for active holds, active loans per copy, and FIFO queue positions were not applied — a design that preserves completed/cancelled history is proposed in chat and awaits approval. No unique constraint was added for pending borrow requests.
- **Files:** `database/migrations/2026_10_24_000009_create_email_delivery_logs_table.php` (renamed from `...email_delivery_log_table.php`); `database/migrations/2026_10_24_000005_create_borrow_requests_table.php`; `database/migrations/2026_10_24_000007_create_holds_table.php`; `database/migrations/2026_10_24_000011_create_notification_templates_table.php`; `app/Models/EmailDeliveryLog.php`; `app/Models/Setting.php`; `app/Models/User.php`; `app/Models/Notification.php`; `docs/CHANGELOG.md`.
- **Database migrations:** Edited unapplied create migrations listed above. No new migration files for already-applied schema. Migrations were not run.
- **Testing:** `php -l` on the edited PHP files. PHPUnit was not run: `phpunit.xml` still points at SQLite `:memory:`, and the books migration uses a MySQL `FULLTEXT` index that SQLite cannot create. Schema tests need a dedicated MySQL test database (see Known issues).
- **Known issues or follow-ups:** (1) Author/category conflict: `AGENTS.md` requires normalized author and category lookup tables; the approved schema and `docs/CHANGELOG.md` store them as free-text columns on `books`. Left unchanged. (2) Unique constraints for one active hold per patron per title, one active loan per copy, and unique active FIFO `queue_position` per title await owner approval of the generated-column design. (3) Configure PHPUnit to use a dedicated MySQL test database instead of SQLite before running schema tests. (4) Migrations still not applied.

## 2026-10-24 — Antigravity — Cataloging / Circulation / System Administration
- **Summary:** Designed and implemented the initial MySQL schema. Extended `users` with role, student_id, is_active, and soft-delete columns. Created 13 migration files and 12 new Eloquent models covering all KaAklat modules. Seeded default settings (8 keys) and notification templates (10 types). Rewrote `User.php` to add SoftDeletes, fillable/hidden arrays, casts, isSuspended() helper, and all relationships.
- **Files:** `database/migrations/2026_10_24_000001` through `000013` (13 files); `app/Models/User.php` (modified); `app/Models/Suspension.php`; `app/Models/Book.php`; `app/Models/BookCopy.php`; `app/Models/BorrowRequest.php`; `app/Models/Loan.php`; `app/Models/Hold.php`; `app/Models/Notification.php`; `app/Models/EmailDeliveryLog.php`; `app/Models/Setting.php`; `app/Models/NotificationTemplate.php`; `app/Models/AuditLog.php`; `app/Models/Backup.php`; `docs/CHANGELOG.md`.
- **Database migrations:** All 13 files listed above.
- **Testing:** `php -l` passed on all 13 migration files and all 13 model files (12 new + 1 modified). Migrations not yet run — MySQL not yet configured in `.env`.
- **Known issues or follow-ups:** (1) Configure MySQL in `.env` then run `php artisan migrate`. (2) Student-ID format unresolved — no format-specific validation added yet. (3) Expiry-job behavior (offer copy to next hold vs. return to Available) not yet implemented. (4) Admin bootstrap Artisan command not yet built. (5) Author/category stored as free text per approved schema — can be normalised later.

## 2026-10-23 — Codex — Project Architecture
- **Summary:** Set the target PHP/Laravel/database versions and documented local queue, scheduler, email, student-ID, administrator-bootstrap, catalog normalization, and expired-request/hold behavior decisions.
- **Files:** `AGENTS.md`; `docs/CHANGELOG.md`.
- **Database migrations:** None.
- **Testing:** Documentation-only change; no application tests run.
- **Known issues or follow-ups:** Verify XAMPP's Apache PHP and database versions; confirm the university's student-ID format and production SMTP provider.


## 2026-10-23 — Codex — Student Portal
- **Summary:** Required student registration to use a university email ending in `@udd.edu.ph` and require email verification before account activation.
- **Files:** `AGENTS.md`; `docs/CHANGELOG.md`.
- **Database migrations:** None.
- **Testing:** Documentation-only change; no application tests run.
- **Known issues or follow-ups:** Define the student-ID format.



