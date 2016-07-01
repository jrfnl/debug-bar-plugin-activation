<?php
/*
Plugin Name: Testing Debug Bar Plugin Activation 3.
Description: Testing Debug Bar Plugin Activation 3.
Version:     1.0
*/


if ( ! function_exists( 'testfile_pa3_deactivate' ) ) {
	/**
	 * Plugin deactivation routine.
	 */
	function testfile_pa3_deactivate() {
		trigger_error( 'Some de-activation error' );

		$var = $variable123; // Should trigger undefined notice.
	}
}


if ( ! function_exists( 'testfile_pa3_uninstall' ) ) {
	/**
	 * Plugin uninstall routine.
	 */
	function testfile_pa3_uninstall() {
		trigger_error( 'Some uninstall error' );

		$var = $variable123; // Should trigger undefined notice.
	}
}


register_activation_hook( __FILE__, 'testfile_pa3_activate' ); // Should trigger undefined function.
register_deactivation_hook( __FILE__, 'testfile_pa3_deactivate' );
register_uninstall_hook( __FILE__, 'testfile_pa3_uninstall' );
