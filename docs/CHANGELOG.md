# Change Log

Add one entry every time an agent changes anything in the project. Put the newest entry first. Use this format:

## YYYY-MM-DD — Agent name — Module
- **Summary:** What changed.
- **Files:** Files created or modified.
- **Database migrations:** Migration files added, or `None`.
- **Testing:** Checks run and their result.
- **Known issues or follow-ups:** Remaining issues, or `None`.

## Entries

## 2026-10-25 — Codex — Oracle MySQL Application Database
- **Summary:** Switched the local application `.env` target to the user-created Oracle MySQL `kaaklat` schema at `127.0.0.1:3307` using the local `kaaklat_app` account. Applied the complete Laravel schema after Laravel reported that the new schema had no migration table. Credentials remain only in the ignored `.env` file.
- **Files:** `.env` (local ignored configuration, updated by the user); `docs/CHANGELOG.md`.
- **Database migrations:** Applied all 16 migrations to Oracle MySQL Community Server 26.10.0, schema `kaaklat`.
- **Testing:** `php artisan migrate` completed successfully. `php artisan migrate:status` reports all 16 migrations as Ran. A read-only `SELECT VERSION(), @@version_comment` confirmed `26.10.0 | MySQL Community Server - GPL`. The full schema test suite had previously passed on the separate `kaaklat_mysql_test` schema (9 tests, 20 assertions); it was not rerun as part of this application-schema migration.
- **Known issues or follow-ups:** None for database setup. Keep `kaaklat_mysql_test` separate for PHPUnit; the suite refreshes its configured test database.

## 2026-10-25 — Codex — MySQL Schema Compatibility
- **Summary:** Changed the notifications-to-loans foreign key from `ON DELETE SET NULL` to `RESTRICT`. `notifications.loan_id` is the base column for a stored generated column used by automated-notice deduplication, and Oracle MySQL does not permit `SET NULL` on a foreign key over the base of a stored generated column. Restricting deletion also preserves notification history. Added a feature test for that deletion rule.
- **Files:** `database/migrations/2026_10_24_000008_create_notifications_table.php`; `tests/Feature/DatabaseSchemaTest.php`; `docs/CHANGELOG.md`.
- **Database migrations:** Edited an unapplied create migration. No database was migrated by this change.
- **Testing:** PHP syntax checks passed for the edited migration and feature test; `git diff --check` passed. User ran the full suite on Oracle MySQL Community Server 26.10.0: 9 tests passed, 20 assertions.
- **Known issues or follow-ups:** Deleting a loan that has notifications is intentionally rejected to preserve history. Revalidate against the exact Oracle MySQL version selected for production.

## 2026-10-25 — Codex — MySQL Schema Verification Setup
- **Summary:** Pointed PHPUnit at the user-created Oracle MySQL test schema on port 3307 and configured a dedicated least-privilege test account name. The test password is intentionally supplied through the invoking environment rather than stored in the repository.
- **Files:** `phpunit.xml`; `docs/CHANGELOG.md`.
- **Database migrations:** None. The test schema is not modified by this configuration change.
- **Testing:** XML configuration was reviewed; the full suite was subsequently run by the user against Oracle MySQL Community Server 26.10.0 on `kaaklat_mysql_test`: 9 tests passed, 20 assertions.
- **Known issues or follow-ups:** Test password remains supplied only through the user's PowerShell environment. The application `.env` was subsequently switched to Oracle MySQL `kaaklat` on port 3307 as recorded in the newer Oracle MySQL Application Database entry.

## 2026-10-24 — Codex — Database Configuration
- **Summary:** Switched the local application and example configuration from SQLite to Laravel's `mysql` connection, targeting `127.0.0.1:3306/kaaklat`. The prior SQLite file was empty, so no records needed to be copied. Created the empty `kaaklat` database on the installed MariaDB server and applied the complete schema.
- **Files:** `.env` (local ignored configuration); `.env.example`; `config/database.php`; `docs/CHANGELOG.md`.
- **Database migrations:** Applied all 16 existing migrations to `kaaklat` on MariaDB 10.4.32. No migration files were added during this configuration change.
- **Testing:** `php artisan migrate` completed successfully; `php artisan migrate:status` reports all 16 migrations as Ran. `php artisan test` passed: 8 tests, 19 assertions against the dedicated `kaaklat_test` database on MariaDB 10.4.32.
- **Known issues or follow-ups:** The `kaaklat` schema on MariaDB 10.4.32 is retained as a separate local database. The application `.env` was subsequently switched to Oracle MySQL; see the newer Oracle MySQL Application Database entry.

## 2026-10-24 — Codex — Database Schema / Catalog / Notifications
- **Summary:** Defined available inventory as the count of non-deleted copies whose status is `Available`; synchronized `books.total_copies` from BookCopy create, soft-delete, restore, and reassignment events. Enforced one loan per borrow request. Prevented duplicate automated loan notices per type/application-local day while allowing staff notices to be resent after a warning. Corrected overdue classification so loans due today are not marked overdue. Added schema tests and a guard requiring a dedicated database ending in `_test`.
- **Files:** `app/Models/Book.php`; `app/Models/BookCopy.php`; `app/Models/Loan.php`; `app/Models/Notification.php`; `database/migrations/2026_10_24_000003_create_books_table.php`; `database/migrations/2026_10_24_000006_create_loans_table.php`; `database/migrations/2026_10_24_000008_create_notifications_table.php`; `phpunit.xml`; `tests/Feature/DatabaseSchemaTest.php`; `docs/CHANGELOG.md`.
- **Database migrations:** Edited the unapplied create migrations for books, loans, and notifications. No new migration files. Test migrations ran only against the dedicated `kaaklat_test` database; the project `.env` database was not migrated.
- **Testing:** `php -l` passed for changed PHP files. `php artisan test` passed: 8 tests, 19 assertions against MariaDB 10.4.32 using Laravel's `mysql` driver and the dedicated `kaaklat_test` database. These results are MariaDB compatibility checks, not Oracle MySQL verification.
- **Known issues or follow-ups:** (1) Oracle MySQL schema compatibility was subsequently verified on MySQL Community Server 26.10.0; validate the exact production version before deployment. (2) The project `.env` targets MariaDB as documented in the later Database Configuration entry; the Oracle test suite uses a separate schema. (3) Bulk copy writes or mass deletes that bypass Eloquent model events must recalculate `total_copies`.

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



