<?php
/**
 * Beleon Features: reasons-to-choose list with icons or numbers.
 *
 * @package beleon-tours
 */

namespace Beleon\Elementor;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Features extends Widget {

	public function get_name() {
		return 'beleon-features';
	}

	public function get_title() {
		return __( 'Beleon Features', 'beleon-tours' );
	}

	public function get_icon() {
		return 'eicon-icon-box';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Items', 'beleon-tours' ) ) );
		$r = new Repeater();
		$r->add_control(
			'icon',
			array(
				'label'   => __( 'Icon', 'beleon-tours' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'compass',
				'options' => array( '' => __( 'Number instead', 'beleon-tours' ) ) + beleon_icon_options(),
			)
		);
		$r->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'beleon-tours' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Feature', 'beleon-tours' ),
			)
		);
		$r->add_control(
			'text',
			array(
				'label' => __( 'Text', 'beleon-tours' ),
				'type'  => Controls_Manager::TEXTAREA,
			)
		);
		$this->add_control(
			'items',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $r->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array(
						'icon'  => 'shield',
						'title' => __( 'Safety, first and always', 'beleon-tours' ),
						'text'  => __( 'Trusted local partners and guides we have worked with for years.', 'beleon-tours' ),
					),
					array(
						'icon'  => 'compass',
						'title' => __( 'Routes we know by heart', 'beleon-tours' ),
						'text'  => __( 'Every itinerary is travelled by our team before it is offered.', 'beleon-tours' ),
					),
					array(
						'icon'  => 'bed',
						'title' => __( 'Comfort without compromise', 'beleon-tours' ),
						'text'  => __( 'From characterful guesthouses to the best hotels each region allows.', 'beleon-tours' ),
					),
				),
			)
		);
		$this->add_control(
			'variant',
			array(
				'label'     => __( 'Style', 'beleon-tours' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'lines',
				'separator' => 'before',
				'options'   => array(
					'lines' => __( 'Minimal with gold rules', 'beleon-tours' ),
					'cards' => __( 'Cards', 'beleon-tours' ),
					'glass' => __( 'Glass (on dark/image)', 'beleon-tours' ),
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
		$this->end_controls_section();

		$this->start_controls_section(
			'style',
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
				'selectors' => array( '{{WRAPPER}} .bl-feature__ico' => 'color: {{VALUE}};' ),
			)
		);
		$this->text_style( 'title_s', __( 'Title', 'beleon-tours' ), '.bl-feature__title' );
		$this->text_style( 'text_s', __( 'Text', 'beleon-tours' ), '.bl-feature__text' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo '<div class="bl-features bl-features--' . esc_attr( $s['variant'] ) . ' bl-stagger">';
		foreach ( (array) $s['items'] as $i => $item ) {
			echo '<div class="bl-feature">';
			echo '<span class="bl-feature__ico">' . ( $item['icon'] ? beleon_icon( $item['icon'] ) : esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ) ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
			echo '<h3 class="bl-feature__title">' . esc_html( $item['title'] ) . '</h3>';
			if ( $item['text'] ) {
				echo '<p class="bl-feature__text">' . esc_html( $item['text'] ) . '</p>';
			}
			echo '</div>';
		}
		echo '</div>';
	}
}
