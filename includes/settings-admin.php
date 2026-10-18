<?php
/**
 * Travel Expense Report - Settings Page with i18n
 *
 * Renders settings with proper translations at display time
 *
 * @package TravelExpenseReport
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Register settings and admin page.
add_action( 'admin_menu', 'ter_add_settings_page' );
add_action( 'admin_init', 'ter_register_settings_advanced' );

/**
 * Register the settings page under Settings.
 *
 * @return void
 */
function ter_add_settings_page() {
	add_options_page(
		esc_html__( 'Travel Expense Report — Settings', 'travel-expense-report' ),
		esc_html__( 'Travel Expense Report', 'travel-expense-report' ),
		'manage_options',
		'ter-settings',
		'ter_render_settings_page_advanced'
	);
}

/**
 * Register settings, sections and fields.
 *
 * @return void
 */
function ter_register_settings_advanced() {
	register_setting(
		'ter_config_group',
		'ter_config',
		array(
			'sanitize_callback' => 'ter_sanitize_config',
			'show_in_rest'      => false,
		)
	);

	// TAB 1: GENERAL SETTINGS.
	add_settings_section( 'ter_basic_settings', 'general_settings', '__return_empty_string', 'ter-settings-general' );
	add_settings_field( 'ter_recipient_email', 'recipient_email', 'ter_field_email', 'ter-settings-general', 'ter_basic_settings', array( 'key' => 'recipient_email' ) );
	add_settings_field( 'ter_send_customer_copy', 'send_customer_copy', 'ter_field_checkbox', 'ter-settings-general', 'ter_basic_settings', array( 'key' => 'send_customer_copy' ) );
	add_settings_field( 'ter_delete_uploads', 'delete_uploads', 'ter_field_checkbox', 'ter-settings-general', 'ter_basic_settings', array( 'key' => 'delete_uploads_after_send' ) );
	add_settings_field( 'ter_temp_folder', 'temp_folder', 'ter_field_text', 'ter-settings-general', 'ter_basic_settings', array( 'key' => 'temp_folder' ) );
	add_settings_field( 'ter_subject_prefix', 'subject_prefix', 'ter_field_text', 'ter-settings-general', 'ter_basic_settings', array( 'key' => 'subject_prefix' ) );

	// TAB 2: EMAIL SETTINGS.
	add_settings_section( 'ter_internal_email', 'internal_email', '__return_empty_string', 'ter-settings-email' );
	add_settings_field( 'ter_internal_subject', 'internal_subject', 'ter_field_textarea', 'ter-settings-email', 'ter_internal_email', array( 'key' => 'internal_subject' ) );
	add_settings_field( 'ter_internal_message', 'internal_message', 'ter_field_textarea', 'ter-settings-email', 'ter_internal_email', array( 'key' => 'internal_message' ) );

	add_settings_section( 'ter_customer_email', 'customer_email', '__return_empty_string', 'ter-settings-email' );
	add_settings_field( 'ter_customer_subject', 'customer_subject', 'ter_field_textarea', 'ter-settings-email', 'ter_customer_email', array( 'key' => 'customer_subject' ) );
	add_settings_field( 'ter_customer_message', 'customer_message', 'ter_field_textarea', 'ter-settings-email', 'ter_customer_email', array( 'key' => 'customer_message' ) );

	add_settings_section( 'ter_form_messages', 'form_messages', '__return_empty_string', 'ter-settings-email' );
	add_settings_field( 'ter_success_message', 'success_message', 'ter_field_textarea', 'ter-settings-email', 'ter_form_messages', array( 'key' => 'success_message' ) );
	add_settings_field( 'ter_failure_message', 'failure_message', 'ter_field_textarea', 'ter-settings-email', 'ter_form_messages', array( 'key' => 'failure_message' ) );

	// TAB 3: COST RATES.
	add_settings_section( 'ter_rates', 'cost_rates', '__return_empty_string', 'ter-settings-rates' );
	add_settings_field( 'ter_rate_km', 'rate_km', 'ter_field_number', 'ter-settings-rates', 'ter_rates', array( 'key' => 'rate_km_allowance' ) );
	add_settings_field( 'ter_rate_passenger', 'rate_passenger', 'ter_field_number', 'ter-settings-rates', 'ter_rates', array( 'key' => 'rate_passenger_allowance' ) );

	// TAB 4: FORM PLACEHOLDERS.
	add_settings_section( 'ter_placeholders', 'placeholders', '__return_empty_string', 'ter-settings-placeholders' );
	$placeholder_keys = array( 'placeholder_iban', 'placeholder_bic', 'placeholder_event', 'placeholder_organizer', 'placeholder_email', 'placeholder_trip_description', 'placeholder_trip_start', 'placeholder_trip_destination', 'placeholder_ticket_start', 'placeholder_ticket_destination', 'placeholder_ticket_description' );
	foreach ( $placeholder_keys as $key ) {
		add_settings_field( 'ter_' . $key, $key, 'ter_field_textarea', 'ter-settings-placeholders', 'ter_placeholders', array( 'key' => $key ) );
	}

	// TAB 5: FORM LABELS & MESSAGES.
	add_settings_section( 'ter_templates', 'labels_messages', '__return_empty_string', 'ter-settings-labels' );

	// Get all translatable keys from both i18n defaults and config defaults.
	$i18n_defaults = ter_get_localized_defaults();
	$config        = ter_get_default_config();

	// Merge both to get all keys.
	$all_defaults = array_merge( $config, $i18n_defaults );

	// Register fields for all translatable keys.
	foreach ( $all_defaults as $key => $default ) {
		if ( strpos( $key, 'label_' ) === 0 || strpos( $key, 'button_' ) === 0 || strpos( $key, 'message_' ) === 0 || strpos( $key, 'error_' ) === 0 || strpos( $key, 'info_' ) === 0 || strpos( $key, 'email_' ) === 0 ) {
			add_settings_field( 'ter_' . $key, $key, 'ter_field_textarea', 'ter-settings-labels', 'ter_templates', array( 'key' => $key ) );
		}
	}
}

