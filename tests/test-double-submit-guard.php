<?php
define( 'ABSPATH', __DIR__ . '/' );

$GLOBALS['dcj_test_options'] = array();

function plugin_dir_path( $file ) { return dirname( $file ) . '/'; }
function add_action() {}
function add_shortcode() {}
function register_activation_hook() {}
function get_option( $key, $default = false ) {
	return array_key_exists( $key, $GLOBALS['dcj_test_options'] )
		? $GLOBALS['dcj_test_options'][ $key ]
		: $default;
}
function update_option( $key, $value ) {
	$GLOBALS['dcj_test_options'][ $key ] = $value;
	return true;
}
function add_option( $key, $value, $deprecated = '', $autoload = null ) {
	if ( array_key_exists( $key, $GLOBALS['dcj_test_options'] ) ) {
		return false;
	}
	$GLOBALS['dcj_test_options'][ $key ] = $value;
	return true;
}
function delete_option( $key ) {
	if ( ! array_key_exists( $key, $GLOBALS['dcj_test_options'] ) ) {
		return false;
	}
	unset( $GLOBALS['dcj_test_options'][ $key ] );
	return true;
}
function sanitize_key( $value ) {
	$value = strtolower( (string) $value );
	return preg_replace( '/[^a-z0-9_\-]/', '', $value );
}
function absint( $value ) { return abs( (int) $value ); }

require dirname( __DIR__ ) . '/dcj-free-pdf-mailer.php';

function dcj_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
	echo "PASS: {$message}\n";
}

$plugin = new DCJ_Free_PDF_Mailer();

$acquire = new ReflectionMethod( 'DCJ_Free_PDF_Mailer', 'acquire_submission_lock' );
$acquire->setAccessible( true );
$release = new ReflectionMethod( 'DCJ_Free_PDF_Mailer', 'release_submission_lock' );
$release->setAccessible( true );
$lock_name_method = new ReflectionMethod( 'DCJ_Free_PDF_Mailer', 'get_submission_lock_option_name' );
$lock_name_method->setAccessible( true );

$key = md5( 'dcj-083-ja' . 'double-submit@example.com' );

dcj_assert( true === $acquire->invoke( $plugin, $key ), 'first in-flight lock is acquired' );
dcj_assert( false === $acquire->invoke( $plugin, $key ), 'second concurrent lock is rejected' );
$release->invoke( $plugin, $key );
dcj_assert( true === $acquire->invoke( $plugin, $key ), 'lock can be acquired again after release' );
$release->invoke( $plugin, $key );

$lock_name = $lock_name_method->invoke( $plugin, $key );
$GLOBALS['dcj_test_options'][ $lock_name ] = time() - DCJ_Free_PDF_Mailer::SUBMISSION_LOCK_EXPIRE - 1;
dcj_assert( true === $acquire->invoke( $plugin, $key ), 'stale in-flight lock is recovered' );
dcj_assert(
	$GLOBALS['dcj_test_options'][ $lock_name ] > time() - 5,
	'stale lock timestamp is replaced with a fresh value'
);
$release->invoke( $plugin, $key );

$source = file_get_contents( dirname( __DIR__ ) . '/dcj-free-pdf-mailer.php' );
$guard = 'if(form.getAttribute("data-dcj-recaptcha-submitting")==="1"){event.preventDefault();return;}';
$client_lock = 'form.setAttribute("data-dcj-recaptcha-submitting","1");';
$disable_button = 'if(submitButton){submitButton.disabled=true;}';
$recaptcha_execute = 'grecaptcha.execute(';

$guard_pos = strpos( $source, $guard );
$lock_pos = strpos( $source, $client_lock );
$disable_pos = strpos( $source, $disable_button );
$execute_pos = strpos( $source, $recaptcha_execute );

dcj_assert( false !== $guard_pos, 'repeat submit event is prevented on the client' );
dcj_assert( false !== $lock_pos, 'client-side submitting lock exists' );
dcj_assert( false !== $disable_pos, 'submit button is disabled during reCAPTCHA processing' );
dcj_assert( false !== $execute_pos, 'reCAPTCHA execution exists' );
dcj_assert( $lock_pos < $execute_pos, 'client-side lock is set before reCAPTCHA execution' );
dcj_assert( $disable_pos < $execute_pos, 'submit button is disabled before reCAPTCHA execution' );

echo "ALL TESTS PASSED\n";
