# Codebase Structure

**Analysis Date:** 2026-05-12

## Directory Layout

```text
[project-root]/
├── app/                 # Application code: controllers, middleware, models, services, jobs, Livewire
├── bootstrap/           # Laravel application bootstrap
├── config/              # Application and package configuration
├── database/            # Migrations, factories, seeders
├── public/              # Web root with bundled theme, plugins, and browser assets
├── resources/           # Blade views, Vite source assets, local codegen stubs
├── routes/              # Web, backend, and console entry points
├── tests/               # Pest baseline tests
└── .planning/codebase/  # Generated codebase map documents
```

## Directory Purposes

**`app/Http/Controllers`:**
- Purpose: HTTP orchestration for auth, admin CRUD, reporting, dashboard, file streaming, and workflow pages.
- Contains: Base `Controller`, `Auth/*`, `Backend/*`, and nested report controllers under `Backend/Laporan`.
- Key files: `app/Http/Controllers/Controller.php`, `app/Http/Controllers/Backend/DashboardController.php`, `app/Http/Controllers/Backend/HasilAuditsController.php`, `app/Http/Controllers/Backend/FileController.php`

**`app/Http/Middleware`:**
- Purpose: Backend request enrichment and authorization.
- Contains: `Backend.php` and `CheckRoutePermission.php`.
- Key files: `app/Http/Middleware/Backend.php`, `app/Http/Middleware/CheckRoutePermission.php`

**`app/Models`:**
- Purpose: Eloquent models for navigation, identity, audit domain entities, and file attachments.
- Contains: UUID-backed models with relationships, accessors, and polymorphic file associations.
- Key files: `app/Models/Menu.php`, `app/Models/User.php`, `app/Models/HasilAudit.php`, `app/Models/File.php`

**`app/Services`:**
- Purpose: Internal scaffolding helpers, not general business services.
- Contains: CRUD, API, and commentable code generators plus path/namespace helpers.
- Key files: `app/Services/MakeControllerService.php`, `app/Services/MakeGlobalService.php`, `app/Services/PathsAndNamespacesService.php`

**`app/Console/Commands`:**
- Purpose: Developer automation and operational commands.
- Contains: CRUD generators, API generators, commentable generators, and Google Drive backup commands.
- Key files: `app/Console/Commands/MakeCrud.php`, `app/Console/Commands/BackupDatabaseToGoogle.php`, `app/Console/Commands/BackupFilesToGoogle.php`

**`app/Jobs`:**
- Purpose: Background backup tasks intended for the Laravel queue worker.
- Contains: Google Drive backup jobs for files and database dumps.
- Key files: `app/Jobs/BackupDatabaseToGoogle.php`, `app/Jobs/BackupFileToGoogle.php`

**`app/Livewire`:**
- Purpose: Stateful server-driven UI components.
- Contains: A single workflow designer component.
- Key files: `app/Livewire/WorkflowDesigner.php`

**`app/View/Components`:**
- Purpose: Blade layout components.
- Contains: Application and auth layout wrappers.
- Key files: `app/View/Components/AppLayout.php`, `app/View/Components/AuthLayout.php`

**`app/Helpers` and `app/Core`:**
- Purpose: Global helper behavior and theme asset state.
- Contains: Menu helpers, formatting utilities, theme asset registries.
- Key files: `app/Helpers/Helper.php`, `app/Helpers/HelpTheme.php`, `app/Core/Theme.php`

**`config`:**
- Purpose: Laravel and package configuration, including the app-specific master config that defines backend roots and content enums.
- Contains: Standard Laravel config plus `master.php`, `permission.php`, `wireflow.php`, `datatables.php`.
- Key files: `config/master.php`, `config/filesystems.php`, `config/permission.php`, `config/wireflow.php`

**`database/migrations`:**
- Purpose: Schema definition for users, menus, permissions, audit domain tables, and attachments.
- Contains: Core Laravel migrations and project-specific audit schema files.
- Key files: `database/migrations/2025_07_20_153716_create_audit_periodes_table.php`, `database/migrations/2025_07_20_161859_create_hasil_audits_table.php`, `database/migrations/2025_09_19_044733_create_template_indikators_table.php`

**`database/seeders`:**
- Purpose: Initial roles, permissions, users, and menu records.
- Contains: Database and menu seeders.
- Key files: `database/seeders/DatabaseSeeder.php`, `database/seeders/MenuSeeder.php`

**`resources/views`:**
- Purpose: Blade templates for auth pages, backend modules, layouts, Livewire, and public pages.
- Contains: One directory per backend module, layout partials, report pages, recursive partials, and Livewire host views.
- Key files: `resources/views/layouts/backend/_default.blade.php`, `resources/views/backend/workflow/index.blade.php`, `resources/views/backend/hasilaudits/auditkriteria.blade.php`, `resources/views/livewire/workflow-designer.blade.php`

**`resources/js`, `resources/sass`, `resources/css`:**
- Purpose: Vite source assets.
- Contains: Minimal JS bootstrap plus Sass/CSS entry points.
- Key files: `resources/js/app.js`, `resources/js/bootstrap.js`, `resources/sass/app.scss`

**`resources/stubs`:**
- Purpose: Local scaffolding templates consumed by generator services.
- Contains: CRUD controller/model/request stubs, API stubs, commentable stubs, and Blade partial stubs.
- Key files: `resources/stubs/Controller.stub`, `resources/stubs/views/index.stub`, `resources/stubs/api/Controller-api.stub`

