<?php
/**
 * Pages. Elementor pages render edge to edge (Elementor owns the layout);
 * classic pages get a hero and a comfortable reading column.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	if ( beleon_is_elementor_page() ) :
		the_content();
	else :
		echo beleon_render_page_hero( // phpcs:ignore WordPress.Security.EscapeOutput
			array(
				'title'   => get_the_title(),
				'text'    => has_excerpt() ? get_the_excerpt() : '',
				'image'   => get_post_thumbnail_id(),
				'variant' => 'ivory',
			)
		);
		?>
		<section class="bl-section">
			<div class="bl-wrap bl-narrow">
				<div class="bl-prose"><?php the_content(); ?></div>
				<?php wp_link_pages(); ?>
			</div>
		</section>
		<?php
	endif;
endwhile;

get_footer();
