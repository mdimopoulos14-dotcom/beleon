<?php
/**
 * Beleon Offices: one card per office with address, hours, phone, email and
 * a directions link (no embedded map, so the page stays fast).
 *
 * @package beleon-tours
 */

namespace Beleon\Elementor;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Offices extends Widget {

	public function get_name() {
		return 'beleon-offices';
	}

	public function get_title() {
		return __( 'Beleon Offices', 'beleon-tours' );
	}

	public function get_icon() {
		return 'eicon-map-pin';
	}

	public function get_keywords() {
		return array( 'beleon', 'contact', 'office', 'address', 'map' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Offices', 'beleon-tours' ) ) );
		$r = new Repeater();
		$r->add_control(
			'label',
			array(
				'label'   => __( 'Label', 'beleon-tours' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Office', 'beleon-tours' ),
			)
		);
		$r->add_control(
			'city',
			array(
				'label'   => __( 'City', 'beleon-tours' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'City', 'beleon-tours' ),
			)
		);
		$r->add_control(
			'address',
			array(
				'label' => __( 'Address', 'beleon-tours' ),
				'type'  => Controls_Manager::TEXTAREA,
				'rows'  => 2,
			)
		);
		$r->add_control(
			'hours',
			array(
				'label' => __( 'Opening hours', 'beleon-tours' ),
				'type'  => Controls_Manager::TEXT,
			)
		);
		$r->add_control(
			'phone',
			array(
				'label' => __( 'Phone', 'beleon-tours' ),
				'type'  => Controls_Manager::TEXT,
			)
		);
		$r->add_control(
			'email',
			array(
				'label' => __( 'Email', 'beleon-tours' ),
				'type'  => Controls_Manager::TEXT,
			)
		);
		$r->add_control(
			'map',
			array(
				'label'       => __( 'Map search', 'beleon-tours' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'What to search on Google Maps for directions. Empty uses the address.', 'beleon-tours' ),
			)
		);
		$this->add_control(
			'items',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $r->get_controls(),
				'title_field' => '{{{ city }}}',
				'default'     => array(
					array(
						'city'    => __( 'Thessaloniki', 'beleon-tours' ),
						'address' => '',
					),
				),
			)
		);
		$this->add_control(
			'directions',
			array(
				'label'   => __( 'Directions button', 'beleon-tours' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Get directions', 'beleon-tours' ),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		if ( empty( $s['items'] ) ) {
			return;
		}
		echo '<div class="bl-offices bl-stagger">';
		foreach ( $s['items'] as $i => $o ) {
			$map = trim( (string) ( $o['map'] ? $o['map'] : preg_replace( '/\s+/', ' ', (string) $o['address'] ) ) );
			echo '<article class="bl-office bl-m-fade-up">';
			echo '<span class="bl-office__num" aria-hidden="true">' . esc_html( sprintf( '%02d', $i + 1 ) ) . '</span>';
			if ( $o['label'] ) {
				echo '<p class="bl-office__label">' . esc_html( $o['label'] ) . '</p>';
			}
			echo '<h3 class="bl-office__city">' . esc_html( $o['city'] ) . '</h3><ul class="bl-office__list">';
			if ( $o['address'] ) {
				echo '<li>' . beleon_icon( 'pin' ) . '<span>' . nl2br( esc_html( $o['address'] ) ) . '</span></li>'; // phpcs:ignore WordPress.Security.EscapeOutput
			}
			if ( $o['hours'] ) {
				echo '<li>' . beleon_icon( 'clock' ) . '<span>' . esc_html( $o['hours'] ) . '</span></li>'; // phpcs:ignore WordPress.Security.EscapeOutput
			}
			if ( $o['phone'] ) {
				echo '<li>' . beleon_icon( 'phone' ) . '<a href="tel:' . esc_attr( preg_replace( '/[^\d+]/', '', $o['phone'] ) ) . '">' . esc_html( $o['phone'] ) . '</a></li>'; // phpcs:ignore WordPress.Security.EscapeOutput
			}
			if ( $o['email'] ) {
				echo '<li>' . beleon_icon( 'mail' ) . '<a href="mailto:' . esc_attr( antispambot( $o['email'] ) ) . '">' . esc_html( antispambot( $o['email'] ) ) . '</a></li>'; // phpcs:ignore WordPress.Security.EscapeOutput
			}
			echo '</ul>';
			if ( $map && $s['directions'] ) {
				echo beleon_button( $s['directions'], array( 'url' => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $map ), 'is_external' => 'on' ), 'line' ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			echo '</article>';
		}
		echo '</div>';
	}
}
