<?php
/*
Plugin Name: Testing Debug Bar Plugin Activation 2.
Description: Testing Debug Bar Plugin Activation 2.
Version:     1.0
*/


if ( ! function_exists( 'testfile_pa2_activate' ) ) {
	/**
	 * Plugin Activation routine.
	 */
	function testfile_pa2_activate() {

		trigger_error( 'Some activation error' );

		$var = $variable123; // Should trigger undefined notice.
	}
}


register_activation_hook( __FILE__, 'testfile_pa2_activate' );
register_deactivation_hook( __FILE__, 'testfile_pa2_deactivate' ); // Should trigger undefined function.
