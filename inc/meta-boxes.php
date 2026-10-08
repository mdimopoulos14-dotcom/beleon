<?php
/**
 * Native fields for tours and destinations, used when no plugin (JetEngine,
 * ACF...) owns them. See Beleon > Fields.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'add_meta_boxes',
	function ( $post_type ) {
		if ( in_array( $post_type, array( 'tours', 'destinations' ), true ) && beleon_uses_own_fields() ) {
			add_meta_box(
				'beleon-details',
				'tours' === $post_type ? __( 'Tour details', 'beleon-tours' ) : __( 'Destination details', 'beleon-tours' ),
				'beleon_render_details_box',
				$post_type,
				'normal',
				'high'
			);
		}
		if ( in_array( $post_type, array( 'page', 'tours', 'destinations' ), true ) ) {
			add_meta_box( 'beleon-layout', __( 'Beleon layout', 'beleon-tours' ), 'beleon_render_layout_box', $post_type, 'side', 'low' );
		}
	}
);

/**
 * Tour / destination details box.
 *
 * @param WP_Post $post Post.
 */
function beleon_render_details_box( $post ) {
	wp_nonce_field( 'beleon_details', 'beleon_details_nonce' );
	echo '<div class="bl-mb">';
	foreach ( beleon_field_definitions() as $field => $def ) {
		list( $label, $type_for, $type, $help ) = $def;
		if ( $type_for !== $post->post_type ) {
			continue;
		}
		$key   = beleon_meta_key( $field );
		$value = get_post_meta( $post->ID, $key, true );
		$id    = 'bl-f-' . $field;

		echo '<div class="bl-mb__row bl-mb__row--' . esc_attr( $type ) . '"><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';

		switch ( $type ) {
			case 'lines':
			case 'itinerary':
				$text = is_array( $value ) ? implode( "\n", array_filter( $value, 'is_scalar' ) ) : (string) $value;
				printf(
					'<textarea id="%1$s" name="beleon_fields[%2$s]" rows="%3$d">%4$s</textarea>',
					esc_attr( $id ),
					esc_attr( $field ),
					'itinerary' === $type ? 12 : 4,
					esc_textarea( $text )
				);
				break;

			case 'relation':
				$selected = beleon_tour_destination_ids( $post->ID );
				$options  = get_posts(
					array(
						'post_type'      => 'destinations',
						'posts_per_page' => 200,
						'orderby'        => 'title',
						'order'          => 'ASC',
						'post_status'    => array( 'publish', 'draft' ),
					)
				);
				echo '<div class="bl-mb__checks">';
				if ( ! $options ) {
					echo '<em>' . esc_html__( 'Add destinations first.', 'beleon-tours' ) . '</em>';
				}
				foreach ( $options as $option ) {
					printf(
						'<label><input type="checkbox" name="beleon_fields[%1$s][]" value="%2$d" %3$s> %4$s</label>',
						esc_attr( $field ),
						(int) $option->ID,
						checked( in_array( (int) $option->ID, $selected, true ), true, false ),
						esc_html( $option->post_title )
					);
				}
				echo '</div>';
				break;

			case 'gallery':
				$ids = beleon_gallery( $field, $post->ID );
				echo '<div class="bl-mb__gallery" data-bl-gallery>';
				echo '<input type="hidden" name="beleon_fields[' . esc_attr( $field ) . ']" value="' . esc_attr( implode( ',', $ids ) ) . '">';
				echo '<div class="bl-mb__thumbs">';
				foreach ( $ids as $img ) {
					echo wp_get_attachment_image( $img, 'thumbnail' );
				}
				echo '</div><button type="button" class="button" data-bl-gallery-pick>' . esc_html__( 'Choose images', 'beleon-tours' ) . '</button> ';
				echo '<button type="button" class="button-link" data-bl-gallery-clear>' . esc_html__( 'Clear', 'beleon-tours' ) . '</button></div>';
				break;

			default:
				printf(
					'<input type="text" id="%1$s" name="beleon_fields[%2$s]" value="%3$s" class="widefat">',
					esc_attr( $id ),
					esc_attr( $field ),
					esc_attr( is_scalar( $value ) ? $value : '' )
				);
		}
		if ( $help ) {
			echo '<p class="description">' . esc_html( $help ) . '</p>';
		}
		echo '</div>';
	}
	echo '</div>';
}

/**
 * Per-page layout options.
 *
 * @param WP_Post $post Post.
 */
function beleon_render_layout_box( $post ) {
	wp_nonce_field( 'beleon_layout', 'beleon_layout_nonce' );
	printf(
		'<label><input type="checkbox" name="beleon_header_over" value="1" %s> %s</label><p class="description">%s</p>',
		checked( (bool) get_post_meta( $post->ID, '_beleon_header_over', true ), true, false ),
		esc_html__( 'Transparent header over the first section', 'beleon-tours' ),
		esc_html__( 'Use when the page starts with a dark hero image.', 'beleon-tours' )
	);
}

add_action(
	'save_post',
	function ( $post_id, $post ) {
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['beleon_layout_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['beleon_layout_nonce'] ), 'beleon_layout' ) ) {
			if ( ! empty( $_POST['beleon_header_over'] ) ) {
				update_post_meta( $post_id, '_beleon_header_over', 1 );
			} else {
				delete_post_meta( $post_id, '_beleon_header_over' );
			}
		}

		if ( ! isset( $_POST['beleon_details_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['beleon_details_nonce'] ), 'beleon_details' ) ) {
			return;
		}
		$input = isset( $_POST['beleon_fields'] ) ? wp_unslash( (array) $_POST['beleon_fields'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		foreach ( beleon_field_definitions() as $field => $def ) {
			if ( $def[1] !== $post->post_type ) {
				continue;
			}
			$key = beleon_meta_key( $field );
			$raw = isset( $input[ $field ] ) ? $input[ $field ] : '';

			switch ( $def[2] ) {
				case 'relation':
					$value = array_values( array_filter( array_map( 'absint', (array) $raw ) ) );
					break;
				case 'gallery':
					$value = implode( ',', array_filter( array_map( 'absint', explode( ',', (string) $raw ) ) ) );
					break;
				case 'lines':
				case 'itinerary':
					$value = sanitize_textarea_field( (string) $raw );
					break;
				default:
					$value = sanitize_text_field( (string) $raw );
			}

			if ( '' === $value || array() === $value ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $value );
			}
		}
	},
	10,
	2
);

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		$screen = get_current_screen();
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || ! $screen || ! in_array( $screen->post_type, array( 'tours', 'destinations' ), true ) ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'beleon-admin', BELEON_URI . '/assets/css/admin.css', array(), BELEON_VERSION );
		wp_enqueue_script( 'beleon-admin', BELEON_URI . '/assets/js/admin.js', array(), BELEON_VERSION, true );
	}
);
