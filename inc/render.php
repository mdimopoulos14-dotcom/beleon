<?php
/**
 * Render functions shared by Elementor widgets and PHP templates.
 * One markup source means the Elementor and fallback designs never drift.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Eyebrow / kicker line.
 *
 * @param string $text  Text.
 * @param string $class Extra classes.
 * @return string
 */
function beleon_eyebrow( $text, $class = '' ) {
	return '' === trim( (string) $text ) ? '' : '<p class="bl-eyebrow ' . esc_attr( $class ) . '">' . esc_html( $text ) . '</p>';
}

/**
 * Button.
 *
 * @param string       $text    Label.
 * @param string|array $link    URL or Elementor link array.
 * @param string       $variant solid|ghost|line|light.
 * @return string
 */
function beleon_button( $text, $link, $variant = 'solid' ) {
	if ( '' === trim( (string) $text ) ) {
		return '';
	}
	$attrs = is_array( $link ) ? beleon_link_attrs( $link ) : ( $link ? ' href="' . esc_url( $link ) . '"' : '' );
	if ( ! $attrs ) {
		return '';
	}
	return '<a class="bl-btn bl-btn--' . esc_attr( $variant ) . '"' . $attrs . '><span>' . esc_html( $text ) . '</span>' . beleon_icon( 'arrow', 'bl-btn__ico' ) . '</a>';
}

/* --------------------------------------------------------------------------
 * Tours
 * ------------------------------------------------------------------------ */

/**
 * Query tour IDs.
 *
 * @param array $a {
 *     @type string $source      latest|upcoming|manual|destination|related|menu
 *     @type int    $count       Max posts.
 *     @type int[]  $ids         Manual IDs.
 *     @type int    $destination Destination ID (0 = current destination).
 *     @type int    $exclude     Post to exclude.
 *     @type bool   $url_filters Respect ?dest= and ?month=.
 * }
 * @return int[]
 */
function beleon_query_tours( $a ) {
	$a = wp_parse_args(
		$a,
		array(
			'source'      => 'upcoming',
			'count'       => 6,
			'ids'         => array(),
			'destination' => 0,
			'exclude'     => 0,
			'url_filters' => false,
		)
	);

	$args = array(
		'post_type'      => 'tours',
		'post_status'    => 'publish',
		'posts_per_page' => max( 1, (int) $a['count'] ),
		'fields'         => 'ids',
		'no_found_rows'  => true,
	);
	$in   = null;

	switch ( $a['source'] ) {
		case 'manual':
			$in               = array_map( 'intval', (array) $a['ids'] );
			$args['orderby']  = 'post__in';
			break;
		case 'destination':
			$dest = $a['destination'] ? (int) $a['destination'] : beleon_context_post( 'destinations' );
			$in   = beleon_destination_tour_ids( $dest );
			break;
		case 'related':
			$tour = beleon_context_post( 'tours' );
			$in   = array();
			foreach ( beleon_tour_destination_ids( $tour ) as $dest ) {
				$in = array_merge( $in, beleon_destination_tour_ids( $dest ) );
			}
			$in            = array_diff( array_unique( $in ), array( $tour ) );
			$a['exclude']  = $tour;
			break;
		case 'menu':
			$args['orderby'] = array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			);
			break;
	}

	if ( $a['url_filters'] ) {
		$filter = beleon_url_filter_ids();
		if ( null !== $filter ) {
			$in = null === $in ? $filter : array_intersect( $in, $filter );
		}
	}

	if ( null !== $in ) {
		$in = array_values( array_filter( array_map( 'intval', $in ) ) );
		if ( ! $in ) {
			return array();
		}
		$args['post__in'] = $in;
	}
	if ( $a['exclude'] ) {
		$args['post__not_in'] = array( (int) $a['exclude'] );
	}

	if ( 'upcoming' === $a['source'] ) {
		// Soonest departure first; tours without dates go last.
		$args['posts_per_page'] = 200;
		$ids                    = get_posts( $args );
		usort(
			$ids,
			function ( $x, $y ) {
				$nx = beleon_next_departure( $x );
				$ny = beleon_next_departure( $y );
				return ( $nx && $nx['ts'] ? $nx['ts'] : PHP_INT_MAX ) <=> ( $ny && $ny['ts'] ? $ny['ts'] : PHP_INT_MAX );
			}
		);
		return array_slice( $ids, 0, max( 1, (int) $a['count'] ) );
	}

	return get_posts( $args );
}

/**
 * Tour IDs matching ?dest= and ?month= (null when no filter is set).
 *
 * @return int[]|null
 */
