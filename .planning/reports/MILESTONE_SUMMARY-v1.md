# Milestone v1 — Project Summary

**Generated:** 2026-05-12  
**Purpose:** Team onboarding and project review  
**Source:** Regenerated from codebase analysis (no `.planning` milestone artifacts present — summary reflects live code)

---

## 1. Project Overview

**SI-AMI Polbeng** (Sistem Informasi Audit Mutu Internal — Politeknik Negeri Bengkalis) is an internal audit management information system for a polytechnic institution. It automates the full Internal Quality Audit (AMI) lifecycle — from audit instrument setup and period scheduling, through auditor assignment and result submission, to performance reporting and analytics.

**Core value proposition:**
- Replaces manual/spreadsheet-based internal audit workflows with a structured, role-aware web application.
- Enforces a defined audit lifecycle: instrument creation → period creation → auditor assignment → result entry → verification → reporting.
- Supports multiple accreditation body frameworks (e.g., LAMEMBA and others) with configurable scoring thresholds.

**Target users:**

| Role | Responsibilities |
|------|-----------------|
| **Super Admin** | Full system control, settings, backup, and all reporting |
| **Admin** | Manage units, templates, criteria, indicators, periods, and users |
| **Auditor** | Review and verify submitted audit results |
| **Auditee** | Fill in audit results, save drafts, submit, and revise |
| **Direktur** | View aggregate dashboards and unit performance rankings |

**Milestone status:** v1 is the initial production release covering the complete audit lifecycle. No formal ROADMAP phases were tracked; the system was built iteratively from commit history spanning **2025-07-25 → 2026-04-09** (114 commits, single contributor).

---

## 2. Architecture & Technical Decisions

The system is a **Laravel 11 monolith** with Blade-first server rendering, a metadata-driven admin UI, and a CRUD code-generation subsystem.

```
┌──────────────────────────────────────────────────┐
│              HTTP + Admin UI Layer               │
│  routes/web.php │ routes/backend.php │ console   │
└──────────┬───────────────────┬──────────┬────────┘
           │                   │          │
           ▼                   ▼          ▼
┌──────────────────────────────────────────────────┐
│  Controllers  │  Middleware  │  Livewire          │
│  app/Http/Controllers/Backend/                   │
│  app/Http/Middleware/  │  app/Livewire/           │
└──────────────────────────────────────────────────┘
           │
           ▼
┌──────────────────────────────────────────────────┐
│  Eloquent Domain + Services (scaffolding only)   │
│  app/Models/  │  app/Services/  │  app/Jobs/     │
└──────────────────────────────────────────────────┘
           │
           ▼
┌──────────────────────────────────────────────────┐
│  MySQL DB  │  Blade views  │  Storage/Google Drive│
└──────────────────────────────────────────────────┘
```

**Key architectural decisions:**

- **Decision:** Menu-driven controller metadata resolution  
  - **Why:** The base `Controller` reads the current `Menu` record to derive `$code`, `$model`, `$url`, and `$view` for every admin request, enabling a single CRUD generator to scaffold fully functional modules without per-controller boilerplate.  
  - **Trade-off:** Controller behavior depends on database menu state, making debugging harder when menu records and routes are mismatched.

- **Decision:** Centralized role/permission enforcement via Spatie Permission + custom middleware  
  - **Why:** `CheckRoutePermission` middleware converts route names (e.g. `hasilaudits.index`) to permission strings (e.g. `hasilaudits list`) automatically, with a `Gate::before` bypass for Super Admin.  
  - **Trade-off:** Requires strict route naming conventions; unnamed or custom routes need manual fallback rules.

- **Decision:** Stub-based CRUD code generation (`php artisan make:crud`)  
  - **Why:** Reduces repetitive controller/model/migration/view/route scaffolding to a single Artisan command. All backend modules (units, periods, assignments, results, etc.) were bootstrapped this way.  
  - **Trade-off:** Generator currently emits unsafe GET-based delete routes and duplicated resource declarations; new modules inherit these issues.

