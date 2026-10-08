<?php
/**
 * Tour and destination data: one place that knows where every value lives.
 *
 * Every theme template and Elementor widget reads tour data through these
 * functions. Each logical field (price, duration, departures...) maps to a
 * post meta key, editable at Beleon > Fields. That lets the theme read the
 * existing JetEngine/ACF fields on the live site without touching them, and
 * fall back to its own meta boxes (keys starting with "bl_") elsewhere.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Logical fields: key => [ label, post type, type, help ].
 *
 * @return array
 */
function beleon_field_definitions() {
	return apply_filters(
		'beleon_field_definitions',
		array(
			'duration'       => array( __( 'Duration', 'beleon-tours' ), 'tours', 'text', __( 'e.g. "8 days / 7 nights"', 'beleon-tours' ) ),
			'price'          => array( __( 'Price from', 'beleon-tours' ), 'tours', 'text', __( 'Number (1290) or text', 'beleon-tours' ) ),
			'price_label'    => array( __( 'Price label', 'beleon-tours' ), 'tours', 'text', __( 'Shown before the price instead of "from", e.g. "Final price"', 'beleon-tours' ) ),
			'price_note'     => array( __( 'Price note', 'beleon-tours' ), 'tours', 'text', __( 'e.g. "per person in a double room"', 'beleon-tours' ) ),
			'departures'     => array( __( 'Departure dates', 'beleon-tours' ), 'tours', 'lines', __( 'One per line: 2026-05-12 or 12/05/2026, optionally "| Last seats"', 'beleon-tours' ) ),
			'departure_city' => array( __( 'Departs from', 'beleon-tours' ), 'tours', 'text', __( 'e.g. "Athens & Thessaloniki"', 'beleon-tours' ) ),
			'group_size'     => array( __( 'Group size', 'beleon-tours' ), 'tours', 'text', __( 'e.g. "12–18 travellers"', 'beleon-tours' ) ),
			'badge'          => array( __( 'Badge', 'beleon-tours' ), 'tours', 'text', __( 'Short label on the card, e.g. "New" or "Last seats"', 'beleon-tours' ) ),
			'destinations'   => array( __( 'Destinations', 'beleon-tours' ), 'tours', 'relation', __( 'Linked destination posts', 'beleon-tours' ) ),
			'highlights'     => array( __( 'Highlights', 'beleon-tours' ), 'tours', 'lines', __( 'One per line', 'beleon-tours' ) ),
			'itinerary'      => array( __( 'Itinerary', 'beleon-tours' ), 'tours', 'itinerary', __( 'One day per block: first line is the title, following lines the description. Separate days with an empty line.', 'beleon-tours' ) ),
			'includes'       => array( __( 'Included', 'beleon-tours' ), 'tours', 'lines', __( 'One per line', 'beleon-tours' ) ),
			'excludes'       => array( __( 'Not included', 'beleon-tours' ), 'tours', 'lines', __( 'One per line', 'beleon-tours' ) ),
			'gallery'        => array( __( 'Gallery', 'beleon-tours' ), 'tours', 'gallery', __( 'Images', 'beleon-tours' ) ),
			'region'         => array( __( 'Region', 'beleon-tours' ), 'destinations', 'text', __( 'e.g. "Caucasus" or "Central Asia"', 'beleon-tours' ) ),
			'tagline'        => array( __( 'Tagline', 'beleon-tours' ), 'destinations', 'text', __( 'One short sentence', 'beleon-tours' ) ),
			'best_season'    => array( __( 'Best season', 'beleon-tours' ), 'destinations', 'text', __( 'e.g. "May – October"', 'beleon-tours' ) ),
			'dest_gallery'   => array( __( 'Gallery', 'beleon-tours' ), 'destinations', 'gallery', __( 'Images', 'beleon-tours' ) ),
		)
	);
}

/**
 * Meta key for a logical field (from Beleon > Fields, default "bl_<field>").
 *
 * @param string $field Logical field.
 * @return string
 */
function beleon_meta_key( $field ) {
	static $map = null;
	if ( null === $map ) {
		$map = (array) get_option( 'beleon_field_map', array() );
	}
	$key = ! empty( $map[ $field ] ) ? $map[ $field ] : 'bl_' . $field;
	return apply_filters( 'beleon_meta_key', $key, $field );
}

/**
 * Are the theme's own meta boxes needed? (Off when another plugin owns the fields.)
 *
 * @return bool
 */
