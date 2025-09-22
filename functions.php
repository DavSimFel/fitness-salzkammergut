<?php
/**
 * Fitness Salzkammergut Theme Functions
 * - Registers CPTs, Taxonomies, Meta
 * - ICS endpoint for Courses
 * - Probetraining form (shortcode + handler)
 * - Block styles (Card, Badge)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FSG_THEME_VERSION', '1.0.0' );

add_action( 'after_switch_theme', function () {
	flush_rewrite_rules();
} );

/**
 * Register Custom Post Types
 */
add_action( 'init', function () {
	// Trainer
	register_post_type( 'trainer', array(
		'labels' => array(
			'name'          => __( 'Trainer', 'fitness-salzkammergut' ),
			'singular_name' => __( 'Trainer', 'fitness-salzkammergut' ),
		),
		'public'       => true,
		'show_in_rest' => true,
		'has_archive'  => true,
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'menu_icon'    => 'dashicons-groups',
	) );

	// Course
	register_post_type( 'course', array(
		'labels' => array(
			'name'          => __( 'Kurse', 'fitness-salzkammergut' ),
			'singular_name' => __( 'Kurs', 'fitness-salzkammergut' ),
		),
		'public'       => true,
		'show_in_rest' => true,
		'has_archive'  => true,
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'menu_icon'    => 'dashicons-calendar-alt',
	) );

	// Tarif (memberships)
	register_post_type( 'tarif', array(
		'labels' => array(
			'name'          => __( 'Tarife', 'fitness-salzkammergut' ),
			'singular_name' => __( 'Tarif', 'fitness-salzkammergut' ),
		),
		'public'       => true,
		'show_in_rest' => true,
		'has_archive'  => true,
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'menu_icon'    => 'dashicons-tickets-alt',
	) );

	// Offer (Kurzzeit/Karten)
	register_post_type( 'offer', array(
		'labels' => array(
			'name'          => __( 'Angebote', 'fitness-salzkammergut' ),
			'singular_name' => __( 'Angebot', 'fitness-salzkammergut' ),
		),
		'public'       => true,
		'show_in_rest' => true,
		'has_archive'  => true,
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'menu_icon'    => 'dashicons-cart',
	) );

	// Testimonial
	register_post_type( 'testimonial', array(
		'labels' => array(
			'name'          => __( 'Testimonials', 'fitness-salzkammergut' ),
			'singular_name' => __( 'Testimonial', 'fitness-salzkammergut' ),
		),
		'public'       => true,
		'show_in_rest' => true,
		'has_archive'  => false,
		'supports'     => array( 'title', 'editor', 'thumbnail' ),
		'menu_icon'    => 'dashicons-format-quote',
	) );
} );

/**
 * Register Taxonomies
 */
add_action( 'init', function () {
	// Studio taxonomy (vivo, exciting-fit)
	register_taxonomy( 'studio', array( 'post', 'course', 'trainer', 'tarif' ), array(
		'labels'       => array(
			'name'          => __( 'Studios', 'fitness-salzkammergut' ),
			'singular_name' => __( 'Studio', 'fitness-salzkammergut' ),
		),
		'public'       => true,
		'show_in_rest' => true,
		'hierarchical' => false,
		'rewrite'      => array( 'slug' => 'studio' ),
	) );

	// Ziel taxonomy
	register_taxonomy( 'ziel', array( 'post', 'course', 'tarif' ), array(
		'labels'       => array(
			'name'          => __( 'Ziele', 'fitness-salzkammergut' ),
			'singular_name' => __( 'Ziel', 'fitness-salzkammergut' ),
		),
		'public'       => true,
		'show_in_rest' => true,
		'hierarchical' => false,
		'rewrite'      => array( 'slug' => 'ziel' ),
	) );

	// Kurskategorie taxonomy (optional)
	register_taxonomy( 'kurskategorie', array( 'course' ), array(
		'labels'       => array(
			'name'          => __( 'Kurskategorien', 'fitness-salzkammergut' ),
			'singular_name' => __( 'Kurskategorie', 'fitness-salzkammergut' ),
		),
		'public'       => true,
		'show_in_rest' => true,
		'hierarchical' => true,
		'rewrite'      => array( 'slug' => 'kurskategorie' ),
	) );

	// Level taxonomy (Einsteiger, Fortgeschrittene)
	register_taxonomy( 'level', array( 'course' ), array(
		'labels'       => array(
			'name'          => __( 'Level', 'fitness-salzkammergut' ),
			'singular_name' => __( 'Level', 'fitness-salzkammergut' ),
		),
		'public'       => true,
		'show_in_rest' => true,
		'hierarchical' => false,
		'rewrite'      => array( 'slug' => 'level' ),
	) );

	// Raum/Ort taxonomy
	register_taxonomy( 'raum', array( 'course' ), array(
		'labels'       => array(
			'name'          => __( 'Räume/Orte', 'fitness-salzkammergut' ),
			'singular_name' => __( 'Raum/Ort', 'fitness-salzkammergut' ),
		),
		'public'       => true,
		'show_in_rest' => true,
		'hierarchical' => true,
		'rewrite'      => array( 'slug' => 'raum' ),
	) );
} );

