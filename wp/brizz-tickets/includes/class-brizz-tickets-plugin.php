<?php
/**
 * Hooks: the shortcode, the block, and the loader script — enqueued only
 * on a page that actually rendered a widget, so the script never loads
 * site-wide.
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'BRIZZ_TICKETS_TESTING' ) ) {
	exit;
}

final class Brizz_Tickets_Plugin {

	const LOADER_HANDLE = 'brizz-checkout-loader';
	const DEFAULT_LOADER = 'https://brizz.me/widget/v1/checkout.js';
	const OPTION = 'brizz_tickets_options';

	public static function boot() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_filter( 'script_loader_tag', array( __CLASS__, 'async_tag' ), 10, 2 );
		if ( is_admin() ) {
			Brizz_Tickets_Settings::boot();
		}
	}

	public static function register() {
		add_shortcode( 'brizz_tickets', array( __CLASS__, 'shortcode' ) );
		register_block_type(
			BRIZZ_TICKETS_DIR . 'blocks/tickets',
			array( 'render_callback' => array( __CLASS__, 'render_block' ) )
		);
		// Registered now, enqueued only where output() runs. No version
		// query on a remote URL — brizz.me controls that file's caching.
		wp_register_script( self::LOADER_HANDLE, self::loader_url(), array(), null, true );
	}

	/** Overridable for a staging Brizz: add_filter( 'brizz_tickets_loader_url', … ). */
	public static function loader_url() {
		return (string) apply_filters( 'brizz_tickets_loader_url', self::DEFAULT_LOADER );
	}

	/** @return array organiser | event | mode, as saved on the settings page. */
	public static function defaults() {
		$options = get_option( self::OPTION, array() );
		return is_array( $options ) ? $options : array();
	}

	/**
	 * [brizz_tickets]                              → from settings
	 * [brizz_tickets event="uuid" mode="button"]   → one event
	 * [brizz_tickets events="a,b" ]                → several
	 * [brizz_tickets organiser="uuid"]             → all upcoming
	 */
	public static function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'event'     => '',
				'events'    => '',
				'organiser' => '',
				'mode'      => '',
				'label'     => '',
			),
			is_array( $atts ) ? $atts : array(),
			'brizz_tickets'
		);

		return self::output( $atts );
	}

	/** The block is dynamic: its attributes come here and share the renderer. */
	public static function render_block( $attributes ) {
		$attributes = is_array( $attributes ) ? $attributes : array();
		$source     = isset( $attributes['source'] ) ? $attributes['source'] : 'default';
		$atts       = array(
			'mode'  => isset( $attributes['mode'] ) ? $attributes['mode'] : '',
			'label' => isset( $attributes['label'] ) ? $attributes['label'] : '',
		);
		if ( 'event' === $source ) {
			$atts['event'] = isset( $attributes['uuid'] ) ? $attributes['uuid'] : '';
		} elseif ( 'events' === $source ) {
			$atts['events'] = isset( $attributes['uuids'] ) ? $attributes['uuids'] : '';
		} elseif ( 'organiser' === $source ) {
			$atts['organiser'] = isset( $attributes['uuid'] ) ? $attributes['uuid'] : '';
		}

		$html = self::output( $atts );
		if ( '' === $html ) {
			return '';
		}
		$wrapper = function_exists( 'get_block_wrapper_attributes' )
			? get_block_wrapper_attributes()
			: 'class="wp-block-brizz-tickets"';

		return '<div ' . $wrapper . '>' . $html . '</div>';
	}

	private static function output( array $atts ) {
		$html = Brizz_Tickets_Renderer::html( $atts, self::defaults() );
		if ( '' === $html ) {
			// Nothing to show. Say why to someone who can fix it; visitors see nothing.
			if ( function_exists( 'current_user_can' ) && current_user_can( 'edit_posts' ) ) {
				return '<p class="brizz-tickets-note">'
					. esc_html__( 'Brizz Tickets: nothing to show yet. Paste your snippet under Settings → Brizz Tickets, or give this block an event.', 'brizz-tickets' )
					. '</p>';
			}
			return '';
		}
		wp_enqueue_script( self::LOADER_HANDLE );

		return $html;
	}

	/** The loader is meant to be async; WordPress < 6.3 has no strategy API, so add the attribute here. */
	public static function async_tag( $tag, $handle ) {
		if ( self::LOADER_HANDLE !== $handle || false !== strpos( $tag, ' async' ) ) {
			return $tag;
		}
		return str_replace( ' src=', ' async src=', $tag );
	}
}
