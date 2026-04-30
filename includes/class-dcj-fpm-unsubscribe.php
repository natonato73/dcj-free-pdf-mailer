<?php
/**
 * Unsubscribe URL helper for DCJ Free PDF Mailer.
 *
 * @package DCJ_Free_PDF_Mailer
 */

// 直接ファイルアクセスを防ぐ
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 配信停止URLの生成・処理を担当します。
 */
class DCJ_FPM_Unsubscribe {

	/**
	 * 購読者リストoption名
	 */
	const OPTION_SUBSCRIBERS = 'dcj_fpm_subscribers';

	/**
	 * 配信停止URLへのアクセスを処理します。
	 */
	public static function handle_request() {

		if ( empty( $_GET['dcj_fpm_unsubscribe'] ) || '1' !== sanitize_text_field( wp_unslash( $_GET['dcj_fpm_unsubscribe'] ) ) ) {
			return;
		}

		$email = ! empty( $_GET['email'] ) ? strtolower( trim( sanitize_email( wp_unslash( $_GET['email'] ) ) ) ) : '';
		$token = ! empty( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
		$lang  = ! empty( $_GET['lang'] ) ? sanitize_key( wp_unslash( $_GET['lang'] ) ) : 'ja';
		$lang  = 'en' === $lang ? 'en' : 'ja';

		if ( empty( $email ) || ! is_email( $email ) || empty( $token ) ) {
			self::render_message( false, $lang );
			return;
		}

		$expected_token = self::get_token( $email );
		$token_valid    = function_exists( 'hash_equals' ) ? hash_equals( $expected_token, $token ) : $expected_token === $token;
		if ( ! $token_valid ) {
			self::render_message( false, $lang );
			return;
		}

		$subscribers = get_option( self::OPTION_SUBSCRIBERS, array() );
		$subscribers = is_array( $subscribers ) ? $subscribers : array();

		if ( ! empty( $subscribers[ $email ] ) && is_array( $subscribers[ $email ] ) ) {
			$subscribers[ $email ]['status'] = 'unsubscribed';
			update_option( self::OPTION_SUBSCRIBERS, $subscribers );
		}

		self::render_message( true, $lang );
	}

	/**
	 * 配信停止URLを生成します。
	 *
	 * @param string $email メールアドレス
	 * @param string $lang 言語
	 * @return string
	 */
	public static function generate_url( $email, $lang = 'ja' ) {

		$email = strtolower( trim( sanitize_email( $email ) ) );
		$token = self::get_token( $email );
		$lang  = 'en' === $lang ? 'en' : 'ja';

		return esc_url_raw(
			home_url(
				'/?dcj_fpm_unsubscribe=1&email=' . rawurlencode( $email ) . '&token=' . rawurlencode( $token ) . '&lang=' . rawurlencode( $lang )
			)
		);
	}

	/**
	 * 配信停止URL用のトークンを生成します。
	 *
	 * @param string $email メールアドレス
	 * @return string
	 */
	private static function get_token( $email ) {

		return hash_hmac( 'sha256', strtolower( trim( $email ) ), wp_salt( 'auth' ) );
	}

	/**
	 * 配信停止結果を簡単なHTMLで表示します。
	 *
	 * @param bool   $success 成功したかどうか
	 * @param string $lang 言語
	 */
	private static function render_message( $success, $lang = 'ja' ) {

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
}
