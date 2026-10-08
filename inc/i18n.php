<?php
/**
 * Translations.
 *
 * Theme strings are written in English and translated in languages/.
 * Beleon publishes in Greek, so by default the Greek strings are used on the
 * front end (and in Elementor's preview) even if the WordPress admin language
 * is English. Change this in Appearance > Customize > Beleon > Brand & contact.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Language forced for theme strings on this request, or '' to follow WordPress.
 *
 * @param bool $anywhere Ignore the wp-admin exception (used while rendering widget previews).
 * @return string
 */
function beleon_forced_lang( $anywhere = false ) {
	// With Polylang, theme texts follow the visitor's language (Greek file for "el", English source otherwise).
	if ( function_exists( 'pll_current_language' ) ) {
		if ( ! $anywhere && is_admin() && ! wp_doing_ajax() ) {
			return '';
		}
		$current = (string) pll_current_language( 'slug' );
		if ( '' === $current && function_exists( 'pll_default_language' ) ) {
			$current = (string) pll_default_language( 'slug' );
		}
		return 'el' === $current && file_exists( BELEON_DIR . '/languages/el.mo' ) ? 'el' : '';
	}
	$lang = sanitize_key( (string) get_theme_mod( 'beleon_front_lang', 'el' ) );
	if ( 'auto' === $lang || '' === $lang ) {
		return '';
	}
	// wp-admin screens follow the user's language; front end, AJAX and previews use the site language.
	if ( ! $anywhere && is_admin() && ! wp_doing_ajax() ) {
		return '';
	}
	return file_exists( BELEON_DIR . '/languages/' . $lang . '.mo' ) ? $lang : '';
}

add_action(
	'after_setup_theme',
	function () {
		load_theme_textdomain( 'beleon-tours', BELEON_DIR . '/languages' );
	}
);

/**
 * Whatever locale WordPress asks for (Elementor's preview switches to the
 * editor's own language), load the forced language file for this theme.
 */
add_filter(
	'load_textdomain_mofile',
	function ( $mofile, $domain ) {
		if ( 'beleon-tours' !== $domain ) {
			return $mofile;
		}
		$lang = beleon_forced_lang();
		return $lang ? BELEON_DIR . '/languages/' . $lang . '.mo' : $mofile;
	},
	10,
	2
);

/**
 * A locale switch unloads every text domain, and WordPress only reloads ours if
 * a file for the new locale exists. Reload the forced language explicitly.
 */
add_action(
	'change_locale',
	function () {
		$lang = beleon_forced_lang();
		if ( $lang ) {
			load_textdomain( 'beleon-tours', BELEON_DIR . '/languages/' . $lang . '.mo' );
		}
	}
);

/**
 * The Elementor editor (a wp-admin screen) pre-renders widget HTML for its
 * preview. Render Beleon widgets in the site language there too, then switch
 * back so the editor's controls stay in the user's language.
 */
add_action(
	'elementor/widget/before_render_content',
	function ( $widget ) {
		if ( ! is_admin() || wp_doing_ajax() || 0 !== strpos( $widget->get_name(), 'beleon-' ) ) {
			return;
		}
		$lang = beleon_forced_lang( true );
		if ( $lang ) {
			unload_textdomain( 'beleon-tours', true );
			load_textdomain( 'beleon-tours', BELEON_DIR . '/languages/' . $lang . '.mo' );
			$GLOBALS['beleon_preview_lang'] = true;
		}
	}
);
add_filter(
	'elementor/widget/render_content',
	function ( $content ) {
		if ( ! empty( $GLOBALS['beleon_preview_lang'] ) ) {
			unload_textdomain( 'beleon-tours', true );
			load_theme_textdomain( 'beleon-tours', BELEON_DIR . '/languages' );
			$GLOBALS['beleon_preview_lang'] = false;
		}
		return $content;
	},
	999
);

/**
 * When theme texts are forced to Greek, declare the page as Greek too
 * (correct uppercase accents, hyphenation and SEO).
 */
add_filter(
	'language_attributes',
	function ( $output ) {
		if ( 'el' !== beleon_forced_lang() || 0 === strpos( determine_locale(), 'el' ) ) {
			return $output;
		}
		return preg_replace( '/lang="[^"]*"/', 'lang="el"', $output );
	}
);

/**
 * Polylang decides the language after themes load: reload the theme texts then.
 */
add_action(
	'pll_language_defined',
	function () {
		unload_textdomain( 'beleon-tours', true );
		$lang = beleon_forced_lang();
		if ( $lang ) {
			load_textdomain( 'beleon-tours', BELEON_DIR . '/languages/' . $lang . '.mo' );
		} else {
			load_theme_textdomain( 'beleon-tours', BELEON_DIR . '/languages' );
		}
		beleon_refresh_type_labels();
	}
);

/**
 * Post type names were translated at init, before the language was known:
 * translate the ones shown on the front end (breadcrumbs, archive titles) again.
 */
add_action( 'wp', 'beleon_refresh_type_labels' );
function beleon_refresh_type_labels() {
	$names = array(
		'tours'        => array( __( 'Tours', 'beleon-tours' ), __( 'Tour', 'beleon-tours' ), __( 'All tours', 'beleon-tours' ) ),
		'destinations' => array( __( 'Destinations', 'beleon-tours' ), __( 'Destination', 'beleon-tours' ), __( 'All destinations', 'beleon-tours' ) ),
	);
	foreach ( (array) ( $GLOBALS['beleon_registered_types'] ?? array() ) as $type ) {
		$object = get_post_type_object( $type );
		if ( ! $object || empty( $names[ $type ] ) ) {
			continue;
		}
		list( $object->labels->name, $object->labels->singular_name, $object->labels->all_items ) = $names[ $type ];
		$object->labels->menu_name = $object->labels->name;
		$object->labels->archives  = $object->labels->name;
		$object->label             = $object->labels->name;
	}
}