/**
 * Get translated label for a settings field using i18n.
 * This ensures proper translations from .mo/.po files.
 *
 * @param string $key Settings key.
 * @return string
 */
function ter_get_field_label( $key ) {
	// First check if this is a translatable key (label_*, button_*, etc.).
	// For these keys, show the TRANSLATED value from i18n, not the technical name.
	if ( strpos( $key, 'label_' ) === 0 ||
		strpos( $key, 'button_' ) === 0 ||
		strpos( $key, 'message_' ) === 0 ||
		strpos( $key, 'error_' ) === 0 ||
		strpos( $key, 'info_' ) === 0 ||
		strpos( $key, 'email_' ) === 0 ) {

		// Get the default value from i18n defaults.
		$i18n_defaults = ter_get_localized_defaults();
		if ( isset( $i18n_defaults[ $key ] ) ) {
			$default_value = $i18n_defaults[ $key ];
			// Use ter_get_translated to get the i18n translation.
			if ( function_exists( 'ter_get_translated' ) ) {
				return esc_html( ter_get_translated( $key, $default_value ) );
			}
			return esc_html( $default_value );
		}
	}

	// For non-translatable keys, use the labels map.
	$labels_map = array(
		// General settings.
		'general_settings'               => 'General Settings',
		'recipient_email'                => 'Internal recipient email',
		'send_customer_copy'             => 'Send copy to customer',
		'delete_uploads'                 => 'Delete uploads after sending',
		'temp_folder'                    => 'Temp folder name',
		'subject_prefix'                 => 'Subject prefix',

		// Email settings.
		'internal_email'                 => 'Internal Email (Admin)',
		'internal_subject'               => 'Subject Template',
		'internal_message'               => 'Message Template (HTML)',
		'customer_email'                 => 'Customer Email (Copy)',
		'customer_subject'               => 'Subject Template',
		'customer_message'               => 'Message Template (HTML)',
		'form_messages'                  => 'Form Messages',
		'success_message'                => 'Success Message',
		'failure_message'                => 'Failure Message',

		// Cost rates.
		'cost_rates'                     => 'Cost Calculation Rates',
		'rate_km'                        => 'Kilometer allowance (€/km)',
		'rate_passenger'                 => 'Passenger allowance (€/km/person)',

		// Placeholders.
		'placeholders'                   => 'Form Input Placeholders',
		'placeholder_iban'               => 'IBAN placeholder',
		'placeholder_bic'                => 'BIC placeholder',
		'placeholder_event'              => 'Event placeholder',
		'placeholder_organizer'          => 'Organizer placeholder',
		'placeholder_email'              => 'Email placeholder',
		'placeholder_trip_description'   => 'Trip description placeholder',
		'placeholder_trip_start'         => 'Trip start placeholder',
		'placeholder_trip_destination'   => 'Trip destination placeholder',
		'placeholder_ticket_start'       => 'Ticket start placeholder',
		'placeholder_ticket_destination' => 'Ticket destination placeholder',
		'placeholder_ticket_description' => 'Ticket description placeholder',

		// Labels & Messages.
		'labels_messages'                => 'Form Labels & Messages',
	);

	if ( isset( $labels_map[ $key ] ) ) {
		return esc_html( ter_get_translated( $key, $labels_map[ $key ] ) );
	}
	return $key;
}

