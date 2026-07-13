# MLHUB Static Legal Pages Install Design

## Goal

Ensure every fresh `php artisan mlhub:install` installs the official MLHUB Privacy Policy and Terms of Use into the existing `options` store for Google Business Profile API verification.

## Scope

This change is limited to the `CustomMLHUB` install flow, a dedicated static-page data file, a small seeding action, and focused tests. It does not create a command, route, model, table, or migration. It does not change `mlhub:update`, `APIPartnerFizaHUB`, or the Google Business scheduler.

## Existing flow

`MLHUBInstallCommand` runs `migrate:fresh`, calls `MLHUBArtisanTasks::seed($this, 'install')`, and then optimizes the application. The seed stack loads `MLHUBBootstrapSeeder::seedSiteOptions()`, which currently imports placeholder values for the four legal-page option keys.

The public endpoints already exist:

- `https://mlhub.vn/privacy-policy` reads `privacy_policy_title` and `privacy_policy_content`.
- `https://mlhub.vn/terms-of-use` reads `terms_of_use_title` and `terms_of_use_content`.

## Components

### Legal-page data

Create `modules/CustomMLHUB/Database/data/mlhub_static_pages.php`. It returns exactly these four keys:

- `privacy_policy_title`
- `privacy_policy_content`
- `terms_of_use_title`
- `terms_of_use_content`

The HTML content is the approved Vietnamese legal copy from the task attachment dated 13/07/2026. Privacy content must include the exact phrases `Google API Services User Data Policy` and `Limited Use`. Terms content must include `Điều khoản Sử dụng`.

### SeedStaticPagesAction

Create `Modules\CustomMLHUB\Actions\SeedStaticPagesAction`. The action receives `OptionStore` and exposes `handle(bool $force = false): array`.

The action uses `Schema::hasTable('options')` only to verify the required table exists. All option reads and writes use `OptionStore::get()` and `OptionStore::set()`; it does not use direct database writes.

For each of the four keys:

- Write the approved default when `$force` is true.
- Otherwise write only when the current value is missing, `null`, blank, or the default placeholder.
- Preserve custom values when `$force` is false.

Placeholder comparison normalizes the current value with `trim(strip_tags(html_entity_decode((string) $value)))` and compares it with `Nội dung đang cập nhật...`. Blank detection uses the same normalized string.

After each write, read the option back and throw a clear runtime error if it was not persisted. Return updated and preserved key lists for command output and tests. If the `options` table does not exist, throw a clear runtime error before reading or writing any option.

`OptionStore::set()` remains responsible for invalidating `options.{key}` and `options.v2.{key}` cache entries.

### Install integration

Inject `SeedStaticPagesAction` into `MLHUBInstallCommand::handle()`. Call `handle(force: true)` immediately after `MLHUBArtisanTasks::seed($this, 'install')` and before `MLHUBArtisanTasks::optimize($this)`.

The action is always forced at this call site because `mlhub:install` has already executed `migrate:fresh`. The command's `--force` flag remains only a confirmation bypass and is not used to choose static-page behavior.

If the action throws, print a clear install error and return `Command::FAILURE`; do not continue to optimization or report a successful installation.

## Tests

Action-level tests use a minimal `options` table and cover:

- missing key;
- stored `null`;
- empty value;
- HTML/entity/whitespace placeholder;
- custom value preservation without force;
- force overwrite of all four keys;
- repeated execution without duplicate option names;
- missing table error;
- cache invalidation through `OptionStore::set()`.

Integration tests cover:

- `MLHUBInstallCommand` invokes the action after install seeding with `force: true`;
- `/privacy-policy` renders `Google API Services User Data Policy` and `Limited Use`;
- `/terms-of-use` renders `Điều khoản Sử dụng`.

## Deployment and operation

No migration is required. Run the existing destructive install command only when a fresh installation is intended:

```bash
php artisan mlhub:install
```

The final Google verification URLs remain:

- `https://mlhub.vn/privacy-policy`
- `https://mlhub.vn/terms-of-use`
