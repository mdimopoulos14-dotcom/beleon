<?php
/**
 * Beleon Tour highlights: checklist of the tour's best moments.
 *
 * @package beleon-tours
 */

namespace Beleon\Elementor;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Tour_Highlights extends Widget {

	public function get_name() {
		return 'beleon-tour-highlights';
	}

	public function get_title() {
		return __( 'Beleon Tour highlights', 'beleon-tours' );
	}

	public function get_icon() {
		return 'eicon-star';
	}

	protected function register_controls() {
		$this->start_controls_section(
			'style',
			array(
				'label' => __( 'Style', 'beleon-tours' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->tone_control();
		$this->add_responsive_control(
			'columns',
			array(
				'label'     => __( 'Columns', 'beleon-tours' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '2',
				'options'   => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
				),
				'selectors' => array( '{{WRAPPER}}' => '--bl-cols: {{VALUE}};' ),
			)
		);
		$this->text_style( 'li_s', __( 'Items', 'beleon-tours' ), '.bl-highlights li' );
		$this->end_controls_section();
	}

	protected function render() {
		echo beleon_render_highlights( beleon_context_post( 'tours' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
}
