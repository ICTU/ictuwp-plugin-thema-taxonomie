<?php

/**
 * @link                https://github.com/ICTU/ictuwp-plugin-thema-taxonomie
 * @package             ictuwp-plugin-thema-taxonomie
 *
 * @wordpress-plugin
 * Plugin Name:         ICTU / Gebruiker Centraal / Thema taxonomie
 * Plugin URI:          https://github.com/ICTU/ictuwp-plugin-thema-taxonomie
 * Description:         Plugin voor het aanmaken van de 'thema'-taxonomie
 * Version:             1.2.20
 * Version description: Force new version
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
	$slug = 'thema'; // TODO: see bug GC-575
}

defined( 'GC_THEMA_TAX' ) or define( 'GC_THEMA_TAX', $slug );
defined( 'GC_THEMA_TAX_OVERVIEW_TEMPLATE' ) or define( 'GC_THEMA_TAX_OVERVIEW_TEMPLATE', 'template-overview-themas.php' );
defined( 'GC_THEMA_TAX_DETAIL_TEMPLATE' ) or define( 'GC_THEMA_TAX_DETAIL_TEMPLATE', 'template-thema-detail.php' );

//========================================================================================================
// only this plugin should activate the GC_THEMA_TAX taxonomy
if ( ! taxonomy_exists( GC_THEMA_TAX ) ) {
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

			// filter the breadcrumbs
			add_filter( 'wpseo_breadcrumb_links', array( $this, 'fn_ictu_thema_yoast_filter_breadcrumb' ) );

			// check if the term has detail page attached
			add_action( 'template_redirect', array( $this, 'fn_ictu_thema_check_redirect' ) );

			// Hide the `metabox_posts_category` field for 'community' related posts
			// (because we already filter on Community tax term)
			add_filter( 'acf/prepare_field/name=metabox_posts_category', function( $field ) {
				global $post;
				if ( ! empty( $post ) ) {
					// Check if we're currently editing a post
					// with a Thema template.
					// If so: *disable* the category selection (return false)
					// because we use the Thema Tax...
					$page_template = get_post_meta( $post->ID, '_wp_page_template', true );
					if ( $page_template === GC_THEMA_TAX_DETAIL_TEMPLATE ) {
						return false;
					}
				}
				return $field;
			} );

		}

		public function fn_ictu_thema_check_redirect() {

			if ( ! function_exists( 'get_field' ) ) {
				// we can't check if ACF is not active
				return;
			}

			if ( is_tax( GC_THEMA_TAX ) ) {

				// check if the current term has a value for 'thema_taxonomy_page'
				$pageid = get_field( 'thema_taxonomy_page', GC_THEMA_TAX . '_' . get_queried_object()->term_id );
				$page   = get_post( $pageid );
				if ( $page ) {
					// cool, a page is selected for this term
					// But is the page published?
					if ( 'publish' === $page->post_status ) {
						// good, it is published
						// let's redirect to that page
						wp_safe_redirect( get_permalink( $page->ID ) );
						exit;

					} else {
						// bad, we only want published pages
						$aargh = 'No published page attached to this thema';
						if ( current_user_can( 'editor' ) ) {
							$editlink = get_edit_term_link( get_queried_object()->term_id, get_queried_object()->taxonomy );
							$aargh    .= '<a href="' . $editlink . '">Please choose a published page for this term.</a>';
						}
						die( $aargh );
					}
				} else {
					// no page is selected for this term
					// for now, do nothing
				}
			}
		}

		/** ----------------------------------------------------------------------------------------------------
		 * Do actually register the post types we need
		 *
		 * @return void
		 */
		public function fn_ictu_thema_register_taxonomy() {

			require_once plugin_dir_path( __FILE__ ) . 'includes/register-thema-taxonomy.php';

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

				if ( ( GC_THEMA_TAX_OVERVIEW_TEMPLATE === $page_template ) || ( GC_THEMA_TAX_DETAIL_TEMPLATE === $page_template ) ) {
					// these names are added by this plugin, so we return
					// the actual file path for this template
					$file = $pluginpath . $page_template;
				} else {
					// exit with the already set template
					return $template;
				}

			} elseif ( is_tax( GC_THEMA_TAX ) ) {
				// Are we dealing with a term for the GC_THEMA_TAX taxonomy?
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


		/**
		 * Filter the Yoast SEO breadcrumb
		 *
		 * @in: $links (array)
		 *
		 * @return: $links (array)
		 *
		 */
		public function fn_ictu_thema_yoast_filter_breadcrumb( $links ) {

			if ( is_tax( GC_THEMA_TAX ) ) {
				// this filter is only for terms in GC_THEMA_TAX taxonomy

				$term = get_queried_object();
				// Append taxonomy if 1st-level child term only
				// old: Home > Term
				// new: Home > Taxonomy > Term

				if ( ! $term->parent ) {

					$thema_overview_page_id = $this->fn_ictu_thema_get_thema_overview_page();

					if ( $thema_overview_page_id ) {
						// Use this page as GC_THEMA_TAX term parent in the breadcrumb
						// If not available,
						// - [1] Do not display root
						// - [2] OR fall back to Taxonomy Rewrite

						$taxonomy_link = array(
							'url'  => get_permalink( $thema_overview_page_id ),
							'text' => get_the_title( $thema_overview_page_id )
						);
						array_splice( $links, - 1, 0, array( $taxonomy_link ) );

					} else {
						// [1] .. do nothing...

						// [2] OR .. use Taxonomy Rewrite as root

						// $taxonomy      = get_taxonomy( GC_THEMA_TAX );
						// $taxonomy_link = [
						// 	'url' => get_home_url() . '/' . $taxonomy->rewrite['slug'],
						// 	'text' => $taxonomy->labels->archives,
						// 	'term_id' => get_queried_object_id(),
						// ];
						// array_splice( $links, -1, 0, [$taxonomy_link] );
					}
				}
			}

			return $links;

		}

		/**
		 * Retrieve a page that is the GC_THEMA_TAX overview page. This
		 * page shows all available GC_THEMA_TAX terms
		 *
		 * @in: $args (array)
		 *
		 * @return: $overview_page_id (integer)
		 *
		 */

		private function fn_ictu_thema_get_thema_overview_page( $args = array() ) {

			$return = 0;

			// TODO: discuss if we need to make this page a site setting
			// there is no technical way to limit the number of pages with
			// template GC_THEMA_TAX_OVERVIEW_TEMPLATE, so the page we find may not be
			// the exact desired page for in the breadcrumb.
			//
			// Try and find 1 Page
			// with the GC_THEMA_TAX_OVERVIEW_TEMPLATE template...
			$page_template_query_args = array(
				'number'      => 1,
				'sort_column' => 'post_date',
				'sort_order'  => 'DESC',
				'meta_key'    => '_wp_page_template',
				'meta_value'  => GC_THEMA_TAX_OVERVIEW_TEMPLATE
			);
			$overview_page            = get_pages( $page_template_query_args );

			if ( $overview_page && isset( $overview_page[0]->ID ) ) {
				$return = $overview_page[0]->ID;
			}

			return $return;

		}


	}

endif;


//========================================================================================================

/**
 * Load plugin textdomain.
 * only load translations if we can safely assume the taxonomy is active
 */
add_action( 'init', 'fn_ictu_thema_load_plugin_textdomain' );

function fn_ictu_thema_load_plugin_textdomain() {

	load_plugin_textdomain( 'gctheme', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

}


//========================================================================================================

/**
 * Returns array of allowed page templates
 *
 * @return array with extra templates
 */
function fn_ictu_thema_add_templates() {

	$return_array = array(
		GC_THEMA_TAX_OVERVIEW_TEMPLATE => _x( '[Thema] alle thema\'s', 'label page template', 'gctheme' ),
		GC_THEMA_TAX_DETAIL_TEMPLATE   => _x( '[Thema] thema-detail', 'label page template', 'gctheme' )
	);

	return $return_array;

}

//========================================================================================================



