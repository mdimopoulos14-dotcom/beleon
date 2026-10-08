<?php
/**
 * Elementor integration.
 *
 * - "Beleon" widget category with the theme's widgets.
 * - Brand fonts in every Elementor typography control (self-hosted, never Google).
 * - Site Settings (global colors & fonts) drive the whole theme, PHP templates included.
 * - "Beleon motion" (reveal animations) on every element; "Beleon surface & shapes"
 *   (gradients, rings, arches, aurora, grain) on every container.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Brand tokens with defaults; Elementor global colors/fonts override them.
 *
 * @return array [ 'colors' => [ id => [ title, css var, hex ] ], 'fonts' => [...] ]
 */
function beleon_brand_tokens() {
	return array(
		'colors' => array(
			'primary'   => array( __( 'Ink', 'beleon-tours' ), '--bl-ink', '#0B1428' ),
			'secondary' => array( __( 'Brass', 'beleon-tours' ), '--bl-gold', '#C29A55' ),
			'text'      => array( __( 'Text', 'beleon-tours' ), '--bl-text', '#252C40' ),
			'accent'    => array( __( 'Champagne', 'beleon-tours' ), '--bl-gold-2', '#E8D2A2' ),
			'bllapis'   => array( __( 'Lapis', 'beleon-tours' ), '--bl-lapis', '#1A2E5E' ),
			'blivory'   => array( __( 'Ivory', 'beleon-tours' ), '--bl-ivory', '#F6F2E9' ),
			'blsand'    => array( __( 'Sand', 'beleon-tours' ), '--bl-sand', '#EAE1CE' ),
			'blmuted'   => array( __( 'Muted', 'beleon-tours' ), '--bl-muted', '#646B7F' ),
			'blclay'    => array( __( 'Clay', 'beleon-tours' ), '--bl-clay', '#B0573A' ),
			'blwhite'   => array( __( 'White', 'beleon-tours' ), '--bl-white', '#FFFFFF' ),
		),
		'fonts'  => array(
			'primary'   => array( __( 'Display', 'beleon-tours' ), '--bl-font-display', 'Beleon Serif', '400' ),
			'secondary' => array( __( 'Subtitle', 'beleon-tours' ), '--bl-font-ui', 'Beleon Sans', '600' ),
			'text'      => array( __( 'Body', 'beleon-tours' ), '--bl-font-body', 'Beleon Sans', '400' ),
			'accent'    => array( __( 'Eyebrow', 'beleon-tours' ), '--bl-font-eyebrow', 'Beleon Sans', '700' ),
		),
	);
}

/**
 * Print brand tokens as CSS variables, reading Elementor's Site Settings when present.
 * Runs on every page, so global color/font edits restyle PHP templates as well.
 */
add_action(
	'wp_head',
	function () {
		$tokens   = beleon_brand_tokens();
		$settings = array();
		if ( did_action( 'elementor/loaded' ) ) {
			$kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
			if ( $kit ) {
				$settings = (array) $kit->get_settings();
			}
		}
		$colors = array();
		foreach ( array_merge( (array) ( $settings['system_colors'] ?? array() ), (array) ( $settings['custom_colors'] ?? array() ) ) as $row ) {
			if ( ! empty( $row['_id'] ) && ! empty( $row['color'] ) ) {
				$colors[ $row['_id'] ] = $row['color'];
			}
		}
		$fonts = array();
		foreach ( (array) ( $settings['system_typography'] ?? array() ) as $row ) {
			if ( ! empty( $row['_id'] ) && ! empty( $row['typography_font_family'] ) ) {
				$fonts[ $row['_id'] ] = $row['typography_font_family'];
			}
		}

		$css = ':root{';
		foreach ( $tokens['colors'] as $id => $def ) {
			$value = isset( $colors[ $id ] ) && preg_match( '/^#[0-9a-fA-F]{3,8}$|^rgba?\([\d\s.,%]+\)$/', $colors[ $id ] ) ? $colors[ $id ] : $def[2];
			$css  .= $def[1] . ':' . $value . ';';
		}
		foreach ( $tokens['fonts'] as $id => $def ) {
			$family   = isset( $fonts[ $id ] ) ? preg_replace( '/[^\w\s\-]/u', '', $fonts[ $id ] ) : $def[2];
			$fallback = false !== stripos( $family, 'serif' ) && false === stripos( $family, 'sans' ) ? '"Beleon Serif Fallback",Georgia,serif' : '"Beleon Sans Fallback",system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif';
			$css     .= $def[1] . ':"' . $family . '",' . $fallback . ';';
		}
		$css .= '}';
		echo '<style id="beleon-tokens">' . $css . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	},
	3
);

