<?php
/**
 * Admin Menus and Pages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function lol_admin_menu() {
    add_menu_page(
        'Laundry Management',
        'Laundry Management',
        'manage_options',
        'lol-laundry-management',
        'lol_admin_dashboard_page',
        'dashicons-cart',
        30
    );

    add_submenu_page(
        'lol-laundry-management',
        'Dashboard',
        'Dashboard',
        'manage_options',
        'lol-laundry-management',
        'lol_admin_dashboard_page'
    );

    add_submenu_page(
        'lol-laundry-management',
        'Orders',
        'Orders',
        'manage_options',
        'lol-orders',
        'lol_admin_orders_page'
    );

    add_submenu_page(
        'lol-laundry-management',
        'All Pickups',
        '📋 All Pickups',
        'manage_options',
        'lol-all-pickups',
        'lol_admin_all_pickups_page'
    );

    add_submenu_page(
        'lol-laundry-management',
        'Settings',
        '⚙️ Settings',
        'manage_options',
        'lol-settings',
        'lol_admin_settings_page'
    );

    add_submenu_page(
        'lol-laundry-management',
        "Today's Delivery",
        "🚚 Today's Delivery",
        'manage_options',
        'lol-todays-delivery',
        'lol_admin_todays_delivery_page'
    );

    add_submenu_page(
        'lol-laundry-management',
        'Partial Delivery Check',
        '🔍 Partial Delivery Check',
        'manage_options',
        'lol-partial-delivery-check',
        'lol_admin_partial_delivery_check_page'
    );

    add_submenu_page(
        'lol-laundry-management',
        'Export Excel',
        'Export Excel',
        'manage_options',
        'lol-export',
        'lol_admin_export_page'
    );

    add_submenu_page(
        'lol-laundry-management',
        'Main Excel Sheet',
        'Main Excel Sheet',
        'manage_options',
        'lol-main-excel',
        'lol_admin_main_excel_page'
    );

    add_submenu_page(
        'lol-laundry-management',
        'SMS Logs',
        'SMS Logs',
        'manage_options',
        'lol-whatsapp-logs',
        'lol_admin_whatsapp_logs_page'
    );

    add_submenu_page(
        'lol-laundry-management',
        'Payment Collection',
        '💰 Payment Collection',
        'manage_options',
        'lol-payment-collection',
        'lol_admin_payment_collection_page'
    );

    add_submenu_page(
        'lol-laundry-management',
        'Customer Reviews',
        '⭐ Reviews',
        'manage_options',
        'lol-reviews',
        'lol_admin_reviews_page'
    );
}
add_action( 'admin_menu', 'lol_admin_menu' );

/**
 * Helper: Generate WhatsApp deep link
 */
function lol_whatsapp_link( $phone, $message ) {
    // Strip non-numeric, add 91 country code if not present
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if ( strlen($phone) === 10 ) {
        $phone = '91' . $phone;
    }
    return 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);
}

/**
 * Helper: Payment status badge HTML
 */
function lol_payment_badge( $status ) {
    $status = strtolower( trim($status) );
    switch ($status) {
        case 'paid':
            return '<span class="lol-badge lol-badge-paid">Paid</span>';
        case 'partial':
            return '<span class="lol-badge lol-badge-partial">Partial</span>';
        case 'unpaid':
        default:
            return '<span class="lol-badge lol-badge-unpaid">Unpaid</span>';
    }
}

/**
 * Helper: Order status badge HTML
 */
function lol_status_badge( $status ) {
    $s = strtolower( trim($status) );
    if ( $s === 'delivered' ) {
        return '<span class="lol-badge lol-badge-delivered">Delivered</span>';
    } elseif ( $s === 'processing' ) {
        return '<span class="lol-badge lol-badge-processing">Processing</span>';
    }
    return '<span class="lol-badge lol-badge-processing">' . esc_html($status) . '</span>';
}

/**
 * Admin page styles (shared across admin pages)
 */
