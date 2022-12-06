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
		$term_url = get_term_link( $thema );

		$item = array(
			'type'  => 'thema',
			'title' => $thema->name,
			'descr' => $thema->description,
			// IGNORE custom page url for now..
			// 'url'   => $thema->thema_taxonomy_page,
			'url'   => $term_url
		);

		if ( $thema->thema_taxonomy_image ) {
			$item['img'] = '<img src="' . $thema->thema_taxonomy_image['sizes']['image-16x9'] . '" alt=""/>';
		}

		$context['overview']['items'][] = $item;

	}
}

Timber::render( [ 'overview.twig', 'page.twig' ], $context );
