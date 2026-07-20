# FizaHUB UI API Contract Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Use superpowers:subagent-driven-development only when the user explicitly requests delegated execution. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the current FizaHUB partner API with the approved breaking v1 cutover of exactly 22 UI-driven endpoints, with atomic duplicate-safe onboarding, complete text-only support lifecycle, CRM login links, and synchronized tests/documentation/Postman.

**Architecture:** Keep the module as an adapter over existing MLHUB user, team, business, campaign, package, and support stores. Add focused identity, catalog, and campaign-approval services; aggregate screen data in the existing profile/dashboard/support services; enforce request tracing and idempotency in shared middleware; store marketing preferences in integration metadata and campaign approval evidence in campaign settings/audit logs. Add only one forward-only migration for encrypted, integration-scoped CRM login-link replay.

**Tech Stack:** PHP 8.3, Laravel 13, Pest 4, Eloquent, SQLite test schemas, Blade documentation pages, Postman Collection v2.1, Laravel Pint.

## Global Constraints

- Expose exactly 22 public routes under `/api/v1/partners/fizahub`; do not retain public aliases for replaced v1 routes.
- Remove only support attachment upload; preserve `SupportTicketBridge`, `support_tickets`, text messages, list, detail, close, and reopen.
- Do not add a destructive migration. Add one forward-only migration for encrypted CRM idempotency replay; integration metadata, campaign settings, API logs, audit logs, and support contexts represent every other new field.
- Every response has `success`, `data`, `meta.request_id`, and `error`.
- Every state-changing request except the read-only SSO verification POST requires `Idempotency-Key`.
- Same key plus same normalized payload replays the stored outcome; same key plus different normalized payload returns HTTP 409 `idempotency_conflict`.
- Every production behavior change must have a failing test observed before the production edit.
- Public onboarding statuses are `awaiting_consultant`, `needs_review`, `in_consultation`, `configuring`, `ready`, `completed`, and `cancelled`.
- Public activation statuses are `inactive`, `onboarding`, `active`, and `suspended`.
- Cursor pagination always returns `next_cursor`, `has_more`, and `per_page`.
- Date ranges default to `30d`; custom ranges require `from` and `to`, enforce `from <= to`, and allow at most 366 inclusive days.
- Preserve unrelated user changes and the untracked `codeok.zip` file.

---

## File Structure

### New production files

- `modules/APIPartnerFizaHUB/Services/PartnerIdentityService.php` — email-derived username generation and deterministic collision handling.
- `modules/APIPartnerFizaHUB/Services/MarketingCatalogService.php` — marketing goals, industries, and package catalog aggregate.
- `modules/APIPartnerFizaHUB/Services/CampaignApprovalService.php` — tenant-scoped campaign approval and changes-requested support handoff.
- `modules/APIPartnerFizaHUB/Http/Requests/UpdateMarketingPreferencesRequest.php` — validates one-to-three goals and requested package.
- `modules/APIPartnerFizaHUB/Http/Requests/CampaignApprovalRequest.php` — validates approval decisions.
- `modules/APIPartnerFizaHUB/Http/Requests/ListCampaignsRequest.php` — cursor, status, query, and date-range validation.
- `modules/APIPartnerFizaHUB/Http/Requests/ListSupportTicketsRequest.php` — support search/status/cursor validation.
- `modules/APIPartnerFizaHUB/Database/Migrations/2026_07_20_000000_add_crm_login_idempotency_to_partner_one_time_logins.php` — adds an integration-scoped idempotency key and encrypted token storage without deleting or rewriting existing data.

### Existing production files to modify

- `modules/APIPartnerFizaHUB/Routes/api.php`
- `modules/APIPartnerFizaHUB/Support/PartnerApiResponse.php`
- `modules/APIPartnerFizaHUB/Support/PartnerExceptionRenderer.php`
- `modules/APIPartnerFizaHUB/Support/OnboardingStatusMachine.php`
- `modules/APIPartnerFizaHUB/Http/Middleware/HandlePartnerRequest.php`
- `modules/APIPartnerFizaHUB/Http/Requests/UpsertOnboardingRequest.php`
- `modules/APIPartnerFizaHUB/Http/Requests/DashboardRequest.php`
- `modules/APIPartnerFizaHUB/Http/Requests/CreateSupportTicketRequest.php`
- `modules/APIPartnerFizaHUB/Http/Controllers/OnboardingController.php`
- `modules/APIPartnerFizaHUB/Http/Controllers/BusinessProfileController.php`
- `modules/APIPartnerFizaHUB/Http/Controllers/PackageCatalogController.php`
- `modules/APIPartnerFizaHUB/Http/Controllers/DashboardController.php`
- `modules/APIPartnerFizaHUB/Http/Controllers/SupportTicketController.php`
- `modules/APIPartnerFizaHUB/Http/Controllers/SupportMessageController.php`
- `modules/APIPartnerFizaHUB/Http/Controllers/OneTimeLoginController.php`
- `modules/APIPartnerFizaHUB/Services/OnboardingService.php`
- `modules/APIPartnerFizaHUB/Services/PartnerMappingService.php`
- `modules/APIPartnerFizaHUB/Services/IntegrationProfileService.php`
- `modules/APIPartnerFizaHUB/Services/PackageAssignmentService.php`
- `modules/APIPartnerFizaHUB/Services/PackageService.php`
- `modules/APIPartnerFizaHUB/Services/DashboardService.php`
- `modules/APIPartnerFizaHUB/Services/SupportTicketBridge.php`
- `modules/APIPartnerFizaHUB/Services/OneTimeLoginService.php`
- `modules/APIPartnerFizaHUB/Models/PartnerOnboardingRequest.php`
- `modules/APIPartnerFizaHUB/Models/PartnerOneTimeLogin.php`
- `modules/APIPartnerFizaHUB/Console/Commands/FizaHubDoctorCommand.php`

### Tests to create or reshape

- `tests/Feature/APIPartnerFizaHUB/UiApiContractTest.php`
- `tests/Feature/APIPartnerFizaHUB/IdempotencyContractTest.php`
- `tests/Feature/APIPartnerFizaHUB/OnboardingUiContractTest.php`
- `tests/Feature/APIPartnerFizaHUB/MarketingBootstrapApiTest.php`
- `tests/Feature/APIPartnerFizaHUB/GrowthUiContractTest.php`
- `tests/Feature/APIPartnerFizaHUB/SupportUiContractTest.php`
- `tests/Feature/APIPartnerFizaHUB/CrmLoginLinkApiTest.php`
- `tests/Feature/APIPartnerFizaHUB/FullUiHappyPathTest.php`
- Rename `tests/Feature/APIPartnerFizaHUB/SupportLifecycleAttachmentTest.php` to `SupportLifecycleTest.php` and replace attachment behavior tests with route-absence assertions.
- Update existing contract tests whose assertions describe the removed 24-route API.

### Documentation and artifacts to modify

- `modules/APIPartnerFizaHUB/README.md`
- `modules/APIPartnerFizaHUB/docs/ENDPOINT_MATRIX.md`
- `modules/APIPartnerFizaHUB/docs/FizaHUB-Partner-API.postman_collection.json`
- `modules/APIPartnerFizaHUB/Resources/views/api-fizahub.blade.php`
- `modules/APIPartnerFizaHUB/Resources/views/api-fizahub-help-test.blade.php`

---

### Task 1: Route, Envelope, Idempotency, and Documentation Contract

**Files:**
- Create: `tests/Feature/APIPartnerFizaHUB/UiApiContractTest.php`
- Create: `tests/Feature/APIPartnerFizaHUB/IdempotencyContractTest.php`
- Modify: `tests/Feature/APIPartnerFizaHUB/ModuleRoutingTest.php`
- Modify: `tests/Feature/APIPartnerFizaHUB/DocumentationContractTest.php`
- Modify: `modules/APIPartnerFizaHUB/Routes/api.php`
- Modify: `modules/APIPartnerFizaHUB/Http/Middleware/HandlePartnerRequest.php`
- Modify: `modules/APIPartnerFizaHUB/Support/PartnerApiResponse.php`
- Modify: `modules/APIPartnerFizaHUB/Support/PartnerExceptionRenderer.php`
- Modify: `modules/APIPartnerFizaHUB/docs/ENDPOINT_MATRIX.md`

