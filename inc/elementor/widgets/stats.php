<?php
/**
 * Beleon Stats: big serif numbers that count up when they scroll into view.
 *
 * @package beleon-tours
 */

namespace Beleon\Elementor;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Stats extends Widget {

	public function get_name() {
		return 'beleon-stats';
	}

	public function get_title() {
		return __( 'Beleon Stats', 'beleon-tours' );
	}

	public function get_icon() {
		return 'eicon-counter';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Numbers', 'beleon-tours' ) ) );
		$r = new Repeater();
		$r->add_control(
			'number',
			array(
				'label'   => __( 'Number', 'beleon-tours' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 20,
			)
		);
		$r->add_control(
			'prefix',
			array(
				'label' => __( 'Before', 'beleon-tours' ),
				'type'  => Controls_Manager::TEXT,
			)
		);
		$r->add_control(
			'suffix',
			array(
				'label' => __( 'After', 'beleon-tours' ),
				'type'  => Controls_Manager::TEXT,
			)
		);
		$r->add_control(
			'label',
			array(
				'label'   => __( 'Label', 'beleon-tours' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Label', 'beleon-tours' ),
			)
		);
		$this->add_control(
			'items',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $r->get_controls(),
				'title_field' => '{{{ prefix }}}{{{ number }}}{{{ suffix }}} — {{{ label }}}',
				'default'     => array(
					array(
						'number' => 2003,
						'label'  => __( 'Travelling since', 'beleon-tours' ),
					),
					array(
						'number' => 30,
						'suffix' => '+',
						'label'  => __( 'Countries', 'beleon-tours' ),
					),
					array(
						'number' => 12,
						'label'  => __( 'Travellers per group, on average', 'beleon-tours' ),
					),
					array(
						'number' => 2,
						'label'  => __( 'Offices: Thessaloniki & Athens', 'beleon-tours' ),
					),
				),
			)
		);
		$this->add_control(
			'count_up',
			array(
				'label'        => __( 'Count up animation', 'beleon-tours' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);
		$this->add_responsive_control(
			'columns',
			array(
				'label'          => __( 'Columns', 'beleon-tours' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '4',
				'tablet_default' => '2',
				'mobile_default' => '2',
				'options'        => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
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
		$this->text_style( 'num_s', __( 'Number', 'beleon-tours' ), '.bl-stat__num' );
		$this->text_style( 'label_s', __( 'Label', 'beleon-tours' ), '.bl-stat__label' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo '<div class="bl-stats bl-stagger">';
		foreach ( (array) $s['items'] as $item ) {
			$n     = is_numeric( $item['number'] ) ? $item['number'] + 0 : 0;
			$plain = $n >= 1900 && $n <= 2100; // Years: no thousands separator.
			$shown = $plain ? (string) $n : beleon_number( $n );
			echo '<div class="bl-stat"><p class="bl-stat__num">';
			echo esc_html( $item['prefix'] );
			echo '<span' . ( 'yes' === $s['count_up'] ? ' data-bl-count="' . esc_attr( $n ) . '"' . ( $plain ? ' data-bl-plain' : '' ) : '' ) . '>' . esc_html( $shown ) . '</span>';
			echo esc_html( $item['suffix'] ) . '</p>';
			echo '<p class="bl-stat__label">' . esc_html( $item['label'] ) . '</p></div>';
		}
		echo '</div>';
	}
}