function lol_admin_page_styles() {
    ?>
    <style>
        .lol-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            line-height: 1.4;
        }
        .lol-badge-paid {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .lol-badge-unpaid {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .lol-badge-partial {
            background: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .lol-badge-delivered {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .lol-badge-processing {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        .lol-date-header td {
            background: #f0f6fc !important;
            font-weight: 700;
            font-size: 14px;
            color: #1d4ed8;
            padding: 10px 15px !important;
            border-top: 2px solid #93c5fd;
            border-bottom: 1px solid #bfdbfe;
        }
        .lol-wa-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            background: #25D366;
            color: #fff !important;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: background 0.2s;
            white-space: nowrap;
        }
        .lol-wa-btn:hover {
            background: #1ea952;
            color: #fff !important;
        }
        .lol-wa-btn-small {
            padding: 3px 8px;
            font-size: 11px;
        }
        .lol-items-detail {
            font-size: 12px;
            color: #555;
            line-height: 1.6;
        }
        .lol-items-detail .delivered-ok {
            color: #166534;
        }
        .lol-items-detail .delivered-partial {
            color: #92400e;
        }
        .lol-amount-col {
            white-space: nowrap;
        }
        .lol-today-banner {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: #78350f;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
        }
        .lol-today-banner .count {
            background: #fff;
            color: #d97706;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 14px;
        }
        .lol-stat-cards {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        .lol-stat-card {
            background: #fff;
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            flex: 1;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .lol-stat-card h3 {
            margin: 0 0 8px 0;
            color: #6b7280;
            font-size: 13px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .lol-stat-card .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
        }
    </style>
    <script>
    var lol_delivery_nonce_token = "<?php echo wp_create_nonce('lol_delivery_nonce'); ?>";
    document.addEventListener("DOMContentLoaded", function() {
        function logWaSend(orderId, phone, encodedMessage, btn) {
            var message = decodeURIComponent(encodedMessage);
            var originalText = btn ? btn.innerHTML : 'Sending';
            if (btn) btn.innerHTML = 'Sending...';

            var formData = new FormData();
            formData.append('action', 'lol_log_whatsapp');
            formData.append('order_id', orderId);
            formData.append('phone_number', phone);
            formData.append('message', message);
            formData.append('nonce', lol_delivery_nonce_token);
            
            fetch(ajaxurl, { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (btn) btn.innerHTML = originalText;
                    if (res.success) {
                        alert('Message sent successfully!');
                    } else {
                        alert('Error: ' + res.data.message);
                    }
                })
                .catch(e => {
                    if (btn) btn.innerHTML = originalText;
                    alert('Network error.');
                });
        }
        window.logWaSend = logWaSend;

        // Status Update
        var statusSelects = document.querySelectorAll('.lol-status-update');
        statusSelects.forEach(function(sel) {
            sel.addEventListener('change', function() {
                var token = this.getAttribute('data-token');
                var newStatus = this.value;
                var originalColor = this.style.backgroundColor;
                
                if (newStatus === 'Partial Delivery') {
                    // Open Partial Delivery Modal
                    openPartialDeliveryModal(token, sel, originalColor);
                    return;
                }
                
                if (newStatus === 'Processing') {
                    openProcessingModal(token, sel, originalColor);
                    return;
                }
                
                this.style.backgroundColor = '#fef08a'; // yellow loading

                var formData = new FormData();
                formData.append('action', 'lol_update_order_status');
                formData.append('token_id', token);
                formData.append('new_status', newStatus);
                
                fetch(ajaxurl, { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            this.style.backgroundColor = '#bbf7d0'; // green success
                            setTimeout(() => this.style.backgroundColor = originalColor, 1500);
                        } else {
                            alert("Failed to update status");
                            this.style.backgroundColor = '#fecaca'; // red error
                        }
                    });
            });
        });

        // Processing Modal Logic
        var currentProcessingToken = null;
        var currentProcessingSelect = null;
        var currentProcessingOriginalColor = null;

        function openProcessingModal(token, selectElement, originalColor) {
            currentProcessingToken = token;
            currentProcessingSelect = selectElement;
            currentProcessingOriginalColor = originalColor;
            document.getElementById('processing-token-id').textContent = token;
            document.getElementById('processing-amount').value = '';
            document.getElementById('processing-date').value = '';
            document.getElementById('lol-processing-modal').style.display = 'flex';
        }

        if (document.getElementById('btn-close-processing')) {
            document.getElementById('btn-close-processing').addEventListener('click', function() {
                document.getElementById('lol-processing-modal').style.display = 'none';
                if (currentProcessingSelect) {
                    currentProcessingSelect.value = 'Picked Up'; // Revert back temporarily
                }
            });
        }

        if (document.getElementById('btn-save-processing')) {
            document.getElementById('btn-save-processing').addEventListener('click', function() {
                var amt = document.getElementById('processing-amount').value;
                var date = document.getElementById('processing-date').value;
                if (!amt || !date) {
                    alert('Please enter total amount and delivery date.');
                    return;
                }
                
                var formData = new FormData();
                formData.append('action', 'lol_process_order');
                formData.append('token_id', currentProcessingToken);
                formData.append('amount', amt);
                formData.append('delivery_date', date);

                var btn = this;
                var oldText = btn.textContent;
                btn.textContent = 'Saving...';
                btn.disabled = true;

                fetch(ajaxurl, { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(res => {
                        btn.textContent = oldText;
                        btn.disabled = false;
                        if(res.success) {
                            document.getElementById('lol-processing-modal').style.display = 'none';
                            alert('Order processed and customer notified successfully.');
                            location.reload();
                        } else {
                            alert('Error: ' + res.data.message);
                        }
                    })
                    .catch(e => {
                        btn.textContent = oldText;
                        btn.disabled = false;
                        alert('Server error.');
                    });
            });
        }

        // Partial Delivery Modal Logic
        var currentPartialToken = null;
        var currentPartialSelect = null;
        var currentPartialOriginalColor = null;

        function openPartialDeliveryModal(token, selectElement, originalColor) {
            currentPartialToken = token;
            currentPartialSelect = selectElement;
            currentPartialOriginalColor = originalColor;
            document.getElementById('partial-token-id').textContent = token;
            
            var container = document.getElementById('partial-items-container');
            container.innerHTML = '<p>Loading items...</p>';
            document.getElementById('lol-partial-modal').style.display = 'flex';

            // Fetch items using existing action
            var fd = new FormData();
            fd.append('action', 'lol_search_token');
            fd.append('token_id', token);
            fd.append('nonce', lol_delivery_nonce_token);
            fetch(ajaxurl, { method: 'POST', body: fd })
                .then(r => r.json())
                .then(res => {
                    if(res.success) {
                        var items = res.data.items;
                        var html = '<h4 style="margin-top:0; margin-bottom:15px;">Items:</h4>';
                        items.forEach(function(item) {
                            var remain = parseInt(item.quantity) - parseInt(item.delivered_quantity);
                            html += `
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                                    <div style="flex: 1; font-size: 14px; color: #444;">
                                        Picked up: ${item.quantity} x ${item.service_type}
                                    </div>
                                    <div style="display: flex; align-items: center; font-size: 14px; color: #444;">
                                        <label style="margin-right: 10px;">Delivered Qty:</label>
                                        <input type="number" class="partial-qty-input" data-item-id="${item.id}" data-service="${item.service_type}" max="${remain}" min="0" value="0" style="width: 70px; padding: 5px; border-radius: 4px; border: 1px solid #ccc; font-size: 14px;">
                                    </div>
                                </div>
                            `;
                        });
                        container.innerHTML = html;
                    } else {
                        container.innerHTML = '<p style="color:red;">Error loading items.</p>';
                    }
                })
                .catch(e => {
                    container.innerHTML = '<p style="color:red;">Error loading items.</p>';
                });
        }

        if (document.getElementById('btn-close-partial')) {
            document.getElementById('btn-close-partial').addEventListener('click', function() {
                document.getElementById('lol-partial-modal').style.display = 'none';
                if (currentPartialSelect) {
                    currentPartialSelect.value = 'Picked Up'; // Revert back temporarily
                }
            });
        }

        if (document.getElementById('btn-save-partial')) {
            document.getElementById('btn-save-partial').addEventListener('click', function() {
                var inputs = document.querySelectorAll('.partial-qty-input');
                var updates = [];
                var msgLines = [];
                inputs.forEach(function(inp) {
                    var val = parseInt(inp.value);
                    if (val > 0) {
                        updates.push({
                            item_id: inp.getAttribute('data-item-id'),
                            deliver_now: val
                        });
                        msgLines.push('• ' + inp.getAttribute('data-service') + ' - ' + val + ' Items');
                    }
                });

                if (updates.length === 0) {
                    alert('Please select at least one item to deliver.');
                    return;
                }

                this.disabled = true;
                this.textContent = 'Saving...';

                var fd = new FormData();
                fd.append('action', 'lol_save_partial_delivery');
                fd.append('token_id', currentPartialToken);
                fd.append('items', JSON.stringify(updates));
                fd.append('nonce', lol_delivery_nonce_token);

                fetch(ajaxurl, { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(res => {
                        this.disabled = false;
                        this.textContent = 'Save Delivery & Notify';
                        
                        if (res.success) {
                            document.getElementById('lol-partial-modal').style.display = 'none';
                            if (currentPartialSelect) {
                                currentPartialSelect.style.backgroundColor = '#bbf7d0';
                                setTimeout(() => currentPartialSelect.style.backgroundColor = currentPartialOriginalColor, 1500);
                            }
                            
                            var waMsg = `Dear Customer,\nYour laundry order (Token ID: ${currentPartialToken}) is partially ready.\nItems being delivered today:\n${msgLines.join(', ')}\nRemaining items will be delivered shortly.\nThank you.\n⭐ Rate your experience & leave a review:\n${window.location.origin}/?lol_review=${encodeURIComponent(currentPartialToken)}\nHave a great day!`;
                            if (res.data && res.data.phone_number) {
                                window.logWaSend(res.data.order_id, res.data.phone_number, encodeURIComponent(waMsg), null);
                            }
                            
                            setTimeout(() => location.reload(), 1000); // Reload to reflect changes
                        } else {
                            alert(res.data ? res.data.message : 'Error saving partial delivery.');
                        }
                    })
                    .catch(e => {
                        this.disabled = false;
                        this.textContent = 'Save Delivery & Notify';
                        alert('Error saving partial delivery.');
                    });
            });
        }
    });
    </script>
    <?php
}

function lol_admin_dashboard_page() {
    global $wpdb;
    $orders_table = $wpdb->prefix . 'laundry_orders';

    $today = current_time('Y-m-d');
    
    $today_pickups = $wpdb->get_var("SELECT COUNT(*) FROM $orders_table WHERE pickup_date = '$today'");
    $today_deliveries = $wpdb->get_var("SELECT COUNT(*) FROM $orders_table WHERE delivery_date = '$today'");
    $pending_orders = $wpdb->get_var("SELECT COUNT(*) FROM $orders_table WHERE order_status != 'Delivered'");

    lol_admin_page_styles();
    ?>
    <div class="wrap">
        <h1>Laundry Management Dashboard</h1>
        
        <div class="lol-stat-cards">
            <div class="lol-stat-card">
                <h3>Today's Pickups</h3>
                <p class="stat-value"><?php echo intval($today_pickups); ?></p>
            </div>
            <div class="lol-stat-card">
                <h3>Today's Deliveries</h3>
                <p class="stat-value"><?php echo intval($today_deliveries); ?></p>
            </div>
            <div class="lol-stat-card">
                <h3>Pending Orders</h3>
                <p class="stat-value"><?php echo intval($pending_orders); ?></p>
            </div>
        </div>
    </div>
    <?php
}

function lol_admin_orders_page() {
    global $wpdb;
    $orders_table = $wpdb->prefix . 'laundry_orders';
    $items_table = $wpdb->prefix . 'laundry_order_items';

    // Fetch all orders grouped by pickup_date DESC
    $orders = $wpdb->get_results("SELECT * FROM $orders_table ORDER BY pickup_date DESC, id DESC LIMIT 200");

    // Pre-fetch all items for these orders in one query
    $order_ids = array_map(function($o) { return intval($o->id); }, $orders);
    $all_items = array();
    if ( ! empty($order_ids) ) {
        $ids_str = implode(',', $order_ids);
        $items_rows = $wpdb->get_results("SELECT * FROM $items_table WHERE order_id IN ($ids_str)");
        foreach ($items_rows as $item) {
            $all_items[$item->order_id][] = $item;
        }
    }

    lol_admin_page_styles();
    ?>
    <div class="wrap">
        <h1>All Orders</h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 130px;">Token ID</th>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Pickup Date</th>
                    <th>Delivery Date</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Total Amount</th>
                    <th>Items</th><th>Total Clothes</th>
                    <th>Delivery Boy</th>
                    <th style="width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders) :
                    $current_date = null;
                    foreach($orders as $order) :
                        // Date separator row
                        if ( $order->pickup_date !== $current_date ) :
                            $current_date = $order->pickup_date;
                            $formatted_date = date_i18n('l, d F Y', strtotime($current_date));
                            ?>
                            <tr class="lol-date-header">
                                <td colspan="13">📅 <?php echo esc_html($formatted_date); ?></td>
                            </tr>
                        <?php endif;

                        // Items delivered info
                        $items_html = '-';
                        if ( isset($all_items[$order->id]) ) {
                            $item_parts = array();
                            foreach ($all_items[$order->id] as $item) {
                                $del = intval($item->delivered_quantity);
                                $tot = intval($item->quantity);
                                $item_parts[] = esc_html($item->service_type) . ': ' . $del . '/' . $tot;
                            }
                            $items_html = implode('<br>', $item_parts);
                        }

                        // Payment amount display
                        $amount_html = '-';
                        if ( $order->total_bill_amount > 0 || $order->amount_received > 0 ) {
                            $amount_html = '₹' . number_format($order->amount_received, 0) . ' / ₹' . number_format($order->total_bill_amount, 0);
                            if ( $order->balance_due > 0 ) {
                                $amount_html .= '<br>Due: ₹' . number_format($order->balance_due, 0);
                            } else {
                                $amount_html .= '<br>Paid';
                            }
                        }

                        // WhatsApp alerts
                        $wa_actions = '<div style="display:flex; flex-direction:column; gap:4px;">';

                        $review_link_text = "\n\nIf you have any review or feedback for us, please give it by clicking this link: https://lol.infizestpublishings.com/?lol_review=" . $order->token_id;

                        // 1. Picked Up
                        $msg1 = "Hello " . $order->customer_name . ",\nYour item is picked up.\nToken: " . $order->token_id . ".\nThank you! — Team Laugh-O-Laundry" . $review_link_text;
                        $enc1 = rawurlencode($msg1);
                        $wa_actions .= '<a href="javascript:void(0)" class="lol-wa-btn lol-wa-btn-small" style="background:#64748b;" title="Picked Up Alert" onclick="logWaSend('.$order->id.', \''.$order->phone_number.'\', \''.$enc1.'\', this); return false;">📦 Picked Up</a>';

                        // 2. Processing
                        $amount_text = ($order->total_bill_amount > 0) ? "\nTotal Amount Payable: ₹" . number_format($order->total_bill_amount, 2) : "";
                        $due_date_text = ($order->delivery_date && $order->delivery_date !== '0000-00-00') ? "\nExpected Delivery Date: " . date_i18n( get_option('date_format'), strtotime($order->delivery_date) ) : "\nExpected Delivery Date: within 3-4 days";

                        $msg2 = "Hello " . $order->customer_name . ",\nYour item is in processing." . $due_date_text . $amount_text . "\nToken: " . $order->token_id . ".\nThank you! — Team Laugh-O-Laundry" . $review_link_text;
                        $enc2 = rawurlencode($msg2);
                        $wa_actions .= '<a href="javascript:void(0)" class="lol-wa-btn lol-wa-btn-small" style="background:#eab308;" title="Processing Alert" onclick="logWaSend('.$order->id.', \''.$order->phone_number.'\', \''.$enc2.'\', this); return false;">⚙️ Processing</a>';

                        // 3. Out for delivery (Partial/Full logic)
                        $delivery_items_text = "";
                        if ($order->order_status === 'Partial Delivery' && isset($all_items[$order->id])) {
                            $partial_list = [];
                            foreach ($all_items[$order->id] as $item) {
                                if (intval($item->delivered_quantity) > 0 && intval($item->quantity) > intval($item->delivered_quantity)) {
                                    $partial_list[] = $item->service_type; 
                                } elseif (intval($item->delivered_quantity) > 0) {
                                    $partial_list[] = $item->service_type;
                                }
                            }
                            if (!empty($partial_list)) {
                                $delivery_items_text = " (" . implode(", ", $partial_list) . ") and the rest will be delivered within the delivery date";
                            }
                        }
                        $msg3 = "Hello " . $order->customer_name . ",\nYour items should be delivered today" . $delivery_items_text . ".\nToken: " . $order->token_id . ".\nThank you! — Team Laugh-O-Laundry" . $review_link_text;
                        $enc3 = rawurlencode($msg3);
                        $wa_actions .= '<a href="javascript:void(0)" class="lol-wa-btn lol-wa-btn-small" style="background:#0ea5e9;" title="Out for Delivery Alert" onclick="logWaSend('.$order->id.', \''.$order->phone_number.'\', \''.$enc3.'\', this); return false;">🚚 Out for Delivery</a>';

                        // 4. Delivered
                        $msg4 = "Hello " . $order->customer_name . ",\nYour items have been delivered.\nToken: " . $order->token_id . ".\nThank you for choosing Team Laugh-O-Laundry!" . $review_link_text;
                        $enc4 = rawurlencode($msg4);
                        $wa_actions .= '<a href="javascript:void(0)" class="lol-wa-btn lol-wa-btn-small" style="background:#22c55e;" title="Delivered Alert" onclick="logWaSend('.$order->id.', \''.$order->phone_number.'\', \''.$enc4.'\', this); return false;">✅ Delivered</a>';

                        $wa_actions .= '</div>';
                        
                        // Status Dropdown
                        $statuses = ['Picked Up', 'Processing', 'Partial Delivery', 'Partial delivered', 'Delivered', 'Completed'];
                        $status_select = '<select class="lol-status-update" data-token="'.esc_attr($order->token_id).'" style="font-size:11px; padding:0 4px; max-width:100px; margin-top:5px;">';
                        foreach ($statuses as $st) {
                            $selected = ($st === $order->order_status) ? 'selected' : '';
                            $status_select .= '<option value="'.esc_attr($st).'" '.$selected.'>'.esc_html($st).'</option>';
                        }
                        $status_select .= '</select>';
                ?>
                <tr>
                    <td><strong><?php echo esc_html($order->token_id); ?></strong></td>
                    <td><?php echo esc_html($order->customer_name); ?></td>
                    <td><?php echo esc_html($order->phone_number); ?></td>
                    <td><?php echo esc_html(substr($order->address, 0, 30)) . (strlen($order->address)>30?'...':''); ?></td>
                    <td><?php echo esc_html($order->pickup_date); ?></td>
                    <td><?php echo $order->delivery_date ? esc_html($order->delivery_date) : '-'; ?></td>
                    <td><?php echo lol_status_badge($order->order_status) . '<br>' . $status_select; ?></td>
                    <td><?php echo lol_payment_badge($order->payment_status); ?></td>
                    <td class="lol-amount-col"><?php echo $amount_html; ?></td>
                    <td class="lol-items-detail"><?php echo $items_html; ?></td><td class="lol-total-clothes" style="text-align:center;font-weight:600;"><?php $lol_total_clothes = 0; if ( isset($all_items[$order->id]) ) { foreach ( $all_items[$order->id] as $lol_ci ) { $lol_total_clothes += intval($lol_ci->quantity); } } echo $lol_total_clothes; ?></td>
                    <td><?php echo esc_html($order->delivery_boy ? $order->delivery_boy : ($order->pickup_agent_name ? $order->pickup_agent_name : 'Not Assigned')); ?></td>
                    <td><?php echo $wa_actions; ?></td>
                </tr>
                <?php endforeach; else : ?>
                <tr><td colspan="13">No orders found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Partial Delivery Modal -->
        <div id="lol-partial-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:99999; align-items:center; justify-content:center;">
            <div style="background:#fff; padding:20px; border-radius:8px; width:400px; max-width:90%; position:relative;">
                <h2 style="margin-top:0;">Partial Delivery</h2>
                <p>Token ID: <strong id="partial-token-id"></strong></p>
                <div id="partial-items-container" style="max-height:300px; overflow-y:auto; margin-bottom:15px;"></div>
                <button type="button" id="btn-save-partial" class="button button-primary">Save Delivery & Notify</button>
                <button type="button" id="btn-close-partial" class="button" style="margin-left:10px;">Cancel</button>
            </div>
        </div>

        <!-- Processing Modal -->
        <div id="lol-processing-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:99999; align-items:center; justify-content:center;">
            <div style="background:#fff; padding:20px; border-radius:8px; width:400px; max-width:90%; position:relative;">
                <h2 style="margin-top:0;">Processing Details</h2>
                <p>Token ID: <strong id="processing-token-id"></strong></p>
                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px;">Total Amount (₹)</label>
                    <input type="number" id="processing-amount" style="width:100%; padding:8px;">
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px;">Delivery Date</label>
                    <input type="date" id="processing-date" style="width:100%; padding:8px;">
                </div>
                <button type="button" id="btn-save-processing" class="button button-primary">Notify Customer & Save</button>
                <button type="button" id="btn-close-processing" class="button" style="margin-left:10px;">Cancel</button>
            </div>
        </div>

    </div>
    <?php
}

/**
 * Today's Delivery Page
 */
function lol_admin_todays_delivery_page() {
    global $wpdb;
    $orders_table = $wpdb->prefix . 'laundry_orders';
    $items_table = $wpdb->prefix . 'laundry_order_items';

    $today = current_time('Y-m-d');
    $today_formatted = date_i18n('l, d F Y', strtotime($today));

    // Fetch all orders scheduled for delivery today
    $orders = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $orders_table WHERE delivery_date = %s ORDER BY id DESC",
        $today
    ));

    // Pre-fetch items
    $order_ids = array_map(function($o) { return intval($o->id); }, $orders);
    $all_items = array();
    if ( ! empty($order_ids) ) {
        $ids_str = implode(',', $order_ids);
        $items_rows = $wpdb->get_results("SELECT * FROM $items_table WHERE order_id IN ($ids_str)");
        foreach ($items_rows as $item) {
            $all_items[$item->order_id][] = $item;
        }
    }

    $total_count = count($orders);

    lol_admin_page_styles();
    ?>
    <div class="wrap">
        <h1>Today's Delivery</h1>

        <div class="lol-today-banner">
            <span style="font-size: 24px;">🚚</span>
            Deliveries scheduled for <?php echo esc_html($today_formatted); ?>
            <span class="count"><?php echo intval($total_count); ?></span>
        </div>

        <?php if ( $total_count === 0 ) : ?>
            <div style="background: #fff; padding: 40px; text-align: center; border-radius: 10px; border: 1px solid #e5e7eb; margin-top: 10px;">
                <p style="font-size: 18px; color: #9ca3af;">No deliveries scheduled for today.</p>
            </div>
        <?php else : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 140px;">Token ID</th>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Pickup Date</th>
                    <th>Delivery Date</th>
                    <th>Status</th>
                    <th>Payment Status</th>
                    <th>Total Amount</th>
                    <th>Items Delivered</th>
                    <th>Delivery Boy</th>
                    <th style="width: 160px;">Send Message</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order) :
                    // Items delivered info
                    $items_html = '-';
                    if ( isset($all_items[$order->id]) ) {
                        $item_parts = array();
                        foreach ($all_items[$order->id] as $item) {
                            $del = intval($item->delivered_quantity);
                            $tot = intval($item->quantity);
                            $item_parts[] = esc_html($item->service_type) . ': ' . $del . '/' . $tot;
                        }
                        $items_html = implode('<br>', $item_parts);
                    }

                    // Payment amount
                    $amount_html = '-';
                    if ( $order->total_bill_amount > 0 || $order->amount_received > 0 ) {
                        $amount_html = '₹' . number_format($order->amount_received, 0) . ' / ₹' . number_format($order->total_bill_amount, 0);
                        if ( $order->balance_due > 0 ) {
                            $amount_html .= '<br>Due: ₹' . number_format($order->balance_due, 0);
                        } else {
                            $amount_html .= '<br>Paid';
                        }
                    }

                    // WhatsApp message
                    $payment_line = "Payment Status: " . $order->payment_status;
                    if ( $order->total_bill_amount > 0 ) {
                        $payment_line .= " (Due: ₹" . number_format($order->balance_due, 0) . ")";
                    }

                    $wa_msg = "Hello " . $order->customer_name . ",\nYour laundry delivery is scheduled for today (" . date_i18n('d M Y', strtotime($today)) . ").\n" . $payment_line . "\nToken: " . $order->token_id . "\nThank you! — Laugh-O-Laundry\n⭐ Rate your experience & leave a review:\n" . lol_review_url( $order->token_id ) . "\nHave a great day!";
                    $encoded_msg = rawurlencode($wa_msg);
                ?>
                <tr>
                    <td><strong><?php echo esc_html($order->token_id); ?></strong></td>
                    <td><?php echo esc_html($order->customer_name); ?></td>
                    <td><?php echo esc_html($order->phone_number); ?></td>
                    <td><?php echo esc_html($order->pickup_date); ?></td>
                    <td><?php echo esc_html($order->delivery_date); ?></td>
                    <td><?php echo lol_status_badge($order->order_status); ?></td>
                    <td><?php echo lol_payment_badge($order->payment_status); ?></td>
                    <td class="lol-amount-col"><?php echo $amount_html; ?></td>
                    <td class="lol-items-detail"><?php echo $items_html; ?></td>
                    <td><?php echo esc_html($order->delivery_boy ? $order->delivery_boy : ($order->pickup_agent_name ? $order->pickup_agent_name : 'Not Assigned')); ?></td>
                    <td>
                        <a href="javascript:void(0)" class="lol-wa-btn" onclick="logWaSend(<?php echo $order->id; ?>, '<?php echo esc_js($order->phone_number); ?>', '<?php echo $encoded_msg; ?>', this); return false;">
                            📱 Send Message
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    <?php
}

function lol_admin_export_page() {
    ?>
    <div class="wrap">
        <h1>Export to Excel</h1>
        <p>Click the button below to export all orders to an Excel (.xlsx) file.</p>
        <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
            <input type="hidden" name="action" value="lol_export_excel">
            <?php wp_nonce_field('lol_export_excel_action', 'lol_export_excel_nonce'); ?>
            <button type="submit" class="button button-primary button-hero">Export All Orders</button>
        </form>
    </div>
    <?php
}

function lol_admin_main_excel_page() {
    $excel_url = LOL_THEME_URI . '/Laugh-O-Laundry  Customer Sheet .xlsx';
    ?>
    <div class="wrap">
        <h1>Main Excel Sheet (Editable)</h1>
        <p>Displaying contents of Laugh-O-Laundry Customer Sheet.</p>
        <div style="margin-bottom: 15px;">
            <button id="btn-add-row" class="button button-secondary">Add New Row</button>
            <button id="btn-save-excel" class="button button-primary">Save Changes to Excel</button>
            <span id="save-excel-msg" style="margin-left: 10px; font-weight: bold;"></span>
        </div>
        <div id="lol-excel-container">
            <p>Loading Excel Data...</p>
        </div>
    </div>
    <style>
        #lol-excel-table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
            background: #fff;
        }
        #lol-excel-table th, #lol-excel-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        #lol-excel-table th {
            background-color: #f2f2f2;
        }
        #lol-excel-table td[contenteditable="true"]:hover {
            background-color: #f9f9f9;
            cursor: text;
        }
        #lol-excel-table td[contenteditable="true"]:focus {
            outline: 2px solid #2271b1;
            background-color: #fff;
        }
    </style>
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var url = "<?php echo esc_url($excel_url); ?>";
        var currentSheetName = 'June 2026';
        
        fetch(url + '?t=' + new Date().getTime()) // prevent caching
            .then(function(res) { 
                if (!res.ok) throw new Error("Fetch failed");
                return res.arrayBuffer(); 
            })
            .then(function(ab) {
                var wb = XLSX.read(ab, {type: "array"});
                var targetSheetName = wb.SheetNames.find(function(name) {
                    return name.toLowerCase() === 'june 2026';
                });
                
                if (!targetSheetName) {
                    document.getElementById('lol-excel-container').innerHTML = "<p style='color:red;'>Error: 'June 2026' sheet not found in the Excel file.</p>";
                    return;
                }
                
                currentSheetName = targetSheetName;
                var ws = wb.Sheets[targetSheetName];
                var html = XLSX.utils.sheet_to_html(ws, { id: "lol-excel-table" });
                document.getElementById('lol-excel-container').innerHTML = html;

                // Make cells editable
                makeTableEditable();
            })
            .catch(function(err) {
                document.getElementById('lol-excel-container').innerHTML = "<p style='color:red;'>Error loading Excel file: " + err.message + "</p>";
            });

        function makeTableEditable() {
            var table = document.getElementById('lol-excel-table');
            if (!table) return;
            var tds = table.getElementsByTagName('td');
            for (var i = 0; i < tds.length; i++) {
                tds[i].setAttribute('contenteditable', 'true');
            }
        }

        document.getElementById('btn-add-row').addEventListener('click', function() {
            var table = document.getElementById('lol-excel-table');
            if (!table) return;
            
            var tbody = table.querySelector('tbody') || table;
            var rows = table.getElementsByTagName('tr');
            if (rows.length === 0) return;
            
            var colCount = rows[0].children.length;
            var newRow = document.createElement('tr');
            for (var i = 0; i < colCount; i++) {
                var newTd = document.createElement('td');
                newTd.setAttribute('contenteditable', 'true');
                newRow.appendChild(newTd);
            }
            tbody.appendChild(newRow);
        });

        document.getElementById('btn-save-excel').addEventListener('click', function() {
            var table = document.getElementById('lol-excel-table');
            if (!table) return;
            
            var msgEl = document.getElementById('save-excel-msg');
            msgEl.textContent = "Saving...";
            msgEl.style.color = "#2271b1";
            
            try {
                var newWs = XLSX.utils.table_to_sheet(table);
                var newWb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(newWb, newWs, currentSheetName);
                
                var b64 = XLSX.write(newWb, {bookType:'xlsx', type:'base64'});
                
                var formData = new FormData();
                formData.append('action', 'lol_save_excel_file');
                formData.append('excel_base64', b64);
                
                fetch(ajaxurl, {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        msgEl.textContent = "Saved successfully!";
                        msgEl.style.color = "green";

                        // After save, sync delivery dates to DB
                        syncDeliveryDatesToDb(table);

                        setTimeout(() => msgEl.textContent = "", 3000);
                    } else {
                        msgEl.textContent = "Error: " + (res.data ? res.data.message : 'Failed to save.');
                        msgEl.style.color = "red";
                    }
                })
                .catch(err => {
                    msgEl.textContent = "Request failed.";
                    msgEl.style.color = "red";
                });
            } catch (e) {
                msgEl.textContent = "Error generating Excel.";
                msgEl.style.color = "red";
                console.error(e);
            }
        });

        /**
         * After Excel save, scan for delivery dates and sync them to the DB.
         */
        function syncDeliveryDatesToDb(table) {
            var rows = table.querySelectorAll('tr');
            if (rows.length < 2) return;

            // Find header indices
            var headers = [];
            var headerCells = rows[0].querySelectorAll('th, td');
            headerCells.forEach(function(cell) {
                headers.push(cell.textContent.trim().replace(/[^a-z0-9]/gi, '').toLowerCase());
            });

            var idxToken = headers.indexOf('tokenid');
            var idxDelDate = headers.findIndex(function(h) { return h === 'deliverydate'; });
            var idxName = headers.indexOf('name');
            
            if (idxToken === -1 || idxDelDate === -1) return;

            // Collect rows with delivery dates
            var updates = [];
            for (var i = 1; i < rows.length; i++) {
                var cells = rows[i].querySelectorAll('td');
                if (cells.length <= Math.max(idxToken, idxDelDate)) continue;
                
                var tokenVal = cells[idxToken] ? cells[idxToken].textContent.trim() : '';
                var delDateVal = cells[idxDelDate] ? cells[idxDelDate].textContent.trim() : '';
                var nameVal = (idxName !== -1 && cells[idxName]) ? cells[idxName].textContent.trim() : '';

                if (tokenVal && delDateVal) {
                    updates.push({
                        token_id: tokenVal,
                        delivery_date: delDateVal,
                        customer_name: nameVal
                    });
                }
            }

            if (updates.length === 0) return;

            // Send to backend to sync delivery dates
            var formData = new FormData();
            formData.append('action', 'lol_sync_delivery_dates');
            formData.append('updates', JSON.stringify(updates));

            fetch(ajaxurl, { method: 'POST', body: formData })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success && res.data.synced > 0) {
                        console.log('Synced ' + res.data.synced + ' delivery dates to DB.');
                    }
                })
                .catch(function(e) { console.error('Sync error:', e); });
        }
    });

        // End of excel processing logic
    
    </script>
    <?php
}

