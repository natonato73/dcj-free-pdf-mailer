<?php
/**
 * reCAPTCHA v3 helper for DCJ Free PDF Mailer.
 *
 * @package DCJ_Free_PDF_Mailer
 */

// 直接ファイルアクセスを防ぐ
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * reCAPTCHA v3の判定・検証処理を担当します。
 */
class DCJ_FPM_Recaptcha {

	/**
	 * reCAPTCHA v3が検証可能な設定か判定します。
	 *
	 * @param array $mail_settings メール送信設定
	 * @return bool
	 */
	public static function is_ready( $mail_settings ) {

		return ! empty( $mail_settings['recaptcha_enabled'] )
			&& ! empty( $mail_settings['recaptcha_site_key'] )
			&& ! empty( $mail_settings['recaptcha_secret_key'] );
	}

	/**
	 * reCAPTCHA v3送信を検証します。
	 *
	 * @param array $mail_settings メール送信設定
	 * @return bool
	 */
	public static function verify_submission( $mail_settings ) {

		if ( ! self::is_ready( $mail_settings ) ) {
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
}