**Interfaces:**
- Produces: exact named-route list of 22 routes.
- Produces: `HandlePartnerRequest::requestHash(Request): string` based on recursively key-sorted JSON and relevant query parameters.
- Produces: response envelope `{success,data,meta:{request_id},error}` for every partner response.
- Consumes: existing `partner_api_logs` uniqueness on partner, method, concrete endpoint, and idempotency key.

- [x] **Step 1: Write the failing exact-route contract test**

```php
test('public FizaHUB API is the exact approved 22 route cutover', function (): void {
    $expected = [
        ['GET', 'api/v1/partners/fizahub/health'],
        ['POST', 'api/v1/partners/fizahub/partner/sso/verify'],
        ['GET', 'api/v1/partners/fizahub/businesses/{external_business_id}/marketing-status'],
        ['GET', 'api/v1/partners/fizahub/marketing-catalog'],
        ['POST', 'api/v1/partners/fizahub/onboarding-requests'],
        ['GET', 'api/v1/partners/fizahub/onboarding-requests/{request_id}'],
        ['PATCH', 'api/v1/partners/fizahub/businesses/{external_business_id}/profile'],
        ['PATCH', 'api/v1/partners/fizahub/businesses/{external_business_id}/marketing-preferences'],
        ['GET', 'api/v1/partners/fizahub/businesses/{external_business_id}/dashboard'],
        ['GET', 'api/v1/partners/fizahub/businesses/{external_business_id}/growth-insights'],
        ['GET', 'api/v1/partners/fizahub/businesses/{external_business_id}/campaigns'],
        ['GET', 'api/v1/partners/fizahub/businesses/{external_business_id}/campaigns/{campaign_id}'],
        ['POST', 'api/v1/partners/fizahub/businesses/{external_business_id}/campaigns/{campaign_id}/approval'],
        ['GET', 'api/v1/partners/fizahub/businesses/{external_business_id}/package'],
        ['GET', 'api/v1/partners/fizahub/businesses/{external_business_id}/support-presets'],
        ['GET', 'api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets'],
        ['POST', 'api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets'],
        ['GET', 'api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets/{ticket_id}'],
        ['POST', 'api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets/{ticket_id}/messages'],
        ['POST', 'api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets/{ticket_id}/close'],
        ['POST', 'api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets/{ticket_id}/reopen'],
        ['POST', 'api/v1/partners/fizahub/businesses/{external_business_id}/crm-login-links'],
    ];

    $actual = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($route) => str_starts_with($route->uri(), 'api/v1/partners/fizahub'))
        ->map(fn ($route) => [collect($route->methods())->first(fn ($method) => $method !== 'HEAD'), $route->uri()])
        ->values()
        ->all();

    expect($actual)->toEqualCanonicalizing($expected)
        ->and($actual)->toHaveCount(22)
        ->and(collect($actual)->pluck(1))->not->toContain(
            'api/v1/partners/fizahub/support-tickets/{ticket_id}/attachments'
        );
});
```

- [x] **Step 2: Run the route test and verify RED**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/UiApiContractTest.php --filter="exact approved 22 route"
```

Expected: FAIL because the current route list contains 24 routes, includes attachment/confirm/cancel/legacy names, and lacks marketing status, preferences, growth insights, approval, presets, nested support, and CRM link routes.

- [x] **Step 3: Replace the route group with the exact cutover**

Use these route names so Doctor and tests share one stable list:

```php
Route::get('health', HealthController::class)->name('health');
Route::post('partner/sso/verify', [SsoController::class, 'verify'])->name('sso.verify');
Route::get('businesses/{external_business_id}/marketing-status', [BusinessProfileController::class, 'status'])->name('businesses.marketing-status');
Route::get('marketing-catalog', [PackageCatalogController::class, 'index'])->name('marketing-catalog');
Route::post('onboarding-requests', [OnboardingController::class, 'store'])->name('onboarding.store');
Route::get('onboarding-requests/{request_id}', [OnboardingController::class, 'show'])->name('onboarding.show');
Route::patch('businesses/{external_business_id}/profile', [BusinessProfileController::class, 'update'])->name('businesses.profile.update');
Route::patch('businesses/{external_business_id}/marketing-preferences', [BusinessProfileController::class, 'updatePreferences'])->name('businesses.marketing-preferences.update');
Route::get('businesses/{external_business_id}/dashboard', [DashboardController::class, 'show'])->name('businesses.dashboard');
Route::get('businesses/{external_business_id}/growth-insights', [DashboardController::class, 'growthInsights'])->name('businesses.growth-insights');
Route::get('businesses/{external_business_id}/campaigns', [DashboardController::class, 'campaigns'])->name('businesses.campaigns.index');
Route::get('businesses/{external_business_id}/campaigns/{campaign_id}', [DashboardController::class, 'campaignShow'])->name('businesses.campaigns.show');
Route::post('businesses/{external_business_id}/campaigns/{campaign_id}/approval', [DashboardController::class, 'approveCampaign'])->name('businesses.campaigns.approval');
Route::get('businesses/{external_business_id}/package', [PackageController::class, 'show'])->name('businesses.package');
Route::get('businesses/{external_business_id}/support-presets', [SupportTicketController::class, 'presets'])->name('businesses.support-presets');
Route::get('businesses/{external_business_id}/support-tickets', [SupportTicketController::class, 'index'])->name('businesses.support-tickets.index');
Route::post('businesses/{external_business_id}/support-tickets', [SupportTicketController::class, 'store'])->name('businesses.support-tickets.store');
Route::get('businesses/{external_business_id}/support-tickets/{ticket_id}', [SupportTicketController::class, 'show'])->name('businesses.support-tickets.show');
Route::post('businesses/{external_business_id}/support-tickets/{ticket_id}/messages', [SupportMessageController::class, 'store'])->name('businesses.support-tickets.messages.store');
Route::post('businesses/{external_business_id}/support-tickets/{ticket_id}/close', [SupportTicketController::class, 'close'])->name('businesses.support-tickets.close');
Route::post('businesses/{external_business_id}/support-tickets/{ticket_id}/reopen', [SupportTicketController::class, 'reopen'])->name('businesses.support-tickets.reopen');
Route::post('businesses/{external_business_id}/crm-login-links', [OneTimeLoginController::class, 'store'])->name('businesses.crm-login-links.store');
```

Remove the attachment controller import and do not register compatibility aliases.

- [x] **Step 4: Write failing envelope and idempotency tests**

```php
test('all partner errors preserve the response envelope and effective request id', function (): void {
    $requestId = (string) str()->uuid();

    $this->getJson('/api/v1/partners/fizahub/businesses/missing/marketing-status', partnerHeaders([
        'X-Request-Id' => $requestId,
    ]))->assertNotFound()
        ->assertJsonPath('success', false)
        ->assertJsonPath('data', null)
        ->assertJsonPath('meta.request_id', $requestId)
        ->assertJsonPath('error.code', 'integration_not_found');
});

test('unmatched partner URLs still use the canonical error envelope', function (): void {
    $requestId = (string) str()->uuid();

    $this->postJson('/api/v1/partners/fizahub/removed-route', [], partnerHeaders([
        'X-Request-Id' => $requestId,
    ]))->assertNotFound()
        ->assertJsonPath('success', false)
        ->assertJsonPath('data', null)
        ->assertJsonPath('meta.request_id', $requestId)
        ->assertJsonPath('error.code', 'route_not_found');
});

test('same idempotency key with reordered equivalent JSON replays but different data conflicts', function (): void {
    $key = 'idem-contract-001';
    $first = $this->postJson($writeUrl, ['subject' => 'A', 'message' => 'B'], writeHeaders($key));
    $replay = $this->postJson($writeUrl, ['message' => 'B', 'subject' => 'A'], writeHeaders($key));
    $conflict = $this->postJson($writeUrl, ['subject' => 'A', 'message' => 'Changed'], writeHeaders($key));

    expect($replay->status())->toBe($first->status())
        ->and($replay->json('data'))->toBe($first->json('data'));
    $conflict->assertStatus(409)->assertJsonPath('error.code', 'idempotency_conflict');
});
```

The test file defines `partnerHeaders()` and `writeHeaders()` locally and boots `FizaHubTestHelpers.php` so it is independently runnable.

- [x] **Step 5: Run the idempotency tests and verify RED**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/IdempotencyContractTest.php
```