function beleon_uses_own_fields() {
	$mode = get_option( 'beleon_fields_mode', 'auto' );
	if ( 'theme' === $mode ) {
		return true;
	}
	if ( 'external' === $mode ) {
		return false;
	}
	// Auto: if any mapping points away from the theme's own keys, a plugin owns the data.
	foreach ( (array) get_option( 'beleon_field_map', array() ) as $key ) {
		if ( $key && 0 !== strpos( $key, 'bl_' ) ) {
			return false;
		}
	}
	return true;
}

/**
 * Raw value of a logical field.
 *
 * @param string $field   Logical field.
 * @param int    $post_id Post ID (defaults to current post).
 * @return mixed
 */
function beleon_field( $field, $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	if ( ! $post_id ) {
		return '';
	}
	$value = get_post_meta( $post_id, beleon_meta_key( $field ), true );
	return apply_filters( 'beleon_field_value', $value, $field, $post_id );
}

/**
 * Field as a trimmed string ('' when empty or not scalar).
 *
 * @param string $field   Logical field.
 * @param int    $post_id Post ID.
 * @return string
 */
function beleon_text( $field, $post_id = 0 ) {
	$value = beleon_field( $field, $post_id );
	return is_scalar( $value ) ? trim( (string) $value ) : '';
}

/**
 * Field as a list of strings. Accepts newline text, arrays, or repeater rows.
 *
 * @param string $field   Logical field.
 * @param int    $post_id Post ID.
 * @return string[]
 */
function beleon_lines( $field, $post_id = 0 ) {
	$value = beleon_field( $field, $post_id );
	$out   = array();
	if ( is_array( $value ) ) {
		foreach ( $value as $row ) {
			if ( is_array( $row ) ) {
				$row = implode( ' — ', array_filter( array_map( 'strval', array_filter( $row, 'is_scalar' ) ) ) );
			}
			$out[] = (string) $row;
		}
	} else {
		$out = preg_split( '/\r\n|\r|\n/', (string) $value );
	}
	return array_values( array_filter( array_map( 'trim', array_map( 'wp_strip_all_tags', $out ) ), 'strlen' ) );
}

/**
 * Parse a date from the formats people (and JetEngine) actually store.
 *
 * @param mixed $raw Raw date.
 * @return int|null Timestamp (midnight, site timezone) or null.
 */
function beleon_parse_date( $raw ) {
	if ( is_numeric( $raw ) && (int) $raw > 100000000 ) {
		return (int) $raw;
	}
	$raw = trim( (string) $raw );
	if ( '' === $raw ) {
		return null;
	}
	$tz = wp_timezone();
	foreach ( array( '!Y-m-d', '!d/m/Y', '!d.m.Y', '!d-m-Y', '!j/n/Y', '!Y/m/d' ) as $format ) {
		$date = DateTimeImmutable::createFromFormat( $format, $raw, $tz );
		if ( $date && $date->format( ltrim( $format, '!' ) ) === $raw ) {
			return $date->getTimestamp();
		}
	}
	$ts = strtotime( $raw );
	return $ts ? $ts : null;
}

/**
 * "12 May 2026" with month names from the theme's own translations,
 * so dates follow the front-end language setting.
 *
 * @param int  $ts        Timestamp.
 * @param bool $with_year Include the year.
 * @return string
 */
function beleon_date_label( $ts, $with_year = true ) {
	$months = array(
		1  => _x( 'Jan', 'month', 'beleon-tours' ),
		2  => _x( 'Feb', 'month', 'beleon-tours' ),
		3  => _x( 'Mar', 'month', 'beleon-tours' ),
		4  => _x( 'Apr', 'month', 'beleon-tours' ),
		5  => _x( 'May', 'month', 'beleon-tours' ),
		6  => _x( 'Jun', 'month', 'beleon-tours' ),
		7  => _x( 'Jul', 'month', 'beleon-tours' ),
		8  => _x( 'Aug', 'month', 'beleon-tours' ),
		9  => _x( 'Sep', 'month', 'beleon-tours' ),
		10 => _x( 'Oct', 'month', 'beleon-tours' ),
		11 => _x( 'Nov', 'month', 'beleon-tours' ),
		12 => _x( 'Dec', 'month', 'beleon-tours' ),
	);
	$label = wp_date( 'j', $ts ) . ' ' . $months[ (int) wp_date( 'n', $ts ) ];
	return $with_year ? $label . ' ' . wp_date( 'Y', $ts ) : $label;
}

