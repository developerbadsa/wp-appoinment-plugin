# 📖 Appointment Plugin

## 1. Overview
The **Appointment Plugin** allows WordPress site owners to create a simple booking widget (similar to Cal.com / Calendly) with:

- Calendar view for selecting dates  
- Time slot selection (configurable in dashboard)  
- Appointment details form (name, email, notes)  
- Customizable meeting host, platform, and timezone  
- Backend appointment management via custom post type  
- Email notifications for both admin and client  

---

## 2. Features
 Shortcode `[appointment]` to embed booking UI  
 Custom Post Type: `appointment` for storing bookings  
 Admin dashboard settings page under **Appointments → Settings**  
 Configure:
- Host name  
- Company name  
- Clients list (HTML supported)  
- Meeting duration  
- Meeting platform (Google Meet, Zoom, Teams)  
- Timezone (dropdown from all PHP timezones)  
- Available timeslots (comma separated)  
- Notification emails (one or more addresses)  
 Email notifications:
- **Admin(s):** Receive booking details  
- **User:** Receives confirmation  

---

## 3. Installation
1. Download or clone the plugin from GitHub:
   ```bash
   git clone https://github.com/developerbadsa/wp-appointment-plugin.git