- **Decision:** Audit analytics computed in-memory in PHP controllers  
  - **Why:** Fastest path to working dashboards for each role. No caching or pre-aggregation layer was built.  
  - **Trade-off:** `DashboardController` is 1000+ lines; heavy queries run per request with no caching.

- **Decision:** Google Drive for automated backup  
  - **Why:** No self-hosted backup infrastructure; Drive OAuth gives persistent off-site storage via `yaza/laravel-google-drive-storage`.  
  - **Phase:** Implemented in `app/Jobs/BackupDatabaseToGoogle.php` and `app/Jobs/BackupFileToGoogle.php`.

- **Decision:** Livewire + WireFlow for workflow designer  
  - **Why:** The workflow designer (`WorkflowDesigner` Livewire component) provides a drag-and-drop canvas for building audit workflow graphs without a full SPA. Editor state is session-stored, not persisted to DB.

---

## 3. Phases Delivered

No formal phase tracking existed. The following functional areas were identified from the codebase and commit history:

| # | Area | Status | Description |
|---|------|--------|-------------|
| 1 | Foundation & Auth | ✅ Complete | Laravel 11 bootstrap, role-based auth (Spatie), backend middleware, menu-driven navigation |
| 2 | Audit Instrument Setup | ✅ Complete | Accreditation body (`LembagaAkreditasi`), templates (`InstrumenTemplate`), criteria (`Kriteria`), indicators (`Indikator`), rubrics (`RubrikPenilaian`) |
| 3 | Organizational Units | ✅ Complete | Unit CRUD, hierarchical unit structure, unit assignment to audit periods |
| 4 | Audit Period Management | ✅ Complete | `AuditPeriode` lifecycle, period creation, template binding, status management |
| 5 | Auditor Assignment | ✅ Complete | `PenugasanAudit` — assigning auditors to units per period |
| 6 | Audit Result Submission | ✅ Complete | `HasilAudit` entry by auditee, save-draft and submit flow, formula-based scoring (`cekFormula`), LKPS support |
| 7 | Audit Verification | ✅ Complete | Auditor review and verification of submitted results, revision requests |
| 8 | Reporting & Analytics | ✅ Complete | Role-specific dashboards, unit ranking, temuan (findings) reports, standar bermasalah, `RingkasanTemuanAudit` Word export, PDF/form generation |
| 9 | File Management | ✅ Complete | Polymorphic `File` model, file streaming, thumbnails, editor image upload |
| 10 | Berita & Berita Acara | ✅ Complete | Announcement publishing and audit event records |
| 11 | Settings & Backup | ✅ Complete | System settings, Google Drive backup (DB + files), backup status in admin panel |
| 12 | Workflow Designer | ✅ Complete | Livewire-powered drag-and-drop workflow canvas with WireFlow/AlpineFlow integration |
| 13 | CRUD Code Generator | ✅ Complete | `php artisan make:crud` and API/commentable generators for rapid module scaffolding |

---

## 4. Requirements Coverage

*Requirements inferred from codebase behavior (no formal REQUIREMENTS.md exists).*

| Requirement | Status | Notes |
|-------------|--------|-------|
| Secure authenticated access with role-based authorization | ✅ Met | Spatie Permission, `CheckRoutePermission` middleware, `Gate::before` Super Admin bypass |
| Manage accreditation bodies, templates, criteria, indicators, rubrics | ✅ Met | Full CRUD in backend; hierarchical structure supported |
| Audit period lifecycle management | ✅ Met | `AuditPeriode` with status tracking and period-scoped operations |
| Auditor assignment per period and unit | ✅ Met | `PenugasanAudit` module with auditor selection |
| Auditee audit result entry (draft → submit → revise) | ✅ Met | `HasilAudit` with status state machine and formula scoring |
| Auditor verification of submitted results | ✅ Met | Verification and revision-request flows in `HasilAuditsController` |
| Role-specific dashboards and analytics | ✅ Met | `DashboardController` with per-role KPIs: rankings, findings, problematic standards, progress |
| Report generation (PDF, Word) | ✅ Met | `Laporan/*` controllers; `phpoffice/phpword` for Word exports |
| File attachment and streaming | ⚠️ Partial | Polymorphic attachment works; known bug: `$file->exists()` calls a non-existent method |
| Google Drive automated backup | ⚠️ Partial | Job exists and runs; known bug: failure logging crashes on undefined `$e` variable |
| Announcement and berita acara management | ✅ Met | `Berita` and `BeritaAcara` CRUD |
| Workflow designer | ✅ Met | Livewire/WireFlow canvas; session-stored state (not persisted to DB) |
| Automated testing coverage | ❌ Not met | Only placeholder `ExampleTest.php` tests exist; all business flows untested |

