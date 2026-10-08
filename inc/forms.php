<?php
/**
 * Booking and contact forms.
 *
 * No form plugin needed: the forms post to admin-post.php (with fetch when
 * JavaScript is available, a normal POST + redirect otherwise). Every
 * submission is stored under Beleon > Enquiries and emailed to the office.
 * Spam protection: a honeypot field, a signed time token (bots submit
 * instantly) and a per-visitor rate limit.
 *
 * Use: [beleon_form type="contact"] or [beleon_form type="booking" tour="123"],
 * the "Form" Elementor widget, or beleon_render_form() in templates.
 *
 * @package beleon-tours
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Storage for submissions (private, listed under Beleon > Enquiries).
 */
add_action(
	'init',
	function () {
		register_post_type(
			'bl_enquiry',
			array(
				'labels'          => array(
					'name'          => __( 'Enquiries', 'beleon-tours' ),
					'singular_name' => __( 'Enquiry', 'beleon-tours' ),
					'edit_item'     => __( 'Enquiry', 'beleon-tours' ),
					'all_items'     => __( 'Enquiries', 'beleon-tours' ),
					'not_found'     => __( 'No enquiries yet.', 'beleon-tours' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => 'beleon',
				'supports'        => array( 'title', 'editor' ),
				'capability_type' => 'post',
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'    => true,
			)
		);
	}
);

add_filter(
	'manage_bl_enquiry_posts_columns',
	function ( $cols ) {
		return array(
			'cb'        => $cols['cb'],
			'title'     => __( 'Enquiry', 'beleon-tours' ),
			'bl_type'   => __( 'Form', 'beleon-tours' ),
			'bl_email'  => __( 'Email', 'beleon-tours' ),
			'bl_phone'  => __( 'Phone', 'beleon-tours' ),
			'date'      => $cols['date'],
		);
	}
);
add_action(
	'manage_bl_enquiry_posts_custom_column',
	function ( $col, $id ) {
		if ( 'bl_type' === $col ) {
			echo esc_html( 'booking' === get_post_meta( $id, 'bl_type', true ) ? __( 'Booking', 'beleon-tours' ) : __( 'Contact', 'beleon-tours' ) );
		} elseif ( 'bl_email' === $col ) {
			$email = (string) get_post_meta( $id, 'bl_email', true );
			echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
		} elseif ( 'bl_phone' === $col ) {
			echo esc_html( (string) get_post_meta( $id, 'bl_phone', true ) );
		}
	},
	10,
	2
);

/**
 * Fields of each form: name => [ label, type, required, width ].
 *
 * @param string $type contact|booking.
 * @return array
 */
function beleon_form_fields( $type ) {
	$common = array(
		'first_name' => array( __( 'First name', 'beleon-tours' ), 'text', true, 'half' ),
		'last_name'  => array( __( 'Last name', 'beleon-tours' ), 'text', true, 'half' ),
		'email'      => array( __( 'Email', 'beleon-tours' ), 'email', true, 'half' ),
		'phone'      => array( __( 'Phone', 'beleon-tours' ), 'tel', 'booking' === $type, 'half' ),
	);
	if ( 'booking' === $type ) {
		$fields = array(
			'departure' => array( __( 'Departure date', 'beleon-tours' ), 'departure', true, 'half' ),
			'travelers' => array( __( 'Travellers', 'beleon-tours' ), 'number', true, 'half' ),
		) + $common + array(
			'message' => array( __( 'Questions or requests (room type, single room, flights from…)', 'beleon-tours' ), 'textarea', false, 'full' ),
		);
	} else {
		$fields = $common + array(
			'subject' => array( __( 'Subject', 'beleon-tours' ), 'text', false, 'full' ),
			'message' => array( __( 'Message', 'beleon-tours' ), 'textarea', true, 'full' ),
		);
	}
	return apply_filters( 'beleon_form_fields', $fields, $type );
}

/**
 * Signed time token: proves the form was rendered by the site, and when.
 *
 * @param int $time Unix time.
 * @return string
 */
function beleon_form_token( $time ) {
	return $time . '.' . substr( hash_hmac( 'sha256', 'beleon-form|' . $time, wp_salt( 'nonce' ) ), 0, 20 );
}

/**
 * Form markup.
 *
 * @param string $type contact|booking.
 * @param array  $o    tour (ID), title, text, button, id.
 * @return string
 */
function beleon_render_form( $type = 'contact', $o = array() ) {
	$type = 'booking' === $type ? 'booking' : 'contact';
	$o    = wp_parse_args(
		$o,
		array(
			'tour'   => 'booking' === $type && is_singular( 'tours' ) ? get_queried_object_id() : 0,
			'button' => 'booking' === $type ? __( 'Send booking request', 'beleon-tours' ) : __( 'Send message', 'beleon-tours' ),
			'id'     => 'bl-form-' . $type,
		)
	);
	$tour = (int) $o['tour'];
	// phpcs:disable WordPress.Security.NonceVerification
	$sent    = isset( $_GET['bl_sent'] ) && sanitize_key( wp_unslash( $_GET['bl_sent'] ) ) === $type;
	$prefill = isset( $_GET['tour'] ) ? sanitize_text_field( wp_unslash( $_GET['tour'] ) ) : '';
	// phpcs:enable

	$h  = '<form class="bl-form bl-form--' . esc_attr( $type ) . '" id="' . esc_attr( $o['id'] ) . '" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" data-bl-form novalidate>';
	$h .= '<input type="hidden" name="action" value="beleon_form">';
	$h .= '<input type="hidden" name="bl_type" value="' . esc_attr( $type ) . '">';
	$h .= '<input type="hidden" name="bl_t" value="' . esc_attr( beleon_form_token( time() ) ) . '">';
	$h .= '<input type="hidden" name="bl_back" value="' . esc_url( remove_query_arg( 'bl_sent', home_url( add_query_arg( array() ) ) ) ) . '">';
	if ( $tour ) {
		$h .= '<input type="hidden" name="bl_tour" value="' . $tour . '">';
	}
	$h .= '<div class="bl-form__hp" aria-hidden="true"><label>Website<input type="text" name="bl_website" tabindex="-1" autocomplete="off"></label></div>';

	if ( 'booking' === $type && $tour ) {
		$h .= '<p class="bl-form__tour">' . beleon_icon( 'compass' ) . '<span><small>' . esc_html__( 'Tour', 'beleon-tours' ) . '</small>' . esc_html( get_the_title( $tour ) ) . '</span></p>';
	}

	$h .= '<div class="bl-form__grid">';
	foreach ( beleon_form_fields( $type ) as $name => $f ) {
		list( $label, $ftype, $required, $width ) = $f;
		$id   = $o['id'] . '-' . $name;
		$req  = $required ? ' required aria-required="true"' : '';
		$mark = $required ? ' <span class="bl-form__req" aria-hidden="true">*</span>' : '';
		$h   .= '<p class="bl-form__field bl-form__field--' . esc_attr( $width ) . '"><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . $mark . '</label>';
		if ( 'textarea' === $ftype ) {
			$h .= '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" rows="5" maxlength="4000"' . $req . '></textarea>';
		} elseif ( 'departure' === $ftype ) {
			$h .= '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"' . $req . '>';
			$deps = $tour ? beleon_departures( $tour ) : array();
			foreach ( $deps as $d ) {
				$h .= '<option>' . esc_html( $d['label'] . ( $d['note'] ? ' — ' . $d['note'] : '' ) ) . '</option>';
			}
			$h .= '<option value="' . esc_attr__( 'Other date / private departure', 'beleon-tours' ) . '">' . esc_html__( 'Other date / private departure', 'beleon-tours' ) . '</option></select>';
		} else {
			$attrs = '';
			$value = '';
			if ( 'number' === $ftype ) {
				$attrs = ' min="1" max="60" inputmode="numeric"';
				$value = '2';
			} elseif ( 'email' === $ftype ) {
				$attrs = ' autocomplete="email"';
			} elseif ( 'tel' === $ftype ) {
				$attrs = ' autocomplete="tel"';
			} elseif ( 'first_name' === $name ) {
				$attrs = ' autocomplete="given-name"';
			} elseif ( 'last_name' === $name ) {
				$attrs = ' autocomplete="family-name"';
			} elseif ( 'subject' === $name && $prefill ) {
				$value = $prefill;
			}
			$h .= '<input id="' . esc_attr( $id ) . '" type="' . esc_attr( $ftype ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" maxlength="200"' . $attrs . $req . '>';
		}
		$h .= '</p>';
	}
	$h .= '</div>';

	$privacy = get_privacy_policy_url();
	$consent = $privacy
		/* translators: %s: privacy policy link */
		? sprintf( __( 'I agree that Beleon Tours may use my details to answer this request (%s).', 'beleon-tours' ), '<a href="' . esc_url( $privacy ) . '" target="_blank" rel="noopener">' . esc_html__( 'privacy policy', 'beleon-tours' ) . '</a>' )
		: esc_html__( 'I agree that Beleon Tours may use my details to answer this request.', 'beleon-tours' );
	$h .= '<p class="bl-form__consent"><label><input type="checkbox" name="consent" value="1" required aria-required="true"> <span>' . wp_kses( $consent, array( 'a' => array( 'href' => true, 'target' => true, 'rel' => true ) ) ) . '</span></label></p>';
	$h .= '<div class="bl-form__foot"><button class="bl-btn bl-btn--solid" type="submit"><span>' . esc_html( $o['button'] ) . '</span>' . beleon_icon( 'arrow', 'bl-btn__ico' ) . '</button>';
	if ( 'booking' === $type ) {
		$h .= '<p class="bl-form__hint">' . esc_html__( 'No payment now — we reply within one working day with availability and details.', 'beleon-tours' ) . '</p>';
	}
	$h .= '</div>';
	$h .= '<p class="bl-form__status" role="status" aria-live="polite"' . ( $sent ? '' : ' hidden' ) . ' data-ok="' . esc_attr( beleon_form_message( 'ok' ) ) . '">' . ( $sent ? esc_html( beleon_form_message( 'ok' ) ) : '' ) . '</p>';
	return $h . '</form>';
}

/**
 * Messages shown after submitting.
 *
 * @param string $key ok|invalid|spam|limit|error.
 * @return string
 */
function beleon_form_message( $key ) {
	$m = array(
		'ok'      => __( 'Thank you! We received your request and will contact you shortly.', 'beleon-tours' ),
		'invalid' => __( 'Please fill in the required fields correctly.', 'beleon-tours' ),
		'spam'    => __( 'Your message could not be sent. Please reload the page and try again.', 'beleon-tours' ),
		'limit'   => __( 'Too many messages in a short time. Please try again later or call us.', 'beleon-tours' ),
		'error'   => __( 'Something went wrong. Please try again or call us.', 'beleon-tours' ),
	);
	return isset( $m[ $key ] ) ? $m[ $key ] : $m['error'];
}

/**
 * Handle a submission.
 */
function beleon_handle_form() {
	// phpcs:disable WordPress.Security.NonceVerification -- public form, protected by the signed time token.
	$ajax = ! empty( $_POST['bl_ajax'] );
	$type = isset( $_POST['bl_type'] ) && 'booking' === $_POST['bl_type'] ? 'booking' : 'contact';
	$back = isset( $_POST['bl_back'] ) ? wp_validate_redirect( esc_url_raw( wp_unslash( $_POST['bl_back'] ) ), home_url( '/' ) ) : home_url( '/' );

	$fail = function ( $code, $errors = array() ) use ( $ajax, $back ) {
		if ( $ajax ) {
			wp_send_json_error(
				array(
					'message' => beleon_form_message( $code ),
					'fields'  => $errors,
				),
				'invalid' === $code ? 422 : 400
			);
		}
		wp_die( esc_html( beleon_form_message( $code ) ), '', array( 'response' => 400, 'back_link' => true ) );
	};

	// Spam checks.
	$token = isset( $_POST['bl_t'] ) ? sanitize_text_field( wp_unslash( $_POST['bl_t'] ) ) : '';
	$time  = (int) strtok( $token, '.' );
	if ( ! empty( $_POST['bl_website'] ) || ! hash_equals( beleon_form_token( $time ), $token ) || time() - $time < 3 || time() - $time > 7 * DAY_IN_SECONDS ) {
		$fail( 'spam' );
	}
	$ip_key = 'bl_form_' . substr( md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' ), 0, 12 );
	$count  = (int) get_transient( $ip_key );
	if ( $count >= 6 ) {
		$fail( 'limit' );
	}

	// Values.
	$tour   = isset( $_POST['bl_tour'] ) ? absint( $_POST['bl_tour'] ) : 0;
	$tour   = $tour && 'tours' === get_post_type( $tour ) ? $tour : 0;
	$fields = beleon_form_fields( $type );
	$values = array();
	$errors = array();
	foreach ( $fields as $name => $f ) {
		$raw = isset( $_POST[ $name ] ) ? wp_unslash( $_POST[ $name ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$val = 'textarea' === $f[1] ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
		$val = mb_substr( $val, 0, 'textarea' === $f[1] ? 4000 : 200 );
		if ( 'email' === $f[1] && '' !== $val && ! is_email( $val ) ) {
			$errors[] = $name;
		}
		if ( 'number' === $f[1] && '' !== $val ) {
			$val = (string) max( 1, min( 60, (int) $val ) );
		}
		if ( $f[2] && '' === trim( $val ) ) {
			$errors[] = $name;
		}
		$values[ $name ] = $val;
	}
	if ( empty( $_POST['consent'] ) ) {
		$errors[] = 'consent';
	}
	// phpcs:enable
	if ( $errors ) {
		$fail( 'invalid', array_values( array_unique( $errors ) ) );
	}
	set_transient( $ip_key, $count + 1, 10 * MINUTE_IN_SECONDS );

	// Store.
	$name  = trim( $values['first_name'] . ' ' . $values['last_name'] );
	$label = 'booking' === $type ? __( 'Booking', 'beleon-tours' ) : __( 'Contact', 'beleon-tours' );
	$title = $label . ': ' . ( $tour ? get_the_title( $tour ) . ' — ' : ( ! empty( $values['subject'] ) ? $values['subject'] . ' — ' : '' ) ) . $name;
	$lines = array();
	if ( $tour ) {
		$lines[] = __( 'Tour', 'beleon-tours' ) . ': ' . get_the_title( $tour ) . ' — ' . get_permalink( $tour );
	}
	foreach ( $fields as $key => $f ) {
		if ( '' !== $values[ $key ] ) {
			$lines[] = $f[0] . ': ' . $values[ $key ];
		}
	}
	$lines[] = __( 'Sent from', 'beleon-tours' ) . ': ' . $back;
	$body    = implode( "\n", $lines );

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'bl_enquiry',
			'post_status'  => 'private',
			'post_title'   => wp_strip_all_tags( $title ),
			'post_content' => $body,
		)
	);
	if ( $post_id && ! is_wp_error( $post_id ) ) {
		update_post_meta( $post_id, 'bl_type', $type );
		update_post_meta( $post_id, 'bl_email', $values['email'] );
		update_post_meta( $post_id, 'bl_phone', $values['phone'] );
		update_post_meta( $post_id, 'bl_tour', $tour );
		update_post_meta( $post_id, 'bl_values', $values );
	}

	// Email the office.
	$to = beleon_mod( 'beleon_form_email' );
	$to = $to ? $to : beleon_mod( 'beleon_email' );
	$to = $to ? $to : get_option( 'admin_email' );
	$to = array_filter( array_map( 'trim', explode( ',', $to ) ), 'is_email' );
	$headers = array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . str_replace( array( "\r", "\n", ',' ), '', $name ) . ' <' . $values['email'] . '>' );
	$sent    = $to ? wp_mail( $to, '[' . get_bloginfo( 'name' ) . '] ' . $title, $body, apply_filters( 'beleon_form_headers', $headers, $type ) ) : false;
	if ( $post_id && ! is_wp_error( $post_id ) ) {
		update_post_meta( $post_id, 'bl_mailed', $sent ? 1 : 0 );
	}
	do_action( 'beleon_form_submitted', $type, $values, $tour, $post_id );

	if ( $ajax ) {
		wp_send_json_success( array( 'message' => beleon_form_message( 'ok' ) ) );
	}
	wp_safe_redirect( add_query_arg( 'bl_sent', $type, $back ) . '#bl-form-' . $type );
	exit;
}
add_action( 'admin_post_beleon_form', 'beleon_handle_form' );
add_action( 'admin_post_nopriv_beleon_form', 'beleon_handle_form' );

add_shortcode(
	'beleon_form',
	function ( $atts ) {
		$atts = shortcode_atts(
			array(
				'type'   => 'contact',
				'tour'   => 0,
				'button' => '',
			),
			$atts,
			'beleon_form'
		);
		$o = array( 'tour' => (int) $atts['tour'] );
		if ( '' !== $atts['button'] ) {
			$o['button'] = $atts['button'];
		}
		if ( ! $o['tour'] ) {
			unset( $o['tour'] );
		}
		return beleon_render_form( $atts['type'], $o );
	}
);
