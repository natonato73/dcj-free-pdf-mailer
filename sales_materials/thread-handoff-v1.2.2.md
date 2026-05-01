# DCJ Free PDF Mailer v1.2.2 引継ぎメモ

この内容を新しいChatGPT / Codexスレッドへ貼り付けて、続きを進めてください。

## 1. プロジェクト概要

DCJ Free PDF Mailer は、WordPress向けの自作プラグインです。

無料PDFの申込フォームを表示し、入力されたメールアドレスへPDFリンク付きメールを送信します。送信ログ、購読者リスト、お知らせ受信同意、配信停止URL、reCAPTCHA v3、メール送信診断、CSV出力までを扱います。

現在の運用方針は、無料PDF申込から購読者リストを作り、必要に応じてメール配信用CSVを出力し、外部メール配信サービスへ手動インポートする半自動運用です。

## 2. 利用環境

- WordPress本番サイトへ反映済み
- WP Mail SMTP + ConoHa SMTPで本番送信確認済み
- 送信元表示：Dream Coloring Journey <info@dreamcoloringjourney.com>
- reCAPTCHA v3対応済み
- 日本語 / 英語PDF設定対応済み
- GitHubリポジトリ：natonato73/dcj-free-pdf-mailer

SMTPパスワード、APIキー、秘密情報はGitHubやドキュメントに保存しないでください。

## 3. 現在の最新版

- 最新版：v1.2.2
- GitHub Release：作成済み
- GitHub Release URL：

```text
https://github.com/natonato73/dcj-free-pdf-mailer/releases/tag/v1.2.2
```

- 本番 WordPress 反映済み
- WordPressプラグイン一覧で Version 1.2.2 表示確認済み
- 本番確認済み

## 4. 成果物フォルダ構成

成果物フォルダ：

```text
D:\Dev_Projects\dcj-free-pdf-mailer\
```

主な成果物：

- 納品用zip：

```text
D:\Dev_Projects\dcj-free-pdf-mailer\dcj-free-pdf-mailer.zip
```

- 自分用保管zip：

```text
D:\Dev_Projects\dcj-free-pdf-mailer\dcj-free-pdf-mailer-with-sales-materials.zip
```

- 最新PHP単体：

```text
D:\Dev_Projects\dcj-free-pdf-mailer\dcj-free-pdf-mailer-current.php
```

- 差分patch：

```text
D:\Dev_Projects\dcj-free-pdf-mailer\dcj-free-pdf-mailer-current-diff.patch
```

- 成果物説明メモ：

```text
D:\Dev_Projects\dcj-free-pdf-mailer\README-artifacts.txt
```

納品用zipには `sales_materials` を含めません。自分用保管zipには販売資料・引継ぎメモを含めます。

## 5. Git状態

- ブランチ：master
- 最新版タグ：v1.2.2
- 既存タグ：v1.0.0 / v1.1.0 / v1.1.1 / v1.2.0 / v1.2.1 / v1.2.2
- GitHub Release v1.2.2 作成済み
- `.codex` は未追跡のまま触らない

新しい作業を始める前に、必ず `git status --short` で差分を確認してください。

## 6. v1.2.1までの主な機能

- 無料PDF申込フォーム表示
- PDFリンク付きメール送信
- 日本語 / 英語PDF設定
- PDF設定の新規登録・編集・削除・複製
- 送信ログ保存
- 送信ログCSV出力
- 購読者リスト管理
- 管理・バックアップ用CSV出力
- メール配信用CSV出力（全言語）
- 日本語メール配信用CSV出力
- 英語メール配信用CSV出力
- メール配信用CSVのファイル名に `all` / `ja` / `en` を付与
- メール配信用CSVは購読中のみを出力
- 配信停止者はメール配信用CSVから除外
- 購読中 / 配信停止切替
- 購読者個別削除
- 配信停止URL
- reCAPTCHA v3
- メール送信診断
- テストメール送信
- 日本語・英語README
- 日本語・英語操作マニュアル
- リリースノート
- 完成チェックリスト
- 販売資料
- 納品メッセージ

## 7. v1.2.2で整理した内容

v1.2.2では、機能追加ではなく、管理画面上の説明文を販売・納品後の運用向けに整理しました。

主な内容：

- 管理画面冒頭の開発中表現を削除
- 管理画面冒頭の説明を分かりやすく変更
- 送信ログCSVの説明文を整理
- メール配信用CSVを使う場面が分かるように説明を改善
- 管理・バックアップ用CSVの説明を「購読者リストの確認・保管用」として明確化
- README、操作マニュアル、リリースノート、販売資料を更新済み

