<?php
/**
 * Plugin Name: Appointment
 * Description: Custom appointment appoinment widget with calendar + form (Cal.com style)
 * Version: 1.0
 * Author: Rahim Badsa
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Prevent direct access

// Enqueue scripts & styles
function appointment_enqueue_assets() {
    wp_enqueue_style(
        'appointment-css',
        plugin_dir_url(__FILE__) . 'assets/booking.css',
        array(),
        time() // force refresh CSS on every load
    );

    wp_enqueue_script(
        'appointment-js',
        plugin_dir_url(__FILE__) . 'assets/booking.js',
        array('jquery'),
        time(), // also bust cache for JS
        true
    );
    
    
    
     // Localize PHP options for use in booking.js
    wp_localize_script('appointment-js', 'appointmentAjax', [
        'ajax_url' => admin_url('admin-ajax.php')
    ]);

    wp_localize_script('appointment-js', 'wpData', [
        'host_name'    => get_option('appointment_host_name', 'Host'),
        'company'      => get_option('appointment_host_company', 'Company'),
        'clients_html' => wp_kses_post(get_option('appointment_clients_list', '')),
'timeslots' => array_values((array) get_option(
    'appointment_timeslots',
    ['4:40pm','5:00pm','5:20pm','5:40pm','8:00pm','8:40pm']
)),
        'duration'     => get_option('appointment_duration', '20m'),
        'platform'     => get_option('appointment_platform', 'Google Meet'),
        'timezone'     => get_option('appointment_timezone', 'Asia/Dhaka'),
    ]);
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
    // $admin_email = get_option('admin_email');
    
   $admin_email = get_option('appointment_notify_email', get_option('admin_email'));


    

    // Subject & message
    $subject_admin = " New Appointment Booked: $name";
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
        $subject_user = " Appointment Confirmation - $date at $time";
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



// Add Appointment Settings submenu
function appointment_register_settings_submenu() {
    add_submenu_page(
        'edit.php?post_type=appointment', // parent = Appointments CPT
        'Appointment Settings',           // Page title
        'Settings',                       // Menu title (what shows in submenu)
        'manage_options',                 // Capability
        'appointment-settings',           // Menu slug
        'appointment_settings_page_html'  // Callback function
    );
}
add_action('admin_menu', 'appointment_register_settings_submenu');




function appointment_settings_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Save options
    if (isset($_POST['appointment_settings_submit'])) {
        update_option('appointment_host_name', sanitize_text_field($_POST['appointment_host_name']));
        update_option('appointment_host_company', sanitize_text_field($_POST['appointment_host_company']));
        update_option('appointment_clients_list', wp_kses_post($_POST['appointment_clients_list']));
        update_option('appointment_duration', sanitize_text_field($_POST['appointment_duration']));
        update_option('appointment_notify_email', sanitize_email($_POST['appointment_notify_email']));
        update_option('appointment_platform', sanitize_text_field($_POST['appointment_platform']));
        update_option('appointment_timezone', sanitize_text_field($_POST['appointment_timezone']));
        update_option('appointment_timeslots', array_map('sanitize_text_field', explode(',', $_POST['appointment_timeslots'])));

        echo '<div class="updated"><p>Settings saved!</p></div>';
    }

    $host_name    = get_option('appointment_host_name', 'Mehdi');
    $host_company = get_option('appointment_host_company', 'Trinet®');
    $clients_list = get_option('appointment_clients_list', "- test ($20M)");
    $duration     = get_option('appointment_duration', '20m');
    $platform     = get_option('appointment_platform', 'Google Meet');
    $timezone     = get_option('appointment_timezone', 'Asia/Dhaka');
    $timeslots    = implode(',', (array)get_option('appointment_timeslots', ['4:40pm','5:00pm','5:20pm','5:40pm','8:00pm','8:40pm']));
    ?>
    <div class="wrap">
        <h1>Appointment Settings</h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th scope="row">Host Name</th>
                    <td><input type="text" name="appointment_host_name" value="<?php echo esc_attr($host_name); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row">Company</th>
                    <td><input type="text" name="appointment_host_company" value="<?php echo esc_attr($host_company); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row">Clients List (HTML allowed)</th>
                    <td><textarea name="appointment_clients_list" class="large-text" rows="5"><?php echo esc_textarea($clients_list); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row">Meeting Duration</th>
                    <td><input type="text" name="appointment_duration" value="<?php echo esc_attr($duration); ?>" class="small-text"> (e.g. 20m, 30m, 1h)</td>
                </tr>
                <tr>
  <th scope="row">Notification Email</th>
  <td>
    <input type="email" name="appointment_notify_email" value="<?php echo esc_attr( get_option('appointment_notify_email', get_option('admin_email')) ); ?>" class="regular-text">
    <p class="description">Enter the email address where new appointment notifications should be sent.</p>
  </td>
</tr>

                <tr>
                    <th scope="row">Meeting Platform</th>
                    <td>
                        <select name="appointment_platform">
                            <option value="Google Meet" <?php selected($platform,'Google Meet'); ?>>Google Meet</option>
                            <option value="Zoom" <?php selected($platform,'Zoom'); ?>>Zoom</option>
                            <option value="Microsoft Teams" <?php selected($platform,'Microsoft Teams'); ?>>Microsoft Teams</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Timezone</th>
                    <td>
                        <select name="appointment_timezone">
                            <?php
                            foreach(timezone_identifiers_list() as $tz){
                                echo '<option value="'.esc_attr($tz).'" '.selected($timezone,$tz,false).'>'.$tz.'</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Available Time Slots</th>
                    <td>
                        <input type="text" name="appointment_timeslots" value="<?php echo esc_attr($timeslots); ?>" class="large-text">
                        <p class="description">Enter times separated by commas (e.g. 4:40pm,5:00pm,5:20pm,5:40pm,8:00pm,8:40pm)</p>
                    </td>
                </tr>
            </table>
            <p><input type="submit" name="appointment_settings_submit" class="button-primary" value="Save Changes"></p>
        </form>
    </div>
    <?php
}







// Load booking handler
include plugin_dir_path(__FILE__) . 'includes/booking-handler.php';
