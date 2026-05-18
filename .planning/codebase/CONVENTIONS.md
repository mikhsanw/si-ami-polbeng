# Coding Conventions

**Analysis Date:** 2026-05-12

## Naming Patterns

**Files:**
- Use PSR-4 class filenames that match the class name exactly for PHP code under `app/`, such as `app/Http/Controllers/Backend/UserController.php`, `app/Http/Requests/StoreUserRequest.php`, `app/Models/AuditPeriode.php`, and `app/Livewire/WorkflowDesigner.php`.
- Use pluralized resource controller names for backend CRUD controllers in `app/Http/Controllers/Backend/`, for example `UnitsController.php`, `KriteriasController.php`, and `HasilAuditsController.php`.
- Use verb-prefixed request filenames for form requests in `app/Http/Requests/`, for example `StoreUserRequest.php` and `UpdateRoleRequest.php`.
- Use singular PascalCase model filenames in `app/Models/`, for example `User.php`, `Unit.php`, and `BeritaAcara.php`.

**Functions:**
- Use camelCase method names throughout PHP classes, including controller actions such as `createChild()`, `cekFormula()`, and `destroyIndikator()` in `app/Http/Controllers/Backend/KriteriasController.php`.
- Use Laravel resource action names where the controller follows CRUD, such as `index`, `create`, `store`, `show`, `edit`, `update`, and `destroy` in `app/Http/Controllers/Backend/UserController.php`.
- Use relationship-style method names on Eloquent models in lower camelCase, such as `penugasanAuditors()` and `unit()` in `app/Models/User.php`.

**Variables:**
- Use camelCase for local variables and request payload variables, such as `$validatedData`, `$routeName`, `$permissionAction`, and `$nodeSequence` in `app/Http/Controllers/Backend/UnitsController.php`, `app/Http/Middleware/CheckRoutePermission.php`, and `app/Livewire/WorkflowDesigner.php`.
- Use snake_case only for array keys that mirror database columns or request fields, such as `parent_id`, `user_id`, `last_login_at`, and `saved_at` in `app/Http/Controllers/Backend/UnitsController.php`, `app/Models/User.php`, and `app/Livewire/WorkflowDesigner.php`.

