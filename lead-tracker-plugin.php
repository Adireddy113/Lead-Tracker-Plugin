<?php
/*
Plugin Name: Simple Lead Tracker
Description: Tracks leads with UTM info using shortcode and stores them in DB.
Version: 1.0
Author: Adireddy
*/

defined('ABSPATH') or exit;

// Create DB table on activation
register_activation_hook(__FILE__, 'lt_create_table');
function lt_create_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'lead_tracker';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255),
        email VARCHAR(255),
        utm_source VARCHAR(255),
        utm_medium VARCHAR(255),
        utm_campaign VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

// Handle form + shortcode
add_shortcode('lead_form', 'lt_form_shortcode');
function lt_form_shortcode() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lt_submit'])) {
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'lead_tracker', [
            'name' => sanitize_text_field($_POST['lt_name']),
            'email' => sanitize_email($_POST['lt_email']),
            'utm_source' => sanitize_text_field($_POST['lt_utm_source']),
            'utm_medium' => sanitize_text_field($_POST['lt_utm_medium']),
            'utm_campaign' => sanitize_text_field($_POST['lt_utm_campaign'])
        ]);
        echo "<p>Thank you! Submitted.</p>";
    }

    $utm_source = esc_attr($_GET['utm_source'] ?? '');
    $utm_medium = esc_attr($_GET['utm_medium'] ?? '');
    $utm_campaign = esc_attr($_GET['utm_campaign'] ?? '');

    return '
    <form method="POST">
        <input type="text" name="lt_name" placeholder="Your Name" required><br>
        <input type="email" name="lt_email" placeholder="Your Email" required><br>
        <input type="hidden" name="lt_utm_source" value="' . $utm_source . '">
        <input type="hidden" name="lt_utm_medium" value="' . $utm_medium . '">
        <input type="hidden" name="lt_utm_campaign" value="' . $utm_campaign . '">
        <input type="submit" name="lt_submit" value="Submit">
    </form>';
}