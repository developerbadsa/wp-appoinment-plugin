<section class="booking-wrapper">
  <!-- LEFT INFO -->
  <div class="booking-meta" id="metaInfo">
    <h2>Chat with Sajon - OrixCreative®</h2>
    <p>Let’s hop on a free intro call!</p>
    <p>Our clients include:<br>
      - <a href="#">Aviato</a> ($20M)<br>
      - <a href="#">Tandem</a> ($6.1M)<br>
      - <a href="#">Gamestarter</a> ($2.9M)
    </p>
    <div class="meta-item">⏱️ 20m</div>
    <div class="meta-item">📹 Google Meet</div>
    <div class="meta-item">🌍 Asia/Dhaka</div>
  </div>

  <!-- RIGHT PANEL -->
  <div class="step">
    <!-- STEP 1 -->
    <div id="step1">
      <div class="calendar-container">
        <div class="calendar">
          <div class="calendar-header">
            <button id="prevMonth">‹</button>
            <span id="monthYear"></span>
            <button id="nextMonth">›</button>
          </div>
          <div class="calendar-grid" id="calendarGrid"></div>
        </div>
        <div>
          <div class="times-header">
            <span id="dayLabel">Select a date</span>
            <div>
              <button class="time-btn small">12h</button>
              <button class="time-btn small">24h</button>
            </div>
          </div>
          <div id="timeSlots"></div>
        </div>
      </div>
    </div>

    <!-- STEP 2 -->
    <div id="step2" style="display:none;">
      <form class="booking-form" id="bookingForm">
        <label>Your name *</label>
        <input type="text" id="name" required>
        <label>Email address *</label>
        <input type="email" id="email" required>
        <label>Additional notes</label>
        <textarea id="notes"></textarea>
        <div class="form-actions">
          <button type="button" class="btn btn-back" id="backBtn">Back</button>
          <button type="submit" class="btn btn-confirm">Confirm</button>
        </div>
      </form>
    </div>
  </div>
</section>
