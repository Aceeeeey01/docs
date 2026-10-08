# Santé Barley Philippines

A complete PHP 8+ storefront with separate customer and administrator interfaces. The original Mintlify documentation in the repository is preserved.

## Database choice

The final request specifies **Supabase**, so this application uses **PostgreSQL**, not MySQL. Supabase is PostgreSQL. PHP uses PDO prepared statements against a private direct connection or session pooler. The schema is compatible with Supabase; no Supabase project or credentials were supplied, so the running preview uses a local PostgreSQL database. There is no fake JSON database or frontend-only checkout.

## Requirements

- PHP 8.2+ with `pdo_pgsql`, `mbstring`, `fileinfo`, and sessions.
- Supabase PostgreSQL or PostgreSQL 15+.
- Apache with mod_rewrite (production/XAMPP), or PHP development server (local only).
- Google Fonts and Font Awesome 6 load from their public CDNs.

## Supabase setup

1. Create a Supabase project and copy its direct or **session pooler** PostgreSQL connection settings. Use SSL. PHP needs a private database password, not an anon/publishable API key.
2. Set these server-side environment variables (never expose them to JavaScript or commit credentials):

   ```sh
   export DATABASE_DSN='pgsql:host=YOUR_POOLER_HOST;port=5432;dbname=postgres;sslmode=require'
   export DATABASE_USER='YOUR_DATABASE_USER'
   export DATABASE_PASSWORD='YOUR_DATABASE_PASSWORD'
   ```

   Alternatively, copy the array structure in `config/local.example.php` into ignored `config/local.php`.
3. Run `php scripts/seed.php` from this directory. It applies `database/sante_barley_db.sql` and loads the clearly marked sample catalog. The seed is repeatable and does not reset existing products or orders.
4. Create an admin using a private environment variable:

   ```sh
   ADMIN_PASSWORD='CHOOSE_A_UNIQUE_12_OR_MORE_CHARACTER_PASSWORD' php scripts/create-admin.php admin@example.com
   ```

   There are no built-in accounts or default passwords. Both user and admin login begin at `/login.php`; admins are redirected into `/admin/`.
5. Run `php -S 0.0.0.0:3000 -t . router.php` for local development.

Use a dedicated database role for the application in production with access limited to this application's schema and sequences. Migrations/seed require schema owner permissions. Row-level security is enabled on all tables with **no anonymous policies**: browser Supabase Data API access is blocked. The PHP server enforces authorization and connects as a trusted private role with the required database privileges/RLS access. Do not publish a service key or database password.

## Current sandbox preview

The task has PHP 8.5.9, PostgreSQL 15.18, and an ignored local configuration. The private local PostgreSQL socket is `/home/vercel-sandbox/runtime/sante-pg-socket`.

To restore the local database if stopped:

```sh
pg_ctl -D /home/vercel-sandbox/runtime/sante-postgres -l /home/vercel-sandbox/runtime/sante-postgres.log -o '-k /home/vercel-sandbox/runtime/sante-pg-socket -h 127.0.0.1 -p 5432' start
```

Check health using `pg_isready -h /home/vercel-sandbox/runtime/sante-pg-socket`. Then start PHP from this directory with the router command above. The local database uses trust authentication only within this disposable sandbox; production must use private credentials and encrypted connections.

## XAMPP / Apache

Enable PHP `pdo_pgsql` (and mbstring/fileinfo) in php.ini. XAMPP's bundled MySQL is not used. Connect PHP to Supabase using the private environment variables or ignored local configuration. Configure an Apache virtual host with this folder as its document root, enable mod_rewrite and `AllowOverride All`, and use HTTPS. Paths are relative to the application origin, so use a dedicated virtual host rather than a nested URL prefix. `.htaccess` denies private configuration, scripts, database files, and avatars. Never use PHP's development server in production.

## Features

- Landing page, product details, product search/category/price/sort filtering and pagination.
- PHP session cart with stock validation, quantities, removal, and live toast updates.
- Registration, hashed passwords, rotating sessions, login rate limiting, CSRF protection.
- Profile/photo uploads, password changes, saved address, wishlist, notifications, customer order details.
- Checkout uses server-side totals, inventory row locks, atomic order/payment/item writes, and duplicate-submission protection.
- Admin dashboard, product creation/editing/soft deletion, category management, inventory thresholds, customer deactivation, order state transitions, verified payment records, promotion publication, review moderation, support inbox, CSV reports, shipping settings.
- Product reviews require a delivered purchase and moderation before publication. Ratings are computed from approved reviews; no invented customer reviews.
- Cancellation restores inventory exactly once, prevents reversing a cancellation, and requires a verified refund for paid orders.
- Session shopping cart is canonical; the requested `cart` table is provisioned for future cross-device persistence.
- Profile images are validated JPG/PNG/WebP files, stored outside public access and served only to the authenticated owner. Order details enforce ownership.

## Payments and launch limitations

Cash on Delivery, GCash, and Bank Transfer are order payment selections. GCash/bank transfer are **manual, unpaid workflows**; the store must supply verified payment instructions. No card information is accepted. Admin payment controls record independently verified money movement and do not charge or refund customers. A live gateway is not configured.

This is an independent demo storefront, not an official Santé store. Product illustrations are original SVG artwork; prices, pack sizes, inventory, and product descriptions are sample data. Before selling, replace/verify catalog information against authorized sources, obtain image/brand permissions, configure a real merchant workflow, verify fulfillment, publish operational/legal/privacy policies, set HTTPS and secure session configuration (including trusted reverse-proxy HTTPS handling), configure backups/log retention and appropriate production rate limits. Registration and order updates are in-app; outbound email is not configured.

No ingredients, dosage, certifications, medical treatment results, or weight-loss guarantees are invented. Every product points to its actual label and professional advice.

## Image attribution

`assets/images/banners/countryside.jpg`: royalty-free landscape from Unsplash, source https://images.unsplash.com/photo-1500382017468-9049fed747ef, used under the [Unsplash License](https://unsplash.com/license). Product illustrations are original project artwork, not copied official photography or packaging. Brand names identify the requested demonstration concept and do not imply endorsement.

## Validation

```sh
find . -name '*.php' -not -path './var/*' -print0 | xargs -0 -n1 php -l
```

The task's validation record is outside the repository at `workspace-context/validation-record.md` and includes actual browser/integration results. No production Supabase connection or payment gateway was available to test.
