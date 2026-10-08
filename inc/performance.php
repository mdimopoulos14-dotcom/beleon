<?php
/**
 * PageSpeed: trim WordPress and Elementor front-end weight.
 * Each optimisation has a filter to switch it off if a plugin needs it.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Head clutter: emoji script + styles, generator, RSD, shortlink, oEmbed discovery.
 */
add_action(
	'init',
	function () {
		if ( ! apply_filters( 'beleon_clean_head', true ) ) {
			return;
		}
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'emoji_svg_url', '__return_false' );

		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		remove_action( 'wp_head', 'feed_links_extra', 3 );
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		remove_action( 'wp_head', 'rest_output_link_wp_head' );
	}
);

/**
 * Block-editor CSS is only needed where block content is rendered.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! apply_filters( 'beleon_dequeue_block_styles', true ) ) {
			return;
		}
		$needs_blocks = false;
		if ( is_singular() && ! beleon_is_elementor_page() ) {
			$needs_blocks = has_blocks( get_queried_object_id() );
		}
		if ( $needs_blocks ) {
			return;
		}
		foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles', 'core-block-supports' ) as $handle ) {
			wp_dequeue_style( $handle );
		}
	},
	100
);

/**
 * jQuery Migrate is not needed by the theme or modern Elementor.
 */
add_action(
	'wp_default_scripts',
	function ( $scripts ) {
		if ( is_admin() || ! apply_filters( 'beleon_remove_jquery_migrate', true ) || empty( $scripts->registered['jquery'] ) ) {
			return;
		}
		$scripts->registered['jquery']->deps = array_diff( $scripts->registered['jquery']->deps, array( 'jquery-migrate' ) );
	}
);

/**
 * Elementor's icon font (eicons) is only needed for the editor and a few
 * widgets' UI icons; visitors don't need the 50 KB font.
 */
add_action(
	'elementor/frontend/after_enqueue_styles',
	function () {
		if ( is_user_logged_in() || ! apply_filters( 'beleon_dequeue_eicons', true ) ) {
			return;
		}
		wp_dequeue_style( 'elementor-icons' );
	},
	20
);

/**
 * Comment reply script only where threaded comments are open.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}
);

/**
 * Heartbeat is not needed on the front end.
 */
add_action(
	'init',
	function () {
		if ( ! is_admin() && ! is_user_logged_in() && apply_filters( 'beleon_disable_front_heartbeat', true ) ) {
			wp_deregister_script( 'heartbeat' );
		}
	},
	1
);

/**
 * Lazy images: never lazy-load the first content image (likely LCP).
 */
add_filter( 'wp_omit_loading_attr_threshold', fn() => 1 );

/* --------------------------------------------------------------------------
 * Lean Elementor: only load Elementor's front-end JavaScript (and jQuery)
 * when the page actually needs it.
 *
 * The theme's widgets and the usual content widgets (heading, text, image,
 * button...) are pure HTML/CSS. Elementor still loads ~150 KB of JS plus
 * jQuery on every page. While a page renders, the theme notes every widget and
 * effect it uses; if all of them work without Elementor's script, the script
 * is skipped. Anything that needs it (entrance animations, sliders, tabs,
 * background video, sticky, motion effects, unknown third-party widgets)
 * keeps it automatically.
 *
 * Turn off with: add_filter( 'beleon_lean_elementor', '__return_false' );
 * Allow more widgets with the 'beleon_static_widgets' filter.
 * ------------------------------------------------------------------------ */

/**
 * Should the lean mode run on this request?
 *
 * @return bool
 */
function beleon_lean_enabled() {
	if ( is_admin() || ! did_action( 'elementor/loaded' ) || ! apply_filters( 'beleon_lean_elementor', true ) ) {
		return false;
	}
	$plugin = \Elementor\Plugin::$instance;
	if ( ( $plugin->preview && $plugin->preview->is_preview_mode() ) || ( $plugin->editor && $plugin->editor->is_edit_mode() ) ) {
		return false;
	}
	return ! isset( $_GET['elementor-preview'] ); // phpcs:ignore WordPress.Security.NonceVerification
}

/**
 * Widgets that render fine without Elementor's front-end JavaScript.
 *
 * @return string[]
 */
function beleon_static_widgets() {
	return apply_filters(
		'beleon_static_widgets',
		array( 'heading', 'text-editor', 'image', 'button', 'spacer', 'divider', 'icon', 'icon-box', 'image-box', 'icon-list', 'html', 'shortcode', 'google_maps', 'social-icons', 'star-rating', 'wp-widget-nav_menu' )
	);
}

$GLOBALS['beleon_needs_elementor_js'] = false;

/**
 * Inspect each rendered element.
 *
 * @param \Elementor\Element_Base $element Element.
 */
function beleon_lean_inspect( $element ) {
	if ( $GLOBALS['beleon_needs_elementor_js'] ) {
		return;
	}
	$s = $element->get_settings();
	foreach ( array( '_animation', 'animation' ) as $key ) {
		if ( ! empty( $s[ $key ] ) && 'none' !== $s[ $key ] ) {
			$GLOBALS['beleon_needs_elementor_js'] = true;
			return;
		}
	}
	if ( in_array( $s['background_background'] ?? '', array( 'slideshow', 'video' ), true ) || ! empty( $s['sticky'] ) || ! empty( $s['motion_fx_motion_fx_scrolling'] ) || ! empty( $s['motion_fx_motion_fx_mouse'] ) || ! empty( $s['background_motion_fx_motion_fx_scrolling'] ) ) {
		$GLOBALS['beleon_needs_elementor_js'] = true;
		return;
	}
	if ( 'widget' === $element->get_type() ) {
		$name = $element->get_name();
		if ( 0 !== strpos( $name, 'beleon-' ) && ! in_array( $name, beleon_static_widgets(), true ) ) {
			$GLOBALS['beleon_needs_elementor_js'] = true;
		}
	}
}
add_action( 'elementor/frontend/before_render', 'beleon_lean_inspect' );

/**
 * jQuery goes to the footer on the front end, so it never blocks rendering.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! beleon_lean_enabled() ) {
			return;
		}
		foreach ( array( 'jquery', 'jquery-core', 'jquery-migrate' ) as $handle ) {
			wp_scripts()->add_data( $handle, 'group', 1 );
		}
	},
	1
);

/**
 * Just before footer scripts print: drop Elementor's JS if nothing needed it.
 */
add_action(
	'wp_footer',
	function () {
		if ( ! beleon_lean_enabled() || $GLOBALS['beleon_needs_elementor_js'] ) {
			return;
		}
		foreach ( array( 'elementor-frontend', 'elementor-frontend-modules', 'elementor-webpack-runtime', 'elementor-pro-frontend', 'elementor-pro-webpack-runtime', 'pro-elements-handlers' ) as $handle ) {
			wp_dequeue_script( $handle );
		}
	},
	1
);