Expected: FAIL because missing idempotency keys are not rejected globally and the current request hash is raw-body-order dependent.

- [x] **Step 6: Canonicalize payloads and require keys for write routes**

Add these methods to `HandlePartnerRequest` and call them before `beginIdempotentRequest`:

```php
private function requiresIdempotency(Request $request): bool
{
    if ($request->route()?->getName() === 'partner.fizahub.sso.verify') {
        return false;
    }

    return in_array(strtoupper($request->method()), ['POST', 'PUT', 'PATCH', 'DELETE'], true);
}

private function requestHash(Request $request): string
{
    $payload = $request->isJson() ? $request->json()->all() : $request->all();
    $canonical = [
        'query' => $this->canonicalize($request->query()),
        'body' => $this->canonicalize($payload),
    ];

    return hash('sha256', json_encode($canonical, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
}

private function canonicalize(mixed $value): mixed
{
    if (! is_array($value)) {
        return $value;
    }

    if (array_is_list($value)) {
        return array_map(fn (mixed $item): mixed => $this->canonicalize($item), $value);
    }

    ksort($value);

    return array_map(fn (mixed $item): mixed => $this->canonicalize($item), $value);
}
```

When `requiresIdempotency()` is true and the header is empty or exceeds 128 characters, return HTTP 422 `validation_failed`. On replay, replace `meta.request_id` in the stored JSON with the current effective request ID before returning it. Register a partner-prefix not-found renderer so an unmatched URL under `/api/v1/partners/fizahub` also returns the canonical envelope and effective request ID.

For `partner.fizahub.businesses.crm-login-links.store`, middleware still verifies the key and normalized hash but marks the request as an idempotent replay and continues to the controller instead of replaying the redacted API-log response. `OneTimeLoginService` performs the secure link replay from encrypted token storage in Task 6.

- [x] **Step 7: Update the initial endpoint matrix to exactly 22 routes**

Replace the old 24-route table in `ENDPOINT_MATRIX.md` with the approved method/path/name/controller mapping. Explicitly list removed aliases and state that the attachment route is absent, not 501-compatible.

- [x] **Step 8: Run route, envelope, and idempotency tests GREEN**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/UiApiContractTest.php tests/Feature/APIPartnerFizaHUB/IdempotencyContractTest.php tests/Feature/APIPartnerFizaHUB/ModuleRoutingTest.php
```

Expected: PASS with 22 route tuples and zero attachment/legacy aliases.

- [x] **Step 9: Commit Task 1**

```powershell
git add modules/APIPartnerFizaHUB/Routes/api.php modules/APIPartnerFizaHUB/Http/Middleware/HandlePartnerRequest.php modules/APIPartnerFizaHUB/Support tests/Feature/APIPartnerFizaHUB modules/APIPartnerFizaHUB/docs/ENDPOINT_MATRIX.md
git commit -m "feat: cut over FizaHUB API to approved 22 routes"
```

---

### Task 2: Atomic Onboarding Identity, Duplicate Handling, and Onboarding Ticket

**Files:**
- Create: `modules/APIPartnerFizaHUB/Services/PartnerIdentityService.php`
- Create: `tests/Feature/APIPartnerFizaHUB/OnboardingUiContractTest.php`
- Modify: `modules/APIPartnerFizaHUB/Http/Requests/UpsertOnboardingRequest.php`
- Modify: `modules/APIPartnerFizaHUB/Http/Controllers/OnboardingController.php`
- Modify: `modules/APIPartnerFizaHUB/Services/OnboardingService.php`
- Modify: `modules/APIPartnerFizaHUB/Services/PartnerMappingService.php`
- Modify: `modules/APIPartnerFizaHUB/Services/SupportTicketBridge.php`
- Modify: `modules/APIPartnerFizaHUB/Support/OnboardingStatusMachine.php`
- Modify: `modules/APIPartnerFizaHUB/Models/PartnerOnboardingRequest.php`
- Modify: `tests/Feature/APIPartnerFizaHUB/FizaHubTestHelpers.php`
- Update: existing onboarding, root-cause, integration-broken, and admin-transition tests.

**Interfaces:**
- Produces: `PartnerIdentityService::usernameFromEmail(string): string`.
- Produces: `PartnerIdentityService::availableUsernameFromEmail(string): string`.
- Produces: `OnboardingService::upsert(array $payload, string $requestId): array{onboarding: PartnerOnboardingRequest, already_registered: bool, account_created: bool, business_created: bool, integration_created: bool}`.
- Consumes: `SupportTicketBridge::createOnboardingReviewTicket(...)` with valid mapped user/team IDs.

- [x] **Step 1: Write failing username tests**

```php
it('derives lowercase alphanumeric usernames from login email', function (string $email, string $expected): void {
    expect(app(PartnerIdentityService::class)->usernameFromEmail($email))->toBe($expected);
})->with([
    ['van-khoa.lqd123@gmail.com', 'vankhoalqd123'],
    ['doan.van.khoa@gmail.com', 'doanvankhoa'],
    ['đoàn-văn-khoa@example.com', 'doanvankhoa'],
]);

it('uses a deterministic suffix when another email owns the base username', function (): void {
    User::query()->create(userRow('Existing', 'doanvankhoa', 'other@example.com'));
    $username = app(PartnerIdentityService::class)->availableUsernameFromEmail('doan.van.khoa@gmail.com');

    expect($username)->toMatch('/^doanvankhoa[a-f0-9]+$/')
        ->and($username)->toBe(app(PartnerIdentityService::class)->availableUsernameFromEmail('doan.van.khoa@gmail.com'));
});
```

- [x] **Step 2: Run username tests and verify RED**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/OnboardingUiContractTest.php --filter="username"
```

Expected: FAIL because `PartnerIdentityService` does not exist and current usernames are external-business-derived.

- [x] **Step 3: Implement the identity service**

```php
final class PartnerIdentityService
{
    private const MAX_LENGTH = 255; // Matches users.username in the canonical schema.

    public function usernameFromEmail(string $email): string
    {
        $email = strtolower(trim($email));
        $local = Str::before($email, '@');
        $base = preg_replace('/[^a-z0-9]/', '', strtolower(Str::ascii($local))) ?: '';

        if ($base === '') {
            $base = 'user'.substr(hash('sha256', $email), 0, 8);
        }

        return substr($base, 0, self::MAX_LENGTH);
    }

    public function availableUsernameFromEmail(string $email): string
    {
        $base = $this->usernameFromEmail($email);
        $owner = User::query()->where('username', $base)->first();

        if (! $owner || hash_equals(strtolower((string) $owner->email), strtolower(trim($email)))) {
            return $base;
        }

        $suffix = substr(hash('sha256', strtolower(trim($email))), 0, 10);

        return substr($base, 0, self::MAX_LENGTH - strlen($suffix)).$suffix;
    }
}
```

Remove onboarding use of `deterministicUsername`, `availableUsername`, and `provisionalEmail`. Keep deprecated mapping helpers only if unrelated internal callers still require them; no onboarding path may call them.

- [x] **Step 4: Write failing canonical payload, status, duplicate, rollback, and count tests**

The canonical helper in the test file is:

```php
function approvedOnboardingPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'external_business_id' => 'fiza-business-001',
        'external_user_id' => 'fiza-user-001',
        'marketing_goal_codes' => ['qr_checkin', 'customer_retention'],
        'requested_package_code' => 'base',
        'owner' => ['name' => 'Đoàn Văn Khoa', 'email' => 'van-khoa.lqd123@gmail.com'],
        'business' => [
            'name' => 'Fiza Store',
            'industry' => 'restaurant_food',
            'phone' => '0901234888',
            'email' => 'contact@fizastore.vn',
            'website' => 'https://fizastore.vn',
            'address' => '888 Lê Duẩn, Đà Nẵng',
        ],
    ], $overrides);
}
```

Add tests asserting:

