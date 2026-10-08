<?php
/**
 * Theme supports, menus, image sizes, body classes.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'after_setup_theme',
	function () {
		$GLOBALS['content_width'] = 1240;

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 86,
				'width'       => 320,
				'flex-height' => true,
				'flex-width'  => true,
			)
		);
		add_theme_support( 'editor-styles' );

		register_nav_menus(
			array(
				'primary' => __( 'Primary menu', 'beleon-tours' ),
				'footer'  => __( 'Footer menu', 'beleon-tours' ),
				'legal'   => __( 'Footer legal links', 'beleon-tours' ),
			)
		);

		// Card (portrait 4:5), wide (hero / banners, 16:9) and square thumbs.
		// The smaller same-ratio sizes feed srcset, so phones download small files.
		add_image_size( 'beleon-card', 760, 950, true );
		add_image_size( 'beleon-card-sm', 400, 500, true );
		add_image_size( 'beleon-wide', 1920, 1080, true );
		add_image_size( 'beleon-wide-md', 1280, 720, true );
		add_image_size( 'beleon-wide-sm', 800, 450, true );
		add_image_size( 'beleon-thumb', 480, 480, true );
	}
);

add_filter(
	'image_size_names_choose',
	function ( $sizes ) {
		return array_merge(
			$sizes,
			array(
				'beleon-card'  => __( 'Beleon card (4:5)', 'beleon-tours' ),
				'beleon-wide'  => __( 'Beleon wide (16:9)', 'beleon-tours' ),
				'beleon-thumb' => __( 'Beleon square', 'beleon-tours' ),
			)
		);
	}
);

/**
 * New JPEG/PNG uploads also get WebP sub-sizes (smaller files, better PageSpeed).
 * Disable with: add_filter( 'beleon_webp_uploads', '__return_false' );
 */
add_filter(
	'image_editor_output_format',
	function ( $formats ) {
		if ( apply_filters( 'beleon_webp_uploads', true ) && wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
			$formats['image/jpeg'] = 'image/webp';
			$formats['image/png']  = 'image/webp';
		}
		return $formats;
	}
);

/**
 * Slightly lower WebP quality: visually identical for photos, ~30% smaller files.
 */
add_filter(
	'wp_editor_set_quality',
	function ( $quality, $mime ) {
		return 'image/webp' === $mime ? (int) apply_filters( 'beleon_webp_quality', 74 ) : $quality;
	},
	10,
	2
);

add_filter(
	'body_class',
	function ( $classes ) {
		if ( beleon_header_is_transparent() ) {
			$classes[] = 'bl-header-over';
		}
		if ( beleon_is_elementor_page() ) {
			$classes[] = 'bl-elementor-page';
		}
		return $classes;
	}
);

/**
 * Does the header float over the first section of this page?
 */
function beleon_header_is_transparent() {
	$mode = get_theme_mod( 'beleon_header_transparent', 'front' );
	if ( 'never' === $mode ) {
		$on = false;
	} elseif ( 'always' === $mode ) {
		$on = true;
	} else {
		// Front page, and singles/archives of tours & destinations, open with a full-bleed hero.
		$on = is_front_page() || is_singular( array( 'tours', 'destinations' ) );
	}
	if ( is_singular() && get_post_meta( get_queried_object_id(), '_beleon_header_over', true ) ) {
		$on = true;
	}
	return (bool) apply_filters( 'beleon_header_transparent', $on );
}
