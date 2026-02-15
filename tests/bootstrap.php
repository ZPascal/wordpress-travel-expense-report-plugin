<?php
/**
 * PHPUnit Bootstrap for Travel Expense Report Plugin
 *
 * Provides minimal WordPress function stubs required to include and test the plugin.
 * Compatible with PHP 8.1 and PHPUnit 9.5+
 */

declare(strict_types=1);

// Show errors during tests
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Minimal stubs for WordPress functions used in the plugin file.
if (!function_exists('add_action')) {
    function add_action() { return; }
}
if (!function_exists('add_shortcode')) {
    function add_shortcode() { return; }
}
if (!function_exists('add_options_page')) {
    function add_options_page() { return; }
}
if (!function_exists('register_setting')) {
    function register_setting() { return; }
}
if (!function_exists('add_settings_section')) {
    function add_settings_section() { return; }
}
if (!function_exists('add_settings_field')) {
    function add_settings_field() { return; }
}
if (!function_exists('settings_fields')) {
    function settings_fields() { return; }
}
if (!function_exists('do_settings_sections')) {
    function do_settings_sections() { return; }
}
if (!function_exists('submit_button')) {
    function submit_button() { return; }
}
if (!function_exists('wp_enqueue_script')) {
    function wp_enqueue_script() { return; }
}
if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style() { return; }
}
if (!function_exists('wp_localize_script')) {
    function wp_localize_script() { return; }
}
if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($salt = '') { return 'test-nonce'; }
}
if (!function_exists('admin_url')) {
    function admin_url($path = '') { return 'http://example.org/wp-admin/' . ltrim($path, '/'); }
}
if (!function_exists('get_option')) {
    function get_option($key, $default = null) { return $default; }
}
if (!function_exists('update_option')) {
    function update_option($k, $v) { return true; }
}
if (!function_exists('sanitize_file_name')) {
    function sanitize_file_name($name) { return preg_replace('/[^A-Za-z0-9._-]/', '-', $name); }
}
if (!function_exists('trailingslashit')) {
    function trailingslashit($s) { return rtrim($s, "\\/") . '/'; }
}
if (!function_exists('get_temp_dir')) {
    function get_temp_dir() { return sys_get_temp_dir(); }
}
if (!function_exists('wp_mkdir_p')) {
    function wp_mkdir_p($dir) { return @mkdir($dir, 0777, true) || is_dir($dir); }
}
if (!function_exists('wp_unique_filename')) {
    function wp_unique_filename($dir, $filename) {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $unique = $name . '-' . uniqid();
        return $unique . ($ext ? '.' . $ext : '');
    }
}
if (!function_exists('esc_html')) {
    function esc_html($s) { return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
}
if (!function_exists('esc_attr')) {
    function esc_attr($s) { return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
}
if (!function_exists('esc_textarea')) {
    function esc_textarea($s) { return htmlspecialchars((string)$s, ENT_NOQUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
}
if (!function_exists('is_email')) {
    function is_email($s) { return filter_var($s, FILTER_VALIDATE_EMAIL) !== false; }
}
if (!function_exists('sanitize_email')) {
    function sanitize_email($s) { return filter_var($s, FILTER_SANITIZE_EMAIL); }
}
if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($s) { return trim(strip_tags((string)$s)); }
}
if (!function_exists('wp_kses_post')) {
    function wp_kses_post($s) { return $s; }
}
if (!function_exists('wp_mail')) {
    function wp_mail() { return true; }
}
if (!function_exists('wp_send_json_error')) {
    function wp_send_json_error($data = null) { echo json_encode(array('success' => false, 'data' => $data)); }
}
if (!function_exists('wp_send_json_success')) {
    function wp_send_json_success($data = null) { echo json_encode(array('success' => true, 'data' => $data)); }
}
if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action = -1) { return true; }
}
if (!function_exists('status_header')) {
    function status_header($code = 200) { http_response_code($code); }
}
if (!function_exists('wp_die')) {
    function wp_die($message = '') { throw new \Exception('wp_die: ' . $message); }
}
if (!function_exists('check_ajax_referer')) {
    function check_ajax_referer($action = -1, $query_arg = false, $die = true) { return true; }
}
if (!function_exists('add_query_arg')) {
    function add_query_arg($args, $url = '') {
        if (is_array($args)) {
            $sep = strpos($url, '?') === false ? '?' : '&';
            return $url . $sep . http_build_query($args);
        }
        return $url;
    }
}

// Include the plugin file under test
require_once dirname(__DIR__) . '/travel_expense_report.php';

// Ensure tests run in project root
chdir(dirname(__DIR__));


