<?php
/**
 * Admin: Beleon > Layout (Elementor template slots) and Beleon > Fields (field map).
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'admin_menu',
	function () {
		add_menu_page( __( 'Beleon', 'beleon-tours' ), __( 'Beleon', 'beleon-tours' ), 'manage_options', 'beleon', 'beleon_admin_layout_page', 'dashicons-admin-site-alt3', 59 );
		add_submenu_page( 'beleon', __( 'Layout', 'beleon-tours' ), __( 'Layout', 'beleon-tours' ), 'manage_options', 'beleon', 'beleon_admin_layout_page' );
		add_submenu_page( 'beleon', __( 'Tour fields', 'beleon-tours' ), __( 'Tour fields', 'beleon-tours' ), 'manage_options', 'beleon-fields', 'beleon_admin_fields_page' );
		add_submenu_page( 'beleon', __( 'Customize', 'beleon-tours' ), __( 'Brand & contact', 'beleon-tours' ), 'manage_options', 'customize.php?autofocus[panel]=beleon' );
	}
);

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( false !== strpos( $hook, 'beleon' ) ) {
			wp_enqueue_style( 'beleon-admin', BELEON_URI . '/assets/css/admin.css', array(), BELEON_VERSION );
		}
	}
);

/**
 * "Saved" notice after our admin-post redirects.
 */
function beleon_admin_saved_notice() {
	if ( isset( $_GET['updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'beleon-tours' ) . '</p></div>';
	}
}

/**
 * Layout page.
 */
function beleon_admin_layout_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$layout    = (array) get_option( 'beleon_layout', array() );
	$elementor = did_action( 'elementor/loaded' );
	$templates = $elementor ? get_posts(
		array(
			'post_type'      => 'elementor_library',
			'posts_per_page' => 200,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	) : array();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Beleon layout', 'beleon-tours' ); ?></h1>
		<?php beleon_admin_saved_notice(); ?>
		<div class="bl-admin-card">
			<h2><?php esc_html_e( 'Edit any part of the site with Elementor', 'beleon-tours' ); ?></h2>
			<p><?php esc_html_e( 'Pick an Elementor template for a region, or leave it on "Theme design" to use the built-in, fastest version. Elementor Pro Theme Builder templates, when present, take priority.', 'beleon-tours' ); ?></p>
			<?php if ( ! $elementor ) : ?>
				<p><strong><?php esc_html_e( 'Elementor is not active.', 'beleon-tours' ); ?></strong></p>
			<?php else : ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="beleon_save_layout">
				<?php wp_nonce_field( 'beleon_save_layout' ); ?>
				<table class="form-table" role="presentation">
					<?php foreach ( beleon_slots() as $slot => $def ) : ?>
						<?php $current = isset( $layout[ $slot ] ) ? (int) $layout[ $slot ] : 0; ?>
						<tr>
							<th scope="row"><label for="bl-slot-<?php echo esc_attr( $slot ); ?>"><?php echo esc_html( $def[0] ); ?></label></th>
							<td>
								<select id="bl-slot-<?php echo esc_attr( $slot ); ?>" name="beleon_layout[<?php echo esc_attr( $slot ); ?>]">
									<option value="0"><?php esc_html_e( '— Theme design —', 'beleon-tours' ); ?></option>
									<?php foreach ( $templates as $tpl ) : ?>
										<option value="<?php echo (int) $tpl->ID; ?>" <?php selected( $current, $tpl->ID ); ?>><?php echo esc_html( $tpl->post_title ); ?></option>
									<?php endforeach; ?>
								</select>
								<?php if ( $current ) : ?>
									<a class="button" href="<?php echo esc_url( admin_url( 'post.php?post=' . $current . '&action=elementor' ) ); ?>"><?php esc_html_e( 'Edit with Elementor', 'beleon-tours' ); ?></a>
								<?php else : ?>
									<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=beleon_new_template&slot=' . $slot ), 'beleon_new_template' ) ); ?>"><?php esc_html_e( 'Create in Elementor', 'beleon-tours' ); ?></a>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
				<?php submit_button(); ?>
			</form>
			<p class="description"><?php esc_html_e( 'Tip: tour and destination templates can use the "Beleon" widgets (tour facts, itinerary, departures, gallery, related tours). In the editor they preview the latest tour.', 'beleon-tours' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

add_action(
	'admin_post_beleon_save_layout',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'beleon-tours' ) );
		}
		check_admin_referer( 'beleon_save_layout' );
		$in  = isset( $_POST['beleon_layout'] ) ? (array) wp_unslash( $_POST['beleon_layout'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$out = array();
		foreach ( array_keys( beleon_slots() ) as $slot ) {
			$out[ $slot ] = isset( $in[ $slot ] ) ? absint( $in[ $slot ] ) : 0;
		}
		update_option( 'beleon_layout', $out );
		wp_safe_redirect( admin_url( 'admin.php?page=beleon&updated=1' ) );
		exit;
	}
);

/**
 * Create a blank Elementor template for a slot, assign it and open the editor.
 */
add_action(
	'admin_post_beleon_new_template',
	function () {
		if ( ! current_user_can( 'manage_options' ) || ! did_action( 'elementor/loaded' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'beleon-tours' ) );
		}
		check_admin_referer( 'beleon_new_template' );
		$slot  = isset( $_GET['slot'] ) ? sanitize_key( $_GET['slot'] ) : '';
		$slots = beleon_slots();
		if ( ! isset( $slots[ $slot ] ) ) {
			wp_die( esc_html__( 'Unknown region.', 'beleon-tours' ) );
		}
		$type     = in_array( $slot, array( 'header', 'footer' ), true ) ? 'container' : 'page';
		$document = \Elementor\Plugin::$instance->documents->create(
			$type,
			array(
				'post_title'  => 'Beleon — ' . $slots[ $slot ][0],
				'post_status' => 'publish',
			)
		);
		if ( is_wp_error( $document ) || ! $document ) {
			wp_die( esc_html__( 'Could not create the template.', 'beleon-tours' ) );
		}
		$layout          = (array) get_option( 'beleon_layout', array() );
		$layout[ $slot ] = $document->get_main_id();
		update_option( 'beleon_layout', $layout );
		wp_safe_redirect( $document->get_edit_url() );
		exit;
	}
);

/**
 * Meta keys currently used by a post type (helps mapping JetEngine/ACF fields).
 *
 * @param string $post_type Post type.
 * @return string[]
 */
function beleon_detect_meta_keys( $post_type ) {
	global $wpdb;
	$keys = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare(
			"SELECT DISTINCT pm.meta_key FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE p.post_type = %s AND pm.meta_key NOT LIKE %s ORDER BY pm.meta_key LIMIT 300",
			$post_type,
			$wpdb->esc_like( '_' ) . '%'
		)
	);
	return array_values( array_filter( (array) $keys ) );
}