function lol_admin_whatsapp_logs_page() {
    global $wpdb;
    $logs_table = $wpdb->prefix . 'laundry_whatsapp_logs';

    $logs = $wpdb->get_results("SELECT * FROM $logs_table ORDER BY created_at DESC LIMIT 500");

    lol_admin_page_styles();
    ?>
    <div class="wrap">
        <h1>SMS Logs</h1>
        <p>This page shows all the SMS messages that were sent via Twilio.</p>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Date / Time</th>
                    <th>Order ID</th>
                    <th>Phone Number</th>
                    <th>Message Text</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($logs) : foreach($logs as $log) : ?>
                <tr>
                    <td><?php echo esc_html(date_i18n('d M Y, h:i A', strtotime($log->created_at))); ?></td>
                    <td><?php echo esc_html($log->order_id); ?></td>
                    <td><?php echo esc_html($log->phone_number); ?></td>
                    <td><pre style="white-space: pre-wrap; margin:0; font-family: inherit; font-size:12px;"><?php echo esc_html($log->message); ?></pre></td>
                    <td><span class="lol-badge lol-badge-paid"><?php echo esc_html($log->status); ?></span></td>
                </tr>
                <?php endforeach; else : ?>
                <tr><td colspan="5">No logs found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

function lol_admin_payment_collection_page() {
    global $wpdb;
    $payments_table = $wpdb->prefix . 'payment_collections';

    // Handle authentication state
    $is_authenticated = false;
    if ( isset($_POST['payment_auth_password']) && $_POST['payment_auth_password'] === 'admin123' ) {
        $is_authenticated = true;
    }

    lol_admin_page_styles();

    if ( ! $is_authenticated ) {
        ?>
        <div class="wrap" style="max-width: 400px; margin-top: 50px;">
            <div style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <h2 style="margin-top: 0; text-align: center;">Secure Access</h2>
                <p style="text-align: center; color: #6b7280; margin-bottom: 20px;">Please enter the admin password to access Payment Collections.</p>
                <form method="post">
                    <div style="margin-bottom: 15px;">
                        <input type="password" name="payment_auth_password" placeholder="Enter password" style="width: 100%; padding: 10px; font-size: 16px;" required>
                    </div>
                    <?php if ( isset($_POST['payment_auth_password']) ) : ?>
                        <p style="color: #dc2626; font-size: 13px; text-align: center;">Incorrect password. Please try again.</p>
                    <?php endif; ?>
                    <button type="submit" class="button button-primary" style="width: 100%; padding: 10px; font-size: 16px; height: auto;">Unlock Dashboard</button>
                </form>
            </div>
        </div>
        <?php
        return;
    }

    // Dashboard Statistics
    $total_revenue = $wpdb->get_var("SELECT SUM(amount) FROM $payments_table WHERE verification_status = 'Verified'") ?: 0;
    $collected_by_boys = $wpdb->get_var("SELECT SUM(amount) FROM $payments_table") ?: 0;
    $verified_by_admin = $total_revenue;
    $pending_verification = $wpdb->get_var("SELECT SUM(amount) FROM $payments_table WHERE verification_status = 'Pending'") ?: 0;
    $todays_collection = $wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM $payments_table WHERE DATE(collection_date) = %s", current_time('Y-m-d'))) ?: 0;

    // Fetch pending collections
    $pending_list = $wpdb->get_results("SELECT * FROM $payments_table WHERE verification_status = 'Pending' ORDER BY collection_date DESC");

    ?>
    <div class="wrap">
        <h1>Payment Collection Dashboard</h1>

        <div class="lol-stat-cards" style="flex-wrap: wrap;">
            <div class="lol-stat-card">
                <h3>Total Revenue</h3>
                <p class="stat-value" style="color:#16a34a;">₹<?php echo number_format($total_revenue, 0); ?></p>
            </div>
            <div class="lol-stat-card">
                <h3>Collected by Delivery Boys</h3>
                <p class="stat-value">₹<?php echo number_format($collected_by_boys, 0); ?></p>
            </div>
            <div class="lol-stat-card">
                <h3>Verified by Admin</h3>
                <p class="stat-value" style="color:#2563eb;">₹<?php echo number_format($verified_by_admin, 0); ?></p>
            </div>
            <div class="lol-stat-card">
                <h3>Pending Verification</h3>
                <p class="stat-value" style="color:#d97706;">₹<?php echo number_format($pending_verification, 0); ?></p>
            </div>
            <div class="lol-stat-card">
                <h3>Today's Collection</h3>
                <p class="stat-value">₹<?php echo number_format($todays_collection, 0); ?></p>
            </div>
        </div>

        <h2 style="margin-top: 40px;">Pending Collections</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Token ID</th>
                    <th>Delivery Boy</th>
                    <th>Payment Mode</th>
                    <th>Amount</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pending_list) : foreach($pending_list as $pl) : ?>
                <tr id="payment-row-<?php echo esc_attr($pl->id); ?>">
                    <td><?php echo esc_html(date_i18n('d M Y, h:i A', strtotime($pl->collection_date))); ?></td>
                    <td><strong><?php echo esc_html($pl->token_id); ?></strong></td>
                    <td><?php echo esc_html($pl->delivery_boy_id ?: 'Unknown'); ?></td>
                    <td><?php echo esc_html($pl->payment_mode); ?></td>
                    <td style="font-weight:bold; color:#166534;">₹<?php echo number_format($pl->amount, 0); ?></td>
                    <td>
                        <button type="button" class="button button-primary btn-verify-payment" data-id="<?php echo esc_attr($pl->id); ?>">
                            ☑ Verify
                        </button>
                    </td>
                </tr>
                <?php endforeach; else : ?>
                <tr><td colspan="6">No pending collections.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- We need to keep the session alive on page reload, so pass it silently or just let them stay until refresh -->
        <form id="refresh-auth-form" method="post" style="display:none;">
            <input type="hidden" name="payment_auth_password" value="admin123">
        </form>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var verifyBtns = document.querySelectorAll('.btn-verify-payment');
            verifyBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var paymentId = this.getAttribute('data-id');
                    if (!confirm('Have you physically verified this amount?')) return;
                    
                    var oldText = this.textContent;
                    this.textContent = 'Verifying...';
                    this.disabled = true;

                    var fd = new FormData();
                    fd.append('action', 'lol_verify_payment');
                    fd.append('payment_id', paymentId);

                    fetch(ajaxurl, { method: 'POST', body: fd })
                        .then(r => r.json())
                        .then(res => {
                            if(res.success) {
                                document.getElementById('refresh-auth-form').submit(); // Refresh to update stats
                            } else {
                                alert('Error: ' + res.data.message);
                                this.textContent = oldText;
                                this.disabled = false;
                            }
                        })
                        .catch(e => {
                            alert('Network error.');
                            this.textContent = oldText;
                            this.disabled = false;
                        });
                });
            });
        });
        </script>
    </div>
    <?php
}

