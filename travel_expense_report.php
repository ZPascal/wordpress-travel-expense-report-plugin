<?php
/**
 * Plugin Name: Travel Expense Report
 * Description: Creates a form for travel expense reports with dynamic items and mileage allowances.
 * Version: 1.0.0
 * Author: Pascal Zimmermann
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: travel-expense-report
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 8.1
 *
 * @package TravelExpenseReport
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'TER_PLUGIN_FILE', __FILE__ );
define( 'TER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Register activation and deactivation hooks.
register_activation_hook( __FILE__, 'ter_activate_plugin' );
register_deactivation_hook( __FILE__, 'ter_deactivate_plugin' );

/**
 * Plugin activation hook
 * Initialize plugin options and set up defaults
 */
function ter_activate_plugin() {
	// Ensure defaults are set.
	if ( ! get_option( 'ter_config' ) ) {
		$defaults = array(
			'recipient_email'           => get_option( 'admin_email' ),
			'send_customer_copy'        => 0,
			'delete_uploads_after_send' => 0,
			'temp_folder'               => 'travel-expense-report',
			'subject_prefix'            => '[Travel Expense]',
			'rate_km_allowance'         => '0.30',
			'rate_passenger_allowance'  => '0.01',
		);
		add_option( 'ter_config', $defaults );
	}

	// Flush rewrite rules if needed.
	flush_rewrite_rules();
}

/**
 * Plugin deactivation hook
 * Clean up temporary files and cache
 */
function ter_deactivate_plugin() {
	// Flush rewrite rules.
	flush_rewrite_rules();
}

// Include configuration and i18n systems.
// CRITICAL: Load in this order to avoid function not found errors.
if ( ! function_exists( 'ter_get_config' ) ) {
	require_once TER_PLUGIN_DIR . 'includes/config.php';
}
if ( ! function_exists( 'ter_load_textdomain' ) ) {
	require_once TER_PLUGIN_DIR . 'includes/i18n.php';
}
if ( ! function_exists( 'ter_add_settings_page' ) ) {
	require_once TER_PLUGIN_DIR . 'includes/settings-admin.php';
}

// Load TCPDF library (if available).
// Uses tecnickcom/tcpdf from https://github.com/tecnickcom/TCPDF.
$tcpdf_path = TER_PLUGIN_DIR . 'vendor/tcpdf/tcpdf.php';
$fpdi_path  = TER_PLUGIN_DIR . 'vendor/fpdi/src/autoload.php';

if ( file_exists( $tcpdf_path ) && file_exists( $fpdi_path ) ) {
	try {
		require_once $tcpdf_path;
		require_once $fpdi_path;
		define( 'TER_TCPDF_AVAILABLE', true );
	} catch ( Exception $e ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			ter_log( 'TER: Error loading TCPDF: ' . $e->getMessage() );
		}
		define( 'TER_TCPDF_AVAILABLE', false );
	}
} else {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		ter_log( 'TER: TCPDF files not found. TCPDF_PATH: ' . $tcpdf_path . ', FPDI_PATH: ' . $fpdi_path );
	}
	define( 'TER_TCPDF_AVAILABLE', false );
}

// Register shortcode.
add_shortcode( 'travel_expense_report', 'ter_render_form' );

// AJAX handler for email sending.
add_action( 'wp_ajax_ter_send_email', 'ter_send_email' );
add_action( 'wp_ajax_nopriv_ter_send_email', 'ter_send_email' );

// AJAX handler for file upload.
add_action( 'wp_ajax_ter_upload_receipt', 'ter_upload_receipt' );
add_action( 'wp_ajax_nopriv_ter_upload_receipt', 'ter_upload_receipt' );

// AJAX handler for test email from settings (admin only).
add_action( 'wp_ajax_ter_send_test_email', 'ter_send_test_email' );

/**
 * Send a test email to the configured recipient or an optionally provided address.
 * Requires manage_options capability and uses a nonce from the settings page.
 */
function ter_send_test_email() {
	// Capability check.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( ter_get_config( 'error_no_permission', 'No permission.' ) );
		return;
	}

	// Nonce check.
	check_ajax_referer( 'ter_test_email', 'nonce' );

	$test_email = isset( $_POST['test_email'] ) ? sanitize_email( wp_unslash( $_POST['test_email'] ) ) : '';

	$config = get_option( 'ter_config', array() );
	$to     = '';
	if ( ! empty( $test_email ) && is_email( $test_email ) ) {
		$to = $test_email;
	} elseif ( ! empty( $config['recipient_email'] ) && is_email( $config['recipient_email'] ) ) {
		$to = $config['recipient_email'];
	}

	if ( empty( $to ) || ! is_email( $to ) ) {
		wp_send_json_error( ter_get_config( 'error_no_email_config', 'No valid recipient email configured.' ) );
		return;
	}

	$subject = ter_get_config( 'email_test_subject', 'Test email — Travel Expense Plugin' );
	$message = '<p>' . ter_get_config( 'email_test_message', 'This is a test email from the travel expense plugin.' ) . '</p>';
	$headers = array( 'Content-Type: text/html; charset=UTF-8' );

	$sent = wp_mail( $to, $subject, $message, $headers );
	if ( $sent ) {
		wp_send_json_success( ter_get_config( 'email_test_sent', 'Test email sent to: ' ) . $to );
	} else {
		wp_send_json_error( ter_get_config( 'email_test_error', 'Error sending test email to: ' ) . $to );
	}
}

add_action( 'wp_ajax_ter_send_preview_email', 'ter_send_preview_email' );
/**
 * AJAX handler: send a preview of the current templates to an admin-provided email.
 *
 * @return void
 */
