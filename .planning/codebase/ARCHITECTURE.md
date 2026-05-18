<!-- refreshed: 2026-05-12 -->
# Architecture

**Analysis Date:** 2026-05-12

## System Overview

```text
┌─────────────────────────────────────────────────────────────┐
│                   HTTP + Admin UI Layer                    │
├──────────────────┬──────────────────┬───────────────────────┤
│ Public web       │ Admin routes     │ Console / scheduler   │
│ `routes/web.php` │ `routes/backend.php` │ `routes/console.php` │
└────────┬─────────┴────────┬─────────┴──────────┬────────────┘
         │                  │                     │
         ▼                  ▼                     ▼
┌─────────────────────────────────────────────────────────────┐
│              Controllers, Middleware, Livewire             │
│ `app/Http/Controllers`, `app/Http/Middleware`,             │
│ `app/Livewire`, `app/View/Components`                      │
└─────────────────────────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────────────┐
│          Eloquent Domain + Generated CRUD Services         │
│ `app/Models`, `app/Services`, `app/Helpers`, `app/Core`    │
└─────────────────────────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────────────┐
│ Database, Blade views, assets, file storage integrations   │
│ `database/*`, `resources/views`, `public/*`, `config/*`    │
└─────────────────────────────────────────────────────────────┘
```

## Component Responsibilities

| Component | Responsibility | File |
|-----------|----------------|------|
| Laravel bootstrap | Registers route groups, middleware aliases, and console command discovery | `bootstrap/app.php` |
| Backend middleware | Injects shared view state from menu/auth context into every admin request | `app/Http/Middleware/Backend.php` |
| Permission middleware | Maps route names and URL segments to Spatie permissions | `app/Http/Middleware/CheckRoutePermission.php` |
| Base controller | Derives per-page model, code, URL, and Blade namespace from `Menu` records | `app/Http/Controllers/Controller.php` |
| Admin controllers | Own request orchestration, queries, validation, and Blade response assembly | `app/Http/Controllers/Backend/*.php` |
| Livewire workflow UI | Maintains in-browser workflow graph state and renders WireFlow | `app/Livewire/WorkflowDesigner.php` |
| Eloquent models | Hold relationships, accessors, and polymorphic file bindings | `app/Models/*.php` |
| CRUD generator services | Create controllers, models, migrations, requests, views, and route entries from stubs | `app/Console/Commands/*.php`, `app/Services/*.php`, `resources/stubs/*` |

## Pattern Overview

**Overall:** Laravel monolith with controller-centric orchestration and Blade-first rendering.

**Key Characteristics:**
- Routing is split into public and `/admin` groups in `bootstrap/app.php` and backed by separate route files.
- Controllers perform most query composition and response shaping directly against Eloquent models instead of delegating to a dedicated domain/service layer.
- The admin UI is metadata-driven through `Menu` records, which determine the controller base state, page code, view namespace, menu tree, and permission naming.

## Layers

**Routing and bootstrap:**
- Purpose: Define application entry points and middleware composition.
- Location: `bootstrap/app.php`, `routes/web.php`, `routes/backend.php`, `routes/console.php`
- Contains: Route groups, alias registration, scheduler hooks, console command discovery.
- Depends on: Laravel routing and middleware configuration.
- Used by: Every HTTP and console request.

**Request context and access control:**
- Purpose: Enrich admin requests with shared UI data and enforce per-route authorization.
- Location: `app/Http/Middleware/Backend.php`, `app/Http/Middleware/CheckRoutePermission.php`, `app/Providers/AppServiceProvider.php`
- Contains: Menu lookup, shared view data, last-login update, route-to-permission mapping, global Super Admin gate bypass.
- Depends on: `App\Models\Menu`, authenticated user state, Spatie permission APIs.
- Used by: All `/admin` routes declared in `bootstrap/app.php`.

**UI orchestration:**
- Purpose: Execute use-case logic and return Blade or JSON responses.
- Location: `app/Http/Controllers/Backend`, `app/Http/Controllers/Auth`, `app/Livewire`, `app/View/Components`
- Contains: CRUD actions, dashboard aggregation, file streaming endpoints, reporting endpoints, Livewire workflow editor, Blade layout components.
- Depends on: Eloquent models, request validation, storage, session, config.
- Used by: Route files and Blade components.

**Domain and persistence:**
- Purpose: Represent the audit domain and persistence relationships.
- Location: `app/Models`, `database/migrations`, `database/seeders`
- Contains: Audit period, unit, criteria, indicator, assignment, result, activity log, file, menu, and user models plus schema and seed data.
- Depends on: Eloquent ORM, configuration in `config/master.php`, Spatie roles for `User`.
- Used by: Controllers, middleware, jobs, helper classes.

**Code generation subsystem:**
- Purpose: Scaffold CRUD modules and route entries from local stubs.
- Location: `app/Console/Commands`, `app/Services`, `resources/stubs`
- Contains: `make:crud`, API generators, commentable generators, namespace/path resolution, stub filling.
- Depends on: Filesystem writes, stub templates, current `routes/backend.php` marker `//gencrud`.
- Used by: Developers through Artisan commands.

**Presentation and assets:**
- Purpose: Render the backend and public UI.
- Location: `resources/views`, `resources/js`, `resources/sass`, `public/assets`, `public/vendor`
- Contains: Blade screens, partials, Livewire host views, Vite entry assets, bundled theme/plugin files, WireFlow browser assets.
- Depends on: Shared view vars from middleware and layout helpers.
- Used by: Controllers, components, and Livewire.

## Data Flow

### Primary Request Path

1. Laravel boots web routing, mounts `/admin` on `routes/backend.php`, and aliases `backend` plus `check.permission` middleware (`bootstrap/app.php:8`).
2. An admin request enters the backend pipeline, where `Backend` shares `user`, `menus`, `page`, `backend`, `template`, and `helper` with all views and updates `last_login_at` (`app/Http/Middleware/Backend.php:18`).
3. `CheckRoutePermission` converts the route name into a permission string such as `hasilaudits list` and aborts with 403 if the user lacks access (`app/Http/Middleware/CheckRoutePermission.php:16`).
4. The base controller constructor resolves the current menu metadata and derives the dynamic model and Blade namespace used by backend controllers (`app/Http/Controllers/Controller.php:25`).
5. The target controller loads Eloquent relations, computes derived state, and returns a Blade view or JSON response, such as `HasilAuditsController@index` building period progress cards (`app/Http/Controllers/Backend/HasilAuditsController.php:16`).
6. The backend layout component renders the shared frame and inserts the page slot into `layouts.backend._default` (`app/View/Components/AppLayout.php:22`, `resources/views/layouts/backend/_default.blade.php:52`).

### Workflow Designer Flow

1. `/admin/workflow` routes to `WorkflowController@index` (`routes/backend.php:14`, `app/Http/Controllers/Backend/WorkflowController.php:10`).
2. The page view loads Livewire assets, AlpineFlow assets from `public/vendor/alpineflow`, and mounts `<livewire:workflow-designer />` (`resources/views/backend/workflow/index.blade.php:6`).
3. `WorkflowDesigner` initializes node/edge state in `mount()`, mutates that state through Livewire actions, and stores drafts in the browser session (`app/Livewire/WorkflowDesigner.php:20`).
4. The Livewire Blade view binds the component state to the WireFlow canvas and a JSON preview (`resources/views/livewire/workflow-designer.blade.php:1`).

### Audit Criteria Drilldown

1. `/admin/hasilaudits/audit-kriteria/{id}` dispatches to `HasilAuditsController@auditKriteriaIndex` (`routes/backend.php:126`).
2. The controller loads the `AuditPeriode`, its template, and template-scoped criteria and indicators using recursive eager loading (`app/Http/Controllers/Backend/HasilAuditsController.php:134`).
3. The Blade view recursively includes `_kriteria_item` partials and loads page-specific JS assets from the dynamic backend path (`resources/views/backend/hasilaudits/auditkriteria.blade.php:23`).

**State Management:**
- Request-scoped page context lives in middleware-shared view variables and base-controller properties.
- Persistent application state lives in MySQL-backed Eloquent models under `app/Models`.
- The workflow designer stores transient editor state in the session, not the database (`app/Livewire/WorkflowDesigner.php:122`).

## Key Abstractions

**Menu-driven page metadata:**
- Purpose: Bind route code, model class, menu hierarchy, and backend view namespace together.
- Examples: `app/Models/Menu.php`, `app/Http/Controllers/Controller.php`, `app/Http/Middleware/Backend.php`
- Pattern: Route-name prefix selects a `Menu` record; that record configures controller defaults and UI navigation.

**Audit aggregate root by controller query:**
- Purpose: Build dashboard and audit-progress screens from `AuditPeriode` plus eager-loaded child relations.
- Examples: `app/Http/Controllers/Backend/DashboardController.php`, `app/Http/Controllers/Backend/HasilAuditsController.php`
- Pattern: Controllers query `AuditPeriode` with `unit`, `instrumenTemplate.templateIndikators`, and `hasilAudits`, then derive progress/status in PHP.

**Polymorphic file attachment:**
- Purpose: Attach files to domain records and expose stream/download URLs.
- Examples: `app/Models/File.php`, `app/Models/HasilAudit.php`, `app/Http/Controllers/Backend/FileController.php`
- Pattern: `File` is a polymorphic model with computed accessors for storage-backed responses.

**Stub-based scaffolding:**
- Purpose: Generate repetitive CRUD code into the repo structure already used by the app.
- Examples: `app/Console/Commands/MakeCrud.php`, `app/Services/MakeControllerService.php`, `app/Services/PathsAndNamespacesService.php`, `resources/stubs/*`
- Pattern: Console command orchestrates file writers that resolve paths and replace placeholders inside local stubs.

## Entry Points

**HTTP bootstrap:**
- Location: `bootstrap/app.php`
- Triggers: Every web request.
- Responsibilities: Register route files, middleware aliases, health check, and console command directories.

**Public web routes:**
- Location: `routes/web.php`
- Triggers: `/`, auth flows, dynamic JS rendering, public file stream URLs.
- Responsibilities: Serve the public landing page, auth endpoints, and controller-rendered JavaScript fragments.

**Backend admin routes:**
- Location: `routes/backend.php`
- Triggers: Authenticated `/admin/*` requests.
- Responsibilities: CRUD screens, dashboard, reports, workflow designer, settings, file actions.

**Console routes and commands:**
- Location: `routes/console.php`, `app/Console/Commands/*.php`
- Triggers: Scheduler ticks and manual Artisan execution.
- Responsibilities: Queue worker scheduling, backup commands, CRUD generators.

## Architectural Constraints

- **Threading:** Single PHP request/response lifecycle for web requests; queued jobs implement asynchronous work through Laravel queues in `app/Jobs`.
- **Global state:** `App\Core\Theme` uses static properties, and `App\Models\Menu::active()` caches current menu state in static locals (`app/Core/Theme.php`, `app/Models/Menu.php:58`).
- **Dynamic controller state:** Backend controllers rely on `Controller::__construct()` to set `$this->model`, `$this->view`, `$this->code`, and `$this->url` from the current menu (`app/Http/Controllers/Controller.php:25`).
- **Configuration-driven paths:** Backend view roots, model roots, URL prefixes, and permission levels are centralized in `config/master.php` (`config/master.php:10`).
- **Route-generation marker:** The scaffolding pipeline expects the literal `//gencrud` marker to remain in `routes/backend.php` (`app/Console/Commands/MakeCrud.php:141`).

## Anti-Patterns

### Business Logic in Controllers

**What happens:** Controllers such as `DashboardController` and `HasilAuditsController` build aggregates, compute progress, and shape presentation state inline.
**Why it's wrong:** Reuse and testing become harder because query rules and derived state stay coupled to HTTP actions.
**Do this instead:** Add reusable query/domain services under `app/Services` and keep controllers focused on request orchestration, following the separation already used by the scaffolding subsystem in `app/Services/MakeControllerService.php`.

### Menu-Coupled Controller Resolution

**What happens:** The base controller infers model and view namespaces from the current `Menu` row rather than explicit controller configuration.
**Why it's wrong:** Route behavior depends on database seed/config state in addition to code, which makes controller behavior less obvious and increases coupling between navigation data and execution.
**Do this instead:** Keep menu-driven navigation in `app/Models/Menu.php`, but define controller-specific model/view dependencies explicitly inside each controller when adding new non-CRUD flows.

## Error Handling

**Strategy:** Most endpoints use Laravel defaults plus direct `view(...)`, `back()->with(...)`, JSON payloads, or thrown exceptions.

**Patterns:**
- Missing files return custom 404 views in `app/Http/Controllers/Backend/FileController.php`.
- Authorization failures abort immediately in `app/Http/Middleware/CheckRoutePermission.php`.
- Missing template dependencies in audit flows redirect back with flash errors in `app/Http/Controllers/Backend/HasilAuditsController.php`.

## Cross-Cutting Concerns

**Logging:** Queue scheduler liveness and backup operations write through Laravel logging in `routes/console.php` and `app/Jobs/BackupDatabaseToGoogle.php`.
**Validation:** Validation is split between dedicated request classes for some entities such as `User` and `Role` and inline `$request->validate()` calls in many backend controllers under `app/Http/Controllers/Backend`.
**Authentication:** Auth scaffolding comes from `Auth::routes()` in `routes/web.php`, with role and permission enforcement layered in middleware and `Gate::before` in `app/Providers/AppServiceProvider.php`.

---

*Architecture analysis: 2026-05-12*
