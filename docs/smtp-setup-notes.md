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

- WP Mail SMTP with ConoHa SMTP has been configured on production.
- Production email sending succeeds.
- From display has been confirmed as `Dream Coloring Journey <info@dreamcoloringjourney.com>`.
- The previous Gmail-style display such as delivery through `www228.conoha.ne.jp` has been improved in the confirmed production test.
- Emails may still be placed in spam, junk, or promotions folders depending on the recipient mailbox and sending environment.

This means the plugin can send mail, but delivery quality may still depend on server-side mail configuration.

## 2-1. 本番確認済みSMTP設定

本番環境では、WP Mail SMTP と ConoHa WING のメールアドレスを使って、以下の設定で送信元表示の改善を確認しました。

秘密情報は記録しません。SMTPパスワードは、このドキュメントやGitHubには保存しないでください。

| 項目 | 設定内容 |
| --- | --- |
| 使用プラグイン | WP Mail SMTP |
| メーラー | その他のSMTP |
| 送信者名 | Dream Coloring Journey |
| 送信元メールアドレス | info@dreamcoloringjourney.com |
| 送信元メールアドレスを強制使用 | ON |
| 送信者名を強制使用 | ON |
| SMTPホスト | ConoHa WINGで表示されるSMTPサーバー |
| 暗号化 | SSL |
| SMTPポート | 465 |
| 自動TLS | OFF |
| SMTP認証 | ON |
| SMTPユーザー名 | info@dreamcoloringjourney.com |
| SMTPパスワード | 記録しない。ConoHa WINGで作成したメールアドレスのパスワードを使う |

### 本番確認結果

- WP Mail SMTPのテストメール送信：OK
- DCJ Free PDF Mailerのメール送信診断：OK
- 無料PDFフォームからの実送信：OK
- 差出人表示：`Dream Coloring Journey <info@dreamcoloringjourney.com>`
- 以前のような `www228.conoha.ne.jp 経由` 表示の改善を確認

### 推奨設定まとめ

| 項目 | 推奨設定 | メモ |
| --- | --- | --- |
| 送信者名 | Dream Coloring Journey | 差出人名として表示 |
| 送信元メール | info@dreamcoloringjourney.com | 受信可能なブランド用メール |
| 送信元メールアドレスを強制使用 | ON | WordPress側の送信元を統一しやすくする |
| 送信者名を強制使用 | ON | 差出人名のブレを減らす |
| メーラー | その他のSMTP | ConoHa SMTPを使う場合 |
| SMTPホスト | ConoHa WINGで表示されるSMTPサーバー | 実際の値はConoHa WING画面で確認 |
| 暗号化 | SSL | ポート465とセット |
| SMTPポート | 465 | SSL利用時の一般的な設定 |
| 自動TLS | OFF | SSL+465ではOFF推奨 |
| SMTP認証 | ON | メールアドレスとパスワードで認証 |
| SMTPユーザー名 | info@dreamcoloringjourney.com | ConoHa WINGで作成したメールアドレス |
| SMTPパスワード | ドキュメントに記録しない | 安全な場所で管理する |

### 注意事項

- この設定はメール到達を保証するものではありません。
- メール到達率は、サーバー環境、DNS設定、SMTP設定、受信側メール環境に影響されます。
- 必要に応じてSPF / DKIM / DMARCの確認が必要です。
- SMTPパスワードは、ドキュメントやGitHubに保存しないでください。
- WordPress側とDCJ Free PDF Mailer側の送信者名・送信元メールアドレスは揃えてください。

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
