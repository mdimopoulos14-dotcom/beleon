<?php
/**
 * Blog (journal), search and archives.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$beleon_blog = (int) get_option( 'page_for_posts' );
$beleon_text = '';
if ( is_search() ) {
	/* translators: %s: search terms */
	$beleon_title = sprintf( __( 'Results for “%s”', 'beleon-tours' ), get_search_query() );
} elseif ( is_archive() ) {
	$beleon_title = wp_strip_all_tags( get_the_archive_title() );
	$beleon_text  = wp_strip_all_tags( get_the_archive_description() );
} else {
	$beleon_title = is_home() && $beleon_blog ? get_the_title( $beleon_blog ) : __( 'Journal', 'beleon-tours' );
	$beleon_text  = $beleon_blog && has_excerpt( $beleon_blog ) ? get_the_excerpt( $beleon_blog ) : '';
}

echo beleon_render_page_hero( // phpcs:ignore WordPress.Security.EscapeOutput
	array(
		'title'   => $beleon_title,
		'eyebrow' => is_home() ? __( 'Travel stories', 'beleon-tours' ) : '',
		'text'    => $beleon_text,
		'variant' => 'ink',
	)
);

$beleon_cats = is_home() || is_category() ? get_categories( array( 'hide_empty' => true ) ) : array();
?>
<section class="bl-section bl-blog">
	<div class="bl-wrap">
		<?php if ( count( $beleon_cats ) > 1 ) : ?>
			<nav class="bl-chips bl-blog__cats" aria-label="<?php esc_attr_e( 'Categories', 'beleon-tours' ); ?>">
				<a class="bl-chip<?php echo is_home() ? ' is-active' : ''; ?>" href="<?php echo esc_url( $beleon_blog ? get_permalink( $beleon_blog ) : home_url( '/' ) ); ?>"><?php esc_html_e( 'All', 'beleon-tours' ); ?></a>
				<?php foreach ( $beleon_cats as $beleon_cat ) : ?>
					<a class="bl-chip<?php echo is_category( $beleon_cat->term_id ) ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_category_link( $beleon_cat ) ); ?>"><?php echo esc_html( $beleon_cat->name ); ?></a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<?php
			$beleon_first = ! is_paged() && ! is_search();
			$beleon_grid  = false;
			while ( have_posts() ) :
				the_post();
				if ( 'tours' === get_post_type() ) {
					if ( ! $beleon_grid ) {
						echo '<div class="bl-grid bl-stagger" style="--bl-cols:3">';
						$beleon_grid = true;
					}
					echo beleon_render_tour_card( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput
					continue;
				}
				if ( $beleon_first ) {
					echo beleon_render_post_card( get_the_ID(), true ); // phpcs:ignore WordPress.Security.EscapeOutput
					$beleon_first = false;
					continue;
				}
				if ( ! $beleon_grid ) {
					echo '<div class="bl-posts">';
					$beleon_grid = true;
				}
				echo beleon_render_post_card( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput
			endwhile;
			if ( $beleon_grid ) {
				echo '</div>';
			}
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
