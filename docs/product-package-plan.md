# Product Package Plan

This is an internal planning memo for possible future packaging, delivery, maintenance, or sale of DCJ Free PDF Mailer.

This document is not a public sales page. It is a working note for organizing what should be included, excluded, checked, and clarified before any commercial use.

## 1. Purpose

The purpose of this document is to organize a future product package structure for DCJ Free PDF Mailer.

Possible future uses include:

- Selling the plugin as a digital product.
- Delivering the plugin to a client.
- Maintaining the plugin as a reusable business tool.
- Preparing documentation and support boundaries.

Current status: pre-sale internal planning.

## 2. Possible Sales or Delivery Models

Potential package models:

- Internal business-use plugin for the site owner.
- Custom delivery for individual clients.
- Template-style plugin for small business owners.
- Future digital product sale.

The current plugin is project-specific but may become a reusable package after documentation, support policy, compatibility checks, and licensing are clarified.

## 3. Items to Include in a Sales Package

Recommended package contents:

- `dcj-free-pdf-mailer.zip`
- `README.md`
- `docs/operation-manual.md`
- `docs/release-checklist.md`
- `docs/smtp-setup-notes.md`
- Optional simple setup guide PDF
- Changelog or update history
- Support scope document

If the package is delivered to a client, include only files needed for installation and operation.

## 4. Items Not to Include in a Sales Package

Do not include:

- `.git`
- `.codex`
- Development diff files
- API keys
- SMTP passwords
- Production site URLs
- Personal email addresses
- Database credentials
- Customer-specific settings
- Temporary test data
- Local-only development notes containing private information

The package should be safe to hand to another party without exposing development or production secrets.

## 5. Features to Mention in Product Descriptions

Main features that can be explained in future product material:

- Free PDF distribution form.
- Email address collection.
- Automatic PDF link email sending.
- Multiple PDF setting management.
- Shortcode placement.
- Japanese and English support.
- Duplicate PDF setting flow.
- Suggested management ID generation.
- Media Library URL selection.
- Submission logs.
- CSV export for logs.
- Log clearing.
- From Name and From Email settings.
- Optional newsletter opt-in checkbox.
- Opt-in status tracking in logs and CSV.
- Subscriber list management.
- Subscriber CSV export.
- Subscriber status management.
- Subscribed / unsubscribed status switching.

Avoid overpromising email delivery. Email arrival depends on hosting, DNS, SMTP, and recipient mailbox settings.

## 6. Items to Decide Before Sale

Before selling or delivering as a product, decide:

- License policy.
- Support period.
- Refund policy.
- Scope of guaranteed operation.
- Supported WordPress versions.
- Supported PHP versions.
- Compatibility policy with themes and other plugins.
- Whether SMTP setup support is included.
- Whether email deliverability support is included.
- Whether installation support is included.
- Whether customization is included or separate.

Important note: customer email deliverability should not be guaranteed, because it depends on the customer's server, DNS, SMTP configuration, sender reputation, and recipient mail provider.

## 7. Draft Notes and Disclaimers

Possible disclaimer points:

- Email delivery depends on server, DNS, SMTP settings, and recipient mailbox environment.
- A directly accessible PDF URL must be configured.
- Some WordPress themes or plugins may require adjustment.
- A production backup should be taken before installation or update.
- SMTP setup may be required depending on the customer's hosting environment.
- The plugin does not replace a dedicated email marketing service.
- Future promotional or newsletter emails should be sent only to users who opted in.
- Operators should send newsletters or promotional emails only to active subscribed contacts.
- Unsubscribed contacts should be excluded from future promotional email use.
- Email address and opt-in CSV data must be handled carefully.
- Compliance requirements depend on the operator's region, use case, and email service.
- The plugin stores settings in WordPress options, not a dedicated custom database table.

These notes should be refined before public sale or client delivery.

## 8. Pricing and Plan Ideas

Concrete pricing is not decided yet.

Possible product or service plans:

- Plugin zip only.
- Plugin zip with operation manual.
- Plugin plus individual setup support.
- Plugin plus customization support.
- Plugin plus maintenance plan.
- Client-specific delivery package.

Possible support variations:

- Installation support.
- Basic configuration support.
- SMTP setup guidance.
- Custom field or UI changes.
- Ongoing maintenance and compatibility checks.

Pricing should be decided after the support scope and license policy are clear.

## 9. Pre-Delivery Checklist

Before delivering a package:

- Confirm zip contents.
- Confirm `.git` is not included.
- Confirm `.codex` is not included.
- Confirm `README.md` is included.
- Confirm required `docs/` files are included.
- Confirm LocalWP testing is complete.
- Confirm basic testing in a production-like environment.
- Confirm email sending test.
- Confirm PDF download test.
- Confirm submission log display.
- Confirm CSV export.
- Confirm no credentials or private site information are included.

Recommended zip check:

```bash
unzip -l dcj-free-pdf-mailer.zip
```

## 10. Possible Future Product Improvements

Potential improvements before or after productization:

- Admin UI cleanup.
- File splitting for maintainability.
- Japanese manual as a PDF.
- English manual.
- reCAPTCHA support.
- Stronger SMTP setup guide.
- License key system research.
- Automatic update system research.
- Import/export of PDF settings.
- More detailed log filters.
- Optional privacy notice templates.
- Compatibility test matrix.

These are candidates only. They should be prioritized based on actual use, support cost, and customer needs.
