<?php
/**
 * Theme header: transparent over heroes, frosted glass after scrolling,
 * full-screen menu on mobile.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$beleon_cta   = beleon_mod( 'beleon_header_cta_text' );
$beleon_phone = beleon_mod( 'beleon_phone' );
?>
<header class="bl-header" id="bl-header">
	<div class="bl-header__in">
		<a class="bl-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			<?php if ( has_custom_logo() ) : ?>
				<?php echo wp_get_attachment_image( (int) get_theme_mod( 'custom_logo' ), 'medium', false, array( 'class' => 'bl-logo__img', 'alt' => get_bloginfo( 'name' ), 'loading' => 'eager' ) ); ?>
			<?php else : ?>
				<img class="bl-logo__light" src="<?php echo esc_url( BELEON_URI . '/assets/img/beleon-white.svg' ); ?>" width="168" height="45" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				<img class="bl-logo__dark" src="<?php echo esc_url( BELEON_URI . '/assets/img/beleon-dark.svg' ); ?>" width="168" height="45" alt="" aria-hidden="true">
			<?php endif; ?>
		</a>

		<nav class="bl-nav" id="bl-nav" aria-label="<?php esc_attr_e( 'Main', 'beleon-tours' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'bl-menu',
					'depth'          => 2,
					'fallback_cb'    => 'beleon_menu_fallback',
				)
			);
			?>
			<div class="bl-nav__extra">
				<?php if ( $beleon_cta ) : ?>
					<?php echo beleon_button( $beleon_cta, beleon_url( beleon_mod( 'beleon_header_cta_url' ) ), 'solid' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php endif; ?>
				<?php echo beleon_render_contact( array( 'show' => array( 'phones', 'email', 'socials' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		</nav>

		<div class="bl-header__actions">
			<?php if ( $beleon_phone ) : ?>
				<a class="bl-header__phone" href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $beleon_phone ) ); ?>">
					<?php echo beleon_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $beleon_phone ); ?></span>
				</a>
			<?php endif; ?>
			<?php if ( $beleon_cta ) : ?>
				<a class="bl-btn bl-btn--solid bl-btn--sm bl-header__cta" href="<?php echo esc_url( beleon_url( beleon_mod( 'beleon_header_cta_url' ) ) ); ?>"><span><?php echo esc_html( $beleon_cta ); ?></span></a>
			<?php endif; ?>
			<button class="bl-burger" type="button" aria-expanded="false" aria-controls="bl-nav">
				<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'beleon-tours' ); ?></span><i aria-hidden="true"></i>
			</button>
		</div>
	</div>
</header>
