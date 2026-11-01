# CCAvenue payments

Admin configuration: `/authuser/settings/payments` (`manage_access`). Transaction history and read-only gateway status checks: `/authuser/payments/ccavenue` (`manage_checkouts` or `manage_access`). Secrets are password inputs, excluded from flashed input and encrypted with Laravel APP_KEY in the single server `.env`; never commit them. Settings take effect on every request, including with config cache enabled. Keep APP_KEY stable.

## Activation

1. Enter merchant ID, website access code and matching working key for exactly `https://www.eternityinsights.com`. Choose test or live to match those credentials. The supplied screenshot contains no working key, so initial deployment stays disabled.
2. Report amounts are USD. Enable USD on the CCAvenue merchant account; do not relabel USD prices as INR.
3. Status API access may require separate IP-registered credentials. Optional API access code/key fall back to website credentials. Register the hosting server's actual outbound IP with CCAvenue; it may differ from the website's shared IP.
4. Configure Dynamic Event Notifications to POST to `https://www.eternityinsights.com/payments/ccavenue/notify`. Browser redirect/cancel URLs are generated per attempt. Allow these narrow callback paths through Sucuri if necessary; do not disable the firewall or website-wide CSRF protection.
5. Save, enable, and use CCAvenue's hosted checkout. No real charge was made during development. A genuine sandbox transaction followed by a merchant-approved live transaction is needed to verify the merchant account, USD permissions and bank settlement.

## Behavior and verification

Prices and licenses are read from the database, ignoring submitted amounts. Existing admin-created checkout links retain their saved discount. Report publication and terms acceptance are validated. Orders begin Pending; request and response use CCAvenue's documented AES-128-CBC wire format. The working key never reaches the browser. No card number or CVV is collected or stored.

Payment attempts retain encrypted credential snapshots so browser callbacks can still verify after disabling the gateway or rotating credentials. Static notifications use the current key; reconcile old attempts manually after rotation. Callback validation checks order number, nonce, exact amount and currency without relying on a session cookie. A Success response triggers a separate HTTPS status API lookup. It must match order number, amount, currency and reference. Only successful/shipped API statuses become Success. Failures/cancellations are Failed; network errors, missing status or mismatches remain Pending / Verification pending. No report download is automatically released. Delivery remains the team's existing process.

Transactions can be reconciled by order number even if the buyer closes the browser before returning. Duplicate callbacks are idempotent, and Success cannot be downgraded by a late failure. Live confirmed payments notify the admin (including existing BCC recipients) and customer once; test payments do not send paid-order emails. Each new checkout sends a branded pending-order acknowledgment to the customer and an admin notification with the configured BCC recipients. Customer copies never contain admin BCC recipients or private billing details. SMTP acceptance/failures are logged by order number and audience; an acknowledgment does not claim payment. New checkout submissions accept CCAvenue or Wire Transfer; legacy PayPal callbacks remain available for existing records. The checkout uses a self-hosted logo sourced from https://www.ccavenue.com/images_mcpg/cc-footer-logo.png.

The callback CSRF exception applies only to the CCAvenue response, cancel and notification routes. Billing, admin settings and status rechecks retain normal CSRF protection. Public result URLs are signed and expire in one hour. Logs and saved gateway response fields exclude billing/card data, keys and raw encrypted payloads. Gateway endpoints are hardcoded HTTPS CCAvenue hosts with certificate validation enabled.

Schema: `2026_11_01_000003_add_ccavenue_payments` adds CCAvenue to `checkouts.payment_by` and creates `lr_payment_attempts`. Applied directly on the live database before deployment; mirrored in `database/project-db-update.sql`. Rollback intentionally retains financial history.

CCAvenue-owned store logo, language, SMS/WhatsApp fees, alerts, discounts and settlement preferences remain managed in the merchant dashboard; they are not silently changed by the website settings.

Official references:
- https://links.avenues.info/downloads/CCAvenue_Integration_Ver_3_3.pdf
- https://links.avenues.info/downloads/CCAvenues_API_Vers-1_3.pdf (status API guide; availability can vary)

Current API credentials may verify existing orders when the merchant and environment still match. Callback decryption retains the original website key. Error 31002 rejects currency; the live rejected order had USD 3500. The status API separately returned status=1, Access_code: Invalid Parameter, with only website credentials configured. Both merchant USD activation and correct API credentials require merchant dashboard verification.
