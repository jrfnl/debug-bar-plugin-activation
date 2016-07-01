<?php
/**
 * Code used when the plugin is removed (not just deactivated but actively deleted by the WordPress Admin).
 */

if ( ! current_user_can( 'activate_plugins' ) || ( ! defined( 'ABSPATH' ) || ! defined( 'WP_UNINSTALL_PLUGIN' ) ) ) {
	exit();
}

$var = $undefined; // Should throw undefined notice.
