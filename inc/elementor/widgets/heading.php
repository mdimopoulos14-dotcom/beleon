<?php
/**
 * Beleon Heading: numbered eyebrow, serif title with italic accent, text, link.
 *
 * @package beleon-tours
 */

namespace Beleon\Elementor;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Heading extends Widget {

	public function get_name() {
		return 'beleon-heading';
	}

	public function get_title() {
		return __( 'Beleon Heading', 'beleon-tours' );
	}

	public function get_icon() {
		return 'eicon-heading';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'beleon-tours' ) ) );
		$this->add_control(
			'number',
			array(
				'label'       => __( 'Number', 'beleon-tours' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'description' => __( 'Optional section number, e.g. 01.', 'beleon-tours' ),
			)
		);
		$this->add_control(
			'eyebrow',
			array(
				'label'   => __( 'Eyebrow', 'beleon-tours' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Upcoming departures', 'beleon-tours' ),
				'dynamic' => array( 'active' => true ),
			)
		);
		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'beleon-tours' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => __( 'Journeys worth *waiting* for', 'beleon-tours' ),
				'description' => __( 'Wrap words in *asterisks* for the italic accent.', 'beleon-tours' ),
				'dynamic'     => array( 'active' => true ),
			)
		);
		$this->add_control(
			'text',
			array(
				'label'   => __( 'Text', 'beleon-tours' ),
				'type'    => Controls_Manager::TEXTAREA,
				'dynamic' => array( 'active' => true ),
			)
		);
		$this->add_control(
			'tag',
			array(
				'label'   => __( 'HTML tag', 'beleon-tours' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h2',
				'options' => array(
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'p'  => 'p',
				),
			)
		);
		$this->add_control(
			'size',
			array(
				'label'   => __( 'Size', 'beleon-tours' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'l',
				'options' => array(
					's'  => __( 'Small', 'beleon-tours' ),
					'm'  => __( 'Medium', 'beleon-tours' ),
					'l'  => __( 'Large', 'beleon-tours' ),
					'xl' => __( 'Display', 'beleon-tours' ),
				),
			)
		);
		$this->add_control(
			'align',
			array(
				'label'   => __( 'Layout', 'beleon-tours' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'split',
				'options' => array(
					'left'   => __( 'Left', 'beleon-tours' ),
					'center' => __( 'Center', 'beleon-tours' ),
					'split'  => __( 'Title left, link right', 'beleon-tours' ),
				),
			)
		);
		$this->button_controls( 'link', __( 'Link', 'beleon-tours' ), '', '' );
		$this->end_controls_section();

		$this->start_controls_section(
			'style',
			array(
				'label' => __( 'Style', 'beleon-tours' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->tone_control();
		$this->add_responsive_control(
			'max_width',
			array(
				'label'      => __( 'Title width', 'beleon-tours' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'ch', '%' ),
				'range'      => array( 'px' => array( 'min' => 200, 'max' => 1400 ) ),
				'selectors'  => array( '{{WRAPPER}} .bl-heading__main' => 'max-width: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->text_style( 'eyebrow_s', __( 'Eyebrow', 'beleon-tours' ), '.bl-eyebrow' );
		$this->text_style( 'title_s', __( 'Title', 'beleon-tours' ), '.bl-heading__title' );
		$this->add_control(
			'accent_color',
			array(
				'label'     => __( 'Accent words color', 'beleon-tours' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .bl-accent' => 'color: {{VALUE}};' ),
			)
		);
		$this->text_style( 'text_s', __( 'Text', 'beleon-tours' ), '.bl-heading__text' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo beleon_render_heading( // phpcs:ignore WordPress.Security.EscapeOutput
			array(
				'eyebrow'   => $s['eyebrow'],
				'number'    => $s['number'],
				'title'     => $s['title'],
				'text'      => $s['text'],
				'tag'       => $s['tag'],
				'align'     => $s['align'],
				'size'      => $s['size'],
				'link_text' => $s['link_text'],
				'link'      => $s['link_link'],
			)
		);
	}
}