```php
$first = $this->postJson($url, approvedOnboardingPayload(), writeHeaders('onboard-first'));
$first->assertCreated()
    ->assertJsonPath('data.username', 'vankhoalqd123')
    ->assertJsonPath('data.already_registered', false)
    ->assertJsonPath('data.account_created', true)
    ->assertJsonPath('data.business_created', true)
    ->assertJsonPath('data.integration_created', true);

$counts = fn (): array => [
    User::query()->count(), Team::query()->count(), LocalBusiness::query()->count(),
    PartnerIntegration::query()->count(),
    PartnerPackageAssignment::query()->where('status', 'active')->count(),
    PartnerOnboardingRequest::query()->count(),
    PartnerSupportTicketContext::query()->where('request_code', 'fizahub_onboarding')->count(),
];

$beforeSecond = $counts();
$second = $this->postJson($url, approvedOnboardingPayload(), writeHeaders('onboard-second'));
$second->assertOk()->assertJsonPath('data.already_registered', true);
expect($counts())->toBe($beforeSecond)
    ->and($second->json('data.support_ticket_id'))->toBe($first->json('data.support_ticket_id'));
```

Also assert HTTP 202 for a configured review condition, both typed HTTP 409 cases, HTTP 422 validation, HTTP 503 dependency readiness, correct business persistence, no provisional user, timeline shape, and no rows created on conflicts.

Add a concurrency regression test that launches two first-onboarding attempts for the same normalized partner/business identity. The test must prove that the database uniqueness constraints are the final race guard: one request provisions the graph, the other reloads and returns the existing graph, and final counts remain exactly one user, team, business, integration, active assignment, onboarding request, and onboarding ticket.

- [x] **Step 5: Run onboarding tests and verify RED**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/OnboardingUiContractTest.php
```

Expected: FAIL on required owner phone/verification fields, provisional email behavior, request creation before duplicate checks, missing `already_registered`, and old timeline/status serialization.

- [x] **Step 6: Update onboarding validation**

Use these core rules:

```php
'external_business_id' => ['required', 'string', 'max:128'],
'external_user_id' => ['nullable', 'string', 'max:128'],
'marketing_goal_codes' => ['required', 'array', 'min:1', 'max:3'],
'marketing_goal_codes.*' => ['required', 'string', Rule::in($goalCodes)],
'requested_package_code' => ['required_without:package_code', 'string', Rule::in($packageCodes)],
'package_code' => ['nullable', 'string', Rule::in($packageCodes)],
'owner.name' => ['required', 'string', 'max:255'],
'owner.email' => ['required', 'email:rfc', 'max:255'],
'owner.phone' => ['nullable', 'string', 'max:40'],
'business.name' => ['required', 'string', 'max:255'],
'business.industry' => ['required', 'string', 'max:80'],
'business.phone' => ['required', 'string', 'max:40'],
'business.email' => ['required', 'email:rfc', 'max:255'],
'business.website' => ['nullable', 'url', 'max:500'],
'business.address' => ['required', 'string', 'max:1000'],
'metadata' => ['nullable', 'array'],
```

- [x] **Step 7: Refactor onboarding transaction order**

Inside one `DB::transaction`:

```php
$integration = PartnerIntegration::query()
    ->where('partner_code', $partnerCode)
    ->where('external_business_id', $externalBusinessId)
    ->lockForUpdate()
    ->first();

if ($integration) {
    $mappedUser = User::query()->lockForUpdate()->find($integration->mlhub_user_id);
    throw_unless($mappedUser, PartnerApiException::make('integration_broken', __('Liên kết MLHUB không hợp lệ.'), 409));

    if ($mappedUser->email !== $normalizedOwnerEmail) {
        throw PartnerApiException::make(
            'onboarding_email_mismatch',
            __('Email đăng ký không khớp với tài khoản đã liên kết trước đó.'),
            409
        );
    }

    return $this->reuseExistingRegistration($integration, $payload);
}

if (User::query()->where('email', $normalizedOwnerEmail)->lockForUpdate()->exists()) {
    throw PartnerApiException::make(
        'email_already_registered',
        __('Địa chỉ email này đã được đăng ký trên MLHUB.'),
        409,
        ['next_action' => 'use_existing_account_or_contact_support']
    );
}

return $this->provisionNewRegistration($payload, $requestId);
```

`reuseExistingRegistration` updates only permitted profile fields, reuses the latest canonical onboarding request and existing ticket, and returns all creation flags false. `provisionNewRegistration` creates the onboarding row only after duplicate checks and uses `PartnerIdentityService` for username selection.

`lockForUpdate()` cannot lock a row that does not exist. Keep the existing unique database constraints on `(partner_code, external_business_id)`, `users.email`, and `users.username` as authoritative race guards. If an insert loses one of these named unique-key races, roll back the provisional transaction and classify that exact constraint: reload the winning integration for the normal email-match/mismatch branch, return `email_already_registered` for a newly claimed email, or recompute the deterministic username suffix for a username-only collision. Retry at most once. Never catch a generic database exception and silently reuse unrelated rows.

- [x] **Step 8: Serialize stable status and timeline**

`OnboardingStatusMachine::timelineFor()` returns exactly five steps. Map legacy `consulting` to `in_consultation` and `needs_information` to `needs_review`. When status is `needs_review`, mark the intake step `blocked`; do not append another step.

`OnboardingController::httpStatus()` returns 201 for a new awaiting request, 202 for a new needs-review request, and 200 for an existing registration.

- [x] **Step 9: Preserve exactly one valid onboarding ticket**

Update `createOnboardingReviewTicket` and `ensureOnboardingTicket` so the context always contains:

```php
[
    'ticket_type' => 'onboarding',
    'source' => 'fizahub',
    'preset_code' => 'fizahub_onboarding',
    'audit_reason' => $recreated ? 'referenced_onboarding_ticket_missing' : null,
]
```

Reuse the referenced ticket when it exists. If it was deleted, create one ticket using valid `uid`, `open_by`, and `team_id`, update `support_ticket_id`, and audit the reason.

- [x] **Step 10: Run onboarding and support-ticket regression tests GREEN**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/OnboardingUiContractTest.php tests/Feature/APIPartnerFizaHUB/OnboardingApiTest.php tests/Feature/APIPartnerFizaHUB/RootCauseInvestigationTest.php tests/Feature/APIPartnerFizaHUB/SupportTicketApiTest.php
```

Expected: PASS with unchanged counts after the second onboarding and no orphan/provisional resources.

- [x] **Step 11: Commit Task 2**

```powershell
git add modules/APIPartnerFizaHUB/Services modules/APIPartnerFizaHUB/Http/Requests/UpsertOnboardingRequest.php modules/APIPartnerFizaHUB/Http/Controllers/OnboardingController.php modules/APIPartnerFizaHUB/Support/OnboardingStatusMachine.php modules/APIPartnerFizaHUB/Models/PartnerOnboardingRequest.php tests/Feature/APIPartnerFizaHUB
git commit -m "fix: prevent duplicate FizaHUB accounts and onboarding tickets"
```

---

### Task 3: Marketing Status, Catalog, Profile, and Preferences

**Files:**
- Create: `modules/APIPartnerFizaHUB/Services/MarketingCatalogService.php`
- Create: `modules/APIPartnerFizaHUB/Http/Requests/UpdateMarketingPreferencesRequest.php`
- Create: `tests/Feature/APIPartnerFizaHUB/MarketingBootstrapApiTest.php`
- Modify: `modules/APIPartnerFizaHUB/Services/IntegrationProfileService.php`
- Modify: `modules/APIPartnerFizaHUB/Services/PackageAssignmentService.php`
- Modify: `modules/APIPartnerFizaHUB/Services/PackageService.php`
- Modify: `modules/APIPartnerFizaHUB/Http/Controllers/BusinessProfileController.php`
- Modify: `modules/APIPartnerFizaHUB/Http/Controllers/PackageCatalogController.php`
- Modify: `modules/APIPartnerFizaHUB/config/config.php`
- Update: `IntegrationStatusTest.php`, `PackageApiTest.php`, and `BusinessProfileApiTest.php`.

**Interfaces:**
- Produces: `IntegrationProfileService::marketingStatus(string): array`.
- Produces: `IntegrationProfileService::updatePreferences(string, array): array`.
- Produces: `MarketingCatalogService::catalog(?string $industryCode): array`.

- [x] **Step 1: Write failing bootstrap/catalog/preferences tests**

