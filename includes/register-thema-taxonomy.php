<?php

// This file is a direct copy from the NPR repo. See:
// https://github.com/ICTU/ictuwp-gebruikercentraal
// specifically this file:
// [root]/wp-content/themes/ictuwp-theme-gc2020/includes/taxonomies/register-thema-taxonomy.php
//
// TODO: check the gc2020 theme to move all GC_THEMA_TAX taxonomy functions and checks to this plugin

/**
 * Custom Taxonomy: Thema
 * -  hierarchical (like 'category')
 *
 * @package GebruikerCentraalTheme
 *
 * @see https://developer.wordpress.org/reference/functions/register_taxonomy/
 * @see https://developer.wordpress.org/reference/functions/get_taxonomy_labels/
 *
 * CONTENTS:
 * Set GC_THEMA_TAX taxonomy labels
 * Set GC_THEMA_TAX taxonomy arguments
 * Register GC_THEMA_TAX taxonomy
 * public function fn_ictu_thema_get_post_thema_terms() - Retreive Thema terms with custom field data for Post
 * (NOT USED) Redirect Thema taxonomy Term archive to landingspage
 * ----------------------------------------------------- */


if ( ! taxonomy_exists( GC_THEMA_TAX ) ) {

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
	$thema_slug = GC_THEMA_TAX;
	// TODO: discuss if slug should be set to a page with the overview template
	// like so:
	// $thema_slug = fn_ictu_thema_get_thema_overview_page();

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
			'slug'       => $thema_slug,
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
		'video_page',
		'instrument',
	];

	// check if the post types exist
	$post_types_with_thema = array_filter( $post_types_with_thema, 'post_type_exists' );

	// [3] Register our Custom Taxonomy
	register_taxonomy( GC_THEMA_TAX, $post_types_with_thema, $thema_tax_args );

} // if ( ! taxonomy_exists( GC_THEMA_TAX ) )


/**
 * fn_ictu_thema_get_thema_terms()
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


function fn_ictu_thema_get_thema_terms( $thema_name = null, $term_args = null ) {

	// TODO: I foresee that editors will want to have a custom order to the taxonomy terms
	// but for now the terms are ordered alphabetically
	$thema_taxonomy = GC_THEMA_TAX;
	$thema_terms    = [];
	$thema_query    = is_array( $term_args ) ? $term_args : [
		'taxonomy'   => $thema_taxonomy,
		// We also want Terms with NO linked content, in this case
		'hide_empty' => false,
		// sort by our custom numerical `thema_sort_order` field ASC (so lower == first)
		// With equal values, we would *like* to sort alphabetically (on `name`), but that's not possible :(
		// So: with equal sort order, we sort by `term_id` (which is the order in which they were created)
		'order'      => 'ASC',
		'orderby'    => 'meta_value_num',
		'meta_key'   => 'thema_sort_order',
		'meta_type'  => 'NUMERIC', // sort numerically, even if `thema_sort_order` is stored as String
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
		$thema_terms['title'] =  _n( 'Hoort bij het thema', 'Hoort bij de thema\'s', count( $found_thema_terms ), 'gctheme' ) ;
		$thema_terms['items']   = array();

		foreach ( $found_thema_terms as $thema_term ) {
			foreach ( get_fields( $thema_term ) as $key => $val ) {
				// If we have a linked Page, add it's URL to the Term as extra `url` property
				if( $key == 'thema_taxonomy_page' && ! empty( $val ) ) {
					$thema_term->url = get_permalink( $val );
				}
				// Add our custom ACF fields to this WP_Term..
				$thema_term->$key = $val;
			}
			// DEBUG: prefix name with term_id and thema_sort_order
			// $thema_term->name = $thema_term->term_id . '-' . $thema_term->thema_sort_order . '-' . $thema_term->name;
			$thema_terms['items'][] = $thema_term;
		}
	}

	return $thema_terms;
}

/**
 * fn_ictu_thema_get_post_thema_terms()
 *
 * This function fills an array of all
 * terms, with their extra fields _for a specific Post_...
 *
 * - Only top-lever Terms
 * - 1 by default
 *
 * used in [themes]/ictuwp-theme-gc2020/includes/gc-fill-context-with-acf-fields.php
 *
 * @param String|Number $post_id Post to retrieve linked terms for
 *
 * @return Array        Array of WPTerm Objects with extra ACF fields
 */
function fn_ictu_thema_get_post_thema_terms( $post_id = null, $term_number = 1 ) {
	$return_terms = [];
	if ( ! $post_id ) {
		return $return_terms;
	}

	$post_thema_terms = wp_get_post_terms( $post_id, GC_THEMA_TAX, [
		'taxonomy'   => GC_THEMA_TAX,
		'number'     => $term_number, // Return max $term_number Terms
		'hide_empty' => true,
		'parent'     => 0,
		'fields'     => 'names' // Only return names (to use in `fn_ictu_thema_get_thema_terms()`)
	] );

	foreach ( $post_thema_terms as $_term ) {
		$full_post_thema_term = fn_ictu_thema_get_thema_terms( $_term );
		if ( ! empty( $full_post_thema_term ) ) {
			$return_terms[] = $full_post_thema_term[0];
		}
	}

	return $return_terms;
}


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
function gc_redirect_thema_archives() {
	$queried_object = get_queried_object();
	// [8] Redirect Term Archive:
	if ( $queried_object instanceof WP_Term && $queried_object->taxonomy === GC_THEMA_TAX ) {
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

// @NOTE: @TODO: @FIXME:
// DISABLED FOR NOW...
// add_action( 'template_redirect', 'gc_redirect_thema_archives' );
