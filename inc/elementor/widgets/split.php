<?php
/**
 * Beleon Split: editorial image + story section (arch image, floating second image, signature).
 *
 * @package beleon-tours
 */

namespace Beleon\Elementor;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Split extends Widget {

	public function get_name() {
		return 'beleon-split';
	}

	public function get_title() {
		return __( 'Beleon Image & story', 'beleon-tours' );
	}

	public function get_icon() {
		return 'eicon-image-box';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'beleon-tours' ) ) );
		$this->add_control(
			'eyebrow',
			array(
				'label'   => __( 'Eyebrow', 'beleon-tours' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'About Beleon', 'beleon-tours' ),
			)
		);
		$this->add_control(
			'number',
			array(
				'label' => __( 'Number', 'beleon-tours' ),
				'type'  => Controls_Manager::TEXT,
			)
		);
		$this->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'beleon-tours' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 2,
				'default' => __( 'We travel the routes *before* you do', 'beleon-tours' ),
			)
		);
		$this->add_control(
			'text',
			array(
				'label'   => __( 'Text', 'beleon-tours' ),
				'type'    => Controls_Manager::WYSIWYG,
				'default' => '<p>' . __( 'For more than twenty years we have been designing journeys to places that ask for curiosity — and reward it.', 'beleon-tours' ) . '</p>',
			)
		);
		$this->add_control(
			'signature',
			array(
				'label' => __( 'Signature line', 'beleon-tours' ),
				'type'  => Controls_Manager::TEXT,
			)
		);
		$this->button_controls( 'btn', __( 'Button', 'beleon-tours' ), __( 'Our story', 'beleon-tours' ), '/about-us/' );
		$this->end_controls_section();

		$this->start_controls_section( 'images', array( 'label' => __( 'Images', 'beleon-tours' ) ) );
		$this->add_control(
			'image',
			array(
				'label' => __( 'Main image', 'beleon-tours' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);
		$this->add_control(
			'image_2',
			array(
				'label' => __( 'Floating small image (optional)', 'beleon-tours' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);
		$this->add_control(
			'badge',
			array(
				'label'       => __( 'Round badge text', 'beleon-tours' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Since 2003', 'beleon-tours' ),
				'description' => __( 'Rotating circular badge over the image. Empty hides it.', 'beleon-tours' ),
			)
		);
		$this->add_control(
			'shape',
			array(
				'label'   => __( 'Image shape', 'beleon-tours' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'arch',
				'options' => array(
					'arch'    => __( 'Arch', 'beleon-tours' ),
					'rounded' => __( 'Rounded', 'beleon-tours' ),
					'circle'  => __( 'Circle', 'beleon-tours' ),
				),
			)
		);
		$this->add_control(
			'reverse',
			array(
				'label'        => __( 'Image on the right', 'beleon-tours' ),
				'type'         => Controls_Manager::SWITCHER,
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
		$this->text_style( 'title_s', __( 'Title', 'beleon-tours' ), '.bl-heading__title' );
		$this->text_style( 'text_s', __( 'Text', 'beleon-tours' ), '.bl-split__text' );
		$this->end_controls_section();
		$this->button_style_section();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$img  = $this->media_id( $s['image'] );
		$img2 = $this->media_id( $s['image_2'] );

		echo '<div class="bl-split' . ( 'yes' === $s['reverse'] ? ' is-reverse' : '' ) . '">';
		echo '<div class="bl-split__media bl-split__media--' . esc_attr( $s['shape'] ) . '">';
		echo '<div class="bl-split__img bl-m-curtain">' . ( $img ? beleon_img( $img, 'beleon-card', array( 'alt' => '', 'sizes' => '(max-width: 767px) 92vw, 45vw' ) ) : '<span class="bl-ph"></span>' ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		if ( $img2 ) {
			echo '<div class="bl-split__img2 bl-m-fade-up bl-d-3">' . beleon_img( $img2, 'beleon-thumb', array( 'alt' => '' ) ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		if ( $s['badge'] ) {
			echo '<svg class="bl-split__badge" viewBox="0 0 120 120" aria-hidden="true"><defs><path id="bl-c-' . esc_attr( $this->get_id() ) . '" d="M60 60m-46 0a46 46 0 1 1 92 0a46 46 0 1 1-92 0"/></defs><text><textPath href="#bl-c-' . esc_attr( $this->get_id() ) . '">' . esc_html( str_repeat( $s['badge'] . ' · ', 3 ) ) . '</textPath></text></svg>';
		}
		echo '</div><div class="bl-split__body">';
		echo beleon_render_heading( // phpcs:ignore WordPress.Security.EscapeOutput
			array(
				'eyebrow' => $s['eyebrow'],
				'number'  => $s['number'],
				'title'   => $s['title'],
			)
		);
		echo '<div class="bl-split__text bl-m-fade-up bl-d-2">' . wp_kses_post( $s['text'] ) . '</div>';
		if ( $s['signature'] ) {
			echo '<p class="bl-split__sign bl-m-fade bl-d-3">' . esc_html( $s['signature'] ) . '</p>';
		}
		$btn = beleon_button( $s['btn_text'], $s['btn_link'], 'line' );
		if ( $btn ) {
			echo '<div class="bl-actions bl-m-fade-up bl-d-3">' . $btn . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '</div></div>';
	}
}
