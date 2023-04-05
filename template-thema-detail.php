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

$context     = Timber::context();
$timber_post = new Timber\Post();

/**
 * Fill Timber $context with available page/post Blocks/Metaboxes
 * @see /includes/gc-fill-context-with-acf-fields.php
 */
if ( function_exists( 'gc_fill_context_with_acf_fields' ) ) {
	$context = gc_fill_context_with_acf_fields( $context );
}


$templates            = [ 'thema-detail.twig' ];
$imagesize_for_thumbs = IMAGESIZE_16x9;
$current_thema_taxid  = get_current_thema_tax();
$acf_id               = GC_THEMA_TAX . '_' . $current_thema_taxid;
$themaclass           = get_field( 'thema_taxonomy_image', $acf_id );

if ( $themaclass ) {
	// do not use the slug for the term, use 'thema_taxonomy_image'
	$context['pageclass_thema'] = $themaclass;
}

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
			// select latest events for $current_thema_taxid
			// _event_start_date is a meta field for the events post type
			// this query selects future events for the $current_thema_taxid
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
						'taxonomy' => GC_THEMA_TAX,
						'field'    => 'term_id',
						'terms'    => $current_thema_taxid,
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

			// Add CTA 'overzichtslink' as cta Array to metabox_events
			if ( get_field( 'metabox_events_url_overview' ) ) {
				$url                                       = get_field( 'metabox_events_url_overview' );
				$context['metabox_events']['cta']          = [];
				$context['metabox_events']['cta']['title'] = $url['title'];
				$context['metabox_events']['cta']['url']   = $url['url'];
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
					'taxonomy' => GC_THEMA_TAX,
					'field'    => 'term_id',
					'terms'    => $current_thema_taxid,
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

		// Add CTA 'overzichtslink' as cta Array to metabox_webinars
		if ( get_field( 'metabox_webinars_url_overview' ) ) {
			$url                                         = get_field( 'metabox_webinars_url_overview' );
			$context['metabox_webinars']['cta']          = [];
			$context['metabox_webinars']['cta']['title'] = $url['title'];
			$context['metabox_webinars']['cta']['url']   = $url['url'];
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
					'taxonomy' => GC_THEMA_TAX,
					'field'    => 'term_id',
					'terms'    => $current_thema_taxid,
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

		// Add CTA 'overzichtslink' as cta Array to metabox_podcasts
		if ( get_field( 'metabox_podcasts_url_overview' ) ) {
			$url                                         = get_field( 'metabox_podcasts_url_overview' );
			$context['metabox_podcasts']['cta']          = [];
			$context['metabox_podcasts']['cta']['title'] = $url['title'];
			$context['metabox_podcasts']['cta']['url']   = $url['url'];
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
					'taxonomy' => GC_THEMA_TAX,
					'field'    => 'term_id',
					'terms'    => $current_thema_taxid,
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
		$context['metabox_posts']['description'] = ( get_field( 'metabox_posts_description' ) ? get_field( 'metabox_posts_description' ) : '' );

		// Add CTA 'overzichtslink' as cta Array to metabox_posts
		if ( get_field( 'metabox_posts_url_overview' ) ) {
			$url                                      = get_field( 'metabox_posts_url_overview' );
			$context['metabox_posts']['cta']          = [];
			$context['metabox_posts']['cta']['title'] = $url['title'];
			$context['metabox_posts']['cta']['url']   = $url['url'];
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
		$context['metabox_instrumenten']['all']         = []; // ALL instrumenten, unordered
		$context['metabox_instrumenten']['items']       = []; // Ordered instrumenten, passed to Twig
		$context['metabox_instrumenten']['title']       = ( get_field( 'metabox_instrumenten_titel' ) ? get_field( 'metabox_instrumenten_titel' ) : '' );
		$context['metabox_instrumenten']['description'] = ( get_field( 'metabox_instrumenten_description' ) ? get_field( 'metabox_instrumenten_description' ) : '' );

		// Add CTA 'overzichtslink' as cta Array to metabox_instrumenten
		if ( get_field( 'metabox_instrumenten_url_overview' ) ) {
			$url                                             = get_field( 'metabox_instrumenten_url_overview' );
			$context['metabox_instrumenten']['cta']          = [];
			$context['metabox_instrumenten']['cta']['title'] = $url['title'];
			$context['metabox_instrumenten']['cta']['url']   = $url['url'];
		}

		foreach ( $metabox_items as $postitem ) {

			// $postitem is a WP_Post object of `Instrument` CPT
			// and has some extra ACF fields
			$cpt_acf_link   = get_field( 'instrument_link', $postitem );
			$cpt_acf_sticky = get_field( 'instrument_sticky', $postitem );

			$item          = array();
			$item['title'] = get_the_title( $postitem );
			$item['descr'] = get_the_excerpt( $postitem );
			// - For URL we pick the `instrument_link` but fall back to permalink
			$item['url']       = $cpt_acf_link ? $cpt_acf_link['url'] : get_post_permalink( $postitem );
			$item['sticky']    = $cpt_acf_sticky;
			$item['post_type'] = get_post_type( $postitem );
			$item['img']       = get_the_post_thumbnail( $postitem, BLOG_SINGLE_DESKTOP );
			// Exception: we use BLOG_SINGLE_DESKTOP size. Will be shown in max 50% viewport
			// $item['img']       = get_the_post_thumbnail( $postitem );
			// $item['img']       = get_the_post_thumbnail( $postitem, $imagesize_for_thumbs );
			// NOTE: do we want an img _tag_ or _url_?

			// teaser.twig has space for displaying themas.
			// This code below was used to test the color of the thema labels
			// $currentthema      = get_term_by( 'term_id', $current_thema_taxid, GC_THEMA_TAX );
			// if ( $currentthema ) {
			// 	$thema            = array();
			// 	$thema['name']    = $currentthema->name;
			// 	$thema['slug']    = $themaclass; // !!!! not the slug please; use the 'thema_taxonomy_image' field
			// 	$item['themas'][] = $thema;
			// }

			// NOTE: add to `all` first (unordered)
			//       add to `items` later (ordered)
			$context['metabox_instrumenten']['all'][] = $item;
		}

		// Instrumenten have an optional `sticky_instrument` field
		// Sticky instrumenten are shown first in the list, then title ordered DESC
		// Reorder items according to Stickyness
		// NOTE: `sticky` is a custom ACF field, NOT the WP core 'sticky' Post property!
		$all_instruments = $context['metabox_instrumenten']['all'];
		if ( ! empty( $all_instruments ) ) {
			// Collect all sticky/non-sticky items
			// - 1st an array of all sticky items (+ ordered ASC by title)
			// - 2nd an array of all non-sticky items (already ordered by title)
			$all_sticky_instruments = array_filter( $all_instruments, function ( $i ) {
				return $i['sticky'];
			} );
			$non_sticky_instruments = array_filter( $all_instruments, function ( $i ) {
				return ! $i['sticky'];
			} );

			// Now make sure to also SORT the sticky items by title
			if ( ! empty( $all_sticky_instruments ) ) {
				usort( $all_sticky_instruments, function ( $a, $b ) {
					return strcmp( $a['title'], $b['title'] );
				} );
			}

			// Finally construct our new `instrumenten` array of 2 merged arrays:
			$context['metabox_instrumenten']['items'] = array_merge( $all_sticky_instruments, $non_sticky_instruments );
		}

		$context['metabox_instrumenten']['columncounter'] = count( $context['metabox_instrumenten']['items'] );
	}
}


/**
 * 6 - Media + text box
 * ----------------------------- */

// show the_content as 6th block
if ( $timber_post->post_content ) {
	$context['metabox_freehandblocks'] = $timber_post->post_content;
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

		// Add CTA 'overzichtslink' as cta Array to metabox_communities
		if ( get_field( 'metabox_communities_url_overview' ) ) {
			$url                                            = get_field( 'metabox_communities_url_overview' );
			$context['metabox_communities']['cta']          = [];
			$context['metabox_communities']['cta']['title'] = $url['title'];
			$context['metabox_communities']['cta']['url']   = $url['url'];
		}

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

// get the description from GC_THEMA_TAX term to use as inleiding
$term_info = get_term_by( 'id', $current_thema_taxid, GC_THEMA_TAX );

if ( $term_info && ! is_wp_error( $term_info ) ) {

	// move content from editor to metabox_freehandblocks
	$blocks = parse_blocks( $timber_post->post_content );
	foreach ( $blocks as $block ) {
		if ( isset( $block['blockName'] ) && $block['blockName'] !== null ) {
			$context['metabox_content'] = ($context['metabox_content'] ?? '') . render_block( $block );
		}
	}

	// Page title is taken from term name
	$timber_post->post_title = $term_info->name;

	// text for 'inleiding' is taken from term description
	$timber_post->post_content = $term_info->description;

}

$context['post']       = $timber_post;
$context['is_unboxed'] = true;


Timber::render( $templates, $context );
