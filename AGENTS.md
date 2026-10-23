# KaAklat Agent Guide

## Project overview and purpose
KaAklat is a responsive web-based Library Management System for Universidad de Dagupan. It supports patrons, library staff, and system administrators in managing the catalog, circulation, FIFO holds, notifications, and library operations.

## Tech stack and environment assumptions
- Target PHP 8.4 and Laravel 13. The PHP CLI and web-server runtimes must both be PHP 8.3 or later.
- Target Oracle MySQL 8.0 or later, managed locally through phpMyAdmin. phpMyAdmin is a database management interface, not a database server. XAMPP commonly bundles MariaDB instead of Oracle MySQL; verify the installed database product and version. If using bundled MariaDB for local development, confirm compatibility and do not describe it as a MySQL test environment.
- Serve Laravel through its `public/` directory. Use Laravel's Eloquent ORM, migrations, validation, mail, queues, and scheduler.
- Use Laravel's database queue driver locally. Run `php artisan queue:work` while queued jobs are being processed. On Windows, configure Task Scheduler to run `php artisan schedule:run` every minute from the Laravel project directory using a compatible PHP executable.
- Use a local SMTP catcher during development so test messages are not sent to students. Use a university-approved SMTP provider in production. Configure database, SMTP, queue, and scheduler settings through `.env`; never commit secrets.
- Verify the installed XAMPP Apache PHP version, database product/version, student-ID format, and production SMTP provider before relying on them. See Pending Verification.

## Roles and permissions
- **Student/Patron:** register and manage permitted profile details; search the catalog; submit or cancel eligible requests/holds; view their own loans, queue positions, and notifications.
- **Librarian/Staff:** manage catalog records and copies; process requests and check-outs/returns; manage holds; monitor overdue loans and send notifications; view and export reports; suspend or reinstate borrowing privileges with a reason.
- **System Administrator:** manage users and roles, business-rule settings and message templates, read audit logs, and trigger and review local database backups.
- Enforce least privilege on the server for every route and action. Patrons must never access another patron's records. Librarians handle operational library work; administrator functions are reserved to administrators unless a task explicitly defines otherwise.

## Modules
Table names below are conceptual names to guide discussion, not a finalized schema. Follow existing migrations and models when they exist; ask before changing the data model.

### Student Portal
- **Purpose:** Account access, profile, catalog discovery, borrow/hold requests, and personal activity.
- **Entities/tables:** users, books, book_copies, borrow_requests, holds, loans, notifications.
- **Key rules:** Student ID and university email are unique. Student registration must use an email address ending in `@udd.edu.ph`, and the address must be verified before the account is activated. Store student IDs as strings and enforce uniqueness. Do not invent a format-specific validation rule until the university confirms the official student-ID format. Students cannot edit their student ID. Students see only their own records. Block new borrow/hold requests for suspended patrons, patrons with overdue loans, or patrons at the active-loan limit, and show the reason. Prevent duplicate holds and holds on a title the patron currently has on loan.

### Librarian Management
- **Purpose:** Staff workflows for request processing, circulation, overdue monitoring, notification sending, and reports.
- **Entities/tables:** users, borrow_requests, loans, book_copies, holds, notifications, suspensions, report data derived from transactions.
- **Key rules:** Requests are presented in received order. Rejections require a reason. Enforce the configured active-loan limit at approval and check-out. Check-in records copy condition and can trigger FIFO queue processing. Suspension and reinstatement require a reason. Do not apply monetary fines.

### Core Circulation and FIFO Queue Management
- **Purpose:** Track borrow requests, check-outs, returns, reservations, and title hold queues.
- **Entities/tables:** borrow_requests, holds, loans, book_copies, users.
- **Key rules:** Holds are FIFO per title. Maintain queue position; on return, offer/reserve the copy for the first eligible hold and notify the patron. A hold pickup window defaults to 24 hours; when it expires, offer the copy to the next hold or make it available. Pending borrow requests expire after the configured window (default 48 hours); approved requests reserve a copy for the pickup window. If an approved request is not picked up, expire it and offer its copy to the oldest eligible hold; if no eligible hold exists, return the copy to Available inventory. New borrow requests must not bypass an existing hold queue. Keep status transitions and copy availability consistent.

