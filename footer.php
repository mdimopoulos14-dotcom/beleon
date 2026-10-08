<?php
/**
 * Site footer.
 * Priority: Elementor Pro footer > Beleon > Layout footer template > theme footer.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main>
<?php
if ( ! beleon_render_slot( 'footer' ) ) {
	beleon_part( 'site-footer' );
}
?>
<?php wp_footer(); ?>
</body>
</html>