/**
 * Register Post Meta
 */
add_action( 'init', function () {
	// Course meta
	register_post_meta( 'course', 'weekday', array(
		'type'              => 'integer',
		'single'            => true,
		'default'           => 1, // 1=Mon ... 7=Sun (ISO-8601)
		'show_in_rest'      => true,
		'auth_callback'     => '__return_true',
		'sanitize_callback' => 'absint',
	) );
	register_post_meta( 'course', 'start_time', array(
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'auth_callback'     => '__return_true',
		'sanitize_callback' => 'sanitize_text_field', // "HH:MM"
	) );
	register_post_meta( 'course', 'end_time', array(
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'auth_callback'     => '__return_true',
		'sanitize_callback' => 'sanitize_text_field', // "HH:MM"
	) );
	register_post_meta( 'course', 'duration_min', array(
		'type'              => 'integer',
		'single'            => true,
		'default'           => 60,
		'show_in_rest'      => true,
		'auth_callback'     => '__return_true',
		'sanitize_callback' => 'absint',
	) );
	register_post_meta( 'course', 'requires_booking', array(
		'type'              => 'boolean',
		'single'            => true,
		'default'           => false,
		'show_in_rest'      => true,
		'auth_callback'     => '__return_true',
	) );
	register_post_meta( 'course', 'capacity', array(
		'type'              => 'integer',
		'single'            => true,
		'default'           => 0,
		'show_in_rest'      => true,
		'auth_callback'     => '__return_true',
		'sanitize_callback' => 'absint',
	) );
	register_post_meta( 'course', 'restplaetze', array(
		'type'              => 'integer',
		'single'            => true,
		'default'           => 0,
		'show_in_rest'      => true,
		'auth_callback'     => '__return_true',
		'sanitize_callback' => 'absint',
	) );
	register_post_meta( 'course', 'ics_uid', array(
		'type'              => 'string',
		'single'            => true,
		'default'           => '',
		'show_in_rest'      => true,
		'auth_callback'     => '__return_true',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	register_post_meta( 'course', 'highlight_state', array(
		'type'              => 'string', // "past" | "now" | "next"
		'single'            => true,
		'default'           => '',
		'show_in_rest'      => true,
		'auth_callback'     => '__return_true',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	// Trainers related (list of user IDs or post IDs could live separately via relationship — keep simple strings/arrays)
	register_post_meta( 'course', 'trainer_ids', array(
		'type'              => 'array',
		'single'            => true,
		'show_in_rest'      => array(
			'schema' => array(
				'type'  => 'array',
				'items' => array( 'type' => 'integer' ),
			),
		),
		'auth_callback'     => '__return_true',
	) );

	// Trainer meta
	register_post_meta( 'trainer', 'position', array(
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'auth_callback'     => '__return_true',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	register_post_meta( 'trainer', 'qualifications', array(
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'auth_callback'     => '__return_true',
		'sanitize_callback' => 'wp_kses_post',
	) );
	register_post_meta( 'trainer', 'social_links', array(
		'type'              => 'array',
		'single'            => true,
		'show_in_rest'      => array(
			'schema' => array(
				'type'  => 'array',
				'items' => array( 'type' => 'string' ),
			),
		),
		'auth_callback'     => '__return_true',
	) );
	register_post_meta( 'trainer', 'studios', array(
		'type'              => 'array',
		'single'            => true,
		'show_in_rest'      => array(
			'schema' => array(
				'type'  => 'array',
				'items' => array( 'type' => 'integer' ),
			),
		),
		'auth_callback'     => '__return_true',
	) );

	// Tarif meta
	register_post_meta( 'tarif', 'price', array(
		'type'              => 'string', // keep as string to allow "€ xx,– / Monat"
		'single'            => true,
		'show_in_rest'      => true,
		'auth_callback'     => '__return_true',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	register_post_meta( 'tarif', 'is_popular', array(
		'type'              => 'boolean',
		'single'            => true,
		'default'           => false,
		'show_in_rest'      => true,
		'auth_callback'     => '__return_true',
	) );
	register_post_meta( 'tarif', 'is_membership', array(
		'type'              => 'boolean',
		'single'            => true,
		'default'           => true,
		'show_in_rest'      => true,
		'auth_callback'     => '__return_true',
	) );
	register_post_meta( 'tarif', 'applies_both_studios', array(
		'type'              => 'boolean',
		'single'            => true,
		'default'           => true,
		'show_in_rest'      => true,
		'auth_callback'     => '__return_true',
	) );

	// Testimonial meta
	register_post_meta( 'testimonial', 'rating_value', array(
		'type'              => 'number',
		'single'            => true,
		'default'           => 5,
		'show_in_rest'      => true,
		'auth_callback'     => '__return_true',
		'sanitize_callback' => 'floatval',
	) );
	register_post_meta( 'testimonial', 'source_name', array(
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'auth_callback'     => '__return_true',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	register_post_meta( 'testimonial', 'source_location', array(
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'auth_callback'     => '__return_true',
		'sanitize_callback' => 'sanitize_text_field',
	) );
} );

/**
 * Block Styles (Gutenberg-native)
 */
add_action( 'init', function () {
	if ( function_exists( 'register_block_style' ) ) {
		register_block_style( 'core/group', array(
			'name'  => 'card',
			'label' => __( 'Card', 'fitness-salzkammergut' ),
		) );
		register_block_style( 'core/paragraph', array(
			'name'  => 'badge',
			'label' => __( 'Badge', 'fitness-salzkammergut' ),
		) );
	}
} );

/**
 * ICS endpoint for Course CPT
 * - Supports /?course_ics={ID} or pretty permalink /course-ics/{ID}
 */
add_action( 'init', function () {
	add_rewrite_rule( '^course-ics/([0-9]+)/?$', 'index.php?course_ics=$matches[1]', 'top' );
} );

add_filter( 'query_vars', function ( $vars ) {
	$vars[] = 'course_ics';
	return $vars;
} );

add_action( 'template_redirect', function () {
	$course_id = get_query_var( 'course_ics' );
	if ( empty( $course_id ) ) {
		$course_id = isset( $_GET['course_ics'] ) ? absint( $_GET['course_ics'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
	if ( $course_id ) {
		fsg_output_course_ics( $course_id );
		exit;
	}
} );

/**
 * Compute next occurrence and output ICS
 */
function fsg_output_course_ics( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post || 'course' !== $post->post_type ) {
		status_header( 404 );
		wp_die( esc_html__( 'Course not found.', 'fitness-salzkammergut' ) );
	}

	$site_tz = wp_timezone();
	$weekday = (int) get_post_meta( $post_id, 'weekday', true ); // 1..7 (Mon..Sun)
	$start   = (string) get_post_meta( $post_id, 'start_time', true ); // "HH:MM"
	$end     = (string) get_post_meta( $post_id, 'end_time', true );
	$uid     = (string) get_post_meta( $post_id, 'ics_uid', true );

	if ( ! $weekday || ! preg_match( '/^\d{2}:\d{2}$/', $start ) ) {
		status_header( 400 );
		wp_die( esc_html__( 'Missing time/weekday meta.', 'fitness-salzkammergut' ) );
	}

	try {
		$now     = new DateTimeImmutable( 'now', $site_tz );
		$todayN  = (int) $now->format( 'N' ); // 1..7
		$days_a  = ( $weekday - $todayN + 7 ) % 7;

		// If today and time already passed, move to next week.
		$start_today = DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $now->format( 'Y-m-d' ) . ' ' . $start, $site_tz );
		if ( $days_a === 0 && $start_today && $start_today <= $now ) {
			$days_a = 7;
		}

		$base    = $now->modify( "+{$days_a} days" );
		$dtstart = DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $base->format( 'Y-m-d' ) . ' ' . $start, $site_tz );
		$dtend   = null;

		if ( preg_match( '/^\d{2}:\d{2}$/', $end ) ) {
			$dtend = DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $base->format( 'Y-m-d' ) . ' ' . $end, $site_tz );
		} else {
			$duration = (int) get_post_meta( $post_id, 'duration_min', true );
			$duration = $duration > 0 ? $duration : 60;
			$dtend    = $dtstart->modify( "+{$duration} minutes" );
		}

		// Convert to UTC for ICS
		$dtstamp_utc = $now->setTimezone( new DateTimeZone( 'UTC' ) );
		$dtstart_utc = $dtstart->setTimezone( new DateTimeZone( 'UTC' ) );
		$dtend_utc   = $dtend->setTimezone( new DateTimeZone( 'UTC' ) );

		$title   = html_entity_decode( wp_strip_all_tags( get_the_title( $post_id ) ), ENT_QUOTES );
		$desc    = html_entity_decode( wp_strip_all_tags( get_the_excerpt( $post_id ) ), ENT_QUOTES );
		$studio  = '';
		$terms   = get_the_terms( $post_id, 'studio' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$studio = $terms[0]->name;
		}
		$uid     = $uid ? $uid : ( $post_id . '@' . wp_parse_url( home_url(), PHP_URL_HOST ) );

		$ics_lines = array(
			'BEGIN:VCALENDAR',
			'VERSION:2.0',
			'CALSCALE:GREGORIAN',
			'PRODID:-//Fitness Salzkammergut//Course//DE',
			'METHOD:PUBLISH',
			'BEGIN:VEVENT',
			'UID:' . fsg_ics_escape( $uid ),
			'DTSTAMP:' . $dtstamp_utc->format( 'Ymd\THis\Z' ),
			'DTSTART:' . $dtstart_utc->format( 'Ymd\THis\Z' ),
			'DTEND:' . $dtend_utc->format( 'Ymd\THis\Z' ),
			'SUMMARY:' . fsg_ics_escape( $title ),
		);

		if ( $studio ) {
			$ics_lines[] = 'LOCATION:' . fsg_ics_escape( $studio );
		}
		if ( $desc ) {
			$ics_lines[] = 'DESCRIPTION:' . fsg_ics_escape( $desc );
		}

		$ics_lines[] = 'URL:' . fsg_ics_escape( get_permalink( $post_id ) );
		$ics_lines[] = 'END:VEVENT';
		$ics_lines[] = 'END:VCALENDAR';

		$ics = implode( "\r\n", $ics_lines ) . "\r\n";

		nocache_headers();
		header( 'Content-Type: text/calendar; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="course-' . $post_id . '.ics"' );
		echo $ics; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} catch ( Exception $e ) {
		status_header( 500 );
		wp_die( esc_html( $e->getMessage() ) );
	}
}

function fsg_ics_escape( $text ) {
	$text = str_replace( array( "\\", ";", ",", "\n" ), array( "\\\\", "\;", "\,", "\\n" ), $text );
	return $text;
}

/**
 * Probetraining form
 * - Shortcode: [probetraining_form]
 * - POST handler sends wp_mail to admin
 */
add_shortcode( 'probetraining_form', function ( $atts ) {
	$atts = shortcode_atts( array(
		'redirect' => '', // optional URL to redirect
	), $atts, 'probetraining_form' );

	$action = esc_url( add_query_arg( array(), remove_query_arg( array( 'probetraining' ) ) ) );
	ob_start();

	// Success / error messages
	if ( isset( $_GET['probetraining'] ) && 'success' === $_GET['probetraining'] ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		?>
		<div class="wp-block-group is-style-card" role="status" aria-live="polite" style="padding:1rem;background:#F8F8F7;border-left:4px solid #B0E336;">
			<strong><?php esc_html_e( 'Danke!', 'fitness-salzkammergut' ); ?></strong>
			<?php esc_html_e( 'Wir melden uns in Kürze für dein Probetraining.', 'fitness-salzkammergut' ); ?>
		</div>
		<?php
	endif;
	?>
	<form class="fsg-probetraining-form" method="post" action="<?php echo $action; ?>">
		<?php wp_nonce_field( 'fsg_probetraining', 'fsg_probetraining_nonce' ); ?>
		<input type="hidden" name="fsg_probetraining_submit" value="1" />
		<div class="wp-block-group is-style-card" style="gap:12px;padding:16px;">
			<div>
				<label>
					<?php esc_html_e( 'Name', 'fitness-salzkammergut' ); ?><br />
					<input type="text" name="fsg_name" required />
				</label>
			</div>
			<div>
				<label>
					<?php esc_html_e( 'E-Mail', 'fitness-salzkammergut' ); ?><br />
					<input type="email" name="fsg_email" required />
				</label>
			</div>
			<div>
				<label>
					<?php esc_html_e( 'Telefon', 'fitness-salzkammergut' ); ?><br />
					<input type="tel" name="fsg_phone" />
				</label>
			</div>
			<div>
				<label>
					<?php esc_html_e( 'Studio', 'fitness-salzkammergut' ); ?><br />
					<select name="fsg_studio">
						<option value=""><?php esc_html_e( 'Bitte wählen…', 'fitness-salzkammergut' ); ?></option>
						<option value="vivo">VIVO</option>
						<option value="exciting-fit">Exciting Fit</option>
					</select>
				</label>
			</div>
			<div>
				<label>
					<?php esc_html_e( 'Nachricht', 'fitness-salzkammergut' ); ?><br />
					<textarea name="fsg_message" rows="4"></textarea>
				</label>
			</div>
			<div>
				<button type="submit" class="wp-element-button"><?php esc_html_e( 'Probetraining anfragen', 'fitness-salzkammergut' ); ?></button>
			</div>
		</div>
	</form>
	<?php
	return ob_get_clean();
} );

add_action( 'init', function () {
	if ( ! isset( $_POST['fsg_probetraining_submit'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}
	if ( ! isset( $_POST['fsg_probetraining_nonce'] ) || ! wp_verify_nonce( $_POST['fsg_probetraining_nonce'], 'fsg_probetraining' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}

	$name    = isset( $_POST['fsg_name'] ) ? sanitize_text_field( wp_unslash( $_POST['fsg_name'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$email   = isset( $_POST['fsg_email'] ) ? sanitize_email( wp_unslash( $_POST['fsg_email'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$phone   = isset( $_POST['fsg_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['fsg_phone'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$studio  = isset( $_POST['fsg_studio'] ) ? sanitize_text_field( wp_unslash( $_POST['fsg_studio'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$message = isset( $_POST['fsg_message'] ) ? wp_kses_post( wp_unslash( $_POST['fsg_message'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

	$to      = get_option( 'admin_email' );
	$subject = sprintf( '[Probetraining] %s', $name ? $name : __( 'Anfrage', 'fitness-salzkammergut' ) );
	$body    = sprintf(
		"Name: %s\nE-Mail: %s\nTelefon: %s\nStudio: %s\n\nNachricht:\n%s\n\nQuelle: %s",
		$name,
		$email,
		$phone,
		$studio,
		wp_strip_all_tags( $message ),
		home_url( add_query_arg( array(), remove_query_arg( array( 'probetraining' ) ) ) )
	);
	$headers = array();
	if ( $email ) {
		$headers[] = 'Reply-To: ' . $name . ' <' . $email . '>';
	}

	// Send and redirect with success flag
	wp_mail( $to, $subject, $body, $headers );

	$redirect = add_query_arg( 'probetraining', 'success', wp_get_referer() ? wp_get_referer() : home_url( '/tarife' ) );
	wp_safe_redirect( $redirect );
	exit;
} );
