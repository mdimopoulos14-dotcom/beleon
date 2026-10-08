<?php
/**
 * Tour & destination archives (filters, ordering) and structured data.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'pre_get_posts',
	function ( WP_Query $q ) {
		if ( is_admin() || ! $q->is_main_query() ) {
			return;
		}
		if ( $q->is_post_type_archive( 'tours' ) || $q->is_tax( 'tour_region' ) ) {
			// The finder filters and sorts every tour on one page.
			$q->set( 'posts_per_page', 200 );
			$q->set( 'no_found_rows', true );
			$q->set(
				'orderby',
				array(
					'menu_order' => 'ASC',
					'date'       => 'DESC',
				)
			);
		}
		if ( $q->is_post_type_archive( 'destinations' ) ) {
			$q->set( 'posts_per_page', 48 );
			$q->set(
				'orderby',
				array(
					'menu_order' => 'ASC',
					'title'      => 'ASC',
				)
			);
		}
	}
);

/**
 * schema.org TouristTrip for tour pages (rich results / AI search).
 */
add_action(
	'wp_head',
	function () {
		if ( ! is_singular( 'tours' ) || ! apply_filters( 'beleon_tour_schema', true ) ) {
			return;
		}
		$id   = get_queried_object_id();
		$data = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'TouristTrip',
			'name'        => get_the_title( $id ),
			'description' => wp_strip_all_tags( get_the_excerpt( $id ) ),
			'url'         => get_permalink( $id ),
			'provider'    => array(
				'@type' => 'TravelAgency',
				'name'  => get_bloginfo( 'name' ),
				'url'   => home_url( '/' ),
			),
		);
		$img  = get_the_post_thumbnail_url( $id, 'beleon-wide' );
		if ( $img ) {
			$data['image'] = $img;
		}
		$price = preg_replace( '/[^\d]/', '', beleon_text( 'price', $id ) );
		$deps  = beleon_departures( $id );
		if ( $price ) {
			$offer = array(
				'@type'         => 'Offer',
				'price'         => $price,
				'priceCurrency' => 'EUR',
				'url'           => get_permalink( $id ),
				'availability'  => 'https://schema.org/InStock',
			);
			if ( $deps && $deps[0]['ts'] ) {
				$offer['availabilityStarts'] = wp_date( 'Y-m-d', $deps[0]['ts'] );
			}
			$data['offers'] = $offer;
		}
		$places = array();
		foreach ( beleon_tour_destination_ids( $id ) as $dest ) {
			$places[] = array(
				'@type' => 'Place',
				'name'  => get_the_title( $dest ),
			);
		}
		if ( $places ) {
			$data['itinerary'] = array(
				'@type'           => 'ItemList',
				'itemListElement' => $places,
			);
		}
		echo '<script type="application/ld+json">' . wp_json_encode( apply_filters( 'beleon_tour_schema_data', $data, $id ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
	}
);

/**
 * Basic meta description + Open Graph tags, only when no SEO plugin is active
 * (Yoast, Rank Math, SEOPress, AIOSEO, The SEO Framework take over otherwise).
 */
add_action(
	'wp_head',
	function () {
		$seo_plugin = defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'THE_SEO_FRAMEWORK_VERSION' );
		if ( $seo_plugin || ! apply_filters( 'beleon_meta_tags', true ) ) {
			return;
		}
		$desc  = '';
		$image = '';
		if ( is_singular() ) {
			$id   = get_queried_object_id();
			if ( 'destinations' === get_post_type( $id ) && beleon_text( 'tagline', $id ) ) {
				$desc = beleon_text( 'tagline', $id );
			} elseif ( has_excerpt( $id ) ) {
				$desc = get_the_excerpt( $id );
			} elseif ( ! is_front_page() && ! beleon_is_elementor_page( $id ) ) {
				// Elementor pages store flattened text; their summary comes from the excerpt or the site tagline.
				$desc = wp_trim_words( wp_strip_all_tags( strip_shortcodes( get_post_field( 'post_content', $id ) ) ), 28, '…' );
			}
			$image = get_the_post_thumbnail_url( $id, 'beleon-wide' );
		}
		if ( ! $desc && ( is_front_page() || is_home() ) ) {
			$desc = get_bloginfo( 'description' ) . '. ' . beleon_mod( 'beleon_footer_about' );
		}
		if ( ! $desc && is_post_type_archive( 'tours' ) ) {
			$desc = __( 'Choose a destination or a month — every departure is designed and accompanied by our team.', 'beleon-tours' );
		}
		if ( ! $desc ) {
			$desc = beleon_mod( 'beleon_footer_about' );
		}
		$desc = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $desc ) ) );
		if ( $desc ) {
			echo '<meta name="description" content="' . esc_attr( wp_html_excerpt( $desc, 160, '…' ) ) . '">' . "\n";
		}
		echo '<meta property="og:type" content="' . ( is_singular( array( 'tours', 'destinations', 'post' ) ) ? 'article' : 'website' ) . '">' . "\n";
		echo '<meta property="og:title" content="' . esc_attr( wp_get_document_title() ) . '">' . "\n";
		echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
		if ( $desc ) {
			echo '<meta property="og:description" content="' . esc_attr( wp_html_excerpt( $desc, 200, '…' ) ) . '">' . "\n";
		}
		if ( $image ) {
			echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
		}
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	},
	1
);
