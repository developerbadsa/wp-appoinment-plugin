<?php
// AJAX handler for saving booking
add_action('wp_ajax_save_booking', 'appointment_save_booking');
add_action('wp_ajax_nopriv_save_booking', 'appointment_save_booking');

function appointment_save_booking() {
    $name  = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $notes = sanitize_textarea_field($_POST['notes']);
    $date  = sanitize_text_field($_POST['date']);
    $time  = sanitize_text_field($_POST['time']);

    // Save as appointment post
    $post_id = wp_insert_post(array(
        'post_type'   => 'appointment',
        'post_status' => 'publish',
        'post_title'  => "$name - $date $time",
        'post_content'=> $notes,
        'meta_input'  => array(
            'email' => $email,
            'date'  => $date,
            'time'  => $time
        )
    ));

    // Send notification email
    $admin_email = get_option('admin_email');
    wp_mail(
        $admin_email,
        "New Appointment Booking",
        "New booking details:\n\nName: $name\nEmail: $email\nDate: $date\nTime: $time\nNotes: $notes"
    );

    wp_send_json_success("Booking saved and email sent!");
}
