# External Integrations

**Analysis Date:** 2026-05-12

## APIs & External Services

**Cloud storage / backup:**
- Google Drive - Remote backup target for database dumps and stored files
  - SDK/Client: `yaza/laravel-google-drive-storage` via the `google` filesystem disk in `config/filesystems.php`
  - Auth: `GOOGLE_DRIVE_CLIENT_ID`, `GOOGLE_DRIVE_CLIENT_SECRET`, `GOOGLE_DRIVE_REFRESH_TOKEN`, `GOOGLE_DRIVE_FOLDER_ID`
  - Implementation files: `app/Jobs/BackupDatabaseToGoogle.php`, `app/Jobs/BackupFileToGoogle.php`, `app/Console/Commands/BackupDatabaseToGoogle.php`, `app/Console/Commands/BackupFilesToGoogle.php`, `app/Http/Controllers/Backend/SettingController.php`

**Email providers:**
- SMTP / SES / Postmark / Resend - Laravel mail transports configured but not pinned to a single provider in `config/mail.php` and `config/services.php`
  - SDK/Client: Laravel mailer transports from `laravel/framework`
  - Auth: `MAIL_*`, `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `POSTMARK_TOKEN`, `RESEND_KEY`

**Notifications / logging sinks:**
- Slack - Configured as an available notification/log target in `config/services.php` and `config/logging.php`
  - SDK/Client: Laravel Slack notification/log channel support from `laravel/framework`
  - Auth: `SLACK_BOT_USER_OAUTH_TOKEN`, `SLACK_BOT_USER_DEFAULT_CHANNEL`, `LOG_SLACK_WEBHOOK_URL`
- Papertrail - Optional Monolog remote log sink configured in `config/logging.php`
  - SDK/Client: Monolog `SyslogUdpHandler`
  - Auth: `PAPERTRAIL_URL`, `PAPERTRAIL_PORT`

**Frontend assets / CDN-hosted resources:**
- Google Fonts - External font CSS loaded by `resources/views/home.blade.php`, `resources/views/layouts/auth/app.blade.php`, and configured in `config/settings.php`
  - SDK/Client: Direct stylesheet/script tags
  - Auth: None
- Google JSAPI - Registered as a theme asset in `config/settings.php`
  - SDK/Client: Direct script URL mapping
  - Auth: None

## Data Storage

**Databases:**
- Laravel relational database connection, defaulting to SQLite and supporting MySQL, MariaDB, PostgreSQL, and SQL Server in `config/database.php`
  - Connection: `DB_CONNECTION`, `DB_URL`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
  - Client: Laravel Eloquent ORM and query builder in `app/Models/` and controllers under `app/Http/Controllers/Backend/`
- Redis is optionally configured for cache/session/queue support in `config/database.php`
  - Connection: `REDIS_URL`, `REDIS_HOST`, `REDIS_PORT`, `REDIS_USERNAME`, `REDIS_PASSWORD`, `REDIS_DB`, `REDIS_CACHE_DB`
  - Client: Laravel Redis integration from `laravel/framework`

**File Storage:**
- Local filesystem - Default disk in `config/filesystems.php`, storing app files in `storage/app`
- Public local storage - `public` disk exposed via `public/storage`
- Google Drive - Backup destination via `google` disk in `config/filesystems.php`
- Amazon S3 - Available but not referenced by app code; defined as an optional disk in `config/filesystems.php`

**Caching:**
- Database cache is the default store in `config/cache.php`
- Redis, Memcached, DynamoDB, file, and array caches are configured as alternatives in `config/cache.php`

## Authentication & Identity

**Auth Provider:**
- Custom Laravel session auth backed by the local `users` table
  - Implementation: `Auth::routes()` in `routes/web.php`, `App\Models\User` in `app/Models/User.php`, and role-based authorization via `spatie/laravel-permission`
  - Authorization middleware: aliases for `role`, `permission`, and `role_or_permission` in `bootstrap/app.php`

## Monitoring & Observability

**Error Tracking:**
- None detected as a dedicated SaaS error tracker

**Logs:**
- Laravel Monolog stack with `single` file logging enabled by default in `config/logging.php`
- Optional Slack and Papertrail channels are preconfigured in `config/logging.php`
- Scheduled heartbeat logging is implemented in `routes/console.php`

## CI/CD & Deployment

**Hosting:**
- Not detected; the repository contains a standard Laravel public entrypoint in `public/index.php` and local-development dependency on `laravel/sail` in `composer.json`

**CI Pipeline:**
- None detected; no workflow files were found under `.github/`

## Environment Configuration

**Required env vars:**
- Core app: `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL`
- Database: `DB_CONNECTION`, `DB_DATABASE` plus host/user/password variables for non-SQLite deployments from `config/database.php`
- Session/cache/queue: `SESSION_DRIVER`, `CACHE_STORE`, `QUEUE_CONNECTION`
- Mail: `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`
- Google Drive backup: `GOOGLE_DRIVE_CLIENT_ID`, `GOOGLE_DRIVE_CLIENT_SECRET`, `GOOGLE_DRIVE_REFRESH_TOKEN`, `GOOGLE_DRIVE_FOLDER_ID`
- Optional AWS-backed services: `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`

**Secrets location:**
- `.env` in the project root holds runtime secrets
- `.env.example` in the project root provides the non-secret template

## Webhooks & Callbacks

**Incoming:**
- None detected

**Outgoing:**
- Google Drive upload calls through `Storage::disk('google')->put(...)` in `app/Jobs/BackupDatabaseToGoogle.php`, `app/Jobs/BackupFileToGoogle.php`, `app/Console/Commands/BackupDatabaseToGoogle.php`, and `app/Console/Commands/BackupFilesToGoogle.php`
- Email delivery through Laravel mail transports configured in `config/mail.php`
- Optional Slack and Papertrail log delivery through `config/logging.php`

---

*Integration audit: 2026-05-12*
