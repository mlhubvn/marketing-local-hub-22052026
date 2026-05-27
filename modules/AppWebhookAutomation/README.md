# Webhook & Zapier Automation Addon

Webhook & Zapier Automation lets LocalBoost AI send event payloads to external systems such as Zapier, Make, CRMs, Google Sheets, Slack, Telegram, or custom endpoints.

This module is built as a detachable addon under `modules/AppWebhookAutomation`. It owns its routes, migrations, models, Livewire screens, jobs, event listeners, plan keys, and pricing registration.

## Features

- Create webhook automation rules from the portal.
- Trigger webhooks from LocalBoost events.
- Filter rules by business and simple conditions.
- Send default JSON payloads to external URLs.
- Support custom HTTP method, headers, secret token, and retry flag.
- Test webhook payloads before enabling a rule.
- Store webhook logs with request, response, status, and error details.
- Register plan and pricing keys so the addon can be sold separately.

## Portal Menu

The module registers these items under **Automation**:

| Menu | Route name | Path |
| --- | --- | --- |
| Webhook Automations | `portal.webhook-automations` | `/portal/webhook-automation/automations` |
| Webhook Logs | `portal.webhook-logs` | `/portal/webhook-automation/logs` |

## Supported Triggers

The provider registers model listeners only when the related module classes exist. This keeps the addon removable.

| Event | Source |
| --- | --- |
| `lead.submitted` | Lead form submission |
| `booking.submitted` | New booking |
| `booking.confirmed` | Booking status changed to confirmed |
| `booking.cancelled` | Booking status changed to cancelled |
| `booking.completed` | Booking status changed to completed |
| `coupon.claimed` | Coupon redemption created |
| `coupon.used` | Coupon redemption status changed to used |
| `feedback.submitted` | Feedback form response |
| `feedback.low_score` | Feedback rating 1-3 |
| `review.positive` | Review booster rating 4-5 |
| `review.low_score` | Review booster low score |
| `customer.created` | Customer created |

## Database Tables

| Table | Purpose |
| --- | --- |
| `lb_webhook_automations` | Rule configuration, trigger, conditions, endpoint, headers, and status |
| `lb_webhook_automation_logs` | Delivery logs, payloads, response body, status code, and errors |

## Plan And Pricing Keys

```text
webhook_automation
max_webhook_automations
webhooks_per_month
webhook_custom_headers
webhook_retry
```

Suggested packaging:

- Starter: disabled
- Growth: limited rules and webhook sends
- Agency: custom headers, retry, higher monthly send limit

## Example Payload

```json
{
  "event": "lead.submitted",
  "business": {
    "id": 1,
    "name": "Bloom Spa Studio"
  },
  "customer": {
    "name": "Sarah",
    "email": "sarah@example.com",
    "phone": "+1 555 0101"
  },
  "lead": {
    "source": "Lead Form",
    "status": "new",
    "message": "I want a consultation"
  },
  "created_at": "2026-05-12T10:30:00Z"
}
```

## Detachable Addon Notes

- Sidebar registration happens inside `AppWebhookAutomationServiceProvider`.
- Pricing and plan permission registration happen inside the same provider.
- Trigger listeners use `class_exists` checks before binding to optional modules.
- Removing this module should not break core LocalBoost pages.

## Verification

```bash
php artisan migrate --force
php artisan route:list --name=webhook
php artisan view:cache
```

