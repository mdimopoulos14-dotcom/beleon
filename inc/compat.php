<?php
/**
 * Plugin integrations: Polylang (Greek / English) and Yoast SEO.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* --------------------------------------------------------------------------
 * Polylang
 * ------------------------------------------------------------------------ */

/**
 * Current Polylang language slug ('' when Polylang is not active).
 *
 * @return string
 */
function beleon_pll_lang() {
	return function_exists( 'pll_current_language' ) ? (string) pll_current_language( 'slug' ) : '';
}

/**
 * Theme texts that site owners type in the Customizer and that need a
 * translation per language (Languages > Translations).
 *
 * @return string[] Theme mod keys.
 */
function beleon_translatable_mods() {
	return array(
		'beleon_address',
		'beleon_address_2',
		'beleon_license',
		'beleon_footer_about',
		'beleon_header_cta_text',
		'beleon_header_cta_url',
		'beleon_booking_url',
		'beleon_tours_archive_url',
		'beleon_tours_title',
		'beleon_tours_intro',
	);
}

add_action(
	'init',
	function () {
		if ( ! function_exists( 'pll_register_string' ) ) {
			return;
		}
		foreach ( beleon_translatable_mods() as $key ) {
			$value = get_theme_mod( $key, '' );
			if ( is_string( $value ) && '' !== trim( $value ) ) {
				pll_register_string( $key, $value, 'Beleon Tours', false !== strpos( $value, "\n" ) );
			}
		}
	},
	20
);

/**
 * Translate Customizer texts for the current language.
 */
add_filter(
	'beleon_mod',
	function ( $value, $key ) {
		if ( function_exists( 'pll__' ) && '' !== $value && in_array( $key, beleon_translatable_mods(), true ) && '' !== get_theme_mod( $key, '' ) ) {
			return pll__( $value );
		}
		return $value;
	},
	10,
	2
);

/**
 * Link destinations of the same language (tours keep their links when translated).
 */
add_filter(
	'beleon_tour_destination_ids',
	function ( $ids ) {
		if ( ! function_exists( 'pll_get_post' ) || ! beleon_pll_lang() ) {
			return $ids;
		}
		$out = array();
		foreach ( $ids as $id ) {
			$tr    = pll_get_post( $id );
			$out[] = $tr ? (int) $tr : (int) $id;
		}
		return array_values( array_unique( $out ) );
	}
);

/**
 * Language switcher (EL · EN) for the header.
 *
 * @param string $class Extra class.
 * @return string
 */
function beleon_language_switcher( $class = '' ) {
	if ( ! function_exists( 'pll_the_languages' ) ) {
		return '';
	}
	$langs = pll_the_languages(
		array(
			'raw'           => 1,
			'hide_if_empty' => 0,
		)
	);
	if ( ! is_array( $langs ) || count( $langs ) < 2 ) {
		return '';
	}
	$h = '<ul class="bl-lang ' . esc_attr( $class ) . '" aria-label="' . esc_attr__( 'Language', 'beleon-tours' ) . '">';
	foreach ( $langs as $l ) {
		$current = ! empty( $l['current_lang'] );
		$h      .= '<li><a href="' . esc_url( $l['url'] ) . '" hreflang="' . esc_attr( $l['locale'] ? str_replace( '_', '-', $l['locale'] ) : $l['slug'] ) . '" lang="' . esc_attr( $l['slug'] ) . '"' . ( $current ? ' aria-current="true" class="is-current"' : '' ) . '>'
			. '<abbr title="' . esc_attr( $l['name'] ) . '">' . esc_html( strtoupper( $l['slug'] ) ) . '</abbr></a></li>';
	}
	return $h . '</ul>';
}

/**
 * Polylang settings the theme relies on: translate tours, destinations and
 * regions, and copy the tour facts to new translations.
 */
add_filter(
	'pll_get_post_types',
	function ( $types, $is_settings ) {
		if ( ! $is_settings ) {
			$types['tours']        = 'tours';
			$types['destinations'] = 'destinations';
		}
		return $types;
	},
	10,
	2
);
add_filter(
	'pll_get_taxonomies',
	function ( $taxonomies, $is_settings ) {
		if ( ! $is_settings ) {
			$taxonomies['tour_region'] = 'tour_region';
		}
		return $taxonomies;
	},
	10,
	2
);
add_filter(
	'pll_copy_post_metas',
	function ( $metas ) {
		foreach ( array( 'duration', 'price', 'departures', 'departure_city', 'gallery', 'dest_gallery', 'destinations' ) as $field ) {
			$metas[] = beleon_meta_key( $field );
		}
		return array_values( array_unique( $metas ) );
	}
);

/* --------------------------------------------------------------------------
 * Yoast SEO
 * ------------------------------------------------------------------------ */

/**
 * With Yoast active, its breadcrumbs (part of its schema graph) replace the
 * theme's, so the page has a single BreadcrumbList.
 */
add_filter(
	'beleon_breadcrumbs_html',
	function ( $html ) {
		if ( ! function_exists( 'yoast_breadcrumb' ) ) {
			return $html;
		}
		$crumbs = yoast_breadcrumb( '<nav class="bl-crumbs bl-crumbs--yoast bl-m-fade" aria-label="' . esc_attr__( 'Breadcrumb', 'beleon-tours' ) . '">', '</nav>', false );
		return $crumbs ? $crumbs : $html;
	}
);

/**
 * Yoast: tours and destinations get the matching schema.org page type, and
 * the tour price/dates are added to the tour's WebPage as an Offer-based TouristTrip.
 */
add_filter(
	'wpseo_schema_webpage',
	function ( $data ) {
		if ( is_singular( 'tours' ) ) {
			$data['@type'] = array( 'WebPage', 'ItemPage' );
		}
		return $data;
	}
);
