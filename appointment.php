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


// ðŸ”¹ Hide the Default Editor
function appointment_remove_editor_support() {
    remove_post_type_support('appointment', 'editor');
}
add_action('init', 'appointment_remove_editor_support');


// ðŸ”¹ Add Meta Box for Appointment Details
function appointment_add_meta_boxes() {
    add_meta_box(
        'appointment_details',
        'Appointment Details',
        'appointment_meta_box_callback',
        'appointment',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'appointment_add_meta_boxes');

function appointment_meta_box_callback($post) {
    $email = get_post_meta($post->ID, 'email', true);
    $date  = get_post_meta($post->ID, 'date', true);
    $time  = get_post_meta($post->ID, 'time', true);
    $notes = $post->post_content;
    ?>
    <style>
      .appointment-table {
          width: 100%;
          border-collapse: collapse;
          font-size: 14px;
          background: #fff;
      }
      .appointment-table th,
      .appointment-table td {
          border: 1px solid #ddd;
          padding: 10px 12px;
          text-align: left;
      }
      .appointment-table th {
          width: 150px;
          background: #f8f8f8;
          font-weight: 600;
          color: #333;
      }
      .appointment-notes {
          white-space: pre-line; /* keeps line breaks */
      }
    </style>

    <table class="appointment-table">
        <tr>
            <th>Name</th>
            <td><?php echo esc_html(get_the_title($post->ID)); ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?php echo esc_html($email); ?></td>
        </tr>
        <tr>
            <th>Date</th>
            <td><?php echo esc_html($date); ?></td>
        </tr>
        <tr>
            <th>Time</th>
            <td><?php echo esc_html($time); ?></td>
        </tr>
        <tr>
            <th>Notes</th>
            <td class="appointment-notes"><?php echo nl2br(esc_html($notes)); ?></td>
        </tr>
    </table>
    <?php
}




// ------------------------------
// Add custom columns to Appointments list
// ------------------------------
function appointment_columns($columns) {
    $new_columns = array();
    $new_columns['cb'] = $columns['cb']; // keep checkbox
    $new_columns['title'] = __('Name'); // title = booking name
    $new_columns['email'] = __('Email');
    $new_columns['date'] = __('Date');
    $new_columns['time'] = __('Time');
    $new_columns['notes'] = __('Notes');
    return $new_columns;
}
add_filter('manage_appointment_posts_columns', 'appointment_columns');

// ------------------------------
// Show values inside those columns
// ------------------------------
function appointment_custom_column($column, $post_id) {
    switch ($column) {
        case 'email':
            echo esc_html(get_post_meta($post_id, 'email', true));
            break;
        case 'date':
            echo esc_html(get_post_meta($post_id, 'date', true));
            break;
        case 'time':
            echo esc_html(get_post_meta($post_id, 'time', true));
            break;
        case 'notes':
            $notes = get_post_field('post_content', $post_id);
            echo esc_html(wp_trim_words($notes, 10)); // show only 10 words
            break;
    }
}
add_action('manage_appointment_posts_custom_column', 'appointment_custom_column', 10, 2);



// Enqueue custom styles for Appointments admin list table
function appointment_admin_styles($hook) {
    global $post_type;
    if ($post_type == 'appointment' && $hook == 'edit.php') {
        wp_enqueue_style('appointment-admin-css', plugin_dir_url(__FILE__) . 'assets/admin.css');
    }
}
add_action('admin_enqueue_scripts', 'appointment_admin_styles');




// ------------------------------
// Send Email Notifications on New Appointment
// ------------------------------
function appointment_send_email_notifications($post_id, $post, $update) {
    // Only for our CPT
    if ($post->post_type !== 'appointment') return;

    // Prevent running on updates (only send on new)
    if ($update) return;

    // Get appointment data
    $name  = get_the_title($post_id);
    $email = get_post_meta($post_id, 'email', true);
    $date  = get_post_meta($post_id, 'date', true);
    $time  = get_post_meta($post_id, 'time', true);
    $notes = $post->post_content;

    // Admin email
    $admin_email = get_option('admin_email');

    // Subject & message
    $subject_admin = "📅 New Appointment Booked: $name";
    $message_admin = "
A new appointment has been booked:

Name: $name
Email: $email
Date: $date
Time: $time
Notes: $notes
    ";

    // Send to Admin
    wp_mail($admin_email, $subject_admin, $message_admin);

    // Send Confirmation to User
    if (!empty($email)) {
        $subject_user = "✅ Appointment Confirmation - $date at $time";
        $message_user = "Hello $name,

Thank you for booking an appointment with us! Here are your details:

Date: $date
Time: $time
Notes: $notes

We look forward to speaking with you.
- Team";

        wp_mail($email, $subject_user, $message_user);
    }
}
add_action('wp_insert_post', 'appointment_send_email_notifications', 10, 3);





// Load booking handler
include plugin_dir_path(__FILE__) . 'includes/booking-handler.php';
