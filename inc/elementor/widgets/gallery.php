<?php
/**
 * Beleon Gallery: mosaic with a lightweight lightbox. Uses the tour/destination
 * gallery field, or images picked here.
 *
 * @package beleon-tours
 */

namespace Beleon\Elementor;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Gallery extends Widget {

	public function get_name() {
		return 'beleon-gallery';
	}

	public function get_title() {
		return __( 'Beleon Gallery', 'beleon-tours' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Images', 'beleon-tours' ) ) );
		$this->add_control(
			'source',
			array(
				'label'   => __( 'Images from', 'beleon-tours' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'tours',
				'options' => array(
					'tours'        => __( 'Current tour gallery', 'beleon-tours' ),
					'destinations' => __( 'Current destination gallery', 'beleon-tours' ),
					'manual'       => __( 'Choose here', 'beleon-tours' ),
				),
			)
		);
		$this->add_control(
			'images',
			array(
				'label'     => __( 'Images', 'beleon-tours' ),
				'type'      => Controls_Manager::GALLERY,
				'condition' => array( 'source' => 'manual' ),
			)
		);
		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'beleon-tours' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'mosaic',
				'options' => array(
					'mosaic' => __( 'Mosaic', 'beleon-tours' ),
					'grid'   => __( 'Even grid', 'beleon-tours' ),
					'row'    => __( 'Swipe row', 'beleon-tours' ),
				),
			)
		);
		$this->add_control(
			'limit',
			array(
				'label'   => __( 'Max images', 'beleon-tours' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 9,
				'min'     => 1,
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		if ( 'manual' === $s['source'] ) {
			$ids = wp_list_pluck( (array) $s['images'], 'id' );
		} else {
			$ids = beleon_gallery( 'tours' === $s['source'] ? 'gallery' : 'dest_gallery', beleon_context_post( $s['source'] ) );
		}
		echo beleon_render_gallery( // phpcs:ignore WordPress.Security.EscapeOutput
			array_map( 'intval', $ids ),
			array(
				'layout' => $s['layout'],
				'limit'  => $s['limit'],
			)
		);
	}
}
