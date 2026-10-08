<?php
/**
 * Beleon Contact details: phones, email, offices and socials from the Customizer.
 *
 * @package beleon-tours
 */

namespace Beleon\Elementor;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Contact extends Widget {

	public function get_name() {
		return 'beleon-contact';
	}

	public function get_title() {
		return __( 'Beleon Contact details', 'beleon-tours' );
	}

	public function get_icon() {
		return 'eicon-email-field';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'beleon-tours' ) ) );
		$this->add_control(
			'note',
			array(
				'type' => Controls_Manager::RAW_HTML,
				'raw'  => esc_html__( 'Details come from Appearance > Customize > Beleon, so they stay identical everywhere on the site.', 'beleon-tours' ),
			)
		);
		$this->add_control(
			'show',
			array(
				'label'    => __( 'Show', 'beleon-tours' ),
				'type'     => Controls_Manager::SELECT2,
				'multiple' => true,
				'default'  => array( 'phones', 'email', 'addresses', 'socials' ),
				'options'  => array(
					'phones'    => __( 'Phones', 'beleon-tours' ),
					'email'     => __( 'Email', 'beleon-tours' ),
					'addresses' => __( 'Offices', 'beleon-tours' ),
					'socials'   => __( 'Social icons', 'beleon-tours' ),
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
		$this->text_style( 'label_s', __( 'Labels', 'beleon-tours' ), '.bl-contact__item small' );
		$this->text_style( 'value_s', __( 'Values', 'beleon-tours' ), '.bl-contact__item a, {{WRAPPER}} .bl-contact__item div' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo beleon_render_contact( array( 'show' => (array) $s['show'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
}
