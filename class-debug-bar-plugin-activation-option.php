<?php
/**
 * Option Management.
 *
 * @package WordPress\Plugins\Debug Bar Plugin Activation
 * @subpackage Option
 */

// Avoid direct calls to this file.
if ( ! function_exists( 'add_action' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! class_exists( 'Debug_Bar_Plugin_Activation_Option' ) ) {

	/**
	 * Debug Bar Plugin Activation Option Management.
	 */
	class Debug_Bar_Plugin_Activation_Option {

		/* *** DEFINE CLASS CONSTANTS *** */

		/**
		 * Name of options variable containing the plugin proprietary settings.
		 *
		 * @const string
		 */
		const NAME = 'debug_bar_plugin_activation';

		/* *** DEFINE CLASS PROPERTIES *** */

		/**
		 * Default option values.
		 *
		 * @var array
		 */
		protected $defaults = array(
			'version'    => null,
			'activate'   => array(),
			'deactivate' => array(),
			'uninstall'  => array(),
		);


		/* *** CLASS METHODS *** */


		/**
		 * Initialize our option and add all relevant actions and filters
		 */
		public function __construct() {
			/*
			 * Make sure the option will always get validated, independently of register_setting()
			 * which is only available in the back-end.
			 */
			add_filter( 'sanitize_option_' . self::NAME, array( $this, 'validate_option' ) );

			/* Add filters which get applied to get_options() results. */
			$this->add_default_filter();
			add_filter( 'option_' . self::NAME, array( $this, 'filter_option' ) );

			/*
			 * The option validation routines remove the default filters to prevent failing to insert
			 * an options if it's new. Let's add them back afterwards.
			 *
			 * {@internal This was needed due to bug #31047 and fixed with commit #31473 in WP 4.2.
			 * Should at some point in the future when the minimum WP version for the plugin gets
			 * upped, be removed.}}
			 */
			add_action( 'add_option', array( $this, 'add_default_filter' ) );
			add_action( 'update_option', array( $this, 'add_default_filter' ) );


			// Start buffering the output for those actions in which WP does not do so natively.
			add_action( 'deactivate_plugin', array( $this, 'start_output_buffer' ) );
			add_action( 'pre_uninstall_plugin', array( $this, 'start_output_buffer' ) );
			add_action( 'delete_plugin', array( $this, 'start_output_buffer' ) );

			// Save (de-)activation output to our option.
			add_action( 'activated_plugin', array( $this, 'save_activation_output' ) );
			add_action( 'deactivated_plugin', array( $this, 'save_deactivation_output' ) );
			add_action( 'deleted_plugin', array( $this, 'save_deletion_output' ) );

			// Remove plugin from our option on delete.
			add_action( 'deleted_plugin', array( $this, 'remove_plugin_data' ) );
		}


		/**
		 * Start the output buffer for deleting plugins.
		 *
		 * The plugin may or may not have an uninstall routine. If it does, the output
		 * buffering is started there (`pre_uninstall_plugin`). Otherwise it is started at
		 * the next earliest opportunity (`delete_plugin`).
		 *
		 * @param string $plugin The plugin being deleted.
		 */
		public function start_output_buffer( $plugin ) {
			static $plugins;

			if ( ! isset( $plugins[ $plugin ] ) ) {
				ob_start();
				$plugins[ $plugin ] = true;
			}
		}


		/**
		 * Save any output generated during plugin activation.
		 *
		 * @param string $plugin The plugin being activated.
		 */
		public function save_activation_output( $plugin ) {
			$this->update_option_with_output( $plugin, 'activate' );
		}


		/**
		 * Save any output generated during plugin deactivation and stop the output buffering.
		 *
		 * @param string $plugin The plugin being deactivated.
		 */
		public function save_deactivation_output( $plugin ) {
			$this->update_option_with_output( $plugin, 'deactivate' );
			ob_end_clean();
		}


		/**
		 * Save any output generated during plugin deletion and stop the output buffering.
		 *
		 * @param string $plugin The plugin being deleted.
		 */
		public function save_deletion_output( $plugin ) {
			$this->update_option_with_output( $plugin, 'uninstall' );
			ob_end_clean();
		}


		/**
		 * Save the output from the output buffer to our option.
		 *
		 * @param string $plugin The plugin currently being handled.
		 * @param string $action Which action was executed - either 'activate', 'deactivate'
		 *                       or 'uninstall'.
		 */
		protected function update_option_with_output( $plugin, $action ) {
			$option = get_option( self::NAME );
			$output = ob_get_contents();
			$output = trim( $output );
			if ( '' !== $output ) {
				$option[ $action ][ $plugin ] = $output;
				update_option( self::NAME, $option );
			}
		}


		/**
		 * Delete activation and deactivation output related to a plugin on deletion of
		 * that plugin - will be done automagically by the validation routine.
		 *
		 * @param string $plugin The plugin being deleted.
		 */
		public function remove_plugin_data( $plugin ) {
			update_option( self::NAME, get_option( self::NAME ) );
		}


		/**
		 * Add filtering of the option default values.
		 *
		 * @return void
		 */
		public function add_default_filter() {
			if ( false === has_filter( 'default_option_' . self::NAME, array( $this, 'filter_option_defaults' ) ) ) {
				add_filter( 'default_option_' . self::NAME, array( $this, 'filter_option_defaults' ) );
			};
		}


		/**
		 * Remove filtering of the option default values.
		 *
		 * This is needed to allow for inserting of the option if it doesn't exist.
		 * Should be called from our validation routine.
		 *
		 * @return void
		 */
		public function remove_default_filter() {
			remove_filter( 'default_option_' . self::NAME, array( $this, 'filter_option_defaults' ) );
		}


		/**
		 * Filter option defaults.
		 *
		 * This in effect means that get_option() will not return false if the option is not found,
		 * but will instead return our defaults. This way we always have all of our option values available.
		 *
		 * @return array
		 */
		public function filter_option_defaults() {
			return $this->defaults;
		}


		/**
		 * Filter option.
		 *
		 * This in effect means that get_option() will not just return our option from the database,
		 * but will instead return that option merged with our defaults.
		 * This way we always have all of our option values available. Even when we add new option
		 * values (to the defaults array) when the plugin is upgraded.
		 *
		 * @param array $options Current options.
		 *
		 * @return array
		 */
		public function filter_option( $options ) {
			return $this->array_filter_merge( $this->defaults, $options );
		}


		/* *** HELPER METHODS *** */


		/**
		 * Helper method - Combines a fixed array of default values with an options array
		 * while filtering out any keys which are not in the defaults array.
		 *
		 * @static
		 *
		 * @param array	$defaults Entire list of supported defaults.
		 * @param array	$options  Current options.
		 *
		 * @return array Combined and filtered options array.
		 */
		protected function array_filter_merge( $defaults, $options ) {
			$options = (array) $options;
			$return  = array();

			foreach ( $defaults as $name => $default ) {
				if ( array_key_exists( $name, $options ) ) {
					$return[ $name ] = $options[ $name ];
				} else {
					$return[ $name ] = $default;
				}
			}
			return $return;
		}


		/* *** OPTION VALIDATION *** */


		/**
		 * Validated the settings received from our options page.
		 *
		 * @param array $received The new option values.
		 *
		 * @return array Cleaned option to be saved to the db.
		 */
		public function validate_option( $received ) {

			/**
			 * {@internal This was needed due to bug #31047 and fixed with commit #31473 in WP 4.2.
			 * Should at some point in the future when the minimum WP version for the plugin gets
			 * upped, be removed.}}
			 */
			$this->remove_default_filter();

			/* Start off with the defaults. */
			$clean = $this->defaults;

			/*
			   Validate received values and add them to the $clean array if valid:
			   - plugin file should be a valid filename and exist (except for the 'uninstall' section).
			   - output should be a non-empty string.
			 */
			foreach ( $received as $type => $plugins ) {
				if ( 'version' === $type ) {
					continue;
				}

				if ( 'uninstall' !== $type ) {
					foreach ( $plugins as $plugin => $output ) {
						if ( $this->is_valid_plugin_file( $plugin ) && ( ! empty( $output ) && is_string( $output ) ) ) {
							$clean[ $type ][ $plugin ] = $output;
						}
					}
				} else {
					foreach ( $plugins as $plugin => $output ) {
						if ( ! empty( $output ) && is_string( $output ) ) {
							$clean[ $type ][ $plugin ] = $output;
						}
					}
				}
			}

			$clean['version'] = Debug_Bar_Plugin_Activation::VERSION;

			return $clean;
		}


		/**
		 * Validate whether a plugin filename in the form slug/filename.php or filename.php
		 * is a valid installed plugin.
		 *
		 * Based upon code from `wp_get_active_and_valid_plugins()` in `wp-includes/load.php`.
		 *
		 * @param string $plugin Plugin filename.
		 *
		 * @return bool
		 */
		protected function is_valid_plugin_file( $plugin ) {
			return ( 0 === validate_file( $plugin ) // $plugin must validate as file
					&& '.php' === substr( $plugin, -4 ) // $plugin must end with '.php'
					&& file_exists( WP_PLUGIN_DIR . '/' . $plugin ) // $plugin must exist
					);
		}
	} /* End of class. */

} /* End of class exists wrapper. */
