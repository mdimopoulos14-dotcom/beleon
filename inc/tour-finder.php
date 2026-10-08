<?php
/**
 * Tour finder: search, region, destination, month, duration, departure city,
 * budget and sorting for the tours archive (and the Tours widget).
 *
 * Every card carries its facts as data attributes, so main.js filters and
 * sorts instantly without reloading. The same rules run in PHP for the
 * initial request, which keeps shared URLs (?region=caucasus&month=2026-10)
 * and visitors without JavaScript working.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lowercase, accent-free text for search ("Γεωργία" and "γεωργια" match).
 *
 * @param string $text Text.
 * @return string
 */
function beleon_search_fold( $text ) {
	$text = mb_strtolower( wp_strip_all_tags( (string) $text ), 'UTF-8' );
	$text = strtr(
		$text,
		array(
			'ά' => 'α', 'έ' => 'ε', 'ή' => 'η', 'ί' => 'ι', 'ϊ' => 'ι', 'ΐ' => 'ι', 'ό' => 'ο',
			'ύ' => 'υ', 'ϋ' => 'υ', 'ΰ' => 'υ', 'ώ' => 'ω', 'ς' => 'σ',
		)
	);
	return trim( preg_replace( '/\s+/u', ' ', remove_accents( $text ) ) );
}

/**
 * Departure cities a tour leaves from, as stable keys.
 *
 * @param int $id Tour ID.
 * @return string[] Subset of ath, skg.
 */
function beleon_tour_cities( $id ) {
	$text = beleon_search_fold( beleon_text( 'departure_city', $id ) );
	$out  = array();
	if ( preg_match( '/αθην|athen/u', $text ) ) {
		$out[] = 'ath';
	}
	if ( preg_match( '/θεσσαλον|thessalon/u', $text ) ) {
		$out[] = 'skg';
	}
	return $out;
}

/**
 * Departure city choices.
 *
 * @return array key => label.
 */
function beleon_city_choices() {
	return apply_filters(
		'beleon_city_choices',
		array(
			'ath' => __( 'Athens', 'beleon-tours' ),
			'skg' => __( 'Thessaloniki', 'beleon-tours' ),
		)
	);
}

/**
 * Duration choices: key => [ label, min days, max days ].
 *
 * @return array
 */
function beleon_duration_choices() {
	return array(
		'short'  => array( __( 'Up to 5 days', 'beleon-tours' ), 1, 5 ),
		'medium' => array( __( '6 to 9 days', 'beleon-tours' ), 6, 9 ),
		'long'   => array( __( '10 days or more', 'beleon-tours' ), 10, 999 ),
	);
}

/**
 * Budget choices: key => [ label, max price ].
 *
 * @return array
 */
function beleon_budget_choices() {
	$out = array();
	foreach ( apply_filters( 'beleon_budget_steps', array( 500, 1000, 1500, 2500, 4000 ) ) as $max ) {
		/* translators: %s: price, e.g. 1.000 € */
		$out[ (string) $max ] = array( sprintf( __( 'Up to %s', 'beleon-tours' ), beleon_format_price( (string) $max ) ), $max );
	}
	return $out;
}

/**
 * Sort choices.
 *
 * @return array key => label.
 */
function beleon_sort_choices() {
	return array(
		''           => __( 'Recommended', 'beleon-tours' ),
		'date'       => __( 'Next departure', 'beleon-tours' ),
		'price'      => __( 'Price: low to high', 'beleon-tours' ),
		'price-desc' => __( 'Price: high to low', 'beleon-tours' ),
		'days'       => __( 'Duration', 'beleon-tours' ),
	);
}

/**
 * Current filter values from the URL.
 *
 * @return array q, region, dest, month, days, from, price, sort.
 */
