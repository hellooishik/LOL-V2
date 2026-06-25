<?php
/**
 * Database table creation
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function lol_create_custom_tables() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $table_orders = $wpdb->prefix . 'laundry_orders';
    $table_items = $wpdb->prefix . 'laundry_order_items';
    $table_logs = $wpdb->prefix . 'laundry_whatsapp_logs';
    $table_payment_collections = $wpdb->prefix . 'payment_collections';
    $table_partial_deliveries = $wpdb->prefix . 'partial_deliveries';

    $sql_orders = "CREATE TABLE $table_orders (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        token_id varchar(50) NOT NULL,
        customer_name varchar(255) NOT NULL,
        phone_number varchar(20) NOT NULL,
        address text NULL,
        pickup_date date NOT NULL,
        pickup_agent_name varchar(255) NULL,
        delivery_date date NULL,
        delivery_boy varchar(255) NULL,
        payment_status varchar(20) DEFAULT 'Unpaid' NOT NULL,
        payment_mode varchar(50) NULL,
        total_bill_amount decimal(10,2) NULL,
        amount_received decimal(10,2) NULL,
        balance_due decimal(10,2) NULL,
        order_status varchar(50) DEFAULT 'Picked Up' NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY token_id (token_id)
    ) $charset_collate;";

    $sql_items = "CREATE TABLE $table_items (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        order_id bigint(20) NOT NULL,
        quantity int(11) NOT NULL,
        delivered_quantity int(11) DEFAULT 0 NOT NULL,
        service_type varchar(100) NOT NULL,
        is_urgent tinyint(1) DEFAULT 0 NOT NULL,
        urgent_delivery_date date NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        KEY order_id (order_id)
    ) $charset_collate;";

    $sql_logs = "CREATE TABLE $table_logs (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        order_id bigint(20) NOT NULL,
        phone_number varchar(20) NOT NULL,
        message text NOT NULL,
        status varchar(50) DEFAULT 'Sent' NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        KEY order_id (order_id)
    ) $charset_collate;";

    $sql_payment_collections = "CREATE TABLE $table_payment_collections (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        token_id varchar(50) NOT NULL,
        customer_id bigint(20) NULL,
        delivery_boy_id varchar(255) NULL,
        amount decimal(10,2) NOT NULL,
        payment_mode varchar(50) NOT NULL,
        collection_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        verification_status varchar(20) DEFAULT 'Pending' NOT NULL,
        verified_by varchar(255) NULL,
        verified_date datetime NULL,
        PRIMARY KEY  (id),
        KEY token_id (token_id)
    ) $charset_collate;";

    $sql_partial_deliveries = "CREATE TABLE $table_partial_deliveries (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        token_id varchar(50) NOT NULL,
        item_id bigint(20) NOT NULL,
        delivered_quantity int(11) NOT NULL,
        delivery_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        delivery_boy varchar(255) NULL,
        PRIMARY KEY  (id),
        KEY token_id (token_id),
        KEY item_id (item_id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql_orders );
    dbDelta( $sql_items );
    dbDelta( $sql_logs );
    dbDelta( $sql_payment_collections );
    dbDelta( $sql_partial_deliveries );
}
