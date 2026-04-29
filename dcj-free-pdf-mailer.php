<?php
/**
 * Plugin Name: DCJ Free PDF Mailer
 * Plugin URI: https://dreamcoloringjourney.com/
 * Description: Dream Coloring Journey の無料PDF配布フォーム用プラグインです。ショートコードIDごとに無料PDFメールを送信します。
 * Version: 0.3.0
 * Author: 名富企画
 * Author URI: https://dreamcoloringjourney.com/
 * License: GPL2
 * Text Domain: dcj-free-pdf-mailer
 * Domain Path: /languages
 */

// 直接ファイルアクセスを防ぐ
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DCJ Free PDF Mailer メインクラス
 *
 * 第3段階では、ショートコードIDごとに
 * PDF URL・件名・本文・表示メッセージを切り替えます。
 */
class DCJ_Free_PDF_Mailer {

	/**
	 * プラグイン定数
	 */
	const VERSION                = '0.3.0';
	const PLUGIN_SLUG            = 'dcj-free-pdf-mailer';
	const CSS_PREFIX             = 'dcj-fpm-';
	const NONCE_ACTION           = 'dcj_free_pdf_submit';
	const NONCE_NAME             = 'dcj_free_pdf_nonce';
	const DUPLICATE_CHECK_EXPIRE = 300; // 5分（秒）
	const OPTION_PDF_ITEMS       = 'dcj_fpm_pdf_items';

	/**
	 * PDFIDごとの処理結果メッセージ
	 *
	 * @var array
	 */
	private static $messages = array();

	/**
	 * コンストラクタ
	 */
	public function __construct() {

		// フォーム送信処理
		add_action( 'init', array( $this, 'handle_form_submit' ) );

		// ショートコード登録
		add_shortcode( 'dcj_free_pdf', array( $this, 'render_form' ) );

		// CSSと点滅アニメーションを出力
		add_action( 'wp_head', array( $this, 'output_styles' ) );

		// 管理画面メニュー登録
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );

