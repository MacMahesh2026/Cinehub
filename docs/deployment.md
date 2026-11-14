# Production deployment

Production runs Symfony in `prod` mode on the EC2 instance (`/var/www/cinehub`,
user `ec2-user`). Code is deployed by `.github/workflows/deploy.yml`; secrets and
machine-specific settings live on the instance in `.env.local` (git-ignored).

## How booking notifications are queued

A booking dispatches two independent messages on the Doctrine async transport:

| Message | Handler | Purpose |
| --- | --- | --- |
| `App\Message\BookingConfirmedMessage` | `BookingConfirmedHandler` | Sends the confirmation email. |
| `App\Message\BookingWhatsAppMessage` | `BookingWhatsAppHandler` | Sends the WhatsApp template (no-op while unconfigured). |

They are separate so a failing/slow WhatsApp call can never retry — and
therefore duplicate — an email, and vice versa.

**A Messenger worker must be running, otherwise no email is ever sent.** Dispatch
only inserts a row into `messenger_messages`; nothing sends until a worker
consumes it.

## 1. Environment (`.env.local` on the server)

`.env` is **not tracked in git**, so new defaults in `.env.example` do not reach
the server automatically. Keep the required values in `/var/www/cinehub/.env.local`
(untracked, preserved by `git clean -fd`):

```dotenv
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=<32+ random chars>
DEFAULT_URI=https://your-production-domain.example

DATABASE_URL="mysql://USER:PASSWORD@127.0.0.1:3306/cinehub?serverVersion=8.4.0&charset=utf8mb4"
JWT_PASSPHRASE=<passphrase for config/jwt/private.pem>

# Messenger: Doctrine transport + failed-message storage (table is migrated).
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0

# Email (Brevo). Both forms work because symfony/brevo-mailer is installed.
MAILER_DSN=brevo+api://BREVO_API_KEY@default
# MAILER_DSN=brevo+smtp://BREVO_LOGIN:SMTP_KEY@smtp-relay.brevo.com:587
# Must be a verified sender/domain in Brevo:
MAILER_FROM=bookings@your-domain.example

# Posters (already required by prod Flysystem config).
AWS_REGION=ap-south-1
AWS_S3_BUCKET=your-bucket
MOVIE_POSTER_URI_PREFIX=https://your-cloudfront-domain.example

# Optional: leave empty until WhatsApp is configured. Email is unaffected.
WHATSAPP_GRAPH_VERSION=v21.0
WHATSAPP_PHONE_NUMBER_ID=
WHATSAPP_TOKEN=
WHATSAPP_TEMPLATE=booking_confirmation
WHATSAPP_TEMPLATE_LANG=en
```

`MAILER_DSN` and `MAILER_FROM` are the only email-specific values. A missing
`MAILER_FROM` makes the handler fail (by design, so it is noticed), while missing
`WHATSAPP_*` values are safely treated as "WhatsApp disabled".

## 2. Brevo setup (email)

1. Create a Brevo account and add the sending domain under **Senders & IP →
   Domains**.
2. Add the DNS records Brevo shows (SPF, DKIM, DMARC). Wait for "Authenticated".
3. Create an API key (**SMTP & API → API keys**) for `brevo+api://`, or use the
   SMTP login + SMTP key for `brevo+smtp://`.
4. Verify a sender address, and use that address (or any address on the verified
   domain) for `MAILER_FROM`.
5. On the server, send a test:

   ```bash
   cd /var/www/cinehub
   APP_ENV=prod php bin/console mailer:test you@example.com
   ```

   `mailer:test` uses `MAILER_DSN` directly, so it confirms provider credentials
   and sender verification separately from the queue.

## 3. Messenger worker (systemd)

Create `/etc/systemd/system/cinehub-worker.service`:

```ini
[Unit]
Description=CineHub Messenger worker
After=network-online.target
Wants=network-online.target

[Service]
User=ec2-user
Group=ec2-user
WorkingDirectory=/var/www/cinehub
Environment=APP_ENV=prod
Environment=APP_DEBUG=0
ExecStart=/usr/bin/php /var/www/cinehub/bin/console messenger:consume async --time-limit=3600 --memory-limit=256M
Restart=always
RestartSec=5
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now cinehub-worker
sudo systemctl status cinehub-worker
sudo journalctl -u cinehub-worker -f
```

The deploy workflow runs `messenger:stop-workers` after migrations. With
`Restart=always`, systemd starts a fresh worker on the new code automatically.
If you run the worker manually instead, you must restart it after every deploy.

## 4. Migrations

The async transport needs the `messenger_messages` table. It is created by
`migrations/Version20261113181500.php`, which the deploy workflow applies with:

```bash
APP_ENV=prod php bin/console doctrine:migrations:migrate --no-interaction
```

## 5. Verifying a real booking

1. Confirm the worker is running: `sudo systemctl status cinehub-worker`.
2. Make a booking as a customer with an email address.
3. Check the worker log for the `BookingConfirmedMessage` and `BookingWhatsAppMessage`.
4. Check Brevo's transactional log for the message and the customer's inbox/spam.

If nothing is sent:

- `messenger_messages` has rows but the worker is idle → worker not running or
  consuming the wrong environment.
- The message sits in `messenger_messages` with `delivered_at` set and keeps
  reappearing → handler is throwing; inspect
  `APP_ENV=prod php bin/console messenger:failed:show` and `var/log/prod.log`.
- `mailer:test` fails → provider credentials, DNS/SPF/DKIM, or sender
  verification, not application code.
- Emails are in Brevo but not the inbox → spam placement; check domain
  authentication and content.

## 6. WhatsApp (later)

WhatsApp is wired but disabled until `WHATSAPP_PHONE_NUMBER_ID` and
`WHATSAPP_TOKEN` are set and the `booking_confirmation` utility template is
approved in Meta's WhatsApp Manager. Leave the variables empty for now.
