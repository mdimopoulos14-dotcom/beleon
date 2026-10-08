<?php
/**
 * Base class for Beleon widgets: category, lean markup and shared style controls.
 *
 * @package beleon-tours
 */

namespace Beleon\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Widget extends Widget_Base {

	public function get_categories() {
		return array( 'beleon' );
	}

	public function get_keywords() {
		return array( 'beleon', 'tour', 'travel' );
	}

	/**
	 * No extra wrapper div: lighter DOM (Elementor "optimized markup").
	 */
	public function has_widget_inner_wrapper(): bool {
		return false;
	}

	/**
	 * Theme styles are global; nothing extra to enqueue per widget.
	 */
	public function get_style_depends(): array {
		return array();
	}

	/**
	 * Typography + color (+ hover) controls for one element.
	 *
	 * @param string $id       Control id prefix.
	 * @param string $label    Heading label.
	 * @param string $selector CSS selector relative to the wrapper.
	 * @param bool   $hover    Add hover color.
	 */
	protected function text_style( $id, $label, $selector, $hover = false ) {
		$this->add_control(
			$id . '_heading',
			array(
				'label'     => $label,
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => $id . '_typo',
				'selector' => '{{WRAPPER}} ' . $selector,
			)
		);
		$this->add_control(
			$id . '_color',
			array(
				'label'     => __( 'Color', 'beleon-tours' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} ' . $selector => 'color: {{VALUE}};' ),
			)
		);
		if ( $hover ) {
			$this->add_control(
				$id . '_color_hover',
				array(
					'label'     => __( 'Hover color', 'beleon-tours' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( '{{WRAPPER}} ' . $selector . ':hover' => 'color: {{VALUE}};' ),
				)
			);
		}
	}

	/**
	 * Standard "Theme" (light/dark text) control: flips the widget's text palette.
	 */
	protected function tone_control() {
		$this->add_control(
			'tone',
			array(
				'label'        => __( 'Text on', 'beleon-tours' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => '',
				'options'      => array(
					''      => __( 'Light background', 'beleon-tours' ),
					'light' => __( 'Dark background', 'beleon-tours' ),
				),
				'prefix_class' => 'bl-tone-',
			)
		);
	}

	/**
	 * Button controls pair.
	 *
	 * @param string $id      Prefix.
	 * @param string $label   Section heading.
	 * @param string $default Default text.
	 * @param string $url     Default URL.
	 */
	protected function button_controls( $id, $label, $default = '', $url = '' ) {
		$this->add_control(
			$id . '_heading',
			array(
				'label'     => $label,
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);
		$this->add_control(
			$id . '_text',
			array(
				'label'   => __( 'Text', 'beleon-tours' ),
				'type'    => Controls_Manager::TEXT,
				'default' => $default,
				'dynamic' => array( 'active' => true ),
			)
		);
		$this->add_control(
			$id . '_link',
			array(
				'label'   => __( 'Link', 'beleon-tours' ),
				'type'    => Controls_Manager::URL,
				'default' => array( 'url' => $url ),
				'dynamic' => array( 'active' => true ),
			)
		);
	}

	/**
	 * Button style controls (applies to all .bl-btn inside the widget).
	 */
	protected function button_style_section() {
		$this->start_controls_section(
			'style_buttons',
			array(
				'label' => __( 'Buttons', 'beleon-tours' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'btn_typo',
				'selector' => '{{WRAPPER}} .bl-btn',
			)
		);
		$this->add_control(
			'btn_bg',
			array(
				'label'     => __( 'Main button background', 'beleon-tours' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .bl-btn--solid' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'btn_color',
			array(
				'label'     => __( 'Main button text', 'beleon-tours' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .bl-btn--solid' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'btn_radius',
			array(
				'label'      => __( 'Radius', 'beleon-tours' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'  => array( '{{WRAPPER}} .bl-btn' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Options for "pick posts" selects.
	 *
	 * @param string $type Post type.
	 * @return array
	 */
	protected function post_options( $type ) {
		$out = array();
		// Options are only needed by the editor panel; skip the query for visitors.
		if ( ! is_admin() && ! wp_doing_ajax() && ! isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $out;
		}
		$posts = get_posts(
			array(
				'post_type'      => $type,
				'posts_per_page' => 300,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);
		foreach ( $posts as $p ) {
			$out[ $p->ID ] = $p->post_title;
		}
		return $out;
	}

	/**
	 * Image ID from a media control.
	 *
	 * @param array $media Control value.
	 * @return int
	 */
	protected function media_id( $media ) {
		return ! empty( $media['id'] ) ? (int) $media['id'] : 0;
	}
}
