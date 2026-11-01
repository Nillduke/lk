# Enquiry form protection

All public lead POST routes share validation in SiteController: contact, sample, discount,
buying enquiry, customization, TOC and search enquiries. Website/protocol links, bare domain
names and encoded/obfuscated links are rejected in visitor text fields. A normal email address
remains valid in the email field. Phone numbers require 7-15 main digits and may include an
extension. Contact enquiries require company, country and a message of at least 10 characters.
Hidden website_url, url2 and pkjl fields must stay empty. Validation happens before any lead
insert or customer/admin email. Contact submissions are limited to five per minute per IP.

The old Contact Us maths question was JavaScript-only and has been removed. The shared
client validator gives immediate errors; PHP validation remains authoritative if JavaScript
is bypassed. Valid inputs are preserved after rejected submissions.

## Turnstile

Configure Admin > Form protection: `/authuser/settings/forms`. The provided public site key
is prefilled. Enter the matching secret and enable Turnstile. The public site key alone cannot
activate server verification. Only manage_access users may change this configuration.

Enabled forms render the widget with a route-specific action, loading Cloudflare's API once
only on pages that contain an enquiry form. The backend verifies every token with Siteverify
and checks success, configured website hostname and the expected action before saving or
mailing. Missing, expired, reused, incorrect and unavailable verification fail closed.

Configuration is in the existing .env file: TURNSTILE_ENABLED, TURNSTILE_SITE_KEY and
TURNSTILE_SECRET_ENCRYPTED. Secrets entered in the admin page are encrypted with APP_KEY;
TURNSTILE_SECRET_KEY is supported for manual configuration and is cleared when saved through
the admin. The runtime configuration applies even with Laravel config caching. The environment
writer shares the SMTP settings lock, atomically replaces the file and preserves other settings.
Secret inputs and Turnstile responses are excluded from flashed validation input and never logged.

No schema migration, existing lead changes or real verification emails are required.

Reference: https://developers.cloudflare.com/turnstile/get-started/server-side-validation/
