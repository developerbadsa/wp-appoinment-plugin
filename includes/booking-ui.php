<section class="booking-wrapper">
  <!-- LEFT INFO -->
  <div class="booking-meta" id="metaInfo">
    <div class="appointment-header">
      <h2>
        Chat with <?php echo esc_html(get_option('appointment_host_name')); ?> - 
        <?php echo esc_html(get_option('appointment_host_company')); ?>
      </h2>
      <p>Our clients include:</p>
      <div><?php echo wp_kses_post(get_option('appointment_clients_list')); ?></div>
    </div>

    <p>⏱️ <?php echo esc_html(get_option('appointment_duration', '20m')); ?></p>
    <p>📹 <?php echo esc_html(get_option('appointment_platform', 'Google Meet')); ?></p>
    <p>🌍 <?php echo esc_html(get_option('appointment_timezone', 'Asia/Dhaka')); ?></p>
  </div>

  <!-- RIGHT PANEL -->
  <div class="step">
    <!-- STEP 1 -->
    <div id="step1">
      <div class="calendar-container">
        <div class="calendar">
          <div class="calendar-header">
            <button id="prevMonth" aria-label="Previous month">‹</button>
            <span id="monthYear"></span>
            <button id="nextMonth" aria-label="Next month">›</button>
          </div>
          <div class="calendar-grid" id="calendarGrid"></div>
        </div>
        <div>
          <div class="times-header">
            <span id="dayLabel">Select a date</span>
          </div>
          <div id="timeSlots">
            <?php
              $times = (array)get_option('appointment_timeslots');
              foreach ($times as $time) {
                  echo '<button class="slot">'.esc_html($time).'</button>';
              }
            ?>
          </div>
        </div>
      </div>
    </div>

    <!-- STEP 2 -->
    <div id="step2" style="display:none;">
      <form class="booking-form" id="bookingForm">
        <label for="name">Your name *</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email address *</label>
        <input type="email" id="email" name="email" required>

        <label for="notes">Additional notes</label>
        <textarea id="notes" name="notes"></textarea>

        <div class="form-actions">
          <button type="button" class="btn btn-back" id="backBtn">Back</button>
          <button type="submit" class="btn btn-confirm">Confirm</button>
        </div>
      </form>
    </div>
  </div>
</section>