/**
 * Widget category.
 */
add_action(
	'elementor/elements/categories_registered',
	function ( $manager ) {
		$manager->add_category(
			'beleon',
			array(
				'title' => __( 'Beleon', 'beleon-tours' ),
				'icon'  => 'eicon-globe',
			)
		);
	}
);

/**
 * Widgets.
 */
add_action(
	'elementor/widgets/register',
	function ( $widgets_manager ) {
		require_once __DIR__ . '/widget-base.php';
		$widgets = array(
			'hero'          => 'Hero',
			'heading'       => 'Heading',
			'tours'         => 'Tours',
			'destinations'  => 'Destinations',
			'features'      => 'Features',
			'stats'         => 'Stats',
			'testimonials'  => 'Testimonials',
			'cta'           => 'Cta',
			'marquee'       => 'Marquee',
			'split'         => 'Split',
			'contact'       => 'Contact',
			'post-hero'     => 'Post_Hero',
			'post-content'  => 'Post_Content',
			'tour-facts'    => 'Tour_Facts',
			'tour-booking'  => 'Tour_Booking',
			'tour-itinerary' => 'Tour_Itinerary',
			'tour-includes' => 'Tour_Includes',
			'tour-highlights' => 'Tour_Highlights',
			'gallery'       => 'Gallery',
		);
		foreach ( $widgets as $file => $class ) {
			require_once __DIR__ . '/widgets/' . $file . '.php';
			$fqcn = '\\Beleon\\Elementor\\' . $class;
			$widgets_manager->register( new $fqcn() );
		}
	}
);

/**
 * Brand fonts appear in Elementor's font list as their own group. Fonts in a
 * custom group are never requested from Google; the theme self-hosts them.
 */
add_filter(
	'elementor/fonts/groups',
	function ( $groups ) {
		return array( 'beleon' => __( 'Beleon (self-hosted)', 'beleon-tours' ) ) + $groups;
	}
);
add_filter(
	'elementor/fonts/additional_fonts',
	function ( $fonts ) {
		$fonts['Beleon Serif'] = 'beleon';
		$fonts['Beleon Sans']  = 'beleon';
		return $fonts;
	}
);

/**
 * Motion controls on every widget and container (Advanced tab).
 *
 * @param \Elementor\Element_Base $element Element.
 */
