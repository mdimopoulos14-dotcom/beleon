<?php
/**
 * Small shared helpers.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inline SVG icons (stroke icons, 24px grid). No icon font, no requests.
 *
 * @param string $name  Icon name.
 * @param string $class Extra class.
 * @return string
 */
function beleon_icon( $name, $class = '' ) {
	$paths = array(
		'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'arrow-up' => '<path d="M12 19V5M6 11l6-6 6 6"/>',
		'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'calendar' => '<rect x="3.5" y="5" width="17" height="15.5" rx="2.5"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
		'pin'      => '<path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
		'users'    => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 4.6a3.5 3.5 0 0 1 0 6.8M18 14a6.5 6.5 0 0 1 3.5 6"/>',
		'plane'    => '<path d="M10.5 13.5 3 11l1.5-1.5 8 .5 4-4.5a2.1 2.1 0 0 1 3 3l-4.5 4 .5 8L14 22l-2.5-7.5L8 18v3l-1.5 1-1.5-3.5L1.5 17l1-1.5h3z"/>',
		'phone'    => '<path d="M5 3h3.5l1.8 4.6-2.3 1.5a11 11 0 0 0 6 6l1.5-2.3L20 14.5V18a3 3 0 0 1-3 3A17 17 0 0 1 2 6a3 3 0 0 1 3-3z"/>',
		'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="m3.5 6.5 8.5 6.5 8.5-6.5"/>',
		'check'    => '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
		'x'        => '<path d="M6 6l12 12M18 6 6 18"/>',
		'shield'   => '<path d="M12 3 4.5 6v5.5c0 4.6 3.2 8.5 7.5 9.5 4.3-1 7.5-4.9 7.5-9.5V6z"/><path d="m8.8 12 2.2 2.2 4.4-4.4"/>',
		'compass'  => '<circle cx="12" cy="12" r="9"/><path d="m15.5 8.5-2 5-5 2 2-5z"/>',
		'bed'      => '<path d="M3 18V7M3 14h18v4M21 14v-2.5A3.5 3.5 0 0 0 17.5 8H11v6"/><circle cx="7" cy="10.5" r="1.8"/>',
		'star'     => '<path d="m12 3.5 2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 16.9l-5.2 2.7 1-5.8-4.3-4.1 5.9-.9z"/>',
		'heart'    => '<path d="M12 20s-7.5-4.6-7.5-10.2A4.3 4.3 0 0 1 12 7.4a4.3 4.3 0 0 1 7.5 2.4C19.5 15.4 12 20 12 20z"/>',
		'mountain' => '<path d="m2.5 19.5 6.5-11 4 6.5 2.5-3.5 6 8z"/><path d="m7.4 11.2 1.6 1.3 1.6-1.3"/>',
		'globe'    => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.7 3.6 5.7 3.6 9S14.5 18.3 12 21c-2.5-2.7-3.6-5.7-3.6-9S9.5 5.7 12 3z"/>',
		'menu'     => '<path d="M4 8h16M4 16h16"/>',
		'search'   => '<circle cx="11" cy="11" r="6.5"/><path d="m20 20-4.4-4.4"/>',
		'plus'     => '<path d="M12 5v14M5 12h14"/>',
		'quote'    => '<path d="M10 7H6.5A2.5 2.5 0 0 0 4 9.5V13h5v5H4M20 7h-3.5A2.5 2.5 0 0 0 14 9.5V13h5v5h-5"/>',
		'facebook' => '<path d="M14 8.5h2.5V5H14a4 4 0 0 0-4 4v2H7.5v3.5H10V21h3.5v-6.5H16l.5-3.5h-3V9a.5.5 0 0 1 .5-.5z"/>',
		'instagram' => '<rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.3" cy="6.7" r=".6" fill="currentColor"/>',
		'youtube'  => '<rect x="2.5" y="5.5" width="19" height="13" rx="4"/><path d="m10.5 9.3 4.5 2.7-4.5 2.7z" fill="currentColor"/>',
		'tiktok'   => '<path d="M14 3.5v11a3.5 3.5 0 1 1-3.5-3.5M14 3.5c.4 2.6 2.4 4.6 5 5"/>',
		'whatsapp' => '<path d="M4 20.5 5.3 16A8.5 8.5 0 1 1 8.4 19z"/><path d="M9 8.5c0 3.4 2.9 6.5 6.4 6.5l1.1-1.6-2.1-1-.9.9c-1-.4-2.2-1.6-2.6-2.6l.9-.9-1-2.1z"/>',
	);
	$paths = apply_filters( 'beleon_icons', $paths );
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return '<svg class="bl-ico ' . esc_attr( $class ) . '" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[ $name ] . '</svg>';
}

