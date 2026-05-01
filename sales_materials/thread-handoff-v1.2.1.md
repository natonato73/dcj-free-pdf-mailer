# DCJ Free PDF Mailer v1.2.1 引継ぎメモ

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

- 最新版：v1.2.1
- GitHub Release：作成済み
- GitHub Release URL：

```text
https://github.com/natonato73/dcj-free-pdf-mailer/releases/tag/v1.2.1
```

- 本番 WordPress 反映済み
- WordPressプラグイン一覧で Version 1.2.1 表示確認済み
- 本番確認試験は全てOK

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
- 最新版タグ：v1.2.1
- 既存タグ：v1.0.0 / v1.1.0 / v1.1.1 / v1.2.0 / v1.2.1
- GitHub Release v1.2.1 作成済み
- `.codex` は未追跡のまま触らない

新しい作業を始める前に、必ず `git status --short` で差分を確認してください。

## 6. v1.2.1で追加・整理した内容

v1.2.1では、購読者リスト周りのCSV出力UIを整理し、管理用CSVとメール配信用CSVの違いを分かりやすくしました。

主な内容：

- 「購読者リストをCSV出力」を「管理・バックアップ用CSV出力」に変更
- 「配信用CSV出力」を「メール配信用CSV出力（全言語）」に変更
- 管理・バックアップ用CSVとメール配信用CSVの違いを説明文で明確化
- メール配信用CSV出力（全言語）を維持
- 日本語メール配信用CSV出力を追加
- 英語メール配信用CSV出力を追加
- メール配信用CSVのファイル名に `all` / `ja` / `en` を付与
- メール配信用CSVは購読中のみを出力
- 配信停止者はメール配信用CSVから除外
- README、操作マニュアル、リリースノート、販売資料を更新済み

メール配信用CSVの列：

- メールアドレス
- 言語
- 登録元PDF ID
- 登録元タイトル
- 最終同意日時

## 7. 本番確認結果

v1.2.1 は本番 WordPress サイトへ反映済みです。

確認日：

- 2026-05-01

確認結果：

- 本番 WordPress へのアップロード完了
- プラグイン一覧で Version 1.2.1 表示を確認
- 管理画面の表示確認 OK
- 管理・バックアップ用CSV出力ボタンの表示確認 OK
- メール配信用CSV出力（全言語）の表示確認 OK
- 日本語メール配信用CSV出力の表示確認 OK
- 英語メール配信用CSV出力の表示確認 OK
- CSV出力確認 OK
- 無料PDF送信確認 OK
- 送信ログ確認 OK
- 購読者リスト確認 OK
- メール送信診断確認 OK

以上により、v1.2.1 は本番運用可能な状態として確認済みです。

## 8. 開発時のルール

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

## 9. 次の改善候補

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

## 10. 新規スレッド開始時の確認コマンド

新しいスレッドで作業を始める場合は、まず次を確認してください。

```bash
git status --short
git log --oneline -5
git tag --list
php -l dcj-free-pdf-mailer.php
```

必要に応じて、v1.2.1 の差分やリリース情報を確認します。

```bash
git show --stat v1.2.1
git --no-pager diff
```

ドキュメント確認用：

```bash
rg -n "v1.2.1|メール配信用CSV|管理・バックアップ用CSV|日本語メール配信用CSV|英語メール配信用CSV" README.md README-ja.md docs sales_materials
```

## 11. 注意事項

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
