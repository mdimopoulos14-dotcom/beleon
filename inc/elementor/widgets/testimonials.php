<?php
/**
 * Beleon Testimonials: traveller quotes as a swipe slider or grid.
 *
 * @package beleon-tours
 */

namespace Beleon\Elementor;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Testimonials extends Widget {

	public function get_name() {
		return 'beleon-testimonials';
	}

	public function get_title() {
		return __( 'Beleon Testimonials', 'beleon-tours' );
	}

	public function get_icon() {
		return 'eicon-testimonial-carousel';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Quotes', 'beleon-tours' ) ) );
		$r = new Repeater();
		$r->add_control(
			'quote',
			array(
				'label'   => __( 'Quote', 'beleon-tours' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 5,
				'default' => __( 'A journey we will remember for the rest of our lives.', 'beleon-tours' ),
			)
		);
		$r->add_control(
			'name',
			array(
				'label'   => __( 'Name', 'beleon-tours' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Traveller', 'beleon-tours' ),
			)
		);
		$r->add_control(
			'trip',
			array(
				'label' => __( 'Trip', 'beleon-tours' ),
				'type'  => Controls_Manager::TEXT,
			)
		);
		$r->add_control(
			'avatar',
			array(
				'label' => __( 'Photo', 'beleon-tours' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);
		$this->add_control(
			'items',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $r->get_controls(),
				'title_field' => '{{{ name }}}',
				'default'     => array(
					array(
						'quote' => __( 'Everything was thought through, down to the smallest detail. We felt like guests, not tourists.', 'beleon-tours' ),
						'name'  => 'Maria K.',
						'trip'  => __( 'Georgia & Armenia', 'beleon-tours' ),
					),
					array(
						'quote' => __( 'The Silk Road was a dream for years. Beleon made it effortless and unforgettable.', 'beleon-tours' ),
						'name'  => 'Nikos P.',
						'trip'  => __( 'Uzbekistan', 'beleon-tours' ),
					),
				),
			)
		);
		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'beleon-tours' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'slider',
				'options' => array(
					'slider' => __( 'Large quote slider', 'beleon-tours' ),
					'grid'   => __( 'Grid', 'beleon-tours' ),
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
		$this->text_style( 'quote_s', __( 'Quote', 'beleon-tours' ), '.bl-quote__text' );
		$this->text_style( 'name_s', __( 'Name', 'beleon-tours' ), '.bl-quote__name' );
		$this->end_controls_section();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$items = '';
		foreach ( (array) $s['items'] as $item ) {
			$img    = $this->media_id( $item['avatar'] );
			$items .= '<figure class="bl-quote"><span class="bl-quote__mark" aria-hidden="true">&ldquo;</span>';
			$items .= '<blockquote class="bl-quote__text"><p>' . esc_html( $item['quote'] ) . '</p></blockquote><figcaption>';
			if ( $img ) {
				$items .= beleon_img( $img, 'thumbnail', array( 'class' => 'bl-quote__avatar', 'alt' => '' ) );
			}
			$items .= '<span><span class="bl-quote__name">' . esc_html( $item['name'] ) . '</span>' . ( $item['trip'] ? '<span class="bl-quote__trip">' . esc_html( $item['trip'] ) . '</span>' : '' ) . '</span></figcaption></figure>';
		}
		if ( 'slider' === $s['layout'] ) {
			echo '<div class="bl-quotes bl-quotes--slider bl-carousel" data-bl-carousel><div class="bl-carousel__track" tabindex="0">' . $items . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
			echo '<div class="bl-carousel__nav"><button type="button" class="bl-arrow bl-arrow--prev" data-bl-prev aria-label="' . esc_attr__( 'Previous', 'beleon-tours' ) . '">' . beleon_icon( 'arrow' ) . '</button><button type="button" class="bl-arrow" data-bl-next aria-label="' . esc_attr__( 'Next', 'beleon-tours' ) . '">' . beleon_icon( 'arrow' ) . '</button></div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		} else {
			echo '<div class="bl-quotes bl-quotes--grid bl-stagger">' . $items . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
	}
}
