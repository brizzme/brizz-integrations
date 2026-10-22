<?php
/**
 * The one place that turns "what to show" into the widget's HTML.
 *
 * Both the shortcode and the block end here, so there is exactly one
 * contract with the Brizz loader (public/widget/v1/checkout.js in
 * brizz-website-v2):
 *
 *   <div data-brizz-checkout data-event-uuid="…"            data-mode="card|button">
 *   <div data-brizz-checkout data-event-uuids="a,b,c"       data-mode="card-grid|button">
 *   <div data-brizz-checkout data-organiser="<user uuid>"   data-mode="card-grid|button">
 *
 * Precedence mirrors the loader and the backend: an explicit event list
 * beats one event beats the organiser; what the editor put on the block
 * beats the plugin's settings. Pure PHP, no WordPress calls beyond
 * esc_attr(), so it is unit-tested without a WordPress install.
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'BRIZZ_TICKETS_TESTING' ) ) {
	exit;
}

final class Brizz_Tickets_Renderer {

	const MODES = array( 'card', 'button', 'card-grid' );

	/** Lower-case RFC 4122 shape; Brizz issues v4 uuids. */
	const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/';

	/** The loader caps a grid at ten events; more would be silently cut. */
	const MAX_EVENTS = 10;

	/** Button text — a label, not a paragraph. */
	const LABEL_MAX = 60;

	/**
	 * Resolve attributes + settings into what will be rendered.
	 *
	 * @param array $atts     event | events | organiser | mode | label (strings, from the shortcode or block).
	 * @param array $defaults organiser | event | mode (the plugin's settings).
	 * @return array|null  array( 'selector' => array, 'mode' => string, 'label' => string ), or null when nothing valid was given.
	 */
	public static function normalise( array $atts, array $defaults = array() ) {
		$events    = self::uuid_list( isset( $atts['events'] ) ? $atts['events'] : '' );
		$event     = self::uuid( isset( $atts['event'] ) ? $atts['event'] : '' );
		$organiser = self::uuid( isset( $atts['organiser'] ) ? $atts['organiser'] : '' );

		if ( $events ) {
			$selector = array( 'events' => $events );
		} elseif ( $event ) {
			$selector = array( 'event' => $event );
		} elseif ( $organiser ) {
			$selector = array( 'organiser' => $organiser );
		} else {
			// From settings: the organiser (all upcoming events) is the
			// paste-once default; a single event is the fallback.
			$default_organiser = self::uuid( isset( $defaults['organiser'] ) ? $defaults['organiser'] : '' );
			$default_event     = self::uuid( isset( $defaults['event'] ) ? $defaults['event'] : '' );
			if ( $default_organiser ) {
				$selector = array( 'organiser' => $default_organiser );
			} elseif ( $default_event ) {
				$selector = array( 'event' => $default_event );
			} else {
				return null;
			}
		}

		$mode = strtolower( trim( (string) ( isset( $atts['mode'] ) ? $atts['mode'] : '' ) ) );
		if ( ! in_array( $mode, self::MODES, true ) ) {
			$mode = strtolower( trim( (string) ( isset( $defaults['mode'] ) ? $defaults['mode'] : '' ) ) );
			if ( ! in_array( $mode, self::MODES, true ) ) {
				$mode = 'card';
			}
		}
		// One card cannot show several events; the grid can, and so can the
		// button (the checkout opens with a chooser).
		if ( 'card' === $mode && ( isset( $selector['events'] ) || isset( $selector['organiser'] ) ) ) {
			$mode = 'card-grid';
		}

		$label = trim( strip_tags( (string) ( isset( $atts['label'] ) ? $atts['label'] : '' ) ) );
		if ( strlen( $label ) > self::LABEL_MAX ) {
			$label = substr( $label, 0, self::LABEL_MAX );
		}

		return array(
			'selector' => $selector,
			'mode'     => $mode,
			'label'    => $label,
		);
	}

	/**
	 * The widget element, or '' when there is nothing valid to show.
	 *
	 * @param array $atts     See normalise().
	 * @param array $defaults See normalise().
	 * @return string
	 */
	public static function html( array $atts, array $defaults = array() ) {
		$n = self::normalise( $atts, $defaults );
		if ( null === $n ) {
			return '';
		}

		$parts = array( 'class="brizz-tickets"', 'data-brizz-checkout' );
		if ( isset( $n['selector']['events'] ) ) {
			$parts[] = 'data-event-uuids="' . esc_attr( implode( ',', $n['selector']['events'] ) ) . '"';
		} elseif ( isset( $n['selector']['event'] ) ) {
			$parts[] = 'data-event-uuid="' . esc_attr( $n['selector']['event'] ) . '"';
		} else {
			$parts[] = 'data-organiser="' . esc_attr( $n['selector']['organiser'] ) . '"';
		}
		$parts[] = 'data-mode="' . esc_attr( $n['mode'] ) . '"';
		if ( 'button' === $n['mode'] && '' !== $n['label'] ) {
			$parts[] = 'data-label="' . esc_attr( $n['label'] ) . '"';
		}

		return '<div ' . implode( ' ', $parts ) . '></div>';
	}

	/**
	 * What the organiser pasted on the settings page — the whole snippet
	 * from brizz.me, a line of it, or a bare uuid. Nobody should have to
	 * find a uuid inside HTML by hand.
	 *
	 * @param string $text
	 * @return array  organiser | event | events (resolved from the snippet), or uuid (a bare one — the caller decides what it is), or empty.
	 */
	public static function parse_snippet( $text ) {
		$text  = (string) $text;
		$found = array();

		if ( preg_match( '/data-organiser=["\']?([0-9a-fA-F-]{36})/', $text, $m ) && self::uuid( $m[1] ) ) {
			$found['organiser'] = self::uuid( $m[1] );
		}
		if ( preg_match( '/data-event-uuid=["\']?([0-9a-fA-F-]{36})/', $text, $m ) && self::uuid( $m[1] ) ) {
			$found['event'] = self::uuid( $m[1] );
		}
		if ( preg_match( '/data-event-uuids=["\']([^"\']+)/', $text, $m ) ) {
			$list = self::uuid_list( $m[1] );
			if ( $list ) {
				$found['events'] = $list;
			}
		}
		if ( ! $found ) {
			$bare = self::uuid( $text );
			if ( $bare ) {
				$found['uuid'] = $bare;
			}
		}

		return $found;
	}

	/** @return string  the lower-cased uuid, or '' when it is not one. */
	public static function uuid( $value ) {
		$value = strtolower( trim( (string) $value ) );
		return preg_match( self::UUID_PATTERN, $value ) ? $value : '';
	}

	/** @return string[]  valid, de-duplicated, order kept, capped at MAX_EVENTS. */
	public static function uuid_list( $value ) {
		$out = array();
		foreach ( preg_split( '/[\s,]+/', (string) $value ) as $piece ) {
			$u = self::uuid( $piece );
			if ( '' !== $u && ! in_array( $u, $out, true ) ) {
				$out[] = $u;
				if ( count( $out ) >= self::MAX_EVENTS ) {
					break;
				}
			}
		}
		return $out;
	}
}
