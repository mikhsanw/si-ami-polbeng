# Testing Patterns

**Analysis Date:** 2026-05-12

## Test Framework

**Runner:**
- Pest `^2.34` with `pestphp/pest-plugin-laravel` from `composer.json:21-31`
- Config: `phpunit.xml`

**Assertion Library:**
- Pest expectations on top of PHPUnit assertions, shown in `tests/Unit/ExampleTest.php:3-4` and `tests/Feature/ExampleTest.php:3-6`

**Run Commands:**
```bash
./vendor/bin/pest          # Run the Pest suite directly
php artisan test           # Run tests through Laravel's test command
./vendor/bin/phpunit       # Run PHPUnit using phpunit.xml
```

## Test File Organization

**Location:**
- Tests live in top-level `tests/` with the standard Laravel split between `tests/Feature` and `tests/Unit`, configured in `phpunit.xml:7-13`.
- The committed suite is minimal: `tests/Pest.php`, `tests/TestCase.php`, `tests/Feature/ExampleTest.php`, and `tests/Unit/ExampleTest.php`.

**Naming:**
- Use `*Test.php` filenames, such as `ExampleTest.php`.
- Within the file, use Pest’s closure syntax with natural-language descriptions, such as `it('returns a successful response', ...)` and `test('that true is true', ...)`.

**Structure:**
```text
tests/
├── Feature/
│   └── ExampleTest.php
├── Unit/
│   └── ExampleTest.php
├── Pest.php
└── TestCase.php
```

## Test Structure

**Suite Organization:**
```php
uses(
    Tests\TestCase::class,
    // Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Feature');

it('returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
```

This pattern comes from `tests/Pest.php:14-17` and `tests/Feature/ExampleTest.php:3-6`.

**Patterns:**
- Register the Laravel base test case globally for feature tests in `tests/Pest.php:14-17`.
- Keep tests closure-based instead of class-based PHPUnit test cases.
- Use plain request/response assertions for feature tests, shown by `$this->get('/')` and `assertStatus(200)` in `tests/Feature/ExampleTest.php:3-6`.
- Use `expect(...)` assertions for unit tests, shown in `tests/Unit/ExampleTest.php:3-4`.

## Mocking

**Framework:** Mockery is installed in `composer.json:27`, but no active mocking examples are present in committed tests.

**Patterns:**
```php
test('that true is true', function () {
    expect(true)->toBeTrue();
});
```

This is the only current unit-test assertion style in `tests/Unit/ExampleTest.php:3-4`.

**What to Mock:**
- Not established by current tests. No project-specific mocking helper or facade-mocking pattern is committed.

**What NOT to Mock:**
- Not established by current tests.

## Fixtures and Factories

**Test Data:**
```php
expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});
```

The only shared test customization currently lives in `tests/Pest.php:30-32`.

**Location:**
- The app has a factory namespace configured in `composer.json:33-38` and a `database/factories/UserFactory.php` file, but no committed test currently uses factories.
- No fixture directories are present under `tests/`.

## Coverage

**Requirements:** None enforced in repo config.

**View Coverage:**
```bash
php artisan test --coverage
./vendor/bin/pest --coverage
```

No coverage threshold or reporting configuration is checked into `phpunit.xml`.

## Test Types

**Unit Tests:**
- Use Pest closure tests under `tests/Unit`.
- Current coverage is placeholder-only. `tests/Unit/ExampleTest.php` asserts a boolean and does not exercise application code.

**Integration Tests:**
- Feature tests are positioned as Laravel HTTP integration tests under `tests/Feature`.
- Current coverage is placeholder-only. `tests/Feature/ExampleTest.php` hits `/` and asserts status `200`.

**E2E Tests:**
- Not used. No browser automation or dedicated E2E framework config is present.

## Common Patterns

**Async Testing:**
```php
// Not detected in committed tests.
```

Queue, job, event, and Livewire behavior in files such as `app/Jobs/BackupDatabaseToGoogle.php` and `app/Livewire/WorkflowDesigner.php` currently have no matching committed tests.

**Error Testing:**
```php
// Not detected in committed tests.
```

There are no assertions for validation failures, `403` aborts, exception branches, or JSON error payloads despite those patterns existing in `app/Http/Controllers/Backend/UnitsController.php:73-99` and `app/Http/Middleware/CheckRoutePermission.php:47-51`.

---

*Testing analysis: 2026-05-12*
