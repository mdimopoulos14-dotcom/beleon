<?php
/**
 * Beleon Marquee: endless ticker of words (destinations, promises). Pure CSS.
 *
 * @package beleon-tours
 */

namespace Beleon\Elementor;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Marquee extends Widget {

	public function get_name() {
		return 'beleon-marquee';
	}

	public function get_title() {
		return __( 'Beleon Marquee', 'beleon-tours' );
	}

	public function get_icon() {
		return 'eicon-animation-text';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'beleon-tours' ) ) );
		$this->add_control(
			'source',
			array(
				'label'   => __( 'Words from', 'beleon-tours' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'destinations',
				'options' => array(
					'destinations' => __( 'Destination names', 'beleon-tours' ),
					'custom'       => __( 'My list', 'beleon-tours' ),
				),
			)
		);
		$this->add_control(
			'words',
			array(
				'label'       => __( 'Words (one per line)', 'beleon-tours' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 6,
				'default'     => "Georgia\nArmenia\nUzbekistan\nNepal\nBhutan\nMongolia",
				'condition'   => array( 'source' => 'custom' ),
			)
		);
		$this->add_control(
			'separator',
			array(
				'label'   => __( 'Separator', 'beleon-tours' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'star',
				'options' => array(
					'star' => '✦',
					'dot'  => '•',
					'dash' => '—',
				),
			)
		);
		$this->add_control(
			'speed',
			array(
				'label'     => __( 'Duration of one loop (seconds)', 'beleon-tours' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 10, 'max' => 120 ) ),
				'default'   => array( 'size' => 40 ),
				'selectors' => array( '{{WRAPPER}} .bl-marquee' => '--bl-speed: {{SIZE}}s;' ),
			)
		);
		$this->add_control(
			'reverse',
			array(
				'label'        => __( 'Reverse direction', 'beleon-tours' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
			)
		);
		$this->add_control(
			'outline',
			array(
				'label'        => __( 'Alternate outlined words', 'beleon-tours' ),
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
		$this->text_style( 'word_s', __( 'Words', 'beleon-tours' ), '.bl-marquee__item' );
		$this->add_control(
			'sep_color',
			array(
				'label'     => __( 'Separator color', 'beleon-tours' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .bl-marquee__sep' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		if ( 'destinations' === $s['source'] ) {
			$words = array_map( 'get_the_title', beleon_query_destinations( array( 'count' => 20 ) ) );
		} else {
			$words = array_filter( array_map( 'trim', preg_split( '/\R/u', (string) $s['words'] ) ) );
		}
		if ( ! $words ) {
			return;
		}
		$sep  = array(
			'star' => '✦',
			'dot'  => '•',
			'dash' => '—',
		);
		$sep  = '<span class="bl-marquee__sep" aria-hidden="true">' . ( isset( $sep[ $s['separator'] ] ) ? $sep[ $s['separator'] ] : '✦' ) . '</span>';
		$line = '';
		foreach ( array_values( $words ) as $i => $word ) {
			$line .= '<span class="bl-marquee__item' . ( 'yes' === $s['outline'] && $i % 2 ? ' is-outline' : '' ) . '">' . esc_html( $word ) . '</span>' . $sep;
		}
		echo '<div class="bl-marquee' . ( 'yes' === $s['reverse'] ? ' is-reverse' : '' ) . '" role="marquee" aria-label="' . esc_attr( implode( ', ', $words ) ) . '">';
		echo '<div class="bl-marquee__track" aria-hidden="true"><div class="bl-marquee__group">' . $line . '</div><div class="bl-marquee__group">' . $line . '</div></div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}
