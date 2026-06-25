<?php
/**
 * LOL Delivery System functions and definitions
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define theme constants
define( 'LOL_THEME_DIR', get_template_directory() );
define( 'LOL_THEME_URI', get_template_directory_uri() );
define( 'LOL_THEME_VERSION', time() );

// Include Composer autoloader if it exists (for PhpSpreadsheet)
if ( file_exists( LOL_THEME_DIR . '/vendor/autoload.php' ) ) {
    require_once LOL_THEME_DIR . '/vendor/autoload.php';
}

// Include required files
require_once LOL_THEME_DIR . '/inc/database.php';
require_once LOL_THEME_DIR . '/inc/roles.php';
require_once LOL_THEME_DIR . '/inc/admin-menu.php';
require_once LOL_THEME_DIR . '/inc/ajax-handlers.php';
require_once LOL_THEME_DIR . '/inc/export-excel.php';

/**
 * Enqueue scripts and styles.
 */
function lol_delivery_scripts() {
    // Enqueue Google Fonts
    wp_enqueue_style( 'lol-google-fonts', 'https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap', false );

    // Enqueue frontend CSS
    wp_enqueue_style( 'lol-delivery-style', LOL_THEME_URI . '/assets/css/app.css', array('lol-google-fonts'), LOL_THEME_VERSION );

    // Enqueue SheetJS
    wp_enqueue_script( 'sheetjs', 'https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js', array(), null, true );

    // Enqueue frontend JS
    wp_enqueue_script( 'lol-delivery-script', LOL_THEME_URI . '/assets/js/app.js', array('jquery', 'sheetjs'), LOL_THEME_VERSION, true );

    // Localize script for AJAX
    wp_localize_script( 'lol-delivery-script', 'lol_ajax_obj', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'lol_delivery_nonce' ),
        'excel_url' => LOL_THEME_URI . '/Laugh-O-Laundry  Customer Sheet .xlsx'
    ) );
}
add_action( 'wp_enqueue_scripts', 'lol_delivery_scripts' );

/**
 * Redirect delivery partners away from wp-admin to frontend
 */
function lol_redirect_delivery_partner() {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        $current_user = wp_get_current_user();
        if ( in_array( 'delivery_partner', (array) $current_user->roles ) && ! current_user_can( 'manage_options' ) ) {
            wp_redirect( home_url() );
            exit;
        }
    }
}
add_action( 'admin_init', 'lol_redirect_delivery_partner' );

/**
 * Hide admin bar for delivery partners
 */
function lol_hide_admin_bar() {
    $current_user = wp_get_current_user();
    if ( in_array( 'delivery_partner', (array) $current_user->roles ) && ! current_user_can( 'manage_options' ) ) {
        show_admin_bar( false );
    }
}
add_action( 'after_setup_theme', 'lol_hide_admin_bar' );

/**
 * Theme activation hook workaround
 * Themes don't have a direct activation hook like plugins. 
 * We use after_switch_theme.
 */
function lol_theme_activation() {
    lol_create_custom_tables();
    lol_add_delivery_partner_role();
}
add_action('after_switch_theme', 'lol_theme_activation');

/**
 * Ensure delivery_boy column is updated to varchar
 * Also add new columns for pickup_agent_name, payment_mode, total_bill_amount, balance_due
 */
function lol_update_database_schema() {
    global $wpdb;
    $table_orders = $wpdb->prefix . 'laundry_orders';
    // Suppress errors if table doesn't exist yet
    $wpdb->suppress_errors = true;
    
    $table_items = $wpdb->prefix . 'laundry_order_items';
    
    // Ensure delivery_boy is varchar
    $wpdb->query("ALTER TABLE $table_orders MODIFY delivery_boy VARCHAR(255) NULL");
    
    // Add new columns if they don't exist
    $wpdb->query("ALTER TABLE $table_orders ADD COLUMN pickup_agent_name VARCHAR(255) NULL");
    $wpdb->query("ALTER TABLE $table_orders ADD COLUMN payment_mode VARCHAR(50) NULL");
    $wpdb->query("ALTER TABLE $table_orders ADD COLUMN total_bill_amount DECIMAL(10,2) NULL");
    $wpdb->query("ALTER TABLE $table_orders ADD COLUMN balance_due DECIMAL(10,2) NULL");
    $wpdb->query("ALTER TABLE $table_orders ADD COLUMN address TEXT NULL");
    
    $wpdb->query("ALTER TABLE $table_items ADD COLUMN delivered_quantity INT(11) DEFAULT 0 NOT NULL");
    $wpdb->query("ALTER TABLE $table_items ADD COLUMN is_urgent TINYINT(1) DEFAULT 0 NOT NULL");
    $wpdb->query("ALTER TABLE $table_items ADD COLUMN urgent_delivery_date DATE NULL");

    $wpdb->suppress_errors = false;
    
    // Ensure custom tables are created without needing to reactivate the theme
    lol_create_custom_tables();
}
add_action('init', 'lol_update_database_schema');


