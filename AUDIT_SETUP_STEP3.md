# Step 3: UI Consistency + Responsiveness Audit Setup

## 1. Baseline Ready

- `DatabaseSeeder` is the master seed entry.
- `.env` backup created as `.env.backup`.
- `.env.backup` is already present in `.gitignore`.

## 2. Freeze Dataset Per Audit Run

Run this before every audit cycle so page content stays deterministic:

```bash
php artisan optimize:clear
php artisan migrate:fresh --seed
php artisan storage:link
npm run build
```

Optional runtime profile for UI-only checks:

- `APP_DEBUG=false`
- `MAIL_MAILER=log`
- `QUEUE_CONNECTION=sync` (unless you are explicitly validating realtime queue/broadcast flow)

## 3. Target Breakpoints (Must Test)

Use these exact widths in browser devtools device mode:

1. `360x800` - small Android baseline
2. `390x844` - iPhone 12/13/14 baseline
3. `414x896` - large phone baseline
4. `768x1024` - tablet portrait
5. `1024x768` - tablet landscape / small laptop
6. `1280x800` - standard laptop
7. `1440x900` - desktop baseline

## 4. Browser Matrix

Minimum coverage:

1. Chrome (latest)
2. Safari (latest stable on macOS)
3. Firefox (latest)

## 5. Pass/Fail Rules

A page fails if any of the following happen:

1. Horizontal scroll appears on main content.
2. Text/button/card overlap occurs.
3. Primary action is hidden or clipped.
4. Sidebar/topnav/menu breaks layout.
5. Table or form becomes unusable on mobile.

## 6. Major Pages Scope

Audit these flows at all breakpoints:

1. Guest: home, category, product details, cart, checkout, login, register.
2. Customer: dashboard, order list, order details, profile, settings, addresses, payment methods, wishlist.
3. Admin: dashboard, products, categories, brands, orders, payments, hero/settings.

## 7. Defect Logging Format

Capture each issue in this format:

1. Severity: `P0` / `P1` / `P2`
2. Page + Route
3. Breakpoint + Browser
4. Repro steps
5. Expected vs Actual
6. Screenshot/video
7. Proposed fix owner

## 8. Exit Criteria for Step 3

1. Zero `P0` issues.
2. Zero overflow/overlap defects on major pages.
3. Core user and admin flows pass on mobile and desktop.
4. Remaining `P1/P2` items have actionable fix list.
