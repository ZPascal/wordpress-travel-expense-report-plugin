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

// Register text domain for both frontend and admin.
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

	// Load translation files from /languages directory.
	// Parameters:
	// 1. Text domain (string)
	// 2. Deprecated path (false)
	// 3. Relative path from plugins directory.

	$plugin_rel_path = dirname( plugin_basename( __FILE__ ) ) . '/../languages';
	// Bundled translations live in /languages and are not served from WordPress.org language packs.
	$loaded = load_plugin_textdomain( TER_TEXTDOMAIN, false, $plugin_rel_path ); // phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound

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
 * Get current language code (en, de, etc.).
 *
 * @return string Two-letter language code.
 */
function ter_get_current_language() {
	$lang = get_locale();
	// Return just the language part (en, de, etc.).
	return substr( $lang, 0, 2 );
}

/**
 * Get localized configuration defaults based on current WordPress language
 * Returns all translatable strings WITHOUT translation wrapped in __()
 * (so that they can be translated at runtime with __())
 */
function ter_get_untranslated_defaults() {
	return array(
		// General Settings.
		'recipient_email'                => '',
		'send_customer_copy'             => 0,
		'delete_uploads_after_send'      => 0,
		'temp_folder'                    => 'travel-expense-report',
		'subject_prefix'                 => '[Travel Expense]',

		// Cost Rates.
		'rate_km_allowance'              => '0.30',
		'rate_passenger_allowance'       => '0.01',

		// Email Templates.
		'internal_subject'               => 'Travel expense report — {firstname} {lastname}',
		'internal_message'               => '<p>Internal preview: {firstname} {lastname} — {event} — {total} €</p>',
		'customer_subject'               => 'Copy of your travel expense report — {event}',
		'customer_message'               => '<p>Hi {firstname},<br>This is a preview of your expense report for {event} (Total: {total} €).</p>',
		'email_footer'                   => 'This travel expense report was generated automatically by the online form.',
		'success_message'                => 'Email sent successfully!',
		'failure_message'                => 'Error sending email.',

		// Form placeholders.
		'placeholder_iban'               => 'DE89 3704 0044 0532 0130 00',
		'placeholder_bic'                => 'COBADEFFXXX',
		'placeholder_event'              => 'e.g. Summer Camp 2025, January group meeting',
		'placeholder_organizer'          => 'e.g. DPSG Group 1, District Youth Office',
		'placeholder_cost_center'        => '',
		'placeholder_email'              => 'name@example.com',
		'placeholder_trip_description'   => 'Trip description',
		'placeholder_trip_start'         => 'Trip start',
		'placeholder_trip_destination'   => 'Trip destination',
		'placeholder_ticket_start'       => 'Start',
		'placeholder_ticket_destination' => 'Destination',
		'placeholder_ticket_description' => 'Description',

		// Form labels and messages.
		'label_first_name'               => 'First name',
		'label_last_name'                => 'Last name',
		'label_iban'                     => 'IBAN',
		'label_bic'                      => 'BIC',
		'label_event'                    => 'Event / Activity',
		'label_organizer'                => 'Organizer',
		'label_cost_center'              => 'Cost center number',
		'label_customer_email'           => 'Email address (to receive a copy)',
		'label_goods_section'            => 'Advance payments / purchases',
		'label_description'              => 'Description',
		'label_amount'                   => 'Amount (€)',
		'label_receipt'                  => 'Receipt',
		'label_action'                   => 'Action',
		'label_trips_section'            => 'Trips',
		'label_kilometers'               => 'Kilometers',
		'label_passengers'               => 'Passengers',
		'label_start'                    => 'Start',
		'label_destination'              => 'Destination',
		'label_tickets_section'          => 'Train tickets',
		'label_cost'                     => 'Cost (€)',
		'label_ticket'                   => 'Ticket',

		// Buttons.
		'button_add_goods'               => '+ Add purchase',
		'button_upload_receipt'          => 'Upload receipt',
		'button_add_trip'                => '+ Add trip',
		'button_add_ticket'              => '+ Add train ticket',
		'button_send_email'              => 'Send by email',

		// Messages.
		'message_sending'                => 'Sending email...',
		'message_success'                => 'Email sent successfully!',
		'message_error'                  => 'Error sending email.',

		// Errors.
		'error_required_fields'          => 'Please fill all required personal details.',
		'error_invalid_iban'             => 'Invalid IBAN.',
		'error_invalid_bic'              => 'Invalid BIC.',
		'error_invalid_email'            => 'Invalid email address.',
		'error_no_expenses'              => 'Please add at least one expense item.',
		'error_no_receipts'              => 'Please upload receipts for all filled purchases.',
		'error_no_tickets'               => 'Please upload receipts for all filled train tickets.',
		'error_loading'                  => 'Error loading form.',

		// Info boxes.
		'info_submit_per_event'          => 'Important note: Please submit one form per event or cost center.',
		'info_receipts_required'         => 'Note: Only advance payments and purchases with an invoice, receipt or self-issued slip are accepted. Please upload a receipt for every filled entry.',
		'info_cost_summary'              => 'Cost summary:',
		'info_km_allowance'              => 'Kilometer allowance',
		'info_passenger_allowance'       => 'Passenger allowance',
		'info_trip_costs'                => 'Total trip costs:',
		'info_ticket_costs'              => 'Total train tickets:',
		'info_total_costs'               => 'Total costs:',

		// Email labels.
		'email_personal_details'         => 'Personal details',
		'email_name'                     => 'Name:',
		'email_iban'                     => 'IBAN:',
		'email_bic'                      => 'BIC:',
		'email_event'                    => 'Event:',
		'email_organizer'                => 'Organizer:',
		'email_advance_payments'         => 'Advance payments / purchases',
		'email_trips'                    => 'Trips',
		'email_route'                    => 'Route',
		'email_total_trip_costs'         => 'Total trip costs:',
		'email_tickets'                  => 'Train tickets',
		'email_status'                   => 'Status',
		'email_ticket_badge'             => 'Ticket',
		'email_provided'                 => 'Provided',
		'email_missing'                  => 'Missing',
		'email_title'                    => 'Travel expense report from',
	);
}

