<?php
/**
 * Travel Expense Report - Configuration System
 *
 * Stores all plugin settings with defaults
 * Integrates with i18n system for translations
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Helper function to get config value with localized defaults
function ter_get_config($key, $default = '') {
    $config = get_option('ter_config', array());
    $defaults = ter_get_default_config();

    // If value is explicitly set in config AND it's different from the hardcoded English default, use it.
    // This allows users to customize strings while still allowing i18n for non-customized defaults.
    if (isset($config[$key]) && isset($defaults[$key]) && $config[$key] !== $defaults[$key]) {
        // For empty values in optional fields (like cost_center), return empty string if it was intended
        return $config[$key];
    }

    // If it's a field that doesn't have a default in ter_get_default_config (like recipient_email which is empty)
    // but is set in config, return it.
    if (isset($config[$key]) && !isset($defaults[$key]) && $config[$key] !== '') {
        return $config[$key];
    }

    // Otherwise get localized default (if i18n is ready)
    // This will use translations from .mo/.po files
    if (function_exists('ter_get_localized_defaults')) {
        $localized_defaults = ter_get_localized_defaults();
        if (isset($localized_defaults[$key])) {
            $default_value = $localized_defaults[$key];

            // For label_*, button_*, message_*, error_*, info_* keys
            // Apply translation function to get i18n translated text
            if (strpos($key, 'label_') === 0 ||
                strpos($key, 'button_') === 0 ||
                strpos($key, 'message_') === 0 ||
                strpos($key, 'error_') === 0 ||
                strpos($key, 'info_') === 0 ||
                strpos($key, 'email_') === 0) {
                // Use ter_get_translated to get the translated string
                if (function_exists('ter_get_translated')) {
                    return ter_get_translated($key, $default_value);
                }
            }

            return $default_value;
        }
    }

    // Final fallback to provided default
    return $default;
}

/**
 * Get all default configuration values (for initialization)
 * These are non-translatable defaults - actual labels come from i18n
 */
