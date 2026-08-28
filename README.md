# Member On Desk

Multi-tenant gym and library management SaaS built with Laravel 12, PHP 8.2+, MySQL, Blade, and Sanctum APIs.

## Roles

- **Super admin** — businesses, owners, SaaS plans, platform revenue and reports
- **Business owner** — members, plans, subscriptions, QR attendance, cash/UPI payments, WhatsApp reminders

Tenant data is isolated with `business_id`, global scopes, middleware, and policies.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Run the queue worker for WhatsApp jobs:

```bash
php artisan queue:work
```

Daily expiry/payment reminders are scheduled at 09:00:

```bash
php artisan reminders:send
```

## Demo accounts

| Role | Email | Password |
| --- | --- | --- |
| Super admin | leo.a@example.org | password |
| Gym owner | zoe.m@example.net | password |
| Library owner | yosef.c@example.com | password |

SaaS plans: ₹499 / month and ₹4,999 / year (separate from member collections).

## Android API

Base URL: `/api/v1`

- `POST /auth/login`
- `GET /members` `POST /members`
- `GET /plans`
- `GET /subscriptions` `POST /subscriptions`
- `GET /attendance` `POST /attendance` (token or member_id)
- `GET /payments` `POST /payments`
- `GET /payment-requests` `POST /payment-requests`
- `POST /webhooks/razorpay` (signature required, never trust the client)

Use `Authorization: Bearer {token}`.
# memberondesk
