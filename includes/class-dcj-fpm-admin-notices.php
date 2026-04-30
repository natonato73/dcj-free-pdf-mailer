<?php
/**
 * Admin notice rendering for DCJ Free PDF Mailer.
 *
 * @package DCJ_Free_PDF_Mailer
 */

// 直接ファイルアクセスを防ぐ
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 管理画面メッセージを表示します。
 */
class DCJ_FPM_Admin_Notices {

	/**
	 * 成功・エラーメッセージを表示します。
	 */
	public static function render() {

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
	}
}
