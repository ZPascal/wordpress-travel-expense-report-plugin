<?php
/**
 * Travel Expense Report Email / PDF Template
 *
 * @package TravelExpenseReport
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Ensure variables are defined when this template is included.
$firstname         = isset( $firstname ) ? $firstname : '';
$lastname          = isset( $lastname ) ? $lastname : '';
$iban              = isset( $iban ) ? $iban : '';
$bic               = isset( $bic ) ? $bic : '';
$event             = isset( $event ) ? $event : '';
$organizer         = isset( $organizer ) ? $organizer : '';
$cost_center       = isset( $cost_center ) ? $cost_center : '';
$items             = isset( $items ) ? $items : array();
$km_data           = isset( $km_data ) ? $km_data : array();
$ticket_data       = isset( $ticket_data ) ? $ticket_data : array();
$km_total_cost     = isset( $km_total_cost ) ? $km_total_cost : 0;
$ticket_total_cost = isset( $ticket_total_cost ) ? $ticket_total_cost : 0;
$total             = isset( $total ) ? $total : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?php echo esc_html( ter_get_config( 'email_title', 'Travel expense report' ) ); ?></title>
	<style>
		body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
		.container { max-width: 800px; margin: 0 auto; padding: 10px; }
		.header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; border-radius: 5px 5px 0 0; text-align: center; }
		.header h1 { margin: 0; font-size: 16px; }
		.content { background: #fff; border: 1px solid #e0e0e0; }
		/* Reduced vertical spacing for tighter PDF layout */
		.section { margin-bottom: 8px; page-break-inside: avoid; }
		.section h2 { color: #667eea; border-bottom: 2px solid #667eea; margin-bottom: 8px; }
		.personal-info { background: #f8f9fa; padding: 2px; border-radius: 5px; margin-bottom: 8px; }
		.personal-info table { width: 100%; }
		.personal-info td { padding: 8px; }
		.personal-info td:first-child { font-weight: bold; width: 120px; }
		table.items { width: 100%; border-collapse: collapse; margin: 15px 0; }
		table.items th { background: #667eea; color: white; padding: 12px; text-align: left; }
		table.items td { padding: 10px; border-bottom: 1px solid #e0e0e0; }
		table.items tr:hover { background: #f8f9fa; }
		.total { background: #667eea; color: white; padding: 10px; border-radius: 5px; text-align: center; margin-top: 15px; }
		.total h2 { margin: 0; font-size: 14px; }
		.badge { background: #28a745; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px; }
		.summary-box { background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3; margin: 10px 0; }
		.footer { text-align: center; color: #999; font-size: 12px; margin-top: 10px; border-top: 1px solid #e0e0e0; }
		.receipt-image { max-width: 100%; height: auto; border: 1px solid #ddd; padding: 10px; margin: 10px 0; page-break-inside: avoid; }
		.receipt-container { padding: 5px; background: #f9f9f9; border-radius: 5px; page-break-inside: avoid; }
		@media print {
			.header { background: #667eea !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
			.total { background: #667eea !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
			.badge { background: #28a745 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
			.summary-box { background: #e3f2fd !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
			table.items th { background: #667eea !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="header">
			<h1><?php echo esc_html( ter_get_config( 'email_title', 'Travel expense report from' ) . ' ' . gmdate( 'd.m.Y' ) ); ?></h1>
		</div>
		<div class="content">
			<div class="section">
				<h2><?php echo esc_html( ter_get_config( 'email_personal_details', 'Personal details' ) ); ?></h2>
				<div class="personal-info">
					<table>
						<tr>
							<td><?php echo esc_html( ter_get_config( 'email_name', 'Name:' ) ); ?></td>
							<td><?php echo esc_html( $firstname . ' ' . $lastname ); ?></td>
						</tr>
						<tr>
							<td><?php echo esc_html( ter_get_config( 'email_iban', 'IBAN:' ) ); ?></td>
							<td><?php echo esc_html( $iban ); ?></td>
						</tr>
						<tr>
							<td><?php echo esc_html( ter_get_config( 'email_bic', 'BIC:' ) ); ?></td>
							<td><?php echo esc_html( $bic ); ?></td>
						</tr>
						<tr>
							<td><?php echo esc_html( ter_get_config( 'email_event', 'Event:' ) ); ?></td>
							<td><?php echo esc_html( $event ); ?></td>
						</tr>
						<tr>
							<td><?php echo esc_html( ter_get_config( 'email_organizer', 'Organizer:' ) ); ?></td>
							<td><?php echo esc_html( $organizer ); ?></td>
						</tr>
					</table>
				</div>
			</div>

			<?php if ( ! empty( $items ) ) : ?>
			<div class="section">
				<h2><?php echo esc_html( ter_get_config( 'email_advance_payments', 'Advance payments / purchases' ) ); ?></h2>
				<table class="items">
					<thead>
						<tr>
							<th><?php echo esc_html( ter_get_config( 'label_description', 'Description' ) ); ?></th>
							<th style="text-align: right;"><?php echo esc_html( ter_get_config( 'label_amount', 'Amount (€)' ) ); ?></th>
							<th style="text-align: center;"><?php echo esc_html( ter_get_config( 'label_receipt', 'Receipt' ) ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$attachment_counter = 1;
						foreach ( $items as $item ) :
							$item_name   = sanitize_text_field( $item['name'] );
							$item_amount = floatval( $item['amount'] );
							$has_receipt = ! empty( $item['receipt'] );
							?>
						<tr>
							<td><?php echo esc_html( $item_name ); ?></td>
							<td style="text-align: right;"><strong><?php echo number_format( $item_amount, 2, ',', '.' ); ?> €</strong></td>
							<td style="text-align: center;">
								<?php if ( $has_receipt ) : ?>
									<span><?php echo esc_html( ter_get_config( 'email_provided', 'Provided' ) ); ?></span>
								<?php else : ?>
									<span style="color: red;"><?php echo esc_html( ter_get_config( 'email_missing', 'Missing' ) ); ?></span>
								<?php endif; ?>
							</td>
						</tr>
							<?php
							if ( $has_receipt ) :
								$basename  = function_exists( 'ter_receipt_basename_from_value' ) ? ter_receipt_basename_from_value( $item['receipt'] ) : basename( $item['receipt'] );
								$file_path = function_exists( 'ter_resolve_uploaded_file_path' ) ? ter_resolve_uploaded_file_path( $basename ) : ( trailingslashit( get_temp_dir() ) . $basename );
								ter_log( $file_path );
								if ( $file_path && file_exists( $file_path ) ) :
									$file_ext = strtolower( pathinfo( $file_path, PATHINFO_EXTENSION ) );
									?>
						<tr>
							<td colspan="3">
								<div class="receipt-container">
									<strong><?php echo esc_html( ter_get_config( 'label_receipt', 'Receipt' ) . ' for: ' . $item_name ); ?></strong><br>
									<?php if ( 'pdf' === $file_ext ) : ?>
										<p><em>PDF receipt attached (see attachment: <?php echo esc_html( $attachment_counter ); ?>_Receipt_<?php echo esc_html( sanitize_file_name( $item_name ) ); ?>.pdf)</em></p>
										<?php ++$attachment_counter; ?>
										<?php
									else :
										$image_data = base64_encode( file_get_contents( $file_path ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode,WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Inline a local receipt image as a data URI.
										$mime_type  = ( 'png' === $file_ext ) ? 'image/png' : 'image/jpeg';
										?>
										<img src="data:<?php echo esc_attr( $mime_type ); ?>;base64,<?php echo esc_attr( $image_data ); ?>" class="receipt-image" alt="Receipt">
									<?php endif; ?>
								</div>
							</td>
						</tr>
									<?php
								endif;
						endif;
						endforeach;
						?>
					</tbody>
				</table>
			</div>
			<?php endif; ?>

			<?php if ( ! empty( $km_data ) ) : ?>
			<div class="section">
				<h2><?php echo esc_html( ter_get_config( 'email_trips', 'Trips' ) ); ?></h2>
				<table class="items">
					<thead>
						<tr>
							<th><?php echo esc_html( ter_get_config( 'email_route', 'Route' ) ); ?></th>
							<th><?php echo esc_html( ter_get_config( 'label_kilometers', 'Kilometers' ) ); ?></th>
							<th><?php echo esc_html( ter_get_config( 'label_passengers', 'Passengers' ) ); ?></th>
							<th style="text-align: right;"><?php echo esc_html( ter_get_config( 'email_total_trip_costs', 'Amount' ) ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ( $km_data as $trip ) :
							$km          = floatval( $trip['km'] );
							$passengers  = intval( $trip['passengers'] );
							$description = sanitize_text_field( $trip['description'] );
							$start       = sanitize_text_field( $trip['start'] );
							$target      = sanitize_text_field( $trip['target'] );

							// Get configurable rates.
							$km_rate        = floatval( ter_get_config( 'rate_km_allowance', '0.30' ) );
							$passenger_rate = floatval( ter_get_config( 'rate_passenger_allowance', '0.01' ) );

							$km_cost        = $km * $km_rate;
							$passenger_cost = $km * $passengers * $passenger_rate;
							$trip_total     = $km_cost + $passenger_cost;

							$route = esc_html( $start ) . ' → ' . esc_html( $target );
							if ( ! empty( $description ) ) {
								$route .= '<br><small style="color: #666;">(' . esc_html( $description ) . ')</small>';
							}

							$passenger_display = $passengers > 0 ? $passengers . ' × ' . number_format( $km, 0, ',', '.' ) . ' km × ' . number_format( $passenger_rate, 2, ',', '.' ) . ' €' : '0';
							?>
						<tr>
							<td><?php echo wp_kses_post( $route ); ?></td>
							<td><?php echo number_format( $km, 0, ',', '.' ); ?> km × <?php echo number_format( $km_rate, 2, ',', '.' ); ?> €</td>
							<td><?php echo esc_html( $passenger_display ); ?></td>
							<td style="text-align: right;"><strong><?php echo number_format( $trip_total, 2, ',', '.' ); ?> €</strong></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<div class="summary-box">
					<strong><?php echo esc_html( ter_get_config( 'email_total_trip_costs', 'Total trip costs: ' ) ); ?><?php echo number_format( $km_total_cost, 2, ',', '.' ); ?> €</strong>
				</div>
			</div>
			<?php endif; ?>

			<?php if ( ! empty( $ticket_data ) ) : ?>
			<div class="section">
				<h2><?php echo esc_html( ter_get_config( 'email_tickets', 'Train tickets' ) ); ?></h2>
				<table class="items">
					<thead>
						<tr>
							<th><?php echo esc_html( ter_get_config( 'email_route', 'Route' ) ); ?></th>
							<th style="text-align: right;"><?php echo esc_html( ter_get_config( 'label_cost', 'Cost' ) ); ?></th>
							<th style="text-align: center;"><?php echo esc_html( ter_get_config( 'email_status', 'Status' ) ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ( $ticket_data as $ticket ) :
							$cost        = floatval( $ticket['cost'] );
							$description = sanitize_text_field( $ticket['description'] );
							$start       = sanitize_text_field( $ticket['start'] );
							$target      = sanitize_text_field( $ticket['target'] );
							$has_receipt = ! empty( $ticket['receipt'] );

							$route = esc_html( $start ) . ' → ' . esc_html( $target );
							if ( ! empty( $description ) ) {
								$route .= '<br><small style="color: #666;">(' . esc_html( $description ) . ')</small>';
							}
							?>
						<tr>
							<td><?php echo wp_kses_post( $route ); ?></td>
							<td style="text-align: right;"><strong><?php echo number_format( $cost, 2, ',', '.' ); ?> €</strong></td>
							<td style="text-align: center;">
								<?php if ( $has_receipt ) : ?>
									<span class="badge"><?php echo esc_html( ter_get_config( 'email_ticket_badge', '✓ Ticket' ) ); ?></span>
								<?php endif; ?>
							</td>
						</tr>
							<?php
							if ( $has_receipt ) :
								$basename  = function_exists( 'ter_receipt_basename_from_value' ) ? ter_receipt_basename_from_value( $ticket['receipt'] ) : basename( $ticket['receipt'] );
								$file_path = function_exists( 'ter_resolve_uploaded_file_path' ) ? ter_resolve_uploaded_file_path( $basename ) : ( trailingslashit( get_temp_dir() ) . $basename );
								ter_log( $file_path );
								if ( $file_path && file_exists( $file_path ) ) :
									$file_ext     = strtolower( pathinfo( $file_path, PATHINFO_EXTENSION ) );
									$ticket_label = $start . ' → ' . $target;
									if ( ! empty( $description ) ) {
										$ticket_label .= ' (' . $description . ')';
									}
									?>
						<tr>
							<td colspan="3">
								<div class="receipt-container">
									<strong>Ticket receipt: <?php echo esc_html( $ticket_label ); ?></strong><br>
									<?php
									if ( 'pdf' === $file_ext ) :
										$safe_name = sanitize_file_name( $start . '_' . $target );
										if ( ! empty( $description ) ) {
											$safe_name .= '_' . sanitize_file_name( $description );
										}
										?>
										<p><em>PDF receipt attached (see attachment: <?php echo esc_html( $attachment_counter ); ?>_Ticket_<?php echo esc_html( $safe_name ); ?>.pdf)</em></p>
										<?php ++$attachment_counter; ?>
										<?php
									else :
										$image_data = base64_encode( file_get_contents( $file_path ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode,WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Inline a local receipt image as a data URI.
										$mime_type  = ( 'png' === $file_ext ) ? 'image/png' : 'image/jpeg';
										?>
										<img src="data:<?php echo esc_attr( $mime_type ); ?>;base64,<?php echo esc_attr( $image_data ); ?>" class="receipt-image" alt="Ticket receipt">
									<?php endif; ?>
								</div>
							</td>
						</tr>
									<?php
								endif;
						endif;
						endforeach;
						?>
					</tbody>
				</table>
				<div class="summary-box">
					<strong><?php echo esc_html( ter_get_config( 'email_train_tickets_total', 'Train tickets total: ' ) ); ?><?php echo number_format( $ticket_total_cost, 2, ',', '.' ); ?> €</strong>
				</div>
			</div>
			<?php endif; ?>

			<div class="total">
				<h2><?php echo esc_html( ter_get_config( 'info_total_costs', 'Total costs:' ) ); ?> <?php echo number_format( $total, 2, ',', '.' ); ?> €</h2>
			</div>

			<div class="footer">
				<?php echo esc_html( ter_get_config( 'email_footer', 'This travel expense report was generated automatically by the online form.' ) ); ?>
			</div>
		</div>
	</div>
</body>
</html>
