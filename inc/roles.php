<?php
/**
 * Custom Roles
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function lol_add_delivery_partner_role() {
    // Only read capability, they won't even use wp-admin
    add_role(
        'delivery_partner',
        __( 'Delivery Partner', 'lol-delivery' ),
        array(
            'read' => true,
        )
    );
}

// Ensure the role is added on theme initialization if it doesn't exist
add_action( 'init', function() {
    if ( ! get_role( 'delivery_partner' ) ) {
        lol_add_delivery_partner_role();
    }
} );
