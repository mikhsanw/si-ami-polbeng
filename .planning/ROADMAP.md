# SI-AMI Polbeng — Roadmap

## Milestone v2 — Hardening & Quality

**Goal:** Fix known bugs, close security gaps, establish test coverage, and refactor the most fragile areas before the next feature increment.  
**Target:** Mid 2026

---

### Phase 1 — Critical Bug Fixes
**Priority:** 🔴 Immediate  
**Estimated effort:** 1–2 days

Fix the four confirmed runtime bugs identified in v1:

- [ ] `FileController`: replace `$file->exists()` call with `$file->exists` property accessor (or add an `exists()` method to `File` model)
- [ ] `HasilAuditsController`: add `use Carbon\Carbon;` import to fix LKPS date-type formula scoring crash
- [ ] `HasilAuditsController`: add `use Illuminate\Validation\ValidationException;` import to fix 500 instead of 422 responses in audit update path
- [ ] `BackupDatabaseToGoogle` job: define `$e` variable in the catch block (rename from missing binding) so backup failures log the real cause

**Verification:** Manual smoke test each fixed flow; add regression tests per phase 3.

---

### Phase 2 — Security Hardening
**Priority:** 🟠 High  
**Estimated effort:** 2–4 days

Address the four security issues documented in the v1 audit:

- [ ] **File route authentication:** Move all `file` routes (stream, download, delete, editor upload) under `auth` middleware group. Add per-file ownership authorization tied to the owning model before read/delete.
- [ ] **Public file streaming authorization:** Replace bare-ID file lookup in `GET /file-stream/{code}` with signed URLs (`URL::signedRoute`) or authenticated user checks.
- [ ] **GET delete routes → DELETE:** Convert all state-changing GET routes to `POST`/`DELETE` with CSRF protection. Update the `make:crud` generator stub so the pattern stops spreading.
- [ ] **Editor image upload safety:** Apply `FileAllowed` and `SafeFile` validation rules in `handleEditorImageUpload()`. Store sanitized uploads instead of returning raw bytes as base64 data URLs.

**Verification:** Attempt unauthenticated access to file routes; attempt GET-based delete without CSRF; upload non-image file via editor.

---

### Phase 3 — Test Coverage Foundation
**Priority:** 🟡 High  
**Estimated effort:** 3–5 days

Establish meaningful test coverage on the highest-risk flows:

- [ ] `CheckRoutePermission` middleware: test that each role can/cannot access resources (happy path + 403 cases)
- [ ] `HasilAuditsController`: test draft save, submit, formula scoring (numeric and date types), and LKPS scoring
- [ ] `FileController`: test stream 200, missing file 404, delete auth, and editor upload validation
- [ ] `DashboardController`: test role-specific dashboard responses return expected structure
- [ ] `BackupDatabaseToGoogle` job: test success path and failure path log output
- [ ] Factory setup: create Factories for `User`, `AuditPeriode`, `HasilAudit`, `Kriteria`, `Unit`

**Verification:** `php artisan test --coverage` reports > 40% coverage on covered files.

---

### Phase 4 — Controller Refactor (Audit Services)
**Priority:** 🟡 Medium  
**Estimated effort:** 3–5 days

Extract audit business logic out of controllers into reusable services:

- [ ] Create `app/Services/AuditProgressService.php`: accept `AuditPeriode` collection, return normalized progress counters and UI status metadata. Replace duplicated logic in `DashboardController` and `HasilAuditsController`.
- [ ] Create `app/Services/AuditScoringService.php`: extract `cekFormula()` and `calculateScore()` from `HasilAuditsController`. Centralize LAMEMBA vs. other body score thresholds into `config/audit.php`.
- [ ] Slim `HasilAuditsController@update`: delegate to `AuditScoringService` and move file persistence and activity logging into separate private methods or listeners.

**Verification:** All existing (post-phase 3) tests still pass. Dashboard and scoring behavior identical to before.

---

### Phase 5 — Route & Generator Normalization
**Priority:** 🟡 Medium  
**Estimated effort:** 2–3 days

Clean up route conventions and fix the CRUD generator so new modules are born clean:

- [ ] Update `MakeCrud.php` generator: emit RESTful `DELETE` routes, use controller class references (not string-based), remove duplicate resource declarations.
- [ ] Audit `routes/backend.php`: remove duplicate resource registrations (e.g. `rubrikpenilaians`, `indikatorinputs`), convert remaining GET delete routes to `DELETE`.
- [ ] Update `resources/stubs/Controller.stub` to use explicit `$model`/`$view` properties instead of relying on menu-coupled base controller resolution for new controllers.
- [ ] Add `pint.json` and a Composer `lint` script so `laravel/pint` is enforced, not just installed.

**Verification:** `php artisan make:crud TestEntity` generates correct RESTful routes with class references. `composer lint` passes on all changed files.

---

### Phase 6 — Performance Quick Wins
**Priority:** 🔵 Low  
**Estimated effort:** 1–2 days

Address the most impactful performance bottlenecks without structural overhaul:

- [ ] `DashboardController`: replace in-memory `AuditPeriode` + `HasilAudit` aggregation loops with grouped SQL queries (`DB::table(...)->selectRaw(...)->groupBy(...)`).
- [ ] `SettingController@index`: cache Google Drive backup metadata (file list + `lastModified`) to a local DB or cache store after each backup job finishes. Remove synchronous Drive metadata fetch from the page render path.
- [ ] `FileController` / `File` model: replace `Storage::get()` + `response()->make()` with `Storage::download()` or `StreamedResponse` for file delivery.

**Verification:** Settings page loads without Drive API call; dashboard page load time measured before/after.

---

### Phase 7 — UX & Feature Improvements
**Priority:** 🔵 Low (defer if bandwidth is limited)  
**Estimated effort:** TBD — discuss before planning

Potential improvements gathered from v1 usage:

- [ ] Workflow designer: persist canvas state to database instead of session (survives page refresh / multi-device)
- [ ] Audit result revision history: track which fields were revised and by whom
- [ ] Email notifications: notify auditors on new assignment, notify auditees on result verification/revision request
- [ ] Export: add Excel export option alongside Word for audit finding summaries
- [ ] Bulk auditor assignment UI
- [ ] Direktur dashboard: add trend charts (period-over-period comparisons)

> Run `/gsd-discuss-phase` before planning any phase 7 item.

---

## Milestone v1 (Archived Reference)

| Area | Status |
|------|--------|
| Foundation & Auth | ✅ |
| Audit Instrument Setup | ✅ |
| Organizational Units | ✅ |
| Audit Period Management | ✅ |
| Auditor Assignment | ✅ |
| Audit Result Submission | ✅ |
| Audit Verification | ✅ |
| Reporting & Analytics | ✅ |
| File Management | ✅ (with known bug) |
| Berita & Berita Acara | ✅ |
| Settings & Backup | ✅ (with known bug) |
| Workflow Designer | ✅ |
| CRUD Code Generator | ✅ |

---

*Roadmap created: 2026-05-12 — derived from v1 milestone summary and codebase analysis.*
