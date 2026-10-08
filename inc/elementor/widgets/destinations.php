<?php
/**
 * Beleon Destinations: arches, mosaic, grid or carousel of destination posts.
 *
 * @package beleon-tours
 */

namespace Beleon\Elementor;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Destinations extends Widget {

	public function get_name() {
		return 'beleon-destinations';
	}

	public function get_title() {
		return __( 'Beleon Destinations', 'beleon-tours' );
	}

	public function get_icon() {
		return 'eicon-gallery-masonry';
	}

	protected function register_controls() {
		$this->start_controls_section( 'query', array( 'label' => __( 'Destinations', 'beleon-tours' ) ) );
		$this->add_control(
			'source',
			array(
				'label'   => __( 'Show', 'beleon-tours' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'all',
				'options' => array(
					'all'    => __( 'All (by Order field)', 'beleon-tours' ),
					'manual' => __( 'Hand-picked', 'beleon-tours' ),
				),
			)
		);
		$this->add_control(
			'ids',
			array(
				'label'       => __( 'Destinations', 'beleon-tours' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->post_options( 'destinations' ),
				'condition'   => array( 'source' => 'manual' ),
			)
		);
		$this->add_control(
			'count',
			array(
				'label'   => __( 'Number', 'beleon-tours' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 60,
				'default' => 8,
			)
		);
		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'beleon-tours' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'arches',
				'options' => array(
					'arches'   => __( 'Arches', 'beleon-tours' ),
					'mosaic'   => __( 'Mosaic', 'beleon-tours' ),
					'grid'     => __( 'Grid', 'beleon-tours' ),
					'carousel' => __( 'Carousel (swipe)', 'beleon-tours' ),
				),
			)
		);
		$this->add_responsive_control(
			'columns',
			array(
				'label'          => __( 'Columns', 'beleon-tours' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '4',
				'tablet_default' => '3',
				'mobile_default' => '2',
				'options'        => array(
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				),
				'selectors'      => array( '{{WRAPPER}}' => '--bl-cols: {{VALUE}};' ),
				'condition'      => array( 'layout!' => 'mosaic' ),
			)
		);
		$this->add_control(
			'show_count',
			array(
				'label'        => __( 'Number of tours', 'beleon-tours' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);
		$this->add_control(
			'show_region',
			array(
				'label'        => __( 'Region', 'beleon-tours' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'style',
			array(
				'label' => __( 'Style', 'beleon-tours' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->tone_control();
		$this->add_responsive_control(
			'gap',
			array(
				'label'      => __( 'Gap', 'beleon-tours' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'selectors'  => array( '{{WRAPPER}}' => '--bl-gap: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->text_style( 'region_s', __( 'Region', 'beleon-tours' ), '.bl-dcard__region' );
		$this->text_style( 'title_s', __( 'Name', 'beleon-tours' ), '.bl-dcard__title' );
		$this->text_style( 'count_s', __( 'Tours count', 'beleon-tours' ), '.bl-dcard__count' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo beleon_render_destinations( // phpcs:ignore WordPress.Security.EscapeOutput
			beleon_query_destinations(
				array(
					'source' => $s['source'],
					'count'  => $s['count'],
					'ids'    => $s['ids'],
				)
			),
			array(
				'layout'      => $s['layout'],
				'show_count'  => 'yes' === $s['show_count'],
				'show_region' => 'yes' === $s['show_region'],
			)
		);
	}
}
