<?php
/**
 * Beleon Tours theme bootstrap.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BELEON_VERSION', '2.1.0' );
define( 'BELEON_DIR', get_template_directory() );
define( 'BELEON_URI', get_template_directory_uri() );

$beleon_includes = array(
	'setup',
	'i18n',
	'helpers',
	'post-types',
	'fields',
	'meta-boxes',
	'customizer',
	'assets',
	'performance',
	'templates',
	'render',
	'tour-finder',
	'forms',
	'compat',
	'tour-pages',
	'admin',
	'elementor/integration',
);

foreach ( $beleon_includes as $beleon_include ) {
	require_once BELEON_DIR . '/inc/' . $beleon_include . '.php';
}
