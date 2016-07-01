<?php
/**
 * Code used when the plugin is removed (not just deactivated but actively deleted by the WordPress Admin).
 *
 * @package WordPress\Plugins\Debug Bar Plugin Activation
 * @subpackage Uninstall
 */

if ( ! current_user_can( 'activate_plugins' ) || ( ! defined( 'ABSPATH' ) || ! defined( 'WP_UNINSTALL_PLUGIN' ) ) ) {
	exit();
}

delete_option( 'debug_bar_plugin_activation' );
