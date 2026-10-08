<?php
/**
 * Beleon Page hero: title + featured image of the current tour, destination or page.
 *
 * @package beleon-tours
 */

namespace Beleon\Elementor;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Post_Hero extends Widget {

	public function get_name() {
		return 'beleon-post-hero';
	}

	public function get_title() {
		return __( 'Beleon Page hero (current post)', 'beleon-tours' );
	}

	public function get_icon() {
		return 'eicon-featured-image';
	}

	public function get_keywords() {
		return array( 'beleon', 'title', 'hero', 'featured', 'single' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'beleon-tours' ) ) );
		$this->add_control(
			'source',
			array(
				'label'   => __( 'Post', 'beleon-tours' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'auto',
				'options' => array(
					'auto'         => __( 'Current page', 'beleon-tours' ),
					'tours'        => __( 'Current tour', 'beleon-tours' ),
					'destinations' => __( 'Current destination', 'beleon-tours' ),
				),
			)
		);
		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title override', 'beleon-tours' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'Leave empty to use the post title.', 'beleon-tours' ),
			)
		);
		$this->add_control(
			'eyebrow',
			array(
				'label'       => __( 'Eyebrow override', 'beleon-tours' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'Empty: destination names (tours) or region (destinations).', 'beleon-tours' ),
			)
		);
		$this->add_control(
			'show_excerpt',
			array(
				'label'        => __( 'Excerpt / tagline', 'beleon-tours' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);
		$this->add_control(
			'show_facts',
			array(
				'label'        => __( 'Tour facts strip', 'beleon-tours' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);
		$this->add_control(
			'crumbs',
			array(
				'label'        => __( 'Breadcrumbs', 'beleon-tours' ),
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
		$this->add_responsive_control(
			'height',
			array(
				'label'      => __( 'Height', 'beleon-tours' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'vh', 'px' ),
				'range'      => array(
					'vh' => array( 'min' => 30, 'max' => 100 ),
					'px' => array( 'min' => 300, 'max' => 1100 ),
				),
				'selectors'  => array( '{{WRAPPER}} .bl-phero' => 'min-height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'overlay',
			array(
				'label'     => __( 'Image darkness', 'beleon-tours' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 100 ) ),
				'selectors' => array( '{{WRAPPER}} .bl-phero' => '--bl-overlay: calc({{SIZE}} / 100);' ),
			)
		);
		$this->text_style( 'title_s', __( 'Title', 'beleon-tours' ), '.bl-phero__title' );
		$this->text_style( 'text_s', __( 'Text', 'beleon-tours' ), '.bl-phero__text' );
		$this->end_controls_section();
	}

	protected function render() {
		$s  = $this->get_settings_for_display();
		$id = 'auto' === $s['source'] ? get_the_ID() : beleon_context_post( $s['source'] );
		if ( 'auto' === $s['source'] && in_array( get_post_type( $id ), array( 'elementor_library', false ), true ) ) {
			$id = beleon_context_post( 'tours' );
		}
		if ( ! $id ) {
			return;
		}
		$type    = get_post_type( $id );
		$eyebrow = $s['eyebrow'];
		if ( '' === $eyebrow ) {
			$eyebrow = 'tours' === $type ? implode( ' · ', array_map( 'get_the_title', beleon_tour_destination_ids( $id ) ) ) : ( 'destinations' === $type ? beleon_text( 'region', $id ) : '' );
		}
		$text = '';
		if ( 'yes' === $s['show_excerpt'] ) {
			$text = 'destinations' === $type && beleon_text( 'tagline', $id ) ? beleon_text( 'tagline', $id ) : ( has_excerpt( $id ) ? get_the_excerpt( $id ) : '' );
		}
		echo beleon_render_page_hero( // phpcs:ignore WordPress.Security.EscapeOutput
			array(
				'title'   => $s['title'] ? $s['title'] : get_the_title( $id ),
				'eyebrow' => $eyebrow,
				'text'    => $text,
				'image'   => get_post_thumbnail_id( $id ),
				'facts'   => 'yes' === $s['show_facts'] && 'tours' === $type ? beleon_render_facts( $id, array( 'style' => 'glass' ) ) : '',
				'crumbs'  => 'yes' === $s['crumbs'],
			)
		);
	}
}
