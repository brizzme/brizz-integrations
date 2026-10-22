<?php

use PHPUnit\Framework\TestCase;

/**
 * The renderer is the plugin's whole contract with the Brizz loader; the
 * block and the shortcode are thin wrappers over it. These pin the
 * attribute shapes, the precedence rules and the escaping.
 */
final class RendererTest extends TestCase {

	const ORG = '3f8a3c19-0dfb-4d46-bbf1-f68d4ea5d2d3';
	const EV1 = '3a49348e-76db-4854-84fc-87dc342011a4';
	const EV2 = 'dbb2de56-0d98-448e-ad9a-f6ad46778318';

	protected function setUp(): void {
		$GLOBALS['brizz_test_options']  = array();
		$GLOBALS['brizz_test_errors']   = array();
		$GLOBALS['brizz_test_enqueued'] = array();
		$GLOBALS['brizz_test_can_edit'] = false;
	}

	public function test_an_organiser_renders_the_grid_the_loader_expects(): void {
		$this->assertSame(
			'<div class="brizz-tickets" data-brizz-checkout data-organiser="' . self::ORG . '" data-mode="card-grid"></div>',
			Brizz_Tickets_Renderer::html( array( 'organiser' => self::ORG ) )
		);
	}

	public function test_one_event_is_a_card_by_default_and_a_labelled_button_on_request(): void {
		$this->assertSame(
			'<div class="brizz-tickets" data-brizz-checkout data-event-uuid="' . self::EV1 . '" data-mode="card"></div>',
			Brizz_Tickets_Renderer::html( array( 'event' => self::EV1 ) )
		);
		$this->assertSame(
			// Tags are stripped (their text stays), whitespace trimmed, the rest escaped.
			'<div class="brizz-tickets" data-brizz-checkout data-event-uuid="' . self::EV1 . '" data-mode="button" data-label="Tickets &amp; more x"></div>',
			Brizz_Tickets_Renderer::html( array( 'event' => self::EV1, 'mode' => 'Button', 'label' => ' Tickets & more <b>x</b>' ) )
		);
	}

	public function test_several_events_dedupe_drop_junk_keep_order_and_force_the_grid(): void {
		$html = Brizz_Tickets_Renderer::html( array( 'events' => self::EV2 . ', ' . self::EV1 . ",nonsense,\n" . self::EV2, 'mode' => 'card' ) );
		$this->assertStringContainsString( 'data-event-uuids="' . self::EV2 . ',' . self::EV1 . '"', $html );
		$this->assertStringContainsString( 'data-mode="card-grid"', $html );
	}

	public function test_an_explicit_selector_beats_the_settings_and_a_list_beats_one_event(): void {
		$defaults = array( 'organiser' => self::ORG, 'mode' => 'button' );
		$this->assertStringContainsString( 'data-event-uuid="' . self::EV1 . '"', Brizz_Tickets_Renderer::html( array( 'event' => self::EV1 ), $defaults ) );
		$this->assertStringContainsString( 'data-mode="button"', Brizz_Tickets_Renderer::html( array( 'event' => self::EV1 ), $defaults ), 'the layout default still applies' );
		$this->assertStringContainsString( 'data-event-uuids=', Brizz_Tickets_Renderer::html( array( 'event' => self::EV1, 'events' => self::EV2 ) ) );
	}

	public function test_settings_fill_in_when_nothing_is_given_organiser_first(): void {
		$this->assertStringContainsString( 'data-organiser="' . self::ORG . '"', Brizz_Tickets_Renderer::html( array(), array( 'organiser' => self::ORG, 'event' => self::EV1 ) ) );
		$this->assertStringContainsString( 'data-event-uuid="' . self::EV1 . '"', Brizz_Tickets_Renderer::html( array(), array( 'event' => self::EV1 ) ) );
		$this->assertSame( '', Brizz_Tickets_Renderer::html( array(), array() ) );
		$this->assertSame( '', Brizz_Tickets_Renderer::html( array( 'event' => 'not-a-uuid', 'organiser' => '<script>' ) ) );
	}

