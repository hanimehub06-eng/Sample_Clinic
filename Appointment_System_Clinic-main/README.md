# Medi Clinic Appointment Management System

A web-based appointment management system designed for clinics to efficiently manage patient appointments, doctor schedules, and clinic operations.

## Features

### For Patients
- **User Registration & Authentication** - Secure account creation and login
- **Doctor Directory** - Browse doctors by name or specialization with bio and availability
- **Appointment Booking** - 3-step booking process:
  1. Select doctor and preferred date/time
  2. Provide reason for visit
  3. Review and confirm booking
- **Appointment Management** - View, track, and cancel appointments
- **Email Notifications** - Automated emails for:
  - Appointment confirmation
  - Reminders (24 hours and 1 hour before appointment)
  - Cancellation notifications
- **Notification Preferences** - Opt-in/out of email reminders in profile settings
- **Appointment History** - View past and upcoming appointments

### For Doctors
- **Professional Dashboard** - Overview of today's and upcoming appointments
- **Appointment Schedule** - View all appointments with patient details
- **Arrival Notifications** - Real-time in-app alerts when patients arrive
- **Notification Center** - Manage appointment-related notifications

### For Clinic Staff (Clerks)
- **Clinic Dashboard** - Real-time overview with:
  - Today's appointments count
  - New same-day appointment alerts
  - Pending appointments
  - Recent bookings
- **Doctor Schedule Management** - Define and modify weekly working hours per doctor
- **Appointment Blocking** - Block specific dates or date ranges per doctor (e.g., vacation, special events)
- **Clinic Settings** - Configure:
  - Clinic name, address, phone, email
  - Operating hours
- **Holiday Management** - Mark clinic-wide holidays and special dates
- **Appointment Management** - View all appointments with filters (doctor, status, date)
- **Patient Arrival Tracking** - Mark patients as arrived and notify doctors

## Technical Stack

- **Backend**: PHP 7.4+
- **Database**: MariaDB/MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Architecture**: MVC-inspired with role-based access control

## Database

The system uses a relational database with the following main tables:
- `users` - User accounts (patients, doctors, clerks)
- `doctors` - Extended doctor information
- `appointments` - Appointment records with status tracking
- `doctor_schedules` - Weekly working hours per doctor
- `blocked_slots` - Blocked dates/times
- `clinic_settings` - Clinic configuration
- `holidays` - Holiday dates
- `notifications` - In-app notifications
- `email_queue` - Email delivery queue

## User Roles

### Patient
- Book and manage appointments
- View appointment history
- Manage notification preferences
- Update profile

### Doctor
- View assigned appointments
- Receive arrival notifications
- Access notification center

### Clerk
- Manage doctor schedules
- Configure clinic settings
- Mark patient arrivals
- Manage all appointments
- Set holidays and blocked dates

## Security

- **Password Security**: Bcrypt hashing for all passwords
- **SQL Injection Prevention**: Prepared statements with parameterized queries
- **Role-Based Access Control**: Middleware to enforce role permissions
- **Session Management**: Secure PHP sessions for user authentication

## Getting Started

See [SETUP.md](SETUP.md) for detailed installation and configuration instructions.

## Demo Credentials

After setting up the database:
- **Clerk**: username `clerk`, password `clerk123`
- **Doctor**: username `doctor`, password `doctor123`
- **Patient**: email `patient@clinic.com`, password `patient123`

## Project Structure

```
Appointment_System_Clinic/
├── includes/               # Core PHP files
│   ├── db.php             # Database connection
│   ├── auth.php           # Authentication functions
│   ├── functions.php      # Business logic functions
│   ├── header.php         # Navigation header
│   └── footer.php         # Footer template
├── patient/               # Patient dashboard pages
├── doctor/                # Doctor dashboard pages
├── clerk/                 # Clerk management pages
├── cron/                  # Background job scripts
│   ├── send_reminders.php # Appointment reminder queue
│   └── process_emails.php # Email delivery processor
├── imgs/                  # Images and assets
├── style.css              # Main stylesheet
├── panel.css              # Admin panel stylesheet
├── database_setup.sql     # Database initialization
├── index.php              # Public homepage
├── login.php              # Login page
├── register.php           # Patient registration
├── doctors.php            # Doctor directory
├── book.php               # Appointment booking
├── details.php            # Booking details/reason
└── confirm.php            # Booking confirmation
```

## Key Features in Detail

### Appointment Booking
1. Patient selects doctor and browses calendar
2. Booked/blocked slots are hidden from selection
3. Holidays are marked visually
4. Patient enters reason for visit
5. Booking details are reviewed
6. On confirmation: reference number is generated, email is sent, appointment is added immediately

### Schedule Management
- Weekly schedule applies to future bookings only
- Existing confirmed appointments are protected
- Individual date blocks can be created (e.g., lunch, vacation)
- Blocks can be optionally doctor-specific or clinic-wide

### Email Notifications
- **Queue-based system** for reliability
- Automated reminders 24 hours before and 1 hour before appointment
- Respects patient notification preferences
- Each email includes clinic address and appointment details

## Maintenance

### Background Jobs Required

Run these via cron jobs for automated operation:

```bash
# Queue appointment reminders (run hourly)
0 * * * * php /path/to/clinic/cron/send_reminders.php

# Process email queue (run every 5 minutes)
*/5 * * * * php /path/to/clinic/cron/process_emails.php
```

## Support

For issues or questions, contact clinic administration through the application settings page.

## License

Proprietary - Medi Clinic
