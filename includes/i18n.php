<?php
/**
 * Travel Expense Report - Internationalization & Localization
 *
 * Handles language support for English and German
 * Integrates with WordPress language settings
 *
 * @package TravelExpenseReport
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Text domain constant.
define( 'TER_TEXTDOMAIN', 'travel-expense-report' );

// Register text domain for both frontend and admin
// Use early hooks to ensure textdomain is loaded before admin menu is registered.
add_action( 'plugins_loaded', 'ter_load_textdomain', 10 );
add_action( 'admin_init', 'ter_load_textdomain', 1 ); // Load earlier in admin context.

/**
 * Load the plugin text domain.
 *
 * @return void
 */
function ter_load_textdomain() {
	// Check if already loaded (function exists in WordPress 4.6+).
	if ( function_exists( 'is_textdomain_loaded' ) && is_textdomain_loaded( TER_TEXTDOMAIN ) ) {
		return;
	}

	// Load translation files from /languages directory
	// Parameters:
	// 1. Text domain (string)
	// 2. Deprecated path (false)
	// 3. Relative path from plugins directory.

	$plugin_rel_path = dirname( plugin_basename( __FILE__ ) ) . '/../languages';
	$loaded          = load_plugin_textdomain( TER_TEXTDOMAIN, false, $plugin_rel_path );

	// Debug.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		ter_log( 'TER i18n: Plugin file: ' . __FILE__ );
		ter_log( 'TER i18n: Plugin basename: ' . plugin_basename( __FILE__ ) );
		ter_log( 'TER i18n: Relative path: ' . $plugin_rel_path );
		ter_log( 'TER i18n: Textdomain loaded: ' . ( $loaded ? 'YES' : 'NO' ) );
		ter_log( 'TER i18n: Current locale: ' . get_locale() );
	}
}

/**
 * Get current language code (en_US, de_DE, etc.)
 */
function ter_get_current_language() {
	$lang = get_locale();
	// Return just the language part (en, de, etc.).
	return substr( $lang, 0, 2 );
}

/**
 * Get localized configuration defaults based on current WordPress language
 * Returns all translatable strings without translation
 * (so that they can be translated at runtime with __())
 */
