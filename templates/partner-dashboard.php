<div class="lol-dashboard-container">
    
    <div class="lol-header">
        <h1>Laugh-O-Laundry</h1>
        <div class="lol-user-info">
            <?php 
                $current_user = wp_get_current_user();
                echo esc_html( $current_user->display_name ); 
            ?>
            <a href="<?php echo wp_logout_url( home_url() ); ?>" class="lol-logout">Logout</a>
        </div>
    </div>

    <!-- Main Buttons Screen -->
    <div id="lol-main-menu" class="lol-view active-view">
        <button id="btn-show-pickup" class="lol-main-btn lol-btn-pickup">
            <span class="icon">🧺</span>
            PICK UP
        </button>
        
        <button id="btn-show-delivery" class="lol-main-btn lol-btn-delivery">
            <span class="icon">🚚</span>
            DELIVERY
        </button>

        <button id="btn-show-edit" class="lol-main-btn lol-btn-edit">
            <span class="icon">✏️</span>
            EDIT ORDER
        </button>

    </div>

    <!-- Pickup View -->
    <div id="lol-pickup-view" class="lol-view">
        <button class="lol-back-btn">← Back</button>
        <h2>New Pickup</h2>
        <?php get_template_part( 'templates/pickup', 'form' ); ?>
    </div>

    <!-- Delivery View -->
    <div id="lol-delivery-view" class="lol-view">
        <button class="lol-back-btn">← Back</button>
        <h2>Delivery</h2>
        <?php get_template_part( 'templates/delivery', 'form' ); ?>
    </div>
    <!-- Edit View -->
    <div id="lol-edit-view" class="lol-view">
        <button class="lol-back-btn">← Back</button>
        <h2>Edit Order</h2>
        <div id="lol-edit-auth">
            <div class="lol-form-group">
                <label for="edit_search_token">Token ID</label>
                <input type="text" id="edit_search_token" placeholder="LOL-YYYYMMDD-XXXX">
            </div>
            <div class="lol-form-group">
                <label for="edit_password">Admin Password</label>
                <input type="password" id="edit_password" placeholder="Enter password">
            </div>
            <button id="btn-search-edit" class="lol-btn-primary">Verify & Search</button>
            <div id="edit-auth-message" class="lol-message"></div>
        </div>

        <form id="lol-edit-form" style="display: none;">
            <input type="hidden" id="edit_token_id" name="token_id">
            <input type="hidden" id="edit_verified_password" name="password">
            
            <div class="lol-order-details">
                <h3>Order Details</h3>
                <p><strong>Customer:</strong> <span id="edit_detail_name"></span></p>
            </div>

            <div class="lol-items-section">
                <h3>Edit Laundry Items</h3>
                <div id="lol-edit-items-container"></div>
                <button type="button" id="btn-edit-add-item" class="lol-btn-secondary">+ Add Item</button>
            </div>

            <button type="submit" id="btn-submit-edit" class="lol-btn-primary">UPDATE ORDER</button>
            <div id="edit-message" class="lol-message"></div>
        </form>
    </div>

    <!-- All Pickups View has been removed -->

</div>