function ter_send_preview_email() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'No permission.' );
		return;
	}
	check_ajax_referer( 'ter_preview_templates', 'nonce' );

	$preview_email = isset( $_POST['preview_email'] ) ? sanitize_email( wp_unslash( $_POST['preview_email'] ) ) : '';
	$opts          = get_option( 'ter_config', array() );
	if ( empty( $preview_email ) && ! empty( $opts['recipient_email'] ) ) {
		$preview_email = $opts['recipient_email'];
	}
	if ( empty( $preview_email ) || ! is_email( $preview_email ) ) {
		wp_send_json_error( 'No valid preview recipient email provided.' );
		return;
	}

	// Build sample data for placeholders.
	$sample = array(
		'firstname'    => 'Max',
		'lastname'     => 'Mustermann',
		'event'        => 'Template Preview',
		'total'        => number_format( 123.45, 2, ',', '.' ),
		'pdf_filename' => 'TravelExpensePreview.pdf',
	);

	$internal_subject_tpl = ter_get_config( 'internal_subject', 'Travel expense report — {firstname} {lastname}' );
	$internal_message_tpl = ter_get_config( 'internal_message', '<p>Internal preview: {firstname} {lastname} — {event} — {total} €</p>' );
	$customer_subject_tpl = ter_get_config( 'customer_subject', 'Copy of your travel expense report — {event}' );
	$customer_message_tpl = ter_get_config( 'customer_message', '<p>Hi {firstname},<br>This is a preview of your expense report for {event} (Total: {total} €).</p>' );

	$internal_subject = ter_apply_template( $internal_subject_tpl, $sample );
	$internal_message = ter_apply_template( $internal_message_tpl, $sample );
	$customer_subject = ter_apply_template( $customer_subject_tpl, $sample );
	$customer_message = ter_apply_template( $customer_message_tpl, $sample );

	$headers = array( 'Content-Type: text/html; charset=UTF-8' );

	$sent_internal = wp_mail( $preview_email, $internal_subject, $internal_message, $headers );
	$sent_customer = wp_mail( $preview_email, $customer_subject, $customer_message, $headers );

	$msg   = array();
	$msg[] = 'Internal template: ' . ( $sent_internal ? 'sent' : 'ERROR' );
	$msg[] = 'Customer template: ' . ( $sent_customer ? 'sent' : 'ERROR' );

	if ( $sent_internal || $sent_customer ) {
		wp_send_json_success( implode( ' | ', $msg ) );
	} else {
		wp_send_json_error( implode( ' | ', $msg ) );
	}
}

/**
 * Sanitize legacy plugin settings.
 *
 * @param array $input Raw settings input.
 * @return array Sanitized settings.
 */
function ter_sanitize_settings( $input ) {
	$out = array();
	if ( isset( $input['recipient_email'] ) ) {
		$out['recipient_email'] = sanitize_email( $input['recipient_email'] );
	}
	$out['send_customer_copy']        = ! empty( $input['send_customer_copy'] ) ? 1 : 0;
	$out['delete_uploads_after_send'] = ! empty( $input['delete_uploads_after_send'] ) ? 1 : 0;
	if ( isset( $input['temp_folder'] ) ) {
		$out['temp_folder'] = sanitize_file_name( $input['temp_folder'] );
	}
	if ( isset( $input['subject_prefix'] ) ) {
		$out['subject_prefix'] = sanitize_text_field( $input['subject_prefix'] );
	}
	// Sanitize new template/settings.
	if ( isset( $input['internal_subject'] ) ) {
		$out['internal_subject'] = sanitize_text_field( $input['internal_subject'] );
	}
	if ( isset( $input['internal_message'] ) ) {
		// Allow basic HTML in the internal message (admin-controlled). Use wp_kses_post to allow safe HTML tags.
		$out['internal_message'] = wp_kses_post( $input['internal_message'] );
	}
	if ( isset( $input['customer_subject'] ) ) {
		$out['customer_subject'] = sanitize_text_field( $input['customer_subject'] );
	}
	if ( isset( $input['customer_message'] ) ) {
		// Allow basic HTML in the customer message.
		$out['customer_message'] = wp_kses_post( $input['customer_message'] );
	}
	if ( isset( $input['success_message'] ) ) {
		$out['success_message'] = sanitize_text_field( $input['success_message'] );
	}
	if ( isset( $input['failure_message'] ) ) {
		$out['failure_message'] = sanitize_text_field( $input['failure_message'] );
	}
	if ( isset( $input['log_prefix'] ) ) {
		$out['log_prefix'] = sanitize_text_field( $input['log_prefix'] );
	}
	return $out;
}

/**
 * Apply template variables to a string with {placeholders}.
 *
 * @param string $template Template string with {placeholders}.
 * @param array  $vars Associative array of variables to replace in the template.
 * @return string Processed string with variables replaced.
 */
function ter_apply_template( $template, $vars = array() ) {
	if ( empty( $template ) ) {
		return '';
	}
	$replacements = array();
	foreach ( $vars as $k => $v ) {
		// Allow HTML in values if explicitly provided as safe; otherwise escape.
		$replacements[ '{' . $k . '}' ] = is_string( $v ) ? esc_html( $v ) : esc_html( (string) $v );
	}
	return strtr( $template, $replacements );
}

// Settings page rendering moved to includes/settings-admin.php (ter_render_settings_page_advanced).

// --- New helpers: temp upload dir, resolver and file serving ---
/**
 * Return the plugin temp upload directory inside the WordPress system temp dir.
 * Created directory looks like: {get_temp_dir()}/travel-expense-report/
 *
 * @param bool $create Create directory if missing.
 * @return string Trailing-slash path.
 */
function ter_get_temp_upload_dir( $create = true ) {
	$config = get_option( 'ter_config', array() );
	$folder = ! empty( $config['temp_folder'] ) ? sanitize_file_name( $config['temp_folder'] ) : 'travel-expense-report';
	$base   = trailingslashit( get_temp_dir() ) . $folder . '/';
	if ( $create && ! file_exists( $base ) ) {
		wp_mkdir_p( $base );
	}
	return trailingslashit( $base );
}

/**
 * Resolve a stored uploaded filename (basename) to an absolute path if valid.
 * Returns false if the file is outside the plugin temp dir or does not exist.
 *
 * @param string $basename Stored file basename.
 * @return string|false
 */
