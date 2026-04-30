# DCJ Free PDF Mailer Operation Manual

This manual explains how to operate DCJ Free PDF Mailer from the WordPress admin screen.

## 1. Overview

DCJ Free PDF Mailer is a WordPress plugin for distributing free PDF files through email forms.

Basic flow:

1. Create a PDF setting in the admin screen.
2. Place the generated shortcode on a page or post.
3. A visitor enters an email address in the form.
4. WordPress sends an email containing the PDF download URL.
5. The send result is saved in the submission log.

## 2. Registering a New PDF Setting

1. Open the WordPress admin screen.
2. Open **DCJ Free PDF** from the admin menu.
3. In **新規PDF設定を追加**, click **ID候補を生成**.
4. Select the language.
5. Select the category.
6. Enter the title and description.
7. Set the PDF URL.
8. Review the email subject and email body.
9. Confirm that **有効** is checked.
10. Click **追加**.

After saving, confirm that the new item appears in the PDF settings list.

New PDF settings include `{{newsletter_unsubscribe_block}}` in the default email body. Existing PDF settings are not updated automatically. To use this tag in an existing PDF setting, add `{{newsletter_unsubscribe_block}}` manually to the email body.

## 3. Management ID Rules

Management IDs are used in shortcodes.

Examples:

```text
dcj-001-ja
dcj-002-en
```

Recommended format:

```text
dcj-3-digit-number-language
```

Because the management ID is used in shortcodes, avoid changing it after the shortcode has been placed on a live page.

## 4. Setting the PDF URL

Use the **メディアから選択** button next to the PDF URL field to choose a file from the WordPress Media Library.

Important checks:

- The URL must open directly in a browser.
- Test the PDF URL before publishing.
- Avoid URLs under `/wp-content/uploads/dlm_uploads/` if direct access is blocked.
- If the URL returns `Forbidden`, upload the PDF to the standard Media Library and use that URL instead.

## 5. Setting the Thumbnail URL

The thumbnail URL is for future card display or admin preview use.

Current behavior:

- It is not used for email sending.
- It can be left blank.
- It can be selected from the Media Library.

## 6. Using the Shortcode

Copy the shortcode from the PDF settings list.

Example:

```text
[dcj_free_pdf id="dcj-001-ja"]
```

Paste it into:

- A page
- A post
- A custom HTML block
- Any area where WordPress shortcodes are supported

The frontend form also includes an optional newsletter opt-in checkbox. Users can receive the free PDF even if they do not check this box. If checked, the submission is recorded as opted in.

## 7. reCAPTCHA v3 Settings

The free PDF form supports optional reCAPTCHA v3 spam protection.

When reCAPTCHA v3 is enabled, the form submission is verified by Google before the plugin sends the email. If verification fails, the plugin does not send the email, save the submission log, or update the subscriber list.

When reCAPTCHA is disabled, the form works as before.

In **メール送信設定**, you can configure:

- **reCAPTCHA v3を有効にする**: Enable or disable reCAPTCHA v3.
- **Site Key**: The Site Key from Google reCAPTCHA.
- **Secret Key**: The Secret Key from Google reCAPTCHA.
- **Score Threshold**: The minimum score required to accept the submission.

A practical starting threshold is `0.5`. If valid submissions fail, try lowering it to `0.3` and test again.

To use this feature, create keys in the Google reCAPTCHA admin screen and choose **reCAPTCHA v3**. Register the real domain where the form is installed. LocalWP or a different local domain may not pass verification, so it is safer to test existing plugin functions locally with reCAPTCHA disabled and test reCAPTCHA on the production domain.

Common setup issues:

- The key was created for reCAPTCHA v2 instead of v3.
- The Site Key or Secret Key was copied incorrectly.
- The registered domain does not match the site where the form is installed.
- The score threshold is too strict for normal visitors.

## 8. Email Body Replacement Tags

The email body can use replacement tags.

Available tags:

```text
{{title}}
{{pdf_url}}
{{terms_text}}
{{unsubscribe_url}}
{{newsletter_unsubscribe_block}}
```

Example:

```text
Thank you for requesting {{title}}.

You can download your PDF here:
{{pdf_url}}

Terms:
{{terms_text}}

{{newsletter_unsubscribe_block}}
```

`{{unsubscribe_url}}` is replaced with a token-protected unsubscribe URL when it is placed in the email body. If it is not included in the email body, no unsubscribe URL is sent.

`{{newsletter_unsubscribe_block}}` is replaced with unsubscribe guidance only when the visitor opted in to email updates. If the visitor did not opt in, it becomes blank. For Japanese PDF settings, it shows Japanese unsubscribe guidance. For English PDF settings, it shows English unsubscribe guidance.

