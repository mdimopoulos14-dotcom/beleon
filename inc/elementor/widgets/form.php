<?php
/**
 * Beleon Form: contact or tour booking form (submissions go to Beleon > Enquiries and by email).
 *
 * @package beleon-tours
 */

namespace Beleon\Elementor;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Form extends Widget {

	public function get_name() {
		return 'beleon-form';
	}

	public function get_title() {
		return __( 'Beleon Form', 'beleon-tours' );
	}

	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	public function get_keywords() {
		return array( 'form', 'contact', 'booking', 'enquiry', 'φόρμα' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'beleon-tours' ) ) );
		$this->add_control(
			'type',
			array(
				'label'   => __( 'Form', 'beleon-tours' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'contact',
				'options' => array(
					'contact' => __( 'Contact', 'beleon-tours' ),
					'booking' => __( 'Booking (on tour pages and tour templates)', 'beleon-tours' ),
				),
			)
		);
		$this->add_control(
			'button',
			array(
				'label'       => __( 'Button text', 'beleon-tours' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Send message', 'beleon-tours' ),
			)
		);
		$this->add_control(
			'note',
			array(
				'type' => Controls_Manager::RAW_HTML,
				'raw'  => esc_html__( 'Messages are saved under Beleon > Enquiries and emailed to the address in Customize > Beleon > Tours & booking.', 'beleon-tours' ),
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
		$this->text_style( 'label_s', __( 'Labels', 'beleon-tours' ), '.bl-form label' );
		$this->end_controls_section();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$o    = array( 'id' => 'bl-form-' . $this->get_id() );
		$type = 'booking' === $s['type'] ? 'booking' : 'contact';
		if ( 'booking' === $type ) {
			$tour      = beleon_context_post( 'tours' );
			$o['tour'] = $tour ? $tour : 0;
		}
		if ( ! empty( $s['button'] ) ) {
			$o['button'] = $s['button'];
		}
		echo beleon_render_form( $type, $o ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
}
