<?php
/**
 * The main template file
 *
 * @package LOL_Delivery
 */

get_header(); ?>

<div class="lol-delivery-wrapper">
    <?php if ( is_user_logged_in() ) : ?>
        
        <?php
        $current_user = wp_get_current_user();
        if ( in_array( 'delivery_partner', (array) $current_user->roles ) || current_user_can( 'manage_options' ) ) :
            // Include the partner dashboard template
            get_template_part( 'templates/partner', 'dashboard' );
        else :
            echo '<div class="lol-notice">You do not have permission to access the Delivery Partner app.</div>';
        endif;
        ?>

    <?php else : ?>
        <div class="lol-login-container">
            <h2>Delivery Partner Login</h2>
            <?php
            wp_login_form( array(
                'redirect' => home_url(), 
                'form_id' => 'lol-loginform',
                'label_username' => __( 'Username' ),
                'label_password' => __( 'Password' ),
                'label_remember' => __( 'Remember Me' ),
                'label_log_in' => __( 'Log In' ),
                'remember' => true
            ) );
            ?>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