/**
 * Render an email input field.
 *
 * @param array $args Field arguments; expects a "key" entry.
 * @return void
 */
function ter_field_email( $args ) {
	$key    = $args['key'];
	$config = get_option( 'ter_config', array() );
	$value  = isset( $config[ $key ] ) ? $config[ $key ] : '';
	echo '<input type="email" name="ter_config[' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '" class="regular-text">';
}

/**
 * Render a checkbox field.
 *
 * @param array $args Field arguments; expects a "key" entry.
 * @return void
 */
function ter_field_checkbox( $args ) {
	$key    = $args['key'];
	$config = get_option( 'ter_config', array() );
	$value  = ! empty( $config[ $key ] ) ? 1 : 0;
	echo '<input type="checkbox" name="ter_config[' . esc_attr( $key ) . ']" value="1" ' . checked( $value, 1, false ) . '>';
}

/**
 * Render a text input field.
 *
 * @param array $args Field arguments; expects a "key" entry.
 * @return void
 */
function ter_field_text( $args ) {
	$key    = $args['key'];
	$config = get_option( 'ter_config', array() );
	$value  = isset( $config[ $key ] ) ? $config[ $key ] : '';

	// Get the default value.
	$defaults = ter_get_localized_defaults();
	if ( ! isset( $defaults[ $key ] ) ) {
		$defaults = ter_get_default_config();
	}
	$default_value = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';

	// For translatable keys, show the translated version in the placeholder.
	// Include: label_*, button_*, message_*, error_*, info_*, email_*, placeholder_*,
	// and template keys: internal_subject, internal_message, customer_subject, customer_message, etc.
	$translatable_prefixes = array( 'label_', 'button_', 'message_', 'error_', 'info_', 'email_', 'placeholder_' );
	$template_keys         = array(
		'internal_subject',
		'internal_message',
		'customer_subject',
		'customer_message',
		'success_message',
		'failure_message',
		'subject_prefix',
	);

	$is_translatable = false;
	foreach ( $translatable_prefixes as $prefix ) {
		if ( strpos( $key, $prefix ) === 0 ) {
			$is_translatable = true;
			break;
		}
	}
	if ( ! $is_translatable && in_array( $key, $template_keys, true ) ) {
		$is_translatable = true;
	}

	// Apply translation if applicable.
	if ( $is_translatable && function_exists( 'ter_get_translated' ) ) {
		$placeholder = ter_get_translated( $key, $default_value );
	} else {
		$placeholder = $default_value;
	}

	echo '<input type="text" name="ter_config[' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( $placeholder ) . '" class="regular-text">';
}

/**
 * Render a number input field.
 *
 * @param array $args Field arguments; expects a "key" entry.
 * @return void
 */
function ter_field_number( $args ) {
	$key         = $args['key'];
	$config      = get_option( 'ter_config', array() );
	$value       = isset( $config[ $key ] ) ? floatval( $config[ $key ] ) : '';
	$defaults    = ter_get_default_config();
	$placeholder = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	echo '<input type="number" step="0.01" name="ter_config[' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( $placeholder ) . '" class="small-text">';
}

/**
 * Render a textarea field.
 *
 * @param array $args Field arguments; expects a "key" entry.
 * @return void
 */
function ter_field_textarea( $args ) {
	$key    = $args['key'];
	$config = get_option( 'ter_config', array() );
	$value  = isset( $config[ $key ] ) ? $config[ $key ] : '';

	// Get the default value.
	$defaults = ter_get_localized_defaults();
	if ( ! isset( $defaults[ $key ] ) ) {
		$defaults = ter_get_default_config();
	}
	$default_value = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';

	// For translatable keys, show the translated version in the placeholder.
	// Include: label_*, button_*, message_*, error_*, info_*, email_*, placeholder_*,
	// and template keys: internal_subject, internal_message, customer_subject, customer_message, etc.
	$translatable_prefixes = array( 'label_', 'button_', 'message_', 'error_', 'info_', 'email_', 'placeholder_' );
	$template_keys         = array(
		'internal_subject',
		'internal_message',
		'customer_subject',
		'customer_message',
		'success_message',
		'failure_message',
		'subject_prefix',
	);

	$is_translatable = false;
	foreach ( $translatable_prefixes as $prefix ) {
		if ( strpos( $key, $prefix ) === 0 ) {
			$is_translatable = true;
			break;
		}
	}
	if ( ! $is_translatable && in_array( $key, $template_keys, true ) ) {
		$is_translatable = true;
	}

	// Apply translation if applicable.
	if ( $is_translatable && function_exists( 'ter_get_translated' ) ) {
		$placeholder = ter_get_translated( $key, $default_value );
	} else {
		$placeholder = $default_value;
	}

	echo '<textarea name="ter_config[' . esc_attr( $key ) . ']" rows="2" class="large-text" placeholder="' . esc_attr( $placeholder ) . '">' . esc_textarea( $value ) . '</textarea>';
}

