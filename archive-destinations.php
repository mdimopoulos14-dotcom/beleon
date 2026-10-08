<?php
/**
 * Destinations archive.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( ! beleon_render_slot( 'archive_destinations' ) ) :
	echo beleon_render_page_hero( // phpcs:ignore WordPress.Security.EscapeOutput
		array(
			'title'   => __( 'Where we *travel*', 'beleon-tours' ),
			'eyebrow' => __( 'Destinations', 'beleon-tours' ),
			'text'    => __( 'Mountains, deserts and ancient cities — places we know and love.', 'beleon-tours' ),
			'variant' => 'ink',
		)
	);
	?>
	<section class="bl-section">
		<div class="bl-wrap" style="--bl-cols:4">
			<?php echo beleon_render_destinations( wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' ), array( 'layout' => 'arches', 'heading' => 'h2' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	</section>
	<?php
endif;

get_footer();