English replacement example:

```text
Unsubscribe from email updates:
https://...
```

Japanese replacement example:

```text
お知らせメールの配信停止はこちら：
https://...
```

If a visitor only wants the free PDF and does not opt in to email updates, unsubscribe guidance is not shown when you use `{{newsletter_unsubscribe_block}}`. If a visitor opts in, the guidance can be shown in the email.

If `{{unsubscribe_url}}` is placed directly in the email body, the URL is shown regardless of opt-in status. For normal operation, `{{newsletter_unsubscribe_block}}` is recommended because it appears only for opted-in visitors.

When a visitor clicks the unsubscribe URL, the subscriber status changes to `unsubscribed`. This does not prevent the visitor from receiving the requested free PDF. Japanese PDF settings show a Japanese completion screen, and English PDF settings show an English completion screen.

## 9. Editing an Existing PDF Setting

1. Open **DCJ Free PDF**.
2. Find the target PDF setting in the list.
3. Click **編集**.
4. Update the necessary fields.
5. Click **更新**.
6. Confirm the updated content in the list and preview.

The management ID is shown as read-only in the edit form.

## 10. Duplicating a PDF Setting

Use duplicate when creating a similar PDF setting.

1. Open **DCJ Free PDF**.
2. Find the source PDF setting.
3. Click **複製**.
4. The new item form opens with copied values.
5. The management ID is not copied.
6. Click **ID候補を生成** to create a new ID.
7. Review and adjust the copied content.
8. Click **追加**.

The duplicate action only fills the form. It does not save a new setting until **追加** is clicked.

## 11. Deleting a PDF Setting

1. Open **DCJ Free PDF**.
2. Find the target PDF setting.
3. Click **削除**.
4. Confirm the browser confirmation message.

Delete only settings that are no longer used. If a shortcode using that ID remains on a page, the form will no longer work for that shortcode.

## 12. Reading Submission Logs

The **送信ログ** section shows recent send results.

Columns:

- **日時**: Date and time of the send attempt.
- **メールアドレス**: Recipient email address.
- **PDF ID**: PDF setting ID.
- **言語**: Language value saved in the PDF setting.
- **結果**: `success` or `failed`.
- **IPアドレス**: Visitor IP address if available.
- **お知らせ同意**: Whether the user checked the optional newsletter opt-in box.

The admin screen displays the latest 50 logs. The plugin keeps up to 200 logs.

If there are no submission logs, the clear logs button is not displayed. It appears only when at least one submission log exists.

Opt-in status is also included in CSV exports. Use this status carefully if sending future updates, product news, or marketing communications.

### Searching and Filtering Submission Logs

The submission log area includes search and filter controls.

Available controls:

- Email address search
- PDF management ID search
- Newsletter opt-in filter
  - All
  - Opted in
  - Not opted in

The email and PDF ID searches match partial text. For example, searching for `gmail` can show log entries for email addresses that include `gmail`.

The screen shows the total log count and the filtered result count. Use the clear link to reset the search conditions.

The current search and filter controls narrow the currently displayed log range. The same search and filter conditions are also applied to the submission log CSV export.

## 13. CSV Export

Click **送信ログをCSV出力** to download submission logs as a CSV file.

The CSV includes email addresses and newsletter opt-in status. Treat exported files carefully, store them only where needed, and use opt-in status responsibly when planning future updates or promotional messages.

Submission log CSV files are for checking form submission history and records. For future newsletters, product announcements, or coupons, use the subscriber CSV after filtering the subscriber status to subscribed.

CSV files may contain email addresses. Store downloaded CSV files carefully, delete unnecessary CSV files, and mask email addresses if sharing logs or CSV files externally.

## 14. Subscriber List

The **購読者リスト** section is separate from submission logs. Only users who checked the newsletter opt-in box are added to this list.

Saved subscriber fields:

- Email address
- Language
- Source PDF ID
- Source title
- Initial opt-in datetime
- Last seen datetime
- Status

If the same email address opts in again, the existing subscriber record is updated instead of duplicated. The initial opt-in datetime is kept, and the last seen datetime is updated.

Subscriber status can be either:

- `subscribed`: shown as **購読中**
- `unsubscribed`: shown as **配信停止**

Admins can use **配信停止にする** to mark a subscriber as unsubscribed, or **購読中に戻す** to mark the subscriber as subscribed again. If an unsubscribed user submits the free PDF form again with newsletter opt-in checked, the status is set back to subscribed.

Click **購読者リストをCSV出力** to export the subscriber list as a CSV file. Use only subscribed contacts for future newsletters, coupons, or promotional emails. Do not send promotional emails to unsubscribed contacts.