/**
 * Sanitize the submitted plugin configuration.
 *
 * @param mixed $input Raw submitted settings.
 * @return array Sanitized settings.
 */
function ter_sanitize_config( $input ) {
	if ( ! is_array( $input ) ) {
		$input = array();
	}

	$existing = get_option( 'ter_config', array() );
	$defaults = ter_get_default_config();

	// Also get i18n defaults to allow all translatable keys to be saved.
	$i18n_defaults = function_exists( 'ter_get_localized_defaults' ) ? ter_get_localized_defaults() : array();
	$all_defaults  = array_merge( $defaults, $i18n_defaults );

	$out = $existing;

	foreach ( $input as $key => $value ) {
		// Skip keys that are not in defaults (prevent invalid keys).
		if ( ! isset( $all_defaults[ $key ] ) ) {
			continue;
		}

		if ( 0 === strpos( $key, 'rate_' ) ) {
			// Rate fields (numeric).
			$float_val   = floatval( $value );
			$out[ $key ] = $float_val >= 0 ? (string) $float_val : ( isset( $all_defaults[ $key ] ) ? $all_defaults[ $key ] : '' );
		} elseif ( 'recipient_email' === $key || 'customer_email' === $key ) {
			// Only sanitize actual email fields as email.
			$out[ $key ] = sanitize_email( $value );
		} elseif ( 'send_customer_copy' === $key || 'delete_uploads_after_send' === $key ) {
			// Checkbox fields.
			$out[ $key ] = ! empty( $value ) ? 1 : 0;
		} elseif ( 0 === strpos( $key, 'message_' ) ||
			0 === strpos( $key, 'internal_' ) ||
			0 === strpos( $key, 'customer_' ) ||
			0 === strpos( $key, 'success_' ) ||
			0 === strpos( $key, 'failure_' ) ) {
			// Allow HTML in message templates and email templates.
			$out[ $key ] = wp_kses_post( $value );
		} else {
			// Labels, buttons, info, error and email labels are plain text.
			$out[ $key ] = sanitize_text_field( $value );
		}
	}

	return $out;
}

/**
 * Render the settings page.
 *
 * @return void
 */
