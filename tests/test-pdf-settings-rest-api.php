<?php
define( 'ABSPATH', __DIR__ . '/' );

$GLOBALS['dcj_test_options'] = array(
	'dcj_fpm_pdf_items' => array(
		'dcj-047-ja' => array( 'id' => 'dcj-047-ja', 'lang' => 'ja' ),
		'dcj-048-en' => array( 'id' => 'dcj-048-en', 'lang' => 'en' ),
	),
);

function plugin_dir_path( $file ) { return dirname( $file ) . '/'; }
function add_action() {}
function add_shortcode() {}
function register_activation_hook() {}
function register_rest_route() {}
function current_user_can() { return true; }
function home_url() { return 'https://dreamcoloringjourney.com/'; }
function get_option( $key, $default = false ) {
	return array_key_exists( $key, $GLOBALS['dcj_test_options'] )
		? $GLOBALS['dcj_test_options'][ $key ]
		: $default;
}
function update_option( $key, $value ) {
	$GLOBALS['dcj_test_options'][ $key ] = $value;
	return true;
}
function add_option( $key, $value ) { return update_option( $key, $value ); }

function sanitize_key( $value ) {
	$value = strtolower( (string) $value );
	return preg_replace( '/[^a-z0-9_\-]/', '', $value );
}
function sanitize_text_field( $value ) { return trim( strip_tags( (string) $value ) ); }
function sanitize_textarea_field( $value ) { return trim( strip_tags( (string) $value ) ); }
function sanitize_url( $value ) { return filter_var( (string) $value, FILTER_SANITIZE_URL ); }
function esc_url_raw( $value ) { return (string) $value; }
function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }
function rest_sanitize_boolean( $value ) { return filter_var( $value, FILTER_VALIDATE_BOOLEAN ); }
function absint( $value ) { return abs( (int) $value ); }
function wp_unslash( $value ) { return $value; }
function get_bloginfo() { return 'UTF-8'; }
function rest_ensure_response( $value ) { return new WP_REST_Response( $value, 200 ); }
function is_wp_error( $value ) { return $value instanceof WP_Error; }

class WP_REST_Server {
	const READABLE = 'GET';
	const CREATABLE = 'POST';
}

class WP_Error {
	public $code;
	public $message;
	public $data;
	public function __construct( $code, $message, $data = array() ) {
		$this->code = $code;
		$this->message = $message;
		$this->data = $data;
	}
	public function get_error_code() { return $this->code; }
}

class WP_REST_Response {
	public $data;
	public $status;
	public function __construct( $data, $status = 200 ) {
		$this->data = $data;
		$this->status = $status;
	}
}

class DCJ_Test_Request {
	private $payload;
	public function __construct( $payload ) { $this->payload = $payload; }
	public function get_json_params() { return $this->payload; }
	public function get_params() { return $this->payload; }
}

require dirname( __DIR__ ) . '/dcj-free-pdf-mailer.php';

function dcj_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
	echo "PASS: {$message}\n";
}

$plugin = new DCJ_Free_PDF_Mailer();

$list = $plugin->rest_get_pdf_items();
dcj_assert( 2 === $list->data['count'], 'existing PDF item count' );
dcj_assert( 'dcj-049-en' === $list->data['next_ids']['en'], 'next English ID' );

$payload = array(
	'lang'       => 'en',
	'title'      => 'World Landmarks Free Coloring Starter Pack',
	'pdf_url'    => 'https://dreamcoloringjourney.com/wp-content/uploads/free/world-landmarks-starter-en.pdf',
	'category'   => 'book_image',
	'tags'       => array( 'new_pdf', 'adult' ),
	'audience'   => 'adult',
	'enabled'    => true,
);

$created = $plugin->rest_create_pdf_item( new DCJ_Test_Request( $payload ) );
dcj_assert( $created instanceof WP_REST_Response, 'create returns REST response' );
dcj_assert( 201 === $created->status, 'create returns HTTP 201' );
dcj_assert( 'dcj-049-en' === $created->data['id'], 'create uses next ID' );
dcj_assert( true === $created->data['item']['enabled'], 'explicit enabled=true is kept' );
dcj_assert(
	'[dcj_free_pdf id="dcj-049-en"]' === $created->data['shortcode'],
	'create returns shortcode'
);

$duplicate = $plugin->rest_create_pdf_item(
	new DCJ_Test_Request( array_merge( $payload, array( 'id' => 'dcj-049-en' ) ) )
);
dcj_assert( is_wp_error( $duplicate ), 'duplicate ID is rejected' );
dcj_assert( 'dcj_fpm_pdf_id_exists' === $duplicate->get_error_code(), 'duplicate error code' );

$external = $plugin->rest_create_pdf_item(
	new DCJ_Test_Request(
		array_merge(
			$payload,
			array(
				'id'      => 'dcj-050-en',
				'pdf_url' => 'https://example.com/free.pdf',
			)
		)
	)
);
dcj_assert( is_wp_error( $external ), 'external PDF URL is rejected' );
dcj_assert( 'dcj_fpm_pdf_url_not_allowed' === $external->get_error_code(), 'external URL error code' );

echo "ALL TESTS PASSED\n";
