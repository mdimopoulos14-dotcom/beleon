<?php
/**
 * Not found.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<section class="bl-404 bl-surface-night bl-shape-arch bl-grain-yes">
	<div class="bl-wrap bl-404__in">
		<p class="bl-404__code bl-m-fade">404</p>
		<h1 class="bl-404__title bl-m-mask-up bl-d-1"><?php echo beleon_accent( __( 'This road leads *nowhere*', 'beleon-tours' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
		<p class="bl-m-fade-up bl-d-2"><?php esc_html_e( 'The page you are looking for has moved or no longer exists. Let\'s find you a better destination.', 'beleon-tours' ); ?></p>
		<div class="bl-actions bl-m-fade-up bl-d-3">
			<?php echo beleon_button( __( 'Back to home', 'beleon-tours' ), home_url( '/' ), 'solid' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php echo beleon_button( __( 'See all tours', 'beleon-tours' ), beleon_tours_link(), 'ghost' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	</div>
</section>
<?php
get_footer();