function ter_render_settings_page_advanced() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$config      = get_option( 'ter_config', array() );
	$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only tab selection.

	$tabs = array(
		'general'      => esc_html( ter_get_translated( 'general_settings', 'General Settings' ) ),
		'email'        => esc_html( ter_get_translated( 'internal_email', 'Internal Email (Admin)' ) ),
		'rates'        => esc_html( ter_get_translated( 'cost_rates', 'Cost Calculation Rates' ) ),
		'placeholders' => esc_html( ter_get_translated( 'placeholders', 'Form Input Placeholders' ) ),
		'labels'       => esc_html( ter_get_translated( 'labels_messages', 'Form Labels & Messages' ) ),
	);

	?>
	<div class="wrap ter-settings-wrap">
		<h1><?php echo esc_html__( 'Travel Expense Report — Settings', 'travel-expense-report' ); ?></h1>

		<!-- Tab Navigation -->
		<nav class="nav-tab-wrapper" style="border-bottom:1px solid #ccc;margin-bottom:20px;">
			<?php foreach ( $tabs as $tab_key => $tab_label ) : ?>
				<a href="?page=ter-settings&tab=<?php echo esc_attr( $tab_key ); ?>"
					class="nav-tab <?php echo ( $current_tab === $tab_key ) ? 'nav-tab-active' : ''; ?>"
					style="padding:10px 15px;text-decoration:none;border:1px solid #ccc;border-bottom:none;margin-bottom:-1px;background:<?php echo ( $current_tab === $tab_key ) ? '#fff' : '#e7e7e7'; ?>;">
					<?php echo esc_html( $tab_label ); ?>
				</a>
			<?php endforeach; ?>
		</nav>

		<div class="ter-settings-columns" style="display:flex;gap:24px;align-items:flex-start;">
			<div class="ter-settings-left" style="flex:1;min-width:320px;">
				<form method="post" action="options.php">
					<?php settings_fields( 'ter_config_group' ); ?>

					<!-- CUSTOM RENDER - nicht do_settings_sections() -->
					<?php ter_render_current_tab_fields( $current_tab ); ?>

					<?php submit_button( esc_html__( 'Save', 'travel-expense-report' ) ); ?>
				</form>
			</div>

			<div class="ter-settings-right" style="width:360px;background:#fff;border:1px solid #e3e3e3;padding:16px;border-radius:6px;">
				<h2 style="margin-top:0;font-size:18px;"><?php echo esc_html__( 'Quick tests', 'travel-expense-report' ); ?></h2>
				<p class="description"><?php echo esc_html__( 'Test the email configuration without submitting the form.', 'travel-expense-report' ); ?></p>

				<label for="ter_test_email"><strong><?php echo esc_html__( 'Test recipient (optional)', 'travel-expense-report' ); ?></strong></label>
				<input id="ter_test_email" type="email" placeholder="<?php echo esc_attr__( 'empty = configured recipient', 'travel-expense-report' ); ?>" value="<?php echo esc_attr( $config['recipient_email'] ?? '' ); ?>" style="width:100%;margin-top:6px;padding:8px;border:1px solid #ccc;border-radius:4px;">

				<div style="margin-top:12px;display:flex;gap:8px;">
					<button id="ter_send_test_email_btn" class="button button-primary"><?php echo esc_html__( 'Send test email', 'travel-expense-report' ); ?></button>
					<span id="ter_test_email_status" style="line-height:32px;color:#666;font-size:13px;"></span>
				</div>
				<hr style="margin:14px 10px;">
				<h3 style="margin-bottom:6px;font-size:15px;"><?php echo esc_html__( 'Notes', 'travel-expense-report' ); ?></h3>
				<ul style="font-size:13px;line-height:1.4;margin:0;padding-left:18px;color:#444;">
					<li><?php echo esc_html__( 'The test email uses the configured recipient if no test recipient is provided.', 'travel-expense-report' ); ?></li>
					<li><?php echo esc_html__( 'Make sure your webserver can send emails (SMTP/Sendmail).', 'travel-expense-report' ); ?></li>
				</ul>
			</div>
		</div>
	</div>

	<script>
	(function(){
		const btn = document.getElementById('ter_send_test_email_btn');
		const status = document.getElementById('ter_test_email_status');
		const input = document.getElementById('ter_test_email');
		btn.addEventListener('click', function(e){
			e.preventDefault();
			status.textContent = '<?php echo esc_js( __( 'Sending test email...', 'travel-expense-report' ) ); ?>';
			btn.disabled = true;
			const form = new FormData();
			form.append('action','ter_send_test_email');
			form.append('nonce','<?php echo esc_js( wp_create_nonce( 'ter_test_email' ) ); ?>');
			if (input && input.value) form.append('test_email', input.value);
			fetch(ajaxurl, { method:'POST', body: form })
			.then(r=>r.json())
			.then(j=>{
				if (j.success) {
					status.style.color = 'green';
					status.textContent = j.data;
				} else {
					status.style.color = 'red';
					status.textContent = j.data || '<?php echo esc_js( __( 'Error', 'travel-expense-report' ) ); ?>';
				}
			})
			.catch(()=>{
				status.style.color = 'red';
				status.textContent = '<?php echo esc_js( __( 'Network error', 'travel-expense-report' ) ); ?>';
			})
			.finally(()=>{ btn.disabled = false; setTimeout(()=>status.textContent='',5000); });
		});
	})();
	</script>
	<?php
}

/**
 * Render the fields for the current tab with proper translated labels.
 *
 * @param string $current_tab Active tab key.
 * @return void
 */
