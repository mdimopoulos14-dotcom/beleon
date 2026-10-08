<?php
/**
 * Beleon CTA: closing call to action band.
 *
 * @package beleon-tours
 */

namespace Beleon\Elementor;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cta extends Widget {

	public function get_name() {
		return 'beleon-cta';
	}

	public function get_title() {
		return __( 'Beleon Call to action', 'beleon-tours' );
	}

	public function get_icon() {
		return 'eicon-call-to-action';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'beleon-tours' ) ) );
		$this->add_control(
			'eyebrow',
			array(
				'label'   => __( 'Eyebrow', 'beleon-tours' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Your next journey', 'beleon-tours' ),
			)
		);
		$this->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'beleon-tours' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 2,
				'default' => __( 'Many people travel. Few travel *like this*.', 'beleon-tours' ),
			)
		);
		$this->add_control(
			'text',
			array(
				'label' => __( 'Text', 'beleon-tours' ),
				'type'  => Controls_Manager::TEXTAREA,
			)
		);
		$this->button_controls( 'btn1', __( 'Main button', 'beleon-tours' ), __( 'Plan your trip', 'beleon-tours' ), '/contact/' );
		$this->button_controls( 'btn2', __( 'Second button', 'beleon-tours' ), '', '' );
		$this->end_controls_section();

		$this->start_controls_section(
			'design',
			array(
				'label' => __( 'Design', 'beleon-tours' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'surface',
			array(
				'label'   => __( 'Background', 'beleon-tours' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'night',
				'options' => array(
					'night' => __( 'Night', 'beleon-tours' ),
					'lapis' => __( 'Lapis', 'beleon-tours' ),
					'gold'  => __( 'Champagne gold', 'beleon-tours' ),
					'sand'  => __( 'Sand', 'beleon-tours' ),
					'ivory' => __( 'Ivory', 'beleon-tours' ),
				),
			)
		);
		$this->add_control(
			'image',
			array(
				'label' => __( 'Background image (optional)', 'beleon-tours' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);
		$this->add_control(
			'align',
			array(
				'label'   => __( 'Alignment', 'beleon-tours' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'center',
				'options' => array(
					'center' => __( 'Center', 'beleon-tours' ),
					'left'   => __( 'Left', 'beleon-tours' ),
				),
			)
		);
		$this->add_responsive_control(
			'padding',
			array(
				'label'      => __( 'Padding', 'beleon-tours' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array( '{{WRAPPER}} .bl-cta' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'radius',
			array(
				'label'      => __( 'Radius', 'beleon-tours' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'selectors'  => array( '{{WRAPPER}} .bl-cta' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->text_style( 'title_s', __( 'Title', 'beleon-tours' ), '.bl-cta__title' );
		$this->text_style( 'text_s', __( 'Text', 'beleon-tours' ), '.bl-cta__text' );
		$this->end_controls_section();
		$this->button_style_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo beleon_render_cta( // phpcs:ignore WordPress.Security.EscapeOutput
			array(
				'eyebrow' => $s['eyebrow'],
				'title'   => $s['title'],
				'text'    => $s['text'],
				'btn1'    => $s['btn1_text'],
				'link1'   => $s['btn1_link'],
				'btn2'    => $s['btn2_text'],
				'link2'   => $s['btn2_link'],
				'surface' => $s['surface'],
				'image'   => $this->media_id( $s['image'] ),
				'align'   => $s['align'],
			)
		);
	}
}
