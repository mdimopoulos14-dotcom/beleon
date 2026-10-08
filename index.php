<?php
/**
 * Fallback for blog, search and archives.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( is_search() ) {
	/* translators: %s: search terms */
	$beleon_title = sprintf( __( 'Results for “%s”', 'beleon-tours' ), get_search_query() );
} elseif ( is_archive() ) {
	$beleon_title = wp_strip_all_tags( get_the_archive_title() );
} else {
	$beleon_title = is_home() && get_option( 'page_for_posts' ) ? get_the_title( (int) get_option( 'page_for_posts' ) ) : __( 'Journal', 'beleon-tours' );
}

echo beleon_render_page_hero( // phpcs:ignore WordPress.Security.EscapeOutput
	array(
		'title'   => $beleon_title,
		'variant' => 'ivory',
		'crumbs'  => false,
	)
);
?>
<section class="bl-section">
	<div class="bl-wrap">
		<?php if ( have_posts() ) : ?>
			<div class="bl-grid bl-stagger" style="--bl-cols:3">
				<?php
				while ( have_posts() ) :
					the_post();
					if ( 'tours' === get_post_type() ) {
						echo beleon_render_tour_card( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput
						continue;
					}
					?>
					<article <?php post_class( 'bl-tcard bl-tcard--classic' ); ?> style="--bl-ratio:16/10">
						<a class="bl-tcard__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
							<?php echo has_post_thumbnail() ? beleon_img( get_post_thumbnail_id(), 'beleon-card', array( 'alt' => '' ) ) : '<span class="bl-ph"></span>'; // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</a>
						<div class="bl-tcard__body">
							<p class="bl-tcard__place"><?php echo esc_html( get_the_date() ); ?></p>
							<h2 class="bl-tcard__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p class="bl-tcard__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
			<?php
			the_posts_pagination(
				array(
					'mid_size'  => 1,
					'prev_text' => beleon_icon( 'arrow', 'bl-flip' ),
					'next_text' => beleon_icon( 'arrow' ),
				)
			);
			?>
		<?php else : ?>
			<p class="bl-empty"><?php esc_html_e( 'Nothing found. Try another search.', 'beleon-tours' ); ?></p>
			<?php get_search_form(); ?>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
