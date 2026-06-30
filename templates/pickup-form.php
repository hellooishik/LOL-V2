<div class="lol-form-wrapper">
    <form id="lol-pickup-form">
        
        <div class="lol-form-group">
            <label for="pickup_date">Pickup Date</label>
            <input type="text" id="pickup_date" name="pickup_date" value="<?php echo current_time('d F Y'); ?>" readonly>
        </div>

        <div class="lol-form-group">
            <label for="pickup_agent_name">Pickup Agent Name</label>
            <input type="text" id="pickup_agent_name" name="pickup_agent_name" placeholder="Enter agent name">
        </div>

        <div class="lol-form-group">
            <label for="customer_name">Customer Name *</label>
            <input type="text" id="customer_name" name="customer_name" required placeholder="Enter customer name">
        </div>

        <div class="lol-form-group">
            <label for="address">Address *</label>
            <textarea id="address" name="address" required placeholder="Enter complete address" rows="2"></textarea>
        </div>

        <div class="lol-form-group">
            <label for="phone_number">Customer Phone Number *</label>
            <input type="tel" id="phone_number" name="phone_number" pattern="[0-9]{10}" required placeholder="10-digit number">
        </div>

        <div class="lol-items-section">
            <h3>Laundry Items</h3>
            <p style="margin-top: 0;"><strong>Total Items:</strong> <span id="lol-total-items-count">0</span></p>
            
            <div id="lol-items-container">
                <!-- Initial Row -->
                <div class="lol-item-row-wrapper" style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; border-radius: 5px;">
                    <div class="lol-item-row">
                        <div class="lol-item-col">
                            <label>Qty</label>
                            <input type="number" name="items[0][quantity]" min="1" required class="lol-qty-input">
                        </div>
                        <div class="lol-item-col lol-flex-grow">
                            <label>Service Type</label>
                            <select name="items[0][service_type]" required class="lol-service-select">
                                <option value="">Select Service</option>
                                <option value="Wash and fold">Wash and fold</option>
                                <option value="Wash and iron">Wash and iron</option>
                                <option value="Dry clean">Dry clean</option>
                                <option value="Stain removal">Stain removal</option>
                                <option value="Iron and pressing">Iron and pressing</option>
                                <option value="Polish">Polish</option>
                            </select>
                        </div>
                        <div class="lol-item-col lol-remove-col">
                            <button type="button" class="lol-remove-item" style="display:none;">&times;</button>
                        </div>
                    </div>
                    <div class="lol-item-urgent-row" style="margin-top: 8px; display: flex; align-items: center; gap: 10px;">
                        <label style="margin: 0; font-weight: normal;"><input type="checkbox" name="items[0][is_urgent]" value="1" class="lol-urgent-checkbox"> Urgent Need</label>
                        <input type="date" name="items[0][urgent_delivery_date]" class="lol-urgent-date" style="display:none; padding: 5px;">
                    </div>
                </div>
            </div>

            <button type="button" id="btn-add-item" class="lol-btn-secondary">+ Add Item</button>
        </div>

        <button type="submit" id="btn-submit-pickup" class="lol-btn-primary">SAVE PICKUP</button>
        <div id="pickup-message" class="lol-message"></div>
    </form>

    <!-- Success Screen (Hidden initially) -->
    <div id="lol-pickup-success" style="display: none;" class="lol-success-screen">
        <h3>Pickup Saved Successfully!</h3>
        
        <div class="lol-receipt">
            <p><strong>Token ID:</strong> <span id="success-token"></span></p>
            <p><strong>Customer:</strong> <span id="success-name"></span></p>
            <p><strong>Phone:</strong> <span id="success-phone"></span></p>
            <p><strong>Date:</strong> <span id="success-date"></span></p>
            <p><strong>Total Clothes:</strong> <span id="success-total-clothes"></span></p>
        </div>

        <div class="lol-success-actions">
            <button id="btn-copy-token" class="lol-btn-secondary">Copy Token</button>
            <button id="btn-wa-pickup" class="lol-btn-secondary" style="background-color: #25D366; color: white; border-color: #25D366;">SMS Confirm</button>
            <button id="btn-new-pickup" class="lol-btn-primary">New Pickup</button>
        </div>
    </div>
</div>
