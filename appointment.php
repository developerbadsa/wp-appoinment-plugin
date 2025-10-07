<?php
/**
 * Plugin Name: Appointment
 * Description: Custom appointment booking widget with calendar + form (Cal.com style)
 * Version: 1.0
 * Author: Rahim Badsa
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Prevent direct access

// Enqueue scripts & styles
function appointment_enqueue_assets() {
    wp_enqueue_style( 'appointment-css', plugin_dir_url(__FILE__) . 'assets/booking.css' );
    wp_enqueue_script( 'appointment-js', plugin_dir_url(__FILE__) . 'assets/booking.js', array('jquery'), null, true );

    wp_localize_script( 'appointment-js', 'appointmentAjax', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}
add_action( 'wp_enqueue_scripts', 'appointment_enqueue_assets' );

// Shortcode
function appointment_shortcode() {
    ob_start();
    include plugin_dir_path(__FILE__) . 'includes/booking-ui.php';
    return ob_get_clean();
}
add_shortcode('appointment', 'appointment_shortcode');

// Register CPT for appointments
function appointment_cpt() {
    register_post_type('appointment', array(
        'label' => 'Appointments',
        'public' => false,
        'show_ui' => true,
        'supports' => array('title','editor','custom-fields')
    ));
}
add_action('init','appointment_cpt');

// Load booking handler
include plugin_dir_path(__FILE__) . 'includes/booking-handler.php';
