<?php
/**
 * Beleon Tour booking card: price, departure dates and the enquiry button.
 *
 * @package beleon-tours
 */

namespace Beleon\Elementor;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Tour_Booking extends Widget {

	public function get_name() {
		return 'beleon-tour-booking';
	}

	public function get_title() {
		return __( 'Beleon Tour booking card', 'beleon-tours' );
	}

	public function get_icon() {
		return 'eicon-price-table';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'beleon-tours' ) ) );
		$this->add_control(
			'title',
			array(
				'label'   => __( 'Dates label', 'beleon-tours' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Departures', 'beleon-tours' ),
			)
		);
		$this->add_control(
			'button',
			array(
				'label'       => __( 'Button text', 'beleon-tours' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Request availability', 'beleon-tours' ),
				'description' => __( 'Links to the enquiry page set in Customize > Beleon > Tours & booking.', 'beleon-tours' ),
			)
		);
		$this->add_control(
			'max_dates',
			array(
				'label'   => __( 'Max dates', 'beleon-tours' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 8,
				'min'     => 1,
			)
		);
		$this->add_control(
			'show_phone',
			array(
				'label'        => __( 'Phone', 'beleon-tours' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);
		$this->add_control(
			'sticky',
			array(
				'label'        => __( 'Stick while scrolling', 'beleon-tours' ),
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
		$this->add_control(
			'card_bg',
			array(
				'label'     => __( 'Card background', 'beleon-tours' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .bl-book' => 'background: {{VALUE}};' ),
			)
		);
		$this->text_style( 'price_s', __( 'Price', 'beleon-tours' ), '.bl-book__price' );
		$this->end_controls_section();
		$this->button_style_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo beleon_render_booking( // phpcs:ignore WordPress.Security.EscapeOutput
			beleon_context_post( 'tours' ),
			array(
				'title'      => $s['title'],
				'button'     => $s['button'],
				'max_dates'  => $s['max_dates'],
				'show_phone' => 'yes' === $s['show_phone'],
				'sticky'     => 'yes' === $s['sticky'],
			)
		);
	}
}
