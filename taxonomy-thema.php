<?php
/**
 * WIP:
 * The template for displaying Thema (taxonomy) Landings pages.
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since   Timber 0.2
 */


$context       = Timber::context();
$archive       = get_queried_object();
$taxonomy_name = $archive->taxonomy;
$templates     = [
	'overview-' . $taxonomy_name . '.twig',
	'overview.twig',
	'page.twig',
];


// Add our custom ACF fields
// (that we've added to our custom Tax)
// to this WP_Term..
foreach ( get_fields( $archive ) as $key => $val ) {
	$archive->$key = $val;
}

$context['intro'] = $archive->description;

$context['post'] = [
	'title' => 'TODO THEMA LANDINGSPAGE (taxonomy-thema.php):<br> ' . get_the_archive_title(),
	'descr' => $archive->description,
	'img'   => $archive->thema_taxonomy_image
];

// Set data for overview: linked posts with this Term
foreach ( $context['posts'] as $post ) {
	$context['overview']['items'][] = prepare_card_content( $post );
}


Timber::render( $templates, $context );