function lol_admin_all_pickups_page() {
    global $wpdb;
    $orders_table = $wpdb->prefix . 'laundry_orders';
    $items_table = $wpdb->prefix . 'laundry_order_items';

    // Fetch all orders
    $orders = $wpdb->get_results("SELECT * FROM $orders_table ORDER BY id DESC");

    // Fetch items to calculate total clothes and check for urgent items
    $order_ids = array_map(function($o) { return intval($o->id); }, $orders);
    $all_items = array();
    if ( ! empty($order_ids) ) {
        $ids_str = implode(',', $order_ids);
        $items_rows = $wpdb->get_results("SELECT * FROM $items_table WHERE order_id IN ($ids_str)");
        foreach ($items_rows as $item) {
            $all_items[$item->order_id][] = $item;
        }
    }

    lol_admin_page_styles();
    ?>
    <div class="wrap">
        <h1>📋 All Pickups</h1>
        <p>Overview of all pickups including total clothes and urgent item requests.</p>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 140px;">Token ID</th>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Pickup Date</th>
                    <th>Status</th>
                    <th>Total Clothes</th>
                    <th>Urgent Needs</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders) : foreach($orders as $order) :
                    $total_clothes = 0;
                    $urgent_info = array();
                    
                    if ( isset($all_items[$order->id]) ) {
                        foreach ( $all_items[$order->id] as $item ) {
                            $total_clothes += intval($item->quantity);
                            if ( $item->is_urgent == 1 ) {
                                $urgent_qty_text = intval($item->urgent_quantity) > 0 ? "Qty: " . intval($item->urgent_quantity) : 'All';
                                $urgent_date = $item->urgent_delivery_date ? esc_html($item->urgent_delivery_date) : 'No Date';
                                $urgent_info[] = "<span style='color: #dc2626; font-weight: 600;'>Yes (" . esc_html($item->service_type) . " - " . $urgent_qty_text . " - " . $urgent_date . ")</span>";
                            }
                        }
                    }
                    
                    if ( empty($urgent_info) ) {
                        $urgent_html = "No";
                    } else {
                        $urgent_html = implode("<br>", $urgent_info);
                    }
                ?>
                <tr>
                    <td><strong><?php echo esc_html($order->token_id); ?></strong></td>
                    <td><?php echo esc_html($order->customer_name); ?></td>
                    <td><?php echo esc_html($order->phone_number); ?></td>
                    <td><?php echo esc_html($order->pickup_date); ?></td>
                    <td><?php echo lol_status_badge($order->order_status); ?></td>
                    <td style="font-weight: bold;"><?php echo $total_clothes; ?></td>
                    <td><?php echo $urgent_html; ?></td>
                </tr>
                <?php endforeach; else : ?>
                <tr><td colspan="7">No pickups found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

