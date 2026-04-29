# DCJ Free PDF Mailer Development Summary

## Development Environment

- Local development: LocalWP
- Runtime target: WordPress plugin
- Main plugin file: `dcj-free-pdf-mailer.php`
- Repository latest confirmed commit: `3573726 feat: add submission log tracking`

Do not store production admin URLs, email addresses, passwords, API keys, database credentials, or other secrets in this repository.

## Repository Information

- Plugin directory: `dcj-free-pdf-mailer`
- Main option for PDF settings: `dcj_fpm_pdf_items`
- Main option for submission logs: `dcj_fpm_submission_logs`
- Main shortcode: `[dcj_free_pdf id="..."]`

## Current Capabilities

- Register new PDF settings from the admin screen.
- Edit existing PDF settings.
- Delete PDF settings with confirmation and nonce checks.
- Duplicate an existing PDF setting into the new item form.
- Generate suggested management IDs in the new item form.
- Select PDF URLs and thumbnail URLs from the WordPress Media Library.
- Switch default new-form text between Japanese and English.
- Send PDF download links by email.
- Save send results as submission logs.
- Display recent submission logs in the admin screen.

## Production-Checked Items

The following items have been checked on the production site:

- New registration
- Edit/update
- Delete
- Duplicate
- Suggested ID generation
- Media Library selection for PDF URL and thumbnail URL
- Japanese/English default text switching in the new item form
- Email sending
- PDF download from a direct Media Library URL
- Submission log saving and admin display

## Major Features

- PDF settings list with shortcode, language, category, display title, admin note, enabled state, and actions.
- Add/edit forms with sanitized values and escaped output.
- Category labels:
  - `book_image`: 書籍画像
  - `original_image`: 独自画像
  - `practice_material`: 練習教材
  - `other`: その他
- Admin preview form.
- Duplicate link in the list action column.
- ID suggestion format: `dcj-001-ja`, `dcj-002-en`, and so on.
- Submission log columns:
  - datetime
  - email
  - pdf_id
  - lang
  - result
  - ip_address

## Zip Creation Procedure

From the parent directory of the plugin folder:

```bash
zip -r dcj-free-pdf-mailer.zip dcj-free-pdf-mailer \
  -x "dcj-free-pdf-mailer/.git/*" \
  -x "dcj-free-pdf-mailer/.codex/*"
```

Before creating the zip, check that no development-only files or secrets are included.

## Production Update Procedure

1. Confirm the local working tree and review the diff.
2. Create a plugin zip from the clean plugin directory.
3. Upload or replace the plugin on the production WordPress site.
4. Confirm the plugin remains active.
5. Test the admin screen.
6. Test a frontend shortcode form.
7. Confirm email delivery and PDF download.
8. Confirm submission logs are recorded.

## Git Operation Notes

- Do not commit `.codex`.
- Review `git diff` before committing.
- Keep commits focused on one feature or fix.
- Do not commit production credentials or site-specific private information.
- Use `git status --short` before and after work.

## Production Operation Notes

- Use PDF URLs that can be opened directly in a browser.
- Avoid protected download paths such as `/wp-content/uploads/dlm_uploads/` when direct access is blocked.
- Use standard Media Library URLs when possible.
- If email delivery is unreliable, configure authenticated SMTP.
- Ask users to check spam, junk, and promotions folders if emails are not visible.
- Submission logs are stored in WordPress options and capped at 200 entries.

## Possible Future Additions

- CSV export for submission logs.
- Log search and filters.
- Manual log deletion or retention controls.
- Dedicated database table for higher-volume logging.
- More detailed delivery diagnostics.
- Optional admin notices for missing SMTP configuration.
- Import/export of PDF settings.

## Next Thread Start Memo

Project: WordPress custom plugin `DCJ Free PDF Mailer`.

Current confirmed state:

- New registration works.
- Edit/update works.
- Delete works.
- Duplicate works.
- Suggested ID generation works.
- Media Library URL selection works.
- Japanese/English default switching works.
- Email sending works.
- PDF download works when using directly accessible Media Library URLs.
- Submission logging works.

Important constraints:

- Keep changes minimal.
- Do not touch `.codex`.
- Do not commit or push unless explicitly requested.
- Avoid large refactors.
- Preserve existing admin form, save, edit, delete, duplicate, media picker, and mail behavior.
