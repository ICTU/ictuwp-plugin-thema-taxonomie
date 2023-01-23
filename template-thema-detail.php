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

/**
 * Fill Timber $context with available page/post Blocks/Metaboxes
 * @see /includes/gc-fill-context-with-acf-fields.php
 */
if ( function_exists( 'gc_fill_context_with_acf_fields' ) ) {
	$context = gc_fill_context_with_acf_fields( $context );
}


$templates            = [ 'thema-detail.twig' ];
$imagesize_for_thumbs = IMAGESIZE_16x9;
$current_thema_tax    = get_current_thema_tax();
$acf_id               = TAX_THEMA . '_' . $current_thema_tax;
$themaclass           = get_field( 'thema_taxonomy_image', $acf_id );

$context['pageclass_thema'] = $themaclass;

/**
 * returns the ID for the thema term that is
 * attached to this page in ACF field 'thema_detail_select_thema_term'
 *
 * @return int
 */
function get_current_thema_tax() {
	global $post;

	$term_id = 0; // 'Begrijpelijke tekst en beeldtaal'
	if ( get_field( 'thema_detail_select_thema_term' ) ) {
		$term_id = get_field( 'thema_detail_select_thema_term' );
	} else {
		$aargh = ' No thema attached to this page. ';
		if ( current_user_can( 'editor' ) ) {
			$editlink = get_edit_post_link( $post );
			$aargh    .= '<a href="' . $editlink . '">Please choose the appropriate thema to this page.</a>';
		}
		die( $aargh );
	}

	return $term_id;
}

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
				'post_status'    => 'publish',
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
					// Provide Image as URL instead of HTML?
					// $item['img']     = $image;
					// $item['img_alt'] = '';
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
			'post_status'    => 'publish',
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
				// Provide Image as URL instead of HTML?
				// $item['img']     = $image;
				// $item['img_alt'] = '';
			}
			$context['metabox_webinars']['items'][] = $item;
		}
		$context['metabox_webinars']['columncounter'] = count( $context['metabox_webinars']['items'] );

	}

}


/**
 * 3 - Podcasts box
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
			'post_status'    => 'publish',
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
				// Provide Image as URL instead of HTML?
				// $item['img']     = $image;
				// $item['img_alt'] = '';
			}
			$context['metabox_podcasts']['items'][] = $item;
		}
		$context['metabox_podcasts']['columncounter'] = count( $context['metabox_podcasts']['items'] );
	}
}

/**
 * 4 - Posts box
 * ----------------------------- */
