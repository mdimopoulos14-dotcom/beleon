<?php
/**
 * Front-end assets: self-hosted fonts, one stylesheet, one deferred script.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Font files: family => [ style => [ subset => file ] ].
 *
 * @return array
 */
function beleon_font_files() {
	return array(
		'Beleon Sans'  => array(
			'normal' => array(
				'greek' => 'manrope-greek-wght-normal.woff2',
				'latin' => 'manrope-latin-wght-normal.woff2',
			),
		),
		'Beleon Serif' => array(
			'normal' => array(
				'greek' => 'noto-serif-display-greek-wght-normal.woff2',
				'latin' => 'noto-serif-display-latin-wght-normal.woff2',
			),
			'italic' => array(
				'greek' => 'noto-serif-display-greek-wght-italic.woff2',
				'latin' => 'noto-serif-display-latin-wght-italic.woff2',
			),
		),
	);
}

/**
 * Preload the upright fonts (above-the-fold text) and declare all faces inline,
 * so text renders in the brand fonts without an extra CSS request.
 */
add_action(
	'wp_head',
	function () {
		$ranges = array(
			'greek' => 'U+0370-0377,U+037A-037F,U+0384-038A,U+038C,U+038E-03A1,U+03A3-03FF',
			'latin' => 'U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD',
		);
		$base   = BELEON_URI . '/assets/fonts/';
		$css    = '';
		foreach ( beleon_font_files() as $family => $styles ) {
			foreach ( $styles as $style => $subsets ) {
				foreach ( $subsets as $subset => $file ) {
					if ( 'normal' === $style && apply_filters( 'beleon_preload_font', true, $family, $subset ) ) {
						echo '<link rel="preload" href="' . esc_url( $base . $file ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
					}
					$css .= '@font-face{font-family:"' . $family . '";font-style:' . $style . ';font-weight:200 900;font-display:swap;src:url(' . esc_url( $base . $file ) . ') format("woff2");unicode-range:' . $ranges[ $subset ] . '}';
				}
			}
		}
		// Metric-matched fallbacks keep layout shift low while the fonts load.
		$css .= '@font-face{font-family:"Beleon Serif Fallback";src:local("Georgia");size-adjust:104%;ascent-override:96%;descent-override:26%}';
		$css .= '@font-face{font-family:"Beleon Sans Fallback";src:local("Arial");size-adjust:104%;ascent-override:102%;descent-override:29%}';
		echo '<style id="beleon-fonts">' . $css . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	},
	2
);

/**
 * Relative path of an asset, preferring the minified build when present.
 *
 * @param string $name File name without extension.
 * @param string $ext  css|js.
 * @return string
 */
function beleon_asset( $name, $ext ) {
	$min = "assets/{$ext}/{$name}.min.{$ext}";
	return file_exists( BELEON_DIR . '/' . $min ) && ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? $min : "assets/{$ext}/{$name}.{$ext}";
}

add_action(
	'wp_enqueue_scripts',
	function () {
		$css = beleon_asset( 'main', 'css' );
		$js  = beleon_asset( 'main', 'js' );

		/*
		 * The stylesheet is small, so it is printed inline: one less render-blocking
		 * request on first visit. To serve it as a cached file instead:
		 * add_filter( 'beleon_inline_css', '__return_false' );
		 */
		if ( apply_filters( 'beleon_inline_css', true ) ) {
			wp_register_style( 'beleon-main', false, array(), BELEON_VERSION );
			wp_add_inline_style( 'beleon-main', (string) file_get_contents( BELEON_DIR . '/' . $css ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		} else {
			wp_register_style( 'beleon-main', BELEON_URI . '/' . $css, array(), (string) filemtime( BELEON_DIR . '/' . $css ) );
		}
		wp_enqueue_style( 'beleon-main' );

		wp_enqueue_script(
			'beleon-main',
			BELEON_URI . '/' . $js,
			array(),
			(string) filemtime( BELEON_DIR . '/' . $js ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	},
	5
);

/**
 * Elementor editor: same fonts and styles inside the preview, plus small panel tweaks.
 */
add_action(
	'elementor/preview/enqueue_styles',
	function () {
		wp_enqueue_style( 'beleon-editor-preview', BELEON_URI . '/assets/css/editor-preview.css', array(), BELEON_VERSION );
	}
);
