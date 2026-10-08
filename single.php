<?php
/**
 * Blog posts (journal articles) and any other single content.
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
		continue;
	endif;

	$beleon_is_post = 'post' === get_post_type();
	echo beleon_render_page_hero( // phpcs:ignore WordPress.Security.EscapeOutput
		array(
			'title'   => get_the_title(),
			'eyebrow' => '',
			'image'   => get_post_thumbnail_id(),
			'variant' => 'ink',
			'after'   => $beleon_is_post ? beleon_post_meta() : '',
		)
	);
	?>
	<article <?php post_class( 'bl-section bl-article' ); ?>>
		<div class="bl-wrap bl-narrow">
			<div class="bl-prose bl-article__body"><?php the_content(); ?></div>
			<?php wp_link_pages(); ?>

			<?php if ( $beleon_is_post ) : ?>
				<footer class="bl-article__foot">
					<p class="bl-article__share">
						<span><?php esc_html_e( 'Share', 'beleon-tours' ); ?></span>
						<?php
						$beleon_url   = rawurlencode( get_permalink() );
						$beleon_title = rawurlencode( get_the_title() );
						$beleon_share = array(
							'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . $beleon_url,
							'whatsapp' => 'https://wa.me/?text=' . $beleon_title . '%20' . $beleon_url,
							'mail'     => 'mailto:?subject=' . $beleon_title . '&body=' . $beleon_url,
						);
						foreach ( $beleon_share as $beleon_net => $beleon_link ) :
							?>
							<a href="<?php echo esc_url( $beleon_link ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( ucfirst( $beleon_net ) ); ?>"><?php echo beleon_icon( $beleon_net ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
						<?php endforeach; ?>
					</p>
					<?php
					$beleon_prev = get_previous_post();
					$beleon_next = get_next_post();
					if ( $beleon_prev || $beleon_next ) :
						?>
						<nav class="bl-article__nav" aria-label="<?php esc_attr_e( 'More stories', 'beleon-tours' ); ?>">
							<?php if ( $beleon_prev ) : ?>
								<a class="bl-article__prev" href="<?php echo esc_url( get_permalink( $beleon_prev ) ); ?>"><small><?php echo beleon_icon( 'arrow', 'bl-flip' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Previous story', 'beleon-tours' ); ?></small><?php echo esc_html( get_the_title( $beleon_prev ) ); ?></a>
							<?php endif; ?>
							<?php if ( $beleon_next ) : ?>
								<a class="bl-article__next" href="<?php echo esc_url( get_permalink( $beleon_next ) ); ?>"><small><?php esc_html_e( 'Next story', 'beleon-tours' ); ?><?php echo beleon_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></small><?php echo esc_html( get_the_title( $beleon_next ) ); ?></a>
							<?php endif; ?>
						</nav>
					<?php endif; ?>
				</footer>
			<?php endif; ?>

			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</div>
	</article>
	<?php
	if ( $beleon_is_post ) :
		$beleon_tours = beleon_query_tours( array( 'source' => 'latest', 'count' => 3 ) );
		if ( $beleon_tours ) :
			?>
			<section class="bl-section bl-surface-ivory">
				<div class="bl-wrap">
					<?php echo beleon_render_heading( array( 'eyebrow' => __( 'Travel with us', 'beleon-tours' ), 'title' => __( 'Ready for your own *story*?', 'beleon-tours' ), 'align' => 'split', 'link_text' => __( 'All tours', 'beleon-tours' ), 'link' => beleon_tours_link() ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php echo beleon_render_tours( $beleon_tours, array( 'empty' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>
			</section>
			<?php
		endif;
	endif;
endwhile;

get_footer();
