jQuery(document).ready(function($){
  const calendarGrid = $("#calendarGrid");
  const monthYear = $("#monthYear");
  const timeSlotsDiv = $("#timeSlots");
  const dayLabel = $("#dayLabel");
  const step1 = $("#step1");
  const step2 = $("#step2");
  const backBtn = $("#backBtn");
  const metaInfo = $("#metaInfo");

  let currentDate = new Date();
  let selectedDate = null;
  let selectedTime = null;

  function renderCalendar(date) {
    calendarGrid.empty();
    const year = date.getFullYear();
    const month = date.getMonth();
    monthYear.text(date.toLocaleString("default",{month:"long"})+" "+year);
    const firstDay = new Date(year,month,1).getDay();
    const daysInMonth = new Date(year,month+1,0).getDate();

    for(let i=0;i<firstDay;i++) calendarGrid.append("<div></div>");
    for(let d=1; d<=daysInMonth; d++){
      const day = $("<div>").addClass("calendar-day").text(d);
      day.on("click", function(){
        $(".calendar-day").removeClass("active");
        $(this).addClass("active");
        selectedDate = new Date(year,month,d);
        dayLabel.text("Selected: "+selectedDate.toDateString());
        renderTimeSlots();
      });
      calendarGrid.append(day);
    }
  }

  function renderTimeSlots(){
    timeSlotsDiv.empty();
    const times = ["4:40pm","5:00pm","5:20pm","5:40pm","8:00pm","8:40pm"];
    times.forEach(t=>{
      const btn=$("<button>").addClass("time-btn").text(t);
      btn.on("click", function(){
        selectedTime=t;
        metaInfo.html(`
          <h2>Chat with Sajon - OrixCreative®</h2>
          <p>Let’s hop on a free intro call!</p>
          <div class="meta-item">📅 ${selectedDate.toDateString()}</div>
          <div class="meta-item">⏰ ${t}</div>
          <div class="meta-item">⏱️ 20m</div>
          <div class="meta-item">📹 Google Meet</div>
          <div class="meta-item">🌍 Asia/Dhaka</div>
        `);
        step1.hide();
        step2.show();
      });
      timeSlotsDiv.append(btn);
    });
  }

  $("#prevMonth").on("click",()=>{currentDate.setMonth(currentDate.getMonth()-1);renderCalendar(currentDate);});
  $("#nextMonth").on("click",()=>{currentDate.setMonth(currentDate.getMonth()+1);renderCalendar(currentDate);});
  backBtn.on("click",()=>{step2.hide();step1.show();});

  $("#bookingForm").on("submit", function(e){
    e.preventDefault();
    $.post(appointmentAjax.ajax_url, {
      action: 'save_booking',
      name: $("#name").val(),
      email: $("#email").val(),
      notes: $("#notes").val(),
      date: selectedDate.toDateString(),
      time: selectedTime
    }, function(response){
      if(response.success){
        alert("✅ Appointment confirmed!");
        step2.hide();
        step1.show();
      } else {
        alert("❌ Error saving appointment.");
      }
    });
  });

  renderCalendar(currentDate);
});
