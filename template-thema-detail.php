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

$context               = Timber::context();
$timber_post           = new Timber\Post();
$context['post']       = $timber_post;
$context['is_unboxed'] = true;
//$context['is_content_full'] = true;

/**
 * Fill Timber $context with available page/post Blocks/Metaboxes
 * @see /includes/gc-fill-context-with-acf-fields.php
 */
if ( function_exists( 'gc_fill_context_with_acf_fields' ) ) {
	$context = gc_fill_context_with_acf_fields( $context );
}


/**
 * Set the default Twig templates for this page template
 */
$templates            = [ 'thema-detail.twig' ];
$imagesize_for_thumbs = IMAGESIZE_16x9;
$current_thema_tax    = 249; // 'Begrijpelijke tekst en beeldtaal'

/**
 *  Events box
 * ----------------------------- */
if ( 'ja' === get_field( 'metabox_events_show_or_not' ) ) {

	if ( class_exists( 'EM_Events' ) ) {
		// the events manager is active, which helps for selecting events
		$method        = get_field( 'metabox_events_selection_method' );
		$maxnr         = 3; // todo TBD: should this be a user editable field?
		$metabox_items = array();

		if ( 'manualxx' === $method ) {
			// manually selected events, returns an array of posts
			$metabox_items = get_field( 'metabox_events_selection_manual' );

		} else {
			// select latest events for $current_thema_tax
			// _event_start_date is a meta field for the events post type
			// this query selects future events for the $current_thema_tax
			$currentdate = date( "Y-m-d" );
			$args        = array(
				'posts_per_page' => $maxnr,
				'post_type'      => EM_POST_TYPE_EVENT,
				'meta_key'       => '_event_start_date',
				'orderby'        => 'meta_value_num',
				'order'          => 'ASC', // order by start date ascending
				'fields'         => 'ids', // only return IDs
				'tax_query'      => array(
					array(
						'taxonomy' => TAX_THEMA,
						'field'    => 'term_id',
						'terms'    => $current_thema_tax,
					)
				),
				'meta_query'     => array(
					array(
						'key'     => '_event_start_date',
						'value'   => $currentdate,
						'compare' => '>=',
						'type'    => 'DATE',
					),
				)
			);
			$query_items = new WP_Query( $args );

			if ( $query_items->have_posts() ) {
				// we only use post ids for the $metabox_items array
				$metabox_items = $query_items->posts;
			}

			// ensure to reset the main query to original main query
			wp_reset_query();
		}

		if ( $metabox_items ) {
			// we have events
			$context['metabox_events']          = [];
			$context['metabox_events']['items'] = [];
			$context['metabox_events']['title'] = ( get_field( 'metabox_events_titel' ) ? get_field( 'metabox_events_titel' ) : '' );
			if ( get_field( 'metabox_events_url_overview' ) ) {
				$url                     = get_field( 'metabox_events_url_overview' );
				$context['cta']['title'] = $url['title'];
				$context['cta']['url']   = $url['url'];
			}

			foreach ( $metabox_items as $postitem ) {

				$item  = prepare_card_content( get_post( $postitem ) );
				$image = get_the_post_thumbnail_url( $postitem, $imagesize_for_thumbs );
				if ( $image ) {
					// decorative image, no value for alt attr.
					$item['img'] = '<img src="' . $image . '" alt="" />';
				}
				$context['metabox_events']['items'][] = $item;
			}
			$context['metabox_events']['columncounter'] = count( $context['metabox_events']['items'] );
		}
	}
}

/**
 * 2 - GC_VIDEO_PAGE_CPT for this thema-tax
 * ----------------------------- */
if ( 'ja' === get_field( 'metabox_webinars_show_or_not' ) ) {

	$method        = get_field( 'metabox_webinars_selection_method' );
	$maxnr         = 3; // todo TBD: should this be a user editable field?
	$metabox_items = array();

	if ( 'manual' === $method ) {
		// manually selected events, returns an array of posts
		$metabox_items = get_field( 'metabox_webinars_selection_manual' );

	} else {
		$args        = array(
			'posts_per_page' => $maxnr,
			'post_type'      => GC_VIDEO_PAGE_CPT,
			'fields'         => 'ids', // only return IDs
			'tax_query'      => array(
				array(
					'taxonomy' => TAX_THEMA,
					'field'    => 'term_id',
					'terms'    => $current_thema_tax,
				)
			)
		);
		$query_items = new WP_Query( $args );
		if ( $query_items->have_posts() ) {
			// we only use post ids for the $metabox_items array
			$metabox_items = $query_items->posts;
		}

		// ensure to reset the main query to original main query
		wp_reset_query();
	}

	if ( $metabox_items ) {

		$context['metabox_webinars']          = [];
		$context['metabox_webinars']['items'] = [];
		$context['metabox_webinars']['title'] = ( get_field( 'metabox_webinars_titel' ) ? get_field( 'metabox_webinars_titel' ) : '' );

		foreach ( $metabox_items as $postitem ) {

			$item  = prepare_card_content( get_post( $postitem ) );
			$image = get_the_post_thumbnail_url( $postitem, $imagesize_for_thumbs );
			if ( $image ) {
				// decorative image, no value for alt attr.
				$item['img'] = '<img src="' . $image . '" alt="" />';
			}
			$context['metabox_webinars']['items'][] = $item;
		}
		$context['metabox_webinars']['columncounter'] = count( $context['metabox_webinars']['items'] );

	}

}


/**
 *  Podcasts box
 * ----------------------------- */
if ( 'ja' === get_field( 'metabox_podcasts_show_or_not' ) ) {

	$method        = get_field( 'metabox_podcasts_selection_method' );
	$maxnr         = 3; // todo TBD: should this be a user editable field?
	$metabox_items = array();

	if ( 'manual' === $method ) {
		// manually selected events, returns an array of posts
		$metabox_items = get_field( 'metabox_podcasts_selection_manual' );

	} else {
		$args        = array(
			'posts_per_page' => $maxnr,
			'post_type'      => 'podcast',
			'fields'         => 'ids', // only return IDs
			'tax_query'      => array(
				array(
					'taxonomy' => TAX_THEMA,
					'field'    => 'term_id',
					'terms'    => $current_thema_tax,
				)
			)
		);
		$query_items = new WP_Query( $args );
		if ( $query_items->have_posts() ) {
			// we only use post ids for the $metabox_items array
			$metabox_items = $query_items->posts;
		}

		// ensure to reset the main query to original main query
		wp_reset_query();
	}

	if ( $metabox_items ) {

		$context['metabox_podcasts']          = [];
		$context['metabox_podcasts']['items'] = [];
		$context['metabox_podcasts']['title'] = ( get_field( 'metabox_podcasts_titel' ) ? get_field( 'metabox_podcasts_titel' ) : '' );

		foreach ( $metabox_items as $postitem ) {

			$item  = prepare_card_content( get_post( $postitem ) );
			$image = get_the_post_thumbnail_url( $postitem, $imagesize_for_thumbs );
			if ( $image ) {
				// decorative image, no value for alt attr.
				$item['img'] = '<img src="' . $image . '" alt="" />';
			}
			$context['metabox_podcasts']['items'][] = $item;
		}
		$context['metabox_podcasts']['columncounter'] = count( $context['metabox_podcasts']['items'] );

	}

}


Timber::render( $templates, $context );