### Searching and Filtering Subscribers

The subscriber list includes search and filter controls.

Available controls:

- Email address search
- Status filter
  - All
  - Subscribed
  - Unsubscribed

The screen shows the total subscriber count and the filtered result count. Use the clear link to reset the search conditions.

The current search and filter controls narrow the currently displayed subscriber list range. The same search and filter conditions are also applied to the subscriber CSV export.

### Deleting Individual Subscribers

Individual subscribers can be deleted from the subscriber list.

1. Open **DCJ Free PDF**.
2. Find the email address in **購読者リスト**.
3. Click **削除** in the action column.
4. Confirm the browser confirmation dialog.

Deleted subscribers cannot be restored. Export the subscriber CSV first if you may need a backup.

Only the subscriber list entry is deleted. Submission logs are not deleted. This is useful for test data cleanup and incorrect registrations, but be careful not to delete real subscribers by mistake.

## 15. File Structure And Distribution Zip

The plugin is composed of the main plugin file and helper files under `includes/`.

Required files:

- `dcj-free-pdf-mailer.php`
- `includes/class-dcj-fpm-admin-notices.php`
- `includes/class-dcj-fpm-csv-exporter.php`
- `includes/class-dcj-fpm-recaptcha.php`
- `includes/class-dcj-fpm-unsubscribe.php`
- `includes/class-dcj-fpm-subscriber-helper.php`
- `includes/index.php`

The `includes/` folder is required. If it is missing from the plugin zip, PHP errors or plugin failure may occur.

Distribution zip files must include the `includes/` folder. Do not include development files such as `.git` or `.codex` in the distribution zip.

This file split keeps the existing behavior while making the plugin easier to maintain.

## 16. Troubleshooting

### Email Does Not Arrive

- Check the submission log.
- Confirm whether the result is `success` or `failed`.
- Check the WordPress mail configuration.
- Consider using authenticated SMTP.

### Email Goes to Spam or Promotions

- Ask the recipient to check spam, junk, or promotions folders.
- Review the sender address and server mail settings.
- Consider SMTP configuration for better delivery.

### Newsletter or Promotional Emails

- The newsletter opt-in checkbox is optional.
- Do not treat a PDF request by itself as consent for future promotional email.
- Use only subscribed email addresses for future updates or marketing messages.
- Do not send promotional emails to unsubscribed contacts.
- Handle exported CSV files carefully because they contain email addresses, opt-in status, and subscriber status.

### PDF Link Shows Forbidden

- Open the PDF URL directly in a browser.
- Avoid protected download paths such as `/wp-content/uploads/dlm_uploads/`.
- Use a standard Media Library PDF URL when possible.

### Form Does Not Appear from the Shortcode

- Confirm the shortcode ID matches an existing PDF setting.
- Confirm the PDF setting is enabled.
- Check for shortcode typos.

### Same Email Address Cannot Resend Immediately

The plugin prevents repeated submissions from the same email address for a short period. Wait a few minutes and try again.

### reCAPTCHA Verification Fails

- Confirm that the Google reCAPTCHA key type is v3.
- Check that the Site Key and Secret Key are correct.
- Confirm that the registered domain matches the production site domain.
- If testing on LocalWP or a different local domain, disable reCAPTCHA for local testing.
- If valid submissions fail, lower the Score Threshold from `0.5` to `0.3` and test again.

### PDF URL Was Entered Incorrectly

1. Edit the PDF setting.
2. Correct the PDF URL.
3. Save the setting.
4. Test the shortcode form again.

## 17. Production Operation Notes

- Always open the PDF URL in a browser before publishing.
- Send a test email after adding or editing a setting.
- Consider SMTP configuration for production email delivery.
- If using reCAPTCHA v3, create keys for the production domain and test form submission on that domain.
- Confirm that the admin screen opens after updating the plugin.
- Confirm submission log CSV export.
- Confirm subscriber CSV export.
- Confirm reCAPTCHA when it is enabled.
- Confirm the unsubscribe URL.
- Confirm subscriber list display and filtering.
- Confirm `{{newsletter_unsubscribe_block}}` behavior when needed.
- Confirm that the update zip includes the `includes/` folder.
- Delete unnecessary test settings after confirmation.
- Keep management IDs stable after publishing shortcodes.
- Keep backups before plugin updates.
- Use only subscribed email addresses for future updates, product announcements, or coupons.
- Do not send updates or promotional emails to unsubscribed contacts.
- Treat email addresses, submission logs, and CSV exports as sensitive operational data.
- Treat subscriber CSV exports carefully and exclude unsubscribed contacts from future promotional email use.
