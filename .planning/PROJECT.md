# SI-AMI Polbeng — Project Definition

## What This Is

**SI-AMI Polbeng** (Sistem Informasi Audit Mutu Internal — Politeknik Negeri Bengkalis) is a web-based Internal Quality Audit (AMI) management system for a polytechnic institution. It replaces manual/spreadsheet-based audit workflows with a structured, role-aware application that enforces the full AMI lifecycle.

## Core Value Proposition

- Automates the AMI lifecycle from instrument setup → period scheduling → auditor assignment → result entry → verification → reporting.
- Enforces role-based access so each stakeholder (Auditee, Auditor, Admin, Super Admin, Direktur) sees only what is relevant to their function.
- Supports multiple accreditation body frameworks (LAMEMBA and others) with configurable scoring thresholds.
- Provides institutional analytics: unit rankings, audit findings, problematic standards, and progress tracking.

## Target Users

| Role | Core Tasks |
|------|-----------|
| **Super Admin** | Full system control, settings, backup management, all reports |
| **Admin** | Units, templates, criteria, indicators, rubrics, periods, user management |
| **Auditor** | Review and verify submitted audit results, request revisions |
| **Auditee** | Enter audit results, save drafts, submit, revise when requested |
| **Direktur** | View executive dashboards, unit performance rankings |

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 11.8.0 / PHP 8.3 |
| Auth & RBAC | Laravel Auth + Spatie Permission 6.7 |
| Frontend | Blade + Bootstrap 5.3 + Vite 5 |
| Reactive UI | Livewire + WireFlow (workflow designer) |
| Database | MySQL (primary); SQLite supported |
| Queue & Jobs | Laravel Queue (database driver) |
| Backup | Google Drive via `yaza/laravel-google-drive-storage` |
| Word Export | `phpoffice/phpword` |
| DataTables | `yajra/laravel-datatables` 11 |
| Testing | Pest 2 + PHPUnit 11 |
| Scaffolding | Custom `php artisan make:crud` CRUD generator |

## Repository Layout

```
app/Http/Controllers/Backend/   — Admin CRUD and workflow controllers
app/Http/Middleware/            — Backend enrichment and permission enforcement
app/Models/                     — Eloquent domain models
app/Livewire/                   — Livewire components (workflow designer)
app/Jobs/                       — Background backup jobs
app/Console/Commands/           — Artisan commands (CRUD generator, backup)
app/Services/                   — Code generation helpers
resources/views/backend/        — Blade templates per module
resources/stubs/                — CRUD generator stub templates
routes/backend.php              — All /admin/* routes
config/master.php               — Backend root paths, content enums
```

## Current Milestone

- **v1** — Complete (initial production release, Jul 2025 → Apr 2026)
- **v2** — In planning (bug fixes, security hardening, test coverage, UX improvements)

## How to Run

```bash
composer install
cp .env.example .env && php artisan key:generate
# Configure DB_* in .env
php artisan migrate && php artisan db:seed
npm install && npm run dev
php artisan serve
```

## Test Suite

```bash
./vendor/bin/pest          # Run Pest
php artisan test --coverage # With coverage
```

> ⚠️ v1 ships with placeholder tests only. Meaningful coverage is a v2 priority.

---

*Last updated: 2026-05-12*
