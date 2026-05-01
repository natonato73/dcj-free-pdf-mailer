# DCJ Free PDF Mailer v1.2.0 引継ぎプロンプト

この内容を新しいChatGPTスレッドへ貼り付けて、続きを進めてください。

## 現在の最新版

- 最新版：v1.2.0
- GitHub Release：作成済み
- 納品用zip：D:\Dev_Projects\dcj-free-pdf-mailer.zip
- 自分用保管zip：D:\Dev_Projects\dcj-free-pdf-mailer-with-sales-materials.zip

GitHub Release URL：

https://github.com/natonato73/dcj-free-pdf-mailer/releases/tag/v1.2.0

## Git状態

- ブランチ：master
- リモート：origin/master
- 不要な開発ブランチは削除済み
- 未追跡は .codex のみ
- .codex は触らない

タグ：

- v1.0.0：初回完成版
- v1.1.0：メール送信診断機能追加版
- v1.1.1：SMTP本番設定メモ追加版
- v1.2.0：配信用CSV出力機能追加版

## 現在の主な完成機能

- 無料PDF申込フォーム表示
- PDFリンク付きメール送信
- 日本語 / 英語PDF設定
- PDF設定の新規登録・編集・削除・複製
- 送信ログ保存
- 送信ログCSV出力
- 購読者リスト管理
- 購読者リストCSV出力
- 配信用CSV出力
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

## v1.2.0で追加した機能

購読者リスト付近に「配信用CSV出力」を追加済み。

内容：

- 購読中のみを対象にしたCSV出力
- 配信停止者を除外
- メール配信サービスへの手動インポート補助
- 既存の購読者リストCSVは管理・バックアップ用として維持
- 既存の送信ログCSVは従来どおり維持

配信用CSVの列：

- メールアドレス
- 言語
- 登録元PDF ID
- 登録元タイトル
- 最終同意日時

本番確認済み：

- 管理画面が開く
- 配信用CSV出力ボタンが表示される
- 配信用CSVがダウンロードできる
- 購読中のみが含まれる
- 配信停止者が含まれない
- 既存の購読者リストCSVが動く
- 送信ログCSVが動く
- 無料PDF送信が動く

## SMTP本番設定

WP Mail SMTP + ConoHa SMTPで本番確認済み。

送信元表示：

Dream Coloring Journey <info@dreamcoloringjourney.com>

設定概要：

- 使用プラグイン：WP Mail SMTP
- メーラー：その他のSMTP
- 送信者名：Dream Coloring Journey
- 送信元メールアドレス：info@dreamcoloringjourney.com
- 送信者名を強制使用：ON
- 送信元メールアドレスを強制使用：ON
- 暗号化：SSL
- SMTPポート：465
- 自動TLS：OFF
- SMTP認証：ON
- SMTPユーザー名：info@dreamcoloringjourney.com
- SMTPパスワードはGitHubやドキュメントに保存しない

## 開発ルール

- masterを直接触らず、必ず作業ブランチを作る
- git add . は使わない
- .codex は触らない
- 変更対象ファイルを明示して git add する
- PHP変更後は php -l を実行する
- LocalWP確認後にコミットする
- 必要に応じて本番確認する
- PRを作成してmasterへマージする
- マージ後にタグ・Release・zip作成を行う
- 納品用zipには sales_materials を含めない
- 自分用保管zipには sales_materials を含める

## 次の開発候補

候補A：言語別配信用CSV

- 日本語購読者CSV
- 英語購読者CSV
- 配信サービスへ言語別に手動インポートしやすくする

候補B：購読者リストUI整理

- ボタンや説明文を整理
- 配信用CSVと管理用CSVの違いをより分かりやすくする

候補C：購読者の手動追加

- 管理画面からメールアドレスを手動追加
- 同意確認、登録元、重複対策が必要

候補D：メール配信サービスAPI連携

- Brevo / Mailchimp等へのAPI登録
- ただし、APIキー管理や配信停止同期が必要なので、まだ急がない

## おすすめの次ステップ

次は「言語別配信用CSV」または「購読者リストUI整理」がおすすめ。

現状の運用方針はレベル2。

無料PDF申込
↓
同意ありだけ購読者リストへ保存
↓
配信用CSV出力
↓
メール配信サービスへ月1回手動インポート

この半自動運用を安定させる方針で進めてください。
