<?php
/**
 * Runs when the plugin is deleted from the Plugins screen. The plugin keeps
 * one option; nothing else is written anywhere.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'brizz_tickets_options' );