function beleon_add_motion_controls( $element ) {
	$element->start_controls_section(
		'beleon_motion',
		array(
			'label' => __( 'Beleon motion', 'beleon-tours' ),
			'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
		)
	);
	$element->add_control(
		'bl_motion',
		array(
			'label'        => __( 'Reveal on scroll', 'beleon-tours' ),
			'type'         => \Elementor\Controls_Manager::SELECT,
			'default'      => '',
			'options'      => array(
				''           => __( 'None', 'beleon-tours' ),
				'fade-up'    => __( 'Fade up', 'beleon-tours' ),
				'fade'       => __( 'Fade', 'beleon-tours' ),
				'mask-up'    => __( 'Mask reveal (headings)', 'beleon-tours' ),
				'zoom'       => __( 'Soft zoom', 'beleon-tours' ),
				'slide-left' => __( 'Slide from right', 'beleon-tours' ),
				'slide-right' => __( 'Slide from left', 'beleon-tours' ),
				'curtain'    => __( 'Curtain (images)', 'beleon-tours' ),
			),
			'prefix_class' => 'bl-m-',
		)
	);
	$element->add_control(
		'bl_motion_delay',
		array(
			'label'        => __( 'Delay', 'beleon-tours' ),
			'type'         => \Elementor\Controls_Manager::SELECT,
			'default'      => '',
			'options'      => array(
				''  => '0',
				'1' => '0.1s',
				'2' => '0.2s',
				'3' => '0.3s',
				'4' => '0.4s',
				'5' => '0.5s',
				'6' => '0.6s',
			),
			'prefix_class' => 'bl-d-',
			'condition'    => array( 'bl_motion!' => '' ),
		)
	);
	$element->add_control(
		'bl_stagger',
		array(
			'label'        => __( 'Stagger children', 'beleon-tours' ),
			'description'  => __( 'Reveal the items inside one after another.', 'beleon-tours' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'prefix_class' => 'bl-stagger-',
		)
	);
	$element->add_control(
		'bl_hover',
		array(
			'label'        => __( 'Hover effect', 'beleon-tours' ),
			'type'         => \Elementor\Controls_Manager::SELECT,
			'default'      => '',
			'options'      => array(
				''      => __( 'None', 'beleon-tours' ),
				'lift'  => __( 'Lift', 'beleon-tours' ),
				'zoom'  => __( 'Image zoom', 'beleon-tours' ),
				'glow'  => __( 'Gold glow', 'beleon-tours' ),
			),
			'prefix_class' => 'bl-h-',
		)
	);
	$element->end_controls_section();
}
add_action( 'elementor/element/common/_section_style/after_section_end', 'beleon_add_motion_controls' );
add_action( 'elementor/element/container/section_layout/after_section_end', 'beleon_add_motion_controls' );

/**
 * Surface & shape controls on containers (Style tab).
 */
add_action(
	'elementor/element/container/section_layout/after_section_end',
	function ( $element ) {
		$element->start_controls_section(
			'beleon_surface',
			array(
				'label' => __( 'Beleon surface & shapes', 'beleon-tours' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$element->add_control(
			'bl_surface',
			array(
				'label'        => __( 'Surface', 'beleon-tours' ),
				'description'  => __( 'Brand gradient backgrounds. Text colours adapt automatically.', 'beleon-tours' ),
				'type'         => \Elementor\Controls_Manager::SELECT,
				'default'      => '',
				'options'      => array(
					''      => __( 'None', 'beleon-tours' ),
					'night' => __( 'Night (ink gradient + glow)', 'beleon-tours' ),
					'lapis' => __( 'Lapis', 'beleon-tours' ),
					'ivory' => __( 'Ivory', 'beleon-tours' ),
					'sand'  => __( 'Sand fade', 'beleon-tours' ),
					'gold'  => __( 'Champagne gold', 'beleon-tours' ),
					'glass' => __( 'Frosted glass', 'beleon-tours' ),
				),
				'prefix_class' => 'bl-surface-',
			)
		);
		$element->add_control(
			'bl_shape',
			array(
				'label'        => __( 'Decorative shape', 'beleon-tours' ),
				'type'         => \Elementor\Controls_Manager::SELECT,
				'default'      => '',
				'options'      => array(
					''       => __( 'None', 'beleon-tours' ),
					'rings'  => __( 'Orbit rings', 'beleon-tours' ),
					'arch'   => __( 'Silk Road arch', 'beleon-tours' ),
					'aurora' => __( 'Aurora glow (animated)', 'beleon-tours' ),
					'dots'   => __( 'Dot grid', 'beleon-tours' ),
					'line'   => __( 'Gold horizon line', 'beleon-tours' ),
				),
				'prefix_class' => 'bl-shape-',
			)
		);
		$element->add_control(
			'bl_grain',
			array(
				'label'        => __( 'Film grain', 'beleon-tours' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'prefix_class' => 'bl-grain-',
			)
		);
		$element->add_control(
			'bl_radius',
			array(
				'label'        => __( 'Corner style', 'beleon-tours' ),
				'type'         => \Elementor\Controls_Manager::SELECT,
				'default'      => '',
				'options'      => array(
					''      => __( 'Default', 'beleon-tours' ),
					'soft'  => __( 'Soft', 'beleon-tours' ),
					'large' => __( 'Large', 'beleon-tours' ),
					'arch'  => __( 'Arch top', 'beleon-tours' ),
				),
				'prefix_class' => 'bl-r-',
			)
		);
		$element->end_controls_section();
	}
);

/**
 * Apply brand defaults to Elementor's Site Settings once. After that, Site
 * Settings belong to the editor: changes made there are never overwritten.
 * Re-apply from code with beleon_setup_elementor().
 */
function beleon_setup_elementor() {
	if ( ! did_action( 'elementor/loaded' ) ) {
		return;
	}
	update_option( 'elementor_disable_color_schemes', 'yes' );
	update_option( 'elementor_disable_typography_schemes', 'yes' );
	update_option( 'elementor_google_font', '0' );
	update_option( 'elementor_font_display', 'swap' );
	update_option( 'elementor_css_print_method', 'external' );
	update_option( 'elementor_load_fa4_shim', '' );

	$support = get_option( 'elementor_cpt_support', array( 'page', 'post' ) );
	update_option( 'elementor_cpt_support', array_values( array_unique( array_merge( is_array( $support ) ? $support : array(), array( 'page', 'post', 'tours', 'destinations' ) ) ) ) );

	$kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
	if ( ! $kit || ! $kit->get_id() ) {
		return;
	}
	$settings = (array) $kit->get_settings();
	$tokens   = beleon_brand_tokens();

	$system = array();
	$custom = array();
	foreach ( $tokens['colors'] as $id => $def ) {
		$row = array(
			'_id'   => $id,
			'title' => $def[0],
			'color' => $def[2],
		);
		if ( in_array( $id, array( 'primary', 'secondary', 'text', 'accent' ), true ) ) {
			$system[] = $row;
		} else {
			$custom[] = $row;
		}
	}
	$typo = array();
	foreach ( $tokens['fonts'] as $id => $def ) {
		$typo[] = array(
			'_id'                    => $id,
			'title'                  => $def[0],
			'typography_typography'  => 'custom',
			'typography_font_family' => $def[2],
			'typography_font_weight' => $def[3],
		);
	}

	$new = array(
		'system_colors'                   => $system,
		'custom_colors'                   => array_merge(
			$custom,
			array_values(
				array_filter(
					(array) ( $settings['custom_colors'] ?? array() ),
					function ( $row ) use ( $tokens ) {
						return empty( $row['_id'] ) || ! isset( $tokens['colors'][ $row['_id'] ] );
					}
				)
			)
		),
		'system_typography'               => $typo,
		'default_generic_fonts'           => '"Beleon Sans Fallback", system-ui, sans-serif',
		'body_typography_typography'      => 'custom',
		'body_typography_font_family'     => 'Beleon Sans',
		'body_color'                      => '#252C40',
		'container_width'                 => array(
			'unit'  => 'px',
			'size'  => 1280,
			'sizes' => array(),
		),
		'space_between_widgets'           => array(
			'unit'   => 'px',
			'size'   => 20,
			'column' => '20',
			'row'    => '20',
		),
		'page_title_selector'             => 'h1.bl-entry__title',
		'viewport_md'                     => 768,
		'viewport_lg'                     => 1025,
	);
	foreach ( array( 'h1', 'h2', 'h3', 'h4' ) as $h ) {
		$new[ $h . '_typography_typography' ]  = 'custom';
		$new[ $h . '_typography_font_family' ] = 'Beleon Serif';
		$new[ $h . '_typography_font_weight' ] = '400';
	}
	$kit->update_settings( $new );
	\Elementor\Plugin::$instance->files_manager->clear_cache();
	update_option( 'beleon_elementor_setup', BELEON_VERSION );
}
add_action(
	'after_switch_theme',
	function () {
		if ( ! get_option( 'beleon_elementor_setup' ) ) {
			beleon_setup_elementor();
		}
	},
	20
);
add_action(
	'admin_init',
	function () {
		if ( did_action( 'elementor/loaded' ) && ! get_option( 'beleon_elementor_setup' ) && current_user_can( 'manage_options' ) ) {
			beleon_setup_elementor();
		}
	}
);
