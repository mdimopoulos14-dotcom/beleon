<?php
/**
 * Single destination.
 * Priority: Elementor Pro single > Beleon > Layout "Single destination" template > this design.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	if ( beleon_render_slot( 'single_destinations' ) ) {
		continue;
	}

	$beleon_id     = get_the_ID();
	$beleon_tours  = beleon_query_tours(
		array(
			'source'      => 'destination',
			'destination' => $beleon_id,
			'count'       => 12,
		)
	);
	$beleon_season = beleon_text( 'best_season', $beleon_id );
	$beleon_facts  = '';
	if ( $beleon_season || $beleon_tours ) {
		$beleon_facts = '<ul class="bl-facts bl-facts--glass">';
		if ( $beleon_season ) {
			$beleon_facts .= '<li>' . beleon_icon( 'calendar' ) . '<span><small>' . esc_html__( 'Best season', 'beleon-tours' ) . '</small>' . esc_html( $beleon_season ) . '</span></li>';
		}
		if ( $beleon_tours ) {
			/* translators: %d: number of tours */
			$beleon_facts .= '<li>' . beleon_icon( 'compass' ) . '<span><small>' . esc_html__( 'Journeys', 'beleon-tours' ) . '</small>' . esc_html( sprintf( _n( '%d journey', '%d journeys', count( $beleon_tours ), 'beleon-tours' ), count( $beleon_tours ) ) ) . '</span></li>';
		}
		$beleon_facts .= '</ul>';
	}

	echo beleon_render_page_hero( // phpcs:ignore WordPress.Security.EscapeOutput
		array(
			'title'   => get_the_title(),
			'eyebrow' => beleon_text( 'region', $beleon_id ),
			'text'    => beleon_text( 'tagline', $beleon_id ) ? beleon_text( 'tagline', $beleon_id ) : ( has_excerpt() ? get_the_excerpt() : '' ),
			'image'   => get_post_thumbnail_id(),
			'facts'   => $beleon_facts,
		)
	);
	?>
	<section class="bl-section">
		<div class="bl-wrap bl-narrow">
			<div class="bl-prose bl-prose--lead bl-m-fade-up"><?php the_content(); ?></div>
		</div>
	</section>

	<?php $beleon_gallery = beleon_render_gallery( beleon_gallery( 'dest_gallery', $beleon_id ) ); ?>
	<?php if ( $beleon_gallery ) : ?>
		<section class="bl-section bl-section--tight">
			<div class="bl-wrap"><?php echo $beleon_gallery; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		</section>
	<?php endif; ?>

	<section class="bl-section bl-surface-ivory">
		<div class="bl-wrap">
			<?php
			echo beleon_render_heading( // phpcs:ignore WordPress.Security.EscapeOutput
				array(
					'eyebrow'   => __( 'Journeys', 'beleon-tours' ),
					/* translators: %s: destination name */
					'title'     => sprintf( __( 'Travel to *%s*', 'beleon-tours' ), get_the_title() ),
					'align'     => 'split',
					'link_text' => __( 'All tours', 'beleon-tours' ),
					'link'      => beleon_tours_link(),
				)
			);
			echo beleon_render_tours( $beleon_tours ); // phpcs:ignore WordPress.Security.EscapeOutput
			?>
		</div>
	</section>

	<section class="bl-section">
		<div class="bl-wrap">
			<?php
			echo beleon_render_cta( // phpcs:ignore WordPress.Security.EscapeOutput
				array(
					'eyebrow' => __( 'Tailor-made', 'beleon-tours' ),
					/* translators: %s: destination name */
					'title'   => sprintf( __( 'Dreaming of %s on your own dates?', 'beleon-tours' ), get_the_title() ),
					'text'    => __( 'We design private journeys for families, friends and groups.', 'beleon-tours' ),
					'btn1'    => __( 'Plan with us', 'beleon-tours' ),
					'link1'   => beleon_booking_link(),
				)
			);
			?>
		</div>
	</section>
	<?php
endwhile;

get_footer();
