<?php
/**
 * Beleon Tour itinerary: day-by-day timeline (accordion, no JavaScript).
 *
 * @package beleon-tours
 */

namespace Beleon\Elementor;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Tour_Itinerary extends Widget {

	public function get_name() {
		return 'beleon-tour-itinerary';
	}

	public function get_title() {
		return __( 'Beleon Tour itinerary', 'beleon-tours' );
	}

	public function get_icon() {
		return 'eicon-time-line';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Itinerary', 'beleon-tours' ) ) );
		$this->add_control(
			'day_label',
			array(
				'label'   => __( 'Day label', 'beleon-tours' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Day', 'beleon-tours' ),
			)
		);
		$this->add_control(
			'open',
			array(
				'label'   => __( 'Open', 'beleon-tours' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'first',
				'options' => array(
					'first' => __( 'First day', 'beleon-tours' ),
					'all'   => __( 'All days', 'beleon-tours' ),
					'none'  => __( 'None', 'beleon-tours' ),
				),
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
		$this->text_style( 'num_s', __( 'Day number', 'beleon-tours' ), '.bl-itin__num' );
		$this->text_style( 'title_s', __( 'Day title', 'beleon-tours' ), '.bl-itin__title' );
		$this->text_style( 'text_s', __( 'Description', 'beleon-tours' ), '.bl-itin__text' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo beleon_render_itinerary( // phpcs:ignore WordPress.Security.EscapeOutput
			beleon_context_post( 'tours' ),
			array(
				'day_label'  => $s['day_label'],
				'open_first' => 'first' === $s['open'],
				'open_all'   => 'all' === $s['open'],
			)
		);
	}
}
