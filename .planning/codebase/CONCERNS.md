# Codebase Concerns

**Analysis Date:** 2026-05-12

## Tech Debt

**Oversized controller layer with duplicated audit-status logic:**
- Issue: dashboard and audit workflow calculations are implemented directly in controllers and repeated with slight variations instead of being centralized in services or query objects.
- Files: `app/Http/Controllers/Backend/DashboardController.php`, `app/Http/Controllers/Backend/HasilAuditsController.php`, `app/Http/Controllers/Backend/PenugasanAuditsController.php`
- Impact: status rules can drift between screens, fixes require multi-file edits, and regressions are likely because the same counters and labels are recalculated in several places.
- Fix approach: extract audit progress/status calculation into a dedicated service that accepts an `AuditPeriode` collection and returns normalized counters and UI status metadata.

**Generator cements unsafe routing conventions into new modules:**
- Issue: the CRUD generator writes extra GET delete routes into `routes/backend.php`, so the unsafe pattern is not incidental; it is the project default for generated resources.
- Files: `app/Console/Commands/MakeCrud.php`, `routes/backend.php`
- Impact: every generated backend module inherits non-idempotent delete flows, duplicated route declarations, and mixed legacy string-based controller references.
- Fix approach: change the generator to emit RESTful `DELETE` routes only, use controller class references, and regenerate or manually normalize affected resources.

**Base controller depends on menu records for core controller metadata:**
- Issue: `Controller::__construct()` derives `$code`, `$model`, `$url`, and `$view` from `App\Helpers\Helper::menu()` and the current route name.
- Files: `app/Http/Controllers/Controller.php`, `app/Helpers/Helper.php`
- Impact: controller behavior is coupled to database menu configuration; routes without matching menu records silently fall back to `home`, which makes debugging route/controller mismatches difficult and fragile.
- Fix approach: move per-resource metadata into explicit controller properties or route-model configuration instead of resolving it implicitly from the `menus` table.

## Known Bugs

**File endpoints call a non-existent `exists()` method on the `File` model:**
- Symptoms: file stream or delete requests can fail with a runtime error instead of returning content or a clean 404/JSON response.
- Files: `app/Http/Controllers/Backend/FileController.php`, `app/Models/File.php`
- Trigger: hit `GET /admin/file/delete/{id}/{name}` or public/private stream paths where `FileController` executes `if ($file->exists())`.
- Workaround: none in code; the model exposes an `exists` accessor property in `app/Models/File.php`, not an `exists()` instance method.

**Date-based LKPS formulas cannot be evaluated correctly:**
- Symptoms: LKPS scoring can fall back to validation failure even when the user submits valid date input.
- Files: `app/Http/Controllers/Backend/HasilAuditsController.php`
- Trigger: evaluate an LKPS indicator whose `indikator_inputs.tipe_data` is `date`; `calculateScore()` calls `Carbon::parse()` without importing `Carbon`.
- Workaround: avoid date-typed formula inputs until the controller imports `Carbon\Carbon` or uses a fully qualified class name.

**ValidationException handling in audit update path is broken:**
- Symptoms: validation-style failures inside the `try` block can return a generic 500 JSON payload instead of the intended 422 structure.
- Files: `app/Http/Controllers/Backend/HasilAuditsController.php`
- Trigger: throw `\Illuminate\Validation\ValidationException::withMessages(...)` in `update()`, then hit the catch block that checks `if ($e instanceof ValidationException)` without importing or qualifying `ValidationException`.
- Workaround: none in code; callers receive the generic exception branch.

**Database backup job logs an undefined variable on failure:**
- Symptoms: failed database backups can cascade into a second error while trying to log the failure reason.
- Files: `app/Jobs/BackupDatabaseToGoogle.php`
- Trigger: any non-zero `mysqldump` exit code; the handler logs `$e->getMessage()` even though `$e` is not defined.
- Workaround: inspect queue logs around the failed job manually; the job itself does not surface the real failure cleanly.

## Security Considerations

**Public file streaming exposes files by raw ID without auth or ownership checks:**
- Risk: anyone who can guess or obtain a file UUID can request file content through a public route.
- Files: `routes/web.php`, `app/Http/Controllers/Backend/FileController.php`
- Current mitigation: missing files fall back to a 404-style error view.
- Recommendations: require signed URLs or authenticated authorization checks tied to the owning model; do not fetch files by bare `id` from `Route::get('file-stream/{code}', ...)`.

**Administrative file routes bypass the main permission middleware:**
- Risk: `stream`, `download`, `delete`, and editor upload routes are declared outside the `auth` and `check.permission` groups.
- Files: `routes/backend.php`, `app/Http/Controllers/Backend/FileController.php`
- Current mitigation: none beyond whatever obscurity the IDs provide.
- Recommendations: move the entire `file` route group under authenticated middleware and enforce per-file authorization before read or delete actions.

