<?php
/**
 * Template Name: Template Thema's
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$context               = Timber::context();
$timber_post           = new Timber\Post();
$context['post']       = $timber_post;
$context['modifier']   = 'thema-overview';
$context['is_unboxed'] = true;

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
	$themas                          = fn_ictu_thema_get_thema_terms();

	foreach ( $themas as $thema ) {

		$page          = get_post( $thema->thema_taxonomy_page );
		$page_template = get_post_meta( $page->ID, '_wp_page_template', true );
		$term_url      = null;
		$name          = $thema->name;
		$description   = $thema->description;

		if ( ( GC_THEMA_TAX_DETAIL_TEMPLATE === $page_template ) && ( 'publish' === $page->post_status ) ) {
			// this page has the right template and is published
			$term_url = get_permalink( $page->ID );
		} else {
			// the term SHOULD have a page attached. Force editors to correct this, and
			// for other users, just show info, but no link
			if ( current_user_can( 'editor' ) ) {
				$term_url    = get_edit_term_link( $thema, $thema->taxonomy );
				$description = 'Editor, please choose the proper page for this term.<br>';
				$description .= '<strong><a href="' . $term_url . '" style="background: red; color: white;">correct this</a></strong>.';

			}
		}

		$item = array(
			'type'  => 'thema',
			'title' => $name,
			'descr' => $description
		);

		if ( $term_url ) {
			// URL found
			$item['url'] = $term_url;
		}
		if ( $thema->thema_taxonomy_image ) {
			// this term has a color scheme (called 'thema_taxonomy_image')
			$item['thema'] = $thema->thema_taxonomy_image;
		}

		$context['overview']['items'][] = $item;

	}
}

// Add overview-themas.twig to the list of templates to look for
// If its not found, use overview.twig or page.twig
Timber::render( [ 'overview-themas.twig', 'overview.twig', 'page.twig' ], $context );
