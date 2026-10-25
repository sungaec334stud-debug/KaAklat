# Authentication tests on Windows

Run from the KaAklat project directory using PHP 8.3 or later. The verified CLI for this investigation was PHP 8.4.14, with Laravel 13.38.0 and PHPUnit 12.5.40.

Verified on 2026-10-26 against `kaaklat_mysql_test` on Oracle MySQL Community Server 26.10.0: the full suite passed with 26 tests and 153 assertions after the project audit. Execution was outside the Windows sandbox. Tests use notification fakes or a simulated failing mail transport; no real emails are sent by this suite. See `docs/PROJECT_VERIFICATION.md` for the audit results and remaining findings.

## Simulate multiple students

1. For routine local simulation, use the local SMTP catcher configuration. Give every simulated student a different `@udd.edu.ph` email and student ID. Synthetic addresses are suitable for the catcher; do not send live Gmail messages to invented addresses.
2. Register each account once. The account is saved inactive and unverified before email delivery, and only the signed verification link activates it.
3. If the email or student ID is already registered, do not repeat registration or reuse another student's ID. Open `/email/verify` and resend to an existing unverified account, or sign in if already verified. The registration page links directly to resend.
4. Open the captured email and follow its link, then sign in. Sign out before testing the next student's registration, or use separate browser profiles.

Registration allows five submissions per minute from one IP. A 429 response means wait before trying again; the limit is not disabled for simulation. Multiple students sharing a network also share that IP-based limit.

An SMTP transport failure now redirects a newly created student to the verification page with an explicit account-created/delivery-failed message. Resend failures show a retry message; the existing account stays inactive. Failures log the user ID and exception class without SMTP credentials or signed links. Do not assume these failures caused a specific registration attempt without checking the evidence: duplicate validation itself simply means those identifiers already exist.

For a live delivery test, use the owner's own inbox or a classmate participating in the test. The receiving device must be able to reach the address in the email; a `localhost` link only works on the computer hosting KaAklat. See `docs/GMAIL_SMTP_SETUP.md` for the sender setup.

## Local credentials

Use an ignored `.env.testing` file copied from `.env`, with these overrides:

```dotenv
APP_ENV=testing
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=kaaklat_mysql_test
DB_USERNAME=kaaklat_test_user
DB_PASSWORD="your test account password"
DB_URL=
MAIL_MAILER=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
CACHE_STORE=array
```

Keep the copied application key. Enter the actual test account password only in the local file; never commit it. An existing `DB_PASSWORD` process environment variable takes precedence over the file. PHPUnit does not set an empty password, allowing Laravel to load the local testing credential.

Laravel's testing environment file behavior is documented at https://laravel.com/docs/13.x/testing#environment.

## Run

```powershell
php artisan config:clear
php artisan test --filter=AuthenticationTest
php artisan test
```

These feature tests use `RefreshDatabase`, which recreates tables. Verify the target is `kaaklat_mysql_test`, never the application database `kaaklat`. Before refreshing, the database suites validate the resolved connection, including URL overrides, and require the testing environment, MySQL, and the exact `kaaklat_mysql_test` database. Clear cached configuration before running tests even with this guard.

## Diagnose startup and connection errors

If PHPUnit says it cannot open `vendor/autoload.php`, compare:

```powershell
php -r 'var_export(is_readable("vendor/autoload.php"));'
php vendor/phpunit/phpunit/phpunit --list-tests
```

In the Codex Windows sandbox during this investigation, `file_exists` and `file_get_contents` succeeded while `is_readable` returned false. PHPUnit's bootstrap loader checks `is_readable` and exits before discovering tests. Running the same PHP executable outside the sandbox returned true and listed all 16 tests. Run in a normal PowerShell session, or use an approved execution outside the sandbox in Codex. Editing Laravel or vendor code does not address this environment failure.

MySQL error 1045 means the test account could not authenticate. Check the local test password and account access to the server on port 3307. Do not substitute the application database to work around it.
