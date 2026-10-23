# Change Log

Add one entry every time an agent changes anything in the project. Put the newest entry first. Use this format:

## YYYY-MM-DD — Agent name — Module
- **Summary:** What changed.
- **Files:** Files created or modified.
- **Database migrations:** Migration files added, or `None`.
- **Testing:** Checks run and their result.
- **Known issues or follow-ups:** Remaining issues, or `None`.

## Entries

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