/**
 * Fields page.
 */
function beleon_admin_fields_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$map  = (array) get_option( 'beleon_field_map', array() );
	$mode = get_option( 'beleon_fields_mode', 'auto' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Tour & destination fields', 'beleon-tours' ); ?></h1>
		<?php beleon_admin_saved_notice(); ?>
		<div class="bl-admin-card">
			<p><?php esc_html_e( 'The theme reads every tour fact through this map. Keep your existing fields (JetEngine, ACF, ...) and type their meta keys here; leave a row empty to use the theme\'s own field.', 'beleon-tours' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="beleon_save_fields">
				<?php wp_nonce_field( 'beleon_save_fields' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Edit fields with', 'beleon-tours' ); ?></th>
						<td>
							<select name="beleon_fields_mode">
								<option value="auto" <?php selected( $mode, 'auto' ); ?>><?php esc_html_e( 'Automatic', 'beleon-tours' ); ?></option>
								<option value="theme" <?php selected( $mode, 'theme' ); ?>><?php esc_html_e( 'Theme boxes on the edit screen', 'beleon-tours' ); ?></option>
								<option value="external" <?php selected( $mode, 'external' ); ?>><?php esc_html_e( 'My plugin (JetEngine / ACF), hide theme boxes', 'beleon-tours' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Automatic shows the theme boxes unless a field below points to another plugin\'s key.', 'beleon-tours' ); ?></p>
						</td>
					</tr>
					<?php foreach ( beleon_field_definitions() as $field => $def ) : ?>
						<tr>
							<th scope="row"><label for="bl-map-<?php echo esc_attr( $field ); ?>"><?php echo esc_html( $def[0] ); ?></label> <small>(<?php echo esc_html( 'tours' === $def[1] ? __( 'tour', 'beleon-tours' ) : __( 'destination', 'beleon-tours' ) ); ?>)</small></th>
							<td>
								<input class="regular-text code" id="bl-map-<?php echo esc_attr( $field ); ?>" name="beleon_field_map[<?php echo esc_attr( $field ); ?>]" value="<?php echo esc_attr( isset( $map[ $field ] ) ? $map[ $field ] : '' ); ?>" placeholder="bl_<?php echo esc_attr( $field ); ?>" list="bl-keys-<?php echo esc_attr( $def[1] ); ?>">
								<p class="description"><?php echo esc_html( $def[3] ); ?></p>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
				<?php foreach ( array( 'tours', 'destinations' ) as $pt ) : ?>
					<datalist id="bl-keys-<?php echo esc_attr( $pt ); ?>">
						<?php foreach ( beleon_detect_meta_keys( $pt ) as $key ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>"></option>
						<?php endforeach; ?>
					</datalist>
				<?php endforeach; ?>
				<?php submit_button(); ?>
			</form>
		</div>
	</div>
	<?php
}

add_action(
	'admin_post_beleon_save_fields',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'beleon-tours' ) );
		}
		check_admin_referer( 'beleon_save_fields' );
		$in  = isset( $_POST['beleon_field_map'] ) ? (array) wp_unslash( $_POST['beleon_field_map'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$out = array();
		foreach ( array_keys( beleon_field_definitions() ) as $field ) {
			$key = isset( $in[ $field ] ) ? preg_replace( '/[^A-Za-z0-9_\-]/', '', (string) $in[ $field ] ) : '';
			if ( $key ) {
				$out[ $field ] = $key;
			}
		}
		update_option( 'beleon_field_map', $out );
		$mode = isset( $_POST['beleon_fields_mode'] ) ? sanitize_key( $_POST['beleon_fields_mode'] ) : 'auto';
		update_option( 'beleon_fields_mode', in_array( $mode, array( 'auto', 'theme', 'external' ), true ) ? $mode : 'auto' );
		wp_cache_delete( 'index', 'beleon_tours' );
		wp_safe_redirect( admin_url( 'admin.php?page=beleon-fields&updated=1' ) );
		exit;
	}
);

add_action(
	'admin_notices',
	function () {
		if ( ! current_user_can( 'install_plugins' ) || did_action( 'elementor/loaded' ) ) {
			return;
		}
		echo '<div class="notice notice-info"><p><strong>Beleon:</strong> ' . esc_html__( 'Install and activate Elementor to edit pages, the header, footer and tour templates visually.', 'beleon-tours' ) . '</p></div>';
	}
);
