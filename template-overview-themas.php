<?php
/**
 * Template Name: Template Thema's
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$context             = Timber::context();
$timber_post         = new Timber\Post();
$context['post']     = $timber_post;
$context['modifier'] = 'thema-overview';

/**
 * Fill Timber $context with available page/post Blocks/Metaboxes
 * @see /includes/gc-fill-context-with-acf-fields.php
 */
if ( function_exists( 'gc_fill_context_with_acf_fields' ) ) {
	$context = gc_fill_context_with_acf_fields( $context );
}

/**
 * Add Thema's (terms in Thema taxonomy)
 */
if ( function_exists( 'fn_ictu_thema_get_thema_terms' ) ) {
	$context['overview']             = [];
	$context['overview']['items']    = [];
	$context['overview']['template'] = 'card--thema';

	foreach ( fn_ictu_thema_get_thema_terms() as $thema ) {
		$taxonomy = get_taxonomy( $thema->taxonomy );

		if ( $thema->thema_taxonomy_page ) {
			// a special page is available for this term
			$term_url = $thema->thema_taxonomy_page;
		} else {
			// just use the term link
			$term_url = get_term_link( $thema );
		}

		$item = array(
			'type'        => 'thema',
			'title'       => $thema->name,
			'descr'       => $thema->description,
			'url'         => $term_url,
		);

		if ( $thema->thema_taxonomy_image ) {
			// this term has a color scheme (called 'thema_taxonomy_image')
			$item['thema'] = $thema->thema_taxonomy_image;
		}

		$context['overview']['items'][] = $item;

	}
}

Timber::render( [ 'overview.twig', 'page.twig' ], $context );
