# Mailster Kickbox

Contributors: everpress, mailster, xaverb  
Tags: mailster, kickbox, email verification, email validation, deliverability  
Requires at least: 6.0  
Tested up to: 7.1  
Stable tag: 1.2.1  
License: GPLv2 or later

Verify Mailster subscribers in real time with Kickbox email verification to cut bounces and improve email deliverability.

## Description

**Mailster Kickbox** adds real-time [Kickbox](https://kickbox.com) email verification and email validation to the [Mailster Newsletter Plugin for WordPress](https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=Kickbox). When someone subscribes, the address is checked through the Kickbox API before it is saved—so invalid, disposable, and low-quality emails stay out of your list.

Cleaner lists mean fewer hard bounces, stronger sender reputation, and better email deliverability for your Mailster campaigns.

> This plugin requires [Mailster](https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=Kickbox) **4.0** or newer and a [Kickbox](https://kickbox.com) account with an API key. Subscriber email addresses are sent to Kickbox for verification.

### Why use Kickbox email verification with Mailster?

- **Real-time verification on signup** — Kickbox validates the address as soon as someone subscribes through Mailster (forms, admin, and other flows that use Mailster’s subscriber verification).
- **Fewer bounces** — Undeliverable and rejected addresses are blocked before they hurt your reputation.
- **Disposable, role, free & catch-all filtering** — Optionally reject trashmail domains, role accounts (e.g. `info@`, `support@`), free providers, or accept-all (catch-all) domains.
- **Sendex quality score** — Set a minimum [Kickbox Sendex](https://docs.kickbox.com/docs/the-sendex) threshold so only higher-quality addresses are accepted.
- **Rules you control** — Accept or reject based on Kickbox results (`deliverable`, `risky`, `unknown`, `undeliverable`) and detailed reason codes.
- **Custom error messages** — Show clear feedback when an email is not accepted.
- **Configurable timeout** — Adjust how long Kickbox may take to respond (milliseconds).
- **Credit balance in settings** — See remaining Kickbox verification credits from the Mailster settings screen.
- **Optional import checks** — Enable verification during bulk import, or keep imports fast and verify large lists in Kickbox first.

### How it works

1. Install and activate **Mailster Kickbox** alongside Mailster.
2. Enter your Kickbox API key under **Newsletter → Settings → Kickbox**.
3. Adjust acceptance rules if you need stricter or looser filtering.
4. New subscribers are verified automatically via the Kickbox Verify API.

Learn more about verification results in the [Kickbox API documentation](https://docs.kickbox.com/docs/using-the-api) and [result terminology](https://docs.kickbox.com/docs/terminology). Addon overview: [Use Kickbox with Mailster](https://mailster.co/addons/kickbox/).

## Installation

1. Upload the entire `mailster-kickbox-io` folder to the `/wp-content/plugins/` directory, or install from the WordPress plugins screen.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Make sure [Mailster](https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=Kickbox) 4.0+ is installed and active.
4. Go to **Newsletter → Settings → Kickbox** and enter your Kickbox API key.
5. Review the default rules (they work well for most lists) and save.

Create an API key in your [Kickbox dashboard](https://kickbox.com) under API → Manage Keys (enable Verify permissions). Kickbox offers free credits to get started; use a production (`live_`) key for real verification, or a sandbox (`test_`) key while testing.

## Frequently Asked Questions

### Does this plugin work without Mailster?

No. Mailster Kickbox is an addon for the [Mailster Newsletter Plugin](https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=Kickbox) and requires Mailster **4.0** or newer.

### Do I need a Kickbox account?

Yes. You need a Kickbox account and an API key. Kickbox offers free credits to get started; paid plans are available for higher volume.

### When are emails verified?

Addresses are verified when a subscriber is added or updated through Mailster (forms, admin, and other signup flows that use Mailster’s subscriber verification). Bulk import verification is optional and off by default because it can slow large imports — for big lists, use [Kickbox list verification](https://kickbox.com/app/verify) first.

### What can I accept or reject?

You can accept addresses by Kickbox result (`deliverable`, `risky`, `unknown`, `undeliverable`), reject specific reason codes (for example invalid domain or rejected email), and control role, free, disposable, and accept-all (catch-all) addresses. You can also require a minimum Sendex score.

### Will this improve newsletter deliverability?

Verifying subscribers with Kickbox helps keep undeliverable and low-quality addresses out of Mailster, which typically lowers bounce rates and supports better email deliverability over time. Results still depend on your sending practices, content, and ESP.

### Is subscriber data sent to a third party?

Yes. To verify an address, this plugin sends the subscriber’s email to the Kickbox API (`api.kickbox.com`). Kickbox processes the request and returns a verification result. You need a Kickbox account; see [Kickbox’s documentation](https://docs.kickbox.com) for how their service handles data.

### Where can I get support?

For Mailster and this addon, see [mailster.co](https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=Kickbox). For Kickbox API or billing questions, use [Kickbox support and docs](https://docs.kickbox.com).

## Changelog

### 1.2.1

- Requires Mailster 4.0 or newer
- Code quality and WordPress coding standards improvements
- Fixed text domain consistency for translations

### 1.2.0

- Code refactor

### 1.1

- Updated name to Kickbox

### 1.0

- Initial release

## Additional Info

This plugin requires the [Mailster Newsletter Plugin for WordPress](https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=Kickbox) **4.0** or newer and an account at [Kickbox](https://kickbox.com). Subscriber emails are verified via the Kickbox API.
