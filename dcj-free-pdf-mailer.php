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
	 * 管理画面一覧ページを表示します。
	 */
	public function display_admin_page() {

		// 権限チェック
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'dcj-free-pdf-mailer' ) );
		}

		// PDF設定を取得
		$pdf_items = $this->get_pdf_items();

		?>
		<div class="wrap">
			<h1><?php echo esc_html( 'DCJ Free PDF Mailer' ); ?></h1>
			
			<div class="notice notice-info inline">
				<p><?php echo esc_html( '現在のPDF設定は WordPress option に保存されています。第4-2段階では一覧表示のみで、編集機能はまだありません。' ); ?></p>
			</div>
			
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php echo esc_html( '管理ID' ); ?></th>
						<th><?php echo esc_html( '言語' ); ?></th>
						<th><?php echo esc_html( '種類' ); ?></th>
						<th><?php echo esc_html( 'カテゴリ' ); ?></th>
						<th><?php echo esc_html( '対象' ); ?></th>
						<th><?php echo esc_html( 'ボリューム' ); ?></th>
						<th><?php echo esc_html( '表示タイトル' ); ?></th>
						<th><?php echo esc_html( '配布方式' ); ?></th>
						<th><?php echo esc_html( '移行状態' ); ?></th>
						<th><?php echo esc_html( '有効/無効' ); ?></th>
						<th><?php echo esc_html( '管理メモ' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $pdf_items as $pdf_id => $pdf_item ) {
						$enabled        = ! empty( $pdf_item['enabled'] ) ? '有効' : '無効';
						$lang           = ! empty( $pdf_item['lang'] ) ? $pdf_item['lang'] : '-';
						$type           = ! empty( $pdf_item['type'] ) ? $pdf_item['type'] : '-';
						$category       = ! empty( $pdf_item['category'] ) ? $pdf_item['category'] : '-';
						$audience       = ! empty( $pdf_item['audience'] ) ? $pdf_item['audience'] : '-';
						$volume_label   = ! empty( $pdf_item['volume_label'] ) ? $pdf_item['volume_label'] : '-';
						$title          = ! empty( $pdf_item['title'] ) ? $pdf_item['title'] : '-';
						$delivery       = ! empty( $pdf_item['delivery_method'] ) ? $pdf_item['delivery_method'] : '-';
						$migration      = ! empty( $pdf_item['migration_status'] ) ? $pdf_item['migration_status'] : '-';
						$admin_note     = ! empty( $pdf_item['admin_note'] ) ? $pdf_item['admin_note'] : '-';
						?>
						<tr>
							<td><?php echo esc_html( $pdf_id ); ?></td>
							<td><?php echo esc_html( $lang ); ?></td>
							<td><?php echo esc_html( $type ); ?></td>
							<td><?php echo esc_html( $category ); ?></td>
							<td><?php echo esc_html( $audience ); ?></td>
							<td><?php echo esc_html( $volume_label ); ?></td>
							<td><?php echo esc_html( $title ); ?></td>
							<td><?php echo esc_html( $delivery ); ?></td>
							<td><?php echo esc_html( $migration ); ?></td>
							<td><?php echo esc_html( $enabled ); ?></td>
							<td><?php echo esc_html( $admin_note ); ?></td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		</div>
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