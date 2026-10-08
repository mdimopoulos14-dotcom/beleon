<?php
/**
 * Theme footer.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$beleon_about   = beleon_mod( 'beleon_footer_about' );
$beleon_license = beleon_mod( 'beleon_license' );
?>
<footer class="bl-footer bl-surface-night bl-grain-yes">
	<div class="bl-wrap">
		<div class="bl-footer__cta">
			<p class="bl-footer__big bl-m-mask-up"><?php echo beleon_accent( __( 'Let\'s design your *next* journey.', 'beleon-tours' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<?php echo beleon_button( beleon_mod( 'beleon_header_cta_text' ) ? beleon_mod( 'beleon_header_cta_text' ) : __( 'Contact us', 'beleon-tours' ), beleon_url( beleon_mod( 'beleon_header_cta_url' ) ), 'solid' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<div class="bl-footer__grid">
			<div class="bl-footer__brand">
				<a class="bl-footer__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<img src="<?php echo esc_url( BELEON_URI . '/assets/img/beleon-white.svg' ); ?>" width="168" height="45" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" loading="lazy">
				</a>
				<?php if ( $beleon_about ) : ?>
					<p><?php echo esc_html( $beleon_about ); ?></p>
				<?php endif; ?>
				<?php echo beleon_render_socials(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>

			<nav class="bl-footer__nav" aria-label="<?php esc_attr_e( 'Footer', 'beleon-tours' ); ?>">
				<p class="bl-footer__label"><?php esc_html_e( 'Explore', 'beleon-tours' ); ?></p>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'depth'          => 1,
						'fallback_cb'    => 'beleon_menu_fallback',
					)
				);
				?>
			</nav>

			<div class="bl-footer__dest">
				<p class="bl-footer__label"><?php esc_html_e( 'Destinations', 'beleon-tours' ); ?></p>
				<ul>
					<?php foreach ( beleon_query_destinations( array( 'count' => 6 ) ) as $beleon_dest ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $beleon_dest ) ); ?>"><?php echo esc_html( get_the_title( $beleon_dest ) ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div class="bl-footer__contact">
				<p class="bl-footer__label"><?php esc_html_e( 'Contact', 'beleon-tours' ); ?></p>
				<?php echo beleon_render_contact( array( 'show' => array( 'phones', 'email', 'addresses' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		</div>

		<div class="bl-footer__bottom">
			<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?><?php echo $beleon_license ? ' · ' . esc_html( $beleon_license ) : ''; ?></p>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'legal',
					'container'      => false,
					'depth'          => 1,
					'menu_class'     => 'bl-legal',
					'fallback_cb'    => false,
				)
			);
			?>
			<a class="bl-totop" href="#content" aria-label="<?php esc_attr_e( 'Back to top', 'beleon-tours' ); ?>"><?php echo beleon_icon( 'arrow-up' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		</div>
	</div>
</footer>
