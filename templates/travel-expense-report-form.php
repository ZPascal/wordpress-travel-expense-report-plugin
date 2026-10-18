<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Travel Expense Report Form Template
 */
?>
<div class="ter-container">
	<div class="ter-info-box">
		<p><?php echo esc_html( ter_get_config( 'info_submit_per_event', '⚠️ Important note: Please submit one form per event or cost center.' ) ); ?></p>
	</div>

	<h2><?php echo esc_html( ter_get_config( 'email_personal_details', 'Personal details' ) ); ?></h2>
	<div class="ter-personal-data">
		<div class="ter-form-row">
			<div class="ter-form-field">
				<label for="ter_firstname"><?php echo esc_html( ter_get_config( 'label_first_name', 'First name' ) ); ?> *</label>
				<input type="text" id="ter_firstname" name="firstname" required>
			</div>
			<div class="ter-form-field">
				<label for="ter_lastname"><?php echo esc_html( ter_get_config( 'label_last_name', 'Last name' ) ); ?> *</label>
				<input type="text" id="ter_lastname" name="lastname" required>
			</div>
		</div>
		<div class="ter-form-row">
			<div class="ter-form-field">
				<label for="ter_iban"><?php echo esc_html( ter_get_config( 'label_iban', 'IBAN' ) ); ?> *</label>
				<input type="text" id="ter_iban" name="iban" value="<?php echo esc_attr( ter_get_config( 'placeholder_iban', 'DE89 3704 0044 0532 0130 00' ) ); ?>" placeholder="<?php echo esc_attr( ter_get_config( 'placeholder_iban', 'DE89 3704 0044 0532 0130 00' ) ); ?>" required pattern="[A-Z]{2}[0-9]{2}[A-Z0-9]+">
			</div>
			<div class="ter-form-field">
				<label for="ter_bic"><?php echo esc_html( ter_get_config( 'label_bic', 'BIC' ) ); ?></label>
				<input type="text" id="ter_bic" name="bic" value="<?php echo esc_attr( ter_get_config( 'placeholder_bic', 'COBADEFFXXX' ) ); ?>" placeholder="<?php echo esc_attr( ter_get_config( 'placeholder_bic', 'COBADEFFXXX' ) ); ?>" pattern="[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?">
			</div>
		</div>
		<div class="ter-form-row">
			<div class="ter-form-field">
				<label for="ter_event"><?php echo esc_html( ter_get_config( 'label_event', 'Event / Activity' ) ); ?> *</label>
				<input type="text" id="ter_event" name="event" value="<?php echo esc_attr( ter_get_config( 'placeholder_event', 'e.g. Summer Camp 2025, January group meeting' ) ); ?>" placeholder="<?php echo esc_attr( ter_get_config( 'placeholder_event', 'e.g. Summer Camp 2025, January group meeting' ) ); ?>" required>
			</div>
			<div class="ter-form-field">
				<label for="ter_organizer"><?php echo esc_html( ter_get_config( 'label_organizer', 'Organizer' ) ); ?></label>
				<input type="text" id="ter_organizer" name="organizer" value="<?php echo esc_attr( ter_get_config( 'placeholder_organizer', 'e.g. DPSG Group 1, District Youth Office' ) ); ?>" placeholder="<?php echo esc_attr( ter_get_config( 'placeholder_organizer', 'e.g. DPSG Group 1, District Youth Office' ) ); ?>">
			</div>
		</div>
		<div class="ter-form-row">
			<div class="ter-form-field">
				<label for="ter_cost_center"><?php echo esc_html( ter_get_config( 'label_cost_center', 'Cost center number' ) ); ?></label>
				<input type="text" id="ter_cost_center" name="cost_center" value="<?php echo esc_attr( ter_get_config( 'placeholder_cost_center', '' ) ); ?>" placeholder="<?php echo esc_attr( ter_get_config( 'placeholder_cost_center', '' ) ); ?>">
			</div>
			<div class="ter-form-field">
				<label for="ter_customer_email"><?php echo esc_html( ter_get_config( 'label_customer_email', 'Email address (to receive a copy)' ) ); ?></label>
				<input type="email" id="ter_customer_email" name="customer_email" value="<?php echo esc_attr( ter_get_config( 'placeholder_email', 'name@example.com' ) ); ?>" placeholder="<?php echo esc_attr( ter_get_config( 'placeholder_email', 'name@example.com' ) ); ?>">
			</div>
		</div>
	</div>

	<h2><?php echo esc_html( ter_get_config( 'label_goods_section', 'Advance payments / purchases' ) ); ?></h2>

	<div class="ter-info-box" style="background-color: #e3f2fd; border-color: #2196f3; margin-bottom: 15px;">
		<p style="color: #1565c0;"><?php echo esc_html( ter_get_config( 'info_receipts_required', 'ℹ️ Note: Only advance payments and purchases with an invoice, receipt or self-issued slip are accepted. Please upload a receipt for every filled entry.' ) ); ?></p>
	</div>

	<table class="ter-table" id="ter_items">
		<thead>
		<tr>
			<th><?php echo esc_html( ter_get_config( 'label_description', 'Description' ) ); ?></th>
			<th><?php echo esc_html( ter_get_config( 'label_amount', 'Amount (€)' ) ); ?></th>
			<th><?php echo esc_html( ter_get_config( 'label_receipt', 'Receipt' ) ); ?> *</th>
			<th><?php echo esc_html( ter_get_config( 'label_action', 'Action' ) ); ?></th>
		</tr>
		</thead>
		<tbody id="ter_tr_goods">
		<tr data-row-id="0">
			<td><input type="text" name="artikel[]" placeholder="<?php echo esc_attr( ter_get_config( 'label_description', 'Description' ) ); ?>"></td>
			<td><input type="number" step="0.01" name="betrag[]" class="betrag" value="0" min="0"></td>
			<td>
				<div class="upload-btn-wrapper">
					<button class="upload-btn" type="button"><?php echo esc_html( ter_get_config( 'button_upload_receipt', 'Upload receipt' ) ); ?></button>
					<input type="file" class="receipt-upload" accept="image/*,.pdf" data-row-id="0">
				</div>
				<div class="receipt-info" id="receipt-info-0"></div>
			</td>
			<td><button class="ter-btn removeRow">−</button></td>
		</tr>
		</tbody>
	</table>

	<button class="ter-btn" id="addGoodsRow"><?php echo esc_html( ter_get_config( 'button_add_goods', '+ Add purchase' ) ); ?></button>

	<h2><?php echo esc_html( ter_get_config( 'label_trips_section', 'Trips' ) ); ?></h2>

	<!-- Desktop/Tablet Tabelle -->
	<table class="ter-table ter-table-responsive" id="ter_items_km">
		<thead>
		<tr>
			<th><?php echo esc_html( ter_get_config( 'label_kilometers', 'Kilometers' ) ); ?></th>
			<th><?php echo esc_html( ter_get_config( 'label_passengers', 'Passengers' ) ); ?></th>
			<th><?php echo esc_html( ter_get_config( 'label_description', 'Description' ) ); ?></th>
			<th><?php echo esc_html( ter_get_config( 'label_start', 'Start' ) ); ?></th>
			<th><?php echo esc_html( ter_get_config( 'label_destination', 'Destination' ) ); ?></th>
			<th><?php echo esc_html( ter_get_config( 'label_action', 'Action' ) ); ?></th>
		</tr>
		</thead>
		<tbody id="ter_tr_kilometer">
		<tr data-trip-id="0">
			<td><input type="number" class="km_input" value="0" min="0"></td>
			<td><input type="number" class="km_passengers" value="0" min="0" max="20" title="Number of passengers (0.01€ per km per passenger)"></td>
			<td><input type="text" class="km_description" value="<?php echo esc_attr( ter_get_config( 'placeholder_trip_description', 'Trip description' ) ); ?>" placeholder="<?php echo esc_attr( ter_get_config( 'placeholder_trip_description', 'Trip description' ) ); ?>"></td>
			<td><input type="text" class="km_start" value="<?php echo esc_attr( ter_get_config( 'placeholder_trip_start', 'Trip start' ) ); ?>" placeholder="<?php echo esc_attr( ter_get_config( 'placeholder_trip_start', 'Trip start' ) ); ?>"></td>
			<td><input type="text" class="km_target" value="<?php echo esc_attr( ter_get_config( 'placeholder_trip_destination', 'Trip destination' ) ); ?>" placeholder="<?php echo esc_attr( ter_get_config( 'placeholder_trip_destination', 'Trip destination' ) ); ?>"></td>
			<td><button class="ter-btn removeKmRow" data-trip-id="0">−</button></td>
		</tr>
		</tbody>
	</table>

	<!-- Mobile Karten -->
	<div class="ter-trip-cards" id="ter_trip_cards">
		<div class="ter-trip-card" data-trip-id="0">
			<div class="trip-field">
				<label class="trip-label"><?php echo esc_html( ter_get_config( 'label_kilometers', 'Kilometers' ) ); ?></label>
				<input type="number" class="km_input" value="0" min="0">
			</div>
			<div class="trip-field">
				<label class="trip-label"><?php echo esc_html( ter_get_config( 'label_passengers', 'Passengers' ) ); ?> (<?php /* translators: %.2f: allowance in euros per kilometre per passenger. */ printf( esc_html__( '%.2f € per km per passenger', 'travel-expense-report' ), floatval( ter_get_config( 'rate_passenger_allowance', '0.01' ) ) ); ?>)</label>
				<input type="number" class="km_passengers" value="0" min="0" max="20">
			</div>
			<div class="trip-field">
				<label class="trip-label"><?php echo esc_html( ter_get_config( 'label_description', 'Description' ) ); ?></label>
				<input type="text" class="km_description" value="<?php echo esc_attr( ter_get_config( 'placeholder_trip_description', 'Trip description' ) ); ?>" placeholder="<?php echo esc_attr( ter_get_config( 'placeholder_trip_description', 'Trip description' ) ); ?>">
			</div>
			<div class="trip-field">
				<label class="trip-label"><?php echo esc_html( ter_get_config( 'label_start', 'Start' ) ); ?></label>
				<input type="text" class="km_start" value="<?php echo esc_attr( ter_get_config( 'placeholder_trip_start', 'Trip start' ) ); ?>" placeholder="<?php echo esc_attr( ter_get_config( 'placeholder_trip_start', 'Trip start' ) ); ?>">
			</div>
			<div class="trip-field">
				<label class="trip-label"><?php echo esc_html( ter_get_config( 'label_destination', 'Destination' ) ); ?></label>
				<input type="text" class="km_target" value="<?php echo esc_attr( ter_get_config( 'placeholder_trip_destination', 'Trip destination' ) ); ?>" placeholder="<?php echo esc_attr( ter_get_config( 'placeholder_trip_destination', 'Trip destination' ) ); ?>">
			</div>
			<div class="trip-actions">
				<button class="ter-btn removeKmRow" data-trip-id="0">Remove</button>
			</div>
		</div>
	</div>

	<button class="ter-btn" id="addKilometerRow"><?php echo esc_html( ter_get_config( 'button_add_trip', '+ Add trip' ) ); ?></button>

	<h3><?php echo esc_html( ter_get_config( 'label_tickets_section', 'Train tickets' ) ); ?></h3>

	<!-- Desktop/Tablet Tabelle -->
	<table class="ter-table ter-table-responsive" id="ter_items_tickets">
		<thead>
		<tr>
			<th><?php echo esc_html( ter_get_config( 'label_start', 'Start' ) ); ?></th>
			<th><?php echo esc_html( ter_get_config( 'label_destination', 'Destination' ) ); ?></th>
			<th><?php echo esc_html( ter_get_config( 'label_description', 'Description' ) ); ?></th>
			<th><?php echo esc_html( ter_get_config( 'label_cost', 'Cost (€)' ) ); ?></th>
			<th><?php echo esc_html( ter_get_config( 'label_ticket', 'Ticket' ) ); ?> *</th>
			<th><?php echo esc_html( ter_get_config( 'label_action', 'Action' ) ); ?></th>
		</tr>
		</thead>
		<tbody id="ter_tr_tickets">
		<tr data-ticket-id="0">
			<td><input type="text" class="ticket_start" value="<?php echo esc_attr( ter_get_config( 'placeholder_ticket_start', 'Start' ) ); ?>" placeholder="<?php echo esc_attr( ter_get_config( 'placeholder_ticket_start', 'Start' ) ); ?>"></td>
			<td><input type="text" class="ticket_target" value="<?php echo esc_attr( ter_get_config( 'placeholder_ticket_destination', 'Destination' ) ); ?>" placeholder="<?php echo esc_attr( ter_get_config( 'placeholder_ticket_destination', 'Destination' ) ); ?>"></td>
			<td><input type="text" class="ticket_description" value="<?php echo esc_attr( ter_get_config( 'placeholder_ticket_description', 'Description' ) ); ?>" placeholder="<?php echo esc_attr( ter_get_config( 'placeholder_ticket_description', 'Description' ) ); ?>"></td>
			<td><input type="number" step="0.01" class="ticket_cost" value="0" min="0"></td>
			<td>
				<div class="upload-btn-wrapper">
					<button class="upload-btn" type="button"><?php echo esc_html( ter_get_config( 'button_upload_receipt', 'Upload ticket' ) ); ?></button>
					<input type="file" class="ticket-upload" accept="image/*,.pdf" data-ticket-id="0">
				</div>
				<div class="ticket-info" id="ticket-info-0"></div>
			</td>
			<td><button class="ter-btn removeTicketRow" data-ticket-id="0">−</button></td>
		</tr>
		</tbody>
	</table>

	<!-- Mobile Karten -->
	<div class="ter-trip-cards" id="ter_ticket_cards">
		<div class="ter-trip-card" data-ticket-id="0">
			<div class="trip-field">
				<label class="trip-label"><?php echo esc_html( ter_get_config( 'label_start', 'Start' ) ); ?></label>
				<input type="text" class="ticket_start" value="<?php echo esc_attr( ter_get_config( 'placeholder_ticket_start', 'Start' ) ); ?>" placeholder="<?php echo esc_attr( ter_get_config( 'placeholder_ticket_start', 'Start' ) ); ?>">
			</div>
			<div class="trip-field">
				<label class="trip-label"><?php echo esc_html( ter_get_config( 'label_destination', 'Destination' ) ); ?></label>
				<input type="text" class="ticket_target" value="<?php echo esc_attr( ter_get_config( 'placeholder_ticket_destination', 'Destination' ) ); ?>" placeholder="<?php echo esc_attr( ter_get_config( 'placeholder_ticket_destination', 'Destination' ) ); ?>">
			</div>
			<div class="trip-field">
				<label class="trip-label"><?php echo esc_html( ter_get_config( 'label_description', 'Description' ) ); ?></label>
				<input type="text" class="ticket_description" value="<?php echo esc_attr( ter_get_config( 'placeholder_ticket_description', 'Description' ) ); ?>" placeholder="<?php echo esc_attr( ter_get_config( 'placeholder_ticket_description', 'Description' ) ); ?>">
			</div>
			<div class="trip-field">
				<label class="trip-label"><?php echo esc_html( ter_get_config( 'label_cost', 'Cost (€)' ) ); ?></label>
				<input type="number" step="0.01" class="ticket_cost" value="0" min="0">
			</div>
			<div class="trip-field">
				<label class="trip-label"><?php echo esc_html( ter_get_config( 'label_ticket', 'Ticket' ) ); ?></label>
				<div class="upload-btn-wrapper">
					<button class="upload-btn" type="button"><?php echo esc_html( ter_get_config( 'button_upload_receipt', 'Upload ticket' ) ); ?></button>
					<input type="file" class="ticket-upload" accept="image/*,.pdf" data-ticket-id="0">
				</div>
				<div class="ticket-info" id="ticket-info-0-mobile"></div>
			</div>
			<div class="trip-actions">
				<button class="ter-btn removeTicketRow" data-ticket-id="0">Remove</button>
			</div>
		</div>
	</div>

	<button class="ter-btn" id="addTicketRow"><?php echo esc_html( ter_get_config( 'button_add_ticket', '+ Add train ticket' ) ); ?></button>

	<div class="ter-summary">
		<p>
			<strong><?php echo esc_html( ter_get_config( 'info_cost_summary', '📊 Cost summary:' ) ); ?></strong><br>
			<br>
			<strong><?php echo esc_html( ter_get_config( 'label_goods_section', 'Advance payments / purchases' ) ); ?> <span id="goods_total">0.00</span> €</strong><br>
			<br>
			<strong><?php echo esc_html( ter_get_config( 'label_trips_section', 'Trips' ) ); ?>:</strong><br>
			- <?php echo esc_html( ter_get_km_allowance_message() ); ?><br>
			- <?php echo esc_html( ter_get_passenger_allowance_message() ); ?><br>
			<strong><?php echo esc_html( ter_get_config( 'info_trip_costs', 'Total trip costs:' ) ); ?> <span id="km_total">0.00</span> €</strong><br>
			<br>
			<strong><?php echo esc_html( ter_get_config( 'info_ticket_costs', 'Total train tickets:' ) ); ?> <span id="ticket_total">0.00</span> €</strong>
		</p>
	</div>

	<hr>

	<h2><?php echo esc_html( ter_get_config( 'info_total_costs', 'Total costs:' ) ); ?> <span id="gesamt">0.00</span> €</h2>

	<button class="ter-btn ter-submit" id="sendEmail"><?php echo esc_html( ter_get_config( 'button_send_email', 'Send by email' ) ); ?></button>
	<div id="email_message" style="margin-top: 10px;"></div>
</div>
