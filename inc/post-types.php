<?php
/**
 * The "tours" and "destinations" post types.
 *
 * On the live site these are registered by JetEngine. The theme never
 * replaces an existing registration: it only registers same-slug versions
 * when nothing else does (fresh installs, staging, or if JetEngine is turned
 * off), so URLs and content keep working either way.
 *
 * Disable with: add_filter( 'beleon_register_post_types', '__return_false' );
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'init',
	function () {
		if ( ! apply_filters( 'beleon_register_post_types', true ) ) {
			return;
		}

		$types = array(
			'tours'        => array(
				'labels'    => array(
					'name'          => __( 'Tours', 'beleon-tours' ),
					'singular_name' => __( 'Tour', 'beleon-tours' ),
					'add_new_item'  => __( 'Add new tour', 'beleon-tours' ),
					'edit_item'     => __( 'Edit tour', 'beleon-tours' ),
					'all_items'     => __( 'All tours', 'beleon-tours' ),
				),
				'menu_icon' => 'dashicons-palmtree',
				'position'  => 21,
			),
			'destinations' => array(
				'labels'    => array(
					'name'          => __( 'Destinations', 'beleon-tours' ),
					'singular_name' => __( 'Destination', 'beleon-tours' ),
					'add_new_item'  => __( 'Add new destination', 'beleon-tours' ),
					'edit_item'     => __( 'Edit destination', 'beleon-tours' ),
					'all_items'     => __( 'All destinations', 'beleon-tours' ),
				),
				'menu_icon' => 'dashicons-location-alt',
				'position'  => 22,
			),
		);

		foreach ( $types as $slug => $data ) {
			if ( post_type_exists( $slug ) ) {
				continue;
			}
			register_post_type(
				$slug,
				array(
					'labels'        => $data['labels'],
					'public'        => true,
					'show_in_rest'  => true,
					'has_archive'   => true,
					'menu_icon'     => $data['menu_icon'],
					'menu_position' => $data['position'],
					'rewrite'       => array(
						'slug'       => $slug,
						'with_front' => false,
					),
					'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'page-attributes', 'revisions' ),
				)
			);
			$GLOBALS['beleon_registered_types'][] = $slug;
		}
	},
	99
);

/**
 * Flush permalinks once after the theme registers the post types for the first time.
 */
add_action(
	'init',
	function () {
		if ( empty( $GLOBALS['beleon_registered_types'] ) ) {
			return;
		}
		$key = implode( ',', $GLOBALS['beleon_registered_types'] );
		if ( get_option( 'beleon_flushed_types' ) !== $key ) {
			flush_rewrite_rules( false );
			update_option( 'beleon_flushed_types', $key, false );
		}
	},
	100
);

/**
 * Elementor may edit tours and destinations too.
 */
add_action(
	'after_switch_theme',
	function () {
		$support = get_option( 'elementor_cpt_support', array( 'page', 'post' ) );
		if ( ! is_array( $support ) ) {
			$support = array( 'page', 'post' );
		}
		update_option( 'elementor_cpt_support', array_values( array_unique( array_merge( $support, array( 'page', 'tours', 'destinations' ) ) ) ) );
	}
);
