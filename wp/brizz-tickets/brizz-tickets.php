<?php
/**
 * Plugin Name:       Brizz Tickets
 * Plugin URI:        https://brizz.me/host-an-event
 * Description:       Sell tickets for your Brizz events on your own WordPress site — a block and a shortcode that show your event cards and open the Brizz checkout.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Brizz
 * Author URI:        https://brizz.me
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       brizz-tickets
 *
 * What it does, in one paragraph: the Brizz checkout widget is two lines of
 * HTML — a <script> and a <div data-brizz-checkout>. Pasting those into
 * WordPress is harder than it sounds (visual editors turn tags into text,
 * some page builders have no HTML widget, and the site list on Brizz is an
 * exact host match that nobody knows about). This plugin writes the <div>
 * for you from a block or a shortcode, loads the script only on pages that
 * need it, and tells you the exact host to list on Brizz.
 *
 * Nothing runs server-side against Brizz: the plugin only prints HTML. The
 * loader (https://brizz.me/widget/v1/checkout.js) and the checkout run in
 * the visitor's browser and talk to api.brizz.me, as on any site.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BRIZZ_TICKETS_VERSION', '1.0.0' );
define( 'BRIZZ_TICKETS_FILE', __FILE__ );
define( 'BRIZZ_TICKETS_DIR', plugin_dir_path( __FILE__ ) );

require_once BRIZZ_TICKETS_DIR . 'includes/class-brizz-tickets-renderer.php';
require_once BRIZZ_TICKETS_DIR . 'includes/class-brizz-tickets-plugin.php';
require_once BRIZZ_TICKETS_DIR . 'includes/class-brizz-tickets-settings.php';

Brizz_Tickets_Plugin::boot();