---

## 5. Key Decisions Log

| ID | Decision | Rationale |
|----|----------|-----------|
| D-01 | Menu-driven controller metadata | Single CRUD generator scaffolds full modules; reduces boilerplate at the cost of DB coupling |
| D-02 | Spatie Permission for RBAC | Mature, widely maintained Laravel RBAC; integrates with middleware aliases cleanly |
| D-03 | CRUD generator with GET delete routes | Faster initial development; acknowledged tech debt — should be changed to DELETE verb |
| D-04 | Controller-centric audit aggregation | No service layer needed for MVP; DashboardController owns all analytics logic inline |
| D-05 | Google Drive for backups | No self-hosted storage; Drive OAuth provides persistent off-site backup with minimal infra |
| D-06 | Session-stored workflow designer state | WireFlow canvas is a design tool, not a workflow engine; DB persistence deferred |
| D-07 | LAMEMBA vs. other body score thresholds | Accreditation-body-specific compliance thresholds encoded inline in `HasilAuditsController` |
| D-08 | `yajra/laravel-datatables` for list views | Server-side DataTables for all admin list pages; consistent search/sort/paginate behavior |
| D-09 | `phpoffice/phpword` for Word export | Audit finding summary required `.docx` format for institutional use |
| D-10 | Livewire for workflow designer UI | Server-driven interactivity without SPA overhead; avoids separate JS framework |

---

## 6. Tech Debt & Deferred Items

### 🔴 Known Bugs (Fix First)

| Bug | File | Impact |
|-----|------|--------|
| `$file->exists()` calls a non-existent instance method | `FileController.php`, `File.php` | File stream/delete endpoints throw runtime errors |
| Missing `Carbon` import in LKPS date formula scoring | `HasilAuditsController.php` | Date-typed LKPS indicators always fail scoring |
| `ValidationException` not imported in audit update catch block | `HasilAuditsController.php` | Validation errors return 500 instead of 422 |
| Undefined `$e` in backup job failure handler | `BackupDatabaseToGoogle.php` | Backup failures log a second error, masking the real cause |

### 🟡 Tech Debt (Refactor When Stable)

| Issue | Files | Suggested Fix |
|-------|-------|---------------|
| Business logic duplicated across dashboard and audit controllers | `DashboardController.php`, `HasilAuditsController.php` | Extract audit progress/status into a dedicated `AuditProgressService` |
| CRUD generator emits GET delete routes and duplicates resource registrations | `MakeCrud.php`, `routes/backend.php` | Update generator to emit RESTful `DELETE` routes with controller class references |
| Menu-coupled controller resolution is opaque and fragile | `Controller.php`, `Helper.php` | Define `$model`/`$view` explicitly per controller; keep Menu only for navigation |
| Critical business rules (score thresholds) embedded in controllers | `HasilAuditsController.php` | Move to config (`config/audit.php`) or a dedicated policy class |
| Settings page makes synchronous Google Drive metadata calls on page load | `SettingController.php` | Persist backup metadata locally after jobs finish; fetch asynchronously |
| File responses load full file into memory | `File.php`, `FileController.php` | Switch to streamed responses with chunked reads |