**`routes`:**
- Purpose: Application entry points for HTTP and console workloads.
- Contains: Public web routes, admin backend routes, and scheduler/Artisan route definitions.
- Key files: `routes/web.php`, `routes/backend.php`, `routes/console.php`

**`public`:**
- Purpose: Web-served static assets and prebuilt frontend bundles.
- Contains: Admin theme assets, plugins, media, Vite output, and vendored WireFlow browser files.
- Key files: `public/assets/*`, `public/vendor/alpineflow/*`, `public/front/dist/*`

**`tests`:**
- Purpose: Pest test bootstrap and example test placeholders.
- Contains: `Feature`, `Unit`, `Pest.php`, and Laravel base test case.
- Key files: `tests/Pest.php`, `tests/TestCase.php`, `tests/Feature/ExampleTest.php`

## Key File Locations

**Entry Points:**
- `bootstrap/app.php`: Main Laravel bootstrap and route-group composition.
- `routes/web.php`: Public pages, auth routes, dynamic JS endpoint, public file endpoints.
- `routes/backend.php`: Admin module routing under `/admin`.
- `routes/console.php`: Scheduler and console route definitions.

**Configuration:**
- `config/master.php`: App-specific backend root paths, URL prefixes, content enums, and permission levels.
- `config/filesystems.php`: Storage disks used by `app/Models/File.php` and backup jobs.
- `vite.config.js`: Vite input registration for `resources/js/app.js` and `resources/sass/app.scss`.

**Core Logic:**
- `app/Http/Controllers/Backend/DashboardController.php`: Role-based dashboard aggregation.
- `app/Http/Controllers/Backend/HasilAuditsController.php`: Audit result listing, drilldown, editing, and scoring.
- `app/Models/Menu.php`: Menu hierarchy and dynamic model resolution.
- `app/Models/HasilAudit.php`: Audit result relationships and formatted attributes.
- `app/Livewire/WorkflowDesigner.php`: WireFlow state machine for workflow editing.
- `app/Console/Commands/MakeCrud.php`: Scaffolding entry point for new CRUD modules.

**Testing:**
- `tests/Pest.php`: Pest bootstrap.
- `tests/TestCase.php`: Laravel base test case.
- `tests/Feature/ExampleTest.php`: Current feature test placeholder.

## Naming Conventions

**Files:**
- PHP classes use StudlyCase filenames that match class names: `DashboardController.php`, `AuditPeriode.php`, `WorkflowDesigner.php`.
- Blade module views are organized by route/resource code in lowercase plural directories: `resources/views/backend/hasilaudits`, `resources/views/backend/penugasanaudits`.
- Reusable Blade fragments use underscore-prefixed or descriptive partial names: `resources/views/backend/hasilaudits/_kriteria_item.blade.php`, `resources/views/layouts/backend/parsial/header.blade.php`.

**Directories:**
- Backend module directories follow route/resource names: `app/Http/Controllers/Backend`, `resources/views/backend/units`, `resources/views/backend/ringkasanunits`.
- Report controllers and views are grouped under `Laporan` and corresponding `ringkasan*` view folders: `app/Http/Controllers/Backend/Laporan`, `resources/views/backend/ringkasanstandars`.
- Generator stubs are grouped by feature family: `resources/stubs/api`, `resources/stubs/commentable`, `resources/stubs/views`.

## Where to Add New Code

**New backend feature:**
- Primary code: add routes in `routes/backend.php`, controller code in `app/Http/Controllers/Backend`, and views in `resources/views/backend/<resource-code>`.
- Tests: add feature coverage in `tests/Feature`.

**New public page or endpoint:**
- Implementation: add route definitions in `routes/web.php`, controller logic in `app/Http/Controllers`, and views under `resources/views`.

**New audit-domain model:**
- Implementation: add the model in `app/Models`, schema in `database/migrations`, and seed data in `database/seeders` when required.

**New Livewire-backed admin UI:**
- Implementation: add the component class in `app/Livewire`, mount it from a backend Blade view in `resources/views/backend/<feature>`, and use `x-app-layout` from `app/View/Components/AppLayout.php`.

**New background task:**
- Implementation: queueable logic belongs in `app/Jobs`; manual or scheduled entry points belong in `app/Console/Commands` and `routes/console.php`.

**New scaffolding template or generator behavior:**
- Shared helpers: extend `app/Services/*` and `resources/stubs/*`.
- Route insertion behavior: preserve the `//gencrud` marker in `routes/backend.php`.

**Utilities:**
- Shared helpers: `app/Helpers` for formatting/menu helpers and `app/Core` for theme asset helpers.
- Avoid placing domain business logic in `app/Services` unless it is intentionally a reusable application service; that directory is currently dominated by code generation concerns.

## Special Directories

**`resources/stubs`:**
- Purpose: Source templates for CRUD/API/commentable generation.
- Generated: No
- Committed: Yes

**`public/vendor/alpineflow`:**
- Purpose: Browser assets for the workflow designer’s WireFlow integration.
- Generated: Vendor-distributed assets
- Committed: Yes

**`public/assets`:**
- Purpose: Admin theme bundles, plugin bundles, icons, media, and page JS.
- Generated: Mix of vendored and committed static assets
- Committed: Yes

**`.planning/codebase`:**
- Purpose: Generated architecture and mapping reference documents.
- Generated: Yes
- Committed: Yes

---

*Structure analysis: 2026-05-12*
