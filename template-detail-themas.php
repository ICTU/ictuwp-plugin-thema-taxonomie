<?php
/**
 * Thema Term detail page
 *
 * This template is attached to a random page
 * that has been assigned to a Thema taxonomy term as
 * ACF `thema_taxonomy_page` Link field
 * 
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$context         = Timber::context();
$timber_post     = new Timber\Post();
$context['post'] = $timber_post;

/**
 * Fill Timber $context with available page/post Blocks/Metaboxes
 * @see /includes/gc-fill-context-with-acf-fields.php
 */
if ( function_exists( 'gc_fill_context_with_acf_fields' ) ) {
	$context = gc_fill_context_with_acf_fields( $context );
}

// DEBUGGING

$context['post']->title .= '<br><b>WORK IN PROGRESS</b><br><small>template-detail-themas.php should be improved...</small>';

/**
 * Set the default Twig templates for this page template
 */
$templates = ['page-' . $timber_post->ID . '.twig', 'page.twig'];

Timber::render( $templates, $context );
