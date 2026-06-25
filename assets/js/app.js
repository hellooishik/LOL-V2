jQuery(document).ready(function($) {

    // --- View Navigation --- //
    $('#btn-show-pickup').click(function() {
        $('.lol-view').removeClass('active-view');
        $('#lol-pickup-view').addClass('active-view');
    });

    $('#btn-show-delivery').click(function() {
        $('.lol-view').removeClass('active-view');
        $('#lol-delivery-view').addClass('active-view');
    });

    $('#btn-show-edit').click(function() {
        $('.lol-view').removeClass('active-view');
        $('#lol-edit-view').addClass('active-view');
    });

    $('.lol-back-btn').click(function() {
        $('.lol-view').removeClass('active-view');
        $('#lol-main-menu').addClass('active-view');
        // Reset forms when going back
        $('#lol-pickup-form')[0].reset();
        $('#lol-delivery-form').hide();
        $('#lol-delivery-search').show();
        $('#lol-edit-form').hide();
        $('#lol-edit-auth').show();
        $('#edit_search_token').val('');
        $('#edit_password').val('');
        $('.lol-message').removeClass('error success').text('').hide();
    });

    // --- Pickup Form Logic --- //
    function updateTotalItemsCount() {
        let total = 0;
        $('#lol-items-container .lol-qty-input').each(function() {
            let val = parseInt($(this).val());
            if (!isNaN(val)) {
                total += val;
            }
        });
        $('#lol-total-items-count').text(total);
    }

    $(document).on('input', '.lol-qty-input', function() {
        updateTotalItemsCount();
    });

    $(document).on('change', '.lol-urgent-checkbox', function() {
        if ($(this).is(':checked')) {
            $(this).closest('.lol-item-urgent-row').find('.lol-urgent-date').show().prop('required', true);
        } else {
            $(this).closest('.lol-item-urgent-row').find('.lol-urgent-date').hide().prop('required', false).val('');
        }
    });

    let itemIndex = 1;

    $('#btn-add-item').click(function() {
        let newRow = `
            <div class="lol-item-row-wrapper" style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; border-radius: 5px;">
                <div class="lol-item-row">
                    <div class="lol-item-col">
                        <label>Qty</label>
                        <input type="number" name="items[${itemIndex}][quantity]" min="1" required class="lol-qty-input">
                    </div>
                    <div class="lol-item-col lol-flex-grow">
                        <label>Service Type</label>
                        <select name="items[${itemIndex}][service_type]" required class="lol-service-select">
                            <option value="">Select Service</option>
                            <option value="Wash and fold">Wash and fold</option>
                            <option value="Wash and iron">Wash and iron</option>
                            <option value="Dry clean">Dry clean</option>
                            <option value="Stain removal">Stain removal</option>
                            <option value="Iron and pressing">Iron and pressing</option>
                        </select>
                    </div>
                    <div class="lol-item-col lol-remove-col">
                        <button type="button" class="lol-remove-item">&times;</button>
                    </div>
                </div>
                <div class="lol-item-urgent-row" style="margin-top: 8px; display: flex; align-items: center; gap: 10px;">
                    <label style="margin: 0; font-weight: normal;"><input type="checkbox" name="items[${itemIndex}][is_urgent]" value="1" class="lol-urgent-checkbox"> Urgent Need</label>
                    <input type="date" name="items[${itemIndex}][urgent_delivery_date]" class="lol-urgent-date" style="display:none; padding: 5px;">
                </div>
            </div>
        `;
        $('#lol-items-container').append(newRow);
        itemIndex++;
        updateTotalItemsCount();
    });

    $(document).on('click', '.lol-remove-item', function() {
        let wrapper = $(this).closest('.lol-item-row-wrapper');
        if (wrapper.length) {
            wrapper.remove();
        } else {
            $(this).closest('.lol-item-row').remove();
        }
        updateTotalItemsCount();
    });

    $('#lol-pickup-form').submit(function(e) {
        e.preventDefault();
        let $btn = $('#btn-submit-pickup');
        let $msg = $('#pickup-message');
        
        $btn.prop('disabled', true).text('SAVING...');
        $msg.removeClass('error success').text('').hide();

        let formData = $(this).serialize() + '&action=lol_save_pickup&nonce=' + lol_ajax_obj.nonce;

        $.post(lol_ajax_obj.ajax_url, formData, function(response) {
            if (response.success) {
                // Show success screen
                $('#lol-pickup-form').hide();
                $('#lol-pickup-success').show();
                
                let data = response.data;
                $('#success-token').text(data.token_id);
                $('#success-name').text(data.customer_name);
                $('#success-phone').text(data.phone_number);
                $('#success-date').text(data.pickup_date);

                let totalClothes = 0;
                if(data.items && Array.isArray(data.items)) {
                     data.items.forEach(item => totalClothes += parseInt(item.quantity) || 0);
                } else if(data.items) {
                     Object.values(data.items).forEach(item => totalClothes += parseInt(item.quantity) || 0);
                }
                $('#success-total-clothes').text(totalClothes);
                
                // Set WhatsApp Send button logic
                let waMessage = `Hello ${data.customer_name},\n\nWe have successfully picked up your clothes.\nYour order is now being processed and is expected to be delivered within 3–4 days.\n\nThank you for choosing our laundry service.\n\nToken: ${data.token_id}`;
                $('#btn-wa-pickup').off('click').on('click', function() {
                    openWhatsAppAndLog(data.order_id || 0, data.phone_number, waMessage);
                });

                // Update Excel in background
                updateExcelWithOrder('pickup', data);
            } else {
                $msg.addClass('error').text(response.data.message).show();
                $btn.prop('disabled', false).text('SAVE PICKUP');
            }
        }).fail(function() {
            $msg.addClass('error').text('An error occurred. Please try again.').show();
            $btn.prop('disabled', false).text('SAVE PICKUP');
        });
    });

    $('#btn-copy-token').click(function() {
        let token = $('#success-token').text();
        navigator.clipboard.writeText(token).then(function() {
            alert('Token copied to clipboard!');
        });
    });

    $('#btn-new-pickup').click(function() {
        $('#lol-pickup-success').hide();
        $('#lol-pickup-form')[0].reset();
        
        // Reset items to just one row
        $('#lol-items-container').children('.lol-item-row-wrapper:not(:first)').remove();
        $('.lol-urgent-date').hide(); // hide all dates
        updateTotalItemsCount(); // update to 0
        
        $('#btn-submit-pickup').prop('disabled', false).text('SAVE PICKUP');
        $('#lol-pickup-form').show();
    });


    // --- Delivery Form Logic --- //
    $('#btn-search-token').click(function() {
        let token = $('#search_token').val().trim();
        let $msg = $('#search-message');
        let $btn = $(this);

        if (!token) {
            $msg.addClass('error').text('Please enter a Token ID or last 4 digits.').show();
            return;
        }

        $btn.prop('disabled', true).text('Searching...');
        $msg.removeClass('error success').text('').hide();

        $.post(lol_ajax_obj.ajax_url, {
            action: 'lol_search_token',
            token_id: token,
            nonce: lol_ajax_obj.nonce
        }, function(response) {
            if (response.success) {
                let order = response.data.order;
                let items = response.data.items;

                $('#delivery_token_id').val(order.token_id);
                $('#customer_phone_hidden').val(order.phone_number);
                $('#customer_name_hidden').val(order.customer_name);
                
                $('#detail_name').text(order.customer_name);
                $('#detail_phone').text(order.phone_number);
                $('#detail_address').text(order.address || '-');
                $('#detail_date').text(order.pickup_date);
                $('#detail_status').text(order.order_status);
                
                let amtDueHtml = '-';
                if (parseFloat(order.balance_due) > 0) {
                    amtDueHtml = '₹' + order.balance_due;
                } else if (parseFloat(order.total_bill_amount) > 0) {
                    amtDueHtml = 'Paid (₹' + order.total_bill_amount + ')';
                }
                $('#detail_due').text(amtDueHtml);

                let itemsHtml = '';
                items.forEach(function(item) {
                    let deliveredValue = item.delivered_quantity ? item.delivered_quantity : item.quantity;
                    itemsHtml += `
                        <div class="lol-delivery-item-row" style="display: flex; align-items: center; margin-bottom: 10px; gap: 10px;">
                            <div style="flex: 1;">Picked up: ${item.quantity} x ${item.service_type}</div>
                            <div style="flex: 1;">
                                <label style="margin-right: 5px;">Delivered Qty:</label>
                                <input type="number" name="delivered_items[${item.id}]" value="${deliveredValue}" min="0" max="${item.quantity}" style="width: 80px;" class="lol-qty-input">
                            </div>
                        </div>
                    `;
                });
                $('#detail_items_list').html(itemsHtml);

                $('#lol-delivery-search').hide();
                $('#lol-delivery-form').show();
            } else {
                $msg.addClass('error').text(response.data.message).show();
            }
        }).fail(function() {
            $msg.addClass('error').text('An error occurred.').show();
        }).always(function() {
            $btn.prop('disabled', false).text('Search');
        });
    });

    // Payment status toggle
    $('input[name="payment_status"]').change(function() {
        var val = $(this).val();
        if (val === 'Paid' || val === 'Partial') {
            $('#amount_group').show();
            $('#total_bill_amount').prop('required', true);
            $('#amount_received').prop('required', true);
        } else {
            $('#amount_group').hide();
            $('#total_bill_amount').prop('required', false).val('');
            $('#amount_received').prop('required', false).val('');
            $('#balance_due').val('');
        }
    });

    // Balance due calculation + auto-detect partial
    $('#total_bill_amount, #amount_received').on('input', function() {
        let total = parseFloat($('#total_bill_amount').val()) || 0;
        let received = parseFloat($('#amount_received').val()) || 0;
        let balance = total - received;
        $('#balance_due').val(balance.toFixed(2));

        // Auto-select Partial if received > 0 but less than total
        if (received > 0 && received < total) {
            $('input[name="payment_status"][value="Partial"]').prop('checked', true);
        } else if (received > 0 && received >= total) {
            $('input[name="payment_status"][value="Paid"]').prop('checked', true);
        }
    });

    $('#lol-delivery-form').submit(function(e) {
        e.preventDefault();
        let $btn = $('#btn-submit-delivery');
        let $msg = $('#delivery-message');

        $btn.prop('disabled', true).text('UPDATING...');
        $msg.removeClass('error success').text('').hide();

        let formData = $(this).serialize() + '&action=lol_save_delivery&nonce=' + lol_ajax_obj.nonce;
        let deliveryType = $('input[name="delivery_type"]:checked').val();
        let cName = $('#customer_name_hidden').val();
        let cPhone = $('#customer_phone_hidden').val();
        let tId = $('#delivery_token_id').val();

        $.post(lol_ajax_obj.ajax_url, formData, function(response) {
            if (response.success) {
                // Parse form data for background excel sync
                let formDataArr = $('#lol-delivery-form').serializeArray();
                let orderData = {};
                formDataArr.forEach(item => orderData[item.name] = item.value);
                updateExcelWithOrder('delivery', orderData);

                $msg.addClass('success').text(response.data.message).show();
                
                let waMessage = '';
                if (deliveryType === 'Partial') {
                    waMessage = `Hello ${cName},\n\nA partial delivery of your laundry has been completed today.\nThe remaining items are still being processed and will be delivered on the due date.\n\nThank you for your patience.\nToken: ${tId}`;
                } else {
                    waMessage = `Hello ${cName},\n\nYour laundry order has been successfully delivered.\nThank you for choosing our laundry service.\nWe look forward to serving you again.\nToken: ${tId}`;
                }

                // Show a button to send WhatsApp instead of redirecting immediately
                $msg.html(`Delivery saved successfully! <button type="button" id="btn-wa-delivery" class="lol-btn-secondary" style="margin-top:10px;">Send Delivery WhatsApp</button>`);
                
                $('#btn-wa-delivery').on('click', function() {
                    openWhatsAppAndLog(0, cPhone, waMessage);
                });

                setTimeout(function() {
                    if($('#lol-delivery-view').hasClass('active-view')) {
                        $('.lol-back-btn').click(); // Go back to main menu
                    }
                }, 5000); // Give them 5 seconds to click WhatsApp before auto-back
            } else {
                $msg.addClass('error').text(response.data.message).show();
                $btn.prop('disabled', false).text('MARK AS DELIVERED');
            }
        }).fail(function() {
            $msg.addClass('error').text('An error occurred.').show();
            $btn.prop('disabled', false).text('MARK AS DELIVERED');
        });
    });

    // --- Edit Form Logic --- //
    let editItemIndex = 0;

    $('#btn-search-edit').click(function() {
        let token = $('#edit_search_token').val().trim();
        let password = $('#edit_password').val().trim();
        let $msg = $('#edit-auth-message');
        let $btn = $(this);

        if (!token || !password) {
            $msg.addClass('error').text('Please enter Token ID and Password.').show();
            return;
        }

        $btn.prop('disabled', true).text('Verifying...');
        $msg.removeClass('error success').text('').hide();

        $.post(lol_ajax_obj.ajax_url, {
            action: 'lol_get_order_for_edit',
            token_id: token,
            password: password,
            nonce: lol_ajax_obj.nonce
        }, function(response) {
            if (response.success) {
                let order = response.data.order;
                let items = response.data.items;

                $('#edit_token_id').val(order.token_id);
                $('#edit_verified_password').val(password); // store to resend on save
                $('#edit_detail_name').text(order.customer_name);

                let itemsHtml = '';
                editItemIndex = 0;
                items.forEach(function(item) {
                    itemsHtml += `
                        <div class="lol-item-row-wrapper" style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; border-radius: 5px;">
                            <div class="lol-item-row">
                                <div class="lol-item-col">
                                    <label>Qty</label>
                                    <input type="number" name="items[${editItemIndex}][quantity]" min="1" required class="lol-qty-input" value="${item.quantity}">
                                </div>
                                <div class="lol-item-col lol-flex-grow">
                                    <label>Service Type</label>
                                    <select name="items[${editItemIndex}][service_type]" required class="lol-service-select">
                                        <option value="">Select Service</option>
                                        <option value="Wash and fold" ${item.service_type === 'Wash and fold' ? 'selected' : ''}>Wash and fold</option>
                                        <option value="Wash and iron" ${item.service_type === 'Wash and iron' ? 'selected' : ''}>Wash and iron</option>
                                        <option value="Dry clean" ${item.service_type === 'Dry clean' ? 'selected' : ''}>Dry clean</option>
                                        <option value="Stain removal" ${item.service_type === 'Stain removal' ? 'selected' : ''}>Stain removal</option>
                                        <option value="Iron and pressing" ${item.service_type === 'Iron and pressing' ? 'selected' : ''}>Iron and pressing</option>
                                    </select>
                                </div>
                                <div class="lol-item-col lol-remove-col">
                                    <button type="button" class="lol-remove-item">&times;</button>
                                </div>
                            </div>
                            <div class="lol-item-urgent-row" style="margin-top: 8px; display: flex; align-items: center; gap: 10px;">
                                <label style="margin: 0; font-weight: normal;"><input type="checkbox" name="items[${editItemIndex}][is_urgent]" value="1" class="lol-urgent-checkbox" ${item.is_urgent == 1 ? 'checked' : ''}> Urgent Need</label>
                                <input type="date" name="items[${editItemIndex}][urgent_delivery_date]" class="lol-urgent-date" style="${item.is_urgent == 1 ? '' : 'display:none;'} padding: 5px;" value="${item.urgent_delivery_date || ''}">
                            </div>
                        </div>
                    `;
                    editItemIndex++;
                });
                $('#lol-edit-items-container').html(itemsHtml);

                $('#lol-edit-auth').hide();
                $('#lol-edit-form').show();
            } else {
                $msg.addClass('error').text(response.data.message).show();
            }
        }).fail(function() {
            $msg.addClass('error').text('An error occurred.').show();
        }).always(function() {
            $btn.prop('disabled', false).text('Verify & Search');
        });
    });

    $('#btn-edit-add-item').click(function() {
        let newRow = `
            <div class="lol-item-row-wrapper" style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; border-radius: 5px;">
                <div class="lol-item-row">
                    <div class="lol-item-col">
                        <label>Qty</label>
                        <input type="number" name="items[${editItemIndex}][quantity]" min="1" required class="lol-qty-input">
                    </div>
                    <div class="lol-item-col lol-flex-grow">
                        <label>Service Type</label>
                        <select name="items[${editItemIndex}][service_type]" required class="lol-service-select">
                            <option value="">Select Service</option>
                            <option value="Wash and fold">Wash and fold</option>
                            <option value="Wash and iron">Wash and iron</option>
                            <option value="Dry clean">Dry clean</option>
                            <option value="Stain removal">Stain removal</option>
                            <option value="Iron and pressing">Iron and pressing</option>
                        </select>
                    </div>
                    <div class="lol-item-col lol-remove-col">
                        <button type="button" class="lol-remove-item">&times;</button>
                    </div>
                </div>
                <div class="lol-item-urgent-row" style="margin-top: 8px; display: flex; align-items: center; gap: 10px;">
                    <label style="margin: 0; font-weight: normal;"><input type="checkbox" name="items[${editItemIndex}][is_urgent]" value="1" class="lol-urgent-checkbox"> Urgent Need</label>
                    <input type="date" name="items[${editItemIndex}][urgent_delivery_date]" class="lol-urgent-date" style="display:none; padding: 5px;">
                </div>
            </div>
        `;
        $('#lol-edit-items-container').append(newRow);
        editItemIndex++;
    });

    $('#lol-edit-form').submit(function(e) {
        e.preventDefault();
        let $btn = $('#btn-submit-edit');
        let $msg = $('#edit-message');

        $btn.prop('disabled', true).text('UPDATING...');
        $msg.removeClass('error success').text('').hide();

        let formData = $(this).serialize() + '&action=lol_save_edited_order&nonce=' + lol_ajax_obj.nonce;

        $.post(lol_ajax_obj.ajax_url, formData, function(response) {
            if (response.success) {
                $msg.addClass('success').text(response.data.message).show();
                setTimeout(function() {
                    $('.lol-back-btn').click();
                }, 2000);
            } else {
                $msg.addClass('error').text(response.data.message).show();
                $btn.prop('disabled', false).text('UPDATE ORDER');
            }
        }).fail(function() {
            $msg.addClass('error').text('An error occurred.').show();
            $btn.prop('disabled', false).text('UPDATE ORDER');
        });
    });

    // --- All Pickups View Logic --- //
    $('#btn-show-all-pickups').click(function() {
        $('.lol-view').removeClass('active-view');
        $('#lol-all-pickups-view').addClass('active-view');

        let $body = $('#lol-all-pickups-body');
        $body.html('<tr><td colspan="6" style="text-align:center; padding: 20px;">Loading pickups...</td></tr>');

        $.post(lol_ajax_obj.ajax_url, {
            action: 'lol_get_all_pickups',
            nonce: lol_ajax_obj.nonce
        }, function(response) {
            if (response.success) {
                let pickups = response.data.pickups;
                let html = '';
                if (pickups.length === 0) {
                    html = '<tr><td colspan="6" style="text-align:center; padding: 20px;">No pickups found.</td></tr>';
                } else {
                    pickups.forEach(function(p) {
                        html += `
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 10px;">${p.pickup_date}</td>
                                <td style="padding: 10px;"><strong>${p.token_id}</strong></td>
                                <td style="padding: 10px;">${p.customer_name}</td>
                                <td style="padding: 10px;">${p.phone_number}</td>
                                <td style="padding: 10px;">${p.total_items || 0}</td>
                                <td style="padding: 10px;">${p.order_status}</td>
                            </tr>
                        `;
                    });
                }
                $body.html(html);
            } else {
                $body.html('<tr><td colspan="6" style="text-align:center; padding: 20px; color: red;">Failed to load.</td></tr>');
            }
        });
    });

    // --- Background Excel Updater --- //
    function updateExcelWithOrder(actionType, orderData) {
        if (typeof XLSX === 'undefined') {
            console.error("SheetJS not loaded.");
            return;
        }
        
        fetch(lol_ajax_obj.excel_url + '?t=' + new Date().getTime())
            .then(res => res.arrayBuffer())
            .then(ab => {
                var wb = XLSX.read(ab, {type: "array"});
                var targetSheetName = wb.SheetNames.find(name => name.toLowerCase() === 'june 2026');
                if (!targetSheetName) return;
                
                var ws = wb.Sheets[targetSheetName];
                var data = XLSX.utils.sheet_to_json(ws, {header: 1}); 
                
                // Strip trailing empty rows to prevent appending at row 900+
                while (data.length > 1) {
                    var lastRow = data[data.length - 1];
                    var isEmpty = true;
                    if (lastRow && lastRow.length > 0) {
                        for (var j = 0; j < lastRow.length; j++) {
                            if (lastRow[j] !== undefined && lastRow[j] !== null && lastRow[j].toString().trim() !== '') {
                                isEmpty = false;
                                break;
                            }
                        }
                    }
                    if (isEmpty) {
                        data.pop();
                    } else {
                        break;
                    }
                }
                
                var headers = data[0] || [];
                var getColIdx = (name) => {
                    var normName = name.replace(/[^a-z0-9]/gi, '').toLowerCase();
                    return headers.findIndex(h => h && h.toString().replace(/[^a-z0-9]/gi, '').toLowerCase() === normName);
                };
                
                var idxSl = getColIdx('sl');
                var idxDate = getColIdx('date');
                var idxName = getColIdx('name');
                var idxClothes = getColIdx('noofclothes');
                var idxAmount = getColIdx('amount');
                var idxDelDate = getColIdx('deliverydate');
                var idxDelStatus = getColIdx('deliverystatus');
                var idxToken = getColIdx('tokenid');
                var idxDelPartner = getColIdx('deliverypartnername');
                var idxItems = getColIdx('itemsdetails');
                
                if (actionType === 'pickup') {
                    var maxSl = 0;
                    for (var i=1; i<data.length; i++) {
                        if (idxSl !== -1 && data[i][idxSl]) {
                            var slVal = parseInt(data[i][idxSl]);
                            if (!isNaN(slVal) && slVal > maxSl) maxSl = slVal;
                        }
                    }
                    
                    var newRow = new Array(headers.length).fill('');
                    if (idxSl !== -1) newRow[idxSl] = maxSl + 1;
                    if (idxDate !== -1) newRow[idxDate] = orderData.pickup_date;
                    if (idxName !== -1) newRow[idxName] = orderData.customer_name;
                    
                    var totalClothes = 0;
                    var itemsArr = [];
                    if (orderData.items) {
                        var itemsList = Array.isArray(orderData.items) ? orderData.items : Object.values(orderData.items);
                        itemsList.forEach(item => {
                            totalClothes += parseInt(item.quantity) || 0;
                            itemsArr.push(item.quantity + 'x ' + item.service_type);
                        });
                    }
                    
                    if (idxClothes !== -1) newRow[idxClothes] = totalClothes;
                    if (idxItems !== -1) newRow[idxItems] = itemsArr.join(', ');
                    if (idxToken !== -1) newRow[idxToken] = orderData.token_id;
                    
                    data.push(newRow);
                    
                } else if (actionType === 'delivery') {
                    if (idxToken !== -1) {
                        var rowIndex = data.findIndex(row => row[idxToken] === orderData.token_id);
                        if (rowIndex !== -1) {
                            if (idxDelPartner !== -1) data[rowIndex][idxDelPartner] = orderData.delivery_boy;
                            if (idxDelDate !== -1) data[rowIndex][idxDelDate] = new Date().toISOString().split('T')[0];
                            if (idxDelStatus !== -1) data[rowIndex][idxDelStatus] = 'Delivered';
                            if (idxAmount !== -1 && orderData.amount_received) data[rowIndex][idxAmount] = orderData.amount_received;
                        }
                    }
                }
                
                var newWs = XLSX.utils.aoa_to_sheet(data);
                wb.Sheets[targetSheetName] = newWs;
                
                var b64 = XLSX.write(wb, {bookType:'xlsx', type:'base64'});
                var formData = new FormData();
                formData.append('action', 'lol_save_excel_file');
                formData.append('excel_base64', b64);
                
        fetch(lol_ajax_obj.ajax_url, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(res => { 
                if (!res.success) {
                    console.error('Failed to sync excel:', res); 
                } else {
                    console.log('Successfully background synced excel!');
                }
            })
            .catch(e => console.error('Fetch error:', e));
    })
    .catch(err => console.error('Excel fetch error:', err));
}

// --- WhatsApp Logic --- //
function openWhatsAppAndLog(orderId, phone, message) {
    // Determine number format (ensure 91 prefix)
    let cleanPhone = phone.replace(/[^0-9]/g, '');
    if (cleanPhone.length === 10) cleanPhone = '91' + cleanPhone;

    let waUrl = `https://wa.me/${cleanPhone}?text=${encodeURIComponent(message)}`;
    window.open(waUrl, '_blank');

    // Log to backend
    $.post(lol_ajax_obj.ajax_url, {
        action: 'lol_log_whatsapp',
        nonce: lol_ajax_obj.nonce,
        order_id: orderId,
        phone_number: cleanPhone,
        message: message
    });
}

});
