# Gmail SMTP verification delivery

This configuration is for the project owner's requested real-email test to their own student inbox. Keep a local SMTP catcher for routine development; the university-approved production provider is still pending confirmation. No Composer package or database migration is needed.

## 1. Prepare the sender

Use a Gmail account you control as the sender. The student's `@udd.edu.ph` address is the recipient and can be a different account.

1. Sign in to the sender account at https://myaccount.google.com/security.
2. Enable 2-Step Verification.
3. Open https://myaccount.google.com/apppasswords and create an app password named `KaAklat`.
4. Copy the generated app password. Remove display spaces when entering it in `.env`. Do not use the normal account password or share either password in chat.

Google requires 2-Step Verification for app passwords. App-password availability can depend on account security settings and organizational restrictions. See https://support.google.com/accounts/answer/185833.

## 2. Fill in local credentials

The project's regular, ignored `.env` has been prepared with:

```dotenv
APP_URL=http://localhost:8000
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=""
MAIL_PASSWORD=""
MAIL_FROM_ADDRESS="${MAIL_USERNAME}"
MAIL_FROM_NAME="KaAklat"
MAIL_URL=null
```

Set `MAIL_USERNAME` to the sender's full Gmail address and `MAIL_PASSWORD` to its generated app password. `MAIL_FROM_ADDRESS` automatically uses that same address. Credentials remain local and must not be committed. `.env.testing` continues to use the `array` mailer; automated tests do not send real emails.

The existing `config/mail.php` supports these environment variables. On port 587, the `smtp` scheme uses STARTTLS when offered by Gmail. Do not disable certificate verification to work around certificate errors.

## 3. Restart the application

From PowerShell in the project directory:

```powershell
php artisan config:clear
php artisan serve --host=localhost --port=8000
```

Stop an already-running Artisan server with Ctrl+C before restarting. Keep the server running during the check. The current default verification notification is sent during the request; it does not implement `ShouldQueue`, so this verification flow does not need a queue worker. Keep the database queue configuration for other queued work.

## 4. Verify delivery and activation

1. Open http://localhost:8000/register and register with your own real `@udd.edu.ph` address, a unique student ID, and a password of at least 12 characters.
2. For an existing unverified account, open http://localhost:8000/email/verify and request another verification link instead. Already-verified accounts do not receive a new verification message.
3. Check the student's actual Gmail inbox and Spam folder. The sender should be the Gmail account configured above.
4. Open the verification button on the same computer running Laravel. A localhost link opened on a phone points to the phone, not this computer.
5. Confirm the link redirects to login, then sign in and confirm dashboard access.

A successful SMTP handoff is separate from inbox delivery. Confirm receipt and the activation link before reporting real delivery as verified.

## Troubleshooting

| Symptom | Check |
| --- | --- |
| Gmail rejects authentication | Full sender address, correct app password, 2-Step Verification, and any account restrictions. |
| Connection times out | Network/firewall access to `smtp.gmail.com:587`. |
| TLS certificate validation fails | PHP OpenSSL and trusted CA configuration; keep TLS verification enabled. |
| No email but resend reports success | Account must exist and remain unverified; inspect Inbox and Spam. The resend page intentionally gives a generic response. |
| Verification returns 403 | Request a fresh link and use its exact host, port, and query string. |
| Registration failed while sending email | The account may already have been created. Use resend rather than registering the same identifiers again. |

Inspect `storage/logs/laravel.log` locally for errors. Do not share credentials, entire environment files, or signed verification URLs. Return to the local catcher configuration after the real-delivery check for routine development.