### 🟠 Security Issues (Address Before Production Scale-Up)

| Issue | Risk | Fix |
|-------|------|-----|
| Public file streaming has no auth or ownership check | Any user who knows a file UUID can access it | Require signed URLs or authenticated authorization tied to the owning model |
| Admin file routes bypass permission middleware | Unauthenticated file reads/deletes possible | Move all `file` routes under `auth` + `check.permission` groups |
| GET routes perform state-changing operations | CSRF, accidental actions via crawlers/prefetch | Convert delete/mutate routes to `POST`/`DELETE` with CSRF |
| Editor image upload skips file safety checks | Arbitrary file content returned as base64 | Apply `SafeFile`/`FileAllowed` rules in `handleEditorImageUpload()` |

### 🔵 Test Coverage Gap (High Priority)

Currently **0% meaningful test coverage**. Priority areas for new tests:
1. Audit result submission and scoring (`HasilAuditsController`)
2. Role-based authorization middleware (`CheckRoutePermission`)
3. File stream and delete endpoints (`FileController`)
4. Dashboard aggregation logic (`DashboardController`)
5. Backup job success and failure paths (`BackupDatabaseToGoogle`)

---

## 7. Getting Started

### Prerequisites
- PHP 8.2+ (project runs on PHP 8.3.30)
- Node.js v22+ and npm 10+
- MySQL (or compatible) database
- Composer 2.x
- Optional: Google Drive credentials for backup feature

### Setup

```bash
# 1. Install PHP dependencies
composer install

# 2. Configure environment
cp .env.example .env
php artisan key:generate

# 3. Set database credentials in .env (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD)

# 4. Run migrations and seed initial data
php artisan migrate
php artisan db:seed   # Seeds roles, permissions, menus, and a default super admin user

# 5. Install and build frontend assets
npm install && npm run dev

# 6. Start the development server
php artisan serve
```

### Key Entry Points

| What | Where |
|------|-------|
| Application bootstrap | `bootstrap/app.php` |
| Admin routes | `routes/backend.php` |
| Public + auth routes | `routes/web.php` |
| Console schedule | `routes/console.php` |
| Role/permission config | `config/permission.php` |
| Backend root paths | `config/master.php` |
| Google Drive backup config | `config/filesystems.php` |
| New CRUD module scaffold | `php artisan make:crud` |

### Where to Look First

- **Audit lifecycle core:** `app/Http/Controllers/Backend/HasilAuditsController.php`
- **Dashboard analytics:** `app/Http/Controllers/Backend/DashboardController.php`
- **Permission enforcement:** `app/Http/Middleware/CheckRoutePermission.php`
- **Domain models:** `app/Models/AuditPeriode.php`, `app/Models/HasilAudit.php`, `app/Models/Kriteria.php`
- **Menu navigation:** `app/Models/Menu.php`, `database/seeders/MenuSeeder.php`
- **Code generator:** `app/Console/Commands/MakeCrud.php`

### Running Tests

```bash
./vendor/bin/pest          # Run Pest suite
php artisan test           # Run via Laravel
php artisan test --coverage # With coverage report
```

> ⚠️ Current test suite is placeholder-only. Meaningful tests have not yet been written.

---

## Stats

| Metric | Value |
|--------|-------|
| **Timeline** | 2025-07-25 → 2026-04-09 (~8.5 months) |
| **Total commits** | 114 |
| **Contributors** | 1 (ikhsan.flip@gmail.com) |
| **Functional areas delivered** | 13 |
| **Known bugs** | 4 (documented above) |
| **Security issues** | 4 (documented above) |
| **Test coverage** | ~0% (placeholder tests only) |
| **Framework** | Laravel 11.8.0 / PHP 8.3.30 |
| **Primary language** | PHP + Blade |

---

*Summary regenerated 2026-05-12 from codebase analysis artifacts in `.planning/codebase/`.*  
*No formal milestone ROADMAP or REQUIREMENTS files were present; all content reflects live code analysis.*