function beleon_url_filter_ids() {
	$state = beleon_finder_state();
	if ( ! array_filter( array_diff_key( $state, array( 'sort' => 1 ) ) ) ) {
		return null;
	}
	$ids = get_posts(
		array(
			'post_type'      => 'tours',
			'post_status'    => 'publish',
			'posts_per_page' => 500,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);
	$ids = array_filter(
		$ids,
		function ( $id ) use ( $state ) {
			return beleon_tour_matches( $id, $state );
		}
	);
	return array_values( $ids ? $ids : array( 0 ) );
}

/**
 * Card defaults.
 *
 * @return array
 */
function beleon_tour_card_defaults() {
	return array(
		'style'        => 'classic',
		'ratio'        => '4/5',
		'show_place'   => true,
		'show_facts'   => true,
		'show_price'   => true,
		'show_badge'   => true,
		'show_excerpt' => false,
		'price_label'  => __( 'from', 'beleon-tours' ),
		'heading'      => 'h3',
		'image_size'   => 'beleon-card',
		'attrs'        => array(),
	);
}

/**
 * One tour card.
 *
 * @param int   $id Tour ID.
 * @param array $o  Options (see beleon_tour_card_defaults()).
 * @return string
 */
function beleon_render_tour_card( $id, $o = array() ) {
	$o     = wp_parse_args( $o, beleon_tour_card_defaults() );
	$link  = get_permalink( $id );
	$title = get_the_title( $id );
	$tag   = in_array( $o['heading'], array( 'h2', 'h3', 'h4', 'p' ), true ) ? $o['heading'] : 'h3';
	$thumb = get_post_thumbnail_id( $id );
	$place = $o['show_place'] ? beleon_tour_place( $id ) : '';
	$badge = $o['show_badge'] ? beleon_text( 'badge', $id ) : '';
	$price = $o['show_price'] ? beleon_format_price( beleon_text( 'price', $id ) ) : '';
	$next  = beleon_next_departure( $id );
	$dur   = beleon_text( 'duration', $id );

	$attrs = '';
	foreach ( (array) $o['attrs'] as $name => $value ) {
		if ( null !== $value ) {
			$attrs .= ' ' . esc_attr( $name ) . '="' . esc_attr( (string) $value ) . '"';
		}
	}
	$h  = '<article class="bl-tcard bl-tcard--' . esc_attr( $o['style'] ) . '" style="--bl-ratio:' . esc_attr( $o['ratio'] ) . '"' . $attrs . '>';
	$h .= '<a class="bl-tcard__media" href="' . esc_url( $link ) . '" tabindex="-1" aria-hidden="true">';
	$h .= $thumb ? beleon_img( $thumb, $o['image_size'], array( 'alt' => '', 'sizes' => '(max-width: 767px) 92vw, (max-width: 1100px) 46vw, 30vw' ) ) : '<span class="bl-ph"></span>';
	if ( $badge ) {
		$h .= '<span class="bl-badge">' . esc_html( $badge ) . '</span>';
	}
	$h .= '</a><div class="bl-tcard__body">';
	if ( $place ) {
		$h .= '<p class="bl-tcard__place">' . esc_html( $place ) . '</p>';
	}
	$h .= '<' . $tag . ' class="bl-tcard__title"><a href="' . esc_url( $link ) . '">' . esc_html( $title ) . '</a></' . $tag . '>';

	if ( $o['show_excerpt'] && has_excerpt( $id ) ) {
		$h .= '<p class="bl-tcard__excerpt">' . esc_html( wp_trim_words( get_the_excerpt( $id ), 22 ) ) . '</p>';
	}
	if ( $o['show_facts'] && ( $dur || $next ) ) {
		$h .= '<ul class="bl-tcard__facts">';
		if ( $dur ) {
			$h .= '<li>' . beleon_icon( 'clock' ) . esc_html( $dur ) . '</li>';
		}
		if ( $next ) {
			$h .= '<li>' . beleon_icon( 'calendar' ) . esc_html( $next['label'] ) . '</li>';
		}
		$h .= '</ul>';
	}
	$h .= '<div class="bl-tcard__foot">';
	if ( $price ) {
		$h .= '<p class="bl-tcard__price"><small>' . esc_html( beleon_price_label( $id, $o['price_label'] ) ) . '</small> ' . esc_html( $price ) . '</p>';
	}
	/* translators: %s: tour title */
	$h .= '<a class="bl-arrow" href="' . esc_url( $link ) . '" aria-label="' . esc_attr( sprintf( __( 'Discover %s', 'beleon-tours' ), $title ) ) . '">' . beleon_icon( 'arrow' ) . '</a>';
	$h .= '</div></div></article>';
	return $h;
}

/**
 * Tours collection (grid or carousel).
 *
 * @param int[] $ids Tour IDs.
 * @param array $o   Card options + layout, columns, empty.
 * @return string
 */
function beleon_render_tours( $ids, $o = array() ) {
	$o = wp_parse_args(
		$o,
		array(
			'layout' => 'grid',
			'empty'  => __( 'New departures are being prepared. Contact us for tailor-made dates.', 'beleon-tours' ),
		)
	);
	if ( ! $ids ) {
		return '' === $o['empty'] ? '' : '<p class="bl-empty">' . esc_html( $o['empty'] ) . '</p>';
	}
	$cards = '';
	foreach ( $ids as $id ) {
		$cards .= beleon_render_tour_card( $id, $o );
	}
	return beleon_collection( $cards, $o['layout'], 'bl-tours' );
}

/**
 * Grid / carousel wrapper.
 *
 * @param string $items  Items HTML.
 * @param string $layout grid|carousel.
 * @param string $class  Extra class.
 * @return string
 */
function beleon_collection( $items, $layout, $class ) {
	if ( 'carousel' === $layout ) {
		return '<div class="bl-carousel ' . esc_attr( $class ) . '" data-bl-carousel><div class="bl-carousel__track bl-stagger" tabindex="0">' . $items . '</div>'
			. '<div class="bl-carousel__nav"><button type="button" class="bl-arrow bl-arrow--prev" data-bl-prev aria-label="' . esc_attr__( 'Previous', 'beleon-tours' ) . '">' . beleon_icon( 'arrow' ) . '</button>'
			. '<button type="button" class="bl-arrow" data-bl-next aria-label="' . esc_attr__( 'Next', 'beleon-tours' ) . '">' . beleon_icon( 'arrow' ) . '</button></div></div>';
	}
	return '<div class="bl-grid bl-stagger ' . esc_attr( $class ) . '">' . $items . '</div>';
}

/**
 * Filter bar: destination chips + month select (works without JavaScript).
 *
 * @param string $action Form target URL.
 * @return string
 */
function beleon_render_tour_filters( $action = '' ) {
	$action  = $action ? $action : remove_query_arg( array( 'dest', 'month', 'paged' ) );
	$current = isset( $_GET['dest'] ) ? absint( $_GET['dest'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
	$month   = isset( $_GET['month'] ) ? sanitize_text_field( wp_unslash( $_GET['month'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$dests   = get_posts(
		array(
			'post_type'      => 'destinations',
			'posts_per_page' => 60,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'no_found_rows'  => true,
		)
	);

	$h  = '<nav class="bl-filters" aria-label="' . esc_attr__( 'Filter tours', 'beleon-tours' ) . '"><div class="bl-chips">';
	$h .= '<a class="bl-chip' . ( $current ? '' : ' is-active' ) . '" href="' . esc_url( remove_query_arg( 'dest', $action ) ) . '">' . esc_html__( 'All', 'beleon-tours' ) . '</a>';
	foreach ( $dests as $d ) {
		if ( ! beleon_destination_tour_ids( $d->ID ) ) {
			continue;
		}
		$url = add_query_arg( 'dest', $d->ID, $action );
		if ( $month ) {
			$url = add_query_arg( 'month', $month, $url );
		}
		$h .= '<a class="bl-chip' . ( $current === $d->ID ? ' is-active' : '' ) . '" href="' . esc_url( $url ) . '">' . esc_html( $d->post_title ) . '</a>';
	}
	$h .= '</div>';
	$h .= '<form class="bl-month" method="get" action="' . esc_url( $action ) . '">';
	if ( $current ) {
		$h .= '<input type="hidden" name="dest" value="' . (int) $current . '">';
	}
	$h .= '<label class="screen-reader-text" for="bl-month">' . esc_html__( 'Month', 'beleon-tours' ) . '</label>';
	$h .= '<select id="bl-month" name="month" onchange="this.form.submit()">' . beleon_month_options( $month ) . '</select>';
	$h .= '<noscript><button class="bl-btn bl-btn--line" type="submit">' . esc_html__( 'Show', 'beleon-tours' ) . '</button></noscript></form></nav>';
	return $h;
}

/**
 * <option>s for the next 12 months.
 *
 * @param string $selected Selected Y-m.
 * @return string
 */
function beleon_month_options( $selected = '' ) {
	$months = array(
		1  => __( 'January', 'beleon-tours' ),
		2  => __( 'February', 'beleon-tours' ),
		3  => __( 'March', 'beleon-tours' ),
		4  => __( 'April', 'beleon-tours' ),
		5  => __( 'May', 'beleon-tours' ),
		6  => __( 'June', 'beleon-tours' ),
		7  => __( 'July', 'beleon-tours' ),
		8  => __( 'August', 'beleon-tours' ),
		9  => __( 'September', 'beleon-tours' ),
		10 => __( 'October', 'beleon-tours' ),
		11 => __( 'November', 'beleon-tours' ),
		12 => __( 'December', 'beleon-tours' ),
	);
	$h     = '<option value="">' . esc_html__( 'Any month', 'beleon-tours' ) . '</option>';
	$start = strtotime( wp_date( 'Y-m-01' ) );
	for ( $i = 0; $i < 12; $i++ ) {
		$ts    = strtotime( "+{$i} month", $start );
		$value = gmdate( 'Y-m', $ts );
		$h    .= '<option value="' . esc_attr( $value ) . '"' . selected( $selected, $value, false ) . '>' . esc_html( $months[ (int) gmdate( 'n', $ts ) ] . ' ' . gmdate( 'Y', $ts ) ) . '</option>';
	}
	return $h;
}

/**
 * Hero search: destination + month, submits to the tours listing.
 *
 * @param array $o Labels.
 * @return string
 */
function beleon_render_search( $o = array() ) {
	$o     = wp_parse_args(
		$o,
		array(
			'dest_label'  => __( 'Where to?', 'beleon-tours' ),
			'month_label' => __( 'When?', 'beleon-tours' ),
			'button'      => __( 'Find a journey', 'beleon-tours' ),
			'action'      => beleon_tours_link(),
		)
	);
	$dests = get_posts(
		array(
			'post_type'      => 'destinations',
			'posts_per_page' => 60,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		)
	);
	$h     = '<form class="bl-search" method="get" action="' . esc_url( $o['action'] ) . '" role="search">';
	$h    .= '<label class="bl-search__field">' . beleon_icon( 'pin' ) . '<span>' . esc_html( $o['dest_label'] ) . '</span><select name="dest"><option value="">' . esc_html__( 'All destinations', 'beleon-tours' ) . '</option>';
	foreach ( $dests as $d ) {
		$h .= '<option value="' . (int) $d->ID . '">' . esc_html( $d->post_title ) . '</option>';
	}
	$h .= '</select></label>';
	$h .= '<label class="bl-search__field">' . beleon_icon( 'calendar' ) . '<span>' . esc_html( $o['month_label'] ) . '</span><select name="month">' . beleon_month_options() . '</select></label>';
	$h .= '<button class="bl-btn bl-btn--solid" type="submit"><span>' . esc_html( $o['button'] ) . '</span>' . beleon_icon( 'search', 'bl-btn__ico' ) . '</button></form>';
	return $h;
}

/* --------------------------------------------------------------------------
 * Destinations
 * ------------------------------------------------------------------------ */

/**
 * Destination IDs.
 *
 * @param array $a source (all|manual), count, ids.
 * @return int[]
 */
function beleon_query_destinations( $a ) {
	$a    = wp_parse_args(
		$a,
		array(
			'source' => 'all',
			'count'  => 8,
			'ids'    => array(),
		)
	);
	$args = array(
		'post_type'      => 'destinations',
		'post_status'    => 'publish',
		'posts_per_page' => max( 1, (int) $a['count'] ),
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'title'      => 'ASC',
		),
	);
	if ( 'manual' === $a['source'] && $a['ids'] ) {
		$args['post__in'] = array_map( 'intval', (array) $a['ids'] );
		$args['orderby']  = 'post__in';
	}
	return get_posts( $args );
}

/**
 * Destinations collection.
 *
 * @param int[] $ids Destination IDs.
 * @param array $o   layout (arches|mosaic|grid|carousel), show_count, show_region, heading.
 * @return string
 */
function beleon_render_destinations( $ids, $o = array() ) {
	$o = wp_parse_args(
		$o,
		array(
			'layout'      => 'arches',
			'show_count'  => true,
			'show_region' => true,
			'heading'     => 'h3',
		)
	);
	if ( ! $ids ) {
		return '';
	}
	$tag   = in_array( $o['heading'], array( 'h2', 'h3', 'h4', 'p' ), true ) ? $o['heading'] : 'h3';
	$items = '';
	foreach ( $ids as $i => $id ) {
		$count  = count( beleon_destination_tour_ids( $id ) );
		$region = $o['show_region'] ? beleon_text( 'region', $id ) : '';
		$thumb  = get_post_thumbnail_id( $id );
		$size   = 'mosaic' === $o['layout'] && 0 === $i % 5 ? 'large' : 'beleon-card';

		$items .= '<a class="bl-dcard" href="' . esc_url( get_permalink( $id ) ) . '">';
		$items .= '<span class="bl-dcard__media">' . ( $thumb ? beleon_img( $thumb, $size, array( 'alt' => '', 'sizes' => '(max-width: 767px) 46vw, 25vw' ) ) : '<span class="bl-ph"></span>' ) . '</span>';
		$items .= '<span class="bl-dcard__body">';
		if ( $region ) {
			$items .= '<span class="bl-dcard__region">' . esc_html( $region ) . '</span>';
		}
		$items .= '<' . $tag . ' class="bl-dcard__title">' . esc_html( get_the_title( $id ) ) . '</' . $tag . '>';
		if ( $o['show_count'] && $count ) {
			/* translators: %d: number of tours */
			$items .= '<span class="bl-dcard__count">' . esc_html( sprintf( _n( '%d journey', '%d journeys', $count, 'beleon-tours' ), $count ) ) . '</span>';
		}
		$items .= '</span></a>';
	}
	if ( 'carousel' === $o['layout'] ) {
		return beleon_collection( $items, 'carousel', 'bl-dests bl-dests--arches' );
	}
	return '<div class="bl-dests bl-dests--' . esc_attr( $o['layout'] ) . ' bl-stagger">' . $items . '</div>';
}

/* --------------------------------------------------------------------------
 * Single tour parts
 * ------------------------------------------------------------------------ */

/**
 * Facts strip.
 *
 * @param int   $id Tour ID.
 * @param array $o  items (array of keys), style (bar|list|glass).
 * @return string
 */
function beleon_render_facts( $id, $o = array() ) {
	$o     = wp_parse_args(
		$o,
		array(
			'items' => array( 'duration', 'departure_city', 'next', 'group_size' ),
			'style' => 'bar',
		)
	);
	$next  = beleon_next_departure( $id );
	$facts = array(
		'duration'       => array( 'clock', __( 'Duration', 'beleon-tours' ), beleon_text( 'duration', $id ) ),
		'departure_city' => array( 'plane', __( 'Departs from', 'beleon-tours' ), beleon_text( 'departure_city', $id ) ),
		'next'           => array( 'calendar', __( 'Next departure', 'beleon-tours' ), $next ? $next['label'] : '' ),
		'group_size'     => array( 'users', __( 'Group', 'beleon-tours' ), beleon_text( 'group_size', $id ) ),
		'price'          => array( 'star', beleon_price_label( $id, __( 'From', 'beleon-tours' ) ), beleon_format_price( beleon_text( 'price', $id ) ) ),
	);
	$h     = '';
	foreach ( (array) $o['items'] as $key ) {
		if ( empty( $facts[ $key ][2] ) ) {
			continue;
		}
		list( $icon, $label, $value ) = $facts[ $key ];
		$h .= '<li>' . beleon_icon( $icon ) . '<span><small>' . esc_html( $label ) . '</small>' . esc_html( $value ) . '</span></li>';
	}
	return $h ? '<ul class="bl-facts bl-facts--' . esc_attr( $o['style'] ) . '">' . $h . '</ul>' : '';
}

/**
 * Itinerary timeline (native <details>, no JavaScript).
 *
 * @param int   $id Tour ID.
 * @param array $o  open_first, open_all, day_label.
 * @return string
 */
function beleon_render_itinerary( $id, $o = array() ) {
	$o    = wp_parse_args(
		$o,
		array(
			'open_first' => true,
			'open_all'   => false,
			'day_label'  => __( 'Day', 'beleon-tours' ),
		)
	);
	$days = beleon_itinerary( $id );
	if ( ! $days ) {
		return '';
	}
	$h = '<ol class="bl-itin">';
	foreach ( $days as $i => $day ) {
		$open  = $o['open_all'] || ( $o['open_first'] && 0 === $i );
		$title = preg_replace( '/^(ημέρα|ημερα|day)\s*\d+\s*[:.\-–—]\s*/iu', '', $day['title'] );
		$h    .= '<li class="bl-itin__day"><details' . ( $open ? ' open' : '' ) . '><summary>';
		$h    .= '<span class="bl-itin__num"><small>' . esc_html( $o['day_label'] ) . '</small>' . str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) . '</span>';
		$h    .= '<span class="bl-itin__title">' . esc_html( $title ) . '</span><span class="bl-itin__toggle" aria-hidden="true">' . beleon_icon( 'plus' ) . '</span></summary>';
		if ( '' !== $day['text'] ) {
			$h .= '<div class="bl-itin__text">' . wpautop( wp_kses_post( $day['text'] ) ) . '</div>';
		}
		$h .= '</details></li>';
	}
	return $h . '</ol>';
}

/**
 * Booking card: price, departures, enquiry.
 *
 * @param int   $id Tour ID.
 * @param array $o  title, button, show_phone, sticky, max_dates.
 * @return string
 */
function beleon_render_booking( $id, $o = array() ) {
	$o     = wp_parse_args(
		$o,
		array(
			'title'      => __( 'Departures', 'beleon-tours' ),
			'button'     => __( 'Book now', 'beleon-tours' ),
			'show_phone' => true,
			'sticky'     => true,
			'max_dates'  => 8,
		)
	);
	$price = beleon_format_price( beleon_text( 'price', $id ) );
	$note  = beleon_text( 'price_note', $id );
	$deps  = array_slice( beleon_departures( $id ), 0, max( 1, (int) $o['max_dates'] ) );
	$phone = beleon_mod( 'beleon_phone' );

	$h = '<aside class="bl-book' . ( $o['sticky'] ? ' is-sticky' : '' ) . '" id="book">';
	if ( $price ) {
		$h .= '<p class="bl-book__price"><small>' . esc_html( beleon_price_label( $id ) ) . '</small>' . esc_html( $price ) . '</p>';
		if ( $note ) {
			$h .= '<p class="bl-book__note">' . esc_html( $note ) . '</p>';
		}
	}
	if ( $deps ) {
		$h .= '<p class="bl-book__label">' . esc_html( $o['title'] ) . '</p><ul class="bl-book__dates">';
		foreach ( $deps as $d ) {
			$h .= '<li><span>' . esc_html( $d['label'] ) . '</span>' . ( $d['note'] ? '<em>' . esc_html( $d['note'] ) . '</em>' : '' ) . '</li>';
		}
		$h .= '</ul>';
	} else {
		$h .= '<p class="bl-book__note">' . esc_html__( 'New dates soon — ask us about private departures.', 'beleon-tours' ) . '</p>';
	}
	$h .= beleon_button( $o['button'], beleon_booking_link( $id ), 'solid' );
	if ( $o['show_phone'] && $phone ) {
		$h .= '<a class="bl-book__phone" href="tel:' . esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ) . '">' . beleon_icon( 'phone' ) . '<span><small>' . esc_html__( 'Prefer to talk?', 'beleon-tours' ) . '</small>' . esc_html( $phone ) . '</span></a>';
	}
	return $h . '</aside>';
}

/**
 * Booking panel of the tour page sidebar: price, calendar and booking form.
 *
 * @param int $id Tour ID.
 * @return string
 */
function beleon_render_booking_panel( $id ) {
	$price = beleon_format_price( beleon_text( 'price', $id ) );
	$note  = beleon_text( 'price_note', $id );
	$phone = beleon_mod( 'beleon_phone' );

	$h = '<aside class="bl-bookp" id="enquire" aria-labelledby="bl-bookp-title">';
	$h .= '<div class="bl-bookp__head">';
	$h .= '<p class="bl-bookp__eyebrow" id="bl-bookp-title">' . esc_html__( 'Book this journey', 'beleon-tours' ) . '</p>';
	if ( $price ) {
		$h .= '<p class="bl-bookp__price"><small>' . esc_html( beleon_price_label( $id ) ) . '</small>' . esc_html( $price ) . '</p>';
		if ( $note ) {
			$h .= '<p class="bl-bookp__note">' . esc_html( $note ) . '</p>';
		}
	}
	$h .= '</div>';
	$h .= beleon_render_form(
		'booking',
		array(
			'tour'   => $id,
			'layout' => 'panel',
			'id'     => 'bl-form-booking',
		)
	);
	if ( $phone ) {
		$h .= '<a class="bl-book__phone" href="tel:' . esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ) . '">' . beleon_icon( 'phone' ) . '<span><small>' . esc_html__( 'Prefer to talk?', 'beleon-tours' ) . '</small>' . esc_html( $phone ) . '</span></a>';
	}
	return $h . '</aside>';
}

/**
 * Included / not included.
 *
 * @param int   $id Tour ID.
 * @param array $o  Labels.
 * @return string
 */
function beleon_render_includes( $id, $o = array() ) {
	$o   = wp_parse_args(
		$o,
		array(
			'in_title'  => __( 'Included', 'beleon-tours' ),
			'out_title' => __( 'Not included', 'beleon-tours' ),
		)
	);
	$in  = beleon_lines( 'includes', $id );
	$out = beleon_lines( 'excludes', $id );
	if ( ! $in && ! $out ) {
		return '';
	}
	$h = '<div class="bl-incl">';
	foreach ( array( array( $in, $o['in_title'], 'check', 'in' ), array( $out, $o['out_title'], 'x', 'out' ) ) as $col ) {
		if ( ! $col[0] ) {
			continue;
		}
		$h .= '<div class="bl-incl__col bl-incl__col--' . $col[3] . '"><h3>' . esc_html( $col[1] ) . '</h3><ul>';
		foreach ( $col[0] as $line ) {
			$h .= '<li>' . beleon_icon( $col[2] ) . '<span>' . esc_html( $line ) . '</span></li>';
		}
		$h .= '</ul></div>';
	}
	return $h . '</div>';
}

/**
 * Highlights list.
 *
 * @param int $id Tour ID.
 * @return string
 */
function beleon_render_highlights( $id ) {
	$lines = beleon_lines( 'highlights', $id );
	if ( ! $lines ) {
		return '';
	}
	$h = '<ul class="bl-highlights bl-stagger">';
	foreach ( $lines as $line ) {
		$h .= '<li>' . beleon_icon( 'check' ) . '<span>' . esc_html( $line ) . '</span></li>';
	}
	return $h . '</ul>';
}

/**
 * Image gallery with lightbox.
 *
 * @param int[] $ids Attachment IDs.
 * @param array $o   layout (mosaic|grid|row), limit.
 * @return string
 */
function beleon_render_gallery( $ids, $o = array() ) {
	$o   = wp_parse_args(
		$o,
		array(
			'layout' => 'mosaic',
			'limit'  => 9,
		)
	);
	$ids = array_slice( (array) $ids, 0, max( 1, (int) $o['limit'] ) );
	if ( ! $ids ) {
		return '';
	}
	$h = '<div class="bl-gallery bl-gallery--' . esc_attr( $o['layout'] ) . ' bl-stagger" data-bl-gallery>';
	foreach ( $ids as $img ) {
		$full = wp_get_attachment_image_url( $img, 'beleon-wide' );
		$alt  = (string) get_post_meta( $img, '_wp_attachment_image_alt', true );
		$h   .= '<a class="bl-gallery__item" href="' . esc_url( $full ? $full : wp_get_attachment_url( $img ) ) . '" data-bl-lightbox>' . beleon_img( $img, 'beleon-card', array( 'alt' => $alt, 'sizes' => '(max-width: 767px) 50vw, 33vw' ) ) . '</a>';
	}
	return $h . '</div>';
}

/* --------------------------------------------------------------------------
 * Generic sections
 * ------------------------------------------------------------------------ */

/**
 * Section heading.
 *
 * @param array $o eyebrow, number, title, text, tag, align, link_text, link.
 * @return string
 */
function beleon_render_heading( $o ) {
	$o   = wp_parse_args(
		$o,
		array(
			'eyebrow'   => '',
			'number'    => '',
			'title'     => '',
			'text'      => '',
			'tag'       => 'h2',
			'align'     => 'left',
			'link_text' => '',
			'link'      => '',
			'size'      => 'l',
		)
	);
	$tag = in_array( $o['tag'], array( 'h1', 'h2', 'h3', 'h4', 'p' ), true ) ? $o['tag'] : 'h2';
	$h   = '<div class="bl-heading bl-heading--' . esc_attr( $o['align'] ) . ' bl-heading--' . esc_attr( $o['size'] ) . '"><div class="bl-heading__main">';
	if ( $o['eyebrow'] || $o['number'] ) {
		$h .= '<p class="bl-eyebrow">' . ( $o['number'] ? '<span class="bl-eyebrow__num">' . esc_html( $o['number'] ) . '</span>' : '' ) . esc_html( $o['eyebrow'] ) . '</p>';
	}
	if ( $o['title'] ) {
		$h .= '<' . $tag . ' class="bl-heading__title">' . beleon_accent( $o['title'] ) . '</' . $tag . '>';
	}
	if ( $o['text'] ) {
		$h .= '<div class="bl-heading__text">' . wpautop( wp_kses_post( $o['text'] ) ) . '</div>';
	}
	$h .= '</div>';
	if ( $o['link_text'] ) {
		$h .= beleon_button( $o['link_text'], $o['link'], 'line' );
	}
	return $h . '</div>';
}

/**
 * Page hero for archives, pages and singles.
 *
 * @param array $o title, eyebrow, text, image (ID), facts (HTML), crumbs (bool), variant (image|ink|ivory), priority.
 * @return string
 */
function beleon_render_page_hero( $o ) {
	$o       = wp_parse_args(
		$o,
		array(
			'title'   => '',
			'eyebrow' => '',
			'text'    => '',
			'image'   => 0,
			'facts'   => '',
			'crumbs'  => true,
			'variant' => 'image',
			'after'   => '',
		)
	);
	$variant = $o['image'] ? 'image' : ( 'image' === $o['variant'] ? 'ink' : $o['variant'] );
	$h       = '<header class="bl-phero bl-phero--' . esc_attr( $variant ) . ( 'image' === $variant ? '' : ' bl-shape-rings' ) . '">';
	if ( $o['image'] ) {
		$h .= '<div class="bl-phero__media">' . beleon_img( $o['image'], 'beleon-wide', array( 'priority' => true, 'alt' => '', 'sizes' => '100vw' ) ) . '</div>';
	}
	$h .= '<div class="bl-wrap bl-phero__in">';
	if ( $o['crumbs'] ) {
		$h .= beleon_breadcrumbs();
	}
	$h .= beleon_eyebrow( $o['eyebrow'], 'bl-m-fade-up' );
	$h .= '<h1 class="bl-phero__title bl-m-mask-up bl-d-1">' . beleon_accent( $o['title'] ) . '</h1>';
	if ( $o['text'] ) {
		$h .= '<p class="bl-phero__text bl-m-fade-up bl-d-2">' . esc_html( $o['text'] ) . '</p>';
	}
	if ( $o['facts'] ) {
		$h .= '<div class="bl-phero__facts bl-m-fade-up bl-d-3">' . $o['facts'] . '</div>';
	}
	$h .= $o['after'] . '</div></header>';
	return $h;
}

/**
 * Breadcrumbs (with schema.org markup).
 *
 * @return string
 */
function beleon_breadcrumbs() {
	if ( is_front_page() ) {
		return '';
	}
	$items = array( array( __( 'Home', 'beleon-tours' ), home_url( '/' ) ) );
	if ( is_singular( 'tours' ) || is_post_type_archive( 'tours' ) ) {
		$items[] = array( __( 'Tours', 'beleon-tours' ), beleon_tours_link() );
	} elseif ( is_singular( 'destinations' ) || is_post_type_archive( 'destinations' ) ) {
		$archive = get_post_type_archive_link( 'destinations' );
		$items[] = array( __( 'Destinations', 'beleon-tours' ), $archive ? $archive : '' );
	}
	if ( is_singular() ) {
		$items[] = array( get_the_title(), '' );
	}
	$h = '<nav class="bl-crumbs bl-m-fade" aria-label="' . esc_attr__( 'Breadcrumb', 'beleon-tours' ) . '"><ol itemscope itemtype="https://schema.org/BreadcrumbList">';
	foreach ( $items as $i => $item ) {
		$last = count( $items ) - 1 === $i;
		$h   .= '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
		$h   .= ( $item[1] && ! $last ) ? '<a itemprop="item" href="' . esc_url( $item[1] ) . '"><span itemprop="name">' . esc_html( $item[0] ) . '</span></a>' : '<span itemprop="name" aria-current="page">' . esc_html( $item[0] ) . '</span>';
		$h   .= '<meta itemprop="position" content="' . ( $i + 1 ) . '"></li>';
	}
	return apply_filters( 'beleon_breadcrumbs_html', $h . '</ol></nav>' );
}

/**
 * CTA band.
 *
 * @param array $o eyebrow, title, text, btn1, link1, btn2, link2, surface, image.
 * @return string
 */
function beleon_render_cta( $o ) {
	$o = wp_parse_args(
		$o,
		array(
			'eyebrow' => '',
			'title'   => '',
			'text'    => '',
			'btn1'    => '',
			'link1'   => '',
			'btn2'    => '',
			'link2'   => '',
			'surface' => 'night',
			'image'   => 0,
			'align'   => 'center',
		)
	);
	$h = '<div class="bl-cta bl-surface-' . esc_attr( $o['surface'] ) . ' bl-cta--' . esc_attr( $o['align'] ) . ( $o['image'] ? ' has-image' : ' bl-shape-rings' ) . '">';
	if ( $o['image'] ) {
		$h .= '<div class="bl-cta__media">' . beleon_img( $o['image'], 'beleon-wide', array( 'alt' => '', 'sizes' => '100vw' ) ) . '</div>';
	}
	$h .= '<div class="bl-cta__in">' . beleon_eyebrow( $o['eyebrow'], 'bl-m-fade-up' );
	$h .= '<h2 class="bl-cta__title bl-m-mask-up bl-d-1">' . beleon_accent( $o['title'] ) . '</h2>';
	if ( $o['text'] ) {
		$h .= '<p class="bl-cta__text bl-m-fade-up bl-d-2">' . esc_html( $o['text'] ) . '</p>';
	}
	$light = in_array( $o['surface'], array( 'night', 'lapis' ), true ) || $o['image'];
	$h    .= '<div class="bl-actions bl-m-fade-up bl-d-3">' . beleon_button( $o['btn1'], $o['link1'], 'solid' ) . beleon_button( $o['btn2'], $o['link2'], $light ? 'ghost' : 'line' ) . '</div>';
	return $h . '</div></div>';
}

/**
 * Contact details from the Customizer.
 *
 * @param array $o show (phones, email, addresses, socials).
 * @return string
 */
function beleon_render_contact( $o = array() ) {
	$o  = wp_parse_args( $o, array( 'show' => array( 'phones', 'email', 'addresses', 'socials' ) ) );
	$h  = '<div class="bl-contact">';
	$ph = array_filter( array( beleon_mod( 'beleon_phone' ), beleon_mod( 'beleon_phone_2' ) ) );
	if ( in_array( 'phones', $o['show'], true ) && $ph ) {
		$h .= '<div class="bl-contact__item">' . beleon_icon( 'phone' ) . '<div><small>' . esc_html__( 'Call us', 'beleon-tours' ) . '</small>';
		foreach ( $ph as $p ) {
			$h .= '<a href="tel:' . esc_attr( preg_replace( '/[^\d+]/', '', $p ) ) . '">' . esc_html( $p ) . '</a>';
		}
		$h .= '</div></div>';
	}
	$mail = beleon_mod( 'beleon_email' );
	if ( in_array( 'email', $o['show'], true ) && $mail ) {
		$h .= '<div class="bl-contact__item">' . beleon_icon( 'mail' ) . '<div><small>' . esc_html__( 'Write to us', 'beleon-tours' ) . '</small><a href="mailto:' . esc_attr( antispambot( $mail ) ) . '">' . esc_html( antispambot( $mail ) ) . '</a></div></div>';
	}
	if ( in_array( 'addresses', $o['show'], true ) ) {
		foreach ( array_filter( array( beleon_mod( 'beleon_address' ), beleon_mod( 'beleon_address_2' ) ) ) as $addr ) {
			$lines = preg_split( '/\R/u', $addr );
			$h    .= '<div class="bl-contact__item">' . beleon_icon( 'pin' ) . '<div><small>' . esc_html( array_shift( $lines ) ) . '</small>' . esc_html( implode( ', ', $lines ) ) . '</div></div>';
		}
	}
	if ( in_array( 'socials', $o['show'], true ) ) {
		$h .= beleon_render_socials();
	}
	return $h . '</div>';
}

/**
 * Social icons.
 *
 * @return string
 */
function beleon_render_socials() {
	$s = beleon_socials();
	if ( ! $s ) {
		return '';
	}
	$h = '<ul class="bl-socials">';
	foreach ( $s as $net => $url ) {
		$h .= '<li><a href="' . esc_url( $url ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( ucfirst( $net ) ) . '">' . beleon_icon( $net ) . '</a></li>';
	}
	return $h . '</ul>';
}
