<?php
/**
 * i18n Test Script
 *
 * This script tests if the i18n system is working correctly
 * Run from WordPress admin to test translations
 */

// This should be run from within WordPress
if (!function_exists('__')) {
    die('This script must be run within WordPress context.');
}

// Test data
$test_strings = array(
    'General Settings' => 'general_settings',
    'Internal recipient email' => 'recipient_email',
    'Kilometer allowance (€/km)' => 'rate_km',
    'IBAN placeholder' => 'placeholder_iban',
    'Send copy to customer' => 'send_customer_copy',
    'Form Labels & Messages' => 'labels_messages',
);

$locale = get_locale();
$textdomain = 'travel-expense-report';

echo "=== Travel Expense Report - i18n Test ===\n";
echo "Current Locale: $locale\n";
echo "Textdomain: $textdomain\n";
echo "Textdomain Loaded: " . (is_textdomain_loaded($textdomain) ? 'YES' : 'NO') . "\n\n";

echo "Test Translations:\n";
echo str_pad('String Key', 30);
echo str_pad('English (Original)', 30);
echo str_pad('Translated', 30);
echo "Status\n";
echo str_repeat('-', 120) . "\n";

foreach ($test_strings as $english => $key) {
    $translated = __($english, $textdomain);
    $status = ($translated !== $english) ? '✓ TRANSLATED' : '✗ NOT Translated';

    echo str_pad($key, 30);
    echo str_pad($english, 30);
    echo str_pad(substr($translated, 0, 27), 30);
    echo $status . "\n";
}

echo "\n=== Translation Files ===\n";
$plugin_dir = dirname(dirname(__FILE__));
$po_file = $plugin_dir . '/languages/travel-expense-report-de_DE.po';
$mo_file = $plugin_dir . '/languages/travel-expense-report-de_DE.mo';

echo ".po file: " . (file_exists($po_file) ? "✓ Exists" : "✗ Missing") . "\n";
echo ".mo file: " . (file_exists($mo_file) ? "✓ Exists" : "✗ Missing") . "\n";

if (file_exists($mo_file)) {
    echo ".mo file size: " . filesize($mo_file) . " bytes\n";
    echo ".mo file date: " . date('Y-m-d H:i:s', filemtime($mo_file)) . "\n";
}

echo "\n=== Load Text Domain Check ===\n";
echo "is_textdomain_loaded('$textdomain'): " . (is_textdomain_loaded($textdomain) ? '✓ YES' : '✗ NO') . "\n";

