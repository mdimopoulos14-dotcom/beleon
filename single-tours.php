<?php
/**
 * Single tour.
 * Priority: Elementor Pro single > Beleon > Layout "Single tour" template > this design.
 * If the tour itself is built with Elementor, that content replaces the description.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	if ( beleon_render_slot( 'single_tours' ) ) {
		continue;
	}

	$beleon_id      = get_the_ID();
	$beleon_dests   = beleon_tour_destination_ids( $beleon_id );
	$beleon_itin    = beleon_render_itinerary( $beleon_id );
	$beleon_incl    = beleon_render_includes( $beleon_id );
	$beleon_gallery = beleon_render_gallery( beleon_gallery( 'gallery', $beleon_id ) );
	$beleon_price   = beleon_format_price( beleon_text( 'price', $beleon_id ) );

	echo beleon_render_page_hero( // phpcs:ignore WordPress.Security.EscapeOutput
		array(
			'title'   => get_the_title(),
			'eyebrow' => implode( ' · ', array_map( 'get_the_title', $beleon_dests ) ),
			'text'    => has_excerpt() ? get_the_excerpt() : '',
			'image'   => get_post_thumbnail_id(),
			'facts'   => beleon_render_facts( $beleon_id, array( 'style' => 'glass' ) ),
		)
	);
	?>
	<div class="bl-wrap bl-single">
		<div class="bl-single__main">
			<nav class="bl-toc" aria-label="<?php esc_attr_e( 'On this page', 'beleon-tours' ); ?>">
				<a href="#overview"><?php esc_html_e( 'Overview', 'beleon-tours' ); ?></a>
				<?php if ( $beleon_itin ) : ?>
					<a href="#itinerary"><?php esc_html_e( 'Itinerary', 'beleon-tours' ); ?></a>
				<?php endif; ?>
				<?php if ( $beleon_incl ) : ?>
					<a href="#included"><?php esc_html_e( 'What\'s included', 'beleon-tours' ); ?></a>
				<?php endif; ?>
				<?php if ( $beleon_gallery ) : ?>
					<a href="#gallery"><?php esc_html_e( 'Gallery', 'beleon-tours' ); ?></a>
				<?php endif; ?>
				<a href="#enquire"><?php esc_html_e( 'Book', 'beleon-tours' ); ?></a>
			</nav>

			<section id="overview" class="bl-block">
				<?php echo beleon_render_heading( array( 'eyebrow' => __( 'Overview', 'beleon-tours' ), 'title' => __( 'The journey', 'beleon-tours' ), 'size' => 'm' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php echo beleon_render_highlights( $beleon_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<div class="bl-prose bl-m-fade-up"><?php the_content(); ?></div>
			</section>

			<?php if ( $beleon_itin ) : ?>
				<section id="itinerary" class="bl-block">
					<?php echo beleon_render_heading( array( 'eyebrow' => __( 'Day by day', 'beleon-tours' ), 'title' => __( 'Itinerary', 'beleon-tours' ), 'size' => 'm' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php echo $beleon_itin; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</section>
			<?php endif; ?>

			<?php if ( $beleon_incl ) : ?>
				<section id="included" class="bl-block">
					<?php echo beleon_render_heading( array( 'eyebrow' => __( 'Good to know', 'beleon-tours' ), 'title' => __( 'What\'s included', 'beleon-tours' ), 'size' => 'm' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php echo $beleon_incl; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</section>
			<?php endif; ?>

			<?php if ( $beleon_gallery ) : ?>
				<section id="gallery" class="bl-block">
					<?php echo beleon_render_heading( array( 'eyebrow' => __( 'Moments', 'beleon-tours' ), 'title' => __( 'Gallery', 'beleon-tours' ), 'size' => 'm' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php echo $beleon_gallery; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</section>
			<?php endif; ?>

		</div>

		<div class="bl-single__aside">
			<?php echo beleon_render_booking_panel( $beleon_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	</div>

	<?php
	$beleon_related = beleon_query_tours(
		array(
			'source' => 'related',
			'count'  => 3,
		)
	);
	if ( $beleon_related ) :
		?>
		<section class="bl-section bl-surface-ivory">
			<div class="bl-wrap">
				<?php echo beleon_render_heading( array( 'eyebrow' => __( 'Keep exploring', 'beleon-tours' ), 'title' => __( 'You may also *love*', 'beleon-tours' ), 'align' => 'split', 'link_text' => __( 'All tours', 'beleon-tours' ), 'link' => beleon_tours_link() ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php echo beleon_render_tours( $beleon_related, array( 'empty' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		</section>
	<?php endif; ?>

	<div class="bl-mbar" data-bl-mbar>
		<?php if ( $beleon_price ) : ?>
			<p class="bl-mbar__price"><small><?php echo esc_html( beleon_price_label( $beleon_id ) ); ?></small><?php echo esc_html( $beleon_price ); ?></p>
		<?php endif; ?>
		<a class="bl-btn bl-btn--solid bl-btn--sm" href="<?php echo esc_url( beleon_booking_link( $beleon_id ) ); ?>"><span><?php esc_html_e( 'Book now', 'beleon-tours' ); ?></span></a>
	</div>
	<?php
endwhile;

get_footer();