/**
 * ============================================================
 * Customer Feedback & Review System
 * ============================================================
 */

// Create the reviews table (runs once)
function lol_create_reviews_table() {
    if ( get_option( 'lol_reviews_table_v1' ) === 'yes' ) {
        return;
    }
    global $wpdb;
    $table = $wpdb->prefix . 'laundry_reviews';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        token_id varchar(50) NOT NULL,
        customer_name varchar(255) NULL,
        rating tinyint(1) NOT NULL DEFAULT 0,
        comments text NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
    update_option( 'lol_reviews_table_v1', 'yes' );
}
add_action( 'init', 'lol_create_reviews_table' );

// Build the customer review link for an order token
function lol_review_url( $token ) {
    return home_url( '/?lol_review=' . rawurlencode( $token ) );
}

// Render the public review form / handle submission
function lol_handle_review_page() {
    if ( ! isset( $_GET['lol_review'] ) ) {
        return;
    }

    $token = sanitize_text_field( wp_unslash( $_GET['lol_review'] ) );
    global $wpdb;
    $orders_table = $wpdb->prefix . 'laundry_orders';
    $order = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $orders_table WHERE token_id = %s", $token ) );

    $done = false;
    $error = '';

    if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['lol_review_nonce'] ) && wp_verify_nonce( $_POST['lol_review_nonce'], 'lol_submit_review' ) ) {
        $rating = isset( $_POST['rating'] ) ? intval( $_POST['rating'] ) : 0;
        $comments = isset( $_POST['comments'] ) ? sanitize_textarea_field( wp_unslash( $_POST['comments'] ) ) : '';
        $cname = $order ? $order->customer_name : '';
        if ( $rating < 1 || $rating > 5 ) {
            $error = 'Please select a star rating.';
        } else {
            $reviews_table = $wpdb->prefix . 'laundry_reviews';
            $wpdb->insert( $reviews_table, array(
                'token_id'      => $token,
                'customer_name' => $cname,
                'rating'        => $rating,
                'comments'      => $comments,
            ), array( '%s', '%s', '%d', '%s' ) );
            $done = true;
        }
    }

    $site = get_bloginfo( 'name' );
    header( 'Content-Type: text/html; charset=utf-8' );
    ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Feedback &amp; Review</title>
