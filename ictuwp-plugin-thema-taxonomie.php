<?php

/**
 * @link                https://github.com/ICTU/ictuwp-plugin-thema-taxonomie
 * @package             ictuwp-plugin-thema-taxonomie
 *
 * @wordpress-plugin
 * Plugin Name:         ICTU / Gebruiker Centraal / Thema taxonomie
 * Plugin URI:          https://github.com/ICTU/ictuwp-plugin-thema-taxonomie
 * Description:         Plugin voor het aanmaken van de 'thema'-taxonomie
 * Version:             1.0.2
 * Version description: Added English translation files.
 * Author:              Paul van Buuren
 * Author URI:          https://github.com/ICTU/ictuwp-plugin-thema-taxonomie/
 * License:             GPL-2.0+
 * License URI:         http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:         gctheme
 * Domain Path:         /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

//========================================================================================================

// Dutch slug for taxonomy
$slug = 'thema';

if ( get_bloginfo( 'language' ) !== 'nl-NL' ) {
	// non Dutch slug for taxonomy
	$slug = 'topic';
}

defined( 'TAX_THEMA' ) or define( 'TAX_THEMA', $slug );
defined( 'TAX_THEMA_OVERVIEW_TEMPLATE' ) or define( 'TAX_THEMA_OVERVIEW_TEMPLATE', 'template-overview-themas.php' );
defined( 'TAX_THEMA_DETAIL_TEMPLATE' ) or define( 'TAX_THEMA_DETAIL_TEMPLATE', 'template-detail-themas.php' );

//========================================================================================================
// only this plugin should activate the TAX_THEMA taxonomy
if ( ! taxonomy_exists( TAX_THEMA ) ) {
	add_action( 'plugins_loaded', array( 'ICTU_GC_thema_taxonomy', 'init' ), 10 );
}


//========================================================================================================

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.0.1
 */


if ( ! class_exists( 'ICTU_GC_thema_taxonomy' ) ) :

	class ICTU_GC_thema_taxonomy {

		/** ----------------------------------------------------------------------------------------------------
		 * Init
		 */
		public static function init() {

			$newtaxonomy = new self();

		}

		/** ----------------------------------------------------------------------------------------------------
		 * Constructor
		 */
		public function __construct() {

			$this->fn_ictu_thema_setup_actions();

		}

		/** ----------------------------------------------------------------------------------------------------
		 * Hook this plugins functions into WordPress.
		 * Use priority = 20, to ensure that the taxonomy is registered for post types from other plugins,
		 * such as the podcasts plugin (seriously-simple-podcasting)
		 */
		private function fn_ictu_thema_setup_actions() {

			add_action( 'init', array( $this, 'fn_ictu_thema_register_taxonomy' ), 20 );

			// add page templates
			add_filter( 'template_include', array( $this, 'fn_ictu_thema_append_template_locations' ) );
		}


		/** ----------------------------------------------------------------------------------------------------
		 * Do actually register the post types we need
		 *
		 * @return void
		 */
		public function fn_ictu_thema_register_taxonomy() {

			require_once plugin_dir_path( __FILE__ ) . 'includes/thema-taxonomy.php';

		}


		/**
		 * Checks if the template is assigned to the page
		 *
		 * @in: $template (string)
		 *
		 * @return: $template (string)
		 *
		 */
		public function fn_ictu_thema_append_template_locations( $template ) {

			// Get global post
			global $post;
			$file       = '';
			$pluginpath = plugin_dir_path( __FILE__ );


			if ( $post ) {
				// Do we have a post of whatever kind at hand?
				// Get template name; this will only work for pages, obviously
				$page_template = get_post_meta( $post->ID, '_wp_page_template', true );

				if ( ( 'template-overview-themas.php' === $page_template ) || ( 'template-detail-themas.php' === $page_template ) ) {
					// these names are added by this plugin, so we return
					// the actual file path for this template
					$file = $pluginpath . $page_template;
				} else {
					return $template;
				}

			} elseif ( is_tax( TAX_THEMA ) ) {
				// Are we dealing with a term for the TAX_THEMA taxonomy?
				$file = $pluginpath . 'taxonomy-thema.php';

			} else {
				// Not a post, not a term, return the template
				return $template;
			}

			// Just to be safe, check if the file actually exists
			if ( $file && file_exists( $file ) ) {
				return $file;
			} else {
				// o dear, who deleted the file?
				echo $file;
			}

			// If all else fails, return template
			return $template;
		}

	}

endif;


//========================================================================================================

if ( defined( TAX_THEMA ) or taxonomy_exists( TAX_THEMA ) ) {

	/**
	 * Load plugin textdomain.
	 * only load translations if we can safely assume the taxonomy is active
	 */
	add_action( 'init', 'fn_ictu_thema_load_plugin_textdomain' );

	function fn_ictu_thema_load_plugin_textdomain() {

		load_plugin_textdomain( 'gctheme', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	}

}

//========================================================================================================

/**
 * Returns array of allowed page templates
 *
 * @return array with extra templates
 */
function fn_ictu_thema_add_templates() {

	$return_array = array(
		"template-overview-themas.php" => "[GC] Thema overzicht",
		"template-detail-themas.php"   => "[GC] Thema detailpagina"
	);

	return $return_array;

}