/**
 * Upcoming departures, soonest first.
 *
 * @param int  $post_id  Tour ID.
 * @param bool $upcoming Only future dates.
 * @return array[] Each: [ 'ts' => int|null, 'label' => string, 'note' => string ].
 */
function beleon_departures( $post_id = 0, $upcoming = true ) {
	$raw   = beleon_field( 'departures', $post_id );
	$items = array();

	$rows = is_array( $raw ) ? $raw : preg_split( '/\r\n|\r|\n|;/', (string) $raw );
	foreach ( $rows as $row ) {
		$note = '';
		if ( is_array( $row ) ) {
			$values = array_values( array_filter( $row, 'is_scalar' ) );
			$date   = isset( $values[0] ) ? $values[0] : '';
			$note   = isset( $values[1] ) ? (string) $values[1] : '';
		} else {
			$parts = array_map( 'trim', explode( '|', (string) $row, 2 ) );
			$date  = $parts[0];
			$note  = isset( $parts[1] ) ? $parts[1] : '';
		}
		if ( '' === trim( (string) $date ) ) {
			continue;
		}
		$ts      = beleon_parse_date( $date );
		$items[] = array(
			'ts'    => $ts,
			'label' => $ts ? beleon_date_label( $ts ) : (string) $date,
			'note'  => $note,
		);
	}

	if ( $upcoming ) {
		$today = strtotime( 'today', current_time( 'timestamp' ) ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp
		$items = array_filter(
			$items,
			function ( $d ) use ( $today ) {
				return null === $d['ts'] || $d['ts'] >= $today;
			}
		);
	}
	usort(
		$items,
		function ( $a, $b ) {
			return ( $a['ts'] ? $a['ts'] : PHP_INT_MAX ) <=> ( $b['ts'] ? $b['ts'] : PHP_INT_MAX );
		}
	);
	return array_values( $items );
}

/**
 * Next departure, or null.
 *
 * @param int $post_id Tour ID.
 * @return array|null
 */
function beleon_next_departure( $post_id = 0 ) {
	$all = beleon_departures( $post_id );
	return $all ? $all[0] : null;
}

/**
 * Itinerary days: [ [ 'title' => string, 'text' => string ], ... ].
 *
 * @param int $post_id Tour ID.
 * @return array[]
 */
function beleon_itinerary( $post_id = 0 ) {
	$raw  = beleon_field( 'itinerary', $post_id );
	$days = array();

	if ( is_array( $raw ) ) {
		foreach ( $raw as $row ) {
			if ( ! is_array( $row ) ) {
				$row = array( 'title' => (string) $row );
			}
			$title = '';
			$text  = '';
			foreach ( $row as $k => $v ) {
				if ( ! is_scalar( $v ) || '' === trim( (string) $v ) ) {
					continue;
				}
				if ( '' === $title && preg_match( '/title|name|day|heading|τιτλ/i', (string) $k ) ) {
					$title = (string) $v;
				} elseif ( '' === $text && preg_match( '/desc|text|content|body|περιγ/i', (string) $k ) ) {
					$text = (string) $v;
				}
			}
			if ( '' === $title && '' === $text ) {
				$scalars = array_values( array_filter( $row, 'is_scalar' ) );
				$title   = isset( $scalars[0] ) ? (string) $scalars[0] : '';
				$text    = isset( $scalars[1] ) ? (string) $scalars[1] : '';
			}
			if ( '' !== $title || '' !== $text ) {
				$days[] = array( 'title' => $title, 'text' => $text );
			}
		}
	} else {
		$blocks = preg_split( '/\R\s*\R/u', trim( (string) $raw ) );
		foreach ( $blocks as $block ) {
			$lines = preg_split( '/\R/u', trim( $block ) );
			if ( ! $lines || '' === trim( $lines[0] ) ) {
				continue;
			}
			$days[] = array(
				'title' => trim( array_shift( $lines ) ),
				'text'  => trim( implode( "\n", $lines ) ),
			);
		}
	}
	return apply_filters( 'beleon_itinerary', $days, $post_id );
}

/**
 * Attachment IDs from a gallery field (CSV, array of IDs/URLs, or ACF-style arrays).
 *
 * @param string $field   Logical field.
 * @param int    $post_id Post ID.
 * @return int[]
 */
function beleon_gallery( $field = 'gallery', $post_id = 0 ) {
	$raw = beleon_field( $field, $post_id );
	if ( is_string( $raw ) ) {
		$raw = array_filter( array_map( 'trim', explode( ',', $raw ) ) );
	}
	$ids = array();
	foreach ( (array) $raw as $item ) {
		if ( is_array( $item ) ) {
			$item = isset( $item['id'] ) ? $item['id'] : ( isset( $item['ID'] ) ? $item['ID'] : ( isset( $item['url'] ) ? $item['url'] : '' ) );
		}
		if ( is_numeric( $item ) ) {
			$ids[] = (int) $item;
		} elseif ( is_string( $item ) && '' !== $item ) {
			$id = attachment_url_to_postid( $item );
			if ( $id ) {
				$ids[] = $id;
			}
		}
	}
	return array_values( array_unique( array_filter( $ids ) ) );
}

/**
 * Destination IDs linked to a tour.
 *
 * @param int $post_id Tour ID.
 * @return int[]
 */
function beleon_tour_destination_ids( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$raw     = beleon_field( 'destinations', $post_id );
	if ( is_string( $raw ) ) {
		$raw = maybe_unserialize( $raw );
	}
	if ( is_string( $raw ) || is_numeric( $raw ) ) {
		$raw = explode( ',', (string) $raw );
	}
	$ids = array();
	foreach ( (array) $raw as $item ) {
		if ( is_array( $item ) ) {
			$item = isset( $item['ID'] ) ? $item['ID'] : ( isset( $item['id'] ) ? $item['id'] : 0 );
		} elseif ( is_object( $item ) && isset( $item->ID ) ) {
			$item = $item->ID;
		}
		$ids[] = (int) $item;
	}
	$ids = array_values( array_unique( array_filter( $ids ) ) );
	return array_map( 'intval', (array) apply_filters( 'beleon_tour_destination_ids', $ids, $post_id ) );
}

/**
 * Tour IDs linked to a destination. Cached per request and in the object cache.
 *
 * @param int $destination_id Destination ID.
 * @return int[]
 */
function beleon_destination_tour_ids( $destination_id ) {
	$cache_key = 'index-' . ( function_exists( 'beleon_pll_lang' ) ? beleon_pll_lang() : '' );
	$index     = wp_cache_get( $cache_key, 'beleon_tours' );
	if ( false === $index ) {
		$index = array();
		$tours = get_posts(
			array(
				'post_type'      => 'tours',
				'post_status'    => 'publish',
				'posts_per_page' => 500,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
		foreach ( $tours as $tour_id ) {
			foreach ( beleon_tour_destination_ids( $tour_id ) as $dest ) {
				$index[ $dest ][] = (int) $tour_id;
			}
		}
		wp_cache_set( $cache_key, $index, 'beleon_tours', HOUR_IN_SECONDS );
	}
	return isset( $index[ $destination_id ] ) ? $index[ $destination_id ] : array();
}

add_action(
	'save_post',
	function () {
		$langs = function_exists( 'pll_languages_list' ) ? (array) pll_languages_list() : array();
		foreach ( array_merge( array( '' ), $langs ) as $lang ) {
			wp_cache_delete( 'index-' . $lang, 'beleon_tours' );
		}
	}
);

/**
 * First linked destination title (used as the card eyebrow).
 *
 * @param int $post_id Tour ID.
 * @return string
 */
function beleon_tour_place( $post_id = 0 ) {
	foreach ( beleon_tour_destination_ids( $post_id ) as $id ) {
		$title = get_the_title( $id );
		if ( $title ) {
			return $title;
		}
	}
	return '';
}

/**
 * Label shown before a tour's price ("from" unless the tour sets its own).
 *
 * @param int    $post_id Tour ID.
 * @param string $default Fallback label.
 * @return string
 */
function beleon_price_label( $post_id = 0, $default = '' ) {
	$label = beleon_text( 'price_label', $post_id );
	return '' !== $label ? $label : ( '' !== $default ? $default : __( 'from', 'beleon-tours' ) );
}

/**
 * Whole days of a tour, read from the start of its duration ("8 days / 7 nights" -> 8).
 *
 * @param int $post_id Tour ID.
 * @return int
 */
function beleon_tour_days( $post_id = 0 ) {
	return preg_match( '/\d+/', beleon_text( 'duration', $post_id ), $m ) ? (int) $m[0] : 0;
}

/**
 * Price as a number for sorting and filtering (0 when unknown).
 *
 * @param int $post_id Tour ID.
 * @return int
 */
function beleon_tour_price_value( $post_id = 0 ) {
	$digits = preg_replace( '/[^\d]/', '', preg_replace( '/[.,]\d{1,2}$/', '', beleon_text( 'price', $post_id ) ) );
	return '' === $digits ? 0 : (int) $digits;
}