**State-changing GET routes increase CSRF and accidental action risk:**
- Risk: destructive and sensitive UI flows are exposed as GET endpoints, which are easier to trigger via crawlers, prefetch, copied links, or cross-site request tricks.
- Files: `routes/backend.php`, `app/Console/Commands/MakeCrud.php`
- Current mitigation: most routes sit behind `auth` and `check.permission`, but they still violate safe HTTP semantics.
- Recommendations: convert delete and other mutating actions to `POST`/`DELETE` with CSRF protection and update the generator so the pattern stops spreading.

**Editor image upload skips server-side file safety checks and returns base64 inline data:**
- Risk: the editor upload endpoint accepts an uploaded file, reads it directly, and returns a data URL without applying `FileAllowed` or `SafeFile`.
- Files: `app/Http/Controllers/Backend/FileController.php`, `app/Rules/FileAllowed.php`, `app/Rules/SafeFile.php`
- Current mitigation: none in `handleEditorImageUpload()`.
- Recommendations: validate MIME/extension, enable `SafeFile` or equivalent content scanning, and store sanitized uploads instead of echoing arbitrary file bytes as a data URL.

## Performance Bottlenecks

**Dashboard assembly performs heavy in-memory aggregation in a 1000+ line controller:**
- Problem: dashboard endpoints eager-load broad relations and then loop through every `AuditPeriode` and `HasilAudit` in PHP to compute counts and status labels.
- Files: `app/Http/Controllers/Backend/DashboardController.php`
- Cause: business metrics are aggregated per request in controller code instead of with dedicated queries, cached summaries, or service-layer projections.
- Improvement path: precompute summary counts with grouped SQL queries or cached read models and keep controller methods thin.

**Settings page scans remote Google Drive metadata synchronously:**
- Problem: opening the settings screen can block on listing remote backup files and `lastModified` checks.
- Files: `app/Http/Controllers/Backend/SettingController.php`
- Cause: `index()` iterates through `Storage::disk('google')->files('/database/')` and fetches metadata during page render.
- Improvement path: persist backup metadata locally after jobs finish, or fetch remote status asynchronously instead of during the main request.

**File responses load full file contents into memory:**
- Problem: file download and stream actions materialize entire file payloads with `Storage::get()` and `response()->make(...)`.
- Files: `app/Models/File.php`, `app/Http/Controllers/Backend/FileController.php`, `app/Jobs/BackupFileToGoogle.php`
- Cause: the `take` accessor and related consumers read whole objects rather than streaming.
- Improvement path: switch to streamed responses and chunked copy operations for storage-to-client and storage-to-Google transfers.

## Fragile Areas

**Audit submission workflow mixes validation, persistence, scoring, uploads, and logging in one action:**
- Files: `app/Http/Controllers/Backend/HasilAuditsController.php`
- Why fragile: one method coordinates dynamic validation, score calculation, `updateOrCreate`, file creation, activity logging, and JSON response shaping. Small rule changes can break unrelated parts of the workflow.
- Safe modification: isolate formula evaluation, file persistence, and status transitions behind tested services before changing request rules or status behavior.
- Test coverage: no focused tests exist under `tests/`; only `tests/Feature/ExampleTest.php` and `tests/Unit/ExampleTest.php` are present.

**Role and permission gating is split across middleware, controller branches, and route conventions:**
- Files: `app/Http/Middleware/CheckRoutePermission.php`, `app/Providers/AppServiceProvider.php`, `routes/backend.php`, `app/Http/Controllers/Backend/UserController.php`
- Why fragile: access control depends on route names mapping cleanly to `"resource action"` permission strings, with custom fallbacks for unnamed routes and multiple controller-level role exceptions.
- Safe modification: keep route naming consistent, avoid unnamed custom routes, and add authorization tests before changing route names or permission strings.
- Test coverage: no authorization tests are present in `tests/`.

**Backup flow depends on shell tooling and queue health with little verification:**
- Files: `app/Jobs/BackupDatabaseToGoogle.php`, `app/Jobs/BackupFileToGoogle.php`, `app/Http/Controllers/Backend/SettingController.php`, `config/queue.php`
- Why fragile: the database job shells out to `mysqldump`, file backups assume remote disk availability, and the UI only checks whether there are pending jobs in the default queue.
- Safe modification: add explicit failure handling, post-upload verification, queue segregation, and health checks before expanding the backup feature.
- Test coverage: no job or backup integration tests are present in `tests/`.

## Test Coverage Gaps

**Critical backend flows are untested:**
- What's not tested: file streaming/deletion, audit scoring, audit status transitions, dashboard aggregation, permission middleware, and backup jobs.
- Files: `app/Http/Controllers/Backend/FileController.php`, `app/Http/Controllers/Backend/HasilAuditsController.php`, `app/Http/Controllers/Backend/DashboardController.php`, `app/Http/Middleware/CheckRoutePermission.php`, `app/Jobs/BackupDatabaseToGoogle.php`, `app/Jobs/BackupFileToGoogle.php`, `tests/Pest.php`, `tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php`
- Risk: regressions in authorization, scoring, file access, and backups can ship unnoticed because the repository currently contains only the default example tests.
- Priority: High

---

*Concerns audit: 2026-05-12*
