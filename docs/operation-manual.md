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

## 7. Email Body Replacement Tags

The email body can use replacement tags.

Available tags:

```text
{{title}}
{{pdf_url}}
{{terms_text}}
```

Example:

```text
Thank you for requesting {{title}}.

You can download your PDF here:
{{pdf_url}}

Terms:
{{terms_text}}
```

## 8. Editing an Existing PDF Setting

1. Open **DCJ Free PDF**.
2. Find the target PDF setting in the list.
3. Click **編集**.
4. Update the necessary fields.
5. Click **更新**.
6. Confirm the updated content in the list and preview.

The management ID is shown as read-only in the edit form.

## 9. Duplicating a PDF Setting

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

## 10. Deleting a PDF Setting

1. Open **DCJ Free PDF**.
2. Find the target PDF setting.
3. Click **削除**.
4. Confirm the browser confirmation message.

Delete only settings that are no longer used. If a shortcode using that ID remains on a page, the form will no longer work for that shortcode.

## 11. Reading Submission Logs

The **送信ログ** section shows recent send results.

Columns:

- **日時**: Date and time of the send attempt.
- **メールアドレス**: Recipient email address.
- **PDF ID**: PDF setting ID.
- **言語**: Language value saved in the PDF setting.
- **結果**: `success` or `failed`.
- **IPアドレス**: Visitor IP address if available.

The admin screen displays the latest 50 logs. The plugin keeps up to 200 logs.

## 12. Troubleshooting

### Email Does Not Arrive

- Check the submission log.
- Confirm whether the result is `success` or `failed`.
- Check the WordPress mail configuration.
- Consider using authenticated SMTP.

### Email Goes to Spam or Promotions

- Ask the recipient to check spam, junk, or promotions folders.
- Review the sender address and server mail settings.
- Consider SMTP configuration for better delivery.

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

### PDF URL Was Entered Incorrectly

1. Edit the PDF setting.
2. Correct the PDF URL.
3. Save the setting.
4. Test the shortcode form again.

## 13. Production Operation Notes

- Always open the PDF URL in a browser before publishing.
- Send a test email after adding or editing a setting.
- Consider SMTP configuration for production email delivery.
- Delete unnecessary test settings after confirmation.
- Keep management IDs stable after publishing shortcodes.
- Keep backups before plugin updates.
