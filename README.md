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
- Optional newsletter opt-in checkbox.
- Prevent repeated submissions from the same email address for a short period.
- Save recent submission logs and display them in the admin screen.
- Newsletter opt-in status in submission logs and CSV export.

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

## Development Status

Current status:

- Admin add/edit/delete flow is implemented.
- Duplicate flow is implemented.
- Media Library URL picker is implemented.
- Suggested ID generator is implemented.
- Japanese/English default text switching is implemented.
- Email sending is working.
- Submission logging is implemented.
- Newsletter opt-in tracking is implemented.

This plugin is still project-specific and should be reviewed before reuse on unrelated sites.

## Planned License

License is planned as GPL-compatible for WordPress distribution. Final license text should be confirmed before public release.
