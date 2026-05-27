# Email Automation Addon

Email Automation sends customer and business follow-up emails when LocalBoost AI events happen. It provides rule-based email workflows for bookings, coupons, leads, feedback, reviews, and customer records.

This module is built as a detachable addon under `modules/AppEmailAutomation`. It owns its routes, migrations, models, Livewire screens, jobs, trigger listeners, templates, plan keys, and pricing registration.

## Features

- Create email automation rules.
- Manage reusable email templates.
- Send customer, business owner, and custom-recipient emails.
- Support immediate and delayed timing.
- Support conditions where available.
- Store email logs for delivery history and troubleshooting.
- Register plan and pricing keys so the addon can be sold separately.

## Portal Menu

The module registers these items under **Automation**:

| Menu | Route name | Path |
| --- | --- | --- |
| Email Automations | `portal.email-automations` | `/portal/email-automation/automations` |
| Email Templates | `portal.email-templates` | `/portal/email-automation/templates` |
| Email Logs | `portal.email-logs` | `/portal/email-automation/logs` |

## Supported Triggers

The provider registers model listeners only when the related module classes exist.

| Event | Example use |
| --- | --- |
| `lead.submitted` | Send lead confirmation and notify business owner |
| `booking.submitted` | Send booking request confirmation |
| `booking.confirmed` | Send confirmed appointment email |
| `booking.cancelled` | Send cancellation notice |
| `booking.completed` | Send review request link |
| `coupon.claimed` | Send coupon code |
| `feedback.submitted` | Send feedback acknowledgement |
| `review.positive` | Send public review request |
| `review.low_score` | Alert business owner |
| `customer.created` | Send welcome email |

## Database Tables

| Table | Purpose |
| --- | --- |
| `lb_email_automations` | Rule configuration, trigger, recipient, timing, template, and status |
| `lb_email_templates` | Reusable email templates |
| `lb_email_automation_logs` | Delivery logs, recipient, subject, status, and errors |

## Plan And Pricing Keys

```text
email_automation
max_email_automations
max_email_templates
emails_per_month
automation_delay
automation_conditions
```

Suggested packaging:

- Starter: disabled or low limits
- Growth: standard rules and templates
- Agency: delayed workflows, conditions, and higher monthly send limit

## Detachable Addon Notes

- Sidebar registration happens inside `AppEmailAutomationServiceProvider`.
- Pricing and plan permission registration happen inside the same provider.
- Trigger listeners use `class_exists` checks before binding to optional modules.
- Removing this module should not break WhatsApp, Webhooks, or core LocalBoost pages.

## Verification

```bash
php artisan migrate --force
php artisan route:list --name=email
php artisan view:cache
```

