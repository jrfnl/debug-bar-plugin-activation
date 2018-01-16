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


if ( ! class_exists( 'Debug_Bar_Plugin_Activation_Init' ) ) {

	/**
	 * Initialize plugin.
	 */
	class Debug_Bar_Plugin_Activation_Init {

		/**
		 * Plugin slug for use with text-domains and CSS classes.
		 *
		 * @var string
		 */
		const NAME = 'debug-bar-plugin-activation';


		/**
		 * Initialize the plugin.
		 *
		 * @return void
		 */
		public static function init() {
			/*
			 * Add the panel.
			 * Add it "high" so it's close to the other error related panels.
			 */
			add_filter( 'debug_bar_panels', array( __CLASS__, 'add_panel' ), 5 );

			// Show admin notice & de-activate itself if debug-bar plugin not active.
			add_action( 'admin_init', array( __CLASS__, 'check_for_debug_bar' ) );

			add_action( 'init', array( __CLASS__, 'load_textdomain' ) );

			/*
			 * Initialize the option which registers the (de-)activation errors.
			 */
			require_once 'class-debug-bar-plugin-activation-option.php';
			$option = new Debug_Bar_Plugin_Activation_Option();

			// Add our ajax actions.
			add_action( 'wp_ajax_debug-bar-plugin-activation_delete', array( __CLASS__, 'do_ajax' ) );
		}


		/**
		 * Load the plugin text strings.
		 *
		 * Compatible with use of the plugin in the must-use plugins directory.
		 *
		 * {@internal No longer needed since WP 4.6, though the language loading in
		 * WP 4.6 only looks at the `wp-content/languages/` directory and disregards
		 * any translations which may be included with the plugin.
		 * This is acceptable for plugins hosted on org, especially if the plugin
		 * is new and never shipped with it's own translations, but not when the plugin
		 * is hosted elsewhere.
		 * Can be removed if/when the minimum required version for this plugin is ever
		 * upped to 4.6. The `languages` directory can be removed in that case too.
		 * See: {@link https://core.trac.wordpress.org/ticket/34213} and
		 * {@link https://core.trac.wordpress.org/ticket/34114} }}
		 */
		public static function load_textdomain() {
			$domain = self::NAME;

			if ( function_exists( '_load_textdomain_just_in_time' ) ) {
				return;
			}

			if ( is_textdomain_loaded( $domain ) ) {
				return;
			}

			$lang_path = dirname( plugin_basename( __FILE__ ) ) . '/languages';
			if ( false === strpos( __FILE__, basename( WPMU_PLUGIN_DIR ) ) ) {
				load_plugin_textdomain( $domain, false, $lang_path );
			} else {
				load_muplugin_textdomain( $domain, $lang_path );
			}
		}


		/**
		 * Add the Debug Bar Plugin Activation panel to the Debug Bar.
		 *
		 * @param array $panels Existing debug bar panels.
		 *
		 * @return array
		 */
		public static function add_panel( $panels ) {
			require_once 'class-debug-bar-plugin-activation.php';
			$panels[] = new Debug_Bar_Plugin_Activation();
			return $panels;
		}


		/**
		 * Check for the Debug Bar plugin being installed & active.
		 *
		 * @return void
		 */
		public static function check_for_debug_bar() {
			$file = plugin_basename( __FILE__ );

			if ( is_admin()
				&& ( ! class_exists( 'Debug_Bar' ) && current_user_can( 'activate_plugins' ) )
				&& is_plugin_active( $file )
			) {
				add_action( 'admin_notices', array( __CLASS__, 'display_admin_notice' ) );

				deactivate_plugins( $file, false, is_network_admin() );

				// Add to recently active plugins list.
				$insert = array( $file => time() );

				if ( ! is_network_admin() ) {
					update_option( 'recently_activated', ( $insert + (array) get_option( 'recently_activated' ) ) );
				} else {
					update_site_option( 'recently_activated', ( $insert + (array) get_site_option( 'recently_activated' ) ) );
				}

				// Prevent trying to activate again on page reload.
				if ( isset( $_GET['activate'] ) ) {
					unset( $_GET['activate'] );
				}
			}
		}


		/**
		 * Display admin notice about activation failure when dependency not found.
		 *
		 * @return void
		 */
		public static function display_admin_notice() {
			echo '<div class="error"><p>';
			printf(
				/* translators: 1: strong open tag; 2: strong close tag; 3: link to plugin installation page; 4: link close tag. */
				esc_html__( 'Activation failed: Debug Bar must be activated to use the %1$sDebug Bar Plugin Activation%2$s Plugin. %3$sVisit your plugins page to install & activate%4$s.', 'debug-bar-plugin-activation' ),
				'<strong>',
				'</strong>',
				'<a href="' . esc_url( admin_url( 'plugin-install.php?tab=search&s=debug+bar' ) ) . '">',
				'</a>'
			);
			echo '</p></div>';
		}


		/**
		 * Verify validity of ajax request and handle it if valid.
		 *
		 * @return void
		 */
		public static function do_ajax() {
			// Verify this is a valid ajax request.
			if ( ! isset( $_POST['dbpa_nonce'] ) || false === wp_verify_nonce( wp_unslash( $_POST['dbpa_nonce'] ), self::NAME ) ) { // WPCS: sanitization ok.
				exit( '-1' );
			}

			// Sanitize the received values.
			$vars = array_map( 'sanitize_text_field', array_map( 'wp_unslash', $_POST ) );

			// Verify we have received the data needed to do anything.
			if ( ! isset( $vars['type'] ) || ( 'all' !== $vars['type'] && '' === trim( $vars['plugin'] ) ) ) {
				exit( '-1' );
			}

			// Validate & handle the received data.
			if ( in_array( $vars['type'], array( 'activate', 'deactivate', 'uninstall', 'all' ), true ) === false ) {
				exit( '-1' );
			}

			$type = $vars['type'];

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
	}
}

Debug_Bar_Plugin_Activation_Init::init();
