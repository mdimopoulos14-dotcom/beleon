<?php
/**
 * Beleon Tours: grid or carousel of tour cards from the tours post type.
 *
 * @package beleon-tours
 */

namespace Beleon\Elementor;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Tours extends Widget {

	public function get_name() {
		return 'beleon-tours';
	}

	public function get_title() {
		return __( 'Beleon Tours', 'beleon-tours' );
	}

	public function get_icon() {
		return 'eicon-posts-grid';
	}

	protected function register_controls() {
		$this->start_controls_section( 'query', array( 'label' => __( 'Tours', 'beleon-tours' ) ) );
		$this->add_control(
			'source',
			array(
				'label'   => __( 'Show', 'beleon-tours' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'upcoming',
				'options' => array(
					'upcoming'    => __( 'Next departures first', 'beleon-tours' ),
					'latest'      => __( 'Newest', 'beleon-tours' ),
					'menu'        => __( 'Manual order (Order field)', 'beleon-tours' ),
					'manual'      => __( 'Hand-picked', 'beleon-tours' ),
					'destination' => __( 'From one destination', 'beleon-tours' ),
					'related'     => __( 'Related to the current tour', 'beleon-tours' ),
				),
			)
		);
		$this->add_control(
			'ids',
			array(
				'label'       => __( 'Tours', 'beleon-tours' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->post_options( 'tours' ),
				'condition'   => array( 'source' => 'manual' ),
			)
		);
		$this->add_control(
			'destination',
			array(
				'label'       => __( 'Destination', 'beleon-tours' ),
				'type'        => Controls_Manager::SELECT2,
				'label_block' => true,
				'options'     => array( '0' => __( 'Current destination page', 'beleon-tours' ) ) + $this->post_options( 'destinations' ),
				'default'     => '0',
				'condition'   => array( 'source' => 'destination' ),
			)
		);
		$this->add_control(
			'count',
			array(
				'label'   => __( 'Number of tours', 'beleon-tours' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 48,
				'default' => 6,
			)
		);
		$this->add_control(
			'filters',
			array(
				'label'        => __( 'Filter bar (destination & month)', 'beleon-tours' ),
				'description'  => __( 'Shows filter chips and follows the hero search. Use on your "All tours" page.', 'beleon-tours' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
			)
		);
		$this->add_control(
			'empty',
			array(
				'label'   => __( 'Text when there are no tours', 'beleon-tours' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'New departures are being prepared. Contact us for tailor-made dates.', 'beleon-tours' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'card', array( 'label' => __( 'Layout & card', 'beleon-tours' ) ) );
		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'beleon-tours' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => array(
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
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
				),
				'selectors'      => array( '{{WRAPPER}}' => '--bl-cols: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'style',
			array(
				'label'   => __( 'Card style', 'beleon-tours' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'classic',
				'options' => array(
					'classic' => __( 'Image + text below', 'beleon-tours' ),
					'overlay' => __( 'Text over image', 'beleon-tours' ),
				),
			)
		);
		$this->add_control(
			'ratio',
			array(
				'label'   => __( 'Image ratio', 'beleon-tours' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '4/5',
				'options' => array(
					'4/5'  => '4:5',
					'3/4'  => '3:4',
					'1/1'  => '1:1',
					'4/3'  => '4:3',
					'16/10' => '16:10',
				),
			)
		);
		foreach ( array(
			'show_place'   => __( 'Destination', 'beleon-tours' ),
			'show_badge'   => __( 'Badge', 'beleon-tours' ),
			'show_facts'   => __( 'Duration & next date', 'beleon-tours' ),
			'show_price'   => __( 'Price', 'beleon-tours' ),
			'show_excerpt' => __( 'Excerpt', 'beleon-tours' ),
		) as $key => $label ) {
			$this->add_control(
				$key,
				array(
					'label'        => $label,
					'type'         => Controls_Manager::SWITCHER,
					'default'      => 'show_excerpt' === $key ? '' : 'yes',
					'return_value' => 'yes',
				)
			);
		}
		$this->add_control(
			'price_label',
			array(
				'label'     => __( 'Price prefix', 'beleon-tours' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'from', 'beleon-tours' ),
				'condition' => array( 'show_price' => 'yes' ),
			)
		);
		$this->add_control(
			'heading',
			array(
				'label'   => __( 'Title tag', 'beleon-tours' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h3',
				'options' => array(
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'p'  => 'p',
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'style_card',
			array(
				'label' => __( 'Card style', 'beleon-tours' ),
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
		$this->add_responsive_control(
			'radius',
			array(
				'label'      => __( 'Image radius', 'beleon-tours' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'  => array( '{{WRAPPER}} .bl-tcard__media, {{WRAPPER}} .bl-tcard--overlay' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->text_style( 'place_s', __( 'Destination', 'beleon-tours' ), '.bl-tcard__place' );
		$this->text_style( 'title_s', __( 'Title', 'beleon-tours' ), '.bl-tcard__title', true );
		$this->text_style( 'facts_s', __( 'Facts', 'beleon-tours' ), '.bl-tcard__facts' );
		$this->text_style( 'price_s', __( 'Price', 'beleon-tours' ), '.bl-tcard__price' );
		$this->add_control(
			'badge_bg',
			array(
				'label'     => __( 'Badge background', 'beleon-tours' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array( '{{WRAPPER}} .bl-badge' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'arrow_bg',
			array(
				'label'     => __( 'Arrow hover background', 'beleon-tours' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .bl-tcard:hover .bl-arrow' => 'background-color: {{VALUE}}; border-color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$s   = $this->get_settings_for_display();
		$ids = beleon_query_tours(
			array(
				'source'      => $s['source'],
				'count'       => $s['count'],
				'ids'         => $s['ids'],
				'destination' => (int) $s['destination'],
				'url_filters' => 'yes' === $s['filters'],
			)
		);
		if ( 'yes' === $s['filters'] ) {
			echo beleon_render_tour_filters(); // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo beleon_render_tours( // phpcs:ignore WordPress.Security.EscapeOutput
			$ids,
			array(
				'layout'       => $s['layout'],
				'style'        => $s['style'],
				'ratio'        => $s['ratio'],
				'show_place'   => 'yes' === $s['show_place'],
				'show_badge'   => 'yes' === $s['show_badge'],
				'show_facts'   => 'yes' === $s['show_facts'],
				'show_price'   => 'yes' === $s['show_price'],
				'show_excerpt' => 'yes' === $s['show_excerpt'],
				'price_label'  => $s['price_label'],
				'heading'      => $s['heading'],
				'empty'        => $s['empty'],
			)
		);
	}
}
