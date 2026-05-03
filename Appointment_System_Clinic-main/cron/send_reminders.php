<?php
/**
 * Appointment Reminder Script
 * Run this via CLI to send appointment reminders
 * Usage: php send_reminders.php
 *
 * This script checks for appointments that need reminders and queues emails
 */

// Define the root path
define('ROOT_PATH', dirname(__DIR__));

// Include required files
require_once ROOT_PATH . '/includes/db.php';
require_once ROOT_PATH . '/includes/functions.php';

echo "Starting appointment reminder check...\n";

$now = date('Y-m-d H:i:s');
$clinicAddress = getSetting('clinic_address', '123 Medical Center Drive, City, State 12345');

// Check for 24-hour reminders (24 hours before appointment)
$reminder24 = dbFetchAll("
    SELECT a.*, p.full_name as patient_name, p.email as patient_email,
           d.full_name as doctor_name, d.specialization
    FROM appointments a
    JOIN users p ON p.id = a.patient_id
    JOIN users d ON d.id = a.doctor_id
    WHERE a.status IN ('pending', 'confirmed')
    AND a.appointment_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
    AND p.email_notifications = TRUE
");

// Check for 1-hour reminders (1 hour before appointment)
$reminder1 = dbFetchAll("
    SELECT a.*, p.full_name as patient_name, p.email as patient_email,
           d.full_name as doctor_name, d.specialization
    FROM appointments a
    JOIN users p ON p.id = a.patient_id
    JOIN users d ON d.id = a.doctor_id
    WHERE a.status IN ('pending', 'confirmed')
    AND a.appointment_date = CURDATE()
    AND HOUR(a.time_slot) = HOUR(CURTIME()) + 1
    AND p.email_notifications = TRUE
");

$sent = 0;

// Send 24-hour reminders
foreach ($reminder24 as $appt) {
    // Check if reminder already sent
    $existing = dbFetch("
        SELECT id FROM email_queue
        WHERE to_email = ? AND subject LIKE '%Reminder%24 hours%'
        AND created_at > DATE_SUB(NOW(), INTERVAL 2 DAY)
    ", [$appt['patient_email']]);

    if (!$existing) {
        $subject = 'Appointment Reminder - 24 Hours Notice - ' . $appt['reference_no'];
        $body = "Dear " . $appt['patient_name'] . ",\n\n";
        $body .= "This is a reminder that your appointment is tomorrow.\n\n";
        $body .= "Reference Number: " . $appt['reference_no'] . "\n";
        $body .= "Doctor: " . $appt['doctor_name'] . "\n";
        $body .= "Specialization: " . $appt['specialization'] . "\n";
        $body .= "Date: " . date('F j, Y', strtotime($appt['appointment_date'])) . "\n";
        $body .= "Time: " . $appt['time_slot'] . "\n";
        $body .= "Clinic Address: " . $clinicAddress . "\n\n";
        $body .= "Please arrive 15 minutes before your scheduled time.\n\n";
        $body .= "To opt out of reminders, update your profile settings.\n\n";
        $body .= "Best regards,\nMedi Clinic";

        dbInsert('email_queue', [
            'to_email' => $appt['patient_email'],
            'subject' => $subject,
            'body' => $body,
            'scheduled_at' => date('Y-m-d H:i:s'),
            'status' => 'pending'
        ]);

        $sent++;
        echo "Queued 24-hour reminder for " . $appt['patient_email'] . "\n";
    }
}

// Send 1-hour reminders
foreach ($reminder1 as $appt) {
    // Check if reminder already sent
    $existing = dbFetch("
        SELECT id FROM email_queue
        WHERE to_email = ? AND subject LIKE '%Reminder%1 hour%'
        AND created_at > DATE_SUB(NOW(), INTERVAL 2 HOUR)
    ", [$appt['patient_email']]);

    if (!$existing) {
        $subject = 'Appointment Reminder - 1 Hour Notice - ' . $appt['reference_no'];
        $body = "Dear " . $appt['patient_name'] . ",\n\n";
        $body .= "Your appointment is in 1 hour.\n\n";
        $body .= "Reference Number: " . $appt['reference_no'] . "\n";
        $body .= "Doctor: " . $appt['doctor_name'] . "\n";
        $body .= "Time: " . $appt['time_slot'] . "\n";
        $body .= "Clinic Address: " . $clinicAddress . "\n\n";
        $body .= "Please arrive as soon as possible.\n\n";
        $body .= "Best regards,\nMedi Clinic";

        dbInsert('email_queue', [
            'to_email' => $appt['patient_email'],
            'subject' => $subject,
            'body' => $body,
            'scheduled_at' => date('Y-m-d H:i:s'),
            'status' => 'pending'
        ]);

        $sent++;
        echo "Queued 1-hour reminder for " . $appt['patient_email'] . "\n";
    }
}

echo "Reminder check complete. Queued $sent emails.\n";