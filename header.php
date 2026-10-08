<?php
/**
 * Document head and site header.
 * Priority: Elementor Pro header > Beleon > Layout header template > theme header.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#0B1428">
<script>document.documentElement.classList.add('bl-js');</script>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="bl-skip" href="#content"><?php esc_html_e( 'Skip to content', 'beleon-tours' ); ?></a>
<?php
if ( ! beleon_render_slot( 'header' ) ) {
	beleon_part( 'site-header' );
}
?>
<main id="content" class="bl-main">
