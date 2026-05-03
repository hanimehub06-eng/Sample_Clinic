# Medi Clinic Appointment Management System - Specification Document

## 1. Project Overview

**Project Name:** Medi Clinic Appointment Management System
**Project Type:** Web-based clinic management application
**Core Functionality:** A comprehensive appointment booking and management system with three user roles (Patient, Doctor, Clerk), enabling patients to book appointments, clerks to manage schedules, and doctors to view their appointments.
**Target Users:** Clinic patients, medical staff (doctors), and administrative personnel (clerks)

## 2. Technical Stack

- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Backend:** PHP 8.x
- **Database:** MariaDB (via XAMPP)
- **Database User:** `clinic_user` (not root) with full privileges on `clinic_db`
- **Session Management:** PHP native sessions
- **Background Jobs:** PHP CLI script with Windows Task Scheduler

## 3. Database Schema

### 3.1 Users Table
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('patient', 'doctor', 'clerk') NOT NULL,
    phone VARCHAR(20),
    specialization VARCHAR(100), -- For doctors only
    email_notifications BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 3.2 Doctors Table (extends users)
```sql
CREATE TABLE doctors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE NOT NULL,
    bio TEXT,
    photo VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### 3.3 Appointments Table
```sql
CREATE TABLE appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reference_no VARCHAR(20) UNIQUE NOT NULL,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    time_slot VARCHAR(50) NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed', 'no_show') DEFAULT 'pending',
    arrived BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id),
    FOREIGN KEY (doctor_id) REFERENCES users(id),
    UNIQUE KEY unique_booking (patient_id, doctor_id, appointment_date, time_slot)
);
```

### 3.4 Doctor Schedules Table
```sql
CREATE TABLE doctor_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    doctor_id INT NOT NULL,
    day_of_week TINYINT NOT NULL, -- 0=Sunday, 1=Monday, ..., 6=Saturday
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_schedule (doctor_id, day_of_week)
);
```

### 3.5 Blocked Slots Table
```sql
CREATE TABLE blocked_slots (
    id INT PRIMARY KEY AUTO_INCREMENT,
    doctor_id INT,
    blocked_date DATE NOT NULL,
    start_time TIME,
    end_time TIME,
    is_all_day BOOLEAN DEFAULT FALSE,
    reason VARCHAR(255),
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

### 3.6 Clinic Settings Table
```sql
CREATE TABLE clinic_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL
);
```

### 3.7 Holidays Table
```sql
CREATE TABLE holidays (
    id INT PRIMARY KEY AUTO_INCREMENT,
    holiday_date DATE UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

### 3.8 Notifications Table
```sql
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type ENUM('arrival', 'reminder', 'confirmation', 'cancellation', 'new_appointment') NOT NULL,
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    related_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### 3.9 Email Queue Table (for background processing)
```sql
CREATE TABLE email_queue (
    id INT PRIMARY KEY AUTO_INCREMENT,
    to_email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    body TEXT NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    scheduled_at DATETIME NOT NULL,
    sent_at DATETIME,
    attempts INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## 4. UI/UX Specification

### 4.1 Color Palette
- **Primary:** `#1b5e4a` (Deep teal-green)
- **Primary Light:** `#2d7a62`
- **Primary Dark:** `#0f3d2e`
- **Accent:** `#1abc9c` (Bright teal)
- **Secondary:** `#34495e` (Dark slate)
- **Background:** `#f8faf9` (Off-white)
- **Card Background:** `#ffffff`
- **Text Primary:** `#222222`
- **Text Secondary:** `#666666`
- **Error:** `#e74c3c`
- **Success:** `#27ae60`
- **Warning:** `#f39c12`

### 4.2 Typography
- **Primary Font:** 'Segoe UI', system-ui, sans-serif
- **Headings Font:** 'Segoe UI', system-ui, sans-serif
- **Font Sizes:**
  - H1: 28px
  - H2: 24px
  - H3: 20px
  - Body: 14px
  - Small: 12px

### 4.3 Spacing System
- **Base unit:** 8px
- **Padding small:** 8px
- **Padding medium:** 16px
- **Padding large:** 24px
- **Margin between sections:** 32px
- **Border radius:** 8px (cards), 4px (buttons/inputs)

### 4.4 Responsive Breakpoints
- **Mobile:** < 768px
- **Tablet:** 768px - 1024px
- **Desktop:** > 1024px

### 4.5 Layout Structure

#### Patient Portal (Public)
- Fixed navigation bar with logo, links, and login/booking buttons
- Hero section with clinic information
- Doctors listing with specialization filters
- Appointment booking wizard (3 steps)
- Footer with contact info

#### Dashboard Layouts (Clerk/Doctor)
- Sidebar navigation (260px width)
- Main content area with header
- Quick action buttons
- Notification bell with badge

## 5. Feature Specifications

### 5.1 Patient Features

#### Registration & Login
- Email-based registration with password
- Login with email/password
- Password reset capability
- Profile management with email notification preferences

#### Appointment Booking
- **Step 1 - Doctor Selection:**
  - Search by doctor name
  - Filter by specialization
  - View doctor details and schedule
- **Step 2 - Date & Time Selection:**
  - Calendar showing available dates (doctor's working days, non-blocked dates, non-holiday)
  - Time slots based on doctor's schedule
  - Unavailable slots shown as disabled (already booked, blocked, outside working hours)
- **Step 3 - Details & Confirmation:**
  - Required reason for visit
  - Full booking summary displayed
  - Confirm button to submit
- **Post-Booking:**
  - Generate unique reference number (format: APT-YYYYMMDD-XXXX)
  - Send confirmation email
  - Add to patient's upcoming appointments list
  - Prevent duplicate bookings (same patient, doctor, date, time)

#### My Appointments
- View upcoming appointments
- View past appointments
- Cancel pending appointments
- View booking reference numbers

### 5.2 Clerk Features

#### Schedule Management
- Define each doctor's weekly working hours (select days + time ranges)
- Changes apply to future bookings only (existing confirmed appointments unaffected)
- View all doctor schedules in a table

#### Blocked Slots Management
- Block individual dates or date ranges for specific doctors
- Optional reason for blocking
- Blocked slots immediately hidden from booking calendar
- Unblock capability (only if no appointments exist in that slot)

#### Clinic Settings
- Set clinic-wide operating hours
- Mark holiday dates with names
- Holidays visually marked on booking calendar
- Clinic hours override individual doctor availability when closed

#### Appointment Management
- View all appointments with filters
- Mark patient as arrived
- Cancel appointments
- View new same-day appointment alerts

### 5.3 Doctor Features

#### View Appointments
- View today's schedule
- View upcoming appointments
- View patient details for each appointment
- Mark appointment as completed

#### Notifications
- In-app notification when clerk marks patient as arrived
- Notification badge count
- Mark as read on open

### 5.4 Notification System

#### Email Notifications
- **Booking Confirmation:** Immediate email with reference number, doctor, date, time, clinic address
- **Cancellation:** Immediate email with reference number
- **24-Hour Reminder:** Email with doctor name, date, time, clinic address, opt-out link
- **1-Hour Reminder:** Email with doctor name, date, time, clinic address

#### In-App Notifications
- Doctor receives notification when patient arrives
- Clerk dashboard shows real-time alerts for same-day appointments

## 6. Role-Based Access Control

### 6.1 Permissions Matrix

| Feature | Patient | Doctor | Clerk |
|---------|---------|--------|-------|
| Register/Login | ✓ | ✓ | ✓ |
| Book Appointment | ✓ | ✗ | ✗ |
| View Own Appointments | ✓ | ✓ | ✓ |
| Cancel Appointment | ✓ | ✗ | ✓ |
| Manage Schedules | ✗ | ✗ | ✓ |
| Block/Unblock Slots | ✗ | ✗ | ✓ |
| Manage Clinic Hours | ✗ | ✗ | ✓ |
| Manage Holidays | ✗ | ✗ | ✓ |
| Mark Patient Arrived | ✗ | ✗ | ✓ |
| View All Appointments | ✗ | Own | All |
| Receive Notifications | ✓ | ✓ | ✓ |

### 6.2 Session Management
- Role stored in session after login
- Access control checks on each page
- Redirect to appropriate dashboard based on role

## 7. Background Job Scheduler

### 7.1 Email Reminder Script
- PHP CLI script: `cron/send_reminders.php`
- Runs every 15 minutes (via Windows Task Scheduler)
- Checks for appointments needing reminders
- Sends emails for:
  - 24-hour reminders (appointment - 24 hours)
  - 1-hour reminders (appointment - 1 hour)
- Updates email queue status

### 7.2 Email Sending
- Use PHP mail() function
- Queue-based system for reliability
- Store emails in email_queue table
- Process pending emails

## 8. Page Structure

### 8.1 Public Pages
- `index.php` - Landing page
- `doctors.php` - Doctors listing
- `book.php` - Appointment booking wizard
- `details.php` - Booking details form
- `confirm.php` - Confirmation page
- `thankyou.php` - Success page with reference
- `login.php` - Login page
- `register.php` - Patient registration
- `patient/` - Patient dashboard directory

### 8.2 Clerk Pages
- `clerk/index.php` - Dashboard with alerts
- `clerk/schedules.php` - Manage doctor schedules
- `clerk/schedule-edit.php` - Edit specific schedule
- `clerk/blocked-slots.php` - Manage blocked dates
- `clerk/appointments.php` - View all appointments
- `clerk/patients.php` - View patients
- `clerk/settings.php` - Clinic settings & holidays

### 8.3 Doctor Pages
- `doctor/index.php` - Dashboard
- `doctor/appointments.php` - View appointments
- `doctor/view.php` - View appointment details

### 8.4 API/Include Files
- `includes/db.php` - Database connection
- `includes/auth.php` - Authentication functions
- `includes/functions.php` - Utility functions
- `includes/header.php` - Common header
- `includes/footer.php` - Common footer

## 9. Acceptance Criteria

### 9.1 Authentication
- [ ] Patients can register with email and password
- [ ] All users can log in with their credentials
- [ ] Role-based redirection works correctly
- [ ] Sessions persist across page loads

### 9.2 Appointment Booking
- [ ] Patients can search doctors by name
- [ ] Patients can filter by specialization
- [ ] Calendar shows only available dates
- [ ] Booked/blocked slots are hidden/disabled
- [ ] Reason for visit is required
- [ ] Confirmation screen shows all details
- [ ] Duplicate bookings are prevented
- [ ] Reference number is generated
- [ ] Confirmation email is sent

### 9.3 Schedule Management
- [ ] Clerks can set doctor weekly schedules
- [ ] Schedule changes don't affect existing appointments
- [ ] Clerks can block dates with optional reason
- [ ] Blocked slots are hidden from patients
- [ ] Clerks can unblock if no appointments exist

### 9.4 Notifications
- [ ] 24-hour reminder emails are sent
- [ ] 1-hour reminder emails are sent
- [ ] Doctors receive in-app notification on patient arrival
- [ ] Clerk dashboard shows same-day appointment alerts

### 9.5 Clinic Settings
- [ ] Clerks can set clinic operating hours
- [ ] Holidays are marked on calendar
- [ ] Clinic closed hours override doctor availability

## 10. Demo Accounts

| Role | Username | Password |
|------|----------|----------|
| Clerk | clerk | clerk123 |
| Doctor | doctor | doctor123 |
| Patient | patient@clinic.com | patient123 |

## 11. File Structure

```
Appointment_System_Clinic-main/
├── SPEC.md
├── index.php
├── login.php
├── register.php
├── doctors.php
├── book.php
├── details.php
├── confirm.php
├── thankyou.php
├── style.css
├── panel.css
├── favicon.svg
├── imgs/
├── includes/
│   ├── db.php
│   ├── auth.php
│   ├── functions.php
│   ├── header.php
│   └── footer.php
├── patient/
│   ├── index.php
│   ├── appointments.php
│   ├── profile.php
│   └── sidebar.php
├── clerk/
│   ├── index.php
│   ├── schedules.php
│   ├── schedule-edit.php
│   ├── blocked-slots.php
│   ├── appointments.php
│   ├── patients.php
│   ├── settings.php
│   └── sidebar.php
├── doctor/
│   ├── index.php
│   ├── appointments.php
│   ├── view.php
│   └── sidebar.php
└── cron/
    ├── send_reminders.php
    └── process_emails.php
```