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
	const OPTION_SUBMISSION_LOGS = 'dcj_fpm_submission_logs';
	const OPTION_SUBSCRIBERS     = 'dcj_fpm_subscribers';
	const OPTION_MAIL_SETTINGS   = 'dcj_fpm_mail_settings';

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
		add_action( 'init', array( $this, 'handle_unsubscribe_request' ) );
		add_action( 'init', array( $this, 'handle_form_submit' ) );

		// ショートコード登録
		add_shortcode( 'dcj_free_pdf', array( $this, 'render_form' ) );

		// CSSと点滅アニメーションを出力
		add_action( 'wp_head', array( $this, 'output_styles' ) );

		// 管理画面メニュー登録
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'handle_admin_export_logs' ) );
		add_action( 'admin_init', array( $this, 'handle_admin_export_optin_subscribers' ) );
		add_action( 'admin_init', array( $this, 'handle_admin_export_subscribers' ) );
		add_action( 'admin_init', array( $this, 'handle_admin_update_subscriber_status' ) );
		add_action( 'admin_init', array( $this, 'handle_admin_delete_subscriber' ) );
		add_action( 'admin_init', array( $this, 'handle_admin_clear_logs' ) );

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

		$newsletter_optin = ! empty( $_POST['dcj_newsletter_optin'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['dcj_newsletter_optin'] ) ) ? 'yes' : 'no';

		if ( ! $this->verify_recaptcha_submission() ) {
			$recaptcha_lang    = $this->get_pdf_item_language( $pdf_item, $pdf_id );
			$recaptcha_message = 'en' === $recaptcha_lang
				? 'We could not verify your submission. Please try again later.'
				: '送信を確認できませんでした。時間をおいてもう一度お試しください。';
			self::$messages[ $pdf_id ] = $this->get_error_message( $recaptcha_message );
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
		$body_template    = ! empty( $pdf_item['mail_body'] ) ? $pdf_item['mail_body'] : "{{pdf_url}}";
		$pdf_url          = ! empty( $pdf_item['pdf_url'] ) ? esc_url_raw( $pdf_item['pdf_url'] ) : '';
		$unsubscribe_lang = $this->get_pdf_item_language( $pdf_item, $pdf_id );
		$unsubscribe_url  = $this->get_unsubscribe_url( $email, $unsubscribe_lang );

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
			'{{unsubscribe_url}}',
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
			$unsubscribe_url,
		);

		$body = str_replace( $search_tags, $replace_values, $body_template );

		$headers = array(
			'Content-Type: text/plain; charset=UTF-8',
		);
		$mail_settings = $this->get_mail_settings();

		if ( ! empty( $mail_settings['from_email'] ) ) {
			$headers[] = 'From: ' . $mail_settings['from_name'] . ' <' . $mail_settings['from_email'] . '>';
		}

		$sent = wp_mail( $email, $subject, $body, $headers );
		$this->save_submission_log( $email, $pdf_id, ! empty( $pdf_item['lang'] ) ? $pdf_item['lang'] : '', $sent ? 'success' : 'failed', $newsletter_optin );
		if ( 'yes' === $newsletter_optin ) {
			$this->save_subscriber( $email, $pdf_id, $pdf_item );
		}

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
	 * 配信停止URLへのアクセスを処理します。
	 */
	public function handle_unsubscribe_request() {

		if ( empty( $_GET['dcj_fpm_unsubscribe'] ) || '1' !== sanitize_text_field( wp_unslash( $_GET['dcj_fpm_unsubscribe'] ) ) ) {
			return;
		}

		$email = ! empty( $_GET['email'] ) ? strtolower( trim( sanitize_email( wp_unslash( $_GET['email'] ) ) ) ) : '';
		$token = ! empty( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
		$lang  = ! empty( $_GET['lang'] ) ? sanitize_key( wp_unslash( $_GET['lang'] ) ) : 'ja';
		$lang  = 'en' === $lang ? 'en' : 'ja';

		if ( empty( $email ) || ! is_email( $email ) || empty( $token ) ) {
			$this->render_unsubscribe_message( false, $lang );
			return;
		}

		$expected_token = $this->get_unsubscribe_token( $email );
		$token_valid    = function_exists( 'hash_equals' ) ? hash_equals( $expected_token, $token ) : $expected_token === $token;
		if ( ! $token_valid ) {
			$this->render_unsubscribe_message( false, $lang );
			return;
		}

		$subscribers = get_option( self::OPTION_SUBSCRIBERS, array() );
		$subscribers = is_array( $subscribers ) ? $subscribers : array();

		if ( ! empty( $subscribers[ $email ] ) && is_array( $subscribers[ $email ] ) ) {
			$subscribers[ $email ]['status'] = 'unsubscribed';
			update_option( self::OPTION_SUBSCRIBERS, $subscribers );
		}

		$this->render_unsubscribe_message( true, $lang );
	}

	/**
	 * 配信停止URLを生成します。
	 *
	 * @param string $email メールアドレス
	 * @param string $lang 言語
	 * @return string
	 */
	private function get_unsubscribe_url( $email, $lang = 'ja' ) {

		$email = strtolower( trim( sanitize_email( $email ) ) );
		$token = $this->get_unsubscribe_token( $email );
		$lang  = 'en' === $lang ? 'en' : 'ja';

		return esc_url_raw(
			home_url(
				'/?dcj_fpm_unsubscribe=1&email=' . rawurlencode( $email ) . '&token=' . rawurlencode( $token ) . '&lang=' . rawurlencode( $lang )
			)
		);
	}

	/**
	 * PDF設定から言語を判定します。
	 *
	 * @param array  $pdf_item PDF設定
	 * @param string $pdf_id PDF識別ID
	 * @return string
	 */
	private function get_pdf_item_language( $pdf_item, $pdf_id ) {

		if ( ! empty( $pdf_item['language'] ) && 'en' === sanitize_key( $pdf_item['language'] ) ) {
			return 'en';
		}

		if ( ! empty( $pdf_item['lang'] ) && 'en' === sanitize_key( $pdf_item['lang'] ) ) {
			return 'en';
		}

		if ( false !== strpos( sanitize_key( $pdf_id ), '-en' ) ) {
			return 'en';
		}

		return 'ja';
	}

	/**
	 * 配信停止URL用のトークンを生成します。
	 *
	 * @param string $email メールアドレス
	 * @return string
	 */
	private function get_unsubscribe_token( $email ) {

		return hash_hmac( 'sha256', strtolower( trim( $email ) ), wp_salt( 'auth' ) );
	}

	/**
	 * 配信停止結果を簡単なHTMLで表示します。
	 *
	 * @param bool   $success 成功したかどうか
	 * @param string $lang 言語
	 */
	private function render_unsubscribe_message( $success, $lang = 'ja' ) {

		$lang = 'en' === $lang ? 'en' : 'ja';
		nocache_headers();
		status_header( $success ? 200 : 400 );
		header( 'Content-Type: text/html; charset=UTF-8' );

		if ( 'en' === $lang ) {
			$title   = $success ? 'You have been unsubscribed from email updates.' : 'The unsubscribe URL is invalid.';
			$message = $success ? 'You can still receive free PDFs.' : 'Please check the URL and try again.';
		} else {
			$title   = $success ? 'メール配信を停止しました。' : '配信停止URLが正しくありません。';
			$message = $success ? '無料PDFの受け取りには影響ありません。' : 'URLを確認して、もう一度お試しください。';
		}

		?>
		<!doctype html>
		<html lang="<?php echo esc_attr( $lang ); ?>">
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title><?php echo esc_html( $title ); ?></title>
		</head>
		<body>
			<main style="max-width: 640px; margin: 48px auto; padding: 0 20px; font-family: sans-serif; line-height: 1.7;">
				<h1><?php echo esc_html( $title ); ?></h1>
				<p><?php echo esc_html( $message ); ?></p>
			</main>
		</body>
		</html>
		<?php
		exit;
	}

	/**
	 * フォーム送信ログを保存します。
	 *
	 * @param string $email メールアドレス
	 * @param string $pdf_id PDF識別ID
	 * @param string $lang 言語
	 * @param string $result 送信結果
	 * @param string $newsletter_optin お知らせ受信同意
	 */
	private function save_submission_log( $email, $pdf_id, $lang, $result, $newsletter_optin = 'no' ) {

		$logs       = get_option( self::OPTION_SUBMISSION_LOGS, array() );
		$logs       = is_array( $logs ) ? $logs : array();
		$ip_address = '';

		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip_address = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		array_unshift(
			$logs,
			array(
				'datetime'   => current_time( 'mysql' ),
				'email'      => sanitize_email( $email ),
				'pdf_id'     => sanitize_key( $pdf_id ),
				'lang'       => sanitize_key( $lang ),
				'result'     => 'success' === $result ? 'success' : 'failed',
				'ip_address' => $ip_address,
				'newsletter_optin' => 'yes' === $newsletter_optin ? 'yes' : 'no',
			)
		);

		$logs = array_slice( $logs, 0, 200 );

		update_option( self::OPTION_SUBMISSION_LOGS, $logs );
	}

	/**
	 * お知らせ受信に同意した購読者を保存します。
	 *
	 * @param string $email メールアドレス
	 * @param string $pdf_id PDF識別ID
	 * @param array  $pdf_item PDF設定
	 */
	private function save_subscriber( $email, $pdf_id, $pdf_item ) {

		$email       = strtolower( sanitize_email( $email ) );
		$subscribers = get_option( self::OPTION_SUBSCRIBERS, array() );
		$subscribers = is_array( $subscribers ) ? $subscribers : array();

		if ( empty( $email ) ) {
			return;
		}

		$now = current_time( 'mysql' );

		if ( ! isset( $subscribers[ $email ] ) || ! is_array( $subscribers[ $email ] ) ) {
			$subscribers[ $email ] = array(
				'email'              => $email,
				'lang'               => '',
				'source_pdf_id'      => '',
				'source_title'       => '',
				'optin_datetime'     => $now,
				'last_seen_datetime' => '',
				'status'             => 'subscribed',
			);
		}

		$subscribers[ $email ]['email']              = $email;
		$subscribers[ $email ]['lang']               = ! empty( $pdf_item['lang'] ) ? sanitize_key( $pdf_item['lang'] ) : '';
		$subscribers[ $email ]['source_pdf_id']      = sanitize_key( $pdf_id );
		$subscribers[ $email ]['source_title']       = ! empty( $pdf_item['title'] ) ? sanitize_text_field( $pdf_item['title'] ) : '';
		$subscribers[ $email ]['last_seen_datetime'] = $now;
		$subscribers[ $email ]['status']             = 'subscribed';

		update_option( self::OPTION_SUBSCRIBERS, $subscribers );
	}

	/**
	 * フォーム送信ログを取得します。
	 *
	 * @param int $limit 取得件数
	 * @return array
	 */
	private function get_submission_logs( $limit = 50 ) {

		$logs = get_option( self::OPTION_SUBMISSION_LOGS, array() );

		if ( ! is_array( $logs ) ) {
			return array();
		}

		return array_slice( $logs, 0, absint( $limit ) );
	}

	/**
	 * 送信ログ検索条件を取得します。
	 *
	 * @return array
	 */
	private function get_submission_log_filters_from_request() {

		$newsletter_consent = ! empty( $_GET['dcj_log_newsletter_consent'] ) ? sanitize_key( wp_unslash( $_GET['dcj_log_newsletter_consent'] ) ) : 'all';
		$newsletter_consent = in_array( $newsletter_consent, array( 'all', 'yes', 'no' ), true ) ? $newsletter_consent : 'all';

		return array(
			'email_search'       => ! empty( $_GET['dcj_log_email_search'] ) ? sanitize_text_field( wp_unslash( $_GET['dcj_log_email_search'] ) ) : '',
			'pdf_id_search'      => ! empty( $_GET['dcj_log_pdf_id_search'] ) ? sanitize_text_field( wp_unslash( $_GET['dcj_log_pdf_id_search'] ) ) : '',
			'newsletter_consent' => $newsletter_consent,
		);
	}

	/**
	 * 送信ログを検索条件で絞り込みます。
	 *
	 * @param array $logs 送信ログ
	 * @param array $filters 検索条件
	 * @return array
	 */
	private function filter_submission_logs( $logs, $filters ) {

		if ( empty( $filters['email_search'] ) && empty( $filters['pdf_id_search'] ) && ( empty( $filters['newsletter_consent'] ) || 'all' === $filters['newsletter_consent'] ) ) {
			return $logs;
		}

		return array_filter(
			$logs,
			function ( $log ) use ( $filters ) {
				$email              = ! empty( $log['email'] ) ? sanitize_email( $log['email'] ) : '';
				$pdf_id             = ! empty( $log['pdf_id'] ) ? sanitize_text_field( $log['pdf_id'] ) : '';
				$newsletter_consent = ! empty( $log['newsletter_optin'] ) && 'yes' === $log['newsletter_optin'] ? 'yes' : 'no';

				if ( ! empty( $filters['email_search'] ) && false === stripos( $email, $filters['email_search'] ) ) {
					return false;
				}

				if ( ! empty( $filters['pdf_id_search'] ) && false === stripos( $pdf_id, $filters['pdf_id_search'] ) ) {
					return false;
				}

				if ( ! empty( $filters['newsletter_consent'] ) && 'all' !== $filters['newsletter_consent'] && $newsletter_consent !== $filters['newsletter_consent'] ) {
					return false;
				}

				return true;
			}
		);
	}

	/**
	 * 購読者リストを取得します。
	 *
	 * @param int $limit 取得件数。0の場合は全件
	 * @return array
	 */
	private function get_subscribers( $limit = 50 ) {

		$subscribers = get_option( self::OPTION_SUBSCRIBERS, array() );

		if ( ! is_array( $subscribers ) ) {
			return array();
		}

		$subscribers = array_values( $subscribers );
		usort(
			$subscribers,
			function ( $a, $b ) {
				$a_datetime = ! empty( $a['last_seen_datetime'] ) ? $a['last_seen_datetime'] : '';
				$b_datetime = ! empty( $b['last_seen_datetime'] ) ? $b['last_seen_datetime'] : '';

				return strcmp( $b_datetime, $a_datetime );
			}
		);

		if ( empty( $limit ) ) {
			return $subscribers;
		}

		return array_slice( $subscribers, 0, absint( $limit ) );
	}

	/**
	 * 購読者検索条件を取得します。
	 *
	 * @return array
	 */
	private function get_subscriber_filters_from_request() {

		$status = ! empty( $_GET['dcj_subscriber_status'] ) ? sanitize_key( wp_unslash( $_GET['dcj_subscriber_status'] ) ) : 'all';
		$status = in_array( $status, array( 'all', 'subscribed', 'unsubscribed' ), true ) ? $status : 'all';

		return array(
			'search' => ! empty( $_GET['dcj_subscriber_search'] ) ? sanitize_text_field( wp_unslash( $_GET['dcj_subscriber_search'] ) ) : '',
			'status' => $status,
		);
	}

	/**
	 * 購読者を検索条件で絞り込みます。
	 *
	 * @param array $subscribers 購読者リスト
	 * @param array $filters 検索条件
	 * @return array
	 */
	private function filter_subscribers( $subscribers, $filters ) {

		if ( empty( $filters['search'] ) && ( empty( $filters['status'] ) || 'all' === $filters['status'] ) ) {
			return $subscribers;
		}

		return array_filter(
			$subscribers,
			function ( $subscriber ) use ( $filters ) {
				$email  = ! empty( $subscriber['email'] ) ? sanitize_email( $subscriber['email'] ) : '';
				$status = ! empty( $subscriber['status'] ) && 'unsubscribed' === $subscriber['status'] ? 'unsubscribed' : 'subscribed';

				if ( ! empty( $filters['search'] ) && false === stripos( $email, $filters['search'] ) ) {
					return false;
				}

				if ( ! empty( $filters['status'] ) && 'all' !== $filters['status'] && $status !== $filters['status'] ) {
					return false;
				}

				return true;
			}
		);
	}

	/**
	 * 購読者ステータスの表示名を取得します。
	 *
	 * @param string $status 購読者ステータス
	 * @return string
	 */
	private function get_subscriber_status_label( $status ) {

		if ( 'unsubscribed' === $status ) {
			return '配信停止';
		}

		return '購読中';
	}

	/**
	 * メール送信設定を取得します。
	 *
	 * @return array
	 */
	private function get_mail_settings() {

		$settings = get_option( self::OPTION_MAIL_SETTINGS, array() );
		$settings = is_array( $settings ) ? $settings : array();
		$threshold = isset( $settings['recaptcha_threshold'] ) ? (float) $settings['recaptcha_threshold'] : 0.5;

		if ( $threshold < 0 || $threshold > 1 ) {
			$threshold = 0.5;
		}

		return array(
			'from_name'             => ! empty( $settings['from_name'] ) ? sanitize_text_field( $settings['from_name'] ) : 'Dream Coloring Journey',
			'from_email'            => ! empty( $settings['from_email'] ) ? sanitize_email( $settings['from_email'] ) : '',
			'recaptcha_enabled'     => ! empty( $settings['recaptcha_enabled'] ) ? '1' : '',
			'recaptcha_site_key'    => ! empty( $settings['recaptcha_site_key'] ) ? sanitize_text_field( $settings['recaptcha_site_key'] ) : '',
			'recaptcha_secret_key'  => ! empty( $settings['recaptcha_secret_key'] ) ? sanitize_text_field( $settings['recaptcha_secret_key'] ) : '',
			'recaptcha_threshold'   => $threshold,
		);
	}

	/**
	 * reCAPTCHA v3が検証可能な設定か判定します。
	 *
	 * @param array $mail_settings メール送信設定
	 * @return bool
	 */
	private function is_recaptcha_ready( $mail_settings ) {

		return ! empty( $mail_settings['recaptcha_enabled'] )
			&& ! empty( $mail_settings['recaptcha_site_key'] )
			&& ! empty( $mail_settings['recaptcha_secret_key'] );
	}

	/**
	 * reCAPTCHA v3送信を検証します。
	 *
	 * @return bool
	 */
	private function verify_recaptcha_submission() {

		$mail_settings = $this->get_mail_settings();

		if ( ! $this->is_recaptcha_ready( $mail_settings ) ) {
			return true;
		}

		$token = ! empty( $_POST['dcj_recaptcha_token'] ) ? sanitize_text_field( wp_unslash( $_POST['dcj_recaptcha_token'] ) ) : '';

		if ( empty( $token ) ) {
			return false;
		}

		$body = array(
			'secret'   => $mail_settings['recaptcha_secret_key'],
			'response' => $token,
		);

		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$body['remoteip'] = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		$response = wp_remote_post(
			'https://www.google.com/recaptcha/api/siteverify',
			array(
				'timeout' => 8,
				'body'    => $body,
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$result = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $result ) ) {
			return false;
		}

		$success   = ! empty( $result['success'] );
		$score     = isset( $result['score'] ) ? (float) $result['score'] : 0;
		$action    = ! empty( $result['action'] ) ? sanitize_key( $result['action'] ) : '';
		$threshold = (float) $mail_settings['recaptcha_threshold'];

		return $success && $score >= $threshold && 'dcj_free_pdf_submit' === $action;
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
		$newsletter_optin_text = 'ja' === $lang
			? '新作の無料PDF、季節の塗り絵情報、デジタル書籍の割引クーポンをメールで受け取る'
			: 'I would like to receive new free PDFs, seasonal coloring ideas, and discount coupons for digital books by email.';
		$newsletter_optin_note = 'ja' === $lang
			? 'チェックしなくても無料PDFはお受け取りいただけます。お知らせやクーポンは不定期でお送りします。'
			: 'You can receive the free PDF even if you do not check this box. Updates and coupons are sent occasionally.';
		$mail_settings      = $this->get_mail_settings();
		$recaptcha_enabled  = $this->is_recaptcha_ready( $mail_settings );
		$form_id            = self::CSS_PREFIX . 'form-' . $pdf_id;
		$recaptcha_input_id = self::CSS_PREFIX . 'recaptcha-token-' . $pdf_id;

		$html  = '<div class="' . esc_attr( self::CSS_PREFIX . 'form-container' ) . '" data-pdf-id="' . esc_attr( $pdf_id ) . '">';
		$html .= '<form method="post" action="" id="' . esc_attr( $form_id ) . '" class="' . esc_attr( self::CSS_PREFIX . 'form' ) . '">';

		// nonce
		$html .= $nonce_field;

		// このプラグインの送信であることを示す hidden
		$html .= '<input type="hidden" name="dcj_fpm_submit" value="1" />';

		// PDF識別ID
		$html .= '<input type="hidden" name="dcj_pdf_id" value="' . esc_attr( $pdf_id ) . '" />';

		if ( $recaptcha_enabled ) {
			$html .= '<input type="hidden" id="' . esc_attr( $recaptcha_input_id ) . '" name="dcj_recaptcha_token" value="" />';
		}

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

		// お知らせ受信同意
		$html .= '<div class="' . esc_attr( self::CSS_PREFIX . 'form-group' ) . '">';
		$html .= '<label class="' . esc_attr( self::CSS_PREFIX . 'label' ) . '">';
		$html .= '<input type="checkbox" name="dcj_newsletter_optin" value="' . esc_attr( '1' ) . '" /> ';
		$html .= esc_html( $newsletter_optin_text );
		$html .= '</label>';
		$html .= '<div class="' . esc_attr( self::CSS_PREFIX . 'note' ) . '">';
		$html .= esc_html( $newsletter_optin_note );
		$html .= '</div>';
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

		if ( $recaptcha_enabled ) {
			$html .= '<script src="' . esc_url( 'https://www.google.com/recaptcha/api.js?render=' . rawurlencode( $mail_settings['recaptcha_site_key'] ) ) . '"></script>';
			$html .= '<script>';
			$html .= '(function(){';
			$html .= 'var form=document.getElementById(' . wp_json_encode( $form_id ) . ');';
			$html .= 'var tokenInput=document.getElementById(' . wp_json_encode( $recaptcha_input_id ) . ');';
			$html .= 'if(!form||!tokenInput){return;}';
			$html .= 'form.addEventListener("submit",function(event){';
			$html .= 'if(form.getAttribute("data-dcj-recaptcha-submitting")==="1"){return;}';
			$html .= 'event.preventDefault();';
			$html .= 'if(typeof grecaptcha==="undefined"){form.submit();return;}';
			$html .= 'grecaptcha.ready(function(){';
			$html .= 'grecaptcha.execute(' . wp_json_encode( $mail_settings['recaptcha_site_key'] ) . ',{action:"dcj_free_pdf_submit"}).then(function(token){';
			$html .= 'tokenInput.value=token;';
			$html .= 'form.setAttribute("data-dcj-recaptcha-submitting","1");';
			$html .= 'form.submit();';
			$html .= '});';
			$html .= '});';
			$html .= '});';
			$html .= '})();';
			$html .= '</script>';
		}

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

		$pdf_items        = $this->get_pdf_items();
		$existing_pdf_ids = is_array( $pdf_items ) ? array_keys( $pdf_items ) : array();

		?>
		<script>
		(function() {
			var existingPdfIds = <?php echo wp_json_encode( $existing_pdf_ids ); ?>;

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

			document.addEventListener('click', function(event) {
				var button = event.target.closest('.dcj-fpm-generate-id-button');
				var idField;
				var langSelect;
				var lang;
				var nextId;

				if (!button) {
					return;
				}

				event.preventDefault();

				idField = document.getElementById('dcj_pdf_id');
				langSelect = document.getElementById('dcj_lang');
				lang = langSelect ? langSelect.value : 'ja';

				if (!idField) {
					return;
				}

				nextId = generatePdfId(lang);

				if (idField.value && !window.confirm('現在の管理IDをID候補で上書きしますか？')) {
					return;
				}

				idField.value = nextId;
			});

			function generatePdfId(lang) {
				var maxNumber = 0;

				existingPdfIds.forEach(function(pdfId) {
					var match = String(pdfId).match(/^dcj-(\d+)(?:-(?:ja|en))?$/);
					var number;

					if (!match) {
						return;
					}

					number = parseInt(match[1], 10);

					if (number > maxNumber) {
						maxNumber = number;
					}
				});

				return 'dcj-' + String(maxNumber + 1).padStart(3, '0') + '-' + lang;
			}

			var defaults = {
				ja: {
					dcj_title: 'サンプル塗り絵 無料PDF',
					dcj_description: 'メールアドレスを入力すると、無料PDFのご案内をお送りします。',
					dcj_mail_subject: '【Dream Coloring Journey】無料PDFダウンロードリンクのご案内',
					dcj_mail_body: 'こんにちは。\n\n{{title}} にお申し込みいただき、ありがとうございます。\n\n以下のリンクからPDFをダウンロードできます。\n\n{{pdf_url}}\n\n利用条件：\n{{terms_text}}\n\n塗り絵の時間を楽しんでいただければ嬉しいです。\n\nDream Coloring Journey',
					dcj_button_text: '送信する',
					dcj_label_text: 'メールアドレス',
					dcj_note_text: 'ご入力いただいたメールアドレスは、無料PDFのご案内に使用します。',
					dcj_success_message: '無料PDFのご案内メールを送信しました。メールボックスをご確認ください。メールが見つからない場合は、迷惑メールフォルダやプロモーションフォルダもご確認ください。',
					dcj_duplicate_message: 'すでにお申し込み済みです。メールボックスをご確認ください。',
					dcj_disabled_message: 'この無料PDFは現在配布を停止しています。',
					dcj_terms_text: '家庭内での個人利用に限ります。再配布・二次配布・商用利用は禁止です。'
				},
				en: {
					dcj_title: 'Free Coloring PDF Sample',
					dcj_description: 'Enter your email address to receive the free PDF download link.',
					dcj_mail_subject: 'Your Free PDF Download Link from Dream Coloring Journey',
					dcj_mail_body: 'Hello,\n\nThank you for requesting {{title}}.\n\nYou can download your PDF from the link below:\n\n{{pdf_url}}\n\nTerms of use:\n{{terms_text}}\n\nWe hope you enjoy your coloring time.\n\nDream Coloring Journey',
					dcj_button_text: 'Send',
					dcj_label_text: 'Email address',
					dcj_note_text: 'Your email address will be used to send this free PDF.',
					dcj_success_message: 'Your free PDF email has been sent. Please check your inbox. If you cannot find the email, please check your spam or promotions folder.',
					dcj_duplicate_message: 'You have already requested this PDF. Please check your inbox.',
					dcj_disabled_message: 'This free PDF is currently unavailable.',
					dcj_terms_text: 'For personal and family use only. Redistribution, resale, and commercial use are not allowed.'
				}
			};

			function isDefaultValue(fieldId, value) {
				return value === defaults.ja[fieldId] || value === defaults.en[fieldId];
			}

			function updateAddFormDefaults(lang) {
				var fieldId;
				var field;

				if (!defaults[lang]) {
					return;
				}

				for (fieldId in defaults[lang]) {
					if (!Object.prototype.hasOwnProperty.call(defaults[lang], fieldId)) {
						continue;
					}

					field = document.getElementById(fieldId);

					if (!field) {
						continue;
					}

					if ('' === field.value || isDefaultValue(fieldId, field.value)) {
						field.value = defaults[lang][fieldId];
					}
				}
			}

			var langSelect = document.getElementById('dcj_lang');

			if (langSelect && (!langSelect.form || langSelect.form.getAttribute('data-dcj-fpm-duplicate') !== '1')) {
				langSelect.addEventListener('change', function() {
					updateAddFormDefaults(langSelect.value);
				});
			}
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

			<?php $this->render_mail_settings_form(); ?>
			
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
						$duplicate_url  = wp_nonce_url(
							add_query_arg(
								array(
									'page'           => self::PLUGIN_SLUG,
									'dcj_fpm_action' => 'duplicate',
									'dcj_pdf_id'     => rawurlencode( $pdf_id ),
								),
								admin_url( 'admin.php' )
							),
							'dcj_fpm_duplicate_pdf_item_' . $pdf_id,
							'dcj_fpm_duplicate_pdf_item_nonce'
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
								<a href="<?php echo esc_url( $duplicate_url ); ?>"><?php echo esc_html( '複製' ); ?></a>
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

			<?php $this->render_submission_logs(); ?>
			<?php $this->render_subscribers(); ?>
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

		if ( ! empty( $_POST['dcj_fpm_mail_settings_submit'] ) ) {
			return $this->handle_admin_mail_settings_submit();
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
	 * メール送信設定フォームの保存を処理します。
	 *
	 * @return bool
	 */
	private function handle_admin_mail_settings_submit() {

		if ( empty( $_POST['dcj_fpm_mail_settings_nonce'] ) ) {
			return false;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['dcj_fpm_mail_settings_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'dcj_fpm_mail_settings' ) ) {
			return false;
		}

		$from_name  = ! empty( $_POST['dcj_fpm_from_name'] ) ? sanitize_text_field( wp_unslash( $_POST['dcj_fpm_from_name'] ) ) : 'Dream Coloring Journey';
		$from_email = ! empty( $_POST['dcj_fpm_from_email'] ) ? sanitize_email( wp_unslash( $_POST['dcj_fpm_from_email'] ) ) : '';
		$recaptcha_enabled    = ! empty( $_POST['dcj_fpm_recaptcha_enabled'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['dcj_fpm_recaptcha_enabled'] ) ) ? '1' : '';
		$recaptcha_site_key   = ! empty( $_POST['dcj_fpm_recaptcha_site_key'] ) ? sanitize_text_field( wp_unslash( $_POST['dcj_fpm_recaptcha_site_key'] ) ) : '';
		$recaptcha_secret_key = ! empty( $_POST['dcj_fpm_recaptcha_secret_key'] ) ? sanitize_text_field( wp_unslash( $_POST['dcj_fpm_recaptcha_secret_key'] ) ) : '';
		$recaptcha_threshold  = isset( $_POST['dcj_fpm_recaptcha_threshold'] ) ? (float) sanitize_text_field( wp_unslash( $_POST['dcj_fpm_recaptcha_threshold'] ) ) : 0.5;

		if ( ! empty( $from_email ) && ! is_email( $from_email ) ) {
			set_transient( 'dcj_fpm_admin_error', '送信元メールアドレスの形式が正しくありません。', 30 );
			return false;
		}

		if ( $recaptcha_threshold < 0 || $recaptcha_threshold > 1 ) {
			$recaptcha_threshold = 0.5;
		}

		update_option(
			self::OPTION_MAIL_SETTINGS,
			array(
				'from_name'            => $from_name,
				'from_email'           => $from_email,
				'recaptcha_enabled'    => $recaptcha_enabled,
				'recaptcha_site_key'   => $recaptcha_site_key,
				'recaptcha_secret_key' => $recaptcha_secret_key,
				'recaptcha_threshold'  => $recaptcha_threshold,
			)
		);

		set_transient( 'dcj_fpm_admin_success', 'メール送信設定を保存しました。', 30 );
		return true;
	}

	/**
	 * メール送信設定フォームを表示します。
	 */
	private function render_mail_settings_form() {

		$mail_settings = $this->get_mail_settings();

		?>
		<h2><?php echo esc_html( 'メール送信設定' ); ?></h2>
		<p><?php echo esc_html( '送信元メールアドレスを設定すると、無料PDF案内メールのFromに反映されます。メール到達率を高めるには、WP Mail SMTP や FluentSMTP などのSMTP設定も併用してください。' ); ?></p>
		<form method="post" action="">
			<table class="form-table">
				<tr>
					<th scope="row"><label for="dcj_fpm_from_name"><?php echo esc_html( '送信者名' ); ?></label></th>
					<td><input type="text" id="dcj_fpm_from_name" name="dcj_fpm_from_name" value="<?php echo esc_attr( $mail_settings['from_name'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_fpm_from_email"><?php echo esc_html( '送信元メールアドレス' ); ?></label></th>
					<td><input type="email" id="dcj_fpm_from_email" name="dcj_fpm_from_email" value="<?php echo esc_attr( $mail_settings['from_email'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( 'reCAPTCHA v3' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="dcj_fpm_recaptcha_enabled" value="1" <?php checked( $mail_settings['recaptcha_enabled'], '1' ); ?> />
							<?php echo esc_html( 'reCAPTCHA v3を有効にする' ); ?>
						</label>
						<p class="description"><?php echo esc_html( 'reCAPTCHA v3を使うにはGoogle reCAPTCHAで発行したSite KeyとSecret Keyが必要です。LocalWPなど登録ドメインと異なる環境では正常に検証できない場合があります。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_fpm_recaptcha_site_key"><?php echo esc_html( 'Site Key' ); ?></label></th>
					<td><input type="text" id="dcj_fpm_recaptcha_site_key" name="dcj_fpm_recaptcha_site_key" value="<?php echo esc_attr( $mail_settings['recaptcha_site_key'] ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_fpm_recaptcha_secret_key"><?php echo esc_html( 'Secret Key' ); ?></label></th>
					<td><input type="password" id="dcj_fpm_recaptcha_secret_key" name="dcj_fpm_recaptcha_secret_key" value="<?php echo esc_attr( $mail_settings['recaptcha_secret_key'] ); ?>" class="regular-text" autocomplete="off" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_fpm_recaptcha_threshold"><?php echo esc_html( 'Score Threshold' ); ?></label></th>
					<td>
						<input type="number" id="dcj_fpm_recaptcha_threshold" name="dcj_fpm_recaptcha_threshold" value="<?php echo esc_attr( $mail_settings['recaptcha_threshold'] ); ?>" min="0" max="1" step="0.1" />
						<p class="description"><?php echo esc_html( '通常は0.5から開始し、スパム状況や通常送信の失敗状況を見て調整します。0.0〜1.0の範囲外は0.5として保存されます。' ); ?></p>
					</td>
				</tr>
			</table>
			<input type="hidden" name="dcj_fpm_mail_settings_nonce" value="<?php echo esc_attr( wp_create_nonce( 'dcj_fpm_mail_settings' ) ); ?>" />
			<input type="hidden" name="dcj_fpm_mail_settings_submit" value="1" />
			<?php submit_button( 'メール送信設定を保存' ); ?>
		</form>
		<?php
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
	 * 送信ログをCSV出力します。
	 */
	public function handle_admin_export_logs() {

		if ( empty( $_GET['page'] ) || self::PLUGIN_SLUG !== sanitize_key( wp_unslash( $_GET['page'] ) ) ) {
			return;
		}

		if ( empty( $_GET['dcj_fpm_action'] ) || 'export_logs' !== sanitize_key( wp_unslash( $_GET['dcj_fpm_action'] ) ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'dcj-free-pdf-mailer' ) );
		}

		if ( empty( $_GET['dcj_fpm_export_logs_nonce'] ) ) {
			wp_die( esc_html( 'CSV出力の確認に失敗しました。' ) );
		}

		$nonce = sanitize_text_field( wp_unslash( $_GET['dcj_fpm_export_logs_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'dcj_fpm_export_logs' ) ) {
			wp_die( esc_html( 'CSV出力の確認に失敗しました。' ) );
		}

		$logs      = get_option( self::OPTION_SUBMISSION_LOGS, array() );
		$logs      = is_array( $logs ) ? $logs : array();
		$logs      = $this->filter_submission_logs( $logs, $this->get_submission_log_filters_from_request() );
		$timestamp = date_i18n( 'Ymd-His', current_time( 'timestamp' ) );
		$filename  = 'dcj-free-pdf-submission-logs-' . $timestamp . '.csv';

		nocache_headers();
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		$output = fopen( 'php://output', 'w' );

		if ( false !== $output ) {
			fwrite( $output, "\xEF\xBB\xBF" );
			fputcsv( $output, array( '日時', 'メールアドレス', 'PDF ID', '言語', '結果', 'IPアドレス', 'お知らせ同意' ) );

			foreach ( $logs as $log ) {
				$newsletter_optin_label = ! empty( $log['newsletter_optin'] ) && 'yes' === $log['newsletter_optin'] ? '同意あり' : '同意なし';

				fputcsv(
					$output,
					array(
						! empty( $log['datetime'] ) ? $log['datetime'] : '',
						! empty( $log['email'] ) ? $log['email'] : '',
						! empty( $log['pdf_id'] ) ? $log['pdf_id'] : '',
						! empty( $log['lang'] ) ? $log['lang'] : '',
						! empty( $log['result'] ) ? $log['result'] : '',
						! empty( $log['ip_address'] ) ? $log['ip_address'] : '',
						$newsletter_optin_label,
					)
				);
			}

			fclose( $output );
		}

		exit;
	}

	/**
	 * お知らせ受信に同意したメールアドレスをCSV出力します。
	 */
	public function handle_admin_export_optin_subscribers() {

		if ( empty( $_GET['page'] ) || self::PLUGIN_SLUG !== sanitize_key( wp_unslash( $_GET['page'] ) ) ) {
			return;
		}

		if ( empty( $_GET['dcj_fpm_action'] ) || 'export_optin_subscribers' !== sanitize_key( wp_unslash( $_GET['dcj_fpm_action'] ) ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'dcj-free-pdf-mailer' ) );
		}

		if ( empty( $_GET['dcj_fpm_export_optin_subscribers_nonce'] ) ) {
			wp_die( esc_html( 'CSV出力の確認に失敗しました。' ) );
		}

		$nonce = sanitize_text_field( wp_unslash( $_GET['dcj_fpm_export_optin_subscribers_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'dcj_fpm_export_optin_subscribers' ) ) {
			wp_die( esc_html( 'CSV出力の確認に失敗しました。' ) );
		}

		$logs        = get_option( self::OPTION_SUBMISSION_LOGS, array() );
		$logs        = is_array( $logs ) ? $logs : array();
		$log_filters = $this->get_submission_log_filters_from_request();
		$log_filters['newsletter_consent'] = 'all';
		$logs        = $this->filter_submission_logs( $logs, $log_filters );
		$seen_emails = array();
		$subscribers = array();

		foreach ( $logs as $log ) {
			if ( empty( $log['newsletter_optin'] ) || 'yes' !== $log['newsletter_optin'] ) {
				continue;
			}

			$email = ! empty( $log['email'] ) ? sanitize_email( $log['email'] ) : '';
			if ( empty( $email ) || isset( $seen_emails[ $email ] ) ) {
				continue;
			}

			$seen_emails[ $email ] = true;
			$subscribers[]         = array(
				'email'    => $email,
				'lang'     => ! empty( $log['lang'] ) ? sanitize_key( $log['lang'] ) : '',
				'pdf_id'   => ! empty( $log['pdf_id'] ) ? sanitize_key( $log['pdf_id'] ) : '',
				'datetime' => ! empty( $log['datetime'] ) ? sanitize_text_field( $log['datetime'] ) : '',
			);
		}

		$timestamp = date_i18n( 'Ymd-His', current_time( 'timestamp' ) );
		$filename  = 'dcj-free-pdf-optin-subscribers-' . $timestamp . '.csv';

		nocache_headers();
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		$output = fopen( 'php://output', 'w' );

		if ( false !== $output ) {
			fwrite( $output, "\xEF\xBB\xBF" );
			fputcsv( $output, array( 'メールアドレス', '言語', '登録元PDF ID', '同意日時', 'お知らせ同意' ) );

			foreach ( $subscribers as $subscriber ) {
				fputcsv(
					$output,
					array(
						$subscriber['email'],
						$subscriber['lang'],
						$subscriber['pdf_id'],
						$subscriber['datetime'],
						'同意あり',
					)
				);
			}

			fclose( $output );
		}

		exit;
	}

	/**
	 * 購読者リストをCSV出力します。
	 */
	public function handle_admin_export_subscribers() {

		if ( empty( $_GET['page'] ) || self::PLUGIN_SLUG !== sanitize_key( wp_unslash( $_GET['page'] ) ) ) {
			return;
		}

		if ( empty( $_GET['dcj_fpm_action'] ) || 'export_subscribers' !== sanitize_key( wp_unslash( $_GET['dcj_fpm_action'] ) ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'dcj-free-pdf-mailer' ) );
		}

		if ( empty( $_GET['dcj_fpm_export_subscribers_nonce'] ) ) {
			wp_die( esc_html( 'CSV出力の確認に失敗しました。' ) );
		}

		$nonce = sanitize_text_field( wp_unslash( $_GET['dcj_fpm_export_subscribers_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'dcj_fpm_export_subscribers' ) ) {
			wp_die( esc_html( 'CSV出力の確認に失敗しました。' ) );
		}

		$subscribers = $this->filter_subscribers( $this->get_subscribers( 0 ), $this->get_subscriber_filters_from_request() );
		$timestamp   = date_i18n( 'Ymd-His', current_time( 'timestamp' ) );
		$filename    = 'dcj-free-pdf-subscribers-' . $timestamp . '.csv';

		nocache_headers();
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		$output = fopen( 'php://output', 'w' );

		if ( false !== $output ) {
			fwrite( $output, "\xEF\xBB\xBF" );
			fputcsv( $output, array( 'メールアドレス', '言語', '登録元PDF ID', '登録元タイトル', '初回同意日時', '最終同意日時', '状態' ) );

			foreach ( $subscribers as $subscriber ) {
				fputcsv(
					$output,
					array(
						! empty( $subscriber['email'] ) ? $subscriber['email'] : '',
						! empty( $subscriber['lang'] ) ? $subscriber['lang'] : '',
						! empty( $subscriber['source_pdf_id'] ) ? $subscriber['source_pdf_id'] : '',
						! empty( $subscriber['source_title'] ) ? $subscriber['source_title'] : '',
						! empty( $subscriber['optin_datetime'] ) ? $subscriber['optin_datetime'] : '',
						! empty( $subscriber['last_seen_datetime'] ) ? $subscriber['last_seen_datetime'] : '',
						$this->get_subscriber_status_label( ! empty( $subscriber['status'] ) ? $subscriber['status'] : 'subscribed' ),
					)
				);
			}

			fclose( $output );
		}

		exit;
	}

	/**
	 * 購読者ステータスを更新します。
	 */
	public function handle_admin_update_subscriber_status() {

		if ( empty( $_GET['page'] ) || self::PLUGIN_SLUG !== sanitize_key( wp_unslash( $_GET['page'] ) ) ) {
			return;
		}

		if ( empty( $_GET['dcj_fpm_action'] ) || 'update_subscriber_status' !== sanitize_key( wp_unslash( $_GET['dcj_fpm_action'] ) ) ) {
			return;
		}

		$redirect_url = add_query_arg(
			array(
				'page' => self::PLUGIN_SLUG,
			),
			admin_url( 'admin.php' )
		);

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'dcj-free-pdf-mailer' ) );
		}

		$email  = ! empty( $_GET['subscriber_email'] ) ? strtolower( sanitize_email( wp_unslash( $_GET['subscriber_email'] ) ) ) : '';
		$status = ! empty( $_GET['subscriber_status'] ) ? sanitize_key( wp_unslash( $_GET['subscriber_status'] ) ) : '';

		if ( empty( $email ) || ! in_array( $status, array( 'subscribed', 'unsubscribed' ), true ) ) {
			set_transient( 'dcj_fpm_admin_error', '購読者の状態を更新できませんでした。', 30 );
			wp_safe_redirect( $redirect_url );
			exit;
		}

		if ( empty( $_GET['dcj_fpm_subscriber_status_nonce'] ) ) {
			set_transient( 'dcj_fpm_admin_error', '購読者状態更新の確認に失敗しました。', 30 );
			wp_safe_redirect( $redirect_url );
			exit;
		}

		$nonce = sanitize_text_field( wp_unslash( $_GET['dcj_fpm_subscriber_status_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'dcj_fpm_update_subscriber_status_' . $email ) ) {
			set_transient( 'dcj_fpm_admin_error', '購読者状態更新の確認に失敗しました。', 30 );
			wp_safe_redirect( $redirect_url );
			exit;
		}

		$subscribers = get_option( self::OPTION_SUBSCRIBERS, array() );
		$subscribers = is_array( $subscribers ) ? $subscribers : array();

		if ( empty( $subscribers[ $email ] ) || ! is_array( $subscribers[ $email ] ) ) {
			set_transient( 'dcj_fpm_admin_error', '対象の購読者が見つかりません。', 30 );
			wp_safe_redirect( $redirect_url );
			exit;
		}

		$subscribers[ $email ]['status'] = $status;
		update_option( self::OPTION_SUBSCRIBERS, $subscribers );

		set_transient( 'dcj_fpm_admin_success', '購読者の状態を更新しました。', 30 );

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * 購読者を削除します。
	 */
	public function handle_admin_delete_subscriber() {

		if ( empty( $_GET['page'] ) || self::PLUGIN_SLUG !== sanitize_key( wp_unslash( $_GET['page'] ) ) ) {
			return;
		}

		if ( empty( $_GET['dcj_fpm_delete_subscriber'] ) || '1' !== sanitize_text_field( wp_unslash( $_GET['dcj_fpm_delete_subscriber'] ) ) ) {
			return;
		}

		$redirect_url = add_query_arg(
			array(
				'page' => self::PLUGIN_SLUG,
			),
			admin_url( 'admin.php' )
		);

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'dcj-free-pdf-mailer' ) );
		}

		$email = ! empty( $_GET['subscriber_email'] ) ? strtolower( sanitize_email( wp_unslash( $_GET['subscriber_email'] ) ) ) : '';

		if ( empty( $email ) || ! is_email( $email ) ) {
			set_transient( 'dcj_fpm_admin_error', '削除対象の購読者を確認できませんでした。', 30 );
			wp_safe_redirect( $redirect_url );
			exit;
		}

		check_admin_referer( 'dcj_fpm_delete_subscriber_' . $email, 'dcj_fpm_delete_subscriber_nonce' );

		$subscribers = get_option( self::OPTION_SUBSCRIBERS, array() );
		$subscribers = is_array( $subscribers ) ? $subscribers : array();

		if ( empty( $subscribers[ $email ] ) || ! is_array( $subscribers[ $email ] ) ) {
			set_transient( 'dcj_fpm_admin_error', '対象の購読者が見つかりません。', 30 );
			wp_safe_redirect( $redirect_url );
			exit;
		}

		unset( $subscribers[ $email ] );
		update_option( self::OPTION_SUBSCRIBERS, $subscribers );

		set_transient( 'dcj_fpm_admin_success', '購読者を削除しました。', 30 );

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * 送信ログを全削除します。
	 */
	public function handle_admin_clear_logs() {

		if ( empty( $_GET['page'] ) || self::PLUGIN_SLUG !== sanitize_key( wp_unslash( $_GET['page'] ) ) ) {
			return;
		}

		if ( empty( $_GET['dcj_fpm_action'] ) || 'clear_logs' !== sanitize_key( wp_unslash( $_GET['dcj_fpm_action'] ) ) ) {
			return;
		}

		$redirect_url = add_query_arg(
			array(
				'page' => self::PLUGIN_SLUG,
			),
			admin_url( 'admin.php' )
		);

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'dcj-free-pdf-mailer' ) );
		}

		if ( empty( $_GET['dcj_fpm_clear_logs_nonce'] ) ) {
			set_transient( 'dcj_fpm_admin_error', '送信ログ削除の確認に失敗しました。', 30 );
			wp_safe_redirect( $redirect_url );
			exit;
		}

		$nonce = sanitize_text_field( wp_unslash( $_GET['dcj_fpm_clear_logs_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'dcj_fpm_clear_logs' ) ) {
			set_transient( 'dcj_fpm_admin_error', '送信ログ削除の確認に失敗しました。', 30 );
			wp_safe_redirect( $redirect_url );
			exit;
		}

		update_option( self::OPTION_SUBMISSION_LOGS, array() );
		set_transient( 'dcj_fpm_admin_success', '送信ログを削除しました。', 30 );

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * 管理画面に送信ログ一覧を表示します。
	 */
	private function render_submission_logs() {

		$logs                          = $this->get_submission_logs( 50 );
		$log_filters                   = $this->get_submission_log_filters_from_request();
		$log_email_search              = $log_filters['email_search'];
		$log_pdf_id_search             = $log_filters['pdf_id_search'];
		$log_newsletter_consent_filter = $log_filters['newsletter_consent'];
		$total_log_count               = count( $logs );
		$logs                          = $this->filter_submission_logs( $logs, $log_filters );

		$filtered_log_count = count( $logs );
		$clear_search_url   = add_query_arg(
			array(
				'page' => self::PLUGIN_SLUG,
			),
			admin_url( 'admin.php' )
		);
		$log_export_args    = array(
			'page'                       => self::PLUGIN_SLUG,
			'dcj_fpm_action'             => 'export_logs',
			'dcj_log_email_search'       => $log_email_search,
			'dcj_log_pdf_id_search'      => $log_pdf_id_search,
			'dcj_log_newsletter_consent' => $log_newsletter_consent_filter,
		);
		$export_url         = wp_nonce_url(
			add_query_arg( $log_export_args, admin_url( 'admin.php' ) ),
			'dcj_fpm_export_logs',
			'dcj_fpm_export_logs_nonce'
		);
		$clear_url        = wp_nonce_url(
			add_query_arg(
				array(
					'page'           => self::PLUGIN_SLUG,
					'dcj_fpm_action' => 'clear_logs',
				),
				admin_url( 'admin.php' )
			),
			'dcj_fpm_clear_logs',
			'dcj_fpm_clear_logs_nonce'
		);

		?>
		<h2><?php echo esc_html( '送信ログ' ); ?></h2>
		<p>
			<a class="button" href="<?php echo esc_url( $export_url ); ?>"><?php echo esc_html( '送信ログをCSV出力' ); ?></a>
			<?php if ( 0 < $total_log_count ) : ?>
				<a class="button" href="<?php echo esc_url( $clear_url ); ?>" onclick="return confirm('<?php echo esc_attr( '送信ログをすべて削除します。元に戻せません。よろしいですか？' ); ?>');"><?php echo esc_html( '送信ログをすべて削除' ); ?></a>
			<?php endif; ?>
		</p>
		<p><?php echo esc_html( '送信ログCSVは、フォーム送信履歴の確認用です。お知らせ配信・販売案内・クーポン案内に使うメールアドレスは、購読者リストで「購読中」に絞り込んでCSV出力してください。' ); ?></p>
		<p><?php echo esc_html( '現在の検索・絞り込み条件はCSV出力にも反映されます。' ); ?></p>
		<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" style="margin: 1em 0;">
			<input type="hidden" name="page" value="<?php echo esc_attr( self::PLUGIN_SLUG ); ?>">
			<label for="dcj-log-email-search"><?php echo esc_html( 'メールアドレス検索' ); ?></label>
			<input type="text" id="dcj-log-email-search" name="dcj_log_email_search" value="<?php echo esc_attr( $log_email_search ); ?>">
			<label for="dcj-log-pdf-id-search"><?php echo esc_html( 'PDF管理ID検索' ); ?></label>
			<input type="text" id="dcj-log-pdf-id-search" name="dcj_log_pdf_id_search" value="<?php echo esc_attr( $log_pdf_id_search ); ?>">
			<label for="dcj-log-newsletter-consent"><?php echo esc_html( 'お知らせ同意' ); ?></label>
			<select id="dcj-log-newsletter-consent" name="dcj_log_newsletter_consent">
				<option value="all" <?php selected( $log_newsletter_consent_filter, 'all' ); ?>><?php echo esc_html( 'すべて' ); ?></option>
				<option value="yes" <?php selected( $log_newsletter_consent_filter, 'yes' ); ?>><?php echo esc_html( '同意あり' ); ?></option>
				<option value="no" <?php selected( $log_newsletter_consent_filter, 'no' ); ?>><?php echo esc_html( '同意なし' ); ?></option>
			</select>
			<button type="submit" class="button"><?php echo esc_html( '検索' ); ?></button>
			<a href="<?php echo esc_url( $clear_search_url ); ?>"><?php echo esc_html( 'クリア' ); ?></a>
		</form>
		<p>
			<?php echo esc_html( '送信ログ一覧：' . $total_log_count . '件' ); ?><br>
			<?php echo esc_html( '絞り込み結果：' . $filtered_log_count . '件' ); ?>
		</p>
		<?php if ( empty( $logs ) ) : ?>
			<p><?php echo esc_html( 0 === $total_log_count ? 'まだ送信ログはありません。' : '条件に一致する送信ログはありません。' ); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php echo esc_html( '日時' ); ?></th>
						<th><?php echo esc_html( 'メールアドレス' ); ?></th>
						<th><?php echo esc_html( 'PDF ID' ); ?></th>
						<th><?php echo esc_html( '言語' ); ?></th>
						<th><?php echo esc_html( '結果' ); ?></th>
						<th><?php echo esc_html( 'IPアドレス' ); ?></th>
						<th><?php echo esc_html( 'お知らせ同意' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $logs as $log ) : ?>
						<?php $newsletter_optin_label = ! empty( $log['newsletter_optin'] ) && 'yes' === $log['newsletter_optin'] ? '同意あり' : '同意なし'; ?>
						<tr>
							<td><?php echo esc_html( ! empty( $log['datetime'] ) ? $log['datetime'] : '' ); ?></td>
							<td><?php echo esc_html( ! empty( $log['email'] ) ? $log['email'] : '' ); ?></td>
							<td><?php echo esc_html( ! empty( $log['pdf_id'] ) ? $log['pdf_id'] : '' ); ?></td>
							<td><?php echo esc_html( ! empty( $log['lang'] ) ? $log['lang'] : '' ); ?></td>
							<td><?php echo esc_html( ! empty( $log['result'] ) ? $log['result'] : '' ); ?></td>
							<td><?php echo esc_html( ! empty( $log['ip_address'] ) ? $log['ip_address'] : '' ); ?></td>
							<td><?php echo esc_html( $newsletter_optin_label ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		<?php
	}

	/**
	 * 管理画面に購読者リストを表示します。
	 */
	private function render_subscribers() {

		$subscribers              = $this->get_subscribers( 50 );
		$subscriber_filters       = $this->get_subscriber_filters_from_request();
		$subscriber_search        = $subscriber_filters['search'];
		$subscriber_status_filter = $subscriber_filters['status'];
		$total_subscriber_count   = count( $subscribers );
		$subscribers              = $this->filter_subscribers( $subscribers, $subscriber_filters );

		$filtered_subscriber_count = count( $subscribers );
		$clear_url                 = add_query_arg(
			array(
				'page' => self::PLUGIN_SLUG,
			),
			admin_url( 'admin.php' )
		);
		$export_url                = wp_nonce_url(
			add_query_arg(
				array(
					'page'                  => self::PLUGIN_SLUG,
					'dcj_fpm_action'        => 'export_subscribers',
					'dcj_subscriber_search' => $subscriber_search,
					'dcj_subscriber_status' => $subscriber_status_filter,
				),
				admin_url( 'admin.php' )
			),
			'dcj_fpm_export_subscribers',
			'dcj_fpm_export_subscribers_nonce'
		);

		?>
		<h2><?php echo esc_html( '購読者リスト' ); ?></h2>
		<p>
			<a class="button" href="<?php echo esc_url( $export_url ); ?>"><?php echo esc_html( '購読者リストをCSV出力' ); ?></a>
		</p>
		<p><?php echo esc_html( '現在の検索・絞り込み条件はCSV出力にも反映されます。' ); ?></p>
		<p><?php echo esc_html( 'お知らせ配信・販売案内・クーポン案内に使う場合は、ステータスを「購読中」に絞り込んでCSV出力してください。' ); ?></p>
		<p><?php echo esc_html( '削除前に必要に応じて購読者CSVを出力してください。削除した購読者は元に戻せません。' ); ?></p>
		<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" style="margin: 1em 0;">
			<input type="hidden" name="page" value="<?php echo esc_attr( self::PLUGIN_SLUG ); ?>">
			<label for="dcj-subscriber-search"><?php echo esc_html( 'メールアドレス検索' ); ?></label>
			<input type="text" id="dcj-subscriber-search" name="dcj_subscriber_search" value="<?php echo esc_attr( $subscriber_search ); ?>">
			<label for="dcj-subscriber-status"><?php echo esc_html( 'ステータス' ); ?></label>
			<select id="dcj-subscriber-status" name="dcj_subscriber_status">
				<option value="all" <?php selected( $subscriber_status_filter, 'all' ); ?>><?php echo esc_html( 'すべて' ); ?></option>
				<option value="subscribed" <?php selected( $subscriber_status_filter, 'subscribed' ); ?>><?php echo esc_html( '購読中' ); ?></option>
				<option value="unsubscribed" <?php selected( $subscriber_status_filter, 'unsubscribed' ); ?>><?php echo esc_html( '配信停止' ); ?></option>
			</select>
			<button type="submit" class="button"><?php echo esc_html( '検索' ); ?></button>
			<a href="<?php echo esc_url( $clear_url ); ?>"><?php echo esc_html( 'クリア' ); ?></a>
		</form>
		<p>
			<?php echo esc_html( '購読者一覧：' . $total_subscriber_count . '件' ); ?><br>
			<?php echo esc_html( '絞り込み結果：' . $filtered_subscriber_count . '件' ); ?>
		</p>
		<?php if ( empty( $subscribers ) ) : ?>
			<p><?php echo esc_html( 0 === $total_subscriber_count ? 'まだ購読者はありません。' : '条件に一致する購読者はありません。' ); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php echo esc_html( 'メールアドレス' ); ?></th>
						<th><?php echo esc_html( '言語' ); ?></th>
						<th><?php echo esc_html( '登録元PDF ID' ); ?></th>
						<th><?php echo esc_html( '登録元タイトル' ); ?></th>
						<th><?php echo esc_html( '初回同意日時' ); ?></th>
						<th><?php echo esc_html( '最終同意日時' ); ?></th>
						<th><?php echo esc_html( '状態' ); ?></th>
						<th><?php echo esc_html( '操作' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $subscribers as $subscriber ) : ?>
						<?php
						$subscriber_email  = ! empty( $subscriber['email'] ) ? strtolower( sanitize_email( $subscriber['email'] ) ) : '';
						$subscriber_status = ! empty( $subscriber['status'] ) && 'unsubscribed' === $subscriber['status'] ? 'unsubscribed' : 'subscribed';
						$next_status       = 'subscribed' === $subscriber_status ? 'unsubscribed' : 'subscribed';
						$action_label      = 'subscribed' === $subscriber_status ? '配信停止にする' : '購読中に戻す';
						$confirm_message   = 'subscribed' === $subscriber_status ? 'この購読者を配信停止にします。よろしいですか？' : 'この購読者を購読中に戻します。よろしいですか？';
						$status_url        = wp_nonce_url(
							add_query_arg(
								array(
									'page'              => self::PLUGIN_SLUG,
									'dcj_fpm_action'    => 'update_subscriber_status',
									'subscriber_email'  => $subscriber_email,
									'subscriber_status' => $next_status,
								),
								admin_url( 'admin.php' )
							),
							'dcj_fpm_update_subscriber_status_' . $subscriber_email,
							'dcj_fpm_subscriber_status_nonce'
						);
						$delete_url        = wp_nonce_url(
							add_query_arg(
								array(
									'page'                      => self::PLUGIN_SLUG,
									'dcj_fpm_delete_subscriber' => '1',
									'subscriber_email'          => $subscriber_email,
								),
								admin_url( 'admin.php' )
							),
							'dcj_fpm_delete_subscriber_' . $subscriber_email,
							'dcj_fpm_delete_subscriber_nonce'
						);
						?>
						<tr>
							<td><?php echo esc_html( ! empty( $subscriber['email'] ) ? $subscriber['email'] : '' ); ?></td>
							<td><?php echo esc_html( ! empty( $subscriber['lang'] ) ? $subscriber['lang'] : '' ); ?></td>
							<td><?php echo esc_html( ! empty( $subscriber['source_pdf_id'] ) ? $subscriber['source_pdf_id'] : '' ); ?></td>
							<td><?php echo esc_html( ! empty( $subscriber['source_title'] ) ? $subscriber['source_title'] : '' ); ?></td>
							<td><?php echo esc_html( ! empty( $subscriber['optin_datetime'] ) ? $subscriber['optin_datetime'] : '' ); ?></td>
							<td><?php echo esc_html( ! empty( $subscriber['last_seen_datetime'] ) ? $subscriber['last_seen_datetime'] : '' ); ?></td>
							<td><?php echo esc_html( $this->get_subscriber_status_label( $subscriber_status ) ); ?></td>
							<td>
								<a href="<?php echo esc_url( $status_url ); ?>" onclick="return confirm('<?php echo esc_attr( $confirm_message ); ?>');"><?php echo esc_html( $action_label ); ?></a>
								<?php echo esc_html( ' | ' ); ?>
								<a href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('<?php echo esc_attr( 'この購読者を削除します。元に戻せません。よろしいですか？' ); ?>');"><?php echo esc_html( '削除' ); ?></a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		<?php
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
		$duplicate_source_id = '';
		$duplicate_error     = '';
		$add_values          = array(
			'lang'              => 'ja',
			'type'              => 'set',
			'category'          => 'book_image',
			'audience'          => 'preschool',
			'audience_label'    => '幼児向け・3〜6歳',
			'volume_label'      => 'PDF 5枚セット',
			'delivery_method'   => 'email',
			'migration_status'  => 'pending',
			'title'             => 'サンプル塗り絵 無料PDF',
			'description'       => 'メールアドレスを入力すると、無料PDFのご案内をお送りします。',
			'thumbnail_url'     => '',
			'pdf_url'           => '',
			'mail_subject'      => '【Dream Coloring Journey】無料PDFダウンロードリンクのご案内',
			'mail_body'         => $default_mail_body,
			'button_text'       => '送信する',
			'label_text'        => 'メールアドレス',
			'note_text'         => 'ご入力いただいたメールアドレスは、無料PDFのご案内に使用します。',
			'success_message'   => '無料PDFのご案内メールを送信しました。メールボックスをご確認ください。メールが見つからない場合は、迷惑メールフォルダやプロモーションフォルダもご確認ください。',
			'duplicate_message' => 'すでにお申し込み済みです。メールボックスをご確認ください。',
			'disabled_message'  => 'この無料PDFは現在配布を停止しています。',
			'terms_type'        => 'personal_use_only',
			'terms_text'        => '家庭内での個人利用に限ります。再配布・二次配布・商用利用は禁止です。',
			'source_page_url'   => '',
			'kdp_asin'          => '',
			'kdp_title'         => '',
			'kdp_url'           => '',
			'admin_note'        => '管理用メモ。公開フォームには表示されません。',
			'enabled'           => true,
		);

		if ( ! empty( $_GET['dcj_fpm_action'] ) && 'duplicate' === sanitize_key( wp_unslash( $_GET['dcj_fpm_action'] ) ) ) {
			$duplicate_source_id = ! empty( $_GET['dcj_pdf_id'] ) ? sanitize_key( wp_unslash( $_GET['dcj_pdf_id'] ) ) : '';
			$duplicate_nonce     = ! empty( $_GET['dcj_fpm_duplicate_pdf_item_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['dcj_fpm_duplicate_pdf_item_nonce'] ) ) : '';

			if ( empty( $duplicate_source_id ) || empty( $duplicate_nonce ) || ! wp_verify_nonce( $duplicate_nonce, 'dcj_fpm_duplicate_pdf_item_' . $duplicate_source_id ) ) {
				$duplicate_error     = '複製リクエストの確認に失敗しました。通常の新規追加フォームを表示しています。';
				$duplicate_source_id = '';
			} else {
				$pdf_items = $this->get_pdf_items();

				if ( empty( $pdf_items[ $duplicate_source_id ] ) || ! is_array( $pdf_items[ $duplicate_source_id ] ) ) {
					$duplicate_error     = '複製元のPDF設定が見つかりませんでした。通常の新規追加フォームを表示しています。';
					$duplicate_source_id = '';
				} else {
					$duplicate_fields = array(
						'lang',
						'type',
						'category',
						'audience',
						'audience_label',
						'volume_label',
						'title',
						'description',
						'thumbnail_url',
						'pdf_url',
						'mail_subject',
						'mail_body',
						'button_text',
						'label_text',
						'note_text',
						'success_message',
						'duplicate_message',
						'disabled_message',
						'terms_type',
						'terms_text',
						'source_page_url',
						'kdp_asin',
						'kdp_title',
						'kdp_url',
						'admin_note',
						'enabled',
					);

					foreach ( $duplicate_fields as $field_key ) {
						if ( array_key_exists( $field_key, $pdf_items[ $duplicate_source_id ] ) ) {
							$add_values[ $field_key ] = $pdf_items[ $duplicate_source_id ][ $field_key ];
						}
					}
				}
			}
		}

		?>
		<h2><?php echo esc_html( '新規PDF設定を追加' ); ?></h2>
		<?php if ( ! empty( $duplicate_error ) ) : ?>
			<div class="notice notice-error inline"><p><?php echo esc_html( $duplicate_error ); ?></p></div>
		<?php endif; ?>

		<form method="post" action="" data-dcj-fpm-duplicate="<?php echo esc_attr( ! empty( $duplicate_source_id ) ? '1' : '0' ); ?>">
			<table class="form-table">
				<tr>
					<th scope="row"><label for="dcj_pdf_id"><?php echo esc_html( '管理ID' ); ?> *</label></th>
					<td>
						<input type="text" id="dcj_pdf_id" name="dcj_pdf_id" value="" placeholder="<?php echo esc_attr( ! empty( $duplicate_source_id ) ? '複製元: ' . $duplicate_source_id : 'sample-free-pdf-ja' ); ?>" required />
						<button type="button" class="button dcj-fpm-generate-id-button"><?php echo esc_html( 'ID候補を生成' ); ?></button>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_lang"><?php echo esc_html( '言語' ); ?></label></th>
					<td>
						<select id="dcj_lang" name="dcj_lang">
							<option value="ja" <?php selected( $add_values['lang'], 'ja' ); ?>><?php echo esc_html( '日本語' ); ?></option>
							<option value="en" <?php selected( $add_values['lang'], 'en' ); ?>><?php echo esc_html( '英語' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_type"><?php echo esc_html( '種類' ); ?></label></th>
					<td>
						<select id="dcj_type" name="dcj_type">
							<option value="set" <?php selected( $add_values['type'], 'set' ); ?>><?php echo esc_html( 'セット' ); ?></option>
							<option value="single" <?php selected( $add_values['type'], 'single' ); ?>><?php echo esc_html( '単品' ); ?></option>
							<option value="bonus" <?php selected( $add_values['type'], 'bonus' ); ?>><?php echo esc_html( '特典' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_category"><?php echo esc_html( 'カテゴリ' ); ?></label></th>
					<td>
						<select id="dcj_category" name="dcj_category">
							<?php foreach ( $this->get_category_options() as $category_value => $category_label ) : ?>
								<option value="<?php echo esc_attr( $category_value ); ?>" <?php selected( $add_values['category'], $category_value ); ?>><?php echo esc_html( $category_label ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_audience"><?php echo esc_html( '対象' ); ?></label></th>
					<td>
						<select id="dcj_audience" name="dcj_audience">
							<option value="preschool" <?php selected( $add_values['audience'], 'preschool' ); ?>><?php echo esc_html( '幼児向け' ); ?></option>
							<option value="kids" <?php selected( $add_values['audience'], 'kids' ); ?>><?php echo esc_html( '子ども向け' ); ?></option>
							<option value="family" <?php selected( $add_values['audience'], 'family' ); ?>><?php echo esc_html( '親子向け' ); ?></option>
							<option value="adults" <?php selected( $add_values['audience'], 'adults' ); ?>><?php echo esc_html( '大人向け' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_audience_label"><?php echo esc_html( '対象ラベル' ); ?></label></th>
					<td><input type="text" id="dcj_audience_label" name="dcj_audience_label" value="<?php echo esc_attr( $add_values['audience_label'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_volume_label"><?php echo esc_html( 'ボリュームラベル' ); ?></label></th>
					<td><input type="text" id="dcj_volume_label" name="dcj_volume_label" value="<?php echo esc_attr( $add_values['volume_label'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_delivery_method"><?php echo esc_html( '配布方式' ); ?></label></th>
					<td>
						<select id="dcj_delivery_method" name="dcj_delivery_method">
							<option value="email" <?php selected( $add_values['delivery_method'], 'email' ); ?>><?php echo esc_html( 'メール' ); ?></option>
							<option value="direct" <?php selected( $add_values['delivery_method'], 'direct' ); ?>><?php echo esc_html( 'ダイレクト' ); ?></option>
							<option value="selection_form" <?php selected( $add_values['delivery_method'], 'selection_form' ); ?>><?php echo esc_html( '選択フォーム' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_migration_status"><?php echo esc_html( '移行ステータス' ); ?></label></th>
					<td>
						<select id="dcj_migration_status" name="dcj_migration_status">
							<option value="pending" <?php selected( $add_values['migration_status'], 'pending' ); ?>><?php echo esc_html( 'ペンディング' ); ?></option>
							<option value="converted" <?php selected( $add_values['migration_status'], 'converted' ); ?>><?php echo esc_html( '変換済み' ); ?></option>
							<option value="keep_direct" <?php selected( $add_values['migration_status'], 'keep_direct' ); ?>><?php echo esc_html( 'ダイレクト維持' ); ?></option>
							<option value="disabled" <?php selected( $add_values['migration_status'], 'disabled' ); ?>><?php echo esc_html( '無効' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_title"><?php echo esc_html( 'タイトル' ); ?> *</label></th>
					<td>
						<input type="text" id="dcj_title" name="dcj_title" value="<?php echo esc_attr( $add_values['title'] ); ?>" required />
						<p class="description"><?php echo esc_html( 'フォーム上部に表示される無料PDFのタイトルです。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_description"><?php echo esc_html( '説明' ); ?></label></th>
					<td>
						<textarea id="dcj_description" name="dcj_description" rows="4" cols="50"><?php echo esc_textarea( $add_values['description'] ); ?></textarea>
						<p class="description"><?php echo esc_html( 'フォーム内の説明文として表示されます。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_thumbnail_url"><?php echo esc_html( 'サムネイルURL' ); ?></label></th>
					<td>
						<input type="url" id="dcj_thumbnail_url" name="dcj_thumbnail_url" value="<?php echo esc_url( $add_values['thumbnail_url'] ); ?>" placeholder="<?php echo esc_attr( 'https://example.com/thumbnail.jpg' ); ?>" />
						<button type="button" class="button dcj-fpm-media-select-button" data-target="#dcj_thumbnail_url"><?php echo esc_html( 'メディアから選択' ); ?></button>
						<p class="description"><?php echo esc_html( '将来のカード表示や管理画面プレビュー用の画像URLです。現在のメール送信には使用しません。空欄でも問題ありません。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_pdf_url"><?php echo esc_html( 'PDF URL' ); ?> *</label></th>
					<td>
						<input type="url" id="dcj_pdf_url" name="dcj_pdf_url" value="<?php echo esc_url( $add_values['pdf_url'] ); ?>" placeholder="<?php echo esc_attr( 'https://example.com/free-pdf.pdf' ); ?>" required />
						<button type="button" class="button dcj-fpm-media-select-button" data-target="#dcj_pdf_url"><?php echo esc_html( 'メディアから選択' ); ?></button>
						<p class="description"><?php echo esc_html( '受信メール本文の {{pdf_url}} に入ります。必ずブラウザで直接開けるPDF URLを指定してください。/wp-content/uploads/dlm_uploads/ 配下など、直接アクセス禁止のURLは使用しないでください。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_mail_subject"><?php echo esc_html( 'メール件名' ); ?> *</label></th>
					<td>
						<input type="text" id="dcj_mail_subject" name="dcj_mail_subject" value="<?php echo esc_attr( $add_values['mail_subject'] ); ?>" required />
						<p class="description"><?php echo esc_html( '受信者に届くメールの件名です。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_mail_body"><?php echo esc_html( 'メール本文' ); ?> *</label></th>
					<td>
						<textarea id="dcj_mail_body" name="dcj_mail_body" rows="8" cols="50" required><?php echo esc_textarea( $add_values['mail_body'] ); ?></textarea>
						<p class="description"><?php echo esc_html( '受信者に届くメール本文です。{{title}}、{{pdf_url}}、{{terms_text}}、{{unsubscribe_url}} などの置換タグが使えます。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_button_text"><?php echo esc_html( 'ボタンテキスト' ); ?></label></th>
					<td>
						<input type="text" id="dcj_button_text" name="dcj_button_text" value="<?php echo esc_attr( $add_values['button_text'] ); ?>" />
						<p class="description"><?php echo esc_html( 'フォームの送信ボタンに表示されます。空欄の場合は「送信する / Send」が使われます。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_label_text"><?php echo esc_html( 'ラベルテキスト' ); ?></label></th>
					<td>
						<input type="text" id="dcj_label_text" name="dcj_label_text" value="<?php echo esc_attr( $add_values['label_text'] ); ?>" />
						<p class="description"><?php echo esc_html( 'メールアドレス入力欄の上に表示されます。空欄の場合は「メールアドレス / Email address」が使われます。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_note_text"><?php echo esc_html( '注記テキスト' ); ?></label></th>
					<td>
						<textarea id="dcj_note_text" name="dcj_note_text" rows="3" cols="50"><?php echo esc_textarea( $add_values['note_text'] ); ?></textarea>
						<p class="description"><?php echo esc_html( '送信ボタンの下に表示される補足文です。メールアドレスの利用目的などを記載します。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_success_message"><?php echo esc_html( '成功メッセージ' ); ?></label></th>
					<td>
						<textarea id="dcj_success_message" name="dcj_success_message" rows="2" cols="50"><?php echo esc_textarea( $add_values['success_message'] ); ?></textarea>
						<p class="description"><?php echo esc_html( 'メール送信成功後、フォーム下に表示されます。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_duplicate_message"><?php echo esc_html( '重複メッセージ' ); ?></label></th>
					<td>
						<textarea id="dcj_duplicate_message" name="dcj_duplicate_message" rows="2" cols="50"><?php echo esc_textarea( $add_values['duplicate_message'] ); ?></textarea>
						<p class="description"><?php echo esc_html( '同じメールアドレスで短時間に再送信した場合に表示されます。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_disabled_message"><?php echo esc_html( '無効メッセージ' ); ?></label></th>
					<td>
						<textarea id="dcj_disabled_message" name="dcj_disabled_message" rows="2" cols="50"><?php echo esc_textarea( $add_values['disabled_message'] ); ?></textarea>
						<p class="description"><?php echo esc_html( 'このPDF設定を無効にした場合に表示されます。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_terms_type"><?php echo esc_html( '利用規約タイプ' ); ?></label></th>
					<td>
						<select id="dcj_terms_type" name="dcj_terms_type">
							<option value="personal_use_only" <?php selected( $add_values['terms_type'], 'personal_use_only' ); ?>><?php echo esc_html( '個人利用のみ' ); ?></option>
							<option value="classroom_ok" <?php selected( $add_values['terms_type'], 'classroom_ok' ); ?>><?php echo esc_html( '教室利用可' ); ?></option>
							<option value="none" <?php selected( $add_values['terms_type'], 'none' ); ?>><?php echo esc_html( 'なし' ); ?></option>
							<option value="other" <?php selected( $add_values['terms_type'], 'other' ); ?>><?php echo esc_html( 'その他' ); ?></option>
						</select>
						<p class="description"><?php echo esc_html( '利用条件の分類です。管理用の区分として使います。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_terms_text"><?php echo esc_html( '利用規約テキスト' ); ?></label></th>
					<td>
						<textarea id="dcj_terms_text" name="dcj_terms_text" rows="3" cols="50"><?php echo esc_textarea( $add_values['terms_text'] ); ?></textarea>
						<p class="description"><?php echo esc_html( 'メール本文の {{terms_text}} に入ります。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_admin_note"><?php echo esc_html( '管理メモ' ); ?></label></th>
					<td>
						<textarea id="dcj_admin_note" name="dcj_admin_note" rows="3" cols="50"><?php echo esc_textarea( $add_values['admin_note'] ); ?></textarea>
						<p class="description"><?php echo esc_html( '管理画面一覧で目的確認用に表示されます。公開ページには表示されません。' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dcj_enabled"><?php echo esc_html( '有効' ); ?></label></th>
					<td><input type="checkbox" id="dcj_enabled" name="dcj_enabled" value="1" <?php checked( ! empty( $add_values['enabled'] ) ); ?> /></td>
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
							<input type="url" id="dcj_source_page_url" name="dcj_source_page_url" value="<?php echo esc_url( $add_values['source_page_url'] ); ?>" placeholder="<?php echo esc_attr( 'https://example.com/freebie-page/' ); ?>" />
							<p class="description"><?php echo esc_html( 'この無料PDFを設置・案内している元記事URLです。' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="dcj_kdp_asin"><?php echo esc_html( 'KDP ASIN' ); ?></label></th>
						<td>
							<input type="text" id="dcj_kdp_asin" name="dcj_kdp_asin" value="<?php echo esc_attr( $add_values['kdp_asin'] ); ?>" placeholder="<?php echo esc_attr( 'B0XXXXXXXX' ); ?>" />
							<p class="description"><?php echo esc_html( '関連するAmazon KDP商品のASINです。' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="dcj_kdp_title"><?php echo esc_html( 'KDPタイトル' ); ?></label></th>
						<td>
							<input type="text" id="dcj_kdp_title" name="dcj_kdp_title" value="<?php echo esc_attr( $add_values['kdp_title'] ); ?>" placeholder="<?php echo esc_attr( '関連KDP商品のタイトル' ); ?>" />
							<p class="description"><?php echo esc_html( '関連するKDP書籍・シリーズ名の管理用メモです。' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="dcj_kdp_url"><?php echo esc_html( 'KDP URL' ); ?></label></th>
						<td>
							<input type="url" id="dcj_kdp_url" name="dcj_kdp_url" value="<?php echo esc_url( $add_values['kdp_url'] ); ?>" placeholder="<?php echo esc_attr( 'https://www.amazon.co.jp/dp/B0XXXXXXXX' ); ?>" />
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
						<p class="description"><?php echo esc_html( '受信者に届くメール本文です。{{title}}、{{pdf_url}}、{{terms_text}}、{{unsubscribe_url}} などの置換タグが使えます。' ); ?></p>
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