function ter_render_current_tab_fields( $current_tab ) {
	$config = get_option( 'ter_config', array() );

	echo '<table class="form-table">';

	switch ( $current_tab ) {
		case 'general':
			echo '<h2>' . esc_html( ter_get_translated( 'general_settings', 'General Settings' ) ) . '</h2>';
			echo '<p>' . esc_html__( 'Configure recipient, folders and behavior of the travel expense plugin.', 'travel-expense-report' ) . '</p>';

			// Section 1: Email Configuration.
			echo '<tr class="ter-section-header ter-section-email"><td colspan="2"><h3>📧 ' . esc_html( ter_get_translated( 'email_configuration', 'Email Configuration' ) ) . '</h3></td></tr>';
			ter_render_field( 'recipient_email', 'ter_field_email', array( 'key' => 'recipient_email' ) );
			ter_render_field( 'send_customer_copy', 'ter_field_checkbox', array( 'key' => 'send_customer_copy' ) );

			// Section 2: Upload Settings.
			echo '<tr class="ter-section-header ter-section-uploads"><td colspan="2"><h3>💾 ' . esc_html( ter_get_translated( 'upload_settings', 'Upload Settings' ) ) . '</h3></td></tr>';
			ter_render_field( 'delete_uploads', 'ter_field_checkbox', array( 'key' => 'delete_uploads_after_send' ) );
			ter_render_field( 'temp_folder', 'ter_field_text', array( 'key' => 'temp_folder' ) );

			// Section 3: Email Templates.
			echo '<tr class="ter-section-header ter-section-templates"><td colspan="2"><h3>✉️ ' . esc_html( ter_get_translated( 'email_subject_prefix', 'Email Subject Prefix' ) ) . '</h3></td></tr>';
			ter_render_field( 'subject_prefix', 'ter_field_text', array( 'key' => 'subject_prefix' ) );
			break;

		case 'email':
			echo '<h2>' . esc_html( ter_get_translated( 'internal_email', 'Internal Email (Admin)' ) ) . '</h2>';
			echo '<p>' . esc_html__( 'Email sent to internal recipient', 'travel-expense-report' ) . '</p>';
			echo '<tr class="ter-section-header ter-section-email-internal"><td colspan="2"><h3>🔵 ' . esc_html( ter_get_translated( 'internal_email', 'Internal Email (Admin)' ) ) . '</h3></td></tr>';
			ter_render_field( 'internal_subject', 'ter_field_textarea', array( 'key' => 'internal_subject' ) );
			ter_render_field( 'internal_message', 'ter_field_textarea', array( 'key' => 'internal_message' ) );

			echo '<tr class="ter-section-header ter-section-email-customer"><td colspan="2"><h3>🟢 ' . esc_html( ter_get_translated( 'customer_email', 'Customer Email (Copy)' ) ) . '</h3></td></tr>';
			echo '<p style="padding-left:0;margin-top:5px;">' . esc_html__( 'Email sent to customer when enabled', 'travel-expense-report' ) . '</p>';
			ter_render_field( 'customer_subject', 'ter_field_textarea', array( 'key' => 'customer_subject' ) );
			ter_render_field( 'customer_message', 'ter_field_textarea', array( 'key' => 'customer_message' ) );

			echo '<tr class="ter-section-header ter-section-email-messages"><td colspan="2"><h3>🟠 ' . esc_html( ter_get_translated( 'form_messages', 'Form Messages' ) ) . '</h3></td></tr>';
			echo '<p style="padding-left:0;margin-top:5px;">' . esc_html__( 'Messages displayed on frontend after form submission', 'travel-expense-report' ) . '</p>';
			ter_render_field( 'success_message', 'ter_field_textarea', array( 'key' => 'success_message' ) );
			ter_render_field( 'failure_message', 'ter_field_textarea', array( 'key' => 'failure_message' ) );
			break;

		case 'rates':
			echo '<h2>' . esc_html( ter_get_translated( 'cost_rates', 'Cost Calculation Rates' ) ) . '</h2>';
			echo '<p>' . esc_html__( 'Configure the kilometer and passenger allowances for trip cost calculation.', 'travel-expense-report' ) . '</p>';

			echo '<tr class="ter-section-header ter-section-rates"><td colspan="2"><h3>💵 ' . esc_html( ter_get_translated( 'cost_allowances', 'Cost Allowances' ) ) . '</h3></td></tr>';
			ter_render_field( 'rate_km', 'ter_field_number', array( 'key' => 'rate_km_allowance' ) );
			ter_render_field( 'rate_passenger', 'ter_field_number', array( 'key' => 'rate_passenger_allowance' ) );
			break;

		case 'placeholders':
			echo '<h2>' . esc_html( ter_get_translated( 'placeholders', 'Form Input Placeholders' ) ) . '</h2>';
			echo '<p>' . esc_html__( 'Customize the placeholder text shown in form input fields', 'travel-expense-report' ) . '</p>';

			// Section 1: Personal Data Placeholders.
			echo '<tr class="ter-section-header ter-section-personal"><td colspan="2"><h3>👤 ' . esc_html( ter_get_translated( 'personal_data_placeholders', 'Personal Data Placeholders' ) ) . '</h3></td></tr>';
			$personal_placeholders = array( 'placeholder_iban', 'placeholder_bic', 'placeholder_email', 'placeholder_event', 'placeholder_organizer' );
			foreach ( $personal_placeholders as $key ) {
				ter_render_field( $key, 'ter_field_textarea', array( 'key' => $key ) );
			}

			// Section 2: Trip Placeholders.
			echo '<tr class="ter-section-header ter-section-trips"><td colspan="2"><h3>🚗 ' . esc_html( ter_get_translated( 'trip_placeholders', 'Trip Placeholders' ) ) . '</h3></td></tr>';
			$trip_placeholders = array( 'placeholder_trip_description', 'placeholder_trip_start', 'placeholder_trip_destination' );
			foreach ( $trip_placeholders as $key ) {
				ter_render_field( $key, 'ter_field_textarea', array( 'key' => $key ) );
			}

			// Section 3: Train Ticket Placeholders.
			echo '<tr class="ter-section-header ter-section-tickets"><td colspan="2"><h3>🚆 ' . esc_html( ter_get_translated( 'train_ticket_placeholders', 'Train Ticket Placeholders' ) ) . '</h3></td></tr>';
			$ticket_placeholders = array( 'placeholder_ticket_start', 'placeholder_ticket_destination', 'placeholder_ticket_description' );
			foreach ( $ticket_placeholders as $key ) {
				ter_render_field( $key, 'ter_field_textarea', array( 'key' => $key ) );
			}
			break;

		case 'labels':
			echo '<h2>' . esc_html( ter_get_translated( 'labels_messages', 'Form Labels & Messages' ) ) . '</h2>';
			echo '<p>' . esc_html__( 'Customize all form labels and messages', 'travel-expense-report' ) . '</p>';

			// Get all defaults for fallback.
			$i18n_defaults   = ter_get_localized_defaults();
			$config_defaults = ter_get_default_config();
			$all_defaults    = array_merge( $config_defaults, $i18n_defaults );

			// Section 1: Personal Details.
			echo '<tr class="ter-section-header ter-section-personal"><td colspan="2"><h3>📋 ' . esc_html( ter_get_translated( 'personal_details_section', 'Personal Details' ) ) . '</h3></td></tr>';
			$personal_keys = array( 'info_submit_per_event', 'email_personal_details', 'label_first_name', 'label_last_name', 'label_iban', 'label_bic', 'label_event', 'label_organizer', 'label_cost_center', 'label_customer_email' );
			foreach ( $personal_keys as $key ) {
				if ( isset( $all_defaults[ $key ] ) ) {
					ter_render_field( $key, 'ter_field_textarea', array( 'key' => $key ) );
				}
			}

			// Section 2: Advance Payments / Purchases.
			echo '<tr class="ter-section-header ter-section-goods"><td colspan="2"><h3>💰 ' . esc_html( ter_get_translated( 'advance_payments_section', 'Advance Payments / Purchases' ) ) . '</h3></td></tr>';
			$goods_keys = array( 'label_goods_section', 'info_receipts_required', 'label_description', 'label_amount', 'label_receipt', 'label_action', 'button_add_goods', 'button_upload_receipt' );
			foreach ( $goods_keys as $key ) {
				if ( isset( $all_defaults[ $key ] ) ) {
					ter_render_field( $key, 'ter_field_textarea', array( 'key' => $key ) );
				}
			}

			// Section 3: Trips.
			echo '<tr class="ter-section-header ter-section-trips"><td colspan="2"><h3>🚗 ' . esc_html( ter_get_translated( 'trips_section', 'Trips' ) ) . '</h3></td></tr>';
			$trips_keys = array( 'label_trips_section', 'label_kilometers', 'label_passengers', 'label_start', 'label_destination', 'button_add_trip' );
			foreach ( $trips_keys as $key ) {
				if ( isset( $all_defaults[ $key ] ) ) {
					ter_render_field( $key, 'ter_field_textarea', array( 'key' => $key ) );
				}
			}

			// Section 4: Train Tickets.
			echo '<tr class="ter-section-header ter-section-tickets"><td colspan="2"><h3>🚆 ' . esc_html( ter_get_translated( 'train_tickets_section', 'Train Tickets' ) ) . '</h3></td></tr>';
			$tickets_keys = array( 'label_tickets_section', 'label_cost', 'label_ticket', 'button_add_ticket' );
			foreach ( $tickets_keys as $key ) {
				if ( isset( $all_defaults[ $key ] ) ) {
					ter_render_field( $key, 'ter_field_textarea', array( 'key' => $key ) );
				}
			}

			// Section 5: Messages & Info.
			echo '<tr class="ter-section-header ter-section-messages"><td colspan="2"><h3>ℹ️ ' . esc_html( ter_get_translated( 'messages_info_section', 'Messages & Info' ) ) . '</h3></td></tr>';
			$messages_keys = array( 'info_cost_summary', 'info_km_allowance', 'info_passenger_allowance', 'info_trip_costs', 'info_ticket_costs', 'info_total_costs', 'button_send_email', 'message_sending', 'message_success', 'message_error' );
			foreach ( $messages_keys as $key ) {
				if ( isset( $all_defaults[ $key ] ) ) {
					ter_render_field( $key, 'ter_field_textarea', array( 'key' => $key ) );
				}
			}

			// Section 6: Error Messages.
			echo '<tr class="ter-section-header ter-section-errors"><td colspan="2"><h3>⚠️ ' . esc_html( ter_get_translated( 'error_messages_section', 'Error Messages' ) ) . '</h3></td></tr>';
			$error_keys = array( 'error_required_fields', 'error_invalid_iban', 'error_invalid_bic', 'error_invalid_email', 'error_no_receipts', 'error_no_tickets', 'error_loading' );
			foreach ( $error_keys as $key ) {
				if ( isset( $all_defaults[ $key ] ) ) {
					ter_render_field( $key, 'ter_field_textarea', array( 'key' => $key ) );
				}
			}

			// Section 7: Email Labels.
			echo '<tr class="ter-section-header ter-section-email"><td colspan="2"><h3>📧 ' . esc_html( ter_get_translated( 'email_labels_section', 'Email Labels' ) ) . '</h3></td></tr>';
			$email_keys = array( 'email_name', 'email_iban', 'email_bic', 'email_event', 'email_organizer', 'email_advance_payments', 'email_trips', 'email_route', 'email_total_trip_costs', 'email_tickets', 'email_status', 'email_ticket_badge', 'email_provided', 'email_missing', 'email_title' );
			foreach ( $email_keys as $key ) {
				if ( isset( $all_defaults[ $key ] ) ) {
					ter_render_field( $key, 'ter_field_textarea', array( 'key' => $key ) );
				}
			}
			break;
	}

	echo '</table>';
}

