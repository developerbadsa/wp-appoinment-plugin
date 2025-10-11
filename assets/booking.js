jQuery(function ($) {
  const $calendarGrid = $("#calendarGrid");
  const $monthYear    = $("#monthYear");
  const $timeSlots    = $("#timeSlots");
  const $dayLabel     = $("#dayLabel");
  const $step1        = $("#step1");
  const $step2        = $("#step2");
  const $backBtn      = $("#backBtn");
  const $metaInfo     = $("#metaInfo");

  let current = new Date();        // month being shown
  current.setDate(1);              // normalize
  let selectedDate = null;
  let selectedTime = null;

  const MONTHS = [
    "January","February","March","April","May","June",
    "July","August","September","October","November","December"
  ];

  function renderCalendar(year, month) {
    $calendarGrid.empty();
    $monthYear.text(`${MONTHS[month]} ${year}`);

    const today = new Date();
    today.setHours(0,0,0,0);

    const firstDay = new Date(year, month, 1);
    const lastDay  = new Date(year, month + 1, 0);

    // pad grid with blanks before day 1
    const blanks = firstDay.getDay(); // 0=Sun … 6=Sat
    for (let i = 0; i < blanks; i++) $calendarGrid.append("<div></div>");

    for (let d = 1; d <= lastDay.getDate(); d++) {
      const date = new Date(year, month, d);
      const $cell = $('<div/>', { "class": "calendar-day", text: d });

      if (date < today) {
        $cell.addClass("disabled"); // past -> not clickable
      } else {
        $cell.on("click", function () {
          $(".calendar-day").removeClass("active");
          $(this).addClass("active");
          selectedDate = date;
          $dayLabel.text(
            "Selected: " + date.toLocaleDateString(undefined, {
              weekday: "short", month: "short", day: "2-digit", year: "numeric"
            })
          );
          renderTimeSlots();
        });
      }

      $calendarGrid.append($cell);
    }
  }

  function renderTimeSlots() {
    $timeSlots.empty();
    if (!selectedDate) return;

    // use PHP-injected times or fallback
    
    
  const times = wpData.timeslots || [
    "4:40pm","5:00pm","5:20pm","5:40pm","8:00pm","8:40pm"
  ];


    times.forEach(t => {
      const $btn = $('<button/>', { "class": "time-btn", type: "button", text: t });
      $btn.on("click", function () {
        selectedTime = t;

        $metaInfo.html(`
          <div class="appointment-header">
            <h2>
              Chat with ${wpData.host_name} - ${wpData.company}
            </h2>
            <p>Our clients include:</p>
            <div>${wpData.clients_html}</div>
          </div>
          <p>📅 ${selectedDate.toDateString()}</p>
          <p>⏰ ${t}</p>
          <p>⏱️ ${wpData.duration}</p>
          <p>📹 ${wpData.platform}</p>
          <p>🌍 ${wpData.timezone}</p>
        `);

        $step1.hide();
        $step2.show();
      });
      $timeSlots.append($btn);
    });
  }

  // Prev / Next month
  $("#prevMonth").on("click", function () {
    current.setMonth(current.getMonth() - 1);
    renderCalendar(current.getFullYear(), current.getMonth());
  });
  $("#nextMonth").on("click", function () {
    current.setMonth(current.getMonth() + 1);
    renderCalendar(current.getFullYear(), current.getMonth());
  });

  // Back button (from step 2 -> step 1)
  $backBtn.on("click", function () {
    $step2.hide();
    $step1.show();
  });

  // Submit booking
  $("#bookingForm").on("submit", function (e) {
    e.preventDefault();
    if (!selectedDate || !selectedTime) {
      alert("Please select a date and a time.");
      return;
    }

    $.post(appointmentAjax.ajax_url, {
      action: "save_booking",
      name:  $("#name").val(),
      email: $("#email").val(),
      notes: $("#notes").val(),
      date:  selectedDate.toDateString(),
      time:  selectedTime
    }, function (res) {
      if (res && res.success) {
        alert("✅ Appointment confirmed!");
        $step2.hide(); $step1.show();
      } else {
        alert("❌ Error saving appointment.");
      }
    });
  });

  // initial render
  renderCalendar(current.getFullYear(), current.getMonth());
});