function beleon_finder_state() {
	// phpcs:disable WordPress.Security.NonceVerification
	$get = function ( $key ) {
		return isset( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : '';
	};
	$state = array(
		'q'      => mb_substr( $get( 'q' ), 0, 80 ),
		'region' => sanitize_title( $get( 'region' ) ),
		'dest'   => absint( $get( 'dest' ) ),
		'month'  => preg_match( '/^\d{4}-\d{2}$/', $get( 'month' ) ) ? $get( 'month' ) : '',
		'days'   => array_key_exists( $get( 'days' ), beleon_duration_choices() ) ? $get( 'days' ) : '',
		'from'   => array_key_exists( $get( 'from' ), beleon_city_choices() ) ? $get( 'from' ) : '',
		'price'  => array_key_exists( $get( 'price' ), beleon_budget_choices() ) ? $get( 'price' ) : '',
		'sort'   => array_key_exists( $get( 'sort' ), beleon_sort_choices() ) ? $get( 'sort' ) : '',
	);
	// phpcs:enable
	return $state;
}

/**
 * Everything the finder needs to know about one tour (cached per request).
 *
 * @param int $id Tour ID.
 * @return array
 */
function beleon_tour_facts( $id ) {
	static $cache = array();
	if ( isset( $cache[ $id ] ) ) {
		return $cache[ $id ];
	}
	$regions = get_the_terms( $id, 'tour_region' );
	$regions = is_array( $regions ) ? $regions : array();
	$dests   = beleon_tour_destination_ids( $id );
	$months  = array();
	foreach ( beleon_departures( $id ) as $dep ) {
		if ( $dep['ts'] ) {
			$months[] = wp_date( 'Y-m', $dep['ts'] );
		}
	}
	$next   = beleon_next_departure( $id );
	$search = array( get_the_title( $id ), beleon_text( 'duration', $id ), beleon_text( 'departure_city', $id ) );
	foreach ( $dests as $dest ) {
		$search[] = get_the_title( $dest );
	}
	foreach ( $regions as $term ) {
		$search[] = $term->name;
	}

	$cache[ $id ] = array(
		'regions' => wp_list_pluck( $regions, 'slug' ),
		'dests'   => $dests,
		'months'  => array_values( array_unique( $months ) ),
		'days'    => beleon_tour_days( $id ),
		'cities'  => beleon_tour_cities( $id ),
		'price'   => beleon_tour_price_value( $id ),
		'next'    => $next && $next['ts'] ? (int) $next['ts'] : 0,
		'search'  => beleon_search_fold( implode( ' ', $search ) ),
	);
	return $cache[ $id ];
}

/**
 * Does a tour pass the filters?
 *
 * @param int   $id    Tour ID.
 * @param array $state Filter state (beleon_finder_state()).
 * @return bool
 */
function beleon_tour_matches( $id, $state ) {
	$f = beleon_tour_facts( $id );
	if ( $state['region'] && ! in_array( $state['region'], $f['regions'], true ) ) {
		return false;
	}
	if ( $state['dest'] && ! in_array( (int) $state['dest'], $f['dests'], true ) ) {
		return false;
	}
	if ( $state['month'] && ! in_array( $state['month'], $f['months'], true ) ) {
		return false;
	}
	if ( $state['days'] ) {
		$range = beleon_duration_choices()[ $state['days'] ];
		if ( $f['days'] < $range[1] || $f['days'] > $range[2] ) {
			return false;
		}
	}
	if ( $state['from'] && ! in_array( $state['from'], $f['cities'], true ) ) {
		return false;
	}
	if ( $state['price'] && ( ! $f['price'] || $f['price'] > (int) $state['price'] ) ) {
		return false;
	}
	if ( '' !== $state['q'] ) {
		foreach ( explode( ' ', beleon_search_fold( $state['q'] ) ) as $word ) {
			if ( '' !== $word && false === strpos( $f['search'], $word ) ) {
				return false;
			}
		}
	}
	return true;
}

/**
 * Sort tour IDs (stable; tours without a value go last).
 *
 * @param int[]  $ids  Tour IDs in recommended order.
 * @param string $sort Sort key.
 * @return int[]
 */
function beleon_sort_tours( $ids, $sort ) {
	if ( '' === $sort ) {
		return $ids;
	}
	$keys = array();
	foreach ( $ids as $pos => $id ) {
		$f = beleon_tour_facts( $id );
		switch ( $sort ) {
			case 'date':
				$k = $f['next'] ? $f['next'] : PHP_INT_MAX;
				break;
			case 'price':
				$k = $f['price'] ? $f['price'] : PHP_INT_MAX;
				break;
			case 'price-desc':
				$k = -$f['price'];
				break;
			default:
				$k = $f['days'] ? $f['days'] : PHP_INT_MAX;
		}
		$keys[ $id ] = array( $k, $pos );
	}
	usort(
		$ids,
		function ( $a, $b ) use ( $keys ) {
			return $keys[ $a ] <=> $keys[ $b ];
		}
	);
	return $ids;
}

/**
 * <select> for the finder bar.
 *
 * @param string $name     Field name.
 * @param string $label    Accessible label.
 * @param array  $options  value => label ('' first = "any").
 * @param string $selected Selected value.
 * @return string
 */
function beleon_finder_select( $name, $label, $options, $selected ) {
	$id = 'bl-f-' . $name;
	$h  = '<div class="bl-finder__field"><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label><select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';
	foreach ( $options as $value => $text ) {
		$h .= '<option value="' . esc_attr( $value ) . '"' . selected( (string) $selected, (string) $value, false ) . '>' . esc_html( $text ) . '</option>';
	}
	return $h . '</select></div>';
}

/**
 * The finder: filter bar, live count and the tour grid.
 *
 * @param int[] $ids Tour IDs in recommended order.
 * @param array $o   action (form URL), heading (card heading tag), empty.
 * @return string
 */
function beleon_render_tour_finder( $ids, $o = array() ) {
	$o     = wp_parse_args(
		$o,
		array(
			'action'  => get_post_type_archive_link( 'tours' ),
			'heading' => 'h2',
			'empty'   => __( 'No tours match these filters. Change a filter or contact us for a tailor-made trip.', 'beleon-tours' ),
		)
	);
	$state = beleon_finder_state();
	$ids   = array_values( array_map( 'intval', (array) $ids ) );

	// Choices that actually have tours behind them.
	$regions = array();
	$dests   = array();
	$months  = array();
	foreach ( $ids as $id ) {
		$f = beleon_tour_facts( $id );
		foreach ( $f['regions'] as $slug ) {
			$regions[ $slug ] = true;
		}
		foreach ( $f['dests'] as $dest ) {
			$dests[ $dest ] = true;
		}
		foreach ( $f['months'] as $month ) {
			$months[ $month ] = true;
		}
	}
	$terms = $regions ? get_terms(
		array(
			'taxonomy'   => 'tour_region',
			'slug'       => array_keys( $regions ),
			'hide_empty' => false,
			'orderby'    => 'term_order',
		)
	) : array();
	$terms = is_wp_error( $terms ) ? array() : $terms;
	usort(
		$terms,
		function ( $a, $b ) {
			return ( (int) get_term_meta( $a->term_id, 'bl_order', true ) <=> (int) get_term_meta( $b->term_id, 'bl_order', true ) ) ?: strcmp( $a->name, $b->name );
		}
	);

	$dest_opts = array( '' => __( 'All destinations', 'beleon-tours' ) );
	if ( $dests ) {
		$posts = get_posts(
			array(
				'post_type'      => 'destinations',
				'post__in'       => array_keys( $dests ),
				'posts_per_page' => 100,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);
		foreach ( $posts as $p ) {
			$dest_opts[ $p->ID ] = $p->post_title;
		}
	}
	ksort( $months );
	$month_opts = array( '' => __( 'Any month', 'beleon-tours' ) );
	foreach ( array_keys( $months ) as $ym ) {
		$month_opts[ $ym ] = beleon_month_name( (int) substr( $ym, 5, 2 ) ) . ' ' . substr( $ym, 0, 4 );
	}
	$days_opts = array( '' => __( 'Any duration', 'beleon-tours' ) );
	foreach ( beleon_duration_choices() as $key => $c ) {
		$days_opts[ $key ] = $c[0];
	}
	$from_opts  = array( '' => __( 'Any city', 'beleon-tours' ) ) + beleon_city_choices();
	$price_opts = array( '' => __( 'Any budget', 'beleon-tours' ) );
	foreach ( beleon_budget_choices() as $key => $c ) {
		$price_opts[ $key ] = $c[0];
	}

	$action = $o['action'] ? $o['action'] : home_url( '/' );
	$active = array_filter( array_diff_key( $state, array( 'sort' => 1 ) ) );

	$h  = '<div class="bl-finder" data-bl-finder>';
	$h .= '<form class="bl-finder__bar" method="get" action="' . esc_url( $action ) . '" role="search">';
	$h .= '<div class="bl-finder__field bl-finder__field--q"><label for="bl-f-q">' . esc_html__( 'Search', 'beleon-tours' ) . '</label>'
		. beleon_icon( 'search' ) . '<input id="bl-f-q" type="search" name="q" value="' . esc_attr( $state['q'] ) . '" placeholder="' . esc_attr__( 'Country, city or experience…', 'beleon-tours' ) . '" autocomplete="off"></div>';
	if ( count( $dest_opts ) > 2 ) {
		$h .= beleon_finder_select( 'dest', __( 'Destination', 'beleon-tours' ), $dest_opts, $state['dest'] ? $state['dest'] : '' );
	}
	if ( count( $month_opts ) > 1 ) {
		$h .= beleon_finder_select( 'month', __( 'Month', 'beleon-tours' ), $month_opts, $state['month'] );
	}
	$h .= beleon_finder_select( 'days', __( 'Duration', 'beleon-tours' ), $days_opts, $state['days'] );
	$h .= beleon_finder_select( 'from', __( 'Departs from', 'beleon-tours' ), $from_opts, $state['from'] );
	$h .= beleon_finder_select( 'price', __( 'Budget', 'beleon-tours' ), $price_opts, $state['price'] );
	if ( $state['region'] ) {
		$h .= '<input type="hidden" name="region" value="' . esc_attr( $state['region'] ) . '">';
	}
	$h .= '<button class="bl-btn bl-btn--solid bl-btn--sm bl-finder__go" type="submit"><span>' . esc_html__( 'Show tours', 'beleon-tours' ) . '</span></button>';
	$h .= '</form>';

	if ( $terms ) {
		$h .= '<nav class="bl-chips bl-finder__regions" aria-label="' . esc_attr__( 'Regions', 'beleon-tours' ) . '">';
		$base = remove_query_arg( array( 'region', 'paged' ) );
		$h   .= '<a class="bl-chip' . ( $state['region'] ? '' : ' is-active' ) . '" href="' . esc_url( $base ) . '" data-region="">' . esc_html__( 'All', 'beleon-tours' ) . '</a>';
		foreach ( $terms as $term ) {
			$h .= '<a class="bl-chip' . ( $state['region'] === $term->slug ? ' is-active' : '' ) . '" href="' . esc_url( add_query_arg( 'region', $term->slug, $base ) ) . '" data-region="' . esc_attr( $term->slug ) . '">' . esc_html( $term->name ) . '</a>';
		}
		$h .= '</nav>';
	}

	// Cards, sorted and pre-filtered on the server.
	$sorted = beleon_sort_tours( $ids, $state['sort'] );
	$shown  = 0;
	$cards  = '';
	foreach ( $sorted as $id ) {
		$f     = beleon_tour_facts( $id );
		$match = beleon_tour_matches( $id, $state );
		$shown += $match ? 1 : 0;
		$cards .= beleon_render_tour_card(
			$id,
			array(
				'heading' => $o['heading'],
				'attrs'   => array(
					'data-order'   => array_search( $id, $ids, true ),
					'data-region'  => implode( ' ', $f['regions'] ),
					'data-dest'    => implode( ' ', $f['dests'] ),
					'data-months'  => implode( ' ', $f['months'] ),
					'data-days'    => $f['days'],
					'data-from'    => implode( ' ', $f['cities'] ),
					'data-price'   => $f['price'],
					'data-next'    => $f['next'],
					'data-search'  => $f['search'],
					'hidden'       => $match ? null : 'hidden',
				),
			)
		);
	}

	/* translators: %d: number of tours */
	$one  = _n( '%d tour', '%d tours', 1, 'beleon-tours' );
	/* translators: %d: number of tours */
	$many = _n( '%d tour', '%d tours', 2, 'beleon-tours' );
	$h   .= '<div class="bl-finder__meta">';
	$h   .= '<p class="bl-finder__count" aria-live="polite" data-one="' . esc_attr( $one ) . '" data-many="' . esc_attr( $many ) . '">' . esc_html( sprintf( 1 === $shown ? $one : $many, $shown ) ) . '</p>';
	$h   .= '<a class="bl-finder__reset" href="' . esc_url( $action ) . '"' . ( $active ? '' : ' hidden' ) . '>' . esc_html__( 'Clear filters', 'beleon-tours' ) . '</a>';
	$h   .= beleon_finder_select( 'sort', __( 'Sort by', 'beleon-tours' ), beleon_sort_choices(), $state['sort'] );
	$h   .= '</div>';
	$h   .= '<div class="bl-grid bl-tours bl-finder__grid">' . $cards . '</div>';
	$h   .= '<div class="bl-finder__empty"' . ( $shown ? ' hidden' : '' ) . '><p>' . esc_html( $o['empty'] ) . '</p>' . beleon_button( __( 'Plan a tailor-made trip', 'beleon-tours' ), beleon_booking_link(), 'line' ) . '</div>';
	return $h . '</div>';
}

/**
 * Full month name from the theme's translations.
 *
 * @param int $n Month 1-12.
 * @return string
 */
function beleon_month_name( $n ) {
	$names = array(
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
	return isset( $names[ $n ] ) ? $names[ $n ] : '';
}
