<?php
/**
 * Beleon Tour includes: included / not included columns.
 *
 * @package beleon-tours
 */

namespace Beleon\Elementor;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Tour_Includes extends Widget {

	public function get_name() {
		return 'beleon-tour-includes';
	}

	public function get_title() {
		return __( 'Beleon Tour included / not included', 'beleon-tours' );
	}

	public function get_icon() {
		return 'eicon-check-circle';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Labels', 'beleon-tours' ) ) );
		$this->add_control(
			'in_title',
			array(
				'label'   => __( 'Included title', 'beleon-tours' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Included', 'beleon-tours' ),
			)
		);
		$this->add_control(
			'out_title',
			array(
				'label'   => __( 'Not included title', 'beleon-tours' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Not included', 'beleon-tours' ),
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
		$this->text_style( 'h_s', __( 'Titles', 'beleon-tours' ), '.bl-incl h3' );
		$this->text_style( 'li_s', __( 'Items', 'beleon-tours' ), '.bl-incl li' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo beleon_render_includes( // phpcs:ignore WordPress.Security.EscapeOutput
			beleon_context_post( 'tours' ),
			array(
				'in_title'  => $s['in_title'],
				'out_title' => $s['out_title'],
			)
		);
	}
}