```php
test('unknown business marketing status returns the onboarding next action', function (): void {
    $this->getJson($base.'/businesses/missing/marketing-status', partnerHeaders())
        ->assertNotFound()
        ->assertJsonPath('error.code', 'integration_not_found')
        ->assertJsonPath('error.details.next_action', 'create_onboarding_request');
});

test('marketing catalog provides goals industries and packages in one request', function (): void {
    $this->getJson($base.'/marketing-catalog?industry=restaurant_food', partnerHeaders())
        ->assertOk()
        ->assertJsonPath('data.max_goal_selection', 3)
        ->assertJsonPath('data.default_package_code', 'free')
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('data.marketing_goals', 4)
            ->has('data.industries')
            ->has('data.packages')
            ->etc());
});

test('preferences change requested package without changing effective package', function (): void {
    $response = $this->patchJson($base.'/businesses/biz-1/marketing-preferences', [
        'marketing_goal_codes' => ['qr_checkin', 'customer_retention'],
        'requested_package_code' => 'base',
    ], writeHeaders('preferences-1'));

    $response->assertOk()
        ->assertJsonPath('data.requested_package_code', 'base')
        ->assertJsonPath('data.effective_package_code', 'free');
});
```

- [x] **Step 2: Run the new tests and verify RED**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/MarketingBootstrapApiTest.php
```

Expected: FAIL because the aggregate catalog, capabilities, activation enum, and preferences method do not exist.

- [x] **Step 3: Implement the catalog aggregate**

Configure four stable goals:

```php
'marketing_goals' => [
    'local_presence' => ['label' => 'Hiện diện', 'description' => 'Tăng hiện diện địa phương'],
    'qr_checkin' => ['label' => 'QR Check-in', 'description' => 'Thu lead ngay tại cửa hàng'],
    'voucher_return' => ['label' => 'Mã ưu đãi', 'description' => 'Kích thích khách quay lại'],
    'customer_retention' => ['label' => 'Khách hàng', 'description' => 'Lưu và chăm sóc khách cũ'],
],
```

`MarketingCatalogService` combines this config, `BusinessTypeCatalog`, and `PackageAssignmentService::catalog()`. It enriches package rows with `description`, `features`, `recommended_goal_codes`, and `industry_codes` while preserving `is_default` and `is_free`.

- [x] **Step 4: Implement marketing status**

Return:

```php
[
    'external_business_id' => $integration->external_business_id,
    'activation_status' => $this->activationStatus($integration, $onboarding),
    'onboarding_status' => $onboarding?->publicStatus(),
    'is_ready' => in_array($onboarding?->publicStatus(), ['ready', 'completed'], true),
    'effective_package_code' => $integration->package_code,
    'requested_package_code' => $onboarding?->requested_package_code,
    'onboarding_request_id' => $onboarding?->request_id,
    'support_ticket_id' => $ticketSecureId,
    'mlhub_user_id' => $integration->mlhub_user_id,
    'mlhub_workspace_id' => $integration->mlhub_workspace_id,
    'mlhub_business_id' => $integration->mlhub_business_id,
    'capabilities' => [
        'dashboard' => $isActive,
        'support' => true,
        'crm' => $isReady,
    ],
    'links' => $this->linksFor($integration, $onboarding, $ticketSecureId),
];
```

- [x] **Step 5: Implement preferences without package activation**

Validate one-to-three configured goals and a mapped package code. Store goals and requested package in `partner_integrations.metadata`, update the latest onboarding request payload/requested package, and never call `assignEffectivePackage()`.

- [x] **Step 6: Keep profile updates constrained**

Retain nested owner/business input. Add prohibited-key assertions for login email and all external/MLHUB IDs. Verify the business fields and owner name persist, while the login email remains unchanged.

- [x] **Step 7: Run bootstrap/package/profile tests GREEN**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/MarketingBootstrapApiTest.php tests/Feature/APIPartnerFizaHUB/IntegrationStatusTest.php tests/Feature/APIPartnerFizaHUB/PackageApiTest.php tests/Feature/APIPartnerFizaHUB/BusinessProfileApiTest.php
```

Expected: PASS with the new route names and response fields.

- [x] **Step 8: Commit Task 3**

```powershell
git add modules/APIPartnerFizaHUB/Services modules/APIPartnerFizaHUB/Http/Requests/UpdateMarketingPreferencesRequest.php modules/APIPartnerFizaHUB/Http/Controllers modules/APIPartnerFizaHUB/config/config.php tests/Feature/APIPartnerFizaHUB
git commit -m "feat: add FizaHUB marketing bootstrap and preferences"
```

---

### Task 4: Dashboard, Growth Insights, Campaigns, Approval, and Package

**Files:**
- Create: `modules/APIPartnerFizaHUB/Http/Requests/ListCampaignsRequest.php`
- Create: `modules/APIPartnerFizaHUB/Http/Requests/CampaignApprovalRequest.php`
- Create: `modules/APIPartnerFizaHUB/Services/CampaignApprovalService.php`
- Create: `tests/Feature/APIPartnerFizaHUB/GrowthUiContractTest.php`
- Modify: `modules/APIPartnerFizaHUB/Http/Requests/DashboardRequest.php`
- Modify: `modules/APIPartnerFizaHUB/Http/Controllers/DashboardController.php`
- Modify: `modules/APIPartnerFizaHUB/Services/DashboardService.php`
- Modify: `modules/APIPartnerFizaHUB/Services/PackageService.php`
- Modify: `modules/APIPartnerFizaHUB/Services/SupportTicketBridge.php`
- Update: dashboard, insights/campaign, package, and cross-endpoint tests.

**Interfaces:**
- Produces: `DashboardRequest::resolvedRange(): array{0: CarbonImmutable, 1: CarbonImmutable, 2: string}`.
- Produces: `DashboardService::growthInsights(PartnerIntegration, CarbonImmutable, CarbonImmutable): array`.
- Produces: `DashboardService::campaignList(PartnerIntegration, array $filters): array{items: array, pagination: array, summary: array}`.
- Produces: `CampaignApprovalService::decide(PartnerIntegration, QrCampaign, string, ?string): array`.

- [x] **Step 1: Write failing range and zero-data tests**

```php
test('dashboard defaults to 30d and accepts today 7d 30d 90d and custom', function (): void {
    $this->getJson($dashboardUrl, partnerHeaders())->assertOk()->assertJsonPath('data.period.range', '30d');
    $this->getJson($dashboardUrl.'?range=custom&from=2026-01-01&to=2027-01-03', partnerHeaders())
        ->assertUnprocessable()->assertJsonPath('error.code', 'validation_failed');
    $this->getJson($dashboardUrl.'?range=custom&from=2026-02-02', partnerHeaders())
        ->assertUnprocessable()->assertJsonPath('error.code', 'validation_failed');
});

test('zero-data dashboard and campaign list remain successful', function (): void {
    $this->getJson($dashboardUrl, partnerHeaders())
        ->assertOk()->assertJsonPath('data.metrics.new_customers', 0);
    $this->getJson($campaignsUrl, partnerHeaders())
        ->assertOk()->assertJsonPath('data.items', []);
});
```

- [x] **Step 2: Run the range tests and verify RED**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/GrowthUiContractTest.php --filter="dashboard"
```

Expected: FAIL because the current request accepts only `from`/`to` and does not expose `period.range` or screen-06 fields.

- [x] **Step 3: Implement normalized range handling**

Use validation:

```php
'range' => ['nullable', Rule::in(['today', '7d', '30d', '90d', 'custom'])],
'from' => ['nullable', 'date_format:Y-m-d', 'required_if:range,custom'],
'to' => ['nullable', 'date_format:Y-m-d', 'required_if:range,custom'],
```

Resolve preset ranges inclusively and add validator errors when the order is invalid or the span exceeds 366 days.

- [x] **Step 4: Write failing growth-insights and campaign cursor tests**

```php
$this->getJson($base.'/businesses/biz-1/growth-insights?range=30d', partnerHeaders())
    ->assertOk()
    ->assertJsonStructure(['data' => [
        'growth_score', 'growth_score_change', 'customer_sources', 'highlights',
        'recommendations', 'data_period', 'data_freshness',
    ]]);

$this->getJson($base.'/businesses/biz-1/campaigns?status=active&per_page=1', partnerHeaders())
    ->assertOk()
    ->assertJsonStructure(['data' => ['items', 'summary', 'pagination' => [
        'next_cursor', 'has_more', 'per_page',
    ]]]);
