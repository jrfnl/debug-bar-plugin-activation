<?php
/**
 * Debug Bar Plugin Activation, a WordPress plugin.
 *
 * @package     WordPress\Plugins\Debug Bar Plugin Activation
 * @author      Juliette Reinders Folmer <wpplugins_nospam@adviesenzo.nl>
 * @link        https://github.com/jrfnl/debug-bar-plugin-activation
 * @since       1.0
 * @version     1.0
 *
 * @copyright   2016 Juliette Reinders Folmer
 * @license     http://creativecommons.org/licenses/GPL/2.0/ GNU General Public License, version 2 or higher
 */

// Avoid direct calls to this file.
if ( ! function_exists( 'add_action' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


if ( ! class_exists( 'Debug_Bar_Plugin_Activation' ) && class_exists( 'Debug_Bar_Panel' ) ) {

	/**
	 * This class extends the functionality provided by the parent plugin "Debug Bar" by adding a
	 * panel showing plugin (de-)activation errors.
	 */
	class Debug_Bar_Plugin_Activation extends Debug_Bar_Panel {

		/**
		 * Plugin version.
		 *
		 * @const string
		 */
		const VERSION = '1.0';

		/**
		 * Version in which the scripts and styles were last updated.
		 * Used to break out of the cache.
		 *
		 * @const string
		 */
		const ASSETS_VERSION = '1.0';

		/**
		 * Plugin slug.
		 *
		 * @const string
		 */
		const NAME = 'debug-bar-plugin-activation';

		/**
		 * Whether or not an attempt has been made to load the textdomain.
		 * If so, no need to try again.
		 *
		 * @var bool
		 */
		private static $textdomain_loaded = false;


		/**
		 * Constructor.
		 */
		public function init() {
			$this->load_textdomain( self::NAME );
			$this->title( __( 'Plugin (de-)activation output', 'debug-bar-plugin-activation' ) );
			$this->set_visible( false );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
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
		 * upped to 4.6. The `languages` directory can be removed in that case too.}}
		 *
		 * @param string $domain Text domain to load.
		 */
		protected function load_textdomain( $domain ) {
			if ( function_exists( '_load_textdomain_just_in_time' ) ) {
				return;
			}

			if ( is_textdomain_loaded( $domain ) || self::$textdomain_loaded ) {
				return;
			}

			$lang_path = dirname( plugin_basename( __FILE__ ) ) . '/languages';
			if ( false === strpos( __FILE__, basename( WPMU_PLUGIN_DIR ) ) ) {
				load_plugin_textdomain( $domain, false, $lang_path );
			} else {
				load_muplugin_textdomain( $domain, $lang_path );
			}
			self::$textdomain_loaded = true;
		}


		/**
		 * Enqueue js and css files.
		 */
		public function enqueue_scripts() {
			$suffix = ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min' );
			wp_enqueue_style( self::NAME, plugins_url( 'css/debug-bar-plugin-activation' . $suffix . '.css', __FILE__ ), array( 'debug-bar' ), self::ASSETS_VERSION );
			wp_enqueue_script( self::NAME, plugins_url( 'js/debug-bar-plugin-activation' . $suffix . '.js', __FILE__ ), array( 'jquery' ), self::ASSETS_VERSION, true );

			wp_localize_script(
				self::NAME,
				'debugBarPluginActivation',
				array(
					'dbpa_nonce' => wp_create_nonce( 'debug-bar-plugin-activation' ),
					'ajaxurl'	 => admin_url( 'admin-ajax.php' ),
					'spinner'    => admin_url( 'images/wpspin_light.gif' ),
					'errorMsg'   => __( 'An error occurred', 'debug-bar-plugin-activation' ),
				)
			);
		}


		/**
		 * Get the total number of issues recorded.
		 *
		 * @return int
		 */
		protected function get_total() {
			$option = get_option( Debug_Bar_Plugin_Activation_Option::NAME );
			return ( count( $option['activate'] ) + count( $option['deactivate'] ) + count( $option['uninstall'] ) );
		}


		/**
		 * Determine whether or not the panel should show.
		 */
		public function prerender() {
			$total = $this->get_total();
			if ( $total > 0 ) {
				$this->set_visible( true );

				// DB 0.9+ - change over to use version nr (defined + nr) if PR is accepted.
				if ( method_exists( 'Debug_Bar', 'enable_debug_bar' ) ) {
					$this->title( $this->title() . '<span class="debug-bar-issue-count">' . absint( $total ) . '</span>' );
				} else {
					$this->title( $this->title() . '<span class="debug-bar-issue-count" style="float: right;">' . absint( $total ) . '</span>' );
				}
			}
		}


		/**
		 * Add classes to the Debug Bar button in the admin bar.
		 *
		 * @param array $classes Existing classes.
		 *
		 * @return array
		 */
		public function debug_bar_classes( $classes ) {
			if ( $this->get_total() > 0 ) {
				$classes[] = 'debug-bar-php-notice-summary';
				$classes[] = 'debug-bar-notice-summary';
			}
			return $classes;
		}


		/**
		 * Renders the panel.
		 */
		public function render() {
			$option = get_option( Debug_Bar_Plugin_Activation_Option::NAME );

			echo '
			<div id="debug-bar-plugin-activation">
				<h2 id="', esc_attr( self::NAME . '-delete-all' ), '"><a href="#">Clear All</a> <span class="spinner"></span></h2>';

			/* translators: 1: line break. */
			$this->render_title( __( 'Total Plugins%1$s with %1$sActivation Issues:', 'debug-bar-plugin-activation' ), count( $option['activate'] ), 'activate' );
			/* translators: 1: line break. */
			$this->render_title( __( 'Total Plugins%1$s with %1$sDe-activation Issues:', 'debug-bar-plugin-activation' ), count( $option['deactivate'] ), 'deactivate' );
			/* translators: 1: line break. */
			$this->render_title( __( 'Total Plugins%1$s with %1$sUninstall Issues:', 'debug-bar-plugin-activation' ), count( $option['uninstall'] ), 'uninstall' );

			$this->render_table( $option, 'activate', __( 'Activation Issues', 'debug-bar-plugin-activation' ) );
			$this->render_table( $option, 'deactivate', __( 'De-activation Issues', 'debug-bar-plugin-activation' ) );
			$this->render_table( $option, 'uninstall', __( 'Uninstall Issues', 'debug-bar-plugin-activation' ) );

			echo '</div>';
		}


		/**
		 * Render a title block for the top of panel.
		 *
		 * @param string $title The title.
		 * @param int    $count Count.
		 * @param string $type  Counter for type.
		 */
		protected function render_title( $title, $count, $type ) {
			echo '<h2><span>', sprintf( esc_html( $title ), '<br />' ), '</span><span class="count ', esc_attr( $type ), '">', absint( $count ), "</span></h2>\n";
		}


		/**
		 * Render a table of (de-)activation issues encountered.
		 *
		 * @param array  $option The option value holding the currently logged (de-)activation issues.
		 * @param string $type   Type of table to generate - either 'activate' or 'deactivate'.
		 * @param string $title  Title for this table.
		 */
		protected function render_table( $option, $type, $title ) {
			$count = count( $option[ $type ] );
			if ( $count > 0 ) {

				echo // WPCS: xss ok.
				'
		<h3 id="dbpa-', esc_attr( $type ), '">', esc_html( $title ), '</h3>

		<table class="debug-bar-table ', self::NAME, '">', $this->get_table_header( $count > 5 ), '
			<tbody>';

				foreach ( $option[ $type ] as $plugin => $notices ) {
					printf( '
				<tr>
					<td><a href="#" data-type="%1$s" data-plugin="%2$s" class="%3$s"><span class="dashicons dashicons-trash"></span></a> <span class="spinner"></span></td>
					<td>%2$s</td>
					<td>%4$s</td>
				</tr>',
						esc_attr( $type ),
						esc_attr( $plugin ),
						esc_attr( self::NAME . '-delete' ),
						wp_kses_post( $notices )
					);
				}

				echo '
			</tbody>
		</table>';
			}
		}


		/**
		 * Create the table header.
		 *
		 * @param bool $double Whether or not to repeat the column labels at the end of the table.
		 *
		 * @return string
		 */
		protected function get_table_header( $double ) {
			static $header_row;

			/* Create header row. */
			if ( ! isset( $header_row ) ) {
				$header_row = '
				<tr>
					<th class="col-1">&nbsp;</th>
					<th class="col-2">' . esc_html__( 'Plugin slug', 'debug-bar-plugin-activation' ) . '</th>
					<th class="col-3">' . esc_html__( 'Unexpected output', 'debug-bar-plugin-activation' ) . '</th>
				</tr>';
			}

			$table_header = '
			<thead>
			' . $header_row . '
			</thead>';

			if ( true === $double ) {
				$table_header .= '
			<tfoot>
			' . $header_row . '
			</tfoot>';
			}

			return $table_header;
		}
	} // End of class Debug_Bar_Plugin_Activation.

} // End of class_exists wrapper.