<style>
*{box-sizing:border-box;font-family:'Segoe UI',Arial,sans-serif;}
body{margin:0;background:#f3f4f6;color:#1f2937;display:flex;min-height:100vh;align-items:center;justify-content:center;padding:16px;}
.card{background:#fff;max-width:440px;width:100%;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.08);padding:28px;}
h1{font-size:22px;margin:0 0 4px;}
.sub{color:#6b7280;font-size:14px;margin:0 0 20px;}
.stars{display:flex;flex-direction:row-reverse;justify-content:center;gap:6px;margin:10px 0 18px;}
.stars input{display:none;}
.stars label{font-size:40px;color:#d1d5db;cursor:pointer;transition:color .15s;}
.stars input:checked ~ label,.stars label:hover,.stars label:hover ~ label{color:#fbbf24;}
textarea{width:100%;min-height:110px;border:1px solid #d1d5db;border-radius:10px;padding:12px;font-size:14px;resize:vertical;}
button{width:100%;margin-top:16px;background:#4f46e5;color:#fff;border:0;border-radius:10px;padding:14px;font-size:16px;font-weight:600;cursor:pointer;}
button:hover{background:#4338ca;}
.tok{font-size:12px;color:#9ca3af;margin-top:14px;text-align:center;}
.ok{text-align:center;}
.ok .big{font-size:54px;}
.err{background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;padding:10px 12px;border-radius:8px;font-size:14px;margin-bottom:14px;}
</style>
</head>
<body>
<div class="card">
<?php if ( $done ) : ?>
    <div class="ok">
        <div class="big">✅</div>
        <h1>Thank you!</h1>
        <p class="sub">Your feedback has been submitted successfully. We appreciate you choosing <?php echo esc_html( $site ); ?>.</p>
    </div>
<?php elseif ( ! $order ) : ?>
    <h1>Invalid link</h1>
    <p class="sub">We couldn't find an order for this review link. Please check the link and try again.</p>
<?php else : ?>
    <h1>Rate your experience</h1>
    <p class="sub">Hi <?php echo esc_html( $order->customer_name ); ?>, how was our service for order <strong><?php echo esc_html( $token ); ?></strong>?</p>
    <?php if ( $error ) : ?><div class="err"><?php echo esc_html( $error ); ?></div><?php endif; ?>
    <form method="post">
        <div class="stars">
            <input type="radio" id="s5" name="rating" value="5"><label for="s5">★</label>
            <input type="radio" id="s4" name="rating" value="4"><label for="s4">★</label>
            <input type="radio" id="s3" name="rating" value="3"><label for="s3">★</label>
            <input type="radio" id="s2" name="rating" value="2"><label for="s2">★</label>
            <input type="radio" id="s1" name="rating" value="1"><label for="s1">★</label>
        </div>
        <textarea name="comments" placeholder="Tell us about your experience (optional)"></textarea>
        <?php wp_nonce_field( 'lol_submit_review', 'lol_review_nonce' ); ?>
        <button type="submit">Submit Feedback</button>
        <div class="tok">Order: <?php echo esc_html( $token ); ?></div>
    </form>
<?php endif; ?>
</div>
</body>
</html>
    <?php
    exit;
}
add_action( 'template_redirect', 'lol_handle_review_page' );

// Admin page: list submitted reviews
function lol_admin_reviews_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'laundry_reviews';
    $reviews = $wpdb->get_results( "SELECT * FROM $table ORDER BY created_at DESC" );
    $count = is_array( $reviews ) ? count( $reviews ) : 0;
    $avg = $count ? $wpdb->get_var( "SELECT ROUND(AVG(rating),2) FROM $table" ) : 0;

    // Rating distribution
    $dist = array( 5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0 );
    if ( $count ) {
        foreach ( $reviews as $r ) {
            $rr = intval( $r->rating );
            if ( isset( $dist[ $rr ] ) ) { $dist[ $rr ]++; }
        }
    }

    echo '<div class="wrap">';
    echo '<h1 style="margin-bottom:18px;">⭐ Customer Reviews & Feedback</h1>';

    // Stat cards
    echo '<div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:24px;">';
    echo '<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px 24px;min-width:160px;box-shadow:0 1px 3px rgba(0,0,0,.05);">';
    echo '<div style="font-size:13px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Total Reviews</div>';
    echo '<div style="font-size:32px;font-weight:700;color:#1e293b;">' . intval( $count ) . '</div></div>';
    echo '<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px 24px;min-width:160px;box-shadow:0 1px 3px rgba(0,0,0,.05);">';
    echo '<div style="font-size:13px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Average Rating</div>';
    echo '<div style="font-size:32px;font-weight:700;color:#f59e0b;">' . esc_html( $avg ? $avg : '0' ) . ' <span style="font-size:18px;color:#94a3b8;">/ 5</span></div></div>';
    echo '</div>';

    // Distribution bars
    if ( $count ) {
        echo '<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px 24px;max-width:520px;margin-bottom:24px;box-shadow:0 1px 3px rgba(0,0,0,.05);">';
        echo '<div style="font-size:14px;font-weight:600;color:#334155;margin-bottom:12px;">Rating Breakdown</div>';
        for ( $star = 5; $star >= 1; $star-- ) {
            $num = $dist[ $star ];
            $pct = $count ? round( ( $num / $count ) * 100 ) : 0;
            echo '<div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;font-size:13px;">';
            echo '<div style="width:50px;color:#f59e0b;">' . str_repeat( '★', $star ) . '</div>';
            echo '<div style="flex:1;background:#f1f5f9;border-radius:6px;height:14px;overflow:hidden;"><div style="width:' . $pct . '%;background:#f59e0b;height:100%;"></div></div>';
            echo '<div style="width:60px;text-align:right;color:#64748b;">' . $num . ' (' . $pct . '%)</div>';
            echo '</div>';
        }
        echo '</div>';
    }

    // Detailed table
    echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th style="width:140px;">Date</th><th>Token ID</th><th>Customer</th><th style="width:120px;">Rating</th><th>Comments</th></tr></thead><tbody>';
    if ( $count ) {
        foreach ( $reviews as $r ) {
            $rr = intval( $r->rating );
            $stars = str_repeat( '★', $rr ) . str_repeat( '☆', 5 - $rr );
            echo '<tr>';
            echo '<td>' . esc_html( date_i18n( 'd M Y, H:i', strtotime( $r->created_at ) ) ) . '</td>';
            echo '<td><strong>' . esc_html( $r->token_id ) . '</strong></td>';
            echo '<td>' . esc_html( $r->customer_name ) . '</td>';
            echo '<td style="color:#f59e0b;font-size:15px;letter-spacing:1px;">' . $stars . '</td>';
            echo '<td>' . ( $r->comments ? esc_html( $r->comments ) : '<em style="color:#94a3b8;">No comment</em>' ) . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="5" style="text-align:center;padding:24px;color:#64748b;">No reviews submitted yet. Reviews will appear here once customers click the feedback link in their WhatsApp message.</td></tr>';
    }
    echo '</tbody></table></div>';
}