```

- [x] **Step 5: Run insights/campaign tests and verify RED**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/GrowthUiContractTest.php --filter="insights|campaign"
```

Expected: FAIL because insights and recommendations are separate, campaign status is derived incorrectly from `published_at`, and list pagination/filter metadata is absent.

- [x] **Step 6: Implement UI-shaped dashboard and insights**

Extend the summary serializer with headline comparison values, tool cards, `next_actions`, active campaigns, trend series, and freshness timestamps. `growthInsights()` merges the existing insight and suggested-action calculations and emits CTA objects:

```php
[
    'action_type' => 'create_support_ticket',
    'preset_code' => 'growth_recommendation',
    'campaign_id' => null,
    'label' => 'Thiết lập ngay',
]
```

- [x] **Step 7: Implement campaign list/detail and approval**

Use `lb_campaigns.status` as the source of truth, tenant-scope by user and business IDs, filter by allowed public statuses, search `name`, and use `cursorPaginate`.

`CampaignApprovalService::decide()` locks the campaign row. For `approved`, require `pending_approval`, set `status=active`, set `published_at` when empty, and write approval evidence into `settings.partner_approval`. For `changes_requested`, keep `pending_approval`, write the decision evidence, and call a deduplicating bridge method that returns the existing `campaign_request` ticket for the same integration/campaign or creates one.

- [x] **Step 8: Complete package response**

Return `effective_package`, `requested_package`, `approved_package`, package name, slug, status, dates, trial, whitelisted limits, and `mapping_status`. Do not expose prices, credits, internal permissions, or admin fields.

