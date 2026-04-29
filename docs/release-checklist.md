# DCJ Free PDF Mailer Release Checklist

Use this checklist before and after updating the plugin on a production WordPress site.

Do not write production admin URLs, email addresses, passwords, API keys, database credentials, or other secrets in this file.

## 1. Release Assumptions

- Confirm behavior in LocalWP before updating production.
- Confirm the intended changes have been pushed to GitHub.
- Take a production backup before replacing the plugin.
- Keep the previous working zip if available.

## 2. Git Checks

Run:

```bash
git status --short
git log --oneline -5
```

Checklist:

- Confirm the latest commit is the intended release commit.
- `.codex` only as untracked is acceptable.
- Do not use `git add .`.
- Add only intentional files when committing.
- Do not commit credentials or production-only private information.

## 3. LocalWP Checks

Confirm the following in LocalWP:

- Admin screen opens.
- New registration works.
- Edit/update works.
- Delete works.
- Duplicate works.
- Suggested ID generation works.
- Media Library selection works for PDF URL and thumbnail URL.
- Language switching works in the new item form.
- Shortcode displays the form.
- Email sending works in the local test environment.
- PDF download link opens.
- Submission log is recorded and displayed.

## 4. Zip Creation Procedure

Run:

```bash
cd ~/code
rm -f dcj-free-pdf-mailer.zip

zip -r dcj-free-pdf-mailer.zip dcj-free-pdf-mailer \
  -x "dcj-free-pdf-mailer/.git/*" \
  -x "dcj-free-pdf-mailer/.git" \
  -x "dcj-free-pdf-mailer/.codex/*" \
  -x "dcj-free-pdf-mailer/.codex"

unzip -l ~/code/dcj-free-pdf-mailer.zip
cp ~/code/dcj-free-pdf-mailer.zip /mnt/d/Dev_Projects/dcj-free-pdf-mailer.zip
```

## 5. Zip Content Check

Confirm the zip contains:

- `dcj-free-pdf-mailer/`
- `dcj-free-pdf-mailer/dcj-free-pdf-mailer.php`
- Required documentation files if they should be included in the release.

Confirm the zip does not contain:

- `.git`
- `.codex`
- Local-only temporary files
- Secret or credential files

## 6. Production Backup

Before updating, create a production backup with UpdraftPlus or another backup tool.

Include:

- Database
- Plugins
- Themes
- Uploads
- Other WordPress files as needed

Confirm the backup completed before proceeding.

## 7. Production Update Procedure

In WordPress admin:

1. Open **Plugins**.
2. Click **Add New**.
3. Click **Upload Plugin**.
4. Select the plugin zip.
5. Click **Install Now**.
6. Choose the option to replace the existing plugin.
7. Confirm the plugin remains active.

## 8. Post-Update Production Checks

Confirm the following after updating:

- Admin screen opens.
- PDF settings list is displayed.
- New registration works.
- Edit/update works.
- Delete works.
- Duplicate works.
- Suggested ID generation works.
- Media Library selection works.
- Shortcode displays the form.
- Email sending works.
- PDF link opens directly.
- Submission log is recorded and displayed.

Also confirm that existing shortcodes on public pages still work.

## 9. Rollback Plan

If an error appears:

1. Deactivate the plugin.
2. Reinstall the previous working zip if available.
3. Restore from the production backup if needed.
4. Avoid doing extended debugging directly on production.
5. Reproduce the issue in LocalWP before attempting another production update.

## 10. Release Notes

Record release details here:

```text
Update date:
Commit ID:
Checked by:
Production backup completed:
LocalWP check result:
Production check result:
Notes:
```
