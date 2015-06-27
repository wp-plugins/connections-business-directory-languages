<?php
/**
 * An extension for the Connections plugin which adds a metabox for languages.
 *
 * @package   Connections Education Levels
 * @category  Extension
 * @author    Steven A. Zahm
 * @license   GPL-2.0+
 * @link      http://connections-pro.com
 * @copyright 2014 Steven A. Zahm
 *
 * @wordpress-plugin
 * Plugin Name:       Connections Languages
 * Plugin URI:        http://connections-pro.com
 * Description:       An extension for the Connections plugin which adds a metabox for languages.
 * Version:           1.1
 * Author:            Steven A. Zahm
 * Author URI:        http://connections-pro.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       connections_lanugages
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists('Connections_Languages') ) {

	class Connections_Languages {

		public function __construct() {

			self::defineConstants();
			self::loadDependencies();

			// This should run on the `plugins_loaded` action hook. Since the extension loads on the
			// `plugins_loaded action hook, call immediately.
			self::loadTextdomain();

			// Register the metabox and fields.
			add_action( 'cn_metabox', array( __CLASS__, 'registerMetabox') );

			// Add the business hours option to the admin settings page.
			// This is also required so it'll be rendered by $entry->getContentBlock( 'languages' ).
			add_filter( 'cn_content_blocks', array( __CLASS__, 'settingsOption') );

			// Add the action that'll be run when calling $entry->getContentBlock( 'languages' ) from within a template.
			add_action( 'cn_output_meta_field-languages', array( __CLASS__, 'block' ), 10, 4 );

			// Register the widget.
			add_action( 'widgets_init', create_function( '', 'register_widget( "CN_Languages_Widget" );' ) );
		}

		/**
		 * Define the constants.
		 *
		 * @access  private
		 * @static
		 * @since  1.0
		 * @return void
		 */
		private static function defineConstants() {

			define( 'CNLANG_CURRENT_VERSION', '1.1' );
			define( 'CNLANG_DIR_NAME', plugin_basename( dirname( __FILE__ ) ) );
			define( 'CNLANG_BASE_NAME', plugin_basename( __FILE__ ) );
			define( 'CNLANG_PATH', plugin_dir_path( __FILE__ ) );
			define( 'CNLANG_URL', plugin_dir_url( __FILE__ ) );
		}

		/**
		 * The widget.
		 *
		 * @access private
		 * @since  1.0
		 * @static
		 * @return void
		 */
		private static function loadDependencies() {

			require_once( CNLANG_PATH . 'includes/class.widgets.php' );
		}


		public static function activate() {


		}

		public static function deactivate() {

		}

		/**
		 * Load the plugin translation.
		 *
		 * Credit: Adapted from Ninja Forms / Easy Digital Downloads.
		 *
		 * @access private
		 * @since  1.0
		 * @static
		 * @uses   apply_filters()
		 * @uses   get_locale()
		 * @uses   load_textdomain()
		 * @uses   load_plugin_textdomain()
		 * @return void
		 */
		public static function loadTextdomain() {

			// Plugin's unique textdomain string.
			$textdomain = 'connections_languages';

			// Filter for the plugin languages folder.
			$languagesDirectory = apply_filters( 'connections_languages_lang_dir', CNLANG_DIR_NAME . '/languages/' );

			// The 'plugin_locale' filter is also used by default in load_plugin_textdomain().
			$locale = apply_filters( 'plugin_locale', get_locale(), $textdomain );

			// Filter for WordPress languages directory.
			$wpLanguagesDirectory = apply_filters(
				'connections_languages_wp_lang_dir',
				WP_LANG_DIR . '/connections-languages/' . sprintf( '%1$s-%2$s.mo', $textdomain, $locale )
			);

			// Translations: First, look in WordPress' "languages" folder = custom & update-secure!
			load_textdomain( $textdomain, $wpLanguagesDirectory );

			// Translations: Secondly, look in plugin's "languages" folder = default.
			load_plugin_textdomain( $textdomain, FALSE, $languagesDirectory );
		}

		/**
		 * Defines the language options.
		 *
		 * Default list is the most spoken lanuages in the world.
		 * @url http://www.nationsonline.org/oneworld/most_spoken_languages.htm
		 * @url http://www.nationsonline.org/oneworld/languages.htm
		 *
		 * @access private
		 * @since  1.0
		 * @static
		 * @uses   apply_filters()
		 * @return array An indexed array containing the languages.
		 */
		private static function languages() {

			$options = array(
				'ara' => __( 'Arabic', 'connections_languages'),
				'ben' => __( 'Bengali', 'connections_languages'),
				'chi' => __( 'Chinese', 'connections_languages'),
				'eng' => __( 'English', 'connections_languages'),
				'fil' => __( 'Filipino', 'connections_languages'),
				'fra' => __( 'French', 'connections_languages'),
				'ger' => __( 'German', 'connections_languages'),
				'hin' => __( 'Hindi', 'connections_languages'),
				'ind' => __( 'Indonesian', 'connections_languages'),
				'ita' => __( 'Italian', 'connections_languages'),
				'jpn' => __( 'Japanese', 'connections_languages'),
				'kor' => __( 'Korean', 'connections_languages'),
				'por' => __( 'Portuguese', 'connections_languages'),
				'rus' => __( 'Russian', 'connections_languages'),
				'slv' => __( 'Slovenian', 'connections_languages'),
				'spa' => __( 'Spanish', 'connections_languages'),
				'tai' => __( 'Tai-Kadai', 'connections_languages'),
				'vie' => __( 'Vietnamese', 'connections_languages'),
			);

			return apply_filters( 'cn_languages_options', $options );
		}

		/**
		 * Return the language based on the supplied key (ISO 639-2, the alpha-3 code).
		 *
		 * @access private
		 * @since  1.0
		 * @static
		 * @uses   levels()
		 * @param  string $code  The key of the language to return.
		 * @return mixed         bool | string	The language if found, if not, FALSE.
		 */
		private static function language( $code = '' ) {

			if ( ! is_string( $code ) || empty( $code ) || $code === '-1' ) {

				return FALSE;
			}

			$languages = self::languages();
			$language  = isset( $languages[ $code ] ) ? $languages[ $code ] : FALSE;

			return $language;
		}

		/**
		 * Registered the custom metabox.
		 *
		 * @access private
		 * @since  1.0
		 * @static
		 * @uses   levels()
		 * @uses   cnMetaboxAPI::add()
		 * @return void
		 */
		public static function registerMetabox() {

			$atts = array(
				'name'     => __( 'Languages', 'connections_languages' ),
				'id'       => 'languages',
				'title'    => __( 'Languages', 'connections_languages' ),
				'context'  => 'side',
				'priority' => 'core',
				'fields'   => array(
					array(
						'id'      => 'languages',
						'type'    => 'checkboxgroup',
						'options' => self::languages(),
						'default' => '',
						),
					),
				);

			cnMetaboxAPI::add( $atts );
		}

		/**
		 * Add the custom meta as an option in the content block settings in the admin.
		 * This is required for the output to be rendered by $entry->getContentBlock().
		 *
		 * @access private
		 * @since  1.0
		 * @param  array  $blocks An associtive array containing the registered content block settings options.
		 * @return array
		 */
		public static function settingsOption( $blocks ) {

			$blocks['languages'] = __( 'Languages', 'connections_languages' );

			return $blocks;
		}

		/**
		 * Renders the Languages content block.
		 *
		 * Called by the cn_meta_output_field-languages action in cnOutput->getMetaBlock().
		 *
		 * @access  private
		 * @since  1.0
		 * @static
		 * @uses   esc_attr()
		 * @uses   language()
		 * @param  string $id    The field id.
		 * @param  array  $value The language codes (ISO 639-2, the alpha-3 code).
		 * @param  array  $atts  The shortcode atts array passed from the calling action.
		 *
		 * @return string
		 */
		public static function block( $id, $value, $object = NULL, $atts ) {

			echo '<ul class="cn-languages">';

			foreach ( $value as $code ) {

				if ( $language = self::language( $code ) ) {

					printf( '<li class="cn-language cn-%1$s">%2$s</li>', esc_attr( $code ), esc_html( $language ) );
				}

			}

			echo '</ul>';
		}

	}

	/**
	 * Start up the extension.
	 *
	 * @access public
	 * @since 1.0
	 *
	 * @return mixed object | bool
	 */
	function Connections_Languages() {

			if ( class_exists('connectionsLoad') ) {

					return new Connections_Languages();

			} else {

				add_action(
					'admin_notices',
					 create_function(
						 '',
						'echo \'<div id="message" class="error"><p><strong>ERROR:</strong> Connections must be installed and active in order use Connections Languages.</p></div>\';'
						)
				);

				return FALSE;
			}
	}

	/**
	 * Since Connections loads at default priority 10, and this extension is dependent on Connections,
	 * we'll load with priority 11 so we know Connections will be loaded and ready first.
	 */
	add_action( 'plugins_loaded', 'Connections_Languages', 11 );

}