- [x] **Step 9: Run Growth tests GREEN**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/GrowthUiContractTest.php tests/Feature/APIPartnerFizaHUB/DashboardApiTest.php tests/Feature/APIPartnerFizaHUB/InsightsRecommendationsCampaignsTest.php tests/Feature/APIPartnerFizaHUB/PackageApiTest.php
```

Expected: PASS including approval idempotency, invalid state, empty lists, malformed cursors, date boundaries, and campaign tenant isolation.

- [x] **Step 10: Commit Task 4**

```powershell
git add modules/APIPartnerFizaHUB/Http/Requests modules/APIPartnerFizaHUB/Http/Controllers/DashboardController.php modules/APIPartnerFizaHUB/Services/DashboardService.php modules/APIPartnerFizaHUB/Services/CampaignApprovalService.php modules/APIPartnerFizaHUB/Services/PackageService.php modules/APIPartnerFizaHUB/Services/SupportTicketBridge.php tests/Feature/APIPartnerFizaHUB
git commit -m "feat: align FizaHUB growth APIs with approved UI"
```

---

### Task 5: Support Presets and Complete Text-Only Ticket Lifecycle

**Files:**
- Create: `modules/APIPartnerFizaHUB/Http/Requests/ListSupportTicketsRequest.php`
- Create: `tests/Feature/APIPartnerFizaHUB/SupportUiContractTest.php`
- Rename: `tests/Feature/APIPartnerFizaHUB/SupportLifecycleAttachmentTest.php` to `SupportLifecycleTest.php`
- Modify: `modules/APIPartnerFizaHUB/Http/Requests/CreateSupportTicketRequest.php`
- Modify: `modules/APIPartnerFizaHUB/Http/Controllers/SupportTicketController.php`
- Modify: `modules/APIPartnerFizaHUB/Http/Controllers/SupportMessageController.php`
- Modify: `modules/APIPartnerFizaHUB/Services/SupportTicketBridge.php`
- Modify: `modules/APIPartnerFizaHUB/Database/Seeders/PartnerSupportPresetSeeder.php`
- Update: `SupportTicketApiTest.php` and other support contract tests.

**Interfaces:**
- Produces: `SupportTicketBridge::presets(PartnerIntegration): array`.
- Produces: `SupportTicketBridge::list(PartnerIntegration, array $filters): array{summary: array, items: array, pagination: array}`.
- Produces: `SupportTicketBridge::detail(PartnerIntegration, string, ?CarbonImmutable): array` without attachment fields.
- Produces: nested controller signatures receiving `$external_business_id` and `$ticket_id`.

- [ ] **Step 1: Write failing preset/list/lifecycle/tenant tests**

```php
test('support presets expose SOP metadata without attachment promises', function (): void {
    $response = $this->getJson($base.'/businesses/biz-1/support-presets', partnerHeaders());
    $response->assertOk()->assertJsonStructure(['data' => ['items' => [[
        'preset_code', 'subject', 'subject_locked', 'description', 'ticket_type',
        'required_context', 'sla_hours', 'response_channels', 'requires_campaign',
    ]]]);
    expect(json_encode($response->json()))->not->toContain('attachment');
});

test('ticket list contains onboarding and user tickets with summary and cursor pagination', function (): void {
    $response = $this->getJson($base.'/businesses/biz-1/support-tickets?per_page=20', partnerHeaders());
    $response->assertOk()
        ->assertJsonStructure(['data' => ['summary' => [
            'open', 'resolved', 'closed', 'unread_by_business',
        ], 'items', 'pagination' => ['next_cursor', 'has_more', 'per_page']]]);
    expect(collect($response->json('data.items'))->pluck('ticket_type'))->toContain('onboarding');
});
```

Add one sequential test for Create → List → Detail → Message → Close → Reopen, same-key message replay count, cross-tenant 404 for every operation, search/status filters, and route-level 404 for the old attachment URL.

- [ ] **Step 2: Run support tests and verify RED**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/SupportUiContractTest.php
```

Expected: FAIL because routes are not nested in current controllers, list uses page pagination, preset field names are legacy, summary is separate, and detail exposes attachment capabilities.

- [ ] **Step 3: Normalize ticket creation request**

Use:

```php
'preset_code' => ['nullable', 'string', 'max:80'],
'campaign_id' => ['nullable', 'string', 'max:128'],
'subject' => ['nullable', 'string', 'max:255'],
'message' => ['required', 'string', 'max:5000'],
'response_channel' => ['nullable', Rule::in(['in_app', 'phone'])],
'metadata' => ['nullable', 'array'],
```

Require subject only when no preset is selected. For a locked preset subject, ignore the client subject and use the canonical preset subject. Translate canonical fields to context storage as `preset_code`, `campaign_id`, `response_channel`, and metadata.

- [ ] **Step 4: Implement preset catalog**

Seed and serialize at least:

```php
[
    'fizahub_onboarding',
    'qr_scan_not_recorded',
    'growth_recommendation',
    'campaign_request',
    'package_upgrade',
]
```

The onboarding preset is not user-selectable; the public list filters it out unless requested for diagnostic context.

- [ ] **Step 5: Implement nested tenant-scoped lifecycle**

Controller signatures become:

```php
show(Request $request, string $external_business_id, string $ticket_id): JsonResponse
close(Request $request, string $external_business_id, string $ticket_id): JsonResponse
reopen(Request $request, string $external_business_id, string $ticket_id): JsonResponse
store(CreateSupportMessageRequest $request, string $external_business_id, string $ticket_id): JsonResponse
```

Every method resolves the integration from the path before loading the ticket context. Remove query-string scoping code.

- [ ] **Step 6: Implement support list aggregate and polling detail**

Apply `q` to secure ID/title/last message, map public statuses, compute summary on the same tenant-scoped base query, and return cursor pagination. Detail accepts `messages_since`, returns `messages[]` and `next_poll_after_seconds`, and never returns `attachment`, `attachment_url`, upload limits, or allowed MIME types.

- [ ] **Step 7: Preserve safe close/reopen semantics**

Exact same-key replays are handled by middleware. With a new key, closing a closed ticket returns typed 409 `ticket_already_closed`; reopening an open ticket returns typed 409 `ticket_not_closed`. Neither path throws a raw runtime exception or 500.

- [ ] **Step 8: Replace attachment tests with route-absence tests**

Rename the lifecycle file and assert:

```php
$headers = writeHeaders('attachment-must-not-exist');
$requestId = $headers['X-Request-Id'];

$this->postJson(
    $base.'/businesses/biz-1/support-tickets/'.$ticketId.'/attachments',
    [],
    $headers
)->assertNotFound()
    ->assertJsonPath('success', false)
    ->assertJsonPath('meta.request_id', $requestId)
    ->assertJsonPath('error.code', 'route_not_found');

expect(Route::has('partner.fizahub.support-tickets.attachments.store'))->toBeFalse();
```

Keep the attachment model/table/migration untouched.

- [ ] **Step 9: Run all support tests GREEN**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/SupportUiContractTest.php tests/Feature/APIPartnerFizaHUB/SupportLifecycleTest.php tests/Feature/APIPartnerFizaHUB/SupportTicketApiTest.php
```

Expected: PASS for lifecycle, idempotent messages, onboarding visibility, cursor/search/status, and tenant isolation.

- [ ] **Step 10: Commit Task 5**

```powershell
git add modules/APIPartnerFizaHUB/Http/Requests modules/APIPartnerFizaHUB/Http/Controllers modules/APIPartnerFizaHUB/Services/SupportTicketBridge.php modules/APIPartnerFizaHUB/Database/Seeders tests/Feature/APIPartnerFizaHUB
git commit -m "fix: preserve complete FizaHUB support ticket lifecycle"
```

---

### Task 6: CRM Login Link Contract

**Files:**
- Create: `tests/Feature/APIPartnerFizaHUB/CrmLoginLinkApiTest.php`
- Create: `modules/APIPartnerFizaHUB/Database/Migrations/2026_07_20_000000_add_crm_login_idempotency_to_partner_one_time_logins.php`
- Modify: `modules/APIPartnerFizaHUB/Http/Controllers/OneTimeLoginController.php`
- Modify: `modules/APIPartnerFizaHUB/Services/OneTimeLoginService.php`
- Modify: `modules/APIPartnerFizaHUB/Models/PartnerOneTimeLogin.php`
- Modify: `modules/APIPartnerFizaHUB/Http/Middleware/HandlePartnerRequest.php`
- Modify: `tests/Feature/APIPartnerFizaHUB/FizaHubTestHelpers.php`
- Modify: `modules/APIPartnerFizaHUB/Routes/web.php` only if the generated consume URL name or path must change; preserve signed consumption behavior.
- Update: `OneTimeLoginApiTest.php`.

**Interfaces:**
- Produces: `OneTimeLoginService::issue(PartnerIntegration, string $idempotencyKey, string $requestId): array{url: string, expires_at: string, expires_in_seconds: int}`.
- Consumes: middleware hash-conflict detection while bypassing redacted response replay for this sensitive endpoint.

- [ ] **Step 1: Write failing readiness, mapping, expiry, use, and idempotency tests**

```php
test('CRM link is issued only for ready or completed onboarding', function (string $status, int $expected): void {
    seedMappedBusinessWithOnboarding($status);
    $this->postJson($crmUrl, [], writeHeaders('crm-'.$status))->assertStatus($expected);
})->with([
    ['awaiting_consultant', 409],
    ['configuring', 409],
    ['ready', 201],
    ['completed', 201],
]);

test('same key returns the same live CRM link and the mapped user is not admin', function (): void {
    $first = $this->postJson($crmUrl, [], writeHeaders('crm-live-1'))->assertCreated();
    $second = $this->postJson($crmUrl, [], writeHeaders('crm-live-1'))->assertCreated();

    expect($second->json('data.url'))->toBe($first->json('data.url'));
    $user = User::query()->findOrFail($integration->mlhub_user_id);
    expect($user->is_super_admin)->toBeFalse();
});
```

- [ ] **Step 2: Run CRM tests and verify RED**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/CrmLoginLinkApiTest.php
```

Expected: FAIL on the renamed public route and any legacy allowance for an integration without onboarding.

- [ ] **Step 3: Add secure replay storage with an additive migration**

The migration adds nullable columns so existing rows remain valid:

```php
Schema::table('partner_one_time_logins', function (Blueprint $table): void {
    $table->string('idempotency_key', 128)->nullable()->after('request_id');
    $table->text('token_ciphertext')->nullable()->after('token_hash');
    $table->unique(
        ['partner_integration_id', 'idempotency_key'],
        'partner_one_time_logins_integration_idempotency_unique'
    );
});
```

The `down()` method drops only this new unique index and these two new columns. It does not touch rows, tokens, integrations, users, or other tables.

- [ ] **Step 4: Enforce CRM readiness and response contract**

Require an onboarding row with public status `ready` or `completed`; legacy integrations without onboarding return 409 `onboarding_not_ready`. On first issue, store `Crypt::encryptString($plainToken)` in `token_ciphertext` and the request idempotency key. On same-key replay while the row is unused and unexpired, decrypt the existing token and regenerate the same signed URL using the original `expires_at`. Generate links bound to the stored integration, mapped user ID, and workspace ID. Preserve single-use locking and session regeneration in the web controller.

- [ ] **Step 5: Document same-key expiry behavior in tests**

An exact same-key replay returns its stored link only while that link is unused and unexpired. If it has expired or was consumed, return HTTP 409 `crm_login_link_not_reusable` with `details.next_action=new_idempotency_key`; do not create another link for that key. Add an assertion that a new key creates a different link after the original has been consumed or expired.

- [ ] **Step 6: Run CRM tests GREEN**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/CrmLoginLinkApiTest.php tests/Feature/APIPartnerFizaHUB/OneTimeLoginApiTest.php
```

Expected: PASS for readiness, expiry, one-time consume, mapped identity, non-admin access, replay, and new-key replacement.

- [ ] **Step 7: Commit Task 6**

```powershell
git add modules/APIPartnerFizaHUB/Database/Migrations/2026_07_20_000000_add_crm_login_idempotency_to_partner_one_time_logins.php modules/APIPartnerFizaHUB/Models/PartnerOneTimeLogin.php modules/APIPartnerFizaHUB/Http/Middleware/HandlePartnerRequest.php modules/APIPartnerFizaHUB/Http/Controllers/OneTimeLoginController.php modules/APIPartnerFizaHUB/Services/OneTimeLoginService.php modules/APIPartnerFizaHUB/Routes/web.php tests/Feature/APIPartnerFizaHUB
git commit -m "feat: add FizaHUB CRM login link contract"
```

---

### Task 7: Postman, Public Documentation, and Full UI Happy Path

**Files:**
- Create: `tests/Feature/APIPartnerFizaHUB/FullUiHappyPathTest.php`
- Modify: `tests/Feature/APIPartnerFizaHUB/DocumentationContractTest.php`
- Modify: `tests/Feature/APIPartnerFizaHUB/ApiFizaHubDocsPageTest.php`
- Modify: `tests/Feature/APIPartnerFizaHUB/FullPartnerHappyPathTest.php` to remove the old 24-route flow or delegate to the new path.
- Modify: `modules/APIPartnerFizaHUB/docs/FizaHUB-Partner-API.postman_collection.json`
- Modify: `modules/APIPartnerFizaHUB/README.md`
- Modify: `modules/APIPartnerFizaHUB/docs/ENDPOINT_MATRIX.md`
- Modify: `modules/APIPartnerFizaHUB/Resources/views/api-fizahub.blade.php`
- Modify: `modules/APIPartnerFizaHUB/Resources/views/api-fizahub-help-test.blade.php`

**Interfaces:**
- Consumes: all 22 route contracts from Tasks 1–6.
- Produces: one Postman v2.1 collection with exactly 22 executable requests.
- Produces: one sequential application-level happy path matching all 15 UI screens.

- [ ] **Step 1: Rewrite documentation contract tests first**

Assert the flattened collection contains exactly these 22 method/path pairs, has no attachment or legacy paths, and defines variables:

```php
[
    'base_url', 'partner_token', 'external_user_id', 'external_business_id',
    'onboarding_request_id', 'onboarding_ticket_id', 'ticket_id', 'campaign_id',
    'from', 'to',
]
```

Assert every request has `X-Request-Id: {{$guid}}`; every state-changing request except SSO verify has a non-empty generated `Idempotency-Key`; dependent requests have a pre-request skip guard; and no script stores a literal unresolved `{{ticket_id}}` or selects the onboarding ticket as the newly created user ticket.

- [ ] **Step 2: Run documentation tests and verify RED**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/DocumentationContractTest.php tests/Feature/APIPartnerFizaHUB/ApiFizaHubDocsPageTest.php
```

Expected: FAIL because the collection/docs still advertise 24 endpoints, attachment, and legacy paths.

- [ ] **Step 3: Rebuild the collection as five folders**

Use folders and counts:

- System: 2
- Onboarding: 6
- Growth: 6
- Support: 7
- CRM: 1

Total: 22.

The onboarding test script accepts 200/201/202 and stores request/ticket IDs. Campaign list stores the first campaign ID only when present. Create Ticket stores its returned ID. List Tickets may recover the user ticket ID by matching `preset_code` and subject from the just-created request; it must exclude `ticket_type=onboarding`.

- [ ] **Step 4: Update README, endpoint matrix, docs page, and help page**

Each artifact includes:

- exact 22-route list;
- 15-screen mapping;
- explicit Screen 02 mapping to `GET marketing-catalog` plus `PATCH marketing-preferences` after onboarding, and Screen 10 mapping to `GET package` plus `PATCH marketing-preferences` for the interested package;
- canonical envelope;
- HTTP onboarding outcomes;
- idempotency replay/conflict semantics;
- activation/onboarding/timeline enums;
- cursor/date-range contract;
- breaking cutover instructions;
- no attachment upload or upload promise;
- complete text-only support lifecycle;
- CRM readiness and new-key replacement rule.

- [ ] **Step 5: Write the failing full UI happy-path test**

The test executes in this order:

```text
Marketing Status 404
Marketing Catalog
Create Onboarding
Marketing Status 200
Onboarding Detail
Update Profile
Update Marketing Preferences
Dashboard today
Dashboard 30d
Growth Insights
Campaign List
Campaign Detail when ID exists
Campaign Approval when pending approval exists
Business Package
Support Presets
Support List
Create Ticket
Ticket Detail
Send Message
Close
Reopen
CRM Login Link after marking onboarding ready
Repeat Onboarding with a fresh idempotency key
```

It asserts every ID is obtained from an earlier response, repeat onboarding returns HTTP 200, record counts do not change, and the ticket ID is stable.

- [ ] **Step 6: Run the happy path and verify RED, then align remaining serializers/scripts**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/FullUiHappyPathTest.php
```

Expected initial result: FAIL at the first remaining contract drift. Make only the serializer/Postman/docs changes necessary for the failing assertion, rerun, and repeat until the test passes.

- [ ] **Step 7: Run documentation and happy-path tests GREEN**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/DocumentationContractTest.php tests/Feature/APIPartnerFizaHUB/ApiFizaHubDocsPageTest.php tests/Feature/APIPartnerFizaHUB/FullUiHappyPathTest.php
```

Expected: PASS with 22 requests and no attachment/legacy route text.

- [ ] **Step 8: Run Newman against staging or the approved local environment**

Start the application with a test-safe FizaHUB token, then run:

```powershell
npx newman run modules/APIPartnerFizaHUB/docs/FizaHUB-Partner-API.postman_collection.json --env-var "base_url=http://127.0.0.1:8000" --env-var "partner_token=$env:FIZAHUB_PARTNER_TOKEN" --reporters cli,json --reporter-json-export storage/app/fizahub-newman-result.json
```

Expected: 22 requests considered; dependency guards skip only unavailable campaign/approval or not-ready CRM steps; zero failed assertions. Do not place secrets in the collection or exported report committed to git.

- [ ] **Step 9: Commit Task 7**

```powershell
git add modules/APIPartnerFizaHUB/docs modules/APIPartnerFizaHUB/README.md modules/APIPartnerFizaHUB/Resources/views tests/Feature/APIPartnerFizaHUB
git commit -m "docs: publish FizaHUB 22-endpoint integration cutover"
```

---

### Task 8: Production Readiness, Doctor, Full Verification, and Cutover Evidence

**Files:**
- Modify: `modules/APIPartnerFizaHUB/Console/Commands/FizaHubDoctorCommand.php`
- Modify: `tests/Feature/APIPartnerFizaHUB/FizaHubDoctorCommandTest.php`
- Modify: `modules/APIPartnerFizaHUB/Support/PartnerReadinessChecker.php` only if a check is missing for an existing required table/configuration.
- Modify: `modules/APIPartnerFizaHUB/README.md` with the final staging/production cutover checklist.

**Interfaces:**
- Produces: Doctor exact route count and route-name verification for the 22 endpoint contract.
- Consumes: all implementation and documentation artifacts from Tasks 1–7.

- [ ] **Step 1: Write failing Doctor route-set tests**

```php
test('doctor requires exactly the approved 22 routes and rejects legacy aliases', function (): void {
    $this->artisan('fizahub:doctor')
        ->expectsOutputToContain('All 22 documented partner routes are registered')
        ->assertSuccessful();

    expect(Route::has('partner.fizahub.onboarding.confirm'))->toBeFalse()
        ->and(Route::has('partner.fizahub.onboarding.cancel'))->toBeFalse()
        ->and(Route::has('partner.fizahub.support-tickets.attachments.store'))->toBeFalse();
});
```

- [ ] **Step 2: Run Doctor tests and verify RED**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/FizaHubDoctorCommandTest.php
```

Expected: FAIL because Doctor still requires the former 24 named routes.

- [ ] **Step 3: Replace Doctor required route names**

Set `REQUIRED_ROUTES` to the 22 names established in Task 1 and add a forbidden-route check for attachment and old aliases. Add `2026_07_20_000000_add_crm_login_idempotency_to_partner_one_time_logins` to `REQUIRED_MIGRATIONS`.

- [ ] **Step 4: Run the complete module test suite**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB
```

Expected: all APIPartnerFizaHUB tests pass with zero failures.

- [ ] **Step 5: Run Pint check**

Run:

```powershell
vendor/bin/pint --test modules/APIPartnerFizaHUB tests/Feature/APIPartnerFizaHUB
```

Expected: exit 0 with no formatting changes required.

- [ ] **Step 6: Run the live Doctor command**

Run:

```powershell
php artisan fizahub:doctor
```

Expected: `OVERALL: PASS` and the route line reports 22 routes.

- [ ] **Step 7: Verify route count and absence of legacy routes directly**

Run:

```powershell
php artisan route:list --path=api/v1/partners/fizahub --json
```

Parse the JSON and verify 22 entries, with no `attachments`, `integration-status`, `/packages`, `/insights`, `/recommendations`, `/one-time-login`, confirm/cancel, or query-scoped ticket routes.

- [ ] **Step 8: Inspect final diff and migration state**

Run:

```powershell
git diff --check
git status --short
git diff --stat HEAD~8..HEAD
```

Expected: no whitespace errors, no unexpected files staged, `codeok.zip` remains untouched, and the only migration change is the additive CRM idempotency migration.

- [ ] **Step 9: Record staging cutover evidence**

In the final report record:

- 15-screen endpoint matrix;
- final 22 endpoint list;
- files changed;
- onboarding before/after behavior;
- username algorithm;
- duplicate outcomes;
- support lifecycle result;
- resource counts after two onboarding calls;
- Newman result and report location;
- module tests, Pint, and Doctor output;
- migration result (one additive CRM idempotency migration; no destructive operation);
- known limitations;
- commit messages;
- required operational sequence: deploy code and docs together, re-import Postman, smoke-test staging, then cut over production.

- [ ] **Step 10: Commit Task 8**

```powershell
git add modules/APIPartnerFizaHUB/Console/Commands/FizaHubDoctorCommand.php modules/APIPartnerFizaHUB/Support/PartnerReadinessChecker.php modules/APIPartnerFizaHUB/README.md tests/Feature/APIPartnerFizaHUB/FizaHubDoctorCommandTest.php
git commit -m "chore: verify FizaHUB production cutover readiness"
```

---

## Plan Self-Review Checklist

- Every approved endpoint is introduced in Task 1 and implemented by Tasks 2–6.
- All seven locked contract conditions are covered by explicit tests.
- Every production behavior change is preceded by a test and an observed RED command.
- Attachment route removal does not delete dormant attachment storage.
- No public alias can increase the route count above 22.
- Postman, README, docs pages, endpoint matrix, Doctor, and feature tests consume the same route list.
- The only planned migration is additive and stores CRM replay tokens encrypted; no destructive migration or unrelated refactor is planned.