function ter_get_localized_defaults() {
	return array(
		// General Settings.
		'recipient_email'                => '',
		'send_customer_copy'             => 0,
		'delete_uploads_after_send'      => 0,
		'temp_folder'                    => 'travel-expense-report',
		'subject_prefix'                 => __( ' [Travel Expense]', 'travel-expense-report' ),

		// Cost Rates.
		'rate_km_allowance'              => '0.30',
		'rate_passenger_allowance'       => '0.01',

		// Email Templates.
		'internal_subject'               => __( 'Travel expense report — {firstname} {lastname}', 'travel-expense-report' ),
		// phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings -- Email body template keeps its markup.
		'internal_message'               => __( '<p>Internal preview: {firstname} {lastname} — {event} — {total} €</p>', 'travel-expense-report' ),
		'customer_subject'               => __( 'Copy of your travel expense report — {event}', 'travel-expense-report' ),
		'customer_message'               => __( '<p>Hi {firstname},<br>This is a preview of your expense report for {event} (Total: {total} €).</p>', 'travel-expense-report' ),
		'success_message'                => __( 'Email sent successfully!', 'travel-expense-report' ),
		'failure_message'                => __( 'Error sending email.', 'travel-expense-report' ),

		// Form placeholders.
		'placeholder_iban'               => 'DE89 3704 0044 0532 0130 00',
		'placeholder_bic'                => 'COBADEFFXXX',
		'placeholder_event'              => __( 'e.g. Summer Camp 2025, January group meeting', 'travel-expense-report' ),
		'placeholder_organizer'          => __( 'e.g. DPSG Group 1, District Youth Office', 'travel-expense-report' ),
		'placeholder_cost_center'        => '',
		'placeholder_email'              => 'name@example.com',
		'placeholder_trip_description'   => __( 'Trip description', 'travel-expense-report' ),
		'placeholder_trip_start'         => __( 'Trip start', 'travel-expense-report' ),
		'placeholder_trip_destination'   => __( 'Trip destination', 'travel-expense-report' ),
		'placeholder_ticket_start'       => __( 'Start', 'travel-expense-report' ),
		'placeholder_ticket_destination' => __( 'Destination', 'travel-expense-report' ),
		'placeholder_ticket_description' => __( 'Description', 'travel-expense-report' ),

		// Form labels and messages.
		'label_first_name'               => __( 'First name', 'travel-expense-report' ),
		'label_last_name'                => __( 'Last name', 'travel-expense-report' ),
		'label_iban'                     => __( 'IBAN', 'travel-expense-report' ),
		'label_bic'                      => __( 'BIC', 'travel-expense-report' ),
		'label_event'                    => __( 'Event / Activity', 'travel-expense-report' ),
		'label_organizer'                => __( 'Organizer', 'travel-expense-report' ),
		'label_cost_center'              => __( 'Cost center number', 'travel-expense-report' ),
		'label_customer_email'           => __( 'Email address (to receive a copy)', 'travel-expense-report' ),
		'label_goods_section'            => __( 'Advance payments / purchases', 'travel-expense-report' ),
		'label_description'              => __( 'Description', 'travel-expense-report' ),
		'label_amount'                   => __( 'Amount (€)', 'travel-expense-report' ),
		'label_receipt'                  => __( 'Receipt', 'travel-expense-report' ),
		'label_action'                   => __( 'Action', 'travel-expense-report' ),
		'label_trips_section'            => __( 'Trips', 'travel-expense-report' ),
		'label_kilometers'               => __( 'Kilometers', 'travel-expense-report' ),
		'label_passengers'               => __( 'Passengers', 'travel-expense-report' ),
		'label_start'                    => __( 'Start', 'travel-expense-report' ),
		'label_destination'              => __( 'Destination', 'travel-expense-report' ),
		'label_tickets_section'          => __( 'Train tickets', 'travel-expense-report' ),
		'label_cost'                     => __( 'Cost (€)', 'travel-expense-report' ),
		'label_ticket'                   => __( 'Ticket', 'travel-expense-report' ),

		// Buttons.
		'button_add_goods'               => __( '+ Add purchase', 'travel-expense-report' ),
		'button_upload_receipt'          => __( 'Upload receipt', 'travel-expense-report' ),
		'button_add_trip'                => __( '+ Add trip', 'travel-expense-report' ),
		'button_add_ticket'              => __( '+ Add train ticket', 'travel-expense-report' ),
		'button_send_email'              => __( 'Send by email', 'travel-expense-report' ),

		// Messages.
		'message_sending'                => __( 'Sending email...', 'travel-expense-report' ),
		'message_success'                => __( 'Email sent successfully!', 'travel-expense-report' ),
		'message_error'                  => __( 'Error sending email.', 'travel-expense-report' ),

		// Errors.
		'error_required_fields'          => __( 'Please fill all required personal details.', 'travel-expense-report' ),
		'error_invalid_iban'             => __( 'Invalid IBAN.', 'travel-expense-report' ),
		'error_invalid_bic'              => __( 'Invalid BIC.', 'travel-expense-report' ),
		'error_invalid_email'            => __( 'Invalid email address.', 'travel-expense-report' ),
		'error_no_expenses'              => __( 'Please add at least one expense item.', 'travel-expense-report' ),
		'error_no_receipts'              => __( '⚠️ Please upload receipts for all filled purchases.', 'travel-expense-report' ),
		'error_no_tickets'               => __( '⚠️ Please upload receipts for all filled train tickets.', 'travel-expense-report' ),
		'error_loading'                  => __( 'Error loading form.', 'travel-expense-report' ),

		// Info boxes.
		'info_submit_per_event'          => __( '⚠️ Important note: Please submit one form per event or cost center.', 'travel-expense-report' ),
		'info_receipts_required'         => __( 'ℹ️ Note: Only advance payments and purchases with an invoice, receipt or self-issued slip are accepted. Please upload a receipt for every filled entry.', 'travel-expense-report' ),
		'info_cost_summary'              => __( '📊 Cost summary:', 'travel-expense-report' ),
		'info_km_allowance'              => __( 'Kilometer allowance', 'travel-expense-report' ),
		'info_passenger_allowance'       => __( 'Passenger allowance', 'travel-expense-report' ),
		'info_trip_costs'                => __( 'Total trip costs:', 'travel-expense-report' ),
		'info_ticket_costs'              => __( 'Total train tickets:', 'travel-expense-report' ),
		'info_total_costs'               => __( 'Total costs:', 'travel-expense-report' ),

		// Email labels.
		'email_personal_details'         => __( 'Personal details', 'travel-expense-report' ),
		'email_name'                     => __( 'Name:', 'travel-expense-report' ),
		'email_iban'                     => __( 'IBAN:', 'travel-expense-report' ),
		'email_bic'                      => __( 'BIC:', 'travel-expense-report' ),
		'email_event'                    => __( 'Event:', 'travel-expense-report' ),
		'email_organizer'                => __( 'Organizer:', 'travel-expense-report' ),
		'email_advance_payments'         => __( 'Advance payments / purchases', 'travel-expense-report' ),
		'email_trips'                    => __( 'Trips', 'travel-expense-report' ),
		'email_route'                    => __( 'Route', 'travel-expense-report' ),
		'email_total_trip_costs'         => __( 'Total trip costs:', 'travel-expense-report' ),
		'email_tickets'                  => __( 'Train tickets', 'travel-expense-report' ),
		'email_status'                   => __( 'Status', 'travel-expense-report' ),
		'email_ticket_badge'             => __( '✓ Ticket', 'travel-expense-report' ),
		'email_provided'                 => __( 'Provided', 'travel-expense-report' ),
		'email_missing'                  => __( 'Missing', 'travel-expense-report' ),
		'email_title'                    => __( 'Travel expense report from', 'travel-expense-report' ),
	);
}

