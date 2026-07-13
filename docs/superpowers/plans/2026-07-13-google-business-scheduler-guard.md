# Google Business Scheduler Guard Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Prevent record-level Google Business scheduled-post failures, especially simulated demo records, from producing Laravel Scheduler exit code 1 while preserving failure for real system errors.

**Architecture:** Keep candidate selection and publishing in the existing command. Add a small per-record eligibility/preflight boundary, best-effort diagnostic logging, and success exit semantics for handled record failures; let query and required persistence exceptions escape as system failures.

**Tech Stack:** PHP 8.3, Laravel 13, Eloquent, Artisan, Pest 4, Mockery.

## Global Constraints

- Modify only the Google Business scheduled-post command and its focused test.
- Do not modify `APIPartnerFizaHUB`.
- Do not add a migration or disable/change the scheduler.
- Simulated connections must be skipped without changing their posts.
- Connected invalid records must be marked failed, logged, and must not block later posts.

---

### Task 1: Add scheduler regression tests

**Files:**
- Create: `tests/Feature/AppGoogleBusiness/PublishScheduledGoogleBusinessPostsCommandTest.php`

**Interfaces:**
- Consumes: `google-business:publish-scheduled-posts`, `GoogleBusinessClient::publishPost(GoogleBusinessPost): array`, and the current `location.connection` relationships.
- Produces: regression coverage for exit codes, state transitions, client isolation, and structured warnings.

- [ ] **Step 1: Create minimal test tables and model factories**

Create test-local helpers that build `users`, `lb_google_business_connections`, `lb_google_business_locations`, `lb_google_business_posts`, and `lb_google_business_post_logs` with only columns used by the command and models. Create helper functions for a connection, managed location, and due scheduled post.

- [ ] **Step 2: Write the failing behavior tests**

Add tests asserting:

```php
$this->artisan('google-business:publish-scheduled-posts')->assertSuccessful();
```

for an empty queue; a simulated connection whose post stays `scheduled`; a connected connection missing its access token whose post becomes `failed` and emits the required warning; and two connected posts where a client exception on the first does not block publication of the second. Add a system-failure test that removes a required relationship table before command execution and asserts failure.

- [ ] **Step 3: Run the focused test and verify RED**

Run:

```powershell
php artisan test tests/Feature/AppGoogleBusiness/PublishScheduledGoogleBusinessPostsCommandTest.php
```

Expected: simulated and record-failure exit-code assertions fail against the current command.

### Task 2: Implement the minimal command guard

**Files:**
- Modify: `modules/AppGoogleBusiness/Console/PublishScheduledGoogleBusinessPostsCommand.php`
- Test: `tests/Feature/AppGoogleBusiness/PublishScheduledGoogleBusinessPostsCommandTest.php`

**Interfaces:**
- Consumes: `GoogleBusinessPost::$location`, `GoogleBusinessLocation::$connection`, `GoogleBusinessClient::publishPost()`, and `GoogleBusinessClient::postPayload()`.
- Produces: private command helpers for connection eligibility, prerequisite reason resolution, safe payload creation, safe audit-log recording, and safe structured warnings.

- [ ] **Step 1: Exclude non-connected connections before the batch limit**

Add a nested `whereHas('location.connection')` status check before ordering and limiting candidates, then retain an in-loop status guard for race-condition protection. A non-connected post remains unchanged and never calls Google.

- [ ] **Step 2: Validate connected prerequisites**

Resolve one explicit reason when location, connection, access token, Google account ID, or Google location ID is blank. Throw a record-level `RuntimeException` with that reason inside the existing per-record try block so the normal failed-state path handles it.

- [ ] **Step 3: Make record diagnostics best effort**

Wrap `postPayload()` and `GoogleBusinessPostLog::record()` independently. On diagnostic failure, preserve the original publish reason and emit a safe warning rather than rethrowing. Persist `status=failed` and `error_message` before best-effort diagnostics so a failed required write still escapes as a system failure.

- [ ] **Step 4: Emit structured warning context**

Call `Log::warning('Google Business scheduled post failed.', [...])` with `post_id`, `user_id`, `team_id`, `business_id`, `location_id`, `google_location_id`, `connection_id`, and `reason`. Protect the logger call so logging infrastructure cannot convert a handled record failure into command failure.

- [ ] **Step 5: Return success for handled record failures**

Replace the final failed-count ternary with `return self::SUCCESS;`. Do not add an outer catch around candidate queries or required failed-state persistence; those exceptions must continue to produce Artisan failure.

- [ ] **Step 6: Run the focused test and verify GREEN**

Run:

```powershell
php artisan test tests/Feature/AppGoogleBusiness/PublishScheduledGoogleBusinessPostsCommandTest.php
```

Expected: all focused tests pass.

### Task 3: Verify regression scope and command behavior

**Files:**
- Verify: `modules/AppGoogleBusiness/Console/PublishScheduledGoogleBusinessPostsCommand.php`
- Verify: `tests/Feature/AppGoogleBusiness/PublishScheduledGoogleBusinessPostsCommandTest.php`

**Interfaces:**
- Consumes: completed implementation and repository test/lint scripts.
- Produces: fresh evidence for the final report.

- [ ] **Step 1: Run formatting check**

Run:

```powershell
composer lint:check
```

Expected: no formatting errors in changed PHP files.

- [ ] **Step 2: Run the complete test suite**

Run:

```powershell
php artisan test
```

Expected: zero failed tests.

- [ ] **Step 3: Run the command directly**

Run:

```powershell
php artisan google-business:publish-scheduled-posts -vvv
```

Expected for the local empty database: `Scheduled Google posts processed. Published: 0. Failed: 0.` and exit code 0.

- [ ] **Step 4: Review scope**

Run `git diff --name-only` and confirm there are no migrations, scheduler changes, or files under `APIPartnerFizaHUB`.
