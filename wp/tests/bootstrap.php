<?php
/**
 * Test bootstrap — no WordPress. The renderer and the settings sanitiser
 * touch a handful of WordPress functions; these shims make them run so
 * the tests are deterministic and need nothing installed. The shims are
 * deliberately literal (what the real function would do for these inputs),
 * not re-implementations of WordPress.
 */

define( 'BRIZZ_TICKETS_TESTING', true );

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}
if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}
if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( $text, $domain = null ) {
		return esc_html( $text );
	}
}
if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = null ) {
		return $text;
	}
}
if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $text ) {
		return trim( strip_tags( (string) $text ) );
	}
}
if ( ! function_exists( 'shortcode_atts' ) ) {
	function shortcode_atts( $pairs, $atts, $shortcode = '' ) {
		$out = array();
		foreach ( $pairs as $name => $default ) {
			$out[ $name ] = array_key_exists( $name, $atts ) ? $atts[ $name ] : $default;
		}
		return $out;
	}
}
if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $hook, $value ) {
		return $value;
	}
}

/** In-memory option store + settings-error log, so the sanitiser is testable. */
$GLOBALS['brizz_test_options'] = array();
$GLOBALS['brizz_test_errors']  = array();

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $name, $default = false ) {
		return array_key_exists( $name, $GLOBALS['brizz_test_options'] ) ? $GLOBALS['brizz_test_options'][ $name ] : $default;
	}
}
if ( ! function_exists( 'add_settings_error' ) ) {
	function add_settings_error( $setting, $code, $message, $type = 'error' ) {
		$GLOBALS['brizz_test_errors'][] = array( 'code' => $code, 'message' => $message );
	}
}
if ( ! function_exists( 'current_user_can' ) ) {
	function current_user_can( $cap ) {
		return ! empty( $GLOBALS['brizz_test_can_edit'] );
	}
}
if ( ! function_exists( 'wp_enqueue_script' ) ) {
	function wp_enqueue_script( $handle ) {
		$GLOBALS['brizz_test_enqueued'][] = $handle;
	}
}
if ( ! function_exists( 'get_block_wrapper_attributes' ) ) {
	function get_block_wrapper_attributes() {
		return 'class="wp-block-brizz-tickets"';
	}
}

require_once __DIR__ . '/../brizz-tickets/includes/class-brizz-tickets-renderer.php';
require_once __DIR__ . '/../brizz-tickets/includes/class-brizz-tickets-plugin.php';
require_once __DIR__ . '/../brizz-tickets/includes/class-brizz-tickets-settings.php';