if ( 'ja' === get_field( 'metabox_posts_show_or_not' ) ) {

	$method        = get_field( 'metabox_posts_selection_method' );
	$maxnr         = 3; // todo TBD: should this be a user editable field?
	$metabox_items = array();

	if ( 'manual' === $method ) {
		// manually selected events, returns an array of posts
		$metabox_items = get_field( 'metabox_posts_selection_manual' );

	} else {
		$args        = array(
			'posts_per_page' => $maxnr,
			'post_type'      => 'post',
			'post_status'    => 'publish',
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

		$context['metabox_posts']                = [];
		$context['metabox_posts']['items']       = [];
		$context['metabox_posts']['title']       = ( get_field( 'metabox_posts_titel' ) ? get_field( 'metabox_posts_titel' ) : '' );
		$context['metabox_posts']['description'] = ( get_field( 'metabox_instrumenten_description' ) ? get_field( 'metabox_instrumenten_description' ) : '' );

		foreach ( $metabox_items as $postitem ) {

			$item  = prepare_card_content( get_post( $postitem ) );
			$image = get_the_post_thumbnail_url( $postitem, $imagesize_for_thumbs );
			if ( $image ) {
				// decorative image, no value for alt attr.
				$item['img'] = '<img src="' . $image . '" alt="" />';
				// Provide Image as URL instead of HTML?
				// $item['img']     = $image;
				// $item['img_alt'] = '';
			}
			$context['metabox_posts']['items'][] = $item;
		}
		$context['metabox_posts']['columncounter'] = count( $context['metabox_posts']['items'] );
	}
}


/**
 * 5 - Instrumenten box
 * ----------------------------- */
if ( 'ja' === get_field( 'metabox_instrumenten_show_or_not' ) ) {

	$method        = get_field( 'metabox_instrumenten_selection_method' );
	$maxnr         = 3; // todo TBD: should this be a user editable field?
	$metabox_items = get_field( 'metabox_instrumenten_selection' );

	if ( $metabox_items ) {

		$context['metabox_instrumenten']                = [];
		$context['metabox_instrumenten']['items']       = [];
		$context['metabox_instrumenten']['title']       = ( get_field( 'metabox_instrumenten_titel' ) ? get_field( 'metabox_instrumenten_titel' ) : '' );
		$context['metabox_instrumenten']['description'] = ( get_field( 'metabox_instrumenten_description' ) ? get_field( 'metabox_instrumenten_description' ) : '' );

		foreach ( $metabox_items as $postitem ) {

			$item              = array();
			$item['title']     = $postitem['metabox_instrumenten_selection_title'];
			$item['descr']     = $postitem['metabox_instrumenten_selection_description'];
			$item['url']       = $postitem['metabox_instrumenten_selection_url'];
			$item['post_type'] = 'instrument';
			if ( $postitem['metabox_instrumenten_selection_image'] ) {
				$url         = $postitem['metabox_instrumenten_selection_image']['sizes'][ $imagesize_for_thumbs ];
				$alt         = $postitem['metabox_instrumenten_selection_image']['alt'];
				$item['img'] = '<img src="' . $url . '" alt="' . $alt . '" />';
				// Provide Image as URL instead of HTML
				// $item['img']     = $url;
				// $item['img_alt'] = $alt;
			}

			$context['metabox_instrumenten']['items'][] = $item;
		}
		$context['metabox_instrumenten']['columncounter'] = count( $context['metabox_instrumenten']['items'] );
	}
}


/**
 * 6 - Media + text box
 * ----------------------------- */
if ( 'ja' === get_field( 'metabox_freehand_show_or_not' ) ) {

	$method        = get_field( 'metabox_freehand_selection_method' );
	$metabox_items = get_field( 'metabox_freehand_items' );

	if ( $metabox_items ) {

		$context['metabox_freehandblocks']                = [];
		$context['metabox_freehandblocks']['items']       = [];
		$context['metabox_freehandblocks']['title']       = ( get_field( 'metabox_freehand_titel' ) ? get_field( 'metabox_freehand_titel' ) : '' );
		$context['metabox_freehandblocks']['description'] = ( get_field( 'metabox_freehand_description' ) ? get_field( 'metabox_freehand_description' ) : '' );

		foreach ( $metabox_items as $postitem ) {

			$item          = array();
			$item['title'] = $postitem['metabox_freehand_item_title'];
//			$item['descr']     = $postitem['metabox_freehand_item_description'];
			$item['alignment'] = $postitem['metabox_freehand_item_image_alignment'];
			$item['descr']     = $postitem['metabox_freehand_item_description'];
			$item['post_type'] = 'block';
			if ( $postitem['metabox_freehand_item_image'] ) {
				$url         = $postitem['metabox_freehand_item_image']['sizes'][ $imagesize_for_thumbs ];
				$alt         = $postitem['metabox_freehand_item_image']['alt'];
				$item['img'] = '<img src="' . $url . '" alt="' . $alt . '" />';
				// Provide Image as URL instead of HTML?
				// $item['img']     = $url;
				// $item['img_alt'] = $alt;
			}

			$context['metabox_freehandblocks']['items'][] = $item;
		}
		$context['metabox_freehandblocks']['columncounter'] = count( $context['metabox_freehandblocks']['items'] );
	}
}


/**
 * 7 - Communities box
 * ----------------------------- */
if ( 'ja' === get_field( 'metabox_communities_show_or_not' ) ) {

	$method        = get_field( 'metabox_communities_show_or_not' );
	$metabox_items = get_field( 'metabox_communities_selection_manual' );

	if ( $metabox_items ) {

		$context['metabox_communities']                = [];
		$context['metabox_communities']['items']       = [];
		$context['metabox_communities']['title']       = ( get_field( 'metabox_communities_titel' ) ? get_field( 'metabox_communities_titel' ) : '' );
		$context['metabox_communities']['description'] = ( get_field( 'metabox_communities_description' ) ? get_field( 'metabox_communities_description' ) : '' );

		foreach ( $metabox_items as $postitem ) {

			$item          = array();
			$item['title'] = $postitem->name;
			$item['descr'] = $postitem->description;
			$item['url']   = get_term_link( $postitem );

			// TODO no image is yet available for tax communities
//			if ( $postitem['metabox_freehand_item_image'] ) {
//				$url         = $postitem['metabox_freehand_item_image']['sizes'][ $imagesize_for_thumbs ];
//				$alt         = $postitem['metabox_freehand_item_image']['alt'];
//				$item['img'] = '<img src="' . $url . '" alt="' . $postitem[''][''] . '" />';
//				// Provide Image as URL instead of HTML?
//				// $item['img']     = $url;
//				// $item['img_alt'] = $alt;
//			}

			$context['metabox_communities']['items'][] = $item;
		}
		$context['metabox_communities']['columncounter'] = count( $context['metabox_communities']['items'] );
	}
}


Timber::render( $templates, $context );
