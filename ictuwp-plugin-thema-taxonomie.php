<?php

/**
 * @link                https://github.com/ICTU/ictuwp-plugin-thema-taxonomie
 * @package             ictuwp-plugin-thema-taxonomie
 *
 * @wordpress-plugin
 * Plugin Name:         ICTU / Gebruiker Centraal / Thema taxonomie
 * Plugin URI:          https://github.com/ICTU/ictuwp-plugin-thema-taxonomie
 * Description:         Plugin voor het tijdelijk aanmaken van de 'thema'-taxonomie
 * Version:             0.0.1
 * Version description: Initial version.
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

add_action( 'plugins_loaded', array( 'ICTU_GC_thema_taxonomy', 'init' ), 10 );

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
		 * Hook this plugins functions into WordPress
		 */
		private function fn_ictu_thema_setup_actions() {

			add_action( 'init', array( $this, 'fn_ictu_thema_register_taxonomy' ), 20 );

		}


		/** ----------------------------------------------------------------------------------------------------
		 * Do actually register the post types we need
		 *
		 * @return void
		 */
		public function fn_ictu_thema_register_taxonomy() {

			require_once plugin_dir_path(  __FILE__ )  . 'includes/thema-taxonomy.php';

		}


	}

endif;


//========================================================================================================

/**
 * Load plugin textdomain.
 */
add_action( 'init', 'fn_ictu_thema_load_plugin_textdomain' );

function fn_ictu_thema_load_plugin_textdomain() {

	load_plugin_textdomain( 'gctheme', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

}

//========================================================================================================