**Types:**
- Use PascalCase class names for all PHP types under `App\`, such as `CheckRoutePermission`, `BackupDatabaseToGoogle`, and `MakeControllerService`.
- Use scalar and array return types when the file is newer or more deliberately typed, such as `authorize(): bool` and `rules(): array` in `app/Http/Requests/StoreUserRequest.php`, and `makeNode(...): array` in `app/Livewire/WorkflowDesigner.php`.
- Do not assume return types are uniformly enforced across the codebase. Many controllers omit them entirely, for example `store()` and `destroy()` in `app/Http/Controllers/Backend/UserController.php`.

## Code Style

**Formatting:**
- Base formatting is defined by `.editorconfig`, not by a checked-in Pint or Prettier config. Use UTF-8, LF endings, 4-space indentation, final newlines, and trimmed trailing whitespace from `.editorconfig`.
- Keep PHP opening tags on the first line and place namespaces and imports at the top of each file, as shown in `app/Http/Requests/StoreUserRequest.php` and `app/Models/User.php`.
- Follow the existing multi-line array formatting style with trailing commas in newer files, such as `app/Livewire/WorkflowDesigner.php:30-39` and `app/Http/Controllers/Backend/UnitsController.php:66-71`.
- Expect style inconsistency in older CRUD files. Spacing around `if` statements, braces, concatenation, and boolean literals varies in `app/Http/Controllers/Backend/UserController.php:23-59` and `app/Http/Controllers/Backend/UserController.php:127-160`.

**Linting:**
- `laravel/pint` is installed in `composer.json:21-31`, but no `pint.json` or composer script is checked in. Treat Pint as available tooling, not as an enforced project rule.
- No ESLint, Prettier, Biome, or PHP CS Fixer config is present at the repository root. Frontend JavaScript style is effectively lightweight Vite/Laravel default style in `resources/js/app.js` and `resources/js/bootstrap.js`.

## Import Organization

**Order:**
1. Namespace declaration.
2. Framework and app imports.
3. Class definition.

**Path Aliases:**
- PHP uses PSR-4 namespaces from `composer.json:33-47`, primarily `App\` and `Tests\`.
- JavaScript path aliases are not detected. `resources/js/app.js` and `resources/js/bootstrap.js` use relative or package imports only.

## Error Handling

**Patterns:**
- Prefer Laravel validation short-circuiting via form requests where available, as in `app/Http/Requests/StoreUserRequest.php:12-30` and `app/Http/Controllers/Backend/UserController.php:79-88`.
- Many controllers validate inline with `$request->validate(...)`, especially in CRUD controllers such as `app/Http/Controllers/Backend/MenuController.php` and `app/Http/Controllers/Backend/InstrumenTemplatesController.php`.
- Where manual validator control is needed, use `Validator::make(...)`, return JSON with `422` on failure, and wrap persistence in `try/catch`, as in `app/Http/Controllers/Backend/UnitsController.php:64-100`.
- Authorization failures are handled with `abort(403, ...)` rather than custom exceptions, as in `app/Http/Middleware/CheckRoutePermission.php:47-51` and `app/Http/Controllers/Backend/UserController.php:106-110`.
- `findOrFail()` and `firstOrFail()` are used for missing records instead of null checks in many backend flows, including `app/Http/Controllers/Backend/PenugasanAuditsController.php` and `app/Http/Controllers/Backend/HasilAuditsController.php`.

## Logging

**Framework:** `Illuminate\Support\Facades\Log`

**Patterns:**
- Log operational failures inside `catch` blocks with concatenated exception messages, as in `app/Http/Controllers/Backend/UnitsController.php:93-99` and `app/Http/Controllers/Backend/HasilAuditsController.php:471-473`.
- Use `info`, `warning`, and `error` levels directly for long-running or formula-processing flows, as in `app/Jobs/BackupDatabaseToGoogle.php` and `app/Http/Controllers/Backend/HasilAuditsController.php`.
- Import the `Log` facade explicitly when you add new logging. Some files reference `Log::...` without an import, so consistency is not fully enforced.

## Comments

**When to Comment:**
- Keep docblocks on classes and public methods when generated by Laravel stubs, as in `app/Http/Requests/StoreUserRequest.php:9-21` and `app/Http/Controllers/Backend/UserController.php:18-20`.
- Use short inline comments to explain permission rules or non-obvious control flow, such as `// Check Only Super Admin can update his own Profile` in `app/Http/Controllers/Backend/UserController.php:106` and the route-to-permission notes in `app/Http/Middleware/CheckRoutePermission.php:20-45`.
- Do not add narrative comments for self-evident CRUD code. Most controllers rely on method names and structure instead.

**JSDoc/TSDoc:**
- Not used in the JavaScript files present under `resources/js/`.
- PHPDoc appears in generated request classes and middleware signatures, but broad API-level documentation is not present.

## Function Design

**Size:** 
- Keep FormRequest methods compact and single-purpose, as in `app/Http/Requests/StoreUserRequest.php:12-30`.
- Expect backend controllers to hold large, multi-branch action methods. `app/Http/Controllers/Backend/UserController.php` and `app/Http/Controllers/Backend/UnitsController.php` show the prevailing style.

**Parameters:**
- Inject Laravel request objects directly into controller actions, for example `index(Request $request)` and `store(StoreUserRequest $request)` in `app/Http/Controllers/Backend/UserController.php`.
- Use route model binding selectively. `UserController` binds `User $user`, while many other controllers still accept raw `$id` values and call `find()` or `findOrFail()` manually.
- Type Livewire component helpers and internal methods more aggressively than controllers, as in `app/Livewire/WorkflowDesigner.php:133-156`.

**Return Values:**
- Return Blade views from page actions, JSON from AJAX/data mutation endpoints, and let Laravel handle validation exceptions automatically where `$request->validate()` or `FormRequest` is used.
- Use associative arrays with `status` and `message` keys for JSON mutation responses, as in `app/Http/Controllers/Backend/UserController.php:84-88` and `app/Http/Controllers/Backend/UnitsController.php:88-99`.

## Module Design

**Exports:**
- Use one class per PHP file and let Composer PSR-4 autoload it from `app/` or `tests/`, as configured in `composer.json:33-47`.
- Keep helpers as autoloaded global files only where legacy behavior already depends on them, such as `app/Helpers/Helper.php` and `app/Helpers/HelpTheme.php` from `composer.json:39-42`.

**Barrel Files:**
- Not used. No PHP or JavaScript barrel export pattern is present.

---

*Convention analysis: 2026-05-12*