		// 管理画面のメディアライブラリ選択
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_media' ) );
		add_action( 'admin_footer', array( $this, 'output_admin_media_script' ) );
	}

	/**
	 * 無料PDFコンテンツ設定を取得します。
	 *
	 * WordPress option から保存されたPDF設定を取得します。
	 * option が未設定または無効な場合は、デフォルト設定を返します。
	 *
	 * @return array
	 */
	private function get_pdf_items() {

		$items = get_option( self::OPTION_PDF_ITEMS );

		// option が存在し、配列であることを確認
		if ( is_array( $items ) && ! empty( $items ) ) {
			return $items;
		}

		// option が未設定または無効な場合、デフォルト設定を返す
		return self::get_default_pdf_items();
	}

	/**
	 * デフォルト無料PDFコンテンツ設定を取得します。
	 *
	 * @return array
	 */
	public static function get_default_pdf_items() {

		return array(

			'christmas-preschool-ja' => array(
				// 基本情報
				'id'                => 'christmas-preschool-ja',
				'lang'              => 'ja',
				'enabled'           => true,
				'type'              => 'set',
				'category'          => 'christmas',
				'audience'          => 'preschool',
				'sort_order'        => 10,

				// 表示用情報
				'title'             => 'クリスマス塗り絵 無料PDF',
				'description'       => '幼児向けのクリスマス塗り絵PDFを、メールで無料プレゼントします。',
				'audience_label'    => '幼児向け・3〜6歳',
				'volume_label'      => 'PDF 5枚セット',
				'thumbnail_url'     => '',

				// 配信設定
				'pdf_url'           => 'https://dreamcoloringjourney.com/wp-content/uploads/sample/christmas-preschool-ja.pdf',
				'placement_type'    => 'official_freebie_page',
				'delivery_method'   => 'email',
				'migration_status'  => 'converted',

				// メール設定
				'mail_subject'      => '【Dream Coloring Journey】無料PDFダウンロードリンクのご案内',
				'mail_body'         => "こんにちは。\n\nDream Coloring Journey の無料PDFにお申し込みいただき、ありがとうございます。\n\n以下のリンクからPDFをダウンロードできます。\n\n{{pdf_url}}\n\n塗り絵の時間を楽しんでいただければ嬉しいです。\n\nDream Coloring Journey",

				// 利用規約
				'terms_type'        => 'personal_use_only',
				'terms_text'        => '家庭内での個人利用に限ります。再配布・二次配布・商用利用は禁止です。',

				// メッセージ
				'success_message'   => '無料PDFのご案内メールを送信しました。メールボックスをご確認ください。',
				'duplicate_message' => 'すでにお申し込み済みです。メールボックスをご確認ください。',
				'disabled_message'  => 'この無料PDFは現在配布を停止しています。',

				// その他
				'source_page_url'   => '',
				'kdp_asin'          => '',
				'kdp_title'         => '',
				'kdp_url'           => '',
				'admin_note'        => '正式無料DLページ用。幼児向けクリスマス無料PDF。',
			),

			'christmas-preschool-en' => array(
				// 基本情報
				'id'                => 'christmas-preschool-en',
				'lang'              => 'en',
				'enabled'           => true,
				'type'              => 'set',
				'category'          => 'christmas',
				'audience'          => 'preschool',
				'sort_order'        => 20,

				// 表示用情報
				'title'             => 'Free Christmas Coloring PDF',
				'description'       => 'Enter your email address to receive a free Christmas coloring PDF for young children.',
				'audience_label'    => 'Preschool / Ages 3–6',
				'volume_label'      => '5-page PDF set',
				'thumbnail_url'     => '',

				// 配信設定
				'pdf_url'           => 'https://dreamcoloringjourney.com/wp-content/uploads/sample/christmas-preschool-en.pdf',
				'placement_type'    => 'official_freebie_page',
				'delivery_method'   => 'email',
				'migration_status'  => 'converted',

				// メール設定
				'mail_subject'      => 'Your Free PDF Download Link from Dream Coloring Journey',
				'mail_body'         => "Hello,\n\nThank you for requesting a free PDF from Dream Coloring Journey.\n\nYou can download your PDF from the link below:\n\n{{pdf_url}}\n\nWe hope you enjoy your coloring time.\n\nDream Coloring Journey",

				// 利用規約
				'terms_type'        => 'personal_use_only',
				'terms_text'        => 'For personal and family use only. Redistribution, resale, and commercial use are not allowed.',

				// メッセージ
				'success_message'   => 'Your free PDF email has been sent. Please check your inbox.',
				'duplicate_message' => 'You have already requested this PDF. Please check your inbox.',
				'disabled_message'  => 'This free PDF is currently unavailable.',

				// その他
				'source_page_url'   => '',
				'kdp_asin'          => '',
				'kdp_title'         => '',
				'kdp_url'           => '',
				'admin_note'        => 'English version for the official free download page.',
			),

			'new-year-kids-ja' => array(
				// 基本情報
				'id'                => 'new-year-kids-ja',
				'lang'              => 'ja',
				'enabled'           => true,
				'type'              => 'set',
				'category'          => 'new-year',
				'audience'          => 'kids',
				'sort_order'        => 30,

				// 表示用情報
				'title'             => 'お正月塗り絵 無料PDF',
				'description'       => '子ども向けのお正月塗り絵PDFを、メールで無料プレゼントします。',
				'audience_label'    => '子ども向け・小学生向け',
				'volume_label'      => 'PDF 5枚セット',
				'thumbnail_url'     => '',

				// 配信設定
				'pdf_url'           => 'https://dreamcoloringjourney.com/wp-content/uploads/sample/new-year-kids-ja.pdf',
				'placement_type'    => 'official_freebie_page',
				'delivery_method'   => 'email',
				'migration_status'  => 'converted',

				// メール設定
				'mail_subject'      => '【Dream Coloring Journey】お正月塗り絵PDFのご案内',
				'mail_body'         => "こんにちは。\n\nDream Coloring Journey のお正月塗り絵PDFにお申し込みいただき、ありがとうございます。\n\n以下のリンクからPDFをダウンロードできます。\n\n{{pdf_url}}\n\n楽しい塗り絵時間をお過ごしください。\n\nDream Coloring Journey",

				// 利用規約
				'terms_type'        => 'personal_use_only',
				'terms_text'        => '家庭内での個人利用に限ります。再配布・二次配布・商用利用は禁止です。',

				// メッセージ
				'success_message'   => 'お正月塗り絵PDFのご案内メールを送信しました。メールボックスをご確認ください。',
				'duplicate_message' => 'すでにお申し込み済みです。メールボックスをご確認ください。',
				'disabled_message'  => 'この無料PDFは現在配布を停止しています。',

				// その他
				'source_page_url'   => '',
				'kdp_asin'          => '',
				'kdp_title'         => '',
				'kdp_url'           => '',
				'admin_note'        => '正式無料DLページ用。子ども向けお正月無料PDF。',
			),

		);
	}

	/**
	 * 指定IDのPDF設定を取得します。
	 *
	 * @param string $pdf_id PDF識別ID
	 * @return array|null
	 */
	private function get_pdf_item( $pdf_id ) {

		$items = $this->get_pdf_items();

		if ( isset( $items[ $pdf_id ] ) ) {
			return $items[ $pdf_id ];
		}

		return null;
	}

	private function get_category_options() {
		return array(
			'book_image'        => '書籍画像',
			'original_image'    => '独自画像',
			'practice_material' => '練習教材',
			'other'             => 'その他',
		);
	}

	/**
	 * フォーム送信を処理します。
	 */
	public function handle_form_submit() {

		// このプラグインのフォーム送信でなければ何もしない
		if ( empty( $_POST['dcj_fpm_submit'] ) ) {
			return;
		}

		// nonce が存在するか確認
		if ( empty( $_POST[ self::NONCE_NAME ] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) );

		// nonce チェック
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			return;
		}

		// PDF ID取得
		$pdf_id = '';
		if ( ! empty( $_POST['dcj_pdf_id'] ) ) {
			$pdf_id = sanitize_key( wp_unslash( $_POST['dcj_pdf_id'] ) );
		}

		if ( empty( $pdf_id ) ) {
			self::$messages[ $pdf_id ] = $this->get_error_message( '無料PDFのIDが確認できませんでした。' );
			return;
		}

		// PDF設定取得
		$pdf_item = $this->get_pdf_item( $pdf_id );

		if ( empty( $pdf_item ) ) {
			self::$messages[ $pdf_id ] = $this->get_error_message( '指定された無料PDFが見つかりませんでした。' );
			return;
		}

		// 無効化されている場合
		if ( empty( $pdf_item['enabled'] ) ) {
			$disabled_message = ! empty( $pdf_item['disabled_message'] ) ? $pdf_item['disabled_message'] : 'この無料PDFは現在配布を停止しています。';
			self::$messages[ $pdf_id ] = $this->get_error_message( $disabled_message );
			return;
		}

		// メールアドレス取得
		$email = '';
		if ( ! empty( $_POST['dcj_email'] ) ) {
			$email = sanitize_email( wp_unslash( $_POST['dcj_email'] ) );
		}

		// メール形式チェック
		if ( empty( $email ) || ! is_email( $email ) ) {
			self::$messages[ $pdf_id ] = $this->get_error_message( '正しいメールアドレスを入力してください。' );
			return;
		}

		// 重複送信防止チェック（5分以内）
		$duplicate_key = md5( $pdf_id . $email );
		if ( get_transient( 'dcj_fpm_sent_' . $duplicate_key ) ) {
			$duplicate_message = ! empty( $pdf_item['duplicate_message'] ) ? $pdf_item['duplicate_message'] : 'すでにお申し込み済みです。メールボックスをご確認ください。';
			self::$messages[ $pdf_id ] = $this->get_error_message( $duplicate_message );
			return;
		}

		// 件名
		$subject = ! empty( $pdf_item['mail_subject'] ) ? $pdf_item['mail_subject'] : '無料PDFダウンロードリンクのご案内';

		// 本文
		$body_template = ! empty( $pdf_item['mail_body'] ) ? $pdf_item['mail_body'] : "{{pdf_url}}";
		$pdf_url       = ! empty( $pdf_item['pdf_url'] ) ? esc_url_raw( $pdf_item['pdf_url'] ) : '';

		// 置換用タグの準備
		$search_tags = array(
			'{{pdf_url}}',
			'{{title}}',
			'{{audience_label}}',
			'{{volume_label}}',
			'{{terms_text}}',
			'{{kdp_asin}}',
			'{{kdp_title}}',
			'{{kdp_url}}',
		);

		$replace_values = array(
			$pdf_url,
			! empty( $pdf_item['title'] ) ? $pdf_item['title'] : '',
			! empty( $pdf_item['audience_label'] ) ? $pdf_item['audience_label'] : '',
			! empty( $pdf_item['volume_label'] ) ? $pdf_item['volume_label'] : '',
			! empty( $pdf_item['terms_text'] ) ? $pdf_item['terms_text'] : '',
			! empty( $pdf_item['kdp_asin'] ) ? $pdf_item['kdp_asin'] : '',
			! empty( $pdf_item['kdp_title'] ) ? $pdf_item['kdp_title'] : '',
			! empty( $pdf_item['kdp_url'] ) ? esc_url_raw( $pdf_item['kdp_url'] ) : '',
		);

		$body = str_replace( $search_tags, $replace_values, $body_template );

		$headers = array(
			'Content-Type: text/plain; charset=UTF-8',
		);

		$sent = wp_mail( $email, $subject, $body, $headers );

		if ( $sent ) {
			// 送信成功後、重複送信防止フラグを5分間保存
			set_transient( 'dcj_fpm_sent_' . $duplicate_key, 1, self::DUPLICATE_CHECK_EXPIRE );

			$success_message = ! empty( $pdf_item['success_message'] ) ? $pdf_item['success_message'] : '無料PDFのご案内メールを送信しました。';
			self::$messages[ $pdf_id ] = $this->get_success_message( $success_message );
		} else {
			self::$messages[ $pdf_id ] = $this->get_error_message( 'メール送信に失敗しました。Localのメール設定を確認してください。' );
		}
	}

	/**
	 * ショートコードからフォームを表示します。
	 *
	 * 使用例：
	 * [dcj_free_pdf id="christmas-preschool-ja"]
	 *
	 * @param array $atts ショートコード属性
	 * @return string フォームHTML
	 */
	public function render_form( $atts ) {

		$atts = shortcode_atts(
			array(
				'id' => '',
			),
			$atts,
			'dcj_free_pdf'
		);

		// id が未指定の場合はエラー表示
		if ( empty( $atts['id'] ) ) {
			return $this->get_error_message( '無料PDFのIDが指定されていません。' );
		}

		// 管理IDとして使うため sanitize_key を使用
		$pdf_id = sanitize_key( $atts['id'] );

		// PDF設定取得
		$pdf_item = $this->get_pdf_item( $pdf_id );

		if ( empty( $pdf_item ) ) {
			return $this->get_error_message( '指定された無料PDFが見つかりませんでした。' );
		}

		if ( empty( $pdf_item['enabled'] ) ) {
			$disabled_message = ! empty( $pdf_item['disabled_message'] ) ? $pdf_item['disabled_message'] : 'この無料PDFは現在配布を停止しています。';
			return $this->get_error_message( $disabled_message );
		}

		return $this->get_form_html( $pdf_id, $pdf_item );
	}

	/**
	 * フォームHTMLを生成します。
	 *
	 * @param string $pdf_id PDFの識別ID
	 * @param array  $pdf_item PDF設定
	 * @return string フォームHTML
	 */
	private function get_form_html( $pdf_id, $pdf_item ) {

		// nonceフィールドを生成
		$nonce_field = wp_nonce_field(
			self::NONCE_ACTION,
			self::NONCE_NAME,
			true,
			false
		);

		// 1ページに複数フォームを置いてもIDが重複しないようにする
		$email_input_id = self::CSS_PREFIX . 'email-' . $pdf_id;

		$title       = ! empty( $pdf_item['title'] ) ? $pdf_item['title'] : '無料PDFをメールで受け取る';
		$description = ! empty( $pdf_item['description'] ) ? $pdf_item['description'] : 'メールアドレスを入力すると、無料PDFのご案内をお送りします。';
		$lang        = ! empty( $pdf_item['lang'] ) ? $pdf_item['lang'] : 'ja';

		// PDF設定から優先的に取得、なければ言語に応じた既定値を使用
		$button_text = ! empty( $pdf_item['button_text'] ) 
			? $pdf_item['button_text'] 
			: ( 'ja' === $lang ? '送信する' : 'Send' );
		
		$label_text = ! empty( $pdf_item['label_text'] ) 
			? $pdf_item['label_text'] 
			: ( 'ja' === $lang ? 'メールアドレス' : 'Email address' );
		
		$note_text = ! empty( $pdf_item['note_text'] ) 
			? $pdf_item['note_text'] 
			: ( 'ja' === $lang
				? 'ご入力いただいたメールアドレスは、無料PDFのご案内に使用します。'
				: 'Your email address will be used to send this free PDF.' );

		$html  = '<div class="' . esc_attr( self::CSS_PREFIX . 'form-container' ) . '" data-pdf-id="' . esc_attr( $pdf_id ) . '">';
		$html .= '<form method="post" action="" class="' . esc_attr( self::CSS_PREFIX . 'form' ) . '">';

		// nonce
		$html .= $nonce_field;

		// このプラグインの送信であることを示す hidden
		$html .= '<input type="hidden" name="dcj_fpm_submit" value="1" />';

		// PDF識別ID
		$html .= '<input type="hidden" name="dcj_pdf_id" value="' . esc_attr( $pdf_id ) . '" />';

		// タイトル
		$html .= '<div class="' . esc_attr( self::CSS_PREFIX . 'title' ) . '">';
		$html .= esc_html( $title );
		$html .= '</div>';

		// 説明文
		$html .= '<div class="' . esc_attr( self::CSS_PREFIX . 'description' ) . '">';
		$html .= esc_html( $description );
		$html .= '</div>';

		// メールアドレス入力欄
		$html .= '<div class="' . esc_attr( self::CSS_PREFIX . 'form-group' ) . '">';
		$html .= '<label for="' . esc_attr( $email_input_id ) . '" class="' . esc_attr( self::CSS_PREFIX . 'label' ) . '">';
		$html .= esc_html( $label_text );
		$html .= '</label>';

		$html .= '<input ';
		$html .= 'type="email" ';
		$html .= 'id="' . esc_attr( $email_input_id ) . '" ';
		$html .= 'name="dcj_email" ';
		$html .= 'class="' . esc_attr( self::CSS_PREFIX . 'input' ) . '" ';
		$html .= 'required ';
		$html .= 'placeholder="' . esc_attr( 'example@example.com' ) . '" ';
		$html .= '/>';
		$html .= '</div>';

		// 送信ボタン
		$html .= '<div class="' . esc_attr( self::CSS_PREFIX . 'form-group' ) . '">';
		$html .= '<button type="submit" class="' . esc_attr( self::CSS_PREFIX . 'button' ) . '">';
		$html .= esc_html( $button_text );
		$html .= '</button>';
		$html .= '</div>';

		// 補足文
		$html .= '<div class="' . esc_attr( self::CSS_PREFIX . 'note' ) . '">';
		$html .= esc_html( $note_text );
		$html .= '</div>';

		// 処理結果メッセージ（補足文の下に表示）
		if ( isset( self::$messages[ $pdf_id ] ) ) {
			$html .= self::$messages[ $pdf_id ];
		}

		$html .= '</form>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * 成功メッセージを生成します。
	 *
	 * @param string $message メッセージ
	 * @return string HTML
	 */
	private function get_success_message( $message ) {
		return '<div class="' . esc_attr( self::CSS_PREFIX . 'message ' . self::CSS_PREFIX . 'success ' . self::CSS_PREFIX . 'blink' ) . '">' . esc_html( $message ) . '</div>';
	}

	/**
	 * エラーメッセージを生成します。
	 *
	 * @param string $message エラーメッセージ
	 * @return string HTML
	 */
	private function get_error_message( $message ) {
		return '<div class="' . esc_attr( self::CSS_PREFIX . 'message ' . self::CSS_PREFIX . 'error' ) . '">' . esc_html( $message ) . '</div>';
	}

	/**
	 * CSSと点滅アニメーションを出力します。
	 */
	public function output_styles() {
		?>
		<style>
		/* DCJ Free PDF Mailer フォームスタイル */
		.<?php echo esc_attr( self::CSS_PREFIX ); ?>form-container {
			margin: 20px 0;
			padding: 20px;
			border: 1px solid #ddd;
			border-radius: 4px;
			background-color: #f9f9f9;
		}

		.<?php echo esc_attr( self::CSS_PREFIX ); ?>form {
			display: flex;
			flex-direction: column;
		}

		.<?php echo esc_attr( self::CSS_PREFIX ); ?>title {
			font-size: 1.2em;
			font-weight: bold;
			margin-bottom: 10px;
		}

		.<?php echo esc_attr( self::CSS_PREFIX ); ?>description {
			font-size: 0.95em;
			color: #666;
			margin-bottom: 15px;
		}

		.<?php echo esc_attr( self::CSS_PREFIX ); ?>form-group {
			margin-bottom: 15px;
		}

		.<?php echo esc_attr( self::CSS_PREFIX ); ?>label {
			display: block;
			margin-bottom: 5px;
			font-weight: 500;
		}

		.<?php echo esc_attr( self::CSS_PREFIX ); ?>input {
			width: 100%;
			padding: 10px;
			border: 1px solid #ccc;
			border-radius: 4px;
			font-size: 1em;
			box-sizing: border-box;
		}

		.<?php echo esc_attr( self::CSS_PREFIX ); ?>input:focus {
			outline: none;
			border-color: #4CAF50;
			box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
		}

		.<?php echo esc_attr( self::CSS_PREFIX ); ?>button {
			padding: 10px 20px;
			background-color: #4CAF50;
			color: white;
			border: none;
			border-radius: 4px;
			font-size: 1em;
			cursor: pointer;
			transition: background-color 0.3s;
		}

		.<?php echo esc_attr( self::CSS_PREFIX ); ?>button:hover {
			background-color: #45a049;
		}

		.<?php echo esc_attr( self::CSS_PREFIX ); ?>note {
			font-size: 0.85em;
			color: #999;
			margin-top: 10px;
		}

		/* メッセージスタイル */
		.<?php echo esc_attr( self::CSS_PREFIX ); ?>message {
			padding: 12px 15px;
			border-radius: 4px;
			margin-top: 15px;
			margin-bottom: 0;
		}

		.<?php echo esc_attr( self::CSS_PREFIX ); ?>success {
			background-color: #d4edda;
			color: #155724;
			border: 1px solid #c3e6cb;
		}

		.<?php echo esc_attr( self::CSS_PREFIX ); ?>error {
			background-color: #f8d7da;
			color: #721c24;
			border: 1px solid #f5c6cb;
		}

		/* 成功メッセージの点滅アニメーション */
		.<?php echo esc_attr( self::CSS_PREFIX ); ?>blink {
			animation: dcj-fpm-blink-animation 1.5s infinite;
		}

		@keyframes dcj-fpm-blink-animation {
			0% {
				opacity: 1;
				background-color: #d4edda;
				color: #155724;
			}
			50% {
				opacity: 0.7;
				background-color: #b8d9c8;
				color: #0d4017;
			}
			100% {
				opacity: 1;
				background-color: #d4edda;
				color: #155724;
			}
		}

		/* レスポンシブ対応 */
		@media (max-width: 600px) {
			.<?php echo esc_attr( self::CSS_PREFIX ); ?>form-container {
				padding: 15px;
			}

			.<?php echo esc_attr( self::CSS_PREFIX ); ?>title {
				font-size: 1.1em;
			}

			.<?php echo esc_attr( self::CSS_PREFIX ); ?>button {
				font-size: 0.95em;
			}
		}
		</style>
		<?php
	}

	/**
	 * WordPress管理画面にメニューを登録します。
	 */
	public function register_admin_menu() {

		// 権限チェック
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// トップレベルメニューを追加
		add_menu_page(
			'DCJ Free PDF Mailer',               // ページタイトル
			'DCJ Free PDF',                      // メニュー表示名
			'manage_options',                    // 権限
			'dcj-free-pdf-mailer',               // メニュースラッグ
			array( $this, 'display_admin_page' ), // コールバック
			'dashicons-pdf'                      // アイコン
		);
	}

	/**
	 * プラグイン管理画面でメディアライブラリを読み込みます。
	 *
	 * @param string $hook_suffix 管理画面フック名
	 */
	public function enqueue_admin_media( $hook_suffix ) {

		if ( 'toplevel_page_' . self::PLUGIN_SLUG !== $hook_suffix ) {
			return;
		}

		wp_enqueue_media();
	}

	/**
	 * メディアライブラリ選択用の管理画面スクリプトを出力します。
	 */
	public function output_admin_media_script() {

		if ( empty( $_GET['page'] ) || self::PLUGIN_SLUG !== sanitize_key( wp_unslash( $_GET['page'] ) ) ) {
			return;
		}

		?>
		<script>
		(function() {
			document.addEventListener('click', function(event) {
				var button = event.target.closest('.dcj-fpm-media-select-button');

				if (!button || !window.wp || !window.wp.media) {
					return;
				}

				event.preventDefault();

				var targetSelector = button.getAttribute('data-target');
				var target = targetSelector ? document.querySelector(targetSelector) : null;

				if (!target) {
					return;
				}

				var frame = window.wp.media({
					title: button.getAttribute('data-title') || 'メディアから選択',
					button: {
						text: button.getAttribute('data-button-text') || 'このURLを使用'
					},
					multiple: false
				});

				frame.on('select', function() {
					var attachment = frame.state().get('selection').first().toJSON();

					if (attachment && attachment.url) {
						target.value = attachment.url;
						target.dispatchEvent(new Event('change', { bubbles: true }));
					}
				});

				frame.open();
			});
		})();
		</script>
		<?php
	}

	/**
	 * 管理画面一覧ページを表示します。
	 */
	public function display_admin_page() {

		// 権限チェック
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'dcj-free-pdf-mailer' ) );
		}

		// フォーム送信処理
		$this->handle_admin_form_submit();

		// メッセージの取得・表示
		$success_message = get_transient( 'dcj_fpm_admin_success' );
		$error_message   = get_transient( 'dcj_fpm_admin_error' );

		if ( $success_message ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $success_message ) . '</p></div>';
			delete_transient( 'dcj_fpm_admin_success' );
		}

		if ( $error_message ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $error_message ) . '</p></div>';
			delete_transient( 'dcj_fpm_admin_error' );
		}

		// PDF設定を取得
		$pdf_items = $this->get_pdf_items();
		$category_options = $this->get_category_options();
		$edit_pdf_id = $this->get_admin_edit_pdf_id();
		$edit_pdf_item = '';
		if ( ! empty( $edit_pdf_id ) && isset( $pdf_items[ $edit_pdf_id ] ) ) {
			$edit_pdf_item = $pdf_items[ $edit_pdf_id ];
		}

		?>
		<div class="wrap">
			<h1><?php echo esc_html( 'DCJ Free PDF Mailer' ); ?></h1>
			
			<div class="notice notice-info inline">
				<p><?php echo esc_html( '現在のPDF設定は WordPress option に保存されています。第4-3段階では新規追加機能が実装されました。' ); ?></p>
			</div>
			
			<h2><?php echo esc_html( 'PDF設定一覧' ); ?></h2>
			
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php echo esc_html( 'ショートコード' ); ?></th>
						<th><?php echo esc_html( '言語' ); ?></th>
						<th><?php echo esc_html( 'カテゴリ' ); ?></th>
						<th><?php echo esc_html( '表示タイトル' ); ?></th>
						<th><?php echo esc_html( '管理メモ' ); ?></th>
						<th><?php echo esc_html( '有効/無効' ); ?></th>
						<th><?php echo esc_html( '操作' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $pdf_items as $pdf_id => $pdf_item ) {
						$shortcode      = '[dcj_free_pdf id="' . $pdf_id . '"]';
						$enabled        = ! empty( $pdf_item['enabled'] ) ? '有効' : '無効';
						$lang           = ! empty( $pdf_item['lang'] ) ? $pdf_item['lang'] : '-';
						$category       = ! empty( $pdf_item['category'] ) ? $pdf_item['category'] : '-';
						$category       = isset( $category_options[ $category ] ) ? $category_options[ $category ] : $category;
						$title          = ! empty( $pdf_item['title'] ) ? $pdf_item['title'] : '-';
						$admin_note     = ! empty( $pdf_item['admin_note'] ) ? $pdf_item['admin_note'] : '-';
						$edit_url       = wp_nonce_url(
							add_query_arg(
								array(
									'page'           => self::PLUGIN_SLUG,
									'dcj_fpm_action' => 'edit',
									'dcj_pdf_id'     => rawurlencode( $pdf_id ),
								),
								admin_url( 'admin.php' )
							),
							'dcj_fpm_edit_pdf_item_' . $pdf_id,
							'dcj_fpm_edit_pdf_item_nonce'
						);
						$delete_url     = wp_nonce_url(
							add_query_arg(
								array(
									'page'           => self::PLUGIN_SLUG,
									'dcj_fpm_action' => 'delete',
									'dcj_pdf_id'     => rawurlencode( $pdf_id ),
								),
								admin_url( 'admin.php' )
							),
							'dcj_fpm_delete_pdf_item_' . $pdf_id,
							'dcj_fpm_delete_pdf_item_nonce'
						);
						?>
						<tr>
							<td><code><?php echo esc_html( $shortcode ); ?></code></td>
							<td><?php echo esc_html( $lang ); ?></td>
							<td><?php echo esc_html( $category ); ?></td>
							<td><?php echo esc_html( $title ); ?></td>
							<td><?php echo esc_html( $admin_note ); ?></td>
							<td><?php echo esc_html( $enabled ); ?></td>
							<td>
								<a href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( '編集' ); ?></a>
								|
								<a href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('<?php echo esc_attr( 'このPDF設定を削除します。よろしいですか？' ); ?>');"><?php echo esc_html( '削除' ); ?></a>
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>

			<div style="display: flex; gap: 24px; align-items: flex-start;">
				<div style="flex: 1 1 60%; min-width: 0;">
					<?php
					if ( ! empty( $edit_pdf_item ) ) {
						$this->render_edit_pdf_form( $edit_pdf_id, $edit_pdf_item );
					} else {
						$this->render_add_pdf_form();
					}
					?>
				</div>
				<div style="flex: 0 0 360px; max-width: 360px;">
					<?php $this->render_admin_preview_form( $pdf_items, $edit_pdf_id ); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * 管理画面からのPDF設定追加フォーム送信を処理します。
	 *
	 * @return array|false 成功時は保存されたデータ、失敗時は false
	 */
	private function handle_admin_form_submit() {

		// 権限チェック
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$deleted = $this->handle_admin_delete_pdf_item();
		if ( false !== $deleted ) {
			return $deleted;
		}

		$admin_action = ! empty( $_POST['dcj_fpm_admin_action'] ) ? sanitize_key( wp_unslash( $_POST['dcj_fpm_admin_action'] ) ) : '';

		// フォーム送信でなければスキップ
		if ( empty( $admin_action ) && empty( $_POST['dcj_fpm_add_pdf_item_submit'] ) && empty( $_POST['dcj_fpm_edit_pdf_item_submit'] ) ) {
			return false;
		}

		// ID取得・バリデーション
		$pdf_id = '';
		if ( ! empty( $_POST['dcj_pdf_id'] ) ) {
			$pdf_id = sanitize_key( wp_unslash( $_POST['dcj_pdf_id'] ) );
		}

		if ( empty( $pdf_id ) ) {
			set_transient( 'dcj_fpm_admin_error', 'IDは必須項目です。', 30 );
			return false;
		}

		$is_edit          = ( 'edit' === $admin_action ) || ! empty( $_POST['dcj_fpm_edit_pdf_item_submit'] );
		$items            = $this->get_pdf_items();
		$add_nonce_exists = ! empty( $_POST['dcj_fpm_add_pdf_item_nonce'] );
		$edit_nonce_exists = ! empty( $_POST['dcj_fpm_edit_pdf_item_nonce'] );
		$add_nonce_valid  = false;
		$edit_nonce_valid = false;

		if ( $add_nonce_exists ) {
			$add_nonce = sanitize_text_field( wp_unslash( $_POST['dcj_fpm_add_pdf_item_nonce'] ) );
			$add_nonce_valid = (bool) wp_verify_nonce( $add_nonce, 'dcj_fpm_add_pdf_item' );
		}

		if ( $edit_nonce_exists ) {
			$edit_nonce = sanitize_text_field( wp_unslash( $_POST['dcj_fpm_edit_pdf_item_nonce'] ) );
			$edit_nonce_valid = (bool) wp_verify_nonce( $edit_nonce, 'dcj_fpm_edit_pdf_item_' . $pdf_id );
		}

		if ( $is_edit ) {
			if ( ! $edit_nonce_exists ) {
				return false;
			}

			if ( ! $edit_nonce_valid ) {
				return false;
			}

			if ( ! isset( $items[ $pdf_id ] ) ) {
				set_transient( 'dcj_fpm_admin_error', '編集対象のPDF設定が見つかりません。', 30 );
				return false;
			}
		} else {
			if ( ! $add_nonce_exists ) {
				return false;
			}

			if ( ! $add_nonce_valid ) {
				return false;
			}

			if ( isset( $items[ $pdf_id ] ) ) {
				set_transient( 'dcj_fpm_admin_error', '同じ管理IDが既に存在します。', 30 );
				return false;
			}
		}

		$pdf_item = $this->get_sanitized_admin_pdf_item_from_post( $pdf_id );
		if ( false === $pdf_item ) {
			return false;
		}

		// option に追加・更新
		$previous_item    = $is_edit ? $items[ $pdf_id ] : null;
		$items[ $pdf_id ] = $pdf_item;
		$result           = update_option( self::OPTION_PDF_ITEMS, $items );

		if ( $result || ( $is_edit && $previous_item === $pdf_item ) ) {
			set_transient( 'dcj_fpm_admin_success', $is_edit ? 'PDF設定を更新しました。' : 'PDF設定を追加しました。', 30 );
			return $pdf_item;
		} else {
			set_transient( 'dcj_fpm_admin_error', 'PDF設定の保存に失敗しました。', 30 );
			return false;
		}
	}

	/**
	 * 管理画面の削除リクエストを処理します。
	 *
	 * @return bool|null 削除リクエストの場合は結果、それ以外は null
	 */
	private function handle_admin_delete_pdf_item() {

		if ( empty( $_GET['dcj_fpm_action'] ) || 'delete' !== sanitize_key( wp_unslash( $_GET['dcj_fpm_action'] ) ) ) {
			return false;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$pdf_id = ! empty( $_GET['dcj_pdf_id'] ) ? sanitize_key( wp_unslash( $_GET['dcj_pdf_id'] ) ) : '';
		if ( empty( $pdf_id ) ) {
			set_transient( 'dcj_fpm_admin_error', '削除対象のIDが確認できませんでした。', 30 );
			return false;
		}

		if ( empty( $_GET['dcj_fpm_delete_pdf_item_nonce'] ) ) {
			return false;
		}

		$nonce = sanitize_text_field( wp_unslash( $_GET['dcj_fpm_delete_pdf_item_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'dcj_fpm_delete_pdf_item_' . $pdf_id ) ) {
			return false;
		}

		$items = $this->get_pdf_items();
		if ( ! isset( $items[ $pdf_id ] ) ) {
			set_transient( 'dcj_fpm_admin_error', '削除対象のPDF設定が見つかりません。', 30 );
			return false;
		}

		unset( $items[ $pdf_id ] );
		$result = update_option( self::OPTION_PDF_ITEMS, $items );

		if ( $result ) {
			set_transient( 'dcj_fpm_admin_success', 'PDF設定を削除しました。', 30 );
			return true;
		}

		set_transient( 'dcj_fpm_admin_error', 'PDF設定の削除に失敗しました。', 30 );
		return false;
	}

	/**
	 * 管理画面の編集対象IDを取得します。
	 *
	 * @return string
	 */
	private function get_admin_edit_pdf_id() {

		if ( empty( $_GET['dcj_fpm_action'] ) || 'edit' !== sanitize_key( wp_unslash( $_GET['dcj_fpm_action'] ) ) ) {
			return '';
		}

		if ( empty( $_GET['dcj_pdf_id'] ) || empty( $_GET['dcj_fpm_edit_pdf_item_nonce'] ) ) {
			return '';
		}

		$pdf_id = sanitize_key( wp_unslash( $_GET['dcj_pdf_id'] ) );
		$nonce  = sanitize_text_field( wp_unslash( $_GET['dcj_fpm_edit_pdf_item_nonce'] ) );

		if ( ! wp_verify_nonce( $nonce, 'dcj_fpm_edit_pdf_item_' . $pdf_id ) ) {
			return '';
		}

		return $pdf_id;
	}

	/**
	 * 管理画面フォームの入力値をサニタイズしてPDF設定配列にします。
	 *
	 * @param string $pdf_id PDF識別ID
	 * @return array|false
	 */
	private function get_sanitized_admin_pdf_item_from_post( $pdf_id ) {

		// 必須項目の取得・バリデーション
		$title = ! empty( $_POST['dcj_title'] ) ? sanitize_text_field( wp_unslash( $_POST['dcj_title'] ) ) : '';
		if ( empty( $title ) ) {
			set_transient( 'dcj_fpm_admin_error', 'タイトルは必須項目です。', 30 );
			return false;
		}

		$pdf_url = ! empty( $_POST['dcj_pdf_url'] ) ? sanitize_url( wp_unslash( $_POST['dcj_pdf_url'] ) ) : '';
		if ( empty( $pdf_url ) ) {
			set_transient( 'dcj_fpm_admin_error', 'PDF URLは必須項目です。', 30 );
			return false;
		}
		$pdf_url = esc_url_raw( $pdf_url );

		$mail_subject = ! empty( $_POST['dcj_mail_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['dcj_mail_subject'] ) ) : '';
		if ( empty( $mail_subject ) ) {
			set_transient( 'dcj_fpm_admin_error', 'メール件名は必須項目です。', 30 );
			return false;
		}

		$mail_body = ! empty( $_POST['dcj_mail_body'] ) ? wp_unslash( $_POST['dcj_mail_body'] ) : '';
		if ( empty( $mail_body ) ) {
			set_transient( 'dcj_fpm_admin_error', 'メール本文は必須項目です。', 30 );
			return false;
		}
		$mail_body = sanitize_textarea_field( $mail_body );

		// 基本項目の取得
		$lang                = ! empty( $_POST['dcj_lang'] ) ? sanitize_key( wp_unslash( $_POST['dcj_lang'] ) ) : 'ja';
		$type                = ! empty( $_POST['dcj_type'] ) ? sanitize_text_field( wp_unslash( $_POST['dcj_type'] ) ) : 'set';
		$category            = ! empty( $_POST['dcj_category'] ) ? sanitize_text_field( wp_unslash( $_POST['dcj_category'] ) ) : '';
		$audience            = ! empty( $_POST['dcj_audience'] ) ? sanitize_text_field( wp_unslash( $_POST['dcj_audience'] ) ) : '';
		$audience_label      = ! empty( $_POST['dcj_audience_label'] ) ? sanitize_text_field( wp_unslash( $_POST['dcj_audience_label'] ) ) : '';
		$volume_label        = ! empty( $_POST['dcj_volume_label'] ) ? sanitize_text_field( wp_unslash( $_POST['dcj_volume_label'] ) ) : '';
		$sort_order          = ! empty( $_POST['dcj_sort_order'] ) ? absint( wp_unslash( $_POST['dcj_sort_order'] ) ) : 0;
		$placement_type      = ! empty( $_POST['dcj_placement_type'] ) ? sanitize_text_field( wp_unslash( $_POST['dcj_placement_type'] ) ) : 'article_inline';
		$delivery_method     = ! empty( $_POST['dcj_delivery_method'] ) ? sanitize_key( wp_unslash( $_POST['dcj_delivery_method'] ) ) : 'email';
		$migration_status    = ! empty( $_POST['dcj_migration_status'] ) ? sanitize_key( wp_unslash( $_POST['dcj_migration_status'] ) ) : 'pending';
		$enabled             = ! empty( $_POST['dcj_enabled'] ) ? true : false;

		// 表示・配布項目
		$description        = ! empty( $_POST['dcj_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['dcj_description'] ) ) : '';
		$thumbnail_url      = ! empty( $_POST['dcj_thumbnail_url'] ) ? esc_url_raw( sanitize_url( wp_unslash( $_POST['dcj_thumbnail_url'] ) ) ) : '';
		$button_text        = ! empty( $_POST['dcj_button_text'] ) ? sanitize_text_field( wp_unslash( $_POST['dcj_button_text'] ) ) : '';
		$label_text         = ! empty( $_POST['dcj_label_text'] ) ? sanitize_text_field( wp_unslash( $_POST['dcj_label_text'] ) ) : '';
		$note_text          = ! empty( $_POST['dcj_note_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['dcj_note_text'] ) ) : '';

		// メッセージ
		$success_message    = ! empty( $_POST['dcj_success_message'] ) ? sanitize_text_field( wp_unslash( $_POST['dcj_success_message'] ) ) : '';
		$duplicate_message  = ! empty( $_POST['dcj_duplicate_message'] ) ? sanitize_text_field( wp_unslash( $_POST['dcj_duplicate_message'] ) ) : '';
		$disabled_message   = ! empty( $_POST['dcj_disabled_message'] ) ? sanitize_text_field( wp_unslash( $_POST['dcj_disabled_message'] ) ) : '';

		// 利用条件・管理項目
		$terms_type        = ! empty( $_POST['dcj_terms_type'] ) ? sanitize_text_field( wp_unslash( $_POST['dcj_terms_type'] ) ) : '';
		$terms_text        = ! empty( $_POST['dcj_terms_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['dcj_terms_text'] ) ) : '';
		$source_page_url   = ! empty( $_POST['dcj_source_page_url'] ) ? esc_url_raw( sanitize_url( wp_unslash( $_POST['dcj_source_page_url'] ) ) ) : '';
		$kdp_asin          = ! empty( $_POST['dcj_kdp_asin'] ) ? sanitize_text_field( wp_unslash( $_POST['dcj_kdp_asin'] ) ) : '';
		$kdp_title         = ! empty( $_POST['dcj_kdp_title'] ) ? sanitize_text_field( wp_unslash( $_POST['dcj_kdp_title'] ) ) : '';
		$kdp_url           = ! empty( $_POST['dcj_kdp_url'] ) ? esc_url_raw( sanitize_url( wp_unslash( $_POST['dcj_kdp_url'] ) ) ) : '';
		$admin_note        = ! empty( $_POST['dcj_admin_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['dcj_admin_note'] ) ) : '';

		return array(
			'id'                => $pdf_id,
			'lang'              => $lang,
			'enabled'           => $enabled,
			'type'              => $type,
			'category'          => $category,
			'audience'          => $audience,
			'audience_label'    => $audience_label,
			'volume_label'      => $volume_label,
			'sort_order'        => $sort_order,
			'placement_type'    => $placement_type,
			'delivery_method'   => $delivery_method,
			'migration_status'  => $migration_status,
			'title'             => $title,
			'description'       => $description,
			'thumbnail_url'     => $thumbnail_url,
			'pdf_url'           => $pdf_url,
			'mail_subject'      => $mail_subject,
			'mail_body'         => $mail_body,
			'button_text'       => $button_text,
			'label_text'        => $label_text,
			'note_text'         => $note_text,
			'success_message'   => $success_message,
			'duplicate_message' => $duplicate_message,
			'disabled_message'  => $disabled_message,
			'terms_type'        => $terms_type,
			'terms_text'        => $terms_text,
			'source_page_url'   => $source_page_url,
			'kdp_asin'          => $kdp_asin,
			'kdp_title'         => $kdp_title,
			'kdp_url'           => $kdp_url,
			'admin_note'        => $admin_note,
		);
	}

	/**
	 * 新規PDF設定追加フォームを表示します。
	 */
	private function render_add_pdf_form() {

		$nonce = wp_create_nonce( 'dcj_fpm_add_pdf_item' );
		$default_mail_body = "こんにちは。\n\n{{title}} にお申し込みいただき、ありがとうございます。\n\n以下のリンクからPDFをダウンロードできます。\n\n{{pdf_url}}\n\n利用条件：\n{{terms_text}}\n\n塗り絵の時間を楽しんでいただければ嬉しいです。\n\nDream Coloring Journey";

		?>
		<h2><?php echo esc_html( '新規PDF設定を追加' ); ?></h2>

		<form method="post" action="">
			<table class="form-table">
				<tr>
					<th scope="row"><label for="dcj_pdf_id"><?php echo esc_html( '管理ID' ); ?> *</label></th>
					<td><input type="text" id="dcj_pdf_id" name="dcj_pdf_id" value="" placeholder="<?php echo esc_attr( 'sample-free-pdf-ja' ); ?>" required /></td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_lang"><?php echo esc_html( '言語' ); ?></label></th>
					<td>
						<select id="dcj_lang" name="dcj_lang">
							<option value="ja"><?php echo esc_html( '日本語' ); ?></option>
							<option value="en"><?php echo esc_html( '英語' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_type"><?php echo esc_html( '種類' ); ?></label></th>
					<td>
						<select id="dcj_type" name="dcj_type">
							<option value="set"><?php echo esc_html( 'セット' ); ?></option>
							<option value="single"><?php echo esc_html( '単品' ); ?></option>
							<option value="bonus"><?php echo esc_html( '特典' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_category"><?php echo esc_html( 'カテゴリ' ); ?></label></th>
					<td>
						<select id="dcj_category" name="dcj_category">
							<?php foreach ( $this->get_category_options() as $category_value => $category_label ) : ?>
								<option value="<?php echo esc_attr( $category_value ); ?>"><?php echo esc_html( $category_label ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_audience"><?php echo esc_html( '対象' ); ?></label></th>
					<td>
						<select id="dcj_audience" name="dcj_audience">
							<option value="preschool"><?php echo esc_html( '幼児向け' ); ?></option>
							<option value="kids"><?php echo esc_html( '子ども向け' ); ?></option>
							<option value="family"><?php echo esc_html( '親子向け' ); ?></option>
							<option value="adults"><?php echo esc_html( '大人向け' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_audience_label"><?php echo esc_html( '対象ラベル' ); ?></label></th>
					<td><input type="text" id="dcj_audience_label" name="dcj_audience_label" value="<?php echo esc_attr( '幼児向け・3〜6歳' ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_volume_label"><?php echo esc_html( 'ボリュームラベル' ); ?></label></th>
					<td><input type="text" id="dcj_volume_label" name="dcj_volume_label" value="<?php echo esc_attr( 'PDF 5枚セット' ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_delivery_method"><?php echo esc_html( '配布方式' ); ?></label></th>
					<td>
						<select id="dcj_delivery_method" name="dcj_delivery_method">
							<option value="email"><?php echo esc_html( 'メール' ); ?></option>
							<option value="direct"><?php echo esc_html( 'ダイレクト' ); ?></option>
							<option value="selection_form"><?php echo esc_html( '選択フォーム' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_migration_status"><?php echo esc_html( '移行ステータス' ); ?></label></th>
					<td>
						<select id="dcj_migration_status" name="dcj_migration_status">
							<option value="pending"><?php echo esc_html( 'ペンディング' ); ?></option>
							<option value="converted"><?php echo esc_html( '変換済み' ); ?></option>
							<option value="keep_direct"><?php echo esc_html( 'ダイレクト維持' ); ?></option>
							<option value="disabled"><?php echo esc_html( '無効' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_title"><?php echo esc_html( 'タイトル' ); ?> *</label></th>
					<td>
						<input type="text" id="dcj_title" name="dcj_title" value="<?php echo esc_attr( 'サンプル塗り絵 無料PDF' ); ?>" required />
						<p class="description"><?php echo esc_html( 'フォーム上部に表示される無料PDFのタイトルです。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_description"><?php echo esc_html( '説明' ); ?></label></th>
					<td>
						<textarea id="dcj_description" name="dcj_description" rows="4" cols="50"><?php echo esc_textarea( 'メールアドレスを入力すると、無料PDFのご案内をお送りします。' ); ?></textarea>
						<p class="description"><?php echo esc_html( 'フォーム内の説明文として表示されます。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_thumbnail_url"><?php echo esc_html( 'サムネイルURL' ); ?></label></th>
					<td>
						<input type="url" id="dcj_thumbnail_url" name="dcj_thumbnail_url" value="" placeholder="<?php echo esc_attr( 'https://example.com/thumbnail.jpg' ); ?>" />
						<button type="button" class="button dcj-fpm-media-select-button" data-target="#dcj_thumbnail_url"><?php echo esc_html( 'メディアから選択' ); ?></button>
						<p class="description"><?php echo esc_html( '将来のカード表示や管理画面プレビュー用の画像URLです。現在のメール送信には使用しません。空欄でも問題ありません。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_pdf_url"><?php echo esc_html( 'PDF URL' ); ?> *</label></th>
					<td>
						<input type="url" id="dcj_pdf_url" name="dcj_pdf_url" value="" placeholder="<?php echo esc_attr( 'https://example.com/free-pdf.pdf' ); ?>" required />
						<button type="button" class="button dcj-fpm-media-select-button" data-target="#dcj_pdf_url"><?php echo esc_html( 'メディアから選択' ); ?></button>
						<p class="description"><?php echo esc_html( '受信メール本文の {{pdf_url}} に入ります。必ずブラウザで直接開けるPDF URLを指定してください。/wp-content/uploads/dlm_uploads/ 配下など、直接アクセス禁止のURLは使用しないでください。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_mail_subject"><?php echo esc_html( 'メール件名' ); ?> *</label></th>
					<td>
						<input type="text" id="dcj_mail_subject" name="dcj_mail_subject" value="<?php echo esc_attr( '【Dream Coloring Journey】無料PDFダウンロードリンクのご案内' ); ?>" required />
						<p class="description"><?php echo esc_html( '受信者に届くメールの件名です。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_mail_body"><?php echo esc_html( 'メール本文' ); ?> *</label></th>
					<td>
						<textarea id="dcj_mail_body" name="dcj_mail_body" rows="8" cols="50" required><?php echo esc_textarea( $default_mail_body ); ?></textarea>
						<p class="description"><?php echo esc_html( '受信者に届くメール本文です。{{title}}、{{pdf_url}}、{{terms_text}} などの置換タグが使えます。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_button_text"><?php echo esc_html( 'ボタンテキスト' ); ?></label></th>
					<td>
						<input type="text" id="dcj_button_text" name="dcj_button_text" value="<?php echo esc_attr( '送信する' ); ?>" />
						<p class="description"><?php echo esc_html( 'フォームの送信ボタンに表示されます。空欄の場合は「送信する / Send」が使われます。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_label_text"><?php echo esc_html( 'ラベルテキスト' ); ?></label></th>
					<td>
						<input type="text" id="dcj_label_text" name="dcj_label_text" value="<?php echo esc_attr( 'メールアドレス' ); ?>" />
						<p class="description"><?php echo esc_html( 'メールアドレス入力欄の上に表示されます。空欄の場合は「メールアドレス / Email address」が使われます。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_note_text"><?php echo esc_html( '注記テキスト' ); ?></label></th>
					<td>
						<textarea id="dcj_note_text" name="dcj_note_text" rows="3" cols="50"><?php echo esc_textarea( 'ご入力いただいたメールアドレスは、無料PDFのご案内に使用します。' ); ?></textarea>
						<p class="description"><?php echo esc_html( '送信ボタンの下に表示される補足文です。メールアドレスの利用目的などを記載します。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_success_message"><?php echo esc_html( '成功メッセージ' ); ?></label></th>
					<td>
						<textarea id="dcj_success_message" name="dcj_success_message" rows="2" cols="50"><?php echo esc_textarea( '無料PDFのご案内メールを送信しました。メールボックスをご確認ください。メールが見つからない場合は、迷惑メールフォルダやプロモーションフォルダもご確認ください。' ); ?></textarea>
						<p class="description"><?php echo esc_html( 'メール送信成功後、フォーム下に表示されます。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_duplicate_message"><?php echo esc_html( '重複メッセージ' ); ?></label></th>
					<td>
						<textarea id="dcj_duplicate_message" name="dcj_duplicate_message" rows="2" cols="50"><?php echo esc_textarea( 'すでにお申し込み済みです。メールボックスをご確認ください。' ); ?></textarea>
						<p class="description"><?php echo esc_html( '同じメールアドレスで短時間に再送信した場合に表示されます。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_disabled_message"><?php echo esc_html( '無効メッセージ' ); ?></label></th>
					<td>
						<textarea id="dcj_disabled_message" name="dcj_disabled_message" rows="2" cols="50"><?php echo esc_textarea( 'この無料PDFは現在配布を停止しています。' ); ?></textarea>
						<p class="description"><?php echo esc_html( 'このPDF設定を無効にした場合に表示されます。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_terms_type"><?php echo esc_html( '利用規約タイプ' ); ?></label></th>
					<td>
						<select id="dcj_terms_type" name="dcj_terms_type">
							<option value="personal_use_only"><?php echo esc_html( '個人利用のみ' ); ?></option>
							<option value="classroom_ok"><?php echo esc_html( '教室利用可' ); ?></option>
							<option value="none"><?php echo esc_html( 'なし' ); ?></option>
							<option value="other"><?php echo esc_html( 'その他' ); ?></option>
						</select>
						<p class="description"><?php echo esc_html( '利用条件の分類です。管理用の区分として使います。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_terms_text"><?php echo esc_html( '利用規約テキスト' ); ?></label></th>
					<td>
						<textarea id="dcj_terms_text" name="dcj_terms_text" rows="3" cols="50"><?php echo esc_textarea( '家庭内での個人利用に限ります。再配布・二次配布・商用利用は禁止です。' ); ?></textarea>
						<p class="description"><?php echo esc_html( 'メール本文の {{terms_text}} に入ります。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_admin_note"><?php echo esc_html( '管理メモ' ); ?></label></th>
					<td>
						<textarea id="dcj_admin_note" name="dcj_admin_note" rows="3" cols="50"><?php echo esc_textarea( '管理用メモ。公開フォームには表示されません。' ); ?></textarea>
						<p class="description"><?php echo esc_html( '管理画面一覧で目的確認用に表示されます。公開ページには表示されません。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_enabled"><?php echo esc_html( '有効' ); ?></label></th>
					<td><input type="checkbox" id="dcj_enabled" name="dcj_enabled" value="1" checked /></td>
				</tr>
			</table>

			<details>
				<summary><?php echo esc_html( '詳細設定' ); ?></summary>
				<p><?php echo esc_html( 'この項目は任意です。将来、配布元記事の管理、関連KDP書籍リンク、カード一覧の並び順などに使うための項目です。通常は空欄でも問題ありません。' ); ?></p>
				<table class="form-table">
					<tr>
						<th scope="row"><label for="dcj_sort_order"><?php echo esc_html( 'ソート順序' ); ?></label></th>
						<td>
							<input type="number" id="dcj_sort_order" name="dcj_sort_order" value="0" />
							<p class="description"><?php echo esc_html( '将来のカード一覧表示などで使う並び順です。小さい数字ほど上に表示します。' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="dcj_source_page_url"><?php echo esc_html( 'ソースページURL' ); ?></label></th>
						<td>
							<input type="url" id="dcj_source_page_url" name="dcj_source_page_url" value="" placeholder="<?php echo esc_attr( 'https://example.com/freebie-page/' ); ?>" />
							<p class="description"><?php echo esc_html( 'この無料PDFを設置・案内している元記事URLです。' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="dcj_kdp_asin"><?php echo esc_html( 'KDP ASIN' ); ?></label></th>
						<td>
							<input type="text" id="dcj_kdp_asin" name="dcj_kdp_asin" value="" placeholder="<?php echo esc_attr( 'B0XXXXXXXX' ); ?>" />
							<p class="description"><?php echo esc_html( '関連するAmazon KDP商品のASINです。' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="dcj_kdp_title"><?php echo esc_html( 'KDPタイトル' ); ?></label></th>
						<td>
							<input type="text" id="dcj_kdp_title" name="dcj_kdp_title" value="" placeholder="<?php echo esc_attr( '関連KDP商品のタイトル' ); ?>" />
							<p class="description"><?php echo esc_html( '関連するKDP書籍・シリーズ名の管理用メモです。' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="dcj_kdp_url"><?php echo esc_html( 'KDP URL' ); ?></label></th>
						<td>
							<input type="url" id="dcj_kdp_url" name="dcj_kdp_url" value="" placeholder="<?php echo esc_attr( 'https://www.amazon.co.jp/dp/B0XXXXXXXX' ); ?>" />
							<p class="description"><?php echo esc_html( '関連するAmazon商品ページURLです。' ); ?></p>
						</td>
					</tr>
				</table>
			</details>

			<input type="hidden" name="dcj_fpm_add_pdf_item_nonce" value="<?php echo esc_attr( $nonce ); ?>" />
			<input type="hidden" name="dcj_fpm_admin_action" value="add" />
			<input type="hidden" name="dcj_fpm_add_pdf_item_submit" value="1" />

			<?php submit_button( '追加' ); ?>
		</form>
		<?php
	}

	/**
	 * PDF設定編集フォームを表示します。
	 *
	 * @param string $pdf_id PDF識別ID
	 * @param array  $pdf_item PDF設定
	 */
	private function render_edit_pdf_form( $pdf_id, $pdf_item ) {

		$nonce      = wp_create_nonce( 'dcj_fpm_edit_pdf_item_' . $pdf_id );
		$cancel_url = add_query_arg(
			array(
				'page' => self::PLUGIN_SLUG,
			),
			admin_url( 'admin.php' )
		);
		$edit_lang = ! empty( $pdf_item['lang'] ) ? $pdf_item['lang'] : 'ja';
		$default_mail_body   = 'en' === $edit_lang ? "Hello,\n\nThank you for requesting {{title}}.\n\nYou can download your PDF from the link below:\n\n{{pdf_url}}\n\nTerms of use:\n{{terms_text}}\n\nWe hope you enjoy your coloring time.\n\nDream Coloring Journey" : "こんにちは。\n\n{{title}} にお申し込みいただき、ありがとうございます。\n\n以下のリンクからPDFをダウンロードできます。\n\n{{pdf_url}}\n\n利用条件：\n{{terms_text}}\n\n塗り絵の時間を楽しんでいただければ嬉しいです。\n\nDream Coloring Journey";
		$default_button_text = 'en' === $edit_lang ? 'Send' : '送信する';
		$default_label_text  = 'en' === $edit_lang ? 'Email address' : 'メールアドレス';
		$default_note_text   = 'en' === $edit_lang ? 'Your email address will be used to send this free PDF.' : 'ご入力いただいたメールアドレスは、無料PDFのご案内に使用します。';
		$default_success_message   = 'en' === $edit_lang ? 'Your free PDF email has been sent. Please check your inbox. If you cannot find the email, please check your spam or promotions folder.' : '無料PDFのご案内メールを送信しました。メールボックスをご確認ください。メールが見つからない場合は、迷惑メールフォルダやプロモーションフォルダもご確認ください。';
		$default_duplicate_message = 'en' === $edit_lang ? 'You have already requested this PDF. Please check your inbox.' : 'すでにお申し込み済みです。メールボックスをご確認ください。';
		$default_disabled_message  = 'en' === $edit_lang ? 'This free PDF is currently unavailable.' : 'この無料PDFは現在配布を停止しています。';
		$default_terms_text        = 'en' === $edit_lang ? 'For personal and family use only. Redistribution, resale, and commercial use are not allowed.' : '家庭内での個人利用に限ります。再配布・二次配布・商用利用は禁止です。';
		$button_text               = ! empty( $pdf_item['button_text'] ) ? $pdf_item['button_text'] : $default_button_text;
		$label_text                = ! empty( $pdf_item['label_text'] ) ? $pdf_item['label_text'] : $default_label_text;
		$note_text                 = ! empty( $pdf_item['note_text'] ) ? $pdf_item['note_text'] : $default_note_text;
		$success_message           = isset( $pdf_item['success_message'] ) && '' !== trim( (string) $pdf_item['success_message'] ) ? $pdf_item['success_message'] : $default_success_message;
		$duplicate_message         = ! empty( $pdf_item['duplicate_message'] ) ? $pdf_item['duplicate_message'] : $default_duplicate_message;
		$disabled_message          = ! empty( $pdf_item['disabled_message'] ) ? $pdf_item['disabled_message'] : $default_disabled_message;
		$terms_text                = ! empty( $pdf_item['terms_text'] ) ? $pdf_item['terms_text'] : $default_terms_text;
		$mail_body                 = ! empty( $pdf_item['mail_body'] ) ? $pdf_item['mail_body'] : $default_mail_body;

		?>
		<h2><?php echo esc_html( 'PDF設定を編集' ); ?></h2>

		<form method="post" action="">
			<table class="form-table">
				<tr>
					<th scope="row"><label for="dcj_edit_pdf_id"><?php echo esc_html( '管理ID' ); ?> *</label></th>
					<td>
						<input type="text" id="dcj_edit_pdf_id" value="<?php echo esc_attr( $pdf_id ); ?>" readonly />
						<input type="hidden" name="dcj_pdf_id" value="<?php echo esc_attr( $pdf_id ); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_edit_lang"><?php echo esc_html( '言語' ); ?></label></th>
					<td>
						<select id="dcj_edit_lang" name="dcj_lang">
							<option value="ja" <?php selected( ! empty( $pdf_item['lang'] ) ? $pdf_item['lang'] : 'ja', 'ja' ); ?>><?php echo esc_html( '日本語' ); ?></option>
							<option value="en" <?php selected( ! empty( $pdf_item['lang'] ) ? $pdf_item['lang'] : 'ja', 'en' ); ?>><?php echo esc_html( '英語' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_edit_type"><?php echo esc_html( '種類' ); ?></label></th>
					<td>
						<select id="dcj_edit_type" name="dcj_type">
							<option value="set" <?php selected( ! empty( $pdf_item['type'] ) ? $pdf_item['type'] : 'set', 'set' ); ?>><?php echo esc_html( 'セット' ); ?></option>
							<option value="single" <?php selected( ! empty( $pdf_item['type'] ) ? $pdf_item['type'] : 'set', 'single' ); ?>><?php echo esc_html( '単品' ); ?></option>
							<option value="bonus" <?php selected( ! empty( $pdf_item['type'] ) ? $pdf_item['type'] : 'set', 'bonus' ); ?>><?php echo esc_html( '特典' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_edit_category"><?php echo esc_html( 'カテゴリ' ); ?></label></th>
					<td>
						<select id="dcj_edit_category" name="dcj_category">
							<?php foreach ( $this->get_category_options() as $category_value => $category_label ) : ?>
								<option value="<?php echo esc_attr( $category_value ); ?>" <?php selected( ! empty( $pdf_item['category'] ) ? $pdf_item['category'] : '', $category_value ); ?>><?php echo esc_html( $category_label ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_edit_audience"><?php echo esc_html( '対象' ); ?></label></th>
					<td>
						<select id="dcj_edit_audience" name="dcj_audience">
							<option value="preschool" <?php selected( ! empty( $pdf_item['audience'] ) ? $pdf_item['audience'] : 'preschool', 'preschool' ); ?>><?php echo esc_html( '幼児向け' ); ?></option>
							<option value="kids" <?php selected( ! empty( $pdf_item['audience'] ) ? $pdf_item['audience'] : 'preschool', 'kids' ); ?>><?php echo esc_html( '子ども向け' ); ?></option>
							<option value="family" <?php selected( ! empty( $pdf_item['audience'] ) ? $pdf_item['audience'] : 'preschool', 'family' ); ?>><?php echo esc_html( '親子向け' ); ?></option>
							<option value="adults" <?php selected( ! empty( $pdf_item['audience'] ) ? $pdf_item['audience'] : 'preschool', 'adults' ); ?>><?php echo esc_html( '大人向け' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_edit_audience_label"><?php echo esc_html( '対象ラベル' ); ?></label></th>
					<td><input type="text" id="dcj_edit_audience_label" name="dcj_audience_label" value="<?php echo esc_attr( ! empty( $pdf_item['audience_label'] ) ? $pdf_item['audience_label'] : '' ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_edit_volume_label"><?php echo esc_html( 'ボリュームラベル' ); ?></label></th>
					<td><input type="text" id="dcj_edit_volume_label" name="dcj_volume_label" value="<?php echo esc_attr( ! empty( $pdf_item['volume_label'] ) ? $pdf_item['volume_label'] : '' ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_edit_delivery_method"><?php echo esc_html( '配布方式' ); ?></label></th>
					<td>
						<select id="dcj_edit_delivery_method" name="dcj_delivery_method">
							<option value="email" <?php selected( ! empty( $pdf_item['delivery_method'] ) ? $pdf_item['delivery_method'] : 'email', 'email' ); ?>><?php echo esc_html( 'メール' ); ?></option>
							<option value="direct" <?php selected( ! empty( $pdf_item['delivery_method'] ) ? $pdf_item['delivery_method'] : 'email', 'direct' ); ?>><?php echo esc_html( 'ダイレクト' ); ?></option>
							<option value="selection_form" <?php selected( ! empty( $pdf_item['delivery_method'] ) ? $pdf_item['delivery_method'] : 'email', 'selection_form' ); ?>><?php echo esc_html( '選択フォーム' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_edit_migration_status"><?php echo esc_html( '移行ステータス' ); ?></label></th>
					<td>
						<select id="dcj_edit_migration_status" name="dcj_migration_status">
							<option value="pending" <?php selected( ! empty( $pdf_item['migration_status'] ) ? $pdf_item['migration_status'] : 'pending', 'pending' ); ?>><?php echo esc_html( 'ペンディング' ); ?></option>
							<option value="converted" <?php selected( ! empty( $pdf_item['migration_status'] ) ? $pdf_item['migration_status'] : 'pending', 'converted' ); ?>><?php echo esc_html( '変換済み' ); ?></option>
							<option value="keep_direct" <?php selected( ! empty( $pdf_item['migration_status'] ) ? $pdf_item['migration_status'] : 'pending', 'keep_direct' ); ?>><?php echo esc_html( 'ダイレクト維持' ); ?></option>
							<option value="disabled" <?php selected( ! empty( $pdf_item['migration_status'] ) ? $pdf_item['migration_status'] : 'pending', 'disabled' ); ?>><?php echo esc_html( '無効' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_edit_title"><?php echo esc_html( 'タイトル' ); ?> *</label></th>
					<td>
						<input type="text" id="dcj_edit_title" name="dcj_title" value="<?php echo esc_attr( ! empty( $pdf_item['title'] ) ? $pdf_item['title'] : '' ); ?>" required />
						<p class="description"><?php echo esc_html( 'フォーム上部に表示される無料PDFのタイトルです。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_edit_description"><?php echo esc_html( '説明' ); ?></label></th>
					<td>
						<textarea id="dcj_edit_description" name="dcj_description" rows="4" cols="50"><?php echo esc_textarea( ! empty( $pdf_item['description'] ) ? $pdf_item['description'] : '' ); ?></textarea>
						<p class="description"><?php echo esc_html( 'フォーム内の説明文として表示されます。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_edit_thumbnail_url"><?php echo esc_html( 'サムネイルURL' ); ?></label></th>
					<td>
						<input type="url" id="dcj_edit_thumbnail_url" name="dcj_thumbnail_url" value="<?php echo esc_url( ! empty( $pdf_item['thumbnail_url'] ) ? $pdf_item['thumbnail_url'] : '' ); ?>" />
						<button type="button" class="button dcj-fpm-media-select-button" data-target="#dcj_edit_thumbnail_url"><?php echo esc_html( 'メディアから選択' ); ?></button>
						<p class="description"><?php echo esc_html( '将来のカード表示や管理画面プレビュー用の画像URLです。現在のメール送信には使用しません。空欄でも問題ありません。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_edit_pdf_url"><?php echo esc_html( 'PDF URL' ); ?> *</label></th>
					<td>
						<input type="url" id="dcj_edit_pdf_url" name="dcj_pdf_url" value="<?php echo esc_url( ! empty( $pdf_item['pdf_url'] ) ? $pdf_item['pdf_url'] : '' ); ?>" required />
						<button type="button" class="button dcj-fpm-media-select-button" data-target="#dcj_edit_pdf_url"><?php echo esc_html( 'メディアから選択' ); ?></button>
						<p class="description"><?php echo esc_html( '受信メール本文の {{pdf_url}} に入ります。必ずブラウザで直接開けるPDF URLを指定してください。/wp-content/uploads/dlm_uploads/ 配下など、直接アクセス禁止のURLは使用しないでください。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_edit_mail_subject"><?php echo esc_html( 'メール件名' ); ?> *</label></th>
					<td>
						<input type="text" id="dcj_edit_mail_subject" name="dcj_mail_subject" value="<?php echo esc_attr( ! empty( $pdf_item['mail_subject'] ) ? $pdf_item['mail_subject'] : '' ); ?>" required />
						<p class="description"><?php echo esc_html( '受信者に届くメールの件名です。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_edit_mail_body"><?php echo esc_html( 'メール本文' ); ?> *</label></th>
					<td>
						<textarea id="dcj_edit_mail_body" name="dcj_mail_body" rows="6" cols="50" required><?php echo esc_textarea( $mail_body ); ?></textarea>
						<p class="description"><?php echo esc_html( '受信者に届くメール本文です。{{title}}、{{pdf_url}}、{{terms_text}} などの置換タグが使えます。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_edit_button_text"><?php echo esc_html( 'ボタンテキスト' ); ?></label></th>
					<td>
						<input type="text" id="dcj_edit_button_text" name="dcj_button_text" value="<?php echo esc_attr( $button_text ); ?>" />
						<p class="description"><?php echo esc_html( 'フォームの送信ボタンに表示されます。空欄の場合は「送信する / Send」が使われます。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_edit_label_text"><?php echo esc_html( 'ラベルテキスト' ); ?></label></th>
					<td>
						<input type="text" id="dcj_edit_label_text" name="dcj_label_text" value="<?php echo esc_attr( $label_text ); ?>" />
						<p class="description"><?php echo esc_html( 'メールアドレス入力欄の上に表示されます。空欄の場合は「メールアドレス / Email address」が使われます。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_edit_note_text"><?php echo esc_html( '注記テキスト' ); ?></label></th>
					<td>
						<textarea id="dcj_edit_note_text" name="dcj_note_text" rows="3" cols="50"><?php echo esc_textarea( $note_text ); ?></textarea>
						<p class="description"><?php echo esc_html( '送信ボタンの下に表示される補足文です。メールアドレスの利用目的などを記載します。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_edit_success_message"><?php echo esc_html( '成功メッセージ' ); ?></label></th>
					<td>
						<textarea id="dcj_edit_success_message" name="dcj_success_message" rows="2" cols="50"><?php echo esc_textarea( $success_message ); ?></textarea>
						<p class="description"><?php echo esc_html( 'メール送信成功後、フォーム下に表示されます。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_edit_duplicate_message"><?php echo esc_html( '重複メッセージ' ); ?></label></th>
					<td>
						<textarea id="dcj_edit_duplicate_message" name="dcj_duplicate_message" rows="2" cols="50"><?php echo esc_textarea( $duplicate_message ); ?></textarea>
						<p class="description"><?php echo esc_html( '同じメールアドレスで短時間に再送信した場合に表示されます。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_edit_disabled_message"><?php echo esc_html( '無効メッセージ' ); ?></label></th>
					<td>
						<textarea id="dcj_edit_disabled_message" name="dcj_disabled_message" rows="2" cols="50"><?php echo esc_textarea( $disabled_message ); ?></textarea>
						<p class="description"><?php echo esc_html( 'このPDF設定を無効にした場合に表示されます。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_edit_terms_type"><?php echo esc_html( '利用規約タイプ' ); ?></label></th>
					<td>
						<select id="dcj_edit_terms_type" name="dcj_terms_type">
							<option value="personal_use_only" <?php selected( ! empty( $pdf_item['terms_type'] ) ? $pdf_item['terms_type'] : 'personal_use_only', 'personal_use_only' ); ?>><?php echo esc_html( '個人利用のみ' ); ?></option>
							<option value="classroom_ok" <?php selected( ! empty( $pdf_item['terms_type'] ) ? $pdf_item['terms_type'] : 'personal_use_only', 'classroom_ok' ); ?>><?php echo esc_html( '教室利用可' ); ?></option>
							<option value="none" <?php selected( ! empty( $pdf_item['terms_type'] ) ? $pdf_item['terms_type'] : 'personal_use_only', 'none' ); ?>><?php echo esc_html( 'なし' ); ?></option>
							<option value="other" <?php selected( ! empty( $pdf_item['terms_type'] ) ? $pdf_item['terms_type'] : 'personal_use_only', 'other' ); ?>><?php echo esc_html( 'その他' ); ?></option>
						</select>
						<p class="description"><?php echo esc_html( '利用条件の分類です。管理用の区分として使います。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_edit_terms_text"><?php echo esc_html( '利用規約テキスト' ); ?></label></th>
					<td>
						<textarea id="dcj_edit_terms_text" name="dcj_terms_text" rows="3" cols="50"><?php echo esc_textarea( $terms_text ); ?></textarea>
						<p class="description"><?php echo esc_html( 'メール本文の {{terms_text}} に入ります。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_edit_admin_note"><?php echo esc_html( '管理メモ' ); ?></label></th>
					<td>
						<textarea id="dcj_edit_admin_note" name="dcj_admin_note" rows="3" cols="50"><?php echo esc_textarea( ! empty( $pdf_item['admin_note'] ) ? $pdf_item['admin_note'] : '' ); ?></textarea>
						<p class="description"><?php echo esc_html( '管理画面一覧で目的確認用に表示されます。公開ページには表示されません。' ); ?></p>
					</td>
				</tr>
			</table>

			<details>
				<summary><?php echo esc_html( '詳細設定' ); ?></summary>
				<p><?php echo esc_html( 'この項目は任意です。将来、配布元記事の管理、関連KDP書籍リンク、カード一覧の並び順などに使うための項目です。通常は空欄でも問題ありません。' ); ?></p>
				<table class="form-table">
					<tr>
						<th scope="row"><label for="dcj_edit_sort_order"><?php echo esc_html( 'ソート順序' ); ?></label></th>
						<td>
							<input type="number" id="dcj_edit_sort_order" name="dcj_sort_order" value="<?php echo esc_attr( isset( $pdf_item['sort_order'] ) ? absint( $pdf_item['sort_order'] ) : 0 ); ?>" />
							<p class="description"><?php echo esc_html( '将来のカード一覧表示などで使う並び順です。小さい数字ほど上に表示します。' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="dcj_edit_source_page_url"><?php echo esc_html( 'ソースページURL' ); ?></label></th>
						<td>
							<input type="url" id="dcj_edit_source_page_url" name="dcj_source_page_url" value="<?php echo esc_url( ! empty( $pdf_item['source_page_url'] ) ? $pdf_item['source_page_url'] : '' ); ?>" />
							<p class="description"><?php echo esc_html( 'この無料PDFを設置・案内している元記事URLです。' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="dcj_edit_kdp_asin"><?php echo esc_html( 'KDP ASIN' ); ?></label></th>
						<td>
							<input type="text" id="dcj_edit_kdp_asin" name="dcj_kdp_asin" value="<?php echo esc_attr( ! empty( $pdf_item['kdp_asin'] ) ? $pdf_item['kdp_asin'] : '' ); ?>" />
							<p class="description"><?php echo esc_html( '関連するAmazon KDP商品のASINです。' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="dcj_edit_kdp_title"><?php echo esc_html( 'KDPタイトル' ); ?></label></th>
						<td>
							<input type="text" id="dcj_edit_kdp_title" name="dcj_kdp_title" value="<?php echo esc_attr( ! empty( $pdf_item['kdp_title'] ) ? $pdf_item['kdp_title'] : '' ); ?>" />
							<p class="description"><?php echo esc_html( '関連するKDP書籍・シリーズ名の管理用メモです。' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="dcj_edit_kdp_url"><?php echo esc_html( 'KDP URL' ); ?></label></th>
						<td>
							<input type="url" id="dcj_edit_kdp_url" name="dcj_kdp_url" value="<?php echo esc_url( ! empty( $pdf_item['kdp_url'] ) ? $pdf_item['kdp_url'] : '' ); ?>" />
							<p class="description"><?php echo esc_html( '関連するAmazon商品ページURLです。' ); ?></p>
						</td>
					</tr>
				</table>
			</details>

			<table class="form-table">
				<tr>
					<th scope="row"><label for="dcj_edit_enabled"><?php echo esc_html( '有効' ); ?></label></th>
					<td><input type="checkbox" id="dcj_edit_enabled" name="dcj_enabled" value="1" <?php checked( ! empty( $pdf_item['enabled'] ) ); ?> /></td>
				</tr>
			</table>

			<input type="hidden" name="dcj_fpm_edit_pdf_item_nonce" value="<?php echo esc_attr( $nonce ); ?>" />
			<input type="hidden" name="dcj_fpm_admin_action" value="edit" />
			<input type="hidden" name="dcj_fpm_edit_pdf_item_submit" value="1" />

			<?php submit_button( '更新' ); ?>
			<p><a href="<?php echo esc_url( $cancel_url ); ?>"><?php echo esc_html( '編集をキャンセル' ); ?></a></p>
		</form>
		<?php
	}

	/**
	 * 管理画面の確認用フォームを表示します。
	 *
	 * @param array  $pdf_items PDF設定一覧
	 * @param string $edit_pdf_id 編集中のPDF識別ID
	 */
	private function render_admin_preview_form( $pdf_items, $edit_pdf_id = '' ) {

		if ( empty( $pdf_items ) || ! is_array( $pdf_items ) ) {
			return;
		}

		$preview_pdf_id   = '';
		$preview_pdf_item = array();

		if ( ! empty( $edit_pdf_id ) && isset( $pdf_items[ $edit_pdf_id ] ) ) {
			$preview_pdf_id   = $edit_pdf_id;
			$preview_pdf_item = $pdf_items[ $edit_pdf_id ];
		} else {
			$preview_pdf_id   = key( $pdf_items );
			$preview_pdf_item = current( $pdf_items );
		}

		if ( empty( $preview_pdf_id ) || empty( $preview_pdf_item ) || ! is_array( $preview_pdf_item ) ) {
			return;
		}

		$preview_lang      = ! empty( $preview_pdf_item['lang'] ) ? $preview_pdf_item['lang'] : 'ja';
		$default_mail_body = 'en' === $preview_lang ? "Hello,\n\nThank you for requesting {{title}}.\n\nYou can download your PDF from the link below:\n\n{{pdf_url}}\n\nTerms of use:\n{{terms_text}}\n\nWe hope you enjoy your coloring time.\n\nDream Coloring Journey" : "こんにちは。\n\n{{title}} にお申し込みいただき、ありがとうございます。\n\n以下のリンクからPDFをダウンロードできます。\n\n{{pdf_url}}\n\n利用条件：\n{{terms_text}}\n\n塗り絵の時間を楽しんでいただければ嬉しいです。\n\nDream Coloring Journey";
		$preview_mail_body = ! empty( $preview_pdf_item['mail_body'] ) ? $preview_pdf_item['mail_body'] : $default_mail_body;

		$this->output_styles();

		?>
		<h2><?php echo esc_html( '確認用フォーム' ); ?></h2>
		<p><?php echo esc_html( 'ショートコード: ' ); ?><code><?php echo esc_html( '[dcj_free_pdf id="' . $preview_pdf_id . '"]' ); ?></code></p>
		<?php echo $this->get_form_html( $preview_pdf_id, $preview_pdf_item ); ?>
		<h3><?php echo esc_html( 'PDF URL' ); ?></h3>
		<p>
			<?php if ( ! empty( $preview_pdf_item['pdf_url'] ) ) : ?>
				<a href="<?php echo esc_url( $preview_pdf_item['pdf_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $preview_pdf_item['pdf_url'] ); ?></a>
			<?php else : ?>
				<?php echo esc_html( '-' ); ?>
			<?php endif; ?>
		</p>
		<h3><?php echo esc_html( '受信メール件名' ); ?></h3>
		<p><?php echo esc_html( ! empty( $preview_pdf_item['mail_subject'] ) ? $preview_pdf_item['mail_subject'] : '-' ); ?></p>
		<h3><?php echo esc_html( '受信メール本文' ); ?></h3>
		<pre style="white-space: pre-wrap; background: #fff; border: 1px solid #ccd0d4; padding: 12px;"><?php echo esc_html( $preview_mail_body ); ?></pre>
		<?php
	}

	/**
	 * プラグイン有効化時の処理
	 */
	public static function activate() {

		$items = get_option( self::OPTION_PDF_ITEMS, array() );

		// option が未設定または無効な場合のみデフォルト設定を保存
		if ( empty( $items ) || ! is_array( $items ) ) {
			add_option( self::OPTION_PDF_ITEMS, self::get_default_pdf_items() );
		}
	}
}

/**
 * プラグイン初期化
 */
new DCJ_Free_PDF_Mailer();

/**
 * プラグイン有効化フック登録
 */
register_activation_hook( __FILE__, array( 'DCJ_Free_PDF_Mailer', 'activate' ) );
