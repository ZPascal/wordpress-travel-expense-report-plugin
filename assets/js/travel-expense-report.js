/**
 * Travel Expense Report JavaScript
 * Handles form interactions, file uploads, and email submission
 */

let rowCounter = 1;
let tripCounter = 1;
let ticketCounter = 1;
let receipts = {};
let ticketReceipts = {};

document.addEventListener('DOMContentLoaded', function() {
    const addGoodsRowBtn = document.getElementById('addGoodsRow');
    const addKilometerRowBtn = document.getElementById('addKilometerRow');
    const addTicketRowBtn = document.getElementById('addTicketRow');
    const tGoodsBody = document.getElementById('ter_tr_goods');
    const tKilometerBody = document.getElementById('ter_tr_kilometer');
    const tTicketsBody = document.getElementById('ter_tr_tickets');
    const tripCardsContainer = document.getElementById('ter_trip_cards');
    const ticketCardsContainer = document.getElementById('ter_ticket_cards');
    const goodsTotal = document.getElementById('goods_total');
    const kmTotal = document.getElementById('km_total');
    const ticketTotal = document.getElementById('ticket_total');
    const gesamt = document.getElementById('gesamt');
    const sendEmailBtn = document.getElementById('sendEmail');
    const emailMessage = document.getElementById('email_message');

    // Add new goods line row
    addGoodsRowBtn.addEventListener('click', function() {
        let tr = document.createElement('tr');
        tr.setAttribute('data-row-id', rowCounter);
        tr.innerHTML = `
            <td><input type="text" name="artikel[]" placeholder="Description of purchase"></td>
            <td><input type="number" step="0.01" name="betrag[]" class="betrag" value="0" min="0"></td>
            <td>
                <div class="upload-btn-wrapper">
                    <button class="upload-btn" type="button">Upload receipt</button>
                    <input type="file" class="receipt-upload" accept="image/*,.pdf" data-row-id="${rowCounter}">
                </div>
                <div class="receipt-info" id="receipt-info-${rowCounter}"></div>
            </td>
            <td><button class="ter-btn removeRow">−</button></td>
        `;
        tGoodsBody.appendChild(tr);
        rowCounter++;
    });

    // Add new kilometer row (Tabelle und Karte)
    addKilometerRowBtn.addEventListener('click', function() {
        // Tabellen-Zeile
        let tr = document.createElement('tr');
        tr.setAttribute('data-trip-id', tripCounter);
        tr.innerHTML = `
            <td><input type="number" class="km_input" value="0" min="0"></td>
            <td><input type="number" class="km_passengers" value="0" min="0" max="20" title="Number of passengers (0.01€ per km per passenger)"></td>
            <td><input type="text" class="km_description" placeholder="Trip description"></td>
            <td><input type="text" class="km_start" placeholder="Trip start"></td>
            <td><input type="text" class="km_target" placeholder="Trip destination"></td>
            <td><button class="ter-btn removeKmRow" data-trip-id="${tripCounter}">−</button></td>
        `;
        tKilometerBody.appendChild(tr);

        // Mobile Karte
        let card = document.createElement('div');
        card.className = 'ter-trip-card';
        card.setAttribute('data-trip-id', tripCounter);
        card.innerHTML = `
            <div class="trip-field">
                <label class="trip-label">Kilometers</label>
                <input type="number" class="km_input" value="0" min="0">
            </div>
            <div class="trip-field">
                <label class="trip-label">Passengers (0.01€/km/person)</label>
                <input type="number" class="km_passengers" value="0" min="0" max="20">
            </div>
            <div class="trip-field">
                <label class="trip-label">Beschreibung</label>
                <input type="text" class="km_description" placeholder="Beschreibung der Fahrt">
            </div>
            <div class="trip-field">
                <label class="trip-label">Start</label>
                <input type="text" class="km_start" placeholder="Start der Fahrt">
            </div>
            <div class="trip-field">
                <label class="trip-label">Ziel</label>
                <input type="text" class="km_target" placeholder="Ziel der Fahrt">
            </div>
            <div class="trip-actions">
                <button class="ter-btn removeKmRow" data-trip-id="${tripCounter}">Remove</button>
            </div>
        `;
        tripCardsContainer.appendChild(card);

        tripCounter++;
    });

    // Add new ticket row (Tabelle und Karte)
    addTicketRowBtn.addEventListener('click', function() {
        // Tabellen-Zeile
        let tr = document.createElement('tr');
        tr.setAttribute('data-ticket-id', ticketCounter);
        tr.innerHTML = `
            <td><input type="text" class="ticket_start" placeholder="Start"></td>
            <td><input type="text" class="ticket_target" placeholder="Destination"></td>
            <td><input type="text" class="ticket_description" placeholder="Description"></td>
            <td><input type="number" step="0.01" class="ticket_cost" value="0" min="0"></td>
            <td>
                <div class="upload-btn-wrapper">
                    <button class="upload-btn" type="button">Upload ticket</button>
                    <input type="file" class="ticket-upload" accept="image/*,.pdf" data-ticket-id="${ticketCounter}">
                </div>
                <div class="ticket-info" id="ticket-info-${ticketCounter}"></div>
            </td>
            <td><button class="ter-btn removeTicketRow" data-ticket-id="${ticketCounter}">−</button></td>
        `;
        tTicketsBody.appendChild(tr);

        // Mobile Karte
        let card = document.createElement('div');
        card.className = 'ter-trip-card';
        card.setAttribute('data-ticket-id', ticketCounter);
        card.innerHTML = `
            <div class="trip-field">
                <label class="trip-label">Start</label>
                <input type="text" class="ticket_start" placeholder="Start">
            </div>
            <div class="trip-field">
                <label class="trip-label">Ziel</label>
                <input type="text" class="ticket_target" placeholder="Ziel">
            </div>
            <div class="trip-field">
                <label class="trip-label">Beschreibung</label>
                <input type="text" class="ticket_description" placeholder="Beschreibung">
            </div>
            <div class="trip-field">
                <label class="trip-label">Kosten (€)</label>
                <input type="number" step="0.01" class="ticket_cost" value="0" min="0">
            </div>
            <div class="trip-field">
                <label class="trip-label">Ticket</label>
                <div class="upload-btn-wrapper">
                    <button class="upload-btn" type="button">Upload ticket</button>
                    <input type="file" class="ticket-upload" accept="image/*,.pdf" data-ticket-id="${ticketCounter}">
                </div>
                <div class="ticket-info" id="ticket-info-${ticketCounter}-mobile"></div>
            </div>
            <div class="trip-actions">
                <button class="ter-btn removeTicketRow" data-ticket-id="${ticketCounter}">Remove</button>
            </div>
        `;
        ticketCardsContainer.appendChild(card);

        ticketCounter++;
    });

    // File Upload Handler
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('receipt-upload')) {
            handleReceiptUpload(e.target);
        }

        if (e.target.classList.contains('ticket-upload')) {
            handleTicketUpload(e.target);
        }
    });

    // Dynamic removal of rows
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('removeRow')) {
            let rowCount = tGoodsBody.querySelectorAll('tr').length;
            if (rowCount <= 1) {
                alert('At least one item is required!');
                return;
            }

            let row = e.target.closest('tr');
            let rowId = row.getAttribute('data-row-id');

            if (rowId && receipts[rowId]) {
                delete receipts[rowId];
            }

            row.remove();
            updateTotal();
        }

        if (e.target.classList.contains('removeKmRow')) {
            let tripCount = tKilometerBody.querySelectorAll('tr').length;
            if (tripCount <= 1) {
                alert('At least one trip is required!');
                return;
            }

            let tripId = e.target.getAttribute('data-trip-id');

            let tableRow = tKilometerBody.querySelector(`tr[data-trip-id="${tripId}"]`);
            if (tableRow) tableRow.remove();

            let card = tripCardsContainer.querySelector(`.ter-trip-card[data-trip-id="${tripId}"]`);
            if (card) card.remove();

            updateTotal();
        }

        if (e.target.classList.contains('removeTicketRow')) {
            let ticketCount = tTicketsBody.querySelectorAll('tr').length;
            if (ticketCount <= 1) {
                alert('At least one ticket is required!');
                return;
            }

            let ticketId = e.target.getAttribute('data-ticket-id');

            if (ticketId && ticketReceipts[ticketId]) {
                delete ticketReceipts[ticketId];
            }

            let tableRow = tTicketsBody.querySelector(`tr[data-ticket-id="${ticketId}"]`);
            if (tableRow) tableRow.remove();

            let card = ticketCardsContainer.querySelector(`.ter-trip-card[data-ticket-id="${ticketId}"]`);
            if (card) card.remove();

            updateTotal();
        }
    });

    // Monitor changes - synchronisiere Tabelle und Karten
    document.addEventListener('input', function(e) {
        // Synchronisiere Eingaben zwischen Tabelle und Karte für Fahrten
        if (e.target.classList.contains('km_input') ||
            e.target.classList.contains('km_passengers') ||
            e.target.classList.contains('km_description') ||
            e.target.classList.contains('km_start') ||
            e.target.classList.contains('km_target')) {

            let container = e.target.closest('[data-trip-id]');
            if (container) {
                let tripId = container.getAttribute('data-trip-id');
                let className = e.target.className.split(' ')[0];
                let value = e.target.value;

                let tableInput = tKilometerBody.querySelector(`tr[data-trip-id="${tripId}"] .${className}`);
                if (tableInput && tableInput !== e.target) {
                    tableInput.value = value;
                }

                let cardInput = tripCardsContainer.querySelector(`.ter-trip-card[data-trip-id="${tripId}"] .${className}`);
                if (cardInput && cardInput !== e.target) {
                    cardInput.value = value;
                }
            }
        }

        // Synchronisiere Eingaben zwischen Tabelle und Karte für Tickets
        if (e.target.classList.contains('ticket_start') ||
            e.target.classList.contains('ticket_target') ||
            e.target.classList.contains('ticket_description') ||
            e.target.classList.contains('ticket_cost')) {

            let container = e.target.closest('[data-ticket-id]');
            if (container) {
                let ticketId = container.getAttribute('data-ticket-id');
                let className = e.target.className.split(' ')[0];
                let value = e.target.value;

                let tableInput = tTicketsBody.querySelector(`tr[data-ticket-id="${ticketId}"] .${className}`);
                if (tableInput && tableInput !== e.target) {
                    tableInput.value = value;
                }

                let cardInput = ticketCardsContainer.querySelector(`.ter-trip-card[data-ticket-id="${ticketId}"] .${className}`);
                if (cardInput && cardInput !== e.target) {
                    cardInput.value = value;
                }
            }
        }

        // Recalculate only after table and card are in sync (totals read the table inputs)
        if (e.target.classList.contains('betrag') ||
            e.target.classList.contains('km_input') ||
            e.target.classList.contains('km_passengers') ||
            e.target.classList.contains('ticket_cost')) {
            updateTotal();
        }
    });

    // E-Mail versenden
    sendEmailBtn.addEventListener('click', function() {
        sendEmail();
    });

    function handleReceiptUpload(input) {
        let file = input.files[0];
        let rowId = input.getAttribute('data-row-id');

        if (!file) return;

        let formData = new FormData();
        formData.append('action', 'ter_upload_receipt');
        formData.append('receipt', file);
        formData.append('nonce', (typeof terData !== 'undefined' ? terData.nonce : ''));

        let infoDiv = document.getElementById('receipt-info-' + rowId);
        infoDiv.innerHTML = '<small style="color: blue;">Uploading...</small>';

        fetch((typeof terData !== 'undefined' ? terData.ajaxUrl : window.terAjaxUrl), {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                receipts[rowId] = data.data.url;

                let preview = '';
                if (file.type === 'application/pdf') {
                    preview = '<a href="' + data.data.url + '" target="_blank" class="receipt-link">📄 View PDF</a>';
                } else {
                    preview = '<a href="' + data.data.url + '" target="_blank"><img src="' + data.data.url + '" class="receipt-preview"></a>';
                }
                preview += '<br><small style="color: green;">✓ Uploaded</small>';

                infoDiv.innerHTML = preview;
            } else {
                infoDiv.innerHTML = '<small style="color: red;">Error: ' + data.data + '</small>';
            }
        })
        .catch(error => {
            infoDiv.innerHTML = '<small style="color: red;">Upload failed</small>';
        });
    }

    function handleTicketUpload(input) {
        let file = input.files[0];
        let ticketId = input.getAttribute('data-ticket-id');

        if (!file) return;

        let formData = new FormData();
        formData.append('action', 'ter_upload_receipt');
        formData.append('receipt', file);
        formData.append('nonce', (typeof terData !== 'undefined' ? terData.nonce : ''));

        let infoDiv = document.getElementById('ticket-info-' + ticketId);
        if (!infoDiv) {
            infoDiv = document.getElementById('ticket-info-' + ticketId + '-mobile');
        }
        infoDiv.innerHTML = '<small style="color: blue;">Uploading...</small>';

        fetch((typeof terData !== 'undefined' ? terData.ajaxUrl : window.terAjaxUrl), {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ticketReceipts[ticketId] = data.data.url;

                let preview = '';
                if (file.type === 'application/pdf') {
                    preview = '<a href="' + data.data.url + '" target="_blank" class="receipt-link">📄 View PDF</a>';
                } else {
                    preview = '<a href="' + data.data.url + '" target="_blank"><img src="' + data.data.url + '" class="receipt-preview"></a>';
                }
                preview += '<br><small style="color: green;">✓ Uploaded</small>';

                let infoDivDesktop = document.getElementById('ticket-info-' + ticketId);
                let infoDivMobile = document.getElementById('ticket-info-' + ticketId + '-mobile');
                if (infoDivDesktop) infoDivDesktop.innerHTML = preview;
                if (infoDivMobile) infoDivMobile.innerHTML = preview;
            } else {
                infoDiv.innerHTML = '<small style="color: red;">Error: ' + data.data + '</small>';
            }
        })
        .catch(error => {
            infoDiv.innerHTML = '<small style="color: red;">Upload failed</small>';
        });
    }

    function sendEmail() {
        const firstname = document.getElementById('ter_firstname').value.trim();
        const lastname = document.getElementById('ter_lastname').value.trim();
        const iban = document.getElementById('ter_iban').value.trim().toUpperCase().replace(/\s/g, '');
        const bic = document.getElementById('ter_bic').value.trim().toUpperCase();
        const event = document.getElementById('ter_event').value.trim();

        if (!firstname || !lastname || !iban || !event) {
            emailMessage.innerHTML = '<p style="color: red;">Please fill all personal details.</p>';
            return;
        }

        const ibanPattern = /^[A-Z]{2}[0-9]{2}[A-Z0-9]+$/;
        if (!ibanPattern.test(iban)) {
            emailMessage.innerHTML = '<p style="color: red;">Invalid IBAN. Format: DE89370400440532013000</p>';
            return;
        }

        const bicPattern = /^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/;
        if (bic && !bicPattern.test(bic)) {
            emailMessage.innerHTML = '<p style="color: red;">Invalid BIC. Format: COBADEFFXXX</p>';
            return;
        }

        let items = [];
        let missingReceipts = false;
        document.querySelectorAll('#ter_tr_goods tr').forEach(row => {
            let name = row.querySelector('input[name="artikel[]"]').value.trim();
            let amount = parseFloat(row.querySelector('input[name="betrag[]"]').value) || 0;
            let rowId = row.getAttribute('data-row-id');
            let receipt = receipts[rowId] || null;

            if (name || amount > 0) {
                if (name && !receipt) {
                    missingReceipts = true;
                }
                items.push({
                    name: name,
                    amount: amount,
                    receipt: receipt
                });
            }
        });

        if (missingReceipts) {
            emailMessage.innerHTML = '<p style="color: red;">⚠️ Please upload receipts for all filled purchases.</p>';
            return;
        }

        let km_data = [];
        document.querySelectorAll('#ter_tr_kilometer tr').forEach(row => {
            let km = parseFloat(row.querySelector('.km_input').value) || 0;
            let passengers = parseInt(row.querySelector('.km_passengers').value) || 0;
            let description = row.querySelector('.km_description').value;
            let start = row.querySelector('.km_start').value;
            let target = row.querySelector('.km_target').value;

            if (km > 0 || passengers > 0 || description || start || target) {
                km_data.push({
                    km: km,
                    passengers: passengers,
                    description: description,
                    start: start,
                    target: target
                });
            }
        });

        let ticket_data = [];
        let missingTickets = false;
        document.querySelectorAll('#ter_tr_tickets tr').forEach(row => {
            let start = row.querySelector('.ticket_start').value.trim();
            let target = row.querySelector('.ticket_target').value.trim();
            let description = row.querySelector('.ticket_description').value.trim();
            let cost = parseFloat(row.querySelector('.ticket_cost').value) || 0;
            let ticketId = row.getAttribute('data-ticket-id');
            let receipt = ticketReceipts[ticketId] || null;

            if (cost > 0 || start || target || description) {
                if ((start || target || description) && !receipt) {
                    missingTickets = true;
                }
                ticket_data.push({
                    start: start,
                    target: target,
                    description: description,
                    cost: cost,
                    receipt: receipt
                });
            }
        });

        if (missingTickets) {
            emailMessage.innerHTML = '<p style="color: red;">⚠️ Please upload receipts for all filled train tickets.</p>';
            return;
        }

        // Validation: at least one expense type must be present
        if (items.length === 0 && km_data.length === 0 && ticket_data.length === 0) {
            emailMessage.innerHTML = '<p style="color: red;">⚠️ Please add at least one expense item (purchase, trip, or train ticket).</p>';
            return;
        }

        let km_total_cost = parseFloat(kmTotal.innerText) || 0;
        let ticket_total_cost = parseFloat(ticketTotal.innerText) || 0;
        let total = parseFloat(gesamt.innerText) || 0;

        let formData = new FormData();
        formData.append('action', 'ter_send_email');
        formData.append('nonce', (typeof terData !== 'undefined' ? terData.nonce : ''));
        formData.append('firstname', firstname);
        formData.append('lastname', lastname);
        formData.append('iban', iban);
        formData.append('bic', bic);
        formData.append('event', event);

        // Get organizer (required)
        let organizer = document.querySelector('[name="organizer"]');
        formData.append('organizer', (organizer && organizer.value) ? organizer.value.trim() : '');

        // Get cost_center (optional)
        let cost_center = document.querySelector('[name="cost_center"]');
        formData.append('cost_center', (cost_center && cost_center.value) ? cost_center.value.trim() : '');

        // Customer email (copy recipient) - robust retrieval: try by id then by name
        let customerEmail = '';
        const custElById = document.getElementById('ter_customer_email');
        if (custElById && typeof custElById.value !== 'undefined') {
            customerEmail = custElById.value.trim();
        } else {
            const custByName = document.querySelector('[name="customer_email"]');
            if (custByName && typeof custByName.value !== 'undefined') {
                customerEmail = custByName.value.trim();
            }
        }
        customerEmail = customerEmail || '';
        formData.append('customer_email', customerEmail);
        formData.append('items', JSON.stringify(items));
        formData.append('km_data', JSON.stringify(km_data));
        formData.append('ticket_data', JSON.stringify(ticket_data));
        formData.append('km_total_cost', km_total_cost);
        formData.append('ticket_total_cost', ticket_total_cost);
        formData.append('total', total);

        emailMessage.innerHTML = '<p style="color: blue;">Sending email...</p>';

        fetch((typeof terData !== 'undefined' ? terData.ajaxUrl : window.terAjaxUrl), {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                emailMessage.innerHTML = '<p style="color: green;">' + data.data + '</p>';
            } else {
                emailMessage.innerHTML = '<p style="color: red;">' + data.data + '</p>';
            }
        })
        .catch(error => {
            emailMessage.innerHTML = '<p style="color: red;">Error sending email.</p>';
        });
    }

    function updateTotal() {
        // Get rates from configuration (fallback to defaults if not provided)
        let kmRate = typeof terData !== 'undefined' && terData.rates ? parseFloat(terData.rates.km) : 0.30;
        let passengerRate = typeof terData !== 'undefined' && terData.rates ? parseFloat(terData.rates.passenger) : 0.01;

        let totalGoodsCost = 0;
        document.querySelectorAll('.betrag').forEach(input => {
            totalGoodsCost += parseFloat(input.value) || 0;
        });
        goodsTotal.innerText = totalGoodsCost.toFixed(2);

        let totalKmCost = 0;
        document.querySelectorAll('#ter_tr_kilometer tr').forEach(row => {
            let km = parseFloat(row.querySelector('.km_input').value) || 0;
            let passengers = parseInt(row.querySelector('.km_passengers').value) || 0;

            let kmCost = km * kmRate;
            let passengerCost = km * passengers * passengerRate;

            totalKmCost += kmCost + passengerCost;
        });
        kmTotal.innerText = totalKmCost.toFixed(2);

        let totalTicketCost = 0;
        document.querySelectorAll('#ter_tr_tickets tr').forEach(row => {
            let cost = parseFloat(row.querySelector('.ticket_cost').value) || 0;
            totalTicketCost += cost;
        });
        ticketTotal.innerText = totalTicketCost.toFixed(2);

        let sum = totalGoodsCost + totalKmCost + totalTicketCost;
        gesamt.innerText = sum.toFixed(2);
    }

    updateTotal();
});
