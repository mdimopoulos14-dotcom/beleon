<?php
/**
 * Tours archive with destination / month filters.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( ! beleon_render_slot( 'archive_tours' ) ) :
	echo beleon_render_page_hero( // phpcs:ignore WordPress.Security.EscapeOutput
		array(
			'title'   => __( 'Every *journey*', 'beleon-tours' ),
			'eyebrow' => __( 'Small-group tours', 'beleon-tours' ),
			'text'    => __( 'Choose a destination or a month — every departure is designed and accompanied by our team.', 'beleon-tours' ),
			'variant' => 'ink',
		)
	);
	?>
	<section class="bl-section">
		<div class="bl-wrap">
			<?php echo beleon_render_tour_filters( get_post_type_archive_link( 'tours' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php
			$beleon_ids = wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' );
			echo beleon_render_tours(
				$beleon_ids,
				array(
					'heading' => 'h2',
					'empty'   => __( 'No departures match these filters yet. Try another month or contact us.', 'beleon-tours' ),
				)
			); // phpcs:ignore WordPress.Security.EscapeOutput
			the_posts_pagination(
				array(
					'mid_size'  => 1,
					'prev_text' => beleon_icon( 'arrow', 'bl-flip' ),
					'next_text' => beleon_icon( 'arrow' ),
				)
			);
			?>
		</div>
	</section>
	<?php
endif;

get_footer();