/**
 * Icon names for Elementor select controls.
 *
 * @return array
 */
function beleon_icon_options() {
	$names = array( 'compass', 'shield', 'users', 'bed', 'star', 'heart', 'mountain', 'globe', 'plane', 'calendar', 'clock', 'pin', 'check', 'phone', 'mail' );
	return array_combine( $names, array_map( 'ucfirst', $names ) );
}

/**
 * Render "*emphasis*" in headings as an italic accent span (safe: text is escaped first).
 *
 * @param string $text Raw heading text.
 * @return string
 */
function beleon_accent( $text ) {
	$text = esc_html( $text );
	$text = preg_replace( '/\*(.+?)\*/u', '<em class="bl-accent">$1</em>', $text );
	return nl2br( $text );
}

/**
 * Is the current singular page built with Elementor?
 *
 * @param int $post_id Optional post ID.
 * @return bool
 */
function beleon_is_elementor_page( $post_id = 0 ) {
	if ( ! did_action( 'elementor/loaded' ) ) {
		return false;
	}
	if ( ! $post_id ) {
		if ( ! is_singular() ) {
			return false;
		}
		$post_id = get_queried_object_id();
	}
	$document = \Elementor\Plugin::$instance->documents->get( $post_id );
	return $document && $document->is_built_with_elementor();
}

/**
 * Responsive attachment image with sensible defaults.
 *
 * @param int    $id    Attachment ID.
 * @param string $size  Image size.
 * @param array  $attr  Attributes. Pass 'priority' => true for the LCP image.
 * @return string
 */
function beleon_img( $id, $size = 'large', $attr = array() ) {
	if ( ! $id ) {
		return '';
	}
	$priority = ! empty( $attr['priority'] );
	unset( $attr['priority'] );
	$attr = wp_parse_args(
		$attr,
		$priority
			? array( 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async' )
			: array( 'loading' => 'lazy', 'decoding' => 'async' )
	);
	return wp_get_attachment_image( $id, $size, false, $attr );
}

/**
 * Price formatting: numbers become "1.290 €", anything else is printed as entered.
 *
 * @param string $price Raw price.
 * @return string
 */
function beleon_format_price( $price ) {
	$price = trim( (string) $price );
	if ( '' === $price ) {
		return '';
	}
	if ( is_numeric( str_replace( array( '.', ',' ), '', $price ) ) && ! preg_match( '/[^\d.,]/', $price ) ) {
		$number = (float) str_replace( ',', '.', str_replace( '.', '', $price ) );
		$symbol = get_theme_mod( 'beleon_currency', '€' );
		return beleon_number( $number ) . "\u{00A0}" . $symbol;
	}
	return $price;
}

/**
 * Whole number with the thousands separator of the front-end language
 * (Greek: 1.290; otherwise the WordPress locale's).
 *
 * @param float $number Number.
 * @return string
 */
function beleon_number( $number ) {
	if ( 'el' === get_theme_mod( 'beleon_front_lang', 'el' ) || 0 === strpos( determine_locale(), 'el' ) ) {
		return number_format( (float) $number, 0, ',', '.' );
	}
	return number_format_i18n( (float) $number, 0 );
}

/**
 * Safe Elementor link attributes from a URL control value.
 *
 * @param array $link Elementor URL control value.
 * @return string
 */
function beleon_link_attrs( $link ) {
	if ( empty( $link['url'] ) ) {
		return '';
	}
	$attrs = ' href="' . esc_url( $link['url'] ) . '"';
	if ( ! empty( $link['is_external'] ) ) {
		$attrs .= ' target="_blank"';
	}
	$rel = array();
	if ( ! empty( $link['nofollow'] ) ) {
		$rel[] = 'nofollow';
	}
	if ( ! empty( $link['is_external'] ) ) {
		$rel[] = 'noopener';
	}
	if ( $rel ) {
		$attrs .= ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"';
	}
	return $attrs;
}
