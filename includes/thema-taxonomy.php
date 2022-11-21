<?php

// This file is a direct copy from the NPR repo. See:
// https://github.com/ICTU/ictuwp-gebruikercentraal
// specifically this file:
// [root]/wp-content/themes/ictuwp-theme-gc2020/includes/taxonomies/thema-taxonomy.php

/**
 * Custom Taxonomy: Thema
 * - Non hierarchical (like 'tags')
 *
 * @package GebruikerCentraalTheme
 *
 * @see https://developer.wordpress.org/reference/functions/register_taxonomy/
 * @see https://developer.wordpress.org/reference/functions/get_taxonomy_labels/
 *
 * [1] Init Thema taxonomy labels
 * [2] Init Thema taxonomy arguments
 * [3] Register Thema taxonomy
 * [4] gc_get_thema_terms() - Retreive Thema terms with custom field data
 * [5] gc_get_post_thema_terms() - Retreive Thema terms with custom field data for Post
 * [6] gc_sitemap_exclude_theme_taxonomy() exclude Thema from XML sitemap
 * [7] Append Thema root to Yoast breadcrumbs
 * [8] (NOT USED) Redirect Thema taxonomy Term archive to landingspage
 * ----------------------------------------------------- */

defined( 'TAX_THEMA' ) or define( 'TAX_THEMA', 'thema' );
defined( 'TAX_THEMA_OVERVIEW_TEMPLATE' ) or define( 'TAX_THEMA_OVERVIEW_TEMPLATE', 'template-overview-themas.php' );
defined( 'TAX_THEMA_DETAIL_TEMPLATE' ) or define( 'TAX_THEMA_DETAIL_TEMPLATE', 'template-detail-themas.php' );

if ( ! taxonomy_exists( TAX_THEMA ) ) {

	// [1] Thema Taxonomy Labels
	$thema_tax_labels = [
		'name'                       => _x( 'Thema', 'Custom taxonomy labels definition', 'gctheme' ),
		'singular_name'              => _x( 'Thema', 'Custom taxonomy labels definition', 'gctheme' ),
		'search_items'               => _x( 'Zoek thema\'s', 'Custom taxonomy labels definition', 'gctheme' ),
		'popular_items'              => _x( 'Populaire thema\'s', 'Custom taxonomy labels definition', 'gctheme' ),
		'all_items'                  => _x( 'Alle thema\'s', 'Custom taxonomy labels definition', 'gctheme' ),
		'edit_item'                  => _x( 'Bewerk thema', 'Custom taxonomy labels definition', 'gctheme' ),
		'view_item'                  => _x( 'Bekijk thema', 'Custom taxonomy labels definition', 'gctheme' ),
		'update_item'                => _x( 'Thema bijwerken', 'Custom taxonomy labels definition', 'gctheme' ),
		'add_new_item'               => _x( 'Voeg nieuw thema toe', 'Custom taxonomy labels definition', 'gctheme' ),
		'new_item_name'              => _x( 'Nieuwe thema', 'Custom taxonomy labels definition', 'gctheme' ),
		'separate_items_with_commas' => _x( 'Kommagescheiden thema\'s', 'Custom taxonomy labels definition', 'gctheme' ),
		'add_or_remove_items'        => _x( 'Thema\'s toevoegen of verwijderen', 'Custom taxonomy labels definition', 'gctheme' ),
		'choose_from_most_used'      => _x( 'Kies uit de meest-gekozen thema\'s', 'Custom taxonomy labels definition', 'gctheme' ),
		'not_found'                  => _x( 'Geen thema\'s gevonden', 'Custom taxonomy labels definition', 'gctheme' ),
		'no_terms'                   => _x( 'Geen thema\'s gevonden', 'Custom taxonomy labels definition', 'gctheme' ),
		'items_list_navigation'      => _x( 'Navigatie door themalijst', 'Custom taxonomy labels definition', 'gctheme' ),
		'items_list'                 => _x( 'Themalijst', 'Custom taxonomy labels definition', 'gctheme' ),
		'item_link'                  => _x( 'Thema Link', 'Custom taxonomy labels definition', 'gctheme' ),
		'item_link_description'      => _x( 'Een link naar een Thema', 'Custom taxonomy labels definition', 'gctheme' ),
		'menu_name'                  => _x( 'Thema\'s', 'Custom taxonomy labels definition', 'gctheme' ),
		'back_to_items'              => _x( 'Terug naar Thema\'s', 'Custom taxonomy labels definition', 'gctheme' ),
		'not_found_in_trash'         => _x( 'Geen thema\'s gevonden in de prullenbak', 'Custom taxonomy labels definition', 'gctheme' ),
		'featured_image'             => _x( 'Uitgelichte afbeelding', 'Custom taxonomy labels definition', 'gctheme' ),
		'archives'                   => _x( 'Thema overzicht', 'Custom taxonomy labels definition', 'gctheme' ),
	];

	// [2] Thema Taxonomy Arguments
	$thema_tax_args = [
		"labels"             => $thema_tax_labels,
		"label"              => _x( 'Thema\'s', 'Custom taxonomy arguments definition', 'gctheme' ),
		"description"        => _x( 'Thema\'s op het gebied van een gebruikersvriendelijke overheid', 'Custom taxonomy arguments definition', 'gctheme' ),
		"hierarchical"       => true,
		"public"             => true,
		"show_ui"            => true,
		"show_in_menu"       => true,
		"show_in_nav_menus"  => false,
		"query_var"          => false,
		// Needed for tax to appear in Gutenberg editor.
		'show_in_rest'       => true,
		"show_admin_column"  => true,
		// Needed for tax to appear in Gutenberg editor.
		"rewrite"            => [
			'slug'       => TAX_THEMA,
			'with_front' => true,
		],
		"show_in_quick_edit" => true,
	];

	// register the taxonomy with these post types
	$post_types_with_thema = [
		'post',
		'page',
		'podcast',
		'session',
		'keynote',
		'speaker',
		'event',
		'video_page'
	];

	// check if the post types exist
	$post_types_with_thema = array_filter( $post_types_with_thema, 'post_type_exists' );

	// [3] Register our Custom Taxonomy
	register_taxonomy( TAX_THEMA, $post_types_with_thema, $thema_tax_args );

}

