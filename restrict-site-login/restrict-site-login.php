<?php
/**
 * Plugin Name: SecureView
 * Description: Forces users to log in before viewing the site. Toggle in settings(reading)
 * Version: 1.2.2
 * Author: Akeem Foster
 */

// Redirect logic
function restrict_site_to_logged_in_users() {
    $enabled = get_option('rslu_enabled');

    if (!$enabled) return;

    if (
        is_user_logged_in() ||
        is_admin() ||
        defined('DOING_AJAX') ||
        defined('DOING_CRON') ||
        strpos($_SERVER['REQUEST_URI'], 'portal') !== false ||
        strpos($_SERVER['REQUEST_URI'], 'wp-json') !== false
    ) {
        return;
    }

    
    wp_redirect( site_url('/portal') );
    exit;
}
add_action('template_redirect', 'restrict_site_to_logged_in_users');

/**
 * Customize the WordPress login logo
 */
function rslu_custom_login_logo() {
    $logo_url = plugin_dir_url(__FILE__) . 'wapw-fav.png'; 

    echo '
    <style type="text/css">
        #login h1 a {
            background-image: url(' . esc_url($logo_url) . '); 
            background-size: contain; 
            width: 100%; 
            height: 80px; 
        }
    </style>';
}
add_action('login_enqueue_scripts', 'rslu_custom_login_logo');

/**
 * Change the login logo URL
 */
function rslu_login_logo_url() {
    return 'https://wellington-altus.ca/';// 
}
add_filter('login_headerurl', 'rslu_login_logo_url');

/**
 * Change the login logo title 
 */
function rslu_login_logo_title() {
    return 'Wellington-Altus Private Wealth'; 
}
add_filter('login_headertext', 'rslu_login_logo_title');


// Add checkbox to Reading settings
function rslu_register_reading_setting() {
    register_setting('reading', 'rslu_enabled'); // Hook into the Reading settings group

    add_settings_field(
        'rslu_enabled',                        // ID
        'Require login to view site',          // Label
        'rslu_enabled_callback',               // Callback to render the field
        'reading',                             // Page: Settings > Reading
        'default'                              // Section in the page
    );
}
add_action('admin_init', 'rslu_register_reading_setting');

// Render the checkbox
function rslu_enabled_callback() {
    $value = get_option('rslu_enabled');
    ?>
    <input type="checkbox" name="rslu_enabled" value="1" <?php checked(1, $value); ?> />
    <label for="rslu_enabled">Only logged-in users can view the site</label>
    <?php
}