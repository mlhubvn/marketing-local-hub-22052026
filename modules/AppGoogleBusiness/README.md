# Google Business Profile Integration Addon

Google Business Profile Integration Addon connects MLHUB AI with Google Business Profile so users can import Google locations, map them to MLHUB businesses, sync reviews, and publish review replies from the portal.

## What This Module Does

- Connect a Google account with OAuth 2.0.
- Fetch Google Business Profile accounts and locations after OAuth.
- Show a temporary location selection list after Google connect.
- Store only the Google locations that users choose to add.
- Let users choose which added Google locations to manage.
- Map a Google location to an existing MLHUB business.
- Create a MLHUB business from a Google location.
- Stop managing or delete synced Google locations from the workspace.
- Sync business profile data, opening hours, and reviews.
- Auto-sync reviews with a cron command.
- Create Google review auto-reply rules.
- Generate AI review reply drafts or auto-publish safe replies.
- Show review analytics and automation logs.
- Publish review replies back to Google.
- Create local Google Business post drafts.
- Publish standard, offer, event, and alert posts to Google Business Profile.
- Link posts to MLHUB campaigns, booking pages, coupon pages, lead forms, or landing pages by CTA URL.
- Store Google post publish logs for audit and troubleshooting.
- Expose plan and pricing permissions for selling this as a separate addon.

The module is self-contained under `modules/AppGoogleBusiness`. It can be removed without breaking core MLHUB pages because core references use guarded `class_exists` checks.

## Module Routes

| Route | Name | Purpose |
| --- | --- | --- |
| `/portal/integrations/google-business` | `portal.google-business` | Main Google Business portal page |
| `/portal/integrations/google-business/connect` | `portal.google-business.connect` | Start Google OAuth |
| `/portal/integrations/google-business/callback` | `portal.google-business.callback` | Google OAuth callback, location sync, and redirect to Locations |

## Google Cloud Setup

1. Create or open a Google Cloud project.
2. Configure the OAuth consent screen.
3. Create an OAuth 2.0 Client ID.
4. Add this authorized redirect URI:

```text
https://your-domain.com/portal/integrations/google-business/callback
```

For local development, use your local URL:

```text
https://localhost/portal/integrations/google-business/callback
```

5. Enable/request access for Google Business Profile APIs.

Google Business Profile APIs may require API access approval depending on the Google account/project. The module will show a clean error if Google rejects access.

Useful Google docs:

- https://developers.google.com/my-business/ref_overview
- https://developers.google.com/my-business/reference/businessinformation/rest/v1/accounts.locations/list
- https://developers.google.com/my-business/content/review-data
- https://developers.google.com/my-business/reference/rest/v4/accounts.locations.reviews/updateReply

## Admin API Integration Setup

Recommended setup:

1. Open admin dashboard.
2. Go to **Settings > API Integration**.
3. Open **Google Business Profile**.
4. Enable the provider.
5. Enter:
   - Google OAuth Client ID
   - Google OAuth Client Secret
6. Copy the readonly callback URL into Google Cloud OAuth credentials.
7. Save configuration.

The module registers this API Integration item from `AppGoogleBusinessServiceProvider`, so the item disappears automatically if the module is removed.

## Environment Variables

Add these to `.env`:

```env
GOOGLE_BUSINESS_CLIENT_ID=
GOOGLE_BUSINESS_CLIENT_SECRET=
```

Then clear config cache:

```bash
php artisan optimize:clear
```

The module reads credentials from Admin API Integration first, then falls back to `.env`.

## OAuth Scope

The module uses:

```text
https://www.googleapis.com/auth/business.manage
```

This scope is required for managing Google Business Profile locations and reviews.

## Database Tables

The module creates these tables:

| Table | Purpose |
| --- | --- |
| `lb_google_business_connections` | OAuth connection and encrypted tokens |
| `lb_google_business_locations` | Synced Google locations and MLHUB business mapping |
| `lb_google_reviews` | Synced Google reviews and reply state |
| `lb_google_auto_reply_rules` | Auto reply rules by rating, text, location, and business |
| `lb_google_auto_reply_logs` | Auto reply generation, publish, skipped, and failed logs |
| `lb_google_business_posts` | Local Google Business post drafts, publish status, CTA, image, offer, and event fields |
| `lb_google_business_post_logs` | Google post create/update/delete request logs, response body, and errors |

Access and refresh tokens are encrypted with Laravel `Crypt`.

### Location Management State

Google locations from OAuth are first stored temporarily in the session as selection candidates. They are not written to `lb_google_business_locations` until the user chooses **Add to manage**.

`lb_google_business_locations` stores only Google locations that were added by the user.

| State | Meaning |
| --- | --- |
| Available | Added to MLHUB, visible in Locations, but not used for reviews, analytics, auto reply, or cron sync |
| Managed | Enabled by the user with **Manage**, **Map**, or **Import** |
| Stopped | Kept in MLHUB but removed from managed review/analytics/auto-reply workflows |
| Deleted | Removed from MLHUB. The Google account stays connected and the location can be synced again later |

Newly connected Google locations are shown in a temporary choose-location list. This prevents agencies with many Google locations from accidentally adding every location after OAuth.

## Plan And Pricing Keys

The module registers these plan permissions:

```text
google_business
max_google_business_connections
max_google_business_locations
google_review_sync
google_review_reply
google_business_insights
google_business_posts
```

Suggested packaging:

- Starter: disabled
- Growth: location sync + review sync
- Agency/Pro: review replies + insights + posts

## How Users Use It

1. Open **Integrations > Google Business**.
2. Click **Connect Google**.
3. Complete Google OAuth.
4. MLHUB fetches Google locations and redirects to the **Locations** tab.
5. Choose only the locations this workspace should add.
6. For each location, use:
   - **Add to manage** to write the selected Google location into MLHUB.
   - **Manage** to enable reviews, analytics, and auto reply for that location.
   - **Map** to connect it to an existing MLHUB business.
   - **Import** to create a new MLHUB business from the Google location.
   - **Stop** to keep the location synced but remove it from managed workspaces.
   - **Delete** to remove the synced Google location from MLHUB.
7. Open the **Reviews** tab to sync, filter, draft, and publish Google review replies.
8. Open the **Auto Reply** tab to create AI reply rules.
9. Open **Analytics** to monitor review and location performance.

Important: connecting Google does **not** automatically add or manage every Google location. Locations are added to MLHUB only after the user chooses **Add to manage**.

## Google Business Tabs

| Tab | Purpose |
| --- | --- |
| Overview | Location, review, reply, and setup summary |
| Locations | Choose managed Google locations, map/import businesses, sync actions, stop/delete locations |
| Reviews | Review inbox, filters, AI reply drafts, publish to Google |
| Posts | Create drafts and publish Google Business updates, offers, events, and alerts |
| Auto Reply | Rules for manual, draft-only, or auto-published replies |
| Analytics | Review trends, rating distribution, reply performance, and top locations |

There is no separate **Channels** or **Settings** tab in the current UI. Google accounts are managed from the top of **Locations** because most customers connect one account and mainly work from **Locations** and **Reviews**.

Technical setup is intentionally kept in admin:

- OAuth callback and Google credentials belong in **Admin > Settings > API Integration > Google Business Profile**.
- Cron setup belongs in **Admin > Settings > Crons** or the server cron manager.

## Cron Auto Sync

Add this command to the server cron to auto-sync reviews:

```bash
*/15 * * * * php /path/to/artisan google-business:sync-reviews
```

Optional single-location sync:

```bash
php artisan google-business:sync-reviews --location_id=123
```

The command only processes Google locations that are marked as **Managed** and have review sync enabled.

The module also registers **Google Business Review Sync** in **Admin > Settings > Crons**. From there, admins can copy the URL cron or direct Artisan cron command generated by the system cron manager.

## Auto Reply Modes

| Mode | Behavior |
| --- | --- |
| Save AI Draft | AI generates a local draft for user approval. This is the safest default. |
| Auto Publish to Google | AI generates and publishes the reply through Google Business Profile API. Use only for safe positive review replies. |
| Use Template Reply | Sends a fixed reply text without AI generation. |

Recommended default:

- Use **Save AI Draft** for 1-3 star reviews.
- Use **Auto Publish** only for safe 4-5 star thank-you replies.
- Use **Template Reply** for simple, controlled responses that should not call AI.

Supported conditions:

- Rating: any rating, 5 stars, 4-5 stars, 3 stars or below, 1-2 stars, or custom operators (`>=`, `<=`, `=`).
- Text: any review, only reviews with text, only reviews without text, contains keyword, or does not contain keyword.

Auto reply logs record skipped, draft, published, and failed outcomes so API issues and rule decisions can be audited.

## Google Business Posts

The **Posts** tab lets users create and publish Google Business Profile Local Posts from MLHUB.

Supported post types:

| Type | Google topic type | Notes |
| --- | --- | --- |
| Standard update | `STANDARD` | General business update |
| Offer post | `OFFER` | Uses Google offer fields. The old `GET_OFFER` CTA is not used. |
| Event post | `EVENT` | Requires start and end dates |

Alert posts are not exposed in the UI because Google marks `ALERT` as not always available for authoring and many locations return `INVALID_ARGUMENT` when creating new alert posts.

Supported CTA values for non-offer posts:

```text
BOOK
ORDER
SHOP
LEARN_MORE
SIGN_UP
CALL
```

Offer posts use `topicType=OFFER` instead of the deprecated Get Offer CTA. The module stores local post status as draft, scheduled, published, failed, or deleted and records publish logs in `lb_google_business_post_logs`.

Scheduled posts use:

```bash
php artisan google-business:publish-scheduled-posts
```

Recommended cron:

```bash
*/5 * * * * php /path/to/artisan google-business:publish-scheduled-posts
```

The module also registers **Google Business Scheduled Posts** in **Admin > Settings > Crons**.

## Detachable Addon Notes

This module owns its routes, migrations, models, Livewire component, provider, OAuth controller, and Google API client.

Core-safe integrations:

- Sidebar registration happens in `AppGoogleBusinessServiceProvider`.
- Pricing registration happens in `AppGoogleBusinessServiceProvider`.
- Dashboard usage rows are guarded with `class_exists`.

If the module is removed, remove this block from `app/Support/Plans/PlanLimitGuard.php` if you want zero leftover references, but it will not fatal because it checks class existence before querying module models.

## Verification Commands

Run after installing the module:

```bash
php artisan migrate --force
php artisan route:list --name=google-business
php artisan view:cache
```

Expected routes:

```text
portal.google-business
portal.google-business.connect
portal.google-business.callback
```

## Current Phase

Implemented phase:

- Google OAuth scaffold
- Location sync
- Redirect to Locations after OAuth
- User-selected managed locations
- Business mapping
- Review sync
- Review reply publish
- AI reply generation
- Auto reply rules
- Review analytics
- Stop/delete location actions
- Google Business Posts
- Plan/pricing support

Future phase:

- Insights dashboard
- Import Google opening hours into booking availability with stricter normalization
