<?php
/**
 * Template slots: let Elementor own any part of the site.
 *
 * Every region (header, footer, single tour, single destination, tour and
 * destination archives) renders, in order of priority:
 *   1. an Elementor Pro Theme Builder template, when Pro is active and one matches;
 *   2. an Elementor template chosen at Beleon > Layout (works with free Elementor);
 *   3. the theme's own fast PHP template.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Slot definitions: slot => [ label, Elementor Pro location ].
 *
 * @return array
 */
function beleon_slots() {
	return array(
		'header'               => array( __( 'Header', 'beleon-tours' ), 'header' ),
		'footer'               => array( __( 'Footer', 'beleon-tours' ), 'footer' ),
		'single_tours'         => array( __( 'Single tour', 'beleon-tours' ), 'single' ),
		'single_destinations'  => array( __( 'Single destination', 'beleon-tours' ), 'single' ),
		'archive_tours'        => array( __( 'Tours archive', 'beleon-tours' ), 'archive' ),
		'archive_destinations' => array( __( 'Destinations archive', 'beleon-tours' ), 'archive' ),
	);
}

add_action(
	'elementor/theme/register_locations',
	function ( $manager ) {
		$manager->register_all_core_location();
	}
);

/**
 * Template ID assigned to a slot (0 when none or Elementor is off).
 *
 * @param string $slot Slot.
 * @return int
 */
function beleon_slot_template( $slot ) {
	if ( ! did_action( 'elementor/loaded' ) ) {
		return 0;
	}
	$layout = (array) get_option( 'beleon_layout', array() );
	$id     = isset( $layout[ $slot ] ) ? (int) $layout[ $slot ] : 0;
	if ( $id && 'publish' === get_post_status( $id ) ) {
		return (int) apply_filters( 'beleon_slot_template', $id, $slot );
	}
	return 0;
}

/**
 * Render a slot through Elementor. Returns true when Elementor printed it.
 *
 * @param string $slot Slot.
 * @return bool
 */
function beleon_render_slot( $slot ) {
	$slots = beleon_slots();
	if ( isset( $slots[ $slot ] ) && function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( $slots[ $slot ][1] ) ) {
		return true;
	}
	$id = beleon_slot_template( $slot );
	if ( ! $id ) {
		return false;
	}
	// Don't render a template inside itself while it is being edited.
	if ( (int) get_the_ID() === $id && is_singular( 'elementor_library' ) ) {
		return false;
	}
	echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $id, true ); // phpcs:ignore WordPress.Security.EscapeOutput
	return true;
}

/**
 * Elementor needs the slot templates' CSS in the head, not mid-page.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}
		foreach ( array_keys( beleon_slots() ) as $slot ) {
			$id = beleon_slot_template( $slot );
			if ( ! $id || ! beleon_slot_applies( $slot ) ) {
				continue;
			}
			$css = \Elementor\Core\Files\CSS\Post::create( $id );
			$css->enqueue();
		}
	},
	20
);

/**
 * Is a slot used on the current request?
 *
 * @param string $slot Slot.
 * @return bool
 */
function beleon_slot_applies( $slot ) {
	switch ( $slot ) {
		case 'header':
		case 'footer':
			return true;
		case 'single_tours':
			return is_singular( 'tours' );
		case 'single_destinations':
			return is_singular( 'destinations' );
		case 'archive_tours':
			return is_post_type_archive( 'tours' );
		case 'archive_destinations':
			return is_post_type_archive( 'destinations' );
	}
	return false;
}

/**
 * The post a tour/destination widget should describe.
 *
 * On a single tour that's the tour itself. While editing or previewing an
 * Elementor template (or any page), it falls back to the latest published
 * post of that type, so widgets show real content in the editor.
 *
 * @param string $type tours|destinations.
 * @return int
 */
function beleon_context_post( $type = 'tours' ) {
	$id = get_the_ID();
	if ( $id && get_post_type( $id ) === $type ) {
		return (int) $id;
	}
	static $latest = array();
	if ( ! isset( $latest[ $type ] ) ) {
		$posts           = get_posts(
			array(
				'post_type'      => $type,
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
		$latest[ $type ] = $posts ? (int) $posts[0] : 0;
	}
	return $latest[ $type ];
}

/**
 * Template parts with data: get_template_part() + args.
 *
 * @param string $name Part under template-parts/.
 * @param array  $args Args.
 */
function beleon_part( $name, $args = array() ) {
	get_template_part( 'template-parts/' . $name, null, $args );
}

/**
 * Menu fallback until a menu is assigned: tours, destinations and key pages.
 *
 * @param array $args wp_nav_menu args.
 */
function beleon_menu_fallback( $args ) {
	$links = array( array( __( 'Tours', 'beleon-tours' ), beleon_tours_link() ) );
	$dest  = get_post_type_archive_link( 'destinations' );
	if ( $dest ) {
		$links[] = array( __( 'Destinations', 'beleon-tours' ), $dest );
	}
	foreach ( array( 'about-us', 'contact' ) as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			$links[] = array( get_the_title( $page ), get_permalink( $page ) );
		}
	}
	$class = ! empty( $args['menu_class'] ) ? $args['menu_class'] : 'menu';
	echo '<ul class="' . esc_attr( $class ) . '">';
	foreach ( $links as $link ) {
		echo '<li class="menu-item"><a href="' . esc_url( $link[1] ) . '">' . esc_html( $link[0] ) . '</a></li>';
	}
	echo '</ul>';
}