function ter_get_default_config() {
    return array(
        // General Settings
        'recipient_email' => '',
        'send_customer_copy' => 0,
        'delete_uploads_after_send' => 0,
        'temp_folder' => 'travel-expense-report',
        'subject_prefix' => '',

        // Cost Rates
        'rate_km_allowance' => '0.30',
        'rate_passenger_allowance' => '0.01',

        // Email Templates (will be localized)
        'internal_subject' => 'Travel expense report — {firstname} {lastname}',
        'internal_message' => '<p>Internal preview: {firstname} {lastname} — {event} — {total} €</p>',
        'customer_subject' => 'Copy of your travel expense report — {event}',
        'customer_message' => '<p>Hi {firstname},<br>This is a preview of your expense report for {event} (Total: {total} €).</p>',
        'success_message' => 'Email sent successfully!',
        'failure_message' => 'Error sending email.',

        // Form placeholders
        'placeholder_iban' => 'DE89 3704 0044 0532 0130 00',
        'placeholder_bic' => 'COBADEFFXXX',
        'placeholder_event' => 'e.g. Summer Camp 2025, January group meeting',
        'placeholder_organizer' => 'e.g. DPSG Group 1, District Youth Office',
        'placeholder_cost_center' => '',
        'placeholder_email' => 'name@example.com',
        'placeholder_trip_description' => 'Trip description',
        'placeholder_trip_start' => 'Trip start',
        'placeholder_trip_destination' => 'Trip destination',
        'placeholder_ticket_start' => 'Start',
        'placeholder_ticket_destination' => 'Destination',
        'placeholder_ticket_description' => 'Description',

        // All form labels and messages (will be translated via i18n)
        'label_first_name' => 'First name',
        'label_last_name' => 'Last name',
        'label_iban' => 'IBAN',
        'label_bic' => 'BIC',
        'label_event' => 'Event / Activity',
        'label_organizer' => 'Organizer',
        'label_cost_center' => 'Cost center number',
        'label_customer_email' => 'Email address (to receive a copy)',
        'label_goods_section' => 'Advance payments / purchases',
        'label_description' => 'Description',
        'label_amount' => 'Amount (€)',
        'label_receipt' => 'Receipt',
        'label_action' => 'Action',
        'label_trips_section' => 'Trips',
        'label_kilometers' => 'Kilometers',
        'label_passengers' => 'Passengers',
        'label_start' => 'Start',
        'label_destination' => 'Destination',
        'label_tickets_section' => 'Train tickets',
        'label_cost' => 'Cost (€)',
        'label_ticket' => 'Ticket',

        // Buttons
        'button_add_goods' => '+ Add purchase',
        'button_upload_receipt' => 'Upload receipt',
        'button_add_trip' => '+ Add trip',
        'button_add_ticket' => '+ Add train ticket',
        'button_send_email' => 'Send by email',

        // Messages
        'message_sending' => 'Sending email...',
        'message_success' => 'Email sent successfully!',
        'message_error' => 'Error sending email.',

        // Errors
        'error_required_fields' => 'Please fill all required personal details.',
        'error_invalid_iban' => 'Invalid IBAN.',
        'error_invalid_bic' => 'Invalid BIC.',
        'error_invalid_email' => 'Invalid email address.',
        'error_no_expenses' => 'Please add at least one expense item.',
        'error_no_receipts' => '⚠️ Please upload receipts for all filled purchases.',
        'error_no_tickets' => '⚠️ Please upload receipts for all filled train tickets.',
        'error_loading' => 'Error loading form.',

        // Info boxes
        'info_submit_per_event' => '⚠️ Important note: Please submit one form per event or cost center.',
        'info_receipts_required' => 'ℹ️ Note: Only advance payments and purchases with an invoice, receipt or self-issued slip are accepted. Please upload a receipt for every filled entry.',
        'info_cost_summary' => '📊 Cost summary:',
        'info_km_allowance' => 'Kilometer allowance',
        'info_passenger_allowance' => 'Passenger allowance',
        'info_trip_costs' => 'Total trip costs:',
        'info_ticket_costs' => 'Total train tickets:',
        'info_total_costs' => 'Total costs:',

        // Email labels
        'email_personal_details' => 'Personal details',
        'email_name' => 'Name:',
        'email_iban' => 'IBAN:',
        'email_bic' => 'BIC:',
        'email_event' => 'Event:',
        'email_organizer' => 'Organizer:',
        'email_advance_payments' => 'Advance payments / purchases',
        'email_trips' => 'Trips',
        'email_route' => 'Route',
        'email_total_trip_costs' => 'Total trip costs:',
        'email_tickets' => 'Train tickets',
        'email_status' => 'Status',
        'email_ticket_badge' => '✓ Ticket',
        'email_provided' => 'Provided',
        'email_missing' => 'Missing',
        'email_title' => 'Travel expense report from',
    );
}

// Initialize config with defaults if not set
function ter_init_config() {
    $config = get_option('ter_config');
    if (empty($config) || !is_array($config)) {
        // Use English defaults (will be translated at display time)
        $defaults = ter_get_default_config();
        update_option('ter_config', $defaults);
    }
}

// Add initialization hook - use plugins_loaded with priority 20 to run after ter_load_textdomain (priority 10)
add_action('plugins_loaded', 'ter_init_config', 20);

/**
 * Get localized kilometer allowance message
 * Uses the configured rate and translates the unit text
 */
function ter_get_km_allowance_message() {
    $rate = ter_get_config('rate_km_allowance', '0.30');
    $text = ter_get_config('info_km_allowance', 'Kilometer allowance');
    return sprintf('%s: %.2f €/km', $text, floatval($rate));
}

/**
 * Get localized passenger allowance message
 * Uses the configured rate and translates the unit text
 */
function ter_get_passenger_allowance_message() {
    $rate = ter_get_config('rate_passenger_allowance', '0.01');
    $text = ter_get_config('info_passenger_allowance', 'Passenger allowance');
    return sprintf('%s: %.2f € per km per passenger', $text, floatval($rate));
}
