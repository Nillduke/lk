# Eternity Insights Laravel website

Laravel application for the public research-report website and administration.
Local website: http://localhost:8098/
Admin login: http://localhost:8098/auth/v/login

## Repository scope

This repository contains the Laravel application only. It excludes the old PHP
project, dependencies, uploaded media, database dumps, the private media archive,
credentials, logs, caches, test screenshots, unused legacy assets and old CSS builds.
Required website images, source styles/scripts, fonts and current compiled styles
are included. Uploaded media and database backups must be transferred separately.

Never commit `.env` or `storage/app/local-admin.json`. Use `.env.example` as the
credential-free template. All database, SMTP and provider settings belong in `.env`.

## Install on another computer

Requirements: PHP 8.3+, Composer, Node 22.12+ with npm, and MariaDB.
PHP needs the extensions required by Laravel plus GD, ZIP, DOM/XML and XMLWriter.

1. Run `composer install`.
2. Copy `.env.example` to `.env`, enter the database connections, then run
   `php artisan key:generate`.
3. Run `npm ci`. This reinstalls the pinned, self-hosted TinyMCE Community assets
   under `public/vendor/tinymce`; no cloud API key or premium plugins are used.
4. Restore the existing `ei_db` and `reportdb` databases from your private backups.
   Run `php artisan migrate` to apply the Laravel support tables and indexes.
   The SQL alternative is `database/project-db-update.sql` for the existing primary
   database. This update is not a complete database dump.
5. Restore `public/uploads` and/or the private `storage/app/legacy-media.zip`
   separately. Legacy media not already extracted requires this archive.
6. Configure SMTP, payment and other optional providers in `.env` as needed.

Do not run `migrate:fresh`, `migrate:refresh` or `db:wipe` against the existing
catalogue. Laravel records migrations in `lr_migrations`, independently of the
legacy `migrations` table.

## Run locally

For the existing Windows WAMP setup:

```powershell
powershell -ExecutionPolicy Bypass -File .\tools\start-local.ps1
```

The script expects WAMP PHP at `C:\wamp64\bin\php\php8.3.6\php.exe`, and starts
port 8098. For other installations, adjust the PHP path or use:

```sh
php -S 127.0.0.1:8098 -t public tools/server.php
```

For Apache, set the document root to `public` and enable the supplied `.htaccess`.
Do not expose the project root, `.env`, storage, or private media archive.

## Website and admin features

- Report catalogue, industry categories, detail pages, news, CMS pages and search.
- Shared responsive layout, report sticky header and enquiry/checkout forms.
- Self-hosted TinyMCE with image uploads for report, news and CMS content.
- Segment and company fields plus license/price rows with Add/Remove controls.
- New report defaults: Single User USD 3,500; Multi User USD 4,800;
  Corporate User USD 7,200. Existing prices remain editable.
- CSV/XLSX import with safe partial updates, template/TOC generation, generated SVG
  images, optional custom image URLs and explicit regeneration of existing content.
- Automatic `/sitemap.xml` index with at most 2,500 URLs per child sitemap.
- Role-based administration, orders, enquiries, categories, publishers and users.

## Frontend assets

Current compiled CSS and optimized background images are committed, so no build is
required to display the website after installation.

After modifying the public styles, run from this directory:

```sh
npm run assets:build
npm run assets:critical
```

The critical CSS build needs the local website and Chrome. Set `BASE_URL` or
`CHROME_PATH` when using a different URL or browser installation. TinyMCE can be
reinstalled independently with `npm run assets:tinymce` after `npm ci`.

Keep the CSS files referenced by `public/frontend/dist/manifest.json` and
`critical-manifest.json` in Git. Ignore obsolete generated versions when replacing
them. Do not commit generated upload-image caches from `public/frontend/optimized`;
only the shared `background-*.webp` assets belong in Git.

## Verification

```sh
php -d xdebug.mode=off vendor/phpunit/phpunit/phpunit --colors=never
```

Tests use transactions and require a dedicated restored test database plus the
local admin account. Never run them against production. Private account setup,
media and database restores are intentionally outside Git.


### Admin SMTP settings

Administrators with `manage_access` permission can open `/authuser/settings/email` (sidebar: SMTP settings). Set the delivery method, host, port, security, credentials, sender and enquiry recipient. Save first, then send a test to an address you control. STARTTLS requires TLS; SSL/TLS uses an implicit TLS connection. Log mode does not deliver email.

Settings remain in the application `.env` alongside other connections. After the first admin save, mail settings are loaded on each application boot, including when Laravel configuration is cached. Passwords are encrypted with `APP_KEY`; a blank password preserves the existing credential. Keep `APP_KEY` stable. No database migration is needed. Existing mail behavior stays unchanged until settings are saved. Persistent queue workers must be restarted after settings changes (the current deployment uses synchronous mail).