// [4] Get complete Thema term objects

/**
 * gc_get_thema_terms()
 *
 * 'Thema' is a custom taxonomy (tag)
 * It has 2 extra ACF fields for an
 * image and a landingspage
 *
 * This function fills an array of all
 * terms, with their extra fields...
 *
 * If one $thema_name is passed it returns only that
 * If $term_args is passed it uses that for the query
 *
 * @see https://developer.wordpress.org/reference/functions/get_terms/
 * @see https://www.advancedcustomfields.com/resources/adding-fields-taxonomy-term/
 * @see https://developer.wordpress.org/reference/classes/wp_term_query/__construct/
 *
 * @param String $thema_name Specific term name/slug to query
 * @param Array $thema_args Specific term query Arguments to use
 */

if ( ! function_exists( 'gc_get_thema_terms' ) ) {

	function gc_get_thema_terms( $thema_name = null, $term_args = null ) {
		$thema_taxonomy = TAX_THEMA;
		$thema_terms    = [];
		$thema_query    = is_array( $term_args ) ? $term_args : [
			'taxonomy'   => $thema_taxonomy,
			// We also want Terms with NO linked content, in this case
			'hide_empty' => false
		];

		// Query specific term name
		if ( ! empty( $thema_name ) ) {
			// If we find a Space, or an Uppercase letter, we assume `name`
			// otherwise we use `slug`
			$RE_disqualify_slug              = "/[\sA-Z]/";
			$query_prop_type                 = preg_match( $RE_disqualify_slug, $thema_name ) ? 'name' : 'slug';
			$thema_query[ $query_prop_type ] = $thema_name;
		}

		$found_thema_terms = get_terms( $thema_query );

		if ( is_array( $found_thema_terms ) && ! empty( $found_thema_terms ) ) {
			// Add our custom Fields to each found WP_Term instance
			// And add to $thema_terms[]
			foreach ( $found_thema_terms as $thema_term ) {
				foreach ( get_fields( $thema_term ) as $key => $val ) {
					$thema_term->$key = $val;
				}
				$thema_terms[] = $thema_term;
			}
		}

		return $thema_terms;
	}
}

// [5] Get complete Thema term objects for Post

/**
 * gc_get_post_thema_terms()
 *
 * This function fills an array of all
 * terms, with their extra fields _for a specific Post_...
 *
 * - Only top-lever Terms
 * - 1 by default
 *
 * @param String|Number $post_id Post to retrieve linked terms for
 *
 * @return Array        Array of WPTerm Objects with extra ACF fields
 */
