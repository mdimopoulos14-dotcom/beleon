<?php
/**
 * Beleon Tour facts: duration, departure city, next date, group, price.
 *
 * @package beleon-tours
 */

namespace Beleon\Elementor;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Tour_Facts extends Widget {

	public function get_name() {
		return 'beleon-tour-facts';
	}

	public function get_title() {
		return __( 'Beleon Tour facts', 'beleon-tours' );
	}

	public function get_icon() {
		return 'eicon-bullet-list';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Facts', 'beleon-tours' ) ) );
		$this->add_control(
			'items',
			array(
				'label'    => __( 'Show', 'beleon-tours' ),
				'type'     => Controls_Manager::SELECT2,
				'multiple' => true,
				'default'  => array( 'duration', 'departure_city', 'next', 'group_size' ),
				'options'  => array(
					'duration'       => __( 'Duration', 'beleon-tours' ),
					'departure_city' => __( 'Departs from', 'beleon-tours' ),
					'next'           => __( 'Next departure', 'beleon-tours' ),
					'group_size'     => __( 'Group', 'beleon-tours' ),
					'price'          => __( 'Price', 'beleon-tours' ),
				),
			)
		);
		$this->add_control(
			'style',
			array(
				'label'   => __( 'Style', 'beleon-tours' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'bar',
				'options' => array(
					'bar'   => __( 'Bar with dividers', 'beleon-tours' ),
					'glass' => __( 'Glass (over images)', 'beleon-tours' ),
					'list'  => __( 'Vertical list', 'beleon-tours' ),
				),
			)
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'style_s',
			array(
				'label' => __( 'Style', 'beleon-tours' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->tone_control();
		$this->add_control(
			'icon_color',
			array(
				'label'     => __( 'Icon color', 'beleon-tours' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .bl-facts .bl-ico' => 'color: {{VALUE}};' ),
			)
		);
		$this->text_style( 'label_s', __( 'Labels', 'beleon-tours' ), '.bl-facts small' );
		$this->text_style( 'value_s', __( 'Values', 'beleon-tours' ), '.bl-facts li > span' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo beleon_render_facts( // phpcs:ignore WordPress.Security.EscapeOutput
			beleon_context_post( 'tours' ),
			array(
				'items' => (array) $s['items'],
				'style' => $s['style'],
			)
		);
	}
}