/**
 * Get localized configuration defaults based on current WordPress language
 * Returns all translatable strings WITH translation wrapped in __()
 * (for use in backend configuration, not for rendering labels)
 *
 * @deprecated Use ter_get_untranslated_defaults() instead and apply __() at runtime
 */
function ter_get_localized_defaults() {
	return array(
		// General Settings.
		'recipient_email'                => '',
		'send_customer_copy'             => 0,
		'delete_uploads_after_send'      => 0,
		'temp_folder'                    => 'travel-expense-report',
		'subject_prefix'                 => '[Travel Expense]',

		// Cost Rates.
		'rate_km_allowance'              => '0.30',
		'rate_passenger_allowance'       => '0.01',

		// Email Templates.
		'internal_subject'               => 'Travel expense report — {firstname} {lastname}',
		'internal_message'               => '<p>Internal preview: {firstname} {lastname} — {event} — {total} €</p>',
		'customer_subject'               => 'Copy of your travel expense report — {event}',
		'customer_message'               => '<p>Hi {firstname},<br>This is a preview of your expense report for {event} (Total: {total} €).</p>',
		'email_footer'                   => 'This travel expense report was generated automatically by the online form.',
		'success_message'                => 'Email sent successfully!',
		'failure_message'                => 'Error sending email.',

		// Form placeholders.
		'placeholder_iban'               => 'DE89 3704 0044 0532 0130 00',
		'placeholder_bic'                => 'COBADEFFXXX',
		'placeholder_event'              => 'e.g. Summer Camp 2025, January group meeting',
		'placeholder_organizer'          => 'e.g. DPSG Group 1, District Youth Office',
		'placeholder_cost_center'        => '',
		'placeholder_email'              => 'name@example.com',
		'placeholder_trip_description'   => 'Trip description',
		'placeholder_trip_start'         => 'Trip start',
		'placeholder_trip_destination'   => 'Trip destination',
		'placeholder_ticket_start'       => 'Start',
		'placeholder_ticket_destination' => 'Destination',
		'placeholder_ticket_description' => 'Description',

		// Form labels and messages.
		'label_first_name'               => 'First name',
		'label_last_name'                => 'Last name',
		'label_iban'                     => 'IBAN',
		'label_bic'                      => 'BIC',
		'label_event'                    => 'Event / Activity',
		'label_organizer'                => 'Organizer',
		'label_cost_center'              => 'Cost center number',
		'label_customer_email'           => 'Email address (to receive a copy)',
		'label_goods_section'            => 'Advance payments / purchases',
		'label_description'              => 'Description',
		'label_amount'                   => 'Amount (€)',
		'label_receipt'                  => 'Receipt',
		'label_action'                   => 'Action',
		'label_trips_section'            => 'Trips',
		'label_kilometers'               => 'Kilometers',
		'label_passengers'               => 'Passengers',
		'label_start'                    => 'Start',
		'label_destination'              => 'Destination',
		'label_tickets_section'          => 'Train tickets',
		'label_cost'                     => 'Cost (€)',
		'label_ticket'                   => 'Ticket',

		// Buttons.
		'button_add_goods'               => '+ Add purchase',
		'button_upload_receipt'          => 'Upload receipt',
		'button_add_trip'                => '+ Add trip',
		'button_add_ticket'              => '+ Add train ticket',
		'button_send_email'              => 'Send by email',

		// Messages.
		'message_sending'                => 'Sending email...',
		'message_success'                => 'Email sent successfully!',
		'message_error'                  => 'Error sending email.',

		// Errors.
		'error_required_fields'          => 'Please fill all required personal details.',
		'error_invalid_iban'             => 'Invalid IBAN.',
		'error_invalid_bic'              => 'Invalid BIC.',
		'error_invalid_email'            => 'Invalid email address.',
		'error_no_expenses'              => 'Please add at least one expense item.',
		'error_no_receipts'              => 'Please upload receipts for all filled purchases.',
		'error_no_tickets'               => 'Please upload receipts for all filled train tickets.',
		'error_loading'                  => 'Error loading form.',

		// Info boxes.
		'info_submit_per_event'          => 'Important note: Please submit one form per event or cost center.',
		'info_receipts_required'         => 'Note: Only advance payments and purchases with an invoice, receipt or self-issued slip are accepted. Please upload a receipt for every filled entry.',
		'info_cost_summary'              => 'Cost summary:',
		'info_km_allowance'              => 'Kilometer allowance',
		'info_passenger_allowance'       => 'Passenger allowance',
		'info_trip_costs'                => 'Total trip costs:',
		'info_ticket_costs'              => 'Total train tickets:',
		'info_total_costs'               => 'Total costs:',

		// Email labels.
		'email_personal_details'         => 'Personal details',
		'email_name'                     => 'Name:',
		'email_iban'                     => 'IBAN:',
		'email_bic'                      => 'BIC:',
		'email_event'                    => 'Event:',
		'email_organizer'                => 'Organizer:',
		'email_advance_payments'         => 'Advance payments / purchases',
		'email_trips'                    => 'Trips',
		'email_route'                    => 'Route',
		'email_total_trip_costs'         => 'Total trip costs:',
		'email_tickets'                  => 'Train tickets',
		'email_status'                   => 'Status',
		'email_ticket_badge'             => 'Ticket',
		'email_provided'                 => 'Provided',
		'email_missing'                  => 'Missing',
		'email_title'                    => 'Travel expense report from',
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