/**
 * Render a single field with translated label.
 *
 * @param string   $label_key Label key.
 * @param callable $field_callback Field renderer.
 * @param array    $args Field arguments.
 * @return void
 */
function ter_render_field( $label_key, $field_callback, $args ) {
	$label = ter_get_field_label( $label_key );

	echo '<tr><th scope="row"><label>' . esc_html( $label ) . '</label></th><td>';
	call_user_func( $field_callback, $args );
	echo '</td></tr>';
}

/**
 * Output CSS for section styling in admin forms
 * Called during admin settings page rendering
 */
function ter_output_email_section_styles() {
	?>
	<style>
		/* Section headers - table row styling */
		.ter-section-header {
			border-top: 3px solid #e0e0e0 !important;
			background-color: #fafafa !important;
		}

		.ter-section-header td {
			padding: 15px 10px !important;
			border-left: 5px solid #ccc !important;
		}

		.ter-section-header h3 {
			margin: 0;
			font-size: 15px;
			font-weight: 600;
			color: #333;
		}

		/* Email sections - specific header colors */
		.ter-section-email td {
			border-left-color: #4f46e5 !important;
			background-color: #eef2ff !important;
		}

		.ter-section-uploads td {
			border-left-color: #0d9488 !important;
			background-color: #f0fdfa !important;
		}

		.ter-section-templates td {
			border-left-color: #d97706 !important;
			background-color: #fffbeb !important;
		}

		.ter-section-email-internal td {
			border-left-color: #0073aa !important;
			background-color: #f0f6fc !important;
		}

		.ter-section-email-customer td {
			border-left-color: #7cb342 !important;
			background-color: #f1f8e9 !important;
		}

		.ter-section-email-messages td {
			border-left-color: #ffa726 !important;
			background-color: #fff3e0 !important;
		}

		.ter-section-rates td {
			border-left-color: #ec4899 !important;
			background-color: #fdf2f8 !important;
		}

		.ter-section-personal td {
			border-left-color: #0073aa !important;
			background-color: #f0f6fc !important;
		}

		.ter-section-trips td {
			border-left-color: #8b5cf6 !important;
			background-color: #f3e8ff !important;
		}

		.ter-section-tickets td {
			border-left-color: #f97316 !important;
			background-color: #ffedd5 !important;
		}

		.ter-section-goods td {
			border-left-color: #7cb342 !important;
			background-color: #f1f8e9 !important;
		}

		.ter-section-messages td {
			border-left-color: #06b6d4 !important;
			background-color: #ecf8ff !important;
		}

		.ter-section-errors td {
			border-left-color: #dc2626 !important;
			background-color: #fee2e2 !important;
		}

		/* Fields following section headers get special styling */
		.ter-section-header + tr td {
			border-top: 1px solid #e0e0e0;
		}

		.form-table tbody tr:first-child td {
			border-top: none;
		}
	</style>
	<?php
}
add_action( 'admin_head', 'ter_output_email_section_styles' );