function lol_admin_settings_page() {
    // Handle form submission
    if ( isset($_POST['lol_settings_nonce']) && wp_verify_nonce($_POST['lol_settings_nonce'], 'lol_save_settings') ) {
        update_option('twilio_account_sid', sanitize_text_field($_POST['twilio_account_sid']));
        update_option('twilio_auth_token', sanitize_text_field($_POST['twilio_auth_token']));
        update_option('twilio_whatsapp_number', sanitize_text_field($_POST['twilio_whatsapp_number']));
        echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully.</p></div>';
    }

    $sid = get_option('twilio_account_sid', '');
    $token = get_option('twilio_auth_token', '');
    $number = get_option('twilio_whatsapp_number', '');

    lol_admin_page_styles();
    ?>
    <div class="wrap">
        <h1>⚙️ Twilio Settings</h1>
        <p>Enter your Twilio API credentials to enable centralized SMS messaging.</p>
        
        <form method="post" action="">
            <?php wp_nonce_field('lol_save_settings', 'lol_settings_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="twilio_account_sid">Twilio Account SID</label></th>
                    <td>
                        <input name="twilio_account_sid" type="text" id="twilio_account_sid" value="<?php echo esc_attr($sid); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="twilio_auth_token">Twilio Auth Token</label></th>
                    <td>
                        <input name="twilio_auth_token" type="password" id="twilio_auth_token" value="<?php echo esc_attr($token); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="twilio_whatsapp_number">Twilio SMS Number</label></th>
                    <td>
                        <input name="twilio_whatsapp_number" type="text" id="twilio_whatsapp_number" value="<?php echo esc_attr($number); ?>" class="regular-text" placeholder="e.g. +18129955938">
                        <p class="description">Enter your Twilio sender phone number.</p>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
            </p>
        </form>
    </div>
    <?php
}

function lol_admin_partial_delivery_check_page() {
    global $wpdb;
    $orders_table = $wpdb->prefix . 'laundry_orders';
    $items_table = $wpdb->prefix . 'laundry_order_items';

    $token_id = isset($_POST['token_id']) ? sanitize_text_field($_POST['token_id']) : '';
    $order = null;
    $items = array();

    if (!empty($token_id)) {
        if (strlen($token_id) === 4 && is_numeric($token_id)) {
            $order = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $orders_table WHERE token_id LIKE %s ORDER BY id DESC LIMIT 1",
                '%-' . $token_id
            ));
        } else {
            $order = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $orders_table WHERE token_id = %s",
                $token_id
            ));
        }

        if ($order) {
            $items = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $items_table WHERE order_id = %d",
                $order->id
            ));
        }
    }

    lol_admin_page_styles();
    ?>
    <div class="wrap">
        <h1>🔍 Partial Delivery Checking Center</h1>
        <p>Enter a Token ID to check the remaining items for delivery.</p>

        <form method="post" style="margin-bottom: 20px; background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #e5e7eb; display: inline-block;">
            <label for="token_id" style="font-weight: bold; margin-right: 10px;">Token ID or last 4 digits:</label>
            <input type="text" name="token_id" id="token_id" value="<?php echo esc_attr($token_id); ?>" style="padding: 6px; width: 250px;">
            <button type="submit" class="button button-primary">Check Items</button>
        </form>

        <?php if (!empty($token_id)) : ?>
            <?php if ($order) : ?>
                <div style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #e5e7eb; max-width: 600px;">
                    <h2 style="margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #eee;">Order Details: <strong><?php echo esc_html($order->token_id); ?></strong></h2>
                    <p><strong>Customer:</strong> <?php echo esc_html($order->customer_name); ?></p>
                    <p><strong>Status:</strong> <?php echo lol_status_badge($order->order_status); ?></p>
                    <p><strong>Pickup Date:</strong> <?php echo esc_html($order->pickup_date); ?></p>

                    <h3 style="margin-top: 20px;">Remaining Items to Deliver</h3>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Service Type</th>
                                <th>Total Qty</th>
                                <th>Delivered Qty</th>
                                <th>Remaining Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $has_remaining = false;
                            foreach ($items as $item) : 
                                $remaining = intval($item->quantity) - intval($item->delivered_quantity);
                                if ($remaining > 0) :
                                    $has_remaining = true;
                            ?>
                                <tr>
                                    <td><?php echo esc_html($item->service_type); ?></td>
                                    <td><?php echo intval($item->quantity); ?></td>
                                    <td><?php echo intval($item->delivered_quantity); ?></td>
                                    <td style="color: #dc2626; font-weight: bold;"><?php echo $remaining; ?></td>
                                </tr>
                            <?php endif; endforeach; ?>
                            
                            <?php if (!$has_remaining) : ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #16a34a; font-weight: bold;">All items have been delivered!</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <div style="background: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 15px; border-radius: 5px; display: inline-block;">
                    Order not found. Please verify the Token ID.
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
}