	public function test_uuids_are_lower_cased_and_validated_and_a_list_is_capped_at_ten(): void {
		$this->assertSame( self::EV1, Brizz_Tickets_Renderer::uuid( ' ' . strtoupper( self::EV1 ) . ' ' ) );
		$this->assertSame( '', Brizz_Tickets_Renderer::uuid( self::EV1 . 'x' ) );
		$many = array();
		for ( $i = 0; $i < 12; $i++ ) {
			$many[] = sprintf( '%08x-0000-4000-8000-%012d', $i, $i );
		}
		$this->assertCount( 10, Brizz_Tickets_Renderer::uuid_list( implode( ',', $many ) ) );
	}

	public function test_parse_snippet_finds_the_id_in_whatever_the_organiser_pasted(): void {
		$organiser_snippet = "<script src=\"https://brizz.me/widget/v1/checkout.js\" async></script>\n<div data-brizz-checkout data-organiser=\"" . self::ORG . "\" data-mode=\"card-grid\"></div>";
		$this->assertSame( array( 'organiser' => self::ORG ), Brizz_Tickets_Renderer::parse_snippet( $organiser_snippet ) );

		$event_snippet = '<div data-brizz-checkout data-event-uuid="' . strtoupper( self::EV1 ) . '" data-mode="card"></div>';
		$this->assertSame( array( 'event' => self::EV1 ), Brizz_Tickets_Renderer::parse_snippet( $event_snippet ) );

		$this->assertSame( array( 'events' => array( self::EV1, self::EV2 ) ), Brizz_Tickets_Renderer::parse_snippet( 'data-event-uuids="' . self::EV1 . ',' . self::EV2 . '"' ) );
		$this->assertSame( array( 'uuid' => self::ORG ), Brizz_Tickets_Renderer::parse_snippet( "  " . self::ORG . "\n" ) );
		$this->assertSame( array(), Brizz_Tickets_Renderer::parse_snippet( 'hello world' ) );
		$this->assertSame( array(), Brizz_Tickets_Renderer::parse_snippet( 'data-organiser="not-a-uuid-at-all-really-no"' ) );
	}

	// ── the wrappers ─────────────────────────────────────────────────

	public function test_the_shortcode_renders_and_enqueues_the_loader_only_when_it_rendered(): void {
		$this->assertSame( '', Brizz_Tickets_Plugin::shortcode( array() ), 'nothing configured, visitor sees nothing' );
		$this->assertSame( array(), $GLOBALS['brizz_test_enqueued'] );

		$GLOBALS['brizz_test_can_edit'] = true;
		$this->assertStringContainsString( 'nothing to show yet', Brizz_Tickets_Plugin::shortcode( array() ), 'an editor is told why' );
		$this->assertSame( array(), $GLOBALS['brizz_test_enqueued'] );

		$GLOBALS['brizz_test_options']['brizz_tickets_options'] = array( 'organiser' => self::ORG, 'mode' => 'card' );
		$html = Brizz_Tickets_Plugin::shortcode( array( 'label' => 'ignored for cards' ) );
		$this->assertStringContainsString( 'data-organiser="' . self::ORG . '"', $html );
		$this->assertStringNotContainsString( 'data-label', $html );
		$this->assertSame( array( 'brizz-checkout-loader' ), $GLOBALS['brizz_test_enqueued'] );
	}

