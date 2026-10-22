<?php
/**
 * Settings → Brizz Tickets.
 *
 * Three things, no more: paste the snippet from brizz.me (we pull the
 * uuid out of it — nobody types one), pick the default layout, and read
 * the exact host to add to the site list on Brizz. That last one is the
 * reason this page exists: the list is an exact host match, so a site
 * reached as www.example.com must be listed as exactly that, and the
 * widget's only visible failure is "Tickets are currently unavailable".
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'BRIZZ_TICKETS_TESTING' ) ) {
	exit;
}

final class Brizz_Tickets_Settings {

	const PAGE  = 'brizz-tickets';
	const GROUP = 'brizz_tickets';
	const EMBED_PAGE = 'https://brizz.me/my-events/embed';

	public static function boot() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( BRIZZ_TICKETS_FILE ), array( __CLASS__, 'action_links' ) );
	}

	public static function menu() {
		add_options_page(
			__( 'Brizz Tickets', 'brizz-tickets' ),
			__( 'Brizz Tickets', 'brizz-tickets' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render' )
		);
	}

	public static function register() {
		register_setting(
			self::GROUP,
			Brizz_Tickets_Plugin::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => array(),
			)
		);
	}

	public static function action_links( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=' . self::PAGE ) ) . '">' . esc_html__( 'Settings', 'brizz-tickets' ) . '</a>' );
		return $links;
	}

	/**
	 * The form posts a snippet (or nothing), a layout and maybe "clear".
	 * The stored shape is organiser | event | mode — the snippet itself is
	 * never stored, only what it resolved to.
	 */
	public static function sanitize( $input ) {
		$input   = is_array( $input ) ? $input : array();
		$current = Brizz_Tickets_Plugin::defaults();
		$out     = array(
			'organiser' => isset( $current['organiser'] ) ? Brizz_Tickets_Renderer::uuid( $current['organiser'] ) : '',
			'event'     => isset( $current['event'] ) ? Brizz_Tickets_Renderer::uuid( $current['event'] ) : '',
			'mode'      => 'card',
		);

		$snippet = isset( $input['snippet'] ) ? (string) $input['snippet'] : '';
		if ( '' !== trim( $snippet ) ) {
			$found = Brizz_Tickets_Renderer::parse_snippet( $snippet );
			if ( isset( $found['organiser'] ) ) {
				$out['organiser'] = $found['organiser'];
				$out['event']     = '';
			} elseif ( isset( $found['event'] ) ) {
				$out['event']     = $found['event'];
				$out['organiser'] = '';
			} elseif ( isset( $found['uuid'] ) ) {
				// A bare uuid is almost always the organiser's (that is the one
				// the "all events" page shows); an event uuid still works as a
				// grid of one.
				$out['organiser'] = $found['uuid'];
				$out['event']     = '';
			} else {
				add_settings_error(
					self::GROUP,
					'brizz_tickets_snippet',
					__( 'That does not look like a Brizz snippet. Copy it from brizz.me → My Events → Embed on your site.', 'brizz-tickets' )
				);
			}
		}
		if ( ! empty( $input['clear'] ) ) {
			$out['organiser'] = '';
			$out['event']     = '';
		}

		$mode        = sanitize_text_field( isset( $input['mode'] ) ? (string) $input['mode'] : '' );
		$out['mode'] = in_array( $mode, Brizz_Tickets_Renderer::MODES, true ) ? $mode : 'card';

		return $out;
	}

	/** The hosts this site answers on — what the organiser must list on Brizz, exactly. */
	public static function site_hosts() {
		$hosts = array();
		foreach ( array( home_url(), site_url() ) as $url ) {
			$host = wp_parse_url( $url, PHP_URL_HOST );
			if ( is_string( $host ) && '' !== $host ) {
				$hosts[] = strtolower( $host );
			}
		}
		return array_values( array_unique( $hosts ) );
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$o         = Brizz_Tickets_Plugin::defaults();
		$organiser = isset( $o['organiser'] ) ? $o['organiser'] : '';
		$event     = isset( $o['event'] ) ? $o['event'] : '';
		$mode      = isset( $o['mode'] ) && in_array( $o['mode'], Brizz_Tickets_Renderer::MODES, true ) ? $o['mode'] : 'card';
		$hosts     = self::site_hosts();
		$name      = Brizz_Tickets_Plugin::OPTION;
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Brizz Tickets', 'brizz-tickets' ); ?></h1>
			<?php settings_errors( self::GROUP ); ?>

			<h2 class="title"><?php esc_html_e( '1 · Your site on Brizz', 'brizz-tickets' ); ?></h2>
			<p>
				<?php esc_html_e( 'The widget only loads on sites listed on Brizz, and the list matches the exact host. Add this to your site list:', 'brizz-tickets' ); ?>
			</p>
			<ul style="list-style: disc; padding-left: 20px;">
				<?php foreach ( $hosts as $host ) : ?>
					<li><code><?php echo esc_html( $host ); ?></code></li>
				<?php endforeach; ?>
			</ul>
			<p>
				<a class="button" href="<?php echo esc_url( self::EMBED_PAGE ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open Embed on your site at brizz.me', 'brizz-tickets' ); ?></a>
				<span class="description" style="margin-left: 8px;"><?php esc_html_e( 'If visitors see “Tickets are currently unavailable”, the host is missing there — the page lists the sites it was tried from.', 'brizz-tickets' ); ?></span>
			</p>

			<form method="post" action="options.php">
				<?php settings_fields( self::GROUP ); ?>

				<h2 class="title"><?php esc_html_e( '2 · What to show by default', 'brizz-tickets' ); ?></h2>
				<p>
					<?php if ( '' !== $organiser ) : ?>
						<strong><?php esc_html_e( 'All your upcoming events', 'brizz-tickets' ); ?></strong>
						<span class="description"> · <?php esc_html_e( 'organiser', 'brizz-tickets' ); ?> <code><?php echo esc_html( $organiser ); ?></code></span>
					<?php elseif ( '' !== $event ) : ?>
						<strong><?php esc_html_e( 'One event', 'brizz-tickets' ); ?></strong>
						<span class="description"> · <code><?php echo esc_html( $event ); ?></code></span>
					<?php else : ?>
						<strong><?php esc_html_e( 'Nothing yet', 'brizz-tickets' ); ?></strong>
						<span class="description"> · <?php esc_html_e( 'paste your snippet below.', 'brizz-tickets' ); ?></span>
					<?php endif; ?>
				</p>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="brizz-tickets-snippet"><?php esc_html_e( 'Paste your snippet from Brizz', 'brizz-tickets' ); ?></label></th>
						<td>
							<textarea id="brizz-tickets-snippet" name="<?php echo esc_attr( $name ); ?>[snippet]" rows="3" class="large-text code" placeholder='<div data-brizz-checkout data-organiser="…" data-mode="card-grid"></div>'></textarea>
							<p class="description"><?php esc_html_e( 'From brizz.me → My Events → Embed on your site (all events), or an event\'s dashboard → Embed (one event). We only keep the id inside it.', 'brizz-tickets' ); ?></p>
							<?php if ( '' !== $organiser || '' !== $event ) : ?>
								<label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[clear]" value="1"> <?php esc_html_e( 'Clear the current default', 'brizz-tickets' ); ?></label>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="brizz-tickets-mode"><?php esc_html_e( 'Default layout', 'brizz-tickets' ); ?></label></th>
						<td>
							<select id="brizz-tickets-mode" name="<?php echo esc_attr( $name ); ?>[mode]">
								<option value="card" <?php selected( $mode, 'card' ); ?>><?php esc_html_e( 'Card (grid when there are several)', 'brizz-tickets' ); ?></option>
								<option value="button" <?php selected( $mode, 'button' ); ?>><?php esc_html_e( 'Button — “Get tickets”', 'brizz-tickets' ); ?></option>
							</select>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>

			<h2 class="title"><?php esc_html_e( '3 · Put it on a page', 'brizz-tickets' ); ?></h2>
			<p><?php esc_html_e( 'Add the “Brizz Tickets” block where the tickets should appear — or, in any editor, the shortcode:', 'brizz-tickets' ); ?></p>
			<p><code>[brizz_tickets]</code> <span class="description"><?php esc_html_e( 'shows the default above;', 'brizz-tickets' ); ?></span>
				<code>[brizz_tickets event="…" mode="button" label="Get tickets"]</code> <span class="description"><?php esc_html_e( 'one event as a button;', 'brizz-tickets' ); ?></span>
				<code>[brizz_tickets events="a,b,c"]</code> <span class="description"><?php esc_html_e( 'a hand-picked set.', 'brizz-tickets' ); ?></span></p>
			<p class="description"><?php esc_html_e( 'The cards and the checkout appear on the published page, not in the editor. Payments are handled by Brizz and Stripe; every sale shows in your Brizz dashboard.', 'brizz-tickets' ); ?></p>
		</div>
		<?php
	}
}
