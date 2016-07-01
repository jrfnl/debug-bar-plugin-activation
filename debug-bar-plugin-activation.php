<?php
/**
 * Debug Bar Plugin Activation, a WordPress plugin.
 *
 * @package     WordPress\Plugins\Debug Bar Plugin Activation
 * @author      Juliette Reinders Folmer <wpplugins_nospam@adviesenzo.nl>
 * @link        https://github.com/jrfnl/debug-bar-plugin-activation
 * @version     1.0
 *
 * @copyright   2016 Juliette Reinders Folmer
 * @license     http://creativecommons.org/licenses/GPL/2.0/ GNU General Public License, version 2 or higher
 *
 * @wordpress-plugin
 * Plugin Name: Debug Bar Plugin Activation
 * Plugin URI:  https://wordpress.org/plugins/debug-bar-plugin-activation/
 * Description: Debug Bar Plugin Activation adds a new panel to the Debug Bar which displays plugin (de-)activation errors.
 * Version:     1.0
 * Author:      Juliette Reinders Folmer
 * Author URI:  http://www.adviesenzo.nl/
 * Depends:     Debug Bar
 * Text Domain: debug-bar-plugin-activation
 * Domain Path: /languages
 * Copyright:   2016 Juliette Reinders Folmer
 */

// Avoid direct calls to this file.
if ( ! function_exists( 'add_action' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


/**
 * Make sure the plugin slug for this plugin is always available.
 */
if ( ! defined( 'DB_PA_BASENAME' ) ) {
	define( 'DB_PA_BASENAME', plugin_basename( __FILE__ ) );
}


if ( ! function_exists( 'db_plugin_activation_has_parent_plugin' ) ) {
	add_action( 'admin_init', 'db_plugin_activation_has_parent_plugin' );

	/**
	 * Show admin notice & de-activate itself if the debug-bar parent plugin is not active.
	 */
	function db_plugin_activation_has_parent_plugin() {
		if ( is_admin() && ( ! class_exists( 'Debug_Bar' ) && current_user_can( 'activate_plugins' ) ) && is_plugin_active( DB_PA_BASENAME ) ) {
			add_action( 'admin_notices', create_function( null, 'echo \'<div class="error"><p>\', sprintf( __( \'Activation failed: Debug Bar must be activated to use the <strong>Debug Bar Plugin Activation</strong> Plugin. %sVisit your plugins page to activate.\', \'debug-bar-plugin-activation\' ), \'<a href="\' . admin_url( \'plugins.php#debug-bar\' ) . \'">\' ), \'</a></p></div>\';' ) );

			deactivate_plugins( DB_PA_BASENAME, false, is_network_admin() );

			// Add to recently active plugins list.
			if ( ! is_network_admin() ) {
				update_option( 'recently_activated', ( array( DB_PA_BASENAME => time() ) + (array) get_option( 'recently_activated' ) ) );
			} else {
				update_site_option( 'recently_activated', ( array( DB_PA_BASENAME => time() ) + (array) get_site_option( 'recently_activated' ) ) );
			}

			// Prevent trying again on page reload.
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
		}
	}
}


if ( ! function_exists( 'debug_bar_plugin_activation_panel' ) ) {
	// Add it "high" so it's close to the other error related panels.
	add_filter( 'debug_bar_panels', 'debug_bar_plugin_activation_panel', 5 );

	/**
	 * Add the Debug Bar Plugin Activation panel to the Debug Bar.
	 *
	 * @param array $panels Existing debug bar panels.
	 *
	 * @return array
	 */
	function debug_bar_plugin_activation_panel( $panels ) {
		if ( ! class_exists( 'Debug_Bar_Plugin_Activation' ) ) {
			require_once 'class-debug-bar-plugin-activation.php';
		}
		$panels[] = new Debug_Bar_Plugin_Activation();
		return $panels;
	}
}


// Initialize the option which registers the (de-)activation errors.
if ( ! class_exists( 'Debug_Bar_Plugin_Activation_Option' ) ) {
	require_once 'class-debug-bar-plugin-activation-option.php';
}
$debug_bar_plugin_activation_option = new Debug_Bar_Plugin_Activation_Option;


if ( ! function_exists( 'debug_bar_plugin_activation_do_ajax' ) ) {
	/**
	 * Verify validity of ajax request and handle it if valid.
	 */
	function debug_bar_plugin_activation_do_ajax() {
		// Verify this is a valid ajax request.
		if ( ! isset( $_POST['dbpa_nonce'] ) || false === wp_verify_nonce( wp_unslash( $_POST['dbpa_nonce'] ), 'debug-bar-plugin-activation' ) ) { // WPCS: sanitization ok.
			exit( '-1' );
		}

		// Sanitize the received values.
		$vars = array_map( 'sanitize_text_field', array_map( 'wp_unslash', $_POST ) );

		// Verify we have received the data needed to do anything.
		if ( ! isset( $vars['type'] ) || ( 'all' !== $vars['type'] && '' === trim( $vars['plugin'] ) ) ) {
			exit( '-1' );
		}

		// Validate & handle the received data.
		$type = '';
		if ( in_array( $vars['type'], array( 'activate', 'deactivate', 'uninstall', 'all' ), true ) ) {
			$type = $vars['type'];
		}

		if ( 'all' === $type ) {
			delete_option( Debug_Bar_Plugin_Activation_Option::NAME );
			exit( '1' ); // Success response.

		} else {
			$option = get_option( Debug_Bar_Plugin_Activation_Option::NAME );

			if ( isset( $option[ $type ][ $vars['plugin'] ] ) ) {
				unset( $option[ $type ][ $vars['plugin'] ] );
				update_option( Debug_Bar_Plugin_Activation_Option::NAME, $option );
				exit( '1' ); // Success response.
			}
		}

		/*
		   No valid action received (redundancy, can't really happen as WP wouldn't then call this
		   function, but would return 0 and exit already.
		 */
		exit( '-1' );
	}

	/* Add our ajax actions. */
	add_action( 'wp_ajax_debug-bar-plugin-activation_delete', 'debug_bar_plugin_activation_do_ajax' );
}
