<?php
/**
 * Tours archive (and region pages) with the tour finder.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( ! beleon_render_slot( 'archive_tours' ) ) :
	$beleon_term = is_tax( 'tour_region' ) ? get_queried_object() : null;
	echo beleon_render_page_hero( // phpcs:ignore WordPress.Security.EscapeOutput
		array(
			'title'   => $beleon_term ? $beleon_term->name : beleon_mod( 'beleon_tours_title' ),
			'eyebrow' => __( 'Small-group tours', 'beleon-tours' ),
			'text'    => $beleon_term ? term_description( $beleon_term ) : beleon_mod( 'beleon_tours_intro' ),
			'variant' => 'ink',
		)
	);
	?>
	<section class="bl-section">
		<div class="bl-wrap">
			<?php
			echo beleon_render_tour_finder( // phpcs:ignore WordPress.Security.EscapeOutput
				wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' ),
				array( 'action' => $beleon_term ? get_term_link( $beleon_term ) : get_post_type_archive_link( 'tours' ) )
			);
			?>
		</div>
	</section>
	<?php
endif;

get_footer();
