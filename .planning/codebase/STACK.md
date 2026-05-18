# Technology Stack

**Analysis Date:** 2026-05-12

## Languages

**Primary:**
- PHP 8.3.30 - Main application runtime for the Laravel app in `app/`, `bootstrap/`, `config/`, and `routes/`

**Secondary:**
- JavaScript ES modules - Frontend entrypoints and browser HTTP helpers in `resources/js/app.js`, `resources/js/bootstrap.js`, and `vite.config.js`
- Blade templating - Server-rendered UI in `resources/views/`
- SCSS - Frontend styling entrypoint in `resources/sass/app.scss`

## Runtime

**Environment:**
- PHP 8.3.30 via CLI runtime reported by `php artisan about --only=environment`
- Node.js v22.19.0 for frontend asset builds

**Package Manager:**
- Composer 2.9.5 for PHP dependencies (`composer.json`, `composer.lock`)
- npm 10.9.3 for frontend dependencies (`package.json`, `package-lock.json`)
- Lockfile: present for both Composer and npm in `composer.lock` and `package-lock.json`

## Frameworks

**Core:**
- Laravel 11.8.0 - Main web framework configured in `bootstrap/app.php` and declared in `composer.json`
- Laravel UI 4.5.2 - Legacy auth scaffolding implied by `Auth::routes()` in `routes/web.php`
- Livewire component pattern - Used by `app/Livewire/WorkflowDesigner.php`
- ArtisanFlow WireFlow v0.1.2-alpha - Workflow designer integration registered in `bootstrap/providers.php` and configured in `config/wireflow.php`

**Testing:**
- Pest 2.34.7 - Test runner bootstrap in `tests/Pest.php`
- PHPUnit 11 schema config - Test suite configuration in `phpunit.xml`

**Build/Dev:**
- Vite 5.4.10 - Frontend bundler in `vite.config.js`
- `laravel-vite-plugin` 1.0.4 - Laravel asset integration in `vite.config.js`
- Laravel Pint 1.16.0 - PHP formatter in `composer.json`
- Laravel Sail 1.x constraint - Local container workflow dependency declared in `composer.json`
- Laravel Debugbar 3.13.5 - Local debugging package declared in `composer.json`

## Key Dependencies

**Critical:**
- `laravel/framework` 11.8.0 - Application foundation for routing, queue, auth, Eloquent, sessions, cache, and filesystem
- `spatie/laravel-permission` 6.7.0 - Role and permission system wired into middleware aliases in `bootstrap/app.php` and user model traits in `app/Models/User.php`
- `yajra/laravel-datatables` 11.0.0 and `yajra/laravel-datatables-oracle` 11.1.1 - Server-side DataTables support configured in `config/datatables.php`
- `yaza/laravel-google-drive-storage` 4.1.0 - Google Drive filesystem disk configured in `config/filesystems.php` and used by backup jobs in `app/Jobs/BackupDatabaseToGoogle.php` and `app/Jobs/BackupFileToGoogle.php`
- `getartisanflow/wireflow` v0.1.2-alpha - Workflow canvas behavior used by `app/Livewire/WorkflowDesigner.php`

**Infrastructure:**
- `laravel/tinker` 2.9.0 - REPL support for development
- `spatie/laravel-html` 3.9.0 - HTML builder service provider registered in `bootstrap/providers.php`
- `laravolt/avatar` 6.0.0 - Runtime avatar generation used in `resources/views/layouts/backend/parsial/header.blade.php`
- `phpoffice/phpword` 1.4.0 - Word document generation used in `app/Http/Controllers/Backend/Laporan/RingkasanTemuanAuditController.php`
- `axios` 1.7.2 - Browser HTTP client bootstrapped in `resources/js/bootstrap.js`
- `bootstrap` 5.3.3, `@popperjs/core` 2.11.8, and `bootstrap-icons` 1.11.3 - Frontend UI toolkit from `package.json`
- `sass` 1.77.2 - CSS preprocessing for `resources/sass/app.scss`

## Configuration

**Environment:**
- Application settings are env-driven through Laravel config files in `config/*.php`; `.env` exists and `.env.example` documents the baseline local variables
- Core runtime/config variables come from `config/app.php`, `config/database.php`, `config/cache.php`, `config/session.php`, `config/queue.php`, `config/mail.php`, `config/filesystems.php`, and `config/services.php`
- The app defines first-class env hooks for Google Drive backups: `GOOGLE_DRIVE_CLIENT_ID`, `GOOGLE_DRIVE_CLIENT_SECRET`, `GOOGLE_DRIVE_REFRESH_TOKEN`, and `GOOGLE_DRIVE_FOLDER_ID` in `config/filesystems.php`
- Queue-backed background work is expected via `QUEUE_CONNECTION` and the scheduled worker in `routes/console.php`

**Build:**
- Frontend build config lives in `vite.config.js`
- PHP autoload rules and Composer lifecycle scripts live in `composer.json`
- PHPUnit bootstrap and test env overrides live in `phpunit.xml`
- Laravel app bootstrap and middleware/provider registration live in `bootstrap/app.php` and `bootstrap/providers.php`

## Platform Requirements

**Development:**
- PHP 8.2+ required by `composer.json`; current local runtime is PHP 8.3.30
- Node.js and npm required to run `npm run dev` and `npm run build` from `package.json`
- Writable local storage for Laravel cache, sessions, logs, and uploaded/generated files under `storage/`
- Database support for Laravel migrations plus default database/session/cache/queue tables defined by `database/migrations/0001_01_01_000000_create_users_table.php`, `database/migrations/0001_01_01_000001_create_cache_table.php`, and `database/migrations/0001_01_01_000002_create_jobs_table.php`
- `mysqldump` available on the host for the backup flows implemented in `app/Jobs/BackupDatabaseToGoogle.php` and `app/Console/Commands/BackupDatabaseToGoogle.php`

**Production:**
- Web server capable of serving a Laravel public entrypoint at `public/index.php`
- Persistent database plus queue worker execution for the `database` queue configured in `config/queue.php`
- Optional cloud filesystem credentials if the `google` or `s3` disks are enabled in `config/filesystems.php`

---

*Stack analysis: 2026-05-12*