/**
 * Get a translated string using the default value and the translation.
 * This ensures proper i18n at runtime (when the textdomain is loaded).
 *
 * @param string $key The config key.
 * @param string $default_value Default English text.
 * @param string $context Optional context for disambiguation.
 * @return string Translated text or default.
 */
function ter_get_translated( $key, $default_value, $context = '' ) {
	if ( $context ) {
		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText,WordPress.WP.I18n.NonSingularStringLiteralContext,WordPress.WP.I18n.NonSingularStringLiteralDomain -- Runtime lookup of user-configurable strings.
		return _x( $default_value, $context, TER_TEXTDOMAIN );
	}
	// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText,WordPress.WP.I18n.NonSingularStringLiteralDomain -- Runtime lookup of user-configurable strings.
	return __( $default_value, TER_TEXTDOMAIN );
}

/**
 * Get a translated string with attribute escaping.
 * Safe for use in HTML attributes.
 *
 * @param string $key The config key.
 * @param string $default_value Default English text.
 * @return string
 */
function ter_get_translated_attr( $key, $default_value ) {
	return esc_attr( ter_get_translated( $key, $default_value ) );
}

/**
 * Get a translated string with HTML escaping.
 * Safe for use in HTML content.
 *
 * @param string $key The config key.
 * @param string $default_value Default English text.
 * @return string
 */
function ter_get_translated_html( $key, $default_value ) {
	return esc_html( ter_get_translated( $key, $default_value ) );
}

/**
 * Get a translated string allowing HTML tags.
 * Use only with trusted content!
 *
 * @param string $key The config key.
 * @param string $default_value Default English text.
 * @return string
 */
function ter_get_translated_html_content( $key, $default_value ) {
	return wp_kses_post( ter_get_translated( $key, $default_value ) );
}
