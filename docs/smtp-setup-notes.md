# SMTP Setup Notes

This document summarizes future SMTP setup considerations for DCJ Free PDF Mailer.

Do not write real SMTP host names, account passwords, API keys, database credentials, or other secrets in this document.

## 1. Overview

DCJ Free PDF Mailer sends email through WordPress `wp_mail()`.

The plugin admin screen can set:

- From Name
- From Email

However, the actual delivery route depends on the WordPress site and server environment. Setting From Name and From Email in the plugin does not replace SMTP authentication.

## 2. Current Confirmed State

Current production behavior:

- Production email sending succeeds.
- Gmail may show a message similar to delivery through the hosting server, such as ConoHa.
- Emails may be placed in spam, junk, or promotions folders depending on the recipient mailbox and sending environment.

This means the plugin can send mail, but delivery quality may still depend on server-side mail configuration.

## 3. Why SMTP Setup May Be Needed

SMTP setup can help:

- Improve sender reliability.
- Reduce spam classification.
- Align the visible From Email with authenticated mail delivery.
- Make production email behavior more predictable.

SMTP is especially recommended when the plugin is used in a public signup flow, campaign, or sales-related path.

## 4. Recommended SMTP Plugin Options

Candidate WordPress SMTP plugins:

- WP Mail SMTP
- FluentSMTP

Use one SMTP plugin at a time to avoid conflicting mail settings.

## 5. Recommended From Settings

Recommended plugin settings:

- From Name: `Dream Coloring Journey`
- From Email: `info@dreamcoloringjourney.com` or `noreply@dreamcoloringjourney.com`

Use a real mailbox or properly configured address on the site's own domain. Avoid using unrelated free-mail addresses as the production sender.

## 6. Before SMTP Setup

Check the following before configuring SMTP:

- A domain email address has been created in the hosting control panel.
- The sender email address can send and receive mail.
- SMTP host, port, encryption method, authentication username, and password are available.
- A production backup has been completed.
- The current plugin version is known and recoverable if rollback is needed.

Do not store SMTP passwords in GitHub.

## 7. SPF, DKIM, and DMARC

Email deliverability may also depend on DNS authentication settings.

- SPF allows specific mail servers to send email for the domain.
- DKIM adds a digital signature to outgoing email.
- DMARC defines a policy based on SPF and DKIM results.

Exact values depend on the mail service, hosting provider, and SMTP provider. Follow the official instructions from ConoHa WING, the selected SMTP plugin, or the selected mail service.

If unsure, check the official provider documentation before changing DNS records.

## 8. Testing After Setup

After SMTP setup:

1. Send a test email to a Gmail account.
2. Confirm whether it arrives in the inbox.
3. Check spam, junk, and promotions folders.
4. Confirm From Name and From Email display correctly.
5. Submit a DCJ Free PDF Mailer form.
6. Confirm the PDF link opens correctly.
7. Confirm the plugin submission log records `success`.

Repeat the test after changing DNS, SMTP, or From settings.

## 9. Troubleshooting

### Email Does Not Arrive

- Check SMTP plugin test results.
- Check the DCJ Free PDF Mailer submission log.
- Confirm the recipient address is correct.
- Check spam, junk, and promotions folders.

### SMTP Authentication Error

- Confirm username and password.
- Confirm SMTP host and port.
- Confirm encryption method.
- Confirm the mailbox exists and is active.

### From Email Is Rewritten

- Check the SMTP plugin's From Email override settings.
- Confirm the SMTP provider allows the configured sender address.
- Use a sender address from the authenticated domain.

### Email Goes to Spam

- Check SPF, DKIM, and DMARC.
- Use a consistent From Email.
- Avoid spam-like subject lines or body text.
- Consider sending through a reputable SMTP provider.

### Hosting Server Route Still Appears

- Confirm the SMTP plugin is active.
- Confirm WordPress test mail is actually sent through SMTP.
- Check plugin conflict or mail override settings.

### PDF Link Shows Forbidden

- Check the PDF URL setting in DCJ Free PDF Mailer.
- Use a URL that opens directly in a browser.
- Avoid protected download paths when direct access is blocked.

## 10. Operation Policy

Current hosting-standard `wp_mail()` sending can work for basic operation.

For serious production use, campaign traffic, or sales-related flows, authenticated SMTP is recommended.

Operational rules:

- Do not save SMTP passwords in GitHub.
- Do not write production credentials in documentation.
- Manage SMTP values in the WordPress admin screen or a secure password manager.
- Keep a backup before changing mail delivery settings.
- Test email sending after every SMTP-related change.
