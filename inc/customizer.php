<?php
/**
 * Customizer: brand, contact details, header and booking settings.
 * Everything visual inside pages is edited in Elementor; this holds the
 * site-wide facts that the header, footer and tour pages reuse.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default values for theme mods.
 *
 * @return array
 */
function beleon_mod_defaults() {
	return array(
		'beleon_front_lang'         => 'el',
		'beleon_phone'              => '',
		'beleon_phone_2'            => '',
		'beleon_email'              => '',
		'beleon_address'            => '',
		'beleon_address_2'          => '',
		'beleon_whatsapp'           => '',
		'beleon_facebook'           => '',
		'beleon_instagram'          => '',
		'beleon_youtube'            => '',
		'beleon_tiktok'             => '',
		'beleon_license'            => '',
		'beleon_header_cta_text'    => __( 'Plan your trip', 'beleon-tours' ),
		'beleon_header_cta_url'     => '/contact/',
		'beleon_header_transparent' => 'front',
		'beleon_footer_about'       => __( 'Small-group journeys to the Caucasus, Central Asia and the Himalaya, designed by people who have travelled every route.', 'beleon-tours' ),
		'beleon_booking_url'        => '/contact/',
		'beleon_currency'           => '€',
		'beleon_tours_archive_url'  => '',
	);
}

/**
 * Theme mod with the defaults above.
 *
 * @param string $key Mod name.
 * @return string
 */
