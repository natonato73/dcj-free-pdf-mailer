# DCJ Free PDF Mailer 完成チェックリスト

販売・納品・本番更新の前後に確認するチェックリストです。

本番URL、メールアドレス、パスワード、APIキー、データベース情報などの秘密情報は、このファイルに書かないでください。

## 1. Git状態確認

- [ ] `git status --short` を確認した
- [ ] 意図したファイルだけが変更されている
- [ ] `.codex` は未追跡のままコミット対象にしていない
- [ ] `git add .` を使っていない
- [ ] 認証情報、APIキー、本番専用情報をコミットしていない
- [ ] 最新コミットまたは差分が今回のリリース内容と一致している

確認コマンド:

```bash
git status --short
git log --oneline -5
```

## 2. PHP構文確認

- [ ] `dcj-free-pdf-mailer.php` の構文確認を行った
- [ ] `includes/` 配下のPHPファイルの構文確認を行った
- [ ] PHP fatal error が出ていない

確認コマンド:

```bash
php -l dcj-free-pdf-mailer.php
php -l includes/class-dcj-fpm-admin-notices.php
php -l includes/class-dcj-fpm-csv-exporter.php
php -l includes/class-dcj-fpm-recaptcha.php
php -l includes/class-dcj-fpm-unsubscribe.php
php -l includes/class-dcj-fpm-subscriber-helper.php
php -l includes/index.php
```

## 3. 完成済み主要機能の確認

- [ ] 無料PDFフォームが表示される
- [ ] PDFリンク付きメールが送信される
- [ ] PDF設定を新規登録できる
- [ ] PDF設定を編集できる
- [ ] PDF設定を削除できる
- [ ] PDF設定を複製できる
- [ ] 管理ID候補を生成できる
- [ ] 管理ID形式 `dcj-001-ja` / `dcj-002-en` を確認した
- [ ] PDF URLをメディアライブラリから選択できる
- [ ] サムネイルURLをメディアライブラリから選択できる
- [ ] 日本語 / 英語のデフォルト文切替ができる
- [ ] メール送信者名 / 送信元メールアドレスを設定できる
- [ ] メール送信診断セクションが表示される
- [ ] サイトURL / サイトドメインが表示される
- [ ] WordPress管理者メールが表示される
- [ ] 送信元メール情報が表示される
- [ ] 送信元メールアドレス形式チェックを確認した
- [ ] サイトドメインと送信元メールドメインの注意表示を確認した
- [ ] テストメール送信を確認した
- [ ] テストメール成功 / 失敗メッセージを確認した
- [ ] メール到達は保証されない旨を確認した

メール本文置換タグ:

- [ ] `{{title}}` が置換される
- [ ] `{{pdf_url}}` が置換される
- [ ] `{{terms_text}}` が置換される
- [ ] `{{unsubscribe_url}}` が配信停止URL単体に置換される
- [ ] `{{newsletter_unsubscribe_block}}` が同意ありの場合だけ配信停止案内に置換される
- [ ] 同意なしの場合、`{{newsletter_unsubscribe_block}}` は空欄になる

## 4. 送信ログ・購読者管理の確認

- [ ] 送信ログが保存される
- [ ] お知らせ同意あり / なしが送信ログに保存される
- [ ] 送信ログを検索・絞り込みできる
- [ ] 送信ログCSVを出力できる
- [ ] 検索・絞り込み条件が送信ログCSVに反映される
- [ ] 送信ログを全削除できる
- [ ] 送信ログ0件時は全削除ボタンが表示されない
- [ ] お知らせ受信同意チェックが表示される
- [ ] 同意ありの場合、購読者リストに登録される
- [ ] 購読者リストを検索・絞り込みできる
- [ ] 購読者リストCSVを出力できる
- [ ] 検索・絞り込み条件が購読者リストCSVに反映される
- [ ] 購読者ステータスを購読中 / 配信停止に切り替えられる
- [ ] 購読者を1件ずつ削除できる
- [ ] 配信停止後に再度同意ありで送信すると購読中に戻る

## 5. reCAPTCHA・配信停止URLの確認