function ter_resolve_uploaded_file_path( $basename ) {
	$basename = basename( $basename );
	if ( empty( $basename ) ) {
		return false;
	}
	$dir       = ter_get_temp_upload_dir( false );
	$path      = $dir . $basename;
	$real_dir  = realpath( $dir );
	$real_path = realpath( $path );
	if ( false === $real_path || false === $real_dir ) {
		return false;
	}
	// Ensure file is inside our temp dir.
	if ( strpos( $real_path, $real_dir ) !== 0 ) {
		return false;
	}
	return $real_path;
}

// AJAX endpoint to serve temporary uploaded files (preview/download).
add_action( 'wp_ajax_ter_serve_temp_file', 'ter_serve_temp_file' );
add_action( 'wp_ajax_nopriv_ter_serve_temp_file', 'ter_serve_temp_file' );
/**
 * AJAX handler: stream a previously uploaded temp file after nonce verification.
 *
 * @return void
 */
function ter_serve_temp_file() {
	// Expect GET param 'file' and 'nonce'.
	// The nonce is verified right below, before the file is used.
	$file  = isset( $_GET['file'] ) ? sanitize_text_field( wp_unslash( $_GET['file'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$nonce = isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( empty( $file ) || empty( $nonce ) || ! wp_verify_nonce( $nonce, 'ter_get_file' ) ) {
		status_header( 403 );
		wp_die( 'Access denied.' );
	}

	$path = ter_resolve_uploaded_file_path( $file );
	if ( ! $path || ! file_exists( $path ) ) {
		status_header( 404 );
		wp_die( 'File not found.' );
	}

	// Send proper headers and stream the file.
	$mime = mime_content_type( $path );
	if ( ! $mime ) {
		$mime = 'application/octet-stream';
	}
	header( 'Content-Type: ' . $mime );
	header( 'Content-Length: ' . filesize( $path ) );
	// Inline for images and pdfs.
	header( 'Content-Disposition: inline; filename="' . basename( $path ) . '"' );
	readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- Streaming a validated temp upload.
	exit;
}

/**
 * Handle file upload for receipts
 */
function ter_upload_receipt() {
	// Nonce security check.
	check_ajax_referer( 'ter_nonce', 'nonce' );

	// Validate.
	if ( ! isset( $_FILES['receipt'] ) ) {
		wp_send_json_error( 'No file uploaded.' );
		return;
	}

	// Allowed types (extension => MIME), verified by WordPress against the file content.
	$allowed_mimes = array(
		'jpg|jpeg' => 'image/jpeg',
		'png'      => 'image/png',
		'pdf'      => 'application/pdf',
	);

	// Store the file in the plugin temp dir instead of the public uploads dir.
	$temp_dir      = untrailingslashit( ter_get_temp_upload_dir() );
	$redirect_temp = static function ( $dirs ) use ( $temp_dir ) {
		$dirs['path']    = $temp_dir;
		$dirs['basedir'] = $temp_dir;
		$dirs['subdir']  = '';
		return $dirs;
	};

	require_once ABSPATH . 'wp-admin/includes/file.php';

	add_filter( 'upload_dir', $redirect_temp );
	$uploaded = wp_handle_upload(
		$_FILES['receipt'], // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Validated by wp_handle_upload().
		array(
			'test_form' => false,
			'mimes'     => $allowed_mimes,
		)
	);
	remove_filter( 'upload_dir', $redirect_temp );

	if ( isset( $uploaded['error'] ) ) {
		wp_send_json_error( 'Only JPG, PNG and PDF files are allowed.' );
		return;
	}

	$dest_path = $uploaded['file'];

	// URL to access the temp file via our AJAX endpoint (secure with a nonce).
	$file_url = add_query_arg(
		array(
			'action' => 'ter_serve_temp_file',
			'file'   => basename( $dest_path ),
			'nonce'  => wp_create_nonce( 'ter_get_file' ),
		),
		admin_url( 'admin-ajax.php' )
	);

	wp_send_json_success(
		array(
			'url'  => $file_url,
			'file' => basename( $dest_path ),
		)
	);
}

/**
 * Send email with travel expense report
 */
function ter_send_email() {
	// Nonce security check.
	check_ajax_referer( 'ter_nonce', 'nonce' );

	// Debug: log incoming POST keys and customer_email raw value to help debugging missing fields.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		ter_log( 'ter_send_email POST keys: ' . wp_json_encode( array_keys( $_POST ) ) );
		$raw_cust = isset( $_POST['customer_email'] ) ? wp_unslash( $_POST['customer_email'] ) : null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Debug logging only.
		ter_log( 'ter_send_email raw customer_email: ' . wp_json_encode( $raw_cust ) );
	}

	// Validate personal data.
	$firstname   = isset( $_POST['firstname'] ) ? sanitize_text_field( wp_unslash( $_POST['firstname'] ) ) : '';
	$lastname    = isset( $_POST['lastname'] ) ? sanitize_text_field( wp_unslash( $_POST['lastname'] ) ) : '';
	$iban        = isset( $_POST['iban'] ) ? sanitize_text_field( wp_unslash( $_POST['iban'] ) ) : '';
	$bic         = isset( $_POST['bic'] ) ? sanitize_text_field( wp_unslash( $_POST['bic'] ) ) : '';
	$event       = isset( $_POST['event'] ) ? sanitize_text_field( wp_unslash( $_POST['event'] ) ) : '';
	$organizer   = isset( $_POST['organizer'] ) ? sanitize_text_field( wp_unslash( $_POST['organizer'] ) ) : '';
	$cost_center = isset( $_POST['cost_center'] ) ? sanitize_text_field( wp_unslash( $_POST['cost_center'] ) ) : '';

	// Validation - only required fields (organizer is now optional).
	if ( empty( $firstname ) || empty( $lastname ) || empty( $iban ) || empty( $event ) ) {
		wp_send_json_error( ter_get_config( 'error_required_fields', 'Please fill all required personal details.' ) );
		return;
	}

	// Validate IBAN (Format: 2 letters, 2 digits, then alphanumeric).
	if ( ! preg_match( '/^[A-Z]{2}[0-9]{2}[A-Z0-9]+$/', str_replace( ' ', '', $iban ) ) ) {
		wp_send_json_error( ter_get_config( 'error_invalid_iban', 'Invalid IBAN.' ) );
		return;
	}

	// Validate BIC (only if provided - Format: 6 letters, 2 alphanumeric, optional 3 alphanumeric).
	if ( ! empty( $bic ) && ! preg_match( '/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/', $bic ) ) {
		wp_send_json_error( ter_get_config( 'error_invalid_bic', 'Invalid BIC.' ) );
		return;
	}

	// Read data from POST - all optional now.
	$items             = isset( $_POST['items'] ) ? json_decode( wp_unslash( $_POST['items'] ), true ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Decoded JSON, every field is validated and escaped on use.
	$km_data           = isset( $_POST['km_data'] ) ? json_decode( wp_unslash( $_POST['km_data'] ), true ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Decoded JSON, every field is validated and escaped on use.
	$ticket_data       = isset( $_POST['ticket_data'] ) ? json_decode( wp_unslash( $_POST['ticket_data'] ), true ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Decoded JSON, every field is validated and escaped on use.
	$km_total_cost     = isset( $_POST['km_total_cost'] ) ? floatval( wp_unslash( $_POST['km_total_cost'] ) ) : 0;
	$ticket_total_cost = isset( $_POST['ticket_total_cost'] ) ? floatval( wp_unslash( $_POST['ticket_total_cost'] ) ) : 0;
	$total             = isset( $_POST['total'] ) ? floatval( wp_unslash( $_POST['total'] ) ) : 0;

	// Validation: at least one of items, km_data, or ticket_data must be present.
	if ( empty( $items ) && empty( $km_data ) && empty( $ticket_data ) ) {
		wp_send_json_error( ter_get_config( 'error_no_expenses', 'Please add at least one expense item.' ) );
		return;
	}

	// Load plugin options (used for recipient, behavior etc.).
	$opts = get_option( 'ter_config', array() );

	// Email subject.
	$subject = 'Travel expense report - ' . $firstname . ' ' . $lastname . ' - ' . $event . ' - ' . gmdate( 'd.m.Y' );

	// HTML content for full report.
	$html_content = ter_generate_full_report( $firstname, $lastname, $iban, $bic, $event, $organizer, $cost_center, $items, $km_data, $ticket_data, $km_total_cost, $ticket_total_cost, $total );

	// Email message (summary).
	$message = ter_generate_email_summary( $firstname, $lastname, $iban, $bic, $event, $organizer, $cost_center, $items, $km_total_cost, $ticket_total_cost, $total );

	// Collect attachments.
	$attachments = array();

	// PDF generation or HTML fallback.
	$upload_dir    = ter_get_temp_upload_dir();
	$safe_filename = sanitize_file_name( $firstname . '_' . $lastname . '_' . $event . '_' . gmdate( 'Y-m-d' ) );

	// Keep track of files we created so we can delete them later.
	$created_files = array();

	if ( TER_TCPDF_AVAILABLE ) {
		// TCPDF available - generate PDF.
		$pdf_file_path = trailingslashit( $upload_dir ) . 'TravelExpenseReport_' . $safe_filename . '.pdf';
		$pdf_generated = ter_generate_pdf( $html_content, $pdf_file_path, $items, $ticket_data );

		if ( $pdf_generated ) {
			$attachments[]   = $pdf_file_path;
			$created_files[] = $pdf_file_path;
		}
	} else {
		// Fallback to HTML: create and attach and remember for cleanup.
		$html_file_path = $upload_dir . 'TravelExpenseReport_' . $safe_filename . '.html';
		file_put_contents( $html_file_path, $html_content ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Temporary report attachment.
		$attachments[]   = $html_file_path;
		$created_files[] = $html_file_path;
	}

	// Send email.
	$to      = ! empty( $opts['recipient_email'] ) ? $opts['recipient_email'] : '';
	$headers = array( 'Content-Type: text/html; charset=UTF-8' );

	// Subject prefix from settings.
	$prefix = ! empty( $opts['subject_prefix'] ) ? trim( $opts['subject_prefix'] ) . ' ' : '';
	if ( ! empty( $prefix ) ) {
		$subject = $prefix . $subject;
	}

	$send_customer_copy        = ! empty( $opts['send_customer_copy'] );
	$delete_uploads_after_send = ! empty( $opts['delete_uploads_after_send'] );

	// Customer copy: optional (only validated if we will send a copy).
	$customer_email = isset( $_POST['customer_email'] ) ? sanitize_email( wp_unslash( $_POST['customer_email'] ) ) : '';
	if ( $send_customer_copy && ! empty( $customer_email ) && ! is_email( $customer_email ) ) {
		wp_send_json_error( ter_get_config( 'error_invalid_email', 'Invalid email address.' ) );
		return;
	}

	// If customer copy is requested, prefer to send it as BCC on the internal mail.
	// This increases deliverability (single SMTP transaction) and avoids duplicate sends.
	$bcc_added = false;
	if ( $send_customer_copy && ! empty( $customer_email ) ) {
		$headers[] = 'Bcc: ' . $customer_email;
		$bcc_added = true;
	}

	// Prepare internal message with template.
	$internal_message = ter_get_config( 'internal_message', '' );
	$internal_subject = ter_get_config( 'internal_subject', $subject );
	$log_prefix       = ter_get_config( 'log_prefix', 'ter_send_email:' );

	// Apply templates with actual values.
	$internal_message = ter_apply_template(
		$internal_message,
		array(
			'firstname'    => $firstname,
			'lastname'     => $lastname,
			'event'        => $event,
			'total'        => number_format( $total, 2, ',', '.' ),
			'pdf_filename' => basename( reset( $attachments ) ),
		)
	);
	$internal_subject = ter_apply_template(
		$internal_subject,
		array(
			'firstname'    => $firstname,
			'lastname'     => $lastname,
			'event'        => $event,
			'total'        => number_format( $total, 2, ',', '.' ),
			'pdf_filename' => basename( reset( $attachments ) ),
		)
	);

	// Log internal email send attempt.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		ter_log( $log_prefix . ' sending internal email to ' . $to );
	}

	// Send internal mail.
	$customer_sent = false;
	$sent          = wp_mail( $to, $internal_subject, $internal_message, $headers, $attachments );
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		ter_log( $log_prefix . ' internal mail to ' . $to . ' result: ' . ( $sent ? 'ok' : 'fail' ) );
	}

	// If configured, send customer copy (attempt regardless of internal send result).
	$customer_attempted = false;
	if ( $send_customer_copy && ! empty( $customer_email ) ) {
		$customer_attempted = true;
		// If we added BCC to the internal mail, we consider the customer copy attempted/sent.
		if ( $bcc_added ) {
			// Customer copy was sent as BCC with the internal mail; reflect that in flags.
			$customer_sent = $sent;
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				ter_log( $log_prefix . ' customer copy via BCC ' . ( $customer_sent ? 'succeeded' : 'failed' ) . ' for ' . $customer_email );
			}
			// Fallback: if internal send failed, try sending a separate copy without Bcc header.
			if ( ! $customer_sent ) {
				// Remove any Bcc headers.
				$headers_no_bcc = array_values(
					array_filter(
						$headers,
						function ( $h ) {
							return stripos( $h, 'bcc:' ) === false;
						}
					)
				);
				$fallback_sent  = wp_mail( $customer_email, $subject, $message, $headers_no_bcc, $attachments );
				if ( $fallback_sent ) {
					$customer_sent = true;
					if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
						ter_log( $log_prefix . ' fallback customer copy (separate send) succeeded for ' . $customer_email );
					}
				} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					ter_log( $log_prefix . ' fallback customer copy (separate send) failed for ' . $customer_email );
				}
			}
		} else {
			// Separate send (no BCC).
			$cust_subject         = ter_get_config( 'customer_subject', $subject );
			$cust_message         = ter_get_config( 'customer_message', '' );
			$customer_attachments = array();

			// Customer message: apply template.
			$cust_message = ter_apply_template(
				$cust_message,
				array(
					'firstname'    => $firstname,
					'lastname'     => $lastname,
					'event'        => $event,
					'total'        => number_format( $total, 2, ',', '.' ),
					'pdf_filename' => basename( reset( $attachments ) ),
				)
			);
			$cust_subject = ter_apply_template(
				$cust_subject,
				array(
					'firstname'    => $firstname,
					'lastname'     => $lastname,
					'event'        => $event,
					'total'        => number_format( $total, 2, ',', '.' ),
					'pdf_filename' => basename( reset( $attachments ) ),
				)
			);

			// Send customer email.
			$customer_sent = wp_mail( $customer_email, $cust_subject, $cust_message, $headers, $customer_attachments );
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				if ( ! $customer_sent ) {
					ter_log( $log_prefix . ' separate customer copy could not be sent to ' . $customer_email );
				} else {
					ter_log( $log_prefix . ' separate customer copy sent to ' . $customer_email );
				}
			}
		}
	}

	// Clean up temporary files (only those we created).
	$created_deleted = array();
	foreach ( $created_files as $temp_file ) {
		$deleted = false;
		if ( file_exists( $temp_file ) ) {
			wp_delete_file( $temp_file );
			$deleted = ! file_exists( $temp_file );
		}
		$created_deleted[] = array(
			'file'    => $temp_file,
			'deleted' => (bool) $deleted,
		);
	}
	// Log created-file deletions.
	ter_append_deletion_log(
		array(
			'context' => 'created_files',
			'entries' => $created_deleted,
		)
	);

	// Now: only delete uploaded files referenced by the current form (if enabled in settings).
	if ( ! empty( $delete_uploads_after_send ) ) {
		$maybe_dir = ter_get_temp_upload_dir( false );
		if ( $maybe_dir && is_dir( $maybe_dir ) ) {
			$real_dir = realpath( $maybe_dir );
			$folder   = ! empty( $opts['temp_folder'] ) ? sanitize_file_name( $opts['temp_folder'] ) : 'travel-expense-report';
			if ( $real_dir && basename( $real_dir ) === $folder ) {
				$to_delete = array();

				// Collect basenames from items.
				if ( ! empty( $items ) && is_array( $items ) ) {
					foreach ( $items as $it ) {
						if ( ! empty( $it['receipt'] ) ) {
							$b = ter_receipt_basename_from_value( $it['receipt'] );
							if ( $b ) {
								$to_delete[] = $b;
							}
						}
					}
				}

				// Collect basenames from ticket_data.
				if ( ! empty( $ticket_data ) && is_array( $ticket_data ) ) {
					foreach ( $ticket_data as $tk ) {
						if ( ! empty( $tk['receipt'] ) ) {
							$b = ter_receipt_basename_from_value( $tk['receipt'] );
							if ( $b ) {
								$to_delete[] = $b;
							}
						}
					}
				}

				$to_delete = array_unique( array_filter( $to_delete ) );

				$uploaded_deleted = array();
				foreach ( $to_delete as $basename ) {
					$path    = ter_resolve_uploaded_file_path( $basename );
					$deleted = false;
					if ( $path && file_exists( $path ) ) {
						wp_delete_file( $path );
						$deleted = ! file_exists( $path );
					}
					$uploaded_deleted[] = array(
						'basename' => $basename,
						'path'     => $path,
						'deleted'  => (bool) $deleted,
					);
				}
				// Log uploaded receipt deletions.
				ter_append_deletion_log(
					array(
						'context' => 'uploaded_receipts',
						'entries' => $uploaded_deleted,
					)
				);

				// Try to remove the directory if it's now empty.
				$remaining = glob( $real_dir . DIRECTORY_SEPARATOR . '*' );
				if ( false === $remaining || 0 === count( $remaining ) ) {
					rmdir( $real_dir ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- Removing our own empty temp directory.
				}
			} else {
				ter_log( $log_prefix . ' security check for temp dir failed, not deleting: ' . wp_json_encode( $maybe_dir ) );
			}
		}
	}

	$frontend_success = ter_get_config( 'success_message', 'Email sent successfully!' );
	$frontend_failure = ter_get_config( 'failure_message', 'Error sending email.' );

	if ( $sent ) {
		$msg = $frontend_success;
		if ( $send_customer_copy ) {
			if ( ! empty( $customer_email ) ) {
				if ( $customer_attempted ) {
					$msg .= $customer_sent ? ' Customer copy sent to: ' . $customer_email : ' Customer copy could not be sent to: ' . $customer_email;
				} else {
					$msg .= ' (Customer copy: not attempted)';
				}
			} else {
				$msg .= ' (Customer copy not sent: no customer email provided)';
			}
		}
		wp_send_json_success( $msg );
	} else {
		// If internal send failed, also log if we tried customer copy.
		if ( ! empty( $customer_email ) && ! empty( $send_customer_copy ) ) {
			ter_log( $log_prefix . ' internal send failed; customer_attempted=' . ( ! empty( $customer_attempted ) ? '1' : '0' ) . ' customer_sent=' . ( $customer_sent ? '1' : '0' ) );
		}
		wp_send_json_error( $frontend_failure );
	}
}

/**
 * Extract a safe basename from a receipt value which may be a URL (our serve endpoint) or a direct filename.
 *
 * @param string $value Receipt URL or filename.
 * @return string
 */
function ter_receipt_basename_from_value( $value ) {
	$value = trim( (string) $value );
	if ( empty( $value ) ) {
		return '';
	}
	$parts = wp_parse_url( $value );
	if ( false !== $parts && isset( $parts['query'] ) ) {
		parse_str( $parts['query'], $qs );
		if ( ! empty( $qs['file'] ) ) {
			return basename( $qs['file'] );
		}
	}
	return basename( $value );
}

/**
 * Generate PDF with TCPDF (tecnickcom/tcpdf).
 *
 * @param string $html_content HTML report content.
 * @param string $output_path Destination file path.
 * @param array  $items Expense items.
 * @param array  $ticket_data Train tickets.
 * @return bool True when the PDF was created.
 */
function ter_generate_pdf( $html_content, $output_path, $items, $ticket_data ) {
	try {
		// TCPDF object creation.
		$pdf = new setasign\Fpdi\Tcpdf\Fpdi( PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false );

		// Document information.
		$pdf->SetCreator( 'DPSG Travel Expense Report' );
		$pdf->SetAuthor( 'DPSG' );
		$pdf->SetTitle( 'Travel Expense Report' );

		// Disable header and footer.
		$pdf->setPrintHeader( false );
		$pdf->setPrintFooter( false );

		// Set margins.
		$pdf->SetMargins( 15, 15, 15 );
		$pdf->SetAutoPageBreak( true, 15 );

		// Set font.
		$pdf->SetFont( 'helvetica', '', 10 );

		// Add page.
		$pdf->AddPage();

		// Write HTML content.
		$pdf->writeHTML( $html_content, true, false, true, false, '' );

		// Add item receipts.
		foreach ( $items as $item ) {
			if ( ! empty( $item['receipt'] ) ) {
				$basename  = ter_receipt_basename_from_value( $item['receipt'] );
				$file_path = ter_resolve_uploaded_file_path( $basename );
				if ( $file_path && file_exists( $file_path ) ) {
					$file_ext = strtolower( pathinfo( $file_path, PATHINFO_EXTENSION ) );

					if ( in_array( $file_ext, array( 'jpg', 'jpeg', 'png' ), true ) ) {
						// Insert image receipt.
						$pdf->AddPage();
						$pdf->SetFont( 'helvetica', 'B', 12 );
						$pdf->Cell( 0, 10, 'Receipt: ' . $item['name'], 0, 1, 'L' );
						$pdf->Ln( 5 );
						$pdf->Image( $file_path, 15, 35, 180, 0, $file_ext, '', '', false, 300, '', false, false, 0 );
					} elseif ( 'pdf' === $file_ext ) {
						// Insert PDF receipt.
						$page_count = $pdf->setSourceFile( $file_path );
						for ( $i = 1; $i <= $page_count; $i++ ) {
							$pdf->AddPage();
							if ( 1 === $i ) {
								$pdf->SetFont( 'helvetica', 'B', 12 );
								$pdf->Cell( 0, 10, 'Receipt: ' . $item['name'], 0, 1, 'L' );
								$pdf->Ln( 5 );
							}
							$tpl_idx = $pdf->importPage( $i );
							$pdf->useTemplate( $tpl_idx, 15, ( 1 === $i ? 35 : 15 ), 180 );
						}
					}
				}
			}
		}

		// Add ticket receipts.
		foreach ( $ticket_data as $ticket ) {
			if ( ! empty( $ticket['receipt'] ) ) {
				$basename  = ter_receipt_basename_from_value( $ticket['receipt'] );
				$file_path = ter_resolve_uploaded_file_path( $basename );
				if ( $file_path && file_exists( $file_path ) ) {
					$file_ext = strtolower( pathinfo( $file_path, PATHINFO_EXTENSION ) );
					$label    = 'Ticket: ' . $ticket['start'] . ' → ' . $ticket['target'];

					if ( in_array( $file_ext, array( 'jpg', 'jpeg', 'png' ), true ) ) {
						// Insert image receipt.
						$pdf->AddPage();
						$pdf->SetFont( 'helvetica', 'B', 12 );
						$pdf->Cell( 0, 10, $label, 0, 1, 'L' );
						$pdf->Ln( 5 );
						$pdf->Image( $file_path, 15, 35, 180, 0, $file_ext, '', '', false, 300, '', false, false, 0 );
					} elseif ( 'pdf' === $file_ext ) {
						// Insert PDF receipt.
						$page_count = $pdf->setSourceFile( $file_path );
						for ( $i = 1; $i <= $page_count; $i++ ) {
							$pdf->AddPage();
							if ( 1 === $i ) {
								$pdf->SetFont( 'helvetica', 'B', 12 );
								$pdf->Cell( 0, 10, $label, 0, 1, 'L' );
								$pdf->Ln( 5 );
							}
							$tpl_idx = $pdf->importPage( $i );
							$pdf->useTemplate( $tpl_idx, 15, ( 1 === $i ? 35 : 15 ), 180 );
						}
					}
				}
			}
		}

		// Save PDF.
		$pdf->Output( $output_path, 'F' );
		return true;

	} catch ( Exception $e ) {
		ter_log( 'TCPDF Error: ' . $e->getMessage() );
		return false;
	}
}

/**
 * Generate full HTML report with all details and receipts.
 *
 * @param string $firstname First name.
 * @param string $lastname Last name.
 * @param string $iban IBAN.
 * @param string $bic BIC.
 * @param string $event Event name.
 * @param string $organizer Organizer.
 * @param string $cost_center Cost center.
 * @param array  $items Expense items.
 * @param array  $km_data Mileage trips.
 * @param array  $ticket_data Train tickets.
 * @param float  $km_total_cost Total mileage cost.
 * @param float  $ticket_total_cost Total ticket cost.
 * @param float  $total Grand total.
 * @return string HTML report.
 */
function ter_generate_full_report( $firstname, $lastname, $iban, $bic, $event, $organizer, $cost_center, $items, $km_data, $ticket_data, $km_total_cost, $ticket_total_cost, $total ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter -- All parameters are consumed by the included template.
	ob_start();
	include plugin_dir_path( __FILE__ ) . 'templates/travel-expense-report-email.php';
	return ob_get_clean();
}

/**
 * Generate email summary.
 *
 * @param string $firstname First name.
 * @param string $lastname Last name.
 * @param string $iban IBAN.
 * @param string $bic BIC.
 * @param string $event Event name.
 * @param string $organizer Organizer.
 * @param string $cost_center Cost center.
 * @param array  $items Expense items.
 * @param float  $km_total_cost Total mileage cost.
 * @param float  $ticket_total_cost Total ticket cost.
 * @param float  $total Grand total.
 * @return string HTML summary.
 */
function ter_generate_email_summary( $firstname, $lastname, $iban, $bic, $event, $organizer, $cost_center, $items, $km_total_cost, $ticket_total_cost, $total ) {
	$safe_filename = sanitize_file_name( $firstname . '_' . $lastname . '_' . $event . '_' . gmdate( 'Y-m-d' ) );
	$items_total   = array_sum( array_column( $items, 'amount' ) );

	ob_start();
	?>
	<html>
	<head>
		<meta charset="UTF-8">
		<style>
			body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; padding: 20px; }
			.info-box { background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; margin: 20px 0; }
			.summary { background: #f5f5f5; padding: 15px; margin: 15px 0; border-radius: 5px; }
			table { width: 100%; border-collapse: collapse; margin: 15px 0; }
			table td { padding: 8px; border-bottom: 1px solid #ddd; }
			table td:first-child { font-weight: bold; width: 200px; }
		</style>
	</head>
	<body>
	<h1>📋 Travel expense report submitted</h1>

	<div class="info-box">
		<strong>ℹ️ The full report with all receipts is attached<?php echo TER_TCPDF_AVAILABLE ? ' as a PDF file' : ' as an HTML file'; ?>.</strong>
		<?php if ( ! TER_TCPDF_AVAILABLE ) : ?>
			<br><small>Open the HTML file in a browser and print/save as PDF (File → Print → Save as PDF).</small>
		<?php endif; ?>
	</div>

	<h2>Overview</h2>
	<table>
		<tr>
			<td>Name:</td>
			<td><?php echo esc_html( $firstname . ' ' . $lastname ); ?></td>
		</tr>
		<tr>
			<td>Event:</td>
			<td><?php echo esc_html( $event ); ?></td>
		</tr>
		<tr>
			<td>Organizer:</td>
			<td><?php echo esc_html( $organizer ); ?></td>
		</tr>
		<?php if ( ! empty( $cost_center ) ) : ?>
			<tr>
				<td>Cost center:</td>
				<td><?php echo esc_html( $cost_center ); ?></td>
			</tr>
		<?php endif; ?>
		<tr>
			<td>IBAN:</td>
			<td><?php echo esc_html( $iban ); ?></td>
		</tr>
		<?php if ( ! empty( $bic ) ) : ?>
			<tr>
				<td>BIC:</td>
				<td><?php echo esc_html( $bic ); ?></td>
			</tr>
		<?php endif; ?>
		<tr>
			<td>Date:</td>
			<td><?php echo esc_html( gmdate( 'd.m.Y' ) ); ?></td>
		</tr>
	</table>

	<div class="summary">
		<h3>💰 Cost summary</h3>
		<table>
			<tr>
				<td>Prepaid services / Purchases:</td>
				<td><strong><?php echo number_format( $items_total, 2, ',', '.' ); ?> €</strong></td>
			</tr>
			<tr>
				<td>Mileage / Travel costs:</td>
				<td><strong><?php echo number_format( $km_total_cost, 2, ',', '.' ); ?> €</strong></td>
			</tr>
			<tr>
				<td>Train tickets:</td>
				<td><strong><?php echo number_format( $ticket_total_cost, 2, ',', '.' ); ?> €</strong></td>
			</tr>
			<tr style="border-top: 2px solid #333;">
				<td><strong>Total:</strong></td>
				<td><strong style="font-size: 18px;"><?php echo number_format( $total, 2, ',', '.' ); ?> €</strong></td>
			</tr>
		</table>
	</div>

	<p><strong>📎 Attachments:</strong></p>
	<ul>
		<li>TravelExpenseReport_<?php echo esc_html( $safe_filename ); ?>.<?php echo TER_TCPDF_AVAILABLE ? 'pdf' : 'html'; ?> (Full report<?php echo TER_TCPDF_AVAILABLE ? ' with receipts' : ''; ?>)</li>
		<?php if ( ! TER_TCPDF_AVAILABLE ) : ?>
			<li>Separate PDF receipts (if any)</li>
		<?php endif; ?>
	</ul>

	<hr style="margin: 30px 0;">
	<p style="color: #666; font-size: 12px;">
		This email was automatically generated by the travel expense report form.<br>
		If you have questions, please contact the office.
	</p>
	</body>
	</html>
	<?php
	return ob_get_clean();
}

/**
 * Send HTML Email using the full email template
 * Generates a complete HTML document with all expense details
 *
 * @param string $recipient Email address.
 * @param string $subject Email subject.
 * @param string $firstname User first name.
 * @param string $lastname User last name.
 * @param string $iban User IBAN.
 * @param string $bic User BIC.
 * @param string $event Event name.
 * @param string $organizer Event organizer.
 * @param string $cost_center Cost center.
 * @param array  $items Purchase items.
 * @param array  $km_data Trip data.
 * @param array  $ticket_data Ticket data.
 * @param float  $km_total_cost Total km cost.
 * @param float  $ticket_total_cost Total ticket cost.
 * @param float  $total Total cost.
 * @param array  $attachments File attachments (PDF).
 * @return bool True if mail sent successfully
 */
function ter_send_html_email( $recipient, $subject, $firstname, $lastname, $iban, $bic, $event, $organizer, $cost_center, $items, $km_data, $ticket_data, $km_total_cost, $ticket_total_cost, $total, $attachments = array() ) {
	// Set proper headers for HTML email with UTF-8 charset.
	$headers   = array();
	$headers[] = 'Content-Type: text/html; charset=UTF-8';
	$headers[] = 'From: ' . get_option( 'blogname' ) . ' <' . get_option( 'admin_email' ) . '>';

	// Generate full HTML email content using the email template.
	ob_start();
	include plugin_dir_path( __FILE__ ) . 'templates/travel-expense-report-email.php';
	$html_content = ob_get_clean();

	// Send email with HTML content.
	return wp_mail( $recipient, $subject, $html_content, $headers, $attachments );
}

/**
 * Render the form shortcode
 */
function ter_render_form() {
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// Enqueue CSS.
	wp_enqueue_style(
		'ter-styles',
		plugin_dir_url( __FILE__ ) . 'assets/css/travel-expense-report' . $suffix . '.css',
		array(),
		'1.0.0'
	);

	// Enqueue JavaScript.
	wp_enqueue_script(
		'ter-script',
		plugin_dir_url( __FILE__ ) . 'assets/js/travel-expense-report-localized' . $suffix . '.js',
		array(),
		'1.0.0',
		true
	);

	// Localize script with AJAX URL, nonce, configurable labels and rates.
	$opts_for_js = get_option( 'ter_config', array() );
	// Use localized defaults (language-aware).
	$defaults = ter_get_localized_defaults();

	// Merge: defaults first, then override with saved options (saved options take precedence).
	$labels = array_merge( $defaults, $opts_for_js );

	wp_localize_script(
		'ter-script',
		'terData',
		array(
			'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
			'nonce'            => wp_create_nonce( 'ter_nonce' ),
			'sendCustomerCopy' => ! empty( $labels['send_customer_copy'] ) ? 1 : 0,
			'labels'           => $labels,
			'rates'            => array(
				'km'        => isset( $labels['rate_km_allowance'] ) ? floatval( $labels['rate_km_allowance'] ) : 0.30,
				'passenger' => isset( $labels['rate_passenger_allowance'] ) ? floatval( $labels['rate_passenger_allowance'] ) : 0.01,
			),
		)
	);

	// Load form template.
	ob_start();
	include plugin_dir_path( __FILE__ ) . 'templates/travel-expense-report-form.php';
	return ob_get_clean();
}

/**
 * Append entries to a deletion log stored in option 'ter_deletion_log'.
 * Each log item: ['time'=>int, 'user'=>string, 'context'=>string, 'entries'=>array]
 * Keep recent 200 entries.
 *
 * @param array $data Log item to append.
 * @return void
 */
function ter_append_deletion_log( $data ) {
	$log      = get_option( 'ter_deletion_log', array() );
	$user     = function_exists( 'wp_get_current_user' ) ? wp_get_current_user() : null;
	$username = is_object( $user ) && ! empty( $user->user_login ) ? $user->user_login : 'unknown';
	$item     = array(
		'time'    => time(),
		'user'    => $username,
		'context' => isset( $data['context'] ) ? $data['context'] : '',
		'entries' => isset( $data['entries'] ) ? $data['entries'] : array(),
	);
	$log[]    = $item;
	// Keep last 200.
	if ( count( $log ) > 200 ) {
		$log = array_slice( $log, -200 );
	}
	update_option( 'ter_deletion_log', $log );
	// Also write an error_log line for quick debugging.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		ter_log( 'ter_deletion_log: ' . wp_json_encode( $item ) );
	}
}

/**
 * Get the deletion log (array)
 */
function ter_get_deletion_log() {
	return get_option( 'ter_deletion_log', array() );
}

// AJAX handler to clear deletion log (admin only).
add_action( 'wp_ajax_ter_clear_deletion_log', 'ter_clear_deletion_log' );
/**
 * AJAX handler to clear the deletion log (admin only).
 *
 * @return void
 */
function ter_clear_deletion_log() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'No permission.' );
		return;
	}
	check_ajax_referer( 'ter_clear_log', 'nonce' );
	update_option( 'ter_deletion_log', array() );
	wp_send_json_success( 'Log cleared.' );
}
