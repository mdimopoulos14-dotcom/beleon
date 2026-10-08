<?php
/**
 * Beleon Hero: full-bleed image hero with animated title, search and shapes.
 *
 * @package beleon-tours
 */

namespace Beleon\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Hero extends Widget {

	public function get_name() {
		return 'beleon-hero';
	}

	public function get_title() {
		return __( 'Beleon Hero', 'beleon-tours' );
	}

	public function get_icon() {
		return 'eicon-header';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'beleon-tours' ) ) );
		$this->add_control(
			'eyebrow',
			array(
				'label'   => __( 'Eyebrow', 'beleon-tours' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Since 2003 · Small-group journeys', 'beleon-tours' ),
			)
		);
		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'beleon-tours' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'default'     => __( 'Journeys you will *not* find anywhere else', 'beleon-tours' ),
				'description' => __( 'Wrap words in *asterisks* for the italic gold accent.', 'beleon-tours' ),
			)
		);
		$this->add_control(
			'text',
			array(
				'label'   => __( 'Text', 'beleon-tours' ),
				'type'    => Controls_Manager::TEXTAREA,
				'default' => __( 'From the Caucasus and the Silk Road to the Himalaya — designed and led by people who know every route.', 'beleon-tours' ),
			)
		);
		$this->button_controls( 'btn1', __( 'Main button', 'beleon-tours' ), __( 'Explore journeys', 'beleon-tours' ), '/tours/' );
		$this->button_controls( 'btn2', __( 'Second button', 'beleon-tours' ), __( 'Talk to us', 'beleon-tours' ), '/contact/' );
		$this->add_control(
			'search',
			array(
				'label'        => __( 'Show tour search', 'beleon-tours' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'separator'    => 'before',
				'return_value' => 'yes',
			)
		);
		$this->add_control(
			'search_button',
			array(
				'label'     => __( 'Search button', 'beleon-tours' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Find a journey', 'beleon-tours' ),
				'condition' => array( 'search' => 'yes' ),
			)
		);
		$this->add_control(
			'scroll_cue',
			array(
				'label'        => __( 'Scroll cue', 'beleon-tours' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'media', array( 'label' => __( 'Image', 'beleon-tours' ) ) );
		$this->add_control(
			'image',
			array(
				'label'       => __( 'Background image', 'beleon-tours' ),
				'type'        => Controls_Manager::MEDIA,
				'description' => __( 'Loaded with high priority (it is the largest element on screen). 2000px wide is plenty.', 'beleon-tours' ),
			)
		);
		$this->add_control(
			'image_mobile',
			array(
				'label' => __( 'Mobile image (optional, portrait)', 'beleon-tours' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);
		$this->add_control(
			'kenburns',
			array(
				'label'        => __( 'Slow zoom', 'beleon-tours' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);
		$this->add_control(
			'shapes',
			array(
				'label'   => __( 'Decorative shape', 'beleon-tours' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'arch',
				'options' => array(
					''      => __( 'None', 'beleon-tours' ),
					'arch'  => __( 'Silk Road arch', 'beleon-tours' ),
					'rings' => __( 'Orbit rings', 'beleon-tours' ),
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'layout',
			array(
				'label' => __( 'Layout', 'beleon-tours' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'height',
			array(
				'label'      => __( 'Height', 'beleon-tours' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'vh', 'px' ),
				'range'      => array(
					'vh' => array( 'min' => 40, 'max' => 100 ),
					'px' => array( 'min' => 400, 'max' => 1200 ),
				),
				'default'    => array(
					'unit' => 'vh',
					'size' => 100,
				),
				'selectors'  => array( '{{WRAPPER}} .bl-hero' => 'min-height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'align',
			array(
				'label'        => __( 'Alignment', 'beleon-tours' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => array(
					'left'   => array(
						'title' => __( 'Left', 'beleon-tours' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'beleon-tours' ),
						'icon'  => 'eicon-text-align-center',
					),
				),
				'default'      => 'left',
				'prefix_class' => 'bl-hero-align-',
			)
		);
		$this->add_responsive_control(
			'content_width',
			array(
				'label'      => __( 'Text width', 'beleon-tours' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'ch' ),
				'range'      => array( 'px' => array( 'min' => 320, 'max' => 1200 ) ),
				'selectors'  => array( '{{WRAPPER}} .bl-hero__content' => 'max-width: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'overlay',
			array(
				'label'     => __( 'Image darkness', 'beleon-tours' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 100 ) ),
				'default'   => array( 'size' => 55 ),
				'selectors' => array( '{{WRAPPER}} .bl-hero' => '--bl-overlay: calc({{SIZE}} / 100);' ),
			)
		);
		$this->add_control(
			'overlay_color',
			array(
				'label'     => __( 'Overlay color', 'beleon-tours' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .bl-hero' => '--bl-overlay-color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'style_text',
			array(
				'label' => __( 'Typography', 'beleon-tours' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->text_style( 'eyebrow_s', __( 'Eyebrow', 'beleon-tours' ), '.bl-eyebrow' );
		$this->text_style( 'title_s', __( 'Title', 'beleon-tours' ), '.bl-hero__title' );
		$this->add_control(
			'accent_color',
			array(
				'label'     => __( 'Accent words color', 'beleon-tours' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .bl-accent' => 'color: {{VALUE}};' ),
			)
		);
		$this->text_style( 'text_s', __( 'Text', 'beleon-tours' ), '.bl-hero__text' );
		$this->end_controls_section();

		$this->button_style_section();
	}

	protected function render() {
		$s      = $this->get_settings_for_display();
		$img    = $this->media_id( $s['image'] );
		$mobile = $this->media_id( $s['image_mobile'] );
		$shape  = $s['shapes'] ? ' bl-hero--' . $s['shapes'] : '';
		$kb     = 'yes' === $s['kenburns'] ? ' bl-hero--kenburns' : '';

		echo '<section class="bl-hero' . esc_attr( $shape . $kb ) . '">';
		echo '<div class="bl-hero__media">';
		if ( $img ) {
			if ( $mobile ) {
				$src = wp_get_attachment_image_src( $mobile, 'beleon-card' );
				echo '<picture><source media="(max-width: 767px)" srcset="' . esc_url( $src ? $src[0] : '' ) . '">' . beleon_img( $img, 'beleon-wide', array( 'priority' => true, 'alt' => '', 'sizes' => '100vw' ) ) . '</picture>'; // phpcs:ignore WordPress.Security.EscapeOutput
			} else {
				echo beleon_img( $img, 'beleon-wide', array( 'priority' => true, 'alt' => '', 'sizes' => '100vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
		}
		echo '</div><div class="bl-hero__shape" aria-hidden="true"></div>';
		echo '<div class="bl-wrap bl-hero__in"><div class="bl-hero__content">';
		echo beleon_eyebrow( $s['eyebrow'], 'bl-hero__eyebrow' ); // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<h1 class="bl-hero__title">' . beleon_accent( $s['title'] ) . '</h1>'; // phpcs:ignore WordPress.Security.EscapeOutput
		if ( $s['text'] ) {
			echo '<p class="bl-hero__text">' . esc_html( $s['text'] ) . '</p>';
		}
		$buttons = beleon_button( $s['btn1_text'], $s['btn1_link'], 'solid' ) . beleon_button( $s['btn2_text'], $s['btn2_link'], 'ghost' );
		if ( $buttons ) {
			echo '<div class="bl-actions bl-hero__actions">' . $buttons . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '</div>';
		if ( 'yes' === $s['search'] ) {
			echo '<div class="bl-hero__search">' . beleon_render_search( array( 'button' => $s['search_button'] ) ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '</div>';
		if ( 'yes' === $s['scroll_cue'] ) {
			echo '<span class="bl-hero__cue" aria-hidden="true"><i></i></span>';
		}
		echo '</section>';
	}
}
