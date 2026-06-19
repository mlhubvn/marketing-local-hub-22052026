# WhatsApp Notification Addon

WhatsApp Notification sends customer and business follow-up messages through WhatsApp when MLHUB AI events happen. It is similar to Email Automation, but the action channel is WhatsApp.

This module is built as a detachable addon under `modules/AppWhatsAppNotification`. It owns its routes, migrations, models, Livewire screens, jobs, trigger listeners, plan keys, and pricing registration.

## Features

- Create WhatsApp notification automation rules.
- Manage WhatsApp message templates.
- Send messages through WhatsApp Cloud API.
- Support customer, business owner, and custom phone recipients.
- Support immediate and delayed rule timing.
- Store delivery logs for auditing and troubleshooting.
- Register plan and pricing keys so the addon can be sold separately.

## Portal Menu

The module registers these items under **Automation**:

| Menu | Route name | Path |
| --- | --- | --- |
| WhatsApp Notifications | `portal.whatsapp-notifications` | `/portal/whatsapp-notification/automations` |
| WhatsApp Templates | `portal.whatsapp-templates` | `/portal/whatsapp-notification/templates` |
| WhatsApp Logs | `portal.whatsapp-logs` | `/portal/whatsapp-notification/logs` |

## Supported Triggers

The provider registers model listeners only when the related module classes exist.

| Event | Example use |
| --- | --- |
| `lead.submitted` | Thank customer and notify business owner |
| `booking.submitted` | Confirm booking request |
| `booking.confirmed` | Confirm appointment |
| `booking.cancelled` | Notify cancellation |
| `booking.completed` | Send review request link |
| `coupon.claimed` | Send coupon code |
| `feedback.submitted` | Acknowledge feedback |
| `review.positive` | Send review booster follow-up |
| `review.low_score` | Alert business owner |
| `customer.created` | Welcome customer |

## WhatsApp Cloud API Requirements

Recommended production integration:

- Meta Business account
- WhatsApp Business Account
- Phone Number ID
- Access token
- Approved message templates for messages outside the 24-hour customer service window
- Webhook setup if inbound status callbacks are added later

Each automation can store WhatsApp Cloud API settings such as phone number ID, access token, approved template name, and template language.

## Database Tables

| Table | Purpose |
| --- | --- |
| `lb_whatsapp_notifications` | Rule configuration, trigger, recipient, timing, API settings, and status |
| `lb_whatsapp_templates` | Reusable WhatsApp message templates |
| `lb_whatsapp_notification_logs` | Delivery logs, response payloads, status, and errors |

## Plan And Pricing Keys

```text
whatsapp_notification
max_whatsapp_notifications
max_whatsapp_templates
whatsapp_messages_per_month
whatsapp_cloud_api
whatsapp_template_messages
```

Suggested packaging:

- Starter: disabled
- Growth: basic notification rules
- Agency: Cloud API, approved templates, higher monthly send limit

## Detachable Addon Notes

- Sidebar registration happens inside `AppWhatsAppNotificationServiceProvider`.
- Pricing and plan permission registration happen inside the same provider.
- Trigger listeners use `class_exists` checks before binding to optional modules.
- Removing this module should not break Email Automation, Webhooks, or core MLHUB pages.

## Verification

```bash
php artisan migrate --force
php artisan route:list --name=whatsapp
php artisan view:cache
```