if ( ! function_exists( 'gc_get_post_thema_terms' ) ) {
	function gc_get_post_thema_terms( $post_id = null, $term_number = 1 ) {
		$return_terms = [];
		if ( ! $post_id ) {
			return $return_terms;
		}

		$post_thema_terms = wp_get_post_terms( $post_id, TAX_THEMA, [
			'taxonomy'   => TAX_THEMA,
			'number'     => $term_number, // Return max $term_number Terms
			'hide_empty' => true,
			'parent'     => 0,
			'fields'     => 'names' // Only return names (to use in `gc_get_thema_terms()`)
		] );

		foreach ( $post_thema_terms as $_term ) {
			$full_post_thema_term = gc_get_thema_terms( $_term );
			if ( ! empty( $full_post_thema_term ) ) {
				$return_terms[] = $full_post_thema_term[0];
			}
		}

		return $return_terms;
	}
}

// [6] Exclude Thema from XML sitemap
/**
 * Exclude a taxonomy from XML sitemaps.
 * @see https://developer.yoast.com/features/xml-sitemaps/api/#exclude-a-taxonomy
 *
 * @param boolean $excluded Whether the taxonomy is excluded by default.
 * @param string $taxonomy The taxonomy to exclude.
 *
 * @return bool Whether or not a given taxonomy should be excluded.
 */
if ( ! function_exists( 'gc_sitemap_exclude_theme_taxonomy' ) ) {
	function gc_sitemap_exclude_theme_taxonomy( $excluded, $taxonomy ) {
		return $taxonomy === TAX_THEMA;
	}
}

add_filter( 'wpseo_sitemap_exclude_taxonomy', 'gc_sitemap_exclude_theme_taxonomy', 10, 2 );

// [7] Append Thema root to Yoast breadcrumbs
if ( ! function_exists( 'gc_append_yoast_breadcrumb' ) ) {
	function gc_append_yoast_breadcrumb( $links ) {

		if ( is_tax( TAX_THEMA ) ) {
			$term = get_queried_object();
			// Append taxonomy if 1st-level child term only
			// old: Home > Term
			// new: Home > Taxonomy > Term
			if ( ! $term->parent ) {

				// Try and find 1 Page
				// with the TAX_THEMA_OVERVIEW_TEMPLATE template...
				// Use this as Thema Root
				// If not available,
				// - [1] Do not display root
				// - [2] OR fall back to Taxonomy Rewrite
				$page_template_query_args = array(
					'number'      => 1,
					'sort_column' => 'post_date',
					'sort_order'  => 'DESC',
					'meta_key'    => '_wp_page_template',
					'meta_value'  => TAX_THEMA_OVERVIEW_TEMPLATE
				);
				$thema_overview_page      = get_pages( $page_template_query_args );

				if ( ! empty( $thema_overview_page ) ) {

					// Use 1st found page with proper template
					// as Breadcrumb root
					$taxonomy_page = $thema_overview_page[0];
					$taxonomy_link = [
						'url'  => get_permalink( $taxonomy_page ),
						'text' => get_the_title( $taxonomy_page )
					];
					array_splice( $links, - 1, 0, [ $taxonomy_link ] );

				} else {
					// [1] .. do nothing...

					// [2] OR .. use Taxonomy Rewrite as root

					// $taxonomy      = get_taxonomy( TAX_THEMA );
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
}
add_filter( 'wpseo_breadcrumb_links', 'gc_append_yoast_breadcrumb' );

// [8] (NOT USED) Redirect Thema taxonomy Term archive to landingspage.
/**
 * Redirect Thema taxonomy Term archive to chosen landingspage.
 *
 * If we, instead of redirecting, need e.g. to change the Taxonomy
 * template we could use the `taxonomy_template` (filter) hook instead.
 * As for now: we redirect to the page, if given...
 *
 * @see https://developer.wordpress.org/reference/hooks/template_redirect/
 * @see https://wordpress.stackexchange.com/a/209468
 * @see https://developer.wordpress.org/reference/hooks/type_template/
 */
if ( ! function_exists( 'gc_redirect_thema_archives' ) ) {
	function gc_redirect_thema_archives() {
		$queried_object = get_queried_object();
		// [8] Redirect Term Archive:
		if ( $queried_object instanceof WP_Term && $queried_object->taxonomy === TAX_THEMA ) {
			// Add our custom ACF fields
			// (that we've added to our custom Tax)
			// to this WP_Term..
			foreach ( get_fields( $queried_object ) as $key => $val ) {
				$queried_object->$key = $val;
			}
			// When a custom `page` has been added to this term
			// we redirect to that, instead of the default
			// taxonomy-thema.php template...
			if ( ! empty( $queried_object->thema_taxonomy_page ) ) {
				wp_safe_redirect( $queried_object->thema_taxonomy_page );
				exit;
			}
		}
	}
}
// @NOTE: @TODO: @FIXME:
// DISABLED FOR NOW...
// add_action( 'template_redirect', 'gc_redirect_thema_archives' );