	public function test_the_block_maps_its_source_to_the_same_renderer_and_wraps_it(): void {
		$one = Brizz_Tickets_Plugin::render_block( array( 'source' => 'event', 'uuid' => self::EV1, 'mode' => 'button', 'label' => 'Go' ) );
		$this->assertSame( '<div class="wp-block-brizz-tickets"><div class="brizz-tickets" data-brizz-checkout data-event-uuid="' . self::EV1 . '" data-mode="button" data-label="Go"></div></div>', $one );

		$all = Brizz_Tickets_Plugin::render_block( array( 'source' => 'organiser', 'uuid' => self::ORG ) );
		$this->assertStringContainsString( 'data-organiser="' . self::ORG . '" data-mode="card-grid"', $all );

		$set = Brizz_Tickets_Plugin::render_block( array( 'source' => 'events', 'uuids' => self::EV1 . ',' . self::EV2 ) );
		$this->assertStringContainsString( 'data-event-uuids="' . self::EV1 . ',' . self::EV2 . '"', $set );

		$this->assertSame( '', Brizz_Tickets_Plugin::render_block( array( 'source' => 'default' ) ), 'no settings, no output, no wrapper' );
	}

	public function test_the_async_attribute_is_added_once_to_the_loader_tag_only(): void {
		$tag = '<script src="https://brizz.me/widget/v1/checkout.js" id="brizz-checkout-loader-js"></script>';
		$this->assertSame( '<script async src="https://brizz.me/widget/v1/checkout.js" id="brizz-checkout-loader-js"></script>', Brizz_Tickets_Plugin::async_tag( $tag, 'brizz-checkout-loader' ) );
		$this->assertSame( $tag, Brizz_Tickets_Plugin::async_tag( $tag, 'jquery' ) );
		$already = '<script async src="x"></script>';
		$this->assertSame( $already, Brizz_Tickets_Plugin::async_tag( $already, 'brizz-checkout-loader' ) );
	}

	// ── settings sanitiser ───────────────────────────────────────────

	public function test_settings_keep_only_what_the_snippet_resolved_to(): void {
		$saved = Brizz_Tickets_Settings::sanitize( array( 'snippet' => '<div data-brizz-checkout data-organiser="' . self::ORG . '" data-mode="card-grid"></div>', 'mode' => 'button' ) );
		$this->assertSame( array( 'organiser' => self::ORG, 'event' => '', 'mode' => 'button' ), $saved );

		// A single-event snippet replaces the organiser; a bare uuid is read as the organiser.
		$GLOBALS['brizz_test_options']['brizz_tickets_options'] = $saved;
		$this->assertSame( array( 'organiser' => '', 'event' => self::EV1, 'mode' => 'card' ), Brizz_Tickets_Settings::sanitize( array( 'snippet' => 'data-event-uuid="' . self::EV1 . '"', 'mode' => 'weird' ) ) );
		$this->assertSame( self::ORG, Brizz_Tickets_Settings::sanitize( array( 'snippet' => self::ORG ) )['organiser'] );
	}

	public function test_settings_leave_the_current_default_alone_on_an_empty_paste_and_clear_on_request(): void {
		$GLOBALS['brizz_test_options']['brizz_tickets_options'] = array( 'organiser' => self::ORG, 'event' => '', 'mode' => 'card' );
		$this->assertSame( self::ORG, Brizz_Tickets_Settings::sanitize( array( 'snippet' => '   ', 'mode' => 'card' ) )['organiser'] );
		$cleared = Brizz_Tickets_Settings::sanitize( array( 'snippet' => '', 'clear' => '1', 'mode' => 'card' ) );
		$this->assertSame( '', $cleared['organiser'] );
		$this->assertSame( '', $cleared['event'] );
	}

	public function test_a_paste_that_is_not_a_snippet_is_refused_with_a_message_and_changes_nothing(): void {
		$GLOBALS['brizz_test_options']['brizz_tickets_options'] = array( 'organiser' => self::ORG, 'event' => '', 'mode' => 'card' );
		$saved = Brizz_Tickets_Settings::sanitize( array( 'snippet' => 'my website is great', 'mode' => 'card' ) );
		$this->assertSame( self::ORG, $saved['organiser'] );
		$this->assertCount( 1, $GLOBALS['brizz_test_errors'] );
		$this->assertStringContainsString( 'does not look like a Brizz snippet', $GLOBALS['brizz_test_errors'][0]['message'] );
	}
}
