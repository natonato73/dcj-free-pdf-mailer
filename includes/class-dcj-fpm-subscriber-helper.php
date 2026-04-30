<?php
/**
 * 購読者管理の補助処理
 *
 * @package DCJ_Free_PDF_Mailer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 購読者管理の補助クラス
 */
class DCJ_FPM_Subscriber_Helper {

	/**
	 * 購読者ステータスの表示名を取得します。
	 *
	 * @param string $status 購読者ステータス
	 * @return string
	 */
	public static function get_status_label( $status ) {

		if ( 'unsubscribed' === $status ) {
			return '配信停止';
		}

		return '購読中';
	}

	/**
	 * 購読者検索条件を取得します。
	 *
	 * @return array
	 */
	public static function get_filters_from_request() {

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
	public static function filter_subscribers( $subscribers, $filters ) {

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
}
