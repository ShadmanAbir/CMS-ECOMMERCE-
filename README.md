# CMS E-Commerce — White-Label Multi-Site Platform

A white-label e-commerce CMS built on **Laravel 11**. One codebase powers multiple fully-branded storefronts ("sites"), each with its own theme, configuration, and database. Currently ships with two sites: **Raangalay** (Bangladeshi beauty & care) and **Badlab** (bold grooming brand).

## Tech Stack

| Layer      | Technology                                   |
|------------|----------------------------------------------|
| Backend    | PHP 8.4, Laravel 11                          |
| Frontend   | Blade, Tailwind CSS (CDN), Alpine.js         |
| Database   | SQLite (per-site databases)                  |
| Testing    | Pest 3 — **298 tests / 594 assertions**      |
| Payments   | bKash (via PaymentGatewayInterface), Cash on Delivery |

## Architecture (V2)

- **Service layer owns business logic** — controllers only validate, delegate, and respond.
- **Theme-agnostic core** — all branding/config resolved at runtime via `theme()` helper and `ThemeService`.
- **Self-contained sites** — each site lives in `sites/{name}/` with its own `site.php` config, views, CSS, and SQLite database.
- **Key services**: `OrderService`, `PricingService`, `InventoryService`, `CouponService`, `StockAlertService`, `AnalyticsService`, `PaymentGatewayInterface`.

```
sites/
├── _template/     # scaffold for new sites
├── badlab/        # site config + assets + database
└── raangalay/     # site config + assets + database
```

## Features

### ✅ Completed

**Storefront**
- [x] Theme-driven homepage (banners, trending, new arrivals, featured)
- [x] Product catalog with category browsing and detail pages
- [x] Product search + rate-limited live search
- [x] Product variants, batch stock, dynamic per-site product schema
- [x] Cart (guest + authenticated, session merge on login)
- [x] Wishlist with toggle
- [x] Checkout with COD and bKash payment
- [x] Coupon / discount system (fixed, percentage, min-spend, expiry, usage limits)
- [x] Product reviews with images (moderated)
- [x] Customer accounts: profile, order history with status filter + order search
- [x] Order count badge in account navigation

**Admin Panel**
- [x] Sales analytics dashboard (Chart.js: revenue, orders, top products)
- [x] Product CRUD with multi-image upload and search by name/SKU
- [x] Order management: status workflow (pending → processing → shipped → delivered / cancelled), status history, search, status tabs, date-range filter
- [x] Review moderation (approve / reject)
- [x] Coupon management
- [x] Low-stock alerts with auto-resolve on restock
- [x] Scheduled low-stock email digest (daily/weekly, per-site configurable)
- [x] Stock movement audit trail (who restocked, when, how much)
- [x] Banner / content management

**Platform**
- [x] Multi-site theme engine with per-site config, views, CSS, and database
- [x] Superadmin panel for site/theme switching
- [x] Site scaffolding commands (`site:create`, `site:publish`, `site:migrate`)
- [x] Per-site inventory settings (low-stock threshold, digest frequency)
- [x] Guest session handling with scheduled cleanup
- [x] Auto-cancellation of stale pending orders
- [x] Form Requests, Policies, API resources, rate limiting
- [x] 298 Pest tests covering services, controllers, and views

### 🚧 Incomplete / Planned

- [ ] Stock movement history view in the admin product page (data is recorded; UI pending)
- [ ] Bulk product status toggle (activate/deactivate multiple products)
- [ ] Order export to CSV
- [ ] Additional payment gateways (Nagad, SSLCommerz, Stripe)
- [ ] Queued email sending (currently synchronous)
- [ ] Per-site domain routing (currently switched via active theme)
- [ ] Product inventory forecasting / reorder suggestions
- [ ] Customer email notifications on order status changes
- [ ] Multi-language / localization support

## Getting Started

```bash
# 1. Install dependencies
composer install

# 2. Environment
copy .env.example .env
php artisan key:generate

# 3. Database (SQLite)
php artisan migrate --seed

# 4. Serve
php artisan serve
```

Visit `http://127.0.0.1:8000`.

### Scheduled Tasks

Register the Laravel scheduler (cron / Task Scheduler) to enable:

| Command                  | Schedule            | Purpose                          |
|--------------------------|---------------------|----------------------------------|
| `guest-sessions:cleanup` | daily               | purge expired guest sessions     |
| `orders:cancel-pending`  | daily               | auto-cancel stale pending orders |
| `alerts:digest`          | daily / weekly*     | low-stock email digest           |

\* configurable per site via `sites/{site}/site.php` → `inventory.digest_frequency`.

## Testing

```bash
php artisan test
```

## Site Management

```bash
php artisan site:create <name>    # scaffold a new site from _template
php artisan site:publish <name>   # publish site assets
php artisan site:migrate <name>   # run migrations on the site database
```

## License

Proprietary — all rights reserved.
