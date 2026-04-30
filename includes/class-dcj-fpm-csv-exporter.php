<?php
/**
 * CSV export helper for DCJ Free PDF Mailer.
 *
 * @package DCJ_Free_PDF_Mailer
 */

// 直接ファイルアクセスを防ぐ
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CSV出力の共通処理を担当します。
 */
class DCJ_FPM_CSV_Exporter {

	/**
	 * CSVを出力して処理を終了します。
	 *
	 * @param string $filename ファイル名
	 * @param array  $rows CSV行
	 */
	public static function output_csv( $filename, $rows ) {

		nocache_headers();
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . basename( $filename ) . '"' );

		$output = fopen( 'php://output', 'w' );

		if ( false !== $output ) {
			fwrite( $output, "\xEF\xBB\xBF" );

			foreach ( $rows as $row ) {
				fputcsv( $output, $row );
			}

			fclose( $output );
		}

		exit;
	}
}
