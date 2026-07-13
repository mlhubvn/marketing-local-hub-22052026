# Google Business Scheduler Guard Design

## Scope

Fix only `google-business:publish-scheduled-posts`. Do not change the scheduler frequency, database schema, `APIPartnerFizaHUB`, or unrelated Google Business flows.

## Root cause

The scheduled-post command selects due posts through a managed location but does not verify that the location's connection is `connected`. Demo connections are `simulated`, have no OAuth tokens, and own managed locations with scheduled demo posts. When those posts become due, the command calls Google with mock identifiers and an empty token. It catches the record error, marks the post failed, then returns exit code 1 because at least one record failed.

## Design

Keep the current due-post query and relationship names: `GoogleBusinessPost::location()` and `GoogleBusinessLocation::connection()`. Before calling `GoogleBusinessClient::publishPost()`, skip every post whose connection status is not `connected`; simulated posts remain scheduled and unchanged.

For a connected connection, validate the local prerequisites needed to build an authenticated Google request: location relation, connection relation, decrypted access token, `google_account_id`, and `google_location_id`. A missing prerequisite is a record-level failure: mark the post `failed`, store the reason, emit a structured warning, and continue.

All publish exceptions are also record-level failures. Request-payload generation and post-log recording are best-effort diagnostics and are isolated so they cannot turn an already handled record failure into a command failure. Persisting the post's failed state is not best-effort: if that database write fails, the exception escapes and Artisan returns failure as a genuine system failure.

The command returns success after processing all selected records regardless of the record-level failed count. Exceptions raised while querying candidates, eager-loading relationships, or persisting required post state remain outside the per-record boundary and result in command failure.

## Observability

Each connected record failure writes a warning named `Google Business scheduled post failed.` with `post_id`, `user_id`, `team_id`, `business_id`, local `location_id`, external `google_location_id`, `connection_id`, and `reason`. Failure audit rows continue using `lb_google_business_post_logs` when that table is writable.

## Testing

Add a focused Pest feature test with minimal in-memory tables. Cover empty queue, simulated skip, connected prerequisite failure, continuation after one publish failure, and system failure outside the record boundary. Assert command exit status, post state, client calls, and structured warning context.

## Data and deployment

No migration and no direct production data update. Previously failed demo posts remain historical records. Future due demo posts are skipped because their connection is simulated. The scheduler remains enabled every five minutes.