- [ ] reCAPTCHA v3を有効にして送信確認した
- [ ] reCAPTCHA v3を無効にして従来どおり送信できることを確認した
- [ ] 本番ドメインで取得したSite Key / Secret Keyを使用している
- [ ] LocalWPではreCAPTCHA無効状態で確認した
- [ ] 配信停止URLをクリックすると購読者ステータスが配信停止になる
- [ ] 日本語PDF設定では日本語の配信停止完了画面が表示される
- [ ] 英語PDF設定では英語の配信停止完了画面が表示される
- [ ] `{{newsletter_unsubscribe_block}}` は同意ありの場合だけ配信停止案内を表示する
- [ ] 同意なしの場合は配信停止案内が表示されない

## 6. includes構成の確認

- [ ] `includes/class-dcj-fpm-admin-notices.php` がある
- [ ] `includes/class-dcj-fpm-csv-exporter.php` がある
- [ ] `includes/class-dcj-fpm-recaptcha.php` がある
- [ ] `includes/class-dcj-fpm-unsubscribe.php` がある
- [ ] `includes/class-dcj-fpm-subscriber-helper.php` がある
- [ ] `includes/index.php` がある

## 7. zip作成

- [ ] zipを作成した
- [ ] zip内に `dcj-free-pdf-mailer.php` がある
- [ ] zip内に `includes/` フォルダがある
- [ ] zip内に `docs/` フォルダがある
- [ ] zip内に `README.md` がある
- [ ] zip内に `README-ja.md` がある
- [ ] zip内に `.git` がない
- [ ] zip内に `.codex` がない
- [ ] zip内に不要な一時ファイルや秘密情報がない

zip作成例:

```bash
cd ~/code
rm -f dcj-free-pdf-mailer.zip

zip -r dcj-free-pdf-mailer.zip dcj-free-pdf-mailer \
  -x "dcj-free-pdf-mailer/.git/*" \
  -x "dcj-free-pdf-mailer/.git" \
  -x "dcj-free-pdf-mailer/.codex/*" \
  -x "dcj-free-pdf-mailer/.codex"

unzip -l ~/code/dcj-free-pdf-mailer.zip
```

## 8. 本番更新前確認

- [ ] 本番更新前にバックアップを取った
- [ ] データベースをバックアップした
- [ ] プラグインをバックアップした
- [ ] テーマをバックアップした
- [ ] アップロードファイルをバックアップした
- [ ] 直前に戻せる旧zipを保管した

## 9. 本番アップロード

- [ ] WordPress管理画面を開いた
- [ ] **プラグイン** > **新規追加** > **プラグインのアップロード** を開いた
- [ ] 作成したzipを選択した
- [ ] 既存プラグインを置き換えた
- [ ] プラグインが有効なままであることを確認した
- [ ] 管理画面が開くことを確認した

## 10. 本番更新後チェック

- [ ] 無料PDF送信を確認した
- [ ] メール送信診断セクションを確認した
- [ ] テストメール送信を確認した
- [ ] PDFリンクが開くことを確認した
- [ ] 送信ログCSV出力を確認した
- [ ] 購読者リストCSV出力を確認した
- [ ] 購読者リスト表示を確認した
- [ ] 購読者検索・絞り込みを確認した
- [ ] 購読者ステータス変更を確認した
- [ ] 購読者削除を確認した
- [ ] 配信停止URLを確認した
- [ ] reCAPTCHA有効状態を確認した
- [ ] reCAPTCHA無効状態でも送信できることを確認した
- [ ] `{{newsletter_unsubscribe_block}}` の同意あり / なしの表示を確認した

## 11. 運用上の注意

- [ ] PDF URLはブラウザで直接開けるURLを使っている
- [ ] `/wp-content/uploads/dlm_uploads/` 配下などアクセス制限されるURLを避けている
- [ ] メールアドレス・CSVを個人情報として慎重に扱っている
- [ ] お知らせ配信、販売案内、クーポン案内には購読者リストの「購読中」だけを使っている
- [ ] 配信停止の人には送らない運用になっている
- [ ] 送信ログCSVは履歴確認用として使っている
- [ ] 購読者リストCSVは配信用メールアドレス確認用として使っている
- [ ] 既存PDF設定で `{{newsletter_unsubscribe_block}}` を使う場合は、メール本文へ手動追加している

## 12. ロールバック手順

問題が出た場合:

- [ ] プラグインを停止する
- [ ] 旧zipを再インストールする
- [ ] 必要に応じてバックアップから復元する
- [ ] 本番で長時間の調査を行わず、LocalWPで再現確認する

## 13. リリースメモ

```text
リリース日：
コミットID：
確認環境：
本番バックアップ：
本番確認：
確認者：
メモ：
```
