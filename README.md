# DCJ Free PDF Mailer

DCJ Free PDF Mailer is a WordPress plugin for distributing free PDF files through email signup forms.

It was built for Dream Coloring Journey content, but the plugin structure is intentionally simple: each PDF setting is managed from the WordPress admin screen, and each setting can be displayed on a page or post with a shortcode.

## Overview

This plugin displays a small email form for a selected free PDF. When a visitor submits their email address, WordPress sends an email containing the configured PDF URL.

PDF settings are stored in WordPress options. The plugin does not create a custom database table.

## Main Features

- Manage multiple free PDF settings from the WordPress admin screen.
- Add, edit, delete, and duplicate PDF settings.
- Generate suggested management IDs such as `dcj-001-ja`.
- Select PDF URLs and thumbnail URLs from the WordPress Media Library.
- Switch default text between Japanese and English in the new item form.
- Display a confirmation preview in the admin screen.
- Send PDF download links by email.
- Optional reCAPTCHA v3 spam protection for the free PDF form.
- Add `{{unsubscribe_url}}` to email body text to include a token-protected unsubscribe URL.
- Optional newsletter opt-in checkbox.
- Prevent repeated submissions from the same email address for a short period.
- Save recent submission logs and display them in the admin screen.
- Search and filter the submission log by email address, PDF management ID, and newsletter opt-in status.
- Newsletter opt-in status in submission logs and CSV export.
- Subscriber list management.
- Search and filter subscribers by email address and status.
- Subscriber CSV export.
- Subscriber status management.
- Mark subscribers as subscribed or unsubscribed.
- Delete individual subscribers from the subscriber list.

## Requirements

- WordPress with administrator access.
- A working `wp_mail()` environment.
- PHP version compatible with the active WordPress site.

The plugin has been checked in a LocalWP development environment and on the production site used for this project.

## Installation

1. Copy the plugin folder to `wp-content/plugins/dcj-free-pdf-mailer`.
2. Activate **DCJ Free PDF Mailer** from the WordPress plugins screen.
3. Open **DCJ Free PDF** in the WordPress admin menu.
4. Add a PDF setting.
5. Place the shortcode on a page or post.

## Shortcode Example

```text
[dcj_free_pdf id="dcj-001-ja"]
```

Replace `dcj-001-ja` with the management ID configured in the admin screen.

## Notes About PDF URLs

Use a PDF URL that can be opened directly in a browser.

Do not use URLs under locations that block direct access, such as some download-manager protected paths. For example, URLs under `/wp-content/uploads/dlm_uploads/` may return `Forbidden` depending on the site configuration.

PDF files uploaded through the standard WordPress Media Library are usually suitable, as long as the URL can be opened directly.

## Notes About Email Delivery

This plugin uses WordPress mail sending through `wp_mail()`. Delivery depends on the hosting environment, mail settings, and recipient mail provider.

If a recipient cannot find the email, they should check spam, junk, or promotions folders. For more reliable delivery, configure WordPress to send mail through an authenticated SMTP service.

Newsletter opt-in is optional. Use opt-in status for future updates, product news, or marketing communications only when consent has been obtained, and handle exported email/log data carefully.

Use only active subscribed contacts for future newsletters or promotional emails. Do not send promotional emails to unsubscribed contacts.

The unsubscribe URL is included only when `{{unsubscribe_url}}` is placed in the email body. Clicking that URL changes the subscriber status to unsubscribed, but it does not prevent the visitor from receiving the requested free PDF.

CSV exports include email addresses, so store and share them carefully. The admin search and filter controls narrow the currently displayed list range; CSV exports keep their existing behavior.

reCAPTCHA v3 is optional. To use it, create v3 keys in the Google reCAPTCHA admin screen, register the actual domain where the form is installed, and enter the Site Key, Secret Key, and score threshold in the plugin settings. A threshold of `0.5` is a practical starting point; if valid submissions fail, try a lower value such as `0.3`. When reCAPTCHA is disabled, the form works as before.

## Development Status

Current status:

- Admin add/edit/delete flow is implemented.
- Duplicate flow is implemented.
- Media Library URL picker is implemented.
- Suggested ID generator is implemented.
- Japanese/English default text switching is implemented.
- Email sending is working.
- Optional reCAPTCHA v3 verification is implemented.
- Submission logging is implemented.
- Submission log search/filtering is implemented.
- Newsletter opt-in tracking is implemented.
- Subscriber list management is implemented.
- Subscriber status management is implemented.
- Subscriber search/filtering and unsubscribe URL handling are implemented.

This plugin is still project-specific and should be reviewed before reuse on unrelated sites.

## Planned License

License is planned as GPL-compatible for WordPress distribution. Final license text should be confirmed before public release.
