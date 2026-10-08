<?php
/**
 * Beleon Post content: the editor content of the current tour/destination/post,
 * for use inside Elementor single templates (works with free Elementor).
 *
 * @package beleon-tours
 */

namespace Beleon\Elementor;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Post_Content extends Widget {

	public function get_name() {
		return 'beleon-post-content';
	}

	public function get_title() {
		return __( 'Beleon Post content', 'beleon-tours' );
	}

	public function get_icon() {
		return 'eicon-post-content';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'beleon-tours' ) ) );
		$this->add_control(
			'source',
			array(
				'label'   => __( 'Post', 'beleon-tours' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'tours',
				'options' => array(
					'tours'        => __( 'Current tour', 'beleon-tours' ),
					'destinations' => __( 'Current destination', 'beleon-tours' ),
				),
			)
		);
		$this->add_control(
			'lead',
			array(
				'label'        => __( 'Excerpt as lead paragraph', 'beleon-tours' ),
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
		$this->text_style( 'text_s', __( 'Text', 'beleon-tours' ), '.bl-prose' );
		$this->text_style( 'h_s', __( 'Headings', 'beleon-tours' ), '.bl-prose h2, {{WRAPPER}} .bl-prose h3' );
		$this->end_controls_section();
	}

	protected function render() {
		$s  = $this->get_settings_for_display();
		$id = beleon_context_post( $s['source'] );
		if ( ! $id ) {
			return;
		}
		static $depth = 0;
		if ( $depth > 0 ) {
			return; // Never nest content inside itself.
		}
		++$depth;
		$post = get_post( $id );
		echo '<div class="bl-prose">';
		if ( 'yes' === $s['lead'] && has_excerpt( $id ) ) {
			echo '<p class="bl-lead">' . esc_html( get_the_excerpt( $id ) ) . '</p>';
		}
		if ( beleon_is_elementor_page( $id ) && (int) get_the_ID() !== $id ) {
			echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $id ); // phpcs:ignore WordPress.Security.EscapeOutput
		} else {
			echo apply_filters( 'the_content', $post->post_content ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '</div>';
		--$depth;
	}
}
