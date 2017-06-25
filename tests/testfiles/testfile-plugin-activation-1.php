<?php
/**
 * Plugin Name: Testing Debug Bar Plugin Activation 1.
 * Description: Testing Debug Bar Plugin Activation 1.
 * Version:     1.0
 *
 * @package     WordPress\Plugins\Debug Bar Plugin Activation
 * @subpackage  Test
 */

if ( ! function_exists( 'testfile_pa1_activate' ) ) {
	/**
	 * Plugin Activation routine.
	 */
	function testfile_pa1_activate() {
		/* Security check. */
		if ( ! current_user_can( 'activate_plugins' ) ) {
			echo 'You are not allowed to activate plugins';
		}

		echo 'Yeah! You are allowed to activate plugins!';
	}
}


if ( ! function_exists( 'testfile_pa1_deactivate' ) ) {
	/**
	 * Plugin deactivation routine.
	 */
	function testfile_pa1_deactivate() {
		/* Security check. */
		if ( ! current_user_can( 'activate_plugins' ) ) {
			echo 'You are not allowed to de-activate plugins';
		}

		echo 'Yeah! You are allowed to de-activate plugins!';
	}
}


register_activation_hook( __FILE__, 'testfile_pa1_activate' );
register_deactivation_hook( __FILE__, 'testfile_pa1_deactivate' );
