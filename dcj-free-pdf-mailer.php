<?php
/**
 * Plugin Name: DCJ Free PDF Mailer
 * Plugin URI: https://dreamcoloringjourney.com/
 * Description: Dream Coloring Journey の無料PDF配布フォーム用プラグインです。ショートコードでメール入力フォームを表示し、テストメールを送信します。
 * Version: 0.2.0
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
 * 第2段階では、フォーム送信時にテストメールを送信します。
 * 将来的に、PDF別設定・ログ保存・管理画面を追加する想定です。
 */
class DCJ_Free_PDF_Mailer {

	/**
	 * プラグイン定数
	 */
	const VERSION      = '0.2.0';
	const PLUGIN_SLUG  = 'dcj-free-pdf-mailer';
	const CSS_PREFIX   = 'dcj-fpm-';
	const NONCE_ACTION = 'dcj_free_pdf_submit';
	const NONCE_NAME   = 'dcj_free_pdf_nonce';

	/**
	 * 処理結果メッセージ
	 *
	 * @var string
	 */
	private $message = '';

	/**
	 * コンストラクタ
	 */
	public function __construct() {

		// フォーム送信処理
		add_action( 'init', array( $this, 'handle_form_submit' ) );

		// ショートコード登録
		add_shortcode( 'dcj_free_pdf', array( $this, 'render_form' ) );
	}

	/**
	 * フォーム送信を処理します。
	 *
	 * 第2段階では、入力されたメールアドレス宛にテストメールを送信します。
	 */
	public function handle_form_submit() {

		// このプラグインのフォーム送信でなければ何もしない
		if ( empty( $_POST['dcj_fpm_submit'] ) ) {
			return;
		}

		// nonce が存在するか確認
		if ( empty( $_POST[ self::NONCE_NAME ] ) ) {
			$this->message = $this->get_error_message( '送信確認に失敗しました。もう一度お試しください。' );
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) );

		// nonce チェック
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			$this->message = $this->get_error_message( '送信確認に失敗しました。ページを再読み込みしてから、もう一度お試しください。' );
			return;
		}

		// PDF ID取得
		$pdf_id = '';
		if ( ! empty( $_POST['dcj_pdf_id'] ) ) {
			$pdf_id = sanitize_key( wp_unslash( $_POST['dcj_pdf_id'] ) );
		}

		if ( empty( $pdf_id ) ) {
			$this->message = $this->get_error_message( '無料PDFのIDが確認できませんでした。' );
			return;
		}

		// メールアドレス取得
		$email = '';
		if ( ! empty( $_POST['dcj_email'] ) ) {
			$email = sanitize_email( wp_unslash( $_POST['dcj_email'] ) );
		}

		// メール形式チェック
		if ( empty( $email ) || ! is_email( $email ) ) {
			$this->message = $this->get_error_message( '正しいメールアドレスを入力してください。' );
			return;
		}

		// 第2段階用のテストメール
		$subject = '【テスト】無料PDFメール送信確認';

		$body  = "Dream Coloring Journey の無料PDFメール送信テストです。\n\n";
		$body .= "このメールは、DCJ Free PDF Mailer の第2段階テストとして送信されています。\n\n";
		$body .= "選択された無料PDF ID：\n";
		$body .= $pdf_id . "\n\n";
		$body .= "※今回はテスト送信のため、まだPDFリンクは含めていません。\n";

		$headers = array(
			'Content-Type: text/plain; charset=UTF-8',
		);

		$sent = wp_mail( $email, $subject, $body, $headers );

		if ( $sent ) {
			$this->message = $this->get_success_message( 'テストメールを送信しました。Localのメール受信履歴を確認してください。' );
		} else {
			$this->message = $this->get_error_message( 'メール送信に失敗しました。Localのメール設定を確認してください。' );
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

		return $this->get_form_html( $pdf_id );
	}

	/**
	 * フォームHTMLを生成します。
	 *
	 * @param string $pdf_id PDFの識別ID
	 * @return string フォームHTML
	 */
	private function get_form_html( $pdf_id ) {

		// nonceフィールドを生成
		$nonce_field = wp_nonce_field(
			self::NONCE_ACTION,
			self::NONCE_NAME,
			true,
			false
		);

		// 1ページに複数フォームを置いてもIDが重複しないようにする
		$email_input_id = self::CSS_PREFIX . 'email-' . $pdf_id;

		$html  = '<div class="' . esc_attr( self::CSS_PREFIX . 'form-container' ) . '" data-pdf-id="' . esc_attr( $pdf_id ) . '">';
		$html .= '<form method="post" action="" class="' . esc_attr( self::CSS_PREFIX . 'form' ) . '">';

		// 処理結果メッセージ
		if ( ! empty( $this->message ) ) {
			$html .= $this->message;
		}

		// nonce
		$html .= $nonce_field;

		// このプラグインの送信であることを示す hidden
		$html .= '<input type="hidden" name="dcj_fpm_submit" value="1" />';

		// PDF識別ID
		$html .= '<input type="hidden" name="dcj_pdf_id" value="' . esc_attr( $pdf_id ) . '" />';

		// タイトル
		$html .= '<div class="' . esc_attr( self::CSS_PREFIX . 'title' ) . '">';
		$html .= esc_html( '無料PDFをメールで受け取る' );
		$html .= '</div>';

		// 説明文
		$html .= '<div class="' . esc_attr( self::CSS_PREFIX . 'description' ) . '">';
		$html .= esc_html( 'メールアドレスを入力すると、無料PDFのご案内をお送りします。' );
		$html .= '</div>';

		// メールアドレス入力欄
		$html .= '<div class="' . esc_attr( self::CSS_PREFIX . 'form-group' ) . '">';
		$html .= '<label for="' . esc_attr( $email_input_id ) . '" class="' . esc_attr( self::CSS_PREFIX . 'label' ) . '">';
		$html .= esc_html( 'メールアドレス' );
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
		$html .= esc_html( '送信する' );
		$html .= '</button>';
		$html .= '</div>';

		// 補足文
		$html .= '<div class="' . esc_attr( self::CSS_PREFIX . 'note' ) . '">';
		$html .= esc_html( 'ご入力いただいたメールアドレスは、無料PDFのご案内に使用します。' );
		$html .= '</div>';

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
		return '<div class="' . esc_attr( self::CSS_PREFIX . 'message ' . self::CSS_PREFIX . 'success' ) . '">' . esc_html( $message ) . '</div>';
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
}

/**
 * プラグイン初期化
 */
new DCJ_Free_PDF_Mailer();