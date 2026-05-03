# Medi Clinic Appointment System - Setup Guide

## Prerequisites

Before setting up the Appointment Management System, ensure you have:

- **PHP 7.4 or higher** with `php-mysql` or `php-mysqli` extension
- **MariaDB 10.3+** or **MySQL 5.7+**
- **Web Server**: Apache 2.4+ (with `mod_rewrite` enabled) or Nginx
- **File Permissions**: Write access to application directory for logs and temporary files

## Installation Steps

### 1. Database Setup

#### Option A: Using XAMPP/Local Environment

1. Start MariaDB/MySQL service
2. Connect to the database server:
   ```bash
   mysql -u root -p
   ```
3. Create a non-root user for the application:
   ```sql
   CREATE USER 'clinic_admin'@'localhost' IDENTIFIED BY '12345';
   GRANT ALL PRIVILEGES ON clinic_db.* TO 'clinic_admin'@'localhost';
   FLUSH PRIVILEGES;
   ```

4. Import the database schema:
   ```bash
   mysql -u clinic_user -p clinic_db < database_setup.sql
   ```
   When prompted, enter password: `clinic_password`

5. Verify successful import by checking tables:
   ```bash
   mysql -u clinic_user -p -e "USE clinic_db; SHOW TABLES;"
   ```

#### Option B: Using Command Line (One-Step)

```bash
# Create database and user, then import schema
mysql -u root -p -e "
CREATE DATABASE IF NOT EXISTS clinic_db;
CREATE USER IF NOT EXISTS 'clinic_user'@'localhost' IDENTIFIED BY 'clinic_password';
GRANT ALL PRIVILEGES ON clinic_db.* TO 'clinic_user'@'localhost';
FLUSH PRIVILEGES;
" && mysql -u clinic_user -p clinic_db < database_setup.sql
```

### 2. Application Configuration

1. **Database Connection**: The application uses settings in `includes/db.php`
   - Host: `localhost`
   - Database: `clinic_db`
   - User: `clinic_user`
   - Password: `clinic_password`

   Update these values if you're using different credentials:
   ```php
   // in includes/db.php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'clinic_db');
   define('DB_USER', 'clinic_user');
   define('DB_PASS', 'clinic_password');
   ```

2. **Directory Permissions**: Ensure the application directory is readable/writable:
   ```bash
   # On Linux/Mac
   chmod -R 755 /path/to/Appointment_System_Clinic-main
   chmod -R 775 /path/to/Appointment_System_Clinic-main/logs  # if logs directory exists
   
   # On Windows: Right-click folder → Properties → Security → Edit permissions as needed
   ```

3. **Email Configuration**: The system uses PHP's `mail()` function
   - On XAMPP: Configure `php.ini` for sendmail (or use a test SMTP service)
   - In production: Configure a proper SMTP server or use a mail service like SendGrid, Mailgun, etc.

### 3. Web Server Configuration

#### For Apache

1. Enable `mod_rewrite`:
   ```bash
   a2enmod rewrite
   ```

2. Create/update `.htaccess` in the application root (usually already exists):
   ```apache
   <IfModule mod_rewrite.c>
       RewriteEngine On
       RewriteBase /Appointment_System_Clinic-main/
       RewriteCond %{REQUEST_FILENAME} !-f
       RewriteCond %{REQUEST_FILENAME} !-d
       RewriteRule ^index\.php$ - [L]
   </IfModule>
   ```

3. Restart Apache:
   ```bash
   # Linux
   sudo systemctl restart apache2
   
   # macOS (with Homebrew)
   brew services restart httpd
   ```

#### For XAMPP

1. Place the application in `htdocs/`:
   ```
   C:\xampp\htdocs\Appointment_System_Clinic-main
   ```
   or on Linux/Mac:
   ```
   /Applications/XAMPP/htdocs/Appointment_System_Clinic-main
   ```

2. Start Apache and MySQL from XAMPP Control Panel

### 4. Cron Job Setup (Background Tasks)

The system requires two cron jobs for automated operations:

#### Job 1: Queue Appointment Reminders (Hourly)

This job queues email reminders 24 hours and 1 hour before each appointment.

**On Linux/Mac**, add to crontab:
```bash
crontab -e
```

Add this line:
```
0 * * * * cd /path/to/Appointment_System_Clinic-main && php cron/send_reminders.php >> logs/reminders.log 2>&1
```

**On Windows**, use Task Scheduler:
1. Open Task Scheduler
2. Create Basic Task
3. Set trigger: Daily, repeat every 1 hour
4. Set action: Start program
   - Program: `C:\path\to\php\php.exe`
   - Arguments: `C:\xampp\htdocs\Appointment_System_Clinic-main\cron\send_reminders.php`

#### Job 2: Process Email Queue (Every 5 Minutes)

This job processes and sends all pending emails in the queue.

**On Linux/Mac**, add to crontab:
```bash
crontab -e
```

Add this line:
```
*/5 * * * * cd /path/to/Appointment_System_Clinic-main && php cron/process_emails.php >> logs/emails.log 2>&1
```

**On Windows**, use Task Scheduler:
1. Create Basic Task
2. Set trigger: Daily, repeat every 5 minutes
3. Set action: Start program
   - Program: `C:\path\to\php\php.exe`
   - Arguments: `C:\xampp\htdocs\Appointment_System_Clinic-main\cron\process_emails.php`

### 5. First Run & Verification

1. **Access the application** (assuming XAMPP on localhost):
   ```
   http://localhost/Appointment_System_Clinic-main/
   ```

2. **Test the demo accounts** (created by database_setup.sql):
   - **Clerk**: 
     - URL: `http://localhost/Appointment_System_Clinic-main/login.php`
     - Username: `clerk`
     - Password: `clerk123`
   
   - **Doctor**: 
     - URL: `http://localhost/Appointment_System_Clinic-main/login.php`
     - Username: `doctor`
     - Password: `doctor123`
   
   - **Patient**: 
     - URL: `http://localhost/Appointment_System_Clinic-main/login.php`
     - Email: `patient@clinic.com`
     - Password: `patient123`

3. **Create logs directory** (optional but recommended):
   ```bash
   mkdir -p /path/to/Appointment_System_Clinic-main/logs
   chmod 775 /path/to/Appointment_System_Clinic-main/logs
   ```

## Testing Workflow

### Patient Flow
1. Register as a new patient (or login with demo account)
2. Browse available doctors in "Doctors" section
3. Click a doctor to view availability
4. Book an appointment: select date/time → enter reason → confirm
5. View confirmation page with reference number
6. Check email for confirmation (if mail configured)
7. View appointment in "My Appointments"
8. Test cancellation and notification preferences in profile

### Clerk Flow
1. Login with clerk account
2. View dashboard showing today's appointments and pending bookings
3. Navigate to "Doctor Schedules" and edit a doctor's weekly hours
4. Go to "Settings" and:
   - Update clinic information (name, address, phone, email)
   - Set operating hours (start/end times)
   - Add holidays by date
5. Optionally test appointment blocking in "Blocked Dates"
6. Mark a patient as arrived for today's appointment

### Doctor Flow
1. Login with doctor account
2. View dashboard with assigned appointments
3. Check "My Appointments" for schedule
4. Navigate to "Notifications" to see in-app alerts
5. When a patient arrives (marked by clerk), receive arrival notification

### System Features
- Booked slots are hidden from available calendar
- Holiday dates are marked visually during booking
- Duplicate bookings for same patient/time are prevented
- Email reminders queue correctly for 24hrs and 1hr before appointments
- Blocked slots cannot be unblocked if appointments exist

## Troubleshooting

### Database Connection Error
- **Issue**: "Connection failed" message
- **Solution**: 
  - Verify MariaDB/MySQL is running
  - Check credentials in `includes/db.php`
  - Ensure `clinic_db` database exists: `mysql -u clinic_user -p -e "SHOW DATABASES;"`

### Email Not Sending
- **Issue**: Appointment confirmations not received
- **Solution**:
  - Verify `php.ini` sendmail configuration
  - Check mail server is running (or SMTP service configured)
  - Review PHP error logs for mail() errors
  - Test with: `php -r "mail('test@example.com', 'Test', 'Body');"`

### Permission Denied Errors
- **Issue**: Cannot write files or access pages
- **Solution**:
  - Check directory permissions: `ls -la /path/to/clinic`
  - Ensure web server user owns files: `chown -R www-data:www-data /path/to/clinic`
  - Make writable: `chmod -R 755 /path/to/clinic`

### Cron Jobs Not Running
- **Issue**: Reminders/emails not being processed
- **Solution**:
  - Check cron log: `grep CRON /var/log/syslog` (Linux)
  - Verify cron job syntax: `crontab -l`
  - Test manually: `php /path/to/cron/send_reminders.php`
  - Check file permissions: `ls -la /path/to/cron/*.php`

## Production Deployment

For production use, consider:

1. **Security**:
   - Change all demo account passwords immediately
   - Use HTTPS/SSL certificates
   - Store `includes/db.php` outside web root if possible
   - Implement rate limiting on login page

2. **Email**:
   - Use professional mail service (SendGrid, AWS SES, etc.)
   - Implement proper SPF, DKIM, DMARC records
   - Add "Reply-To" and "From" headers in `cron/process_emails.php`

3. **Database**:
   - Regular automated backups (daily minimum)
   - Use strong passwords (not 'clinic_password')
   - Restrict database user IP: `'clinic_user'@'192.168.1.%'`
   - Enable database encryption

4. **Performance**:
   - Enable query caching in MariaDB/MySQL
   - Use CDN for static assets
   - Monitor cron job execution
   - Consider database indexing for large datasets

5. **Monitoring**:
   - Set up error logging and alerts
   - Monitor email queue for failures
   - Track appointment and user statistics
   - Set up log rotation to prevent disk space issues

## Support

For issues or questions:
1. Check application logs for error messages
2. Review database tables to verify data integrity
3. Test user authentication with demo accounts
4. Verify cron jobs are executing properly
5. Contact clinic administration through the application settings page

## License

Proprietary - Medi Clinic