## 8. 本番確認結果

v1.2.2 は本番 WordPress サイトへ反映済みです。

確認日：

- 2026-05-01

確認結果：

- 本番 WordPress へのアップロード完了
- WordPressプラグイン一覧で Version 1.2.2 表示を確認
- DCJ Free PDF Mailer 管理画面の表示確認 OK
- 管理画面冒頭説明文の表示確認 OK
- 送信ログ説明文の表示確認 OK
- 購読者リスト説明文の表示確認 OK
- 無料PDFフォームやCSV出力に大きな異常がないことを確認
- 画面上部に CF7 Apps の外部通知が表示されたが、DCJ Free PDF Mailer の表示ではないことを確認

以上により、v1.2.2 は本番運用可能な状態として確認済みです。

## 9. 開発時のルール

- `.codex` は触らない
- `git add .` は使わない
- 変更対象ファイルを明示して `git add` する
- まだコミットしていない差分がある場合は、先に内容を確認する
- 既存の未追跡ファイルやユーザー変更を勝手に削除・上書きしない
- PHP変更後は `php -l dcj-free-pdf-mailer.php` を実行する
- `includes/` 配下を変更した場合は、変更したPHPファイルも `php -l` で確認する
- LocalWPまたは本番相当環境で管理画面とCSV出力を確認する
- タグ、GitHub Release、zip作成は、明示的に依頼されるまで行わない
- 納品用zipには `.git`、`.codex`、`sales_materials` を含めない
- 自分用保管zipには `sales_materials` を含めてもよい

## 10. 次の改善候補

候補A：購読者の手動追加

- 管理画面からメールアドレスを手動追加
- 同意確認、登録元、重複対策が必要

候補B：メール配信サービスAPI連携

- Brevo / Mailchimp等へのAPI登録
- APIキー管理、同意管理、配信停止同期が必要
- すぐに実装せず、運用が安定してから検討

候補C：配信停止情報の外部サービス同期

- 外部メール配信サービス側の配信停止情報との整合性確認
- 手動運用と自動同期の責任範囲を整理してから検討

候補D：管理画面UI整理

- PDF設定管理画面の見通し改善
- 購読者リストや送信ログの表示整理
- 初心者向けの説明文を増やしすぎず、必要な場所に絞る

候補E：処理の段階的分離

- PDF設定管理処理の分離
- フォーム送信処理の分離
- 将来的な保守性改善

## 11. 新規スレッド開始時の確認コマンド

新しいスレッドで作業を始める場合は、まず次を確認してください。

```bash
git status --short
git log --oneline -5
git tag --list
php -l dcj-free-pdf-mailer.php
```

必要に応じて、v1.2.2 の差分やリリース情報を確認します。

```bash
git show --stat v1.2.2
git --no-pager diff
```

ドキュメント確認用：

```bash
rg -n "v1.2.2|管理画面冒頭|送信ログ説明|購読者リスト説明|メール配信用CSV|管理・バックアップ用CSV" README.md README-ja.md docs sales_materials dcj-free-pdf-mailer.php
```

## 12. 注意事項

- v1.2.2 は機能追加ではなく、管理画面の案内文を整える小改善版です。
- 画面上部に CF7 Apps の外部通知が表示される場合がありますが、DCJ Free PDF Mailer の表示ではありません。
- メール配信用CSVは、メール配信サービスへの手動インポートを補助するためのCSVです。
- メール配信用CSVを使えば安全に配信できる、とは説明しないでください。
- お知らせ配信、販売案内、クーポン案内には、購読中のメールアドレスのみを使います。
- 配信停止者には送らないでください。
- メール配信サービスへインポートする前に、CSV内容、対象言語、同意状況、配信停止者の扱い、配信サービス側の同意管理・配信停止管理を確認してください。
- CSVには個人情報であるメールアドレスが含まれるため、慎重に管理してください。
- WordPress本体、テーマ、他プラグイン、サーバー環境によって追加確認が必要になる場合があります。
- メール到達は保証できません。SMTP、SPF、DKIM、DMARCなどの確認が必要になる場合があります。
- GitHubやドキュメントにSMTPパスワード、APIキー、秘密情報を保存しないでください。
- 次の作業に入る前に、必ず現在のGit状態と未追跡ファイルを確認してください。