function beleon_mod( $key ) {
	$defaults = beleon_mod_defaults();
	return (string) get_theme_mod( $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
}

add_action(
	'customize_register',
	function ( WP_Customize_Manager $wp_customize ) {
		$wp_customize->add_panel(
			'beleon',
			array(
				'title'    => __( 'Beleon', 'beleon-tours' ),
				'priority' => 25,
			)
		);

		$sections = array(
			'beleon_brand'   => __( 'Brand & contact', 'beleon-tours' ),
			'beleon_social'  => __( 'Social profiles', 'beleon-tours' ),
			'beleon_header'  => __( 'Header', 'beleon-tours' ),
			'beleon_booking' => __( 'Tours & booking', 'beleon-tours' ),
		);
		foreach ( $sections as $id => $title ) {
			$wp_customize->add_section( $id, array( 'title' => $title, 'panel' => 'beleon' ) );
		}

		$defaults = beleon_mod_defaults();
		$fields   = array(
			array( 'beleon_front_lang', 'beleon_brand', 'select', __( 'Front-end language of theme texts', 'beleon-tours' ), array( 'el' => 'Ελληνικά', 'auto' => __( 'Same as site language', 'beleon-tours' ) ) ),
			array( 'beleon_phone', 'beleon_brand', 'text', __( 'Phone', 'beleon-tours' ) ),
			array( 'beleon_phone_2', 'beleon_brand', 'text', __( 'Second phone', 'beleon-tours' ) ),
			array( 'beleon_email', 'beleon_brand', 'email', __( 'Email', 'beleon-tours' ) ),
			array( 'beleon_address', 'beleon_brand', 'textarea', __( 'Office address', 'beleon-tours' ) ),
			array( 'beleon_address_2', 'beleon_brand', 'textarea', __( 'Second office address', 'beleon-tours' ) ),
			array( 'beleon_license', 'beleon_brand', 'text', __( 'Licence / registry number (shown in footer)', 'beleon-tours' ) ),
			array( 'beleon_footer_about', 'beleon_brand', 'textarea', __( 'Footer intro text', 'beleon-tours' ) ),
			array( 'beleon_whatsapp', 'beleon_social', 'text', __( 'WhatsApp number (international, digits only)', 'beleon-tours' ) ),
			array( 'beleon_facebook', 'beleon_social', 'url', 'Facebook URL' ),
			array( 'beleon_instagram', 'beleon_social', 'url', 'Instagram URL' ),
			array( 'beleon_youtube', 'beleon_social', 'url', 'YouTube URL' ),
			array( 'beleon_tiktok', 'beleon_social', 'url', 'TikTok URL' ),
			array( 'beleon_header_cta_text', 'beleon_header', 'text', __( 'Header button text (empty hides it)', 'beleon-tours' ) ),
			array( 'beleon_header_cta_url', 'beleon_header', 'text', __( 'Header button link', 'beleon-tours' ) ),
			array(
				'beleon_header_transparent',
				'beleon_header',
				'select',
				__( 'Transparent header over the hero', 'beleon-tours' ),
				array(
					'front'  => __( 'Home, tours and destinations', 'beleon-tours' ),
					'always' => __( 'All pages', 'beleon-tours' ),
					'never'  => __( 'Never (always solid)', 'beleon-tours' ),
				),
			),
			array( 'beleon_booking_url', 'beleon_booking', 'text', __( 'Enquiry / booking page link (the tour name is added as ?tour=)', 'beleon-tours' ) ),
			array( 'beleon_currency', 'beleon_booking', 'text', __( 'Currency symbol', 'beleon-tours' ) ),
			array( 'beleon_tours_archive_url', 'beleon_booking', 'text', __( '"All tours" page link (empty uses the tours archive)', 'beleon-tours' ) ),
		);

		foreach ( $fields as $field ) {
			list( $id, $section, $type, $label ) = $field;
			$sanitize = 'sanitize_text_field';
			if ( 'textarea' === $type ) {
				$sanitize = 'sanitize_textarea_field';
			} elseif ( 'url' === $type ) {
				$sanitize = 'esc_url_raw';
			} elseif ( 'email' === $type ) {
				$sanitize = 'sanitize_email';
			}
			$wp_customize->add_setting(
				$id,
				array(
					'default'           => isset( $defaults[ $id ] ) ? $defaults[ $id ] : '',
					'sanitize_callback' => $sanitize,
					'transport'         => 'refresh',
				)
			);
			$control = array(
				'label'   => $label,
				'section' => $section,
				'type'    => $type,
			);
			if ( isset( $field[4] ) ) {
				$control['choices'] = $field[4];
			}
			$wp_customize->add_control( $id, $control );
		}
	}
);

/**
 * Enquiry link for a tour.
 *
 * @param int $post_id Tour ID.
 * @return string
 */
function beleon_booking_link( $post_id = 0 ) {
	$url = beleon_mod( 'beleon_booking_url' );
	$url = $url ? $url : '/contact/';
	if ( 0 === strpos( $url, '/' ) ) {
		$url = home_url( $url );
	}
	if ( $post_id ) {
		$url = add_query_arg( 'tour', rawurlencode( get_the_title( $post_id ) ), $url );
	}
	return $url;
}

/**
 * "All tours" link.
 *
 * @return string
 */
function beleon_tours_link() {
	$url = beleon_mod( 'beleon_tours_archive_url' );
	if ( $url ) {
		return 0 === strpos( $url, '/' ) ? home_url( $url ) : $url;
	}
	$archive = get_post_type_archive_link( 'tours' );
	return $archive ? $archive : home_url( '/' );
}

/**
 * Relative theme-mod URLs become absolute.
 *
 * @param string $url URL or path.
 * @return string
 */
function beleon_url( $url ) {
	return 0 === strpos( (string) $url, '/' ) ? home_url( $url ) : (string) $url;
}

/**
 * Configured social profiles: [ icon => url ].
 *
 * @return array
 */
function beleon_socials() {
	$out = array();
	foreach ( array( 'facebook', 'instagram', 'youtube', 'tiktok' ) as $net ) {
		$url = beleon_mod( 'beleon_' . $net );
		if ( $url ) {
			$out[ $net ] = $url;
		}
	}
	$wa = preg_replace( '/\D/', '', beleon_mod( 'beleon_whatsapp' ) );
	if ( $wa ) {
		$out['whatsapp'] = 'https://wa.me/' . $wa;
	}
	return $out;
}
