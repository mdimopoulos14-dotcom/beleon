<?php
/**
 * Blog posts and any other single content.
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
				'eyebrow' => get_the_date(),
				'image'   => get_post_thumbnail_id(),
				'variant' => 'ivory',
			)
		);
		?>
		<article <?php post_class( 'bl-section' ); ?>>
			<div class="bl-wrap bl-narrow">
				<div class="bl-prose"><?php the_content(); ?></div>
				<?php wp_link_pages(); ?>
				<?php
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
			</div>
		</article>
		<?php
	endif;
endwhile;

get_footer();