### Cataloging and Inventory
- **Purpose:** Maintain searchable book records and individually tracked physical copies, including CSV import.
- **Entities/tables:** books, authors, categories, book_copies, audit_logs.
- **Key rules:** Book records include title, author, category/genre, publication year, call number, ISBN, and copy totals. Normalize authors and categories into lookup tables; each book references one author and one category unless requirements are explicitly changed. Each physical copy has a unique accession number and status (Available, Reserved, Borrowed, Damaged, or Lost). Available copies cannot be negative or exceed total copies and must reflect copy status changes. Validate CSV rows and report row-level errors. Prevent deleting records with active loans or holds; retain historical references through soft deletion.

### Due-Date and Overdue Notifications
- **Purpose:** Monitor loan status and deliver in-app and email reminders, including staff-initiated messages.
- **Entities/tables:** loans, notifications, notification_templates, settings.
- **Key rules:** Use the configured loan duration (default 7 days); classify On Time, Due Soon, Due Today, Overdue, and Severely Overdue. Defaults are Due Soon within 2 days, severely overdue after 7 days, and repeat severe-overdue reminders every 3 days. Automated daily reminders default to 8:00 AM and must not duplicate for the same loan/type/day; stop when returned or marked lost. Send both email and in-app notices. Keep delivery history; warn before resending the same type for the same loan within 24 hours.

### System Administration
- **Purpose:** Manage accounts and business rules, inspect audit history, and create local SQL-dump backups.
- **Entities/tables:** users, settings, notification_templates, audit_logs, backups.
- **Key rules:** Deactivate accounts with history rather than delete them. Prevent self-deactivation and demotion/deactivation of the last administrator. Record configuration changes with old and new values. Audit logs are read-only to administrators. Record backup history. Bootstrap the first administrator through a one-time interactive Artisan command that uses no hard-coded credentials and refuses to run once an administrator exists. Keep business rules configurable; defaults include 7-day loans, maximum 3 active loans, 48-hour request expiry, and 24-hour hold pickup.

## Out of scope
Agents must refuse requests to add these features unless the project owner explicitly changes scope:
- E-books
- Inter-library sharing
- Monetary fines or online payments; overdue handling uses notifications and borrowing suspension
- Barcode, QR, or RFID support
- SMS or push notifications
- Native mobile applications
- Offline access

## Coding conventions
- Follow Laravel conventions: controllers, models, policies, middleware, form requests, mail/notification classes, jobs, and services in their standard locations. Keep controllers focused and business rules testable.
- Use singular StudlyCase model names and plural snake_case table names; use Laravel migrations for every schema change. Never edit a database schema manually as a substitute for a migration.
- Validate all input server-side, preferably with Form Request classes for substantial rules. Return clear, consistent validation errors.
- Enforce authorization with policies and/or middleware on every protected route and operation; hiding UI controls is not authorization.
- Use Eloquent/query builder parameter binding. Do not interpolate user input into SQL or use raw SQL without bindings.
- Use transactions for multi-record circulation and inventory changes. Protect FIFO ordering and copy counts against concurrent updates.
- Store secrets in environment configuration. Avoid logging passwords, tokens, or other sensitive credentials.

## Workflow rules for agents
1. Work on **one module or task at a time**, and change only what the task asks for.
2. Read `AGENTS.md` and `docs/CHANGELOG.md` before starting.
3. Do not change files outside the task's scope.
4. Ask before making architectural decisions or adding dependencies.
5. Write or update tests for what you build, following the existing test conventions.
6. After finishing, add an entry to `docs/CHANGELOG.md` using the format defined there.

## Definition of done
A task is done when the requested scope is implemented; relevant tests are added or updated and pass; authorization, validation, data integrity, and failure cases are handled; migrations and configuration are included when needed; no out-of-scope files or features were added; and `docs/CHANGELOG.md` contains a complete entry including test results and known follow-ups.

## Pending Verification
- Confirm the PHP version used by XAMPP Apache and the exact local database product/version. The target remains PHP 8.4, Laravel 13, and Oracle MySQL 8.0 or later.
- Confirm the university-issued student-ID format before implementing format-specific validation.
- Confirm the university-approved production SMTP provider.



