<?php
/**
 * Email Queue Processor
 * Run this via CLI to process pending emails
 * Usage: php process_emails.php
 *
 * This script sends all pending emails in the queue
 */

// Define the root path
define('ROOT_PATH', dirname(__DIR__));

// Include required files
require_once ROOT_PATH . '/includes/db.php';
require_once ROOT_PATH . '/includes/functions.php';

echo "Starting email queue processing...\n";

// Get pending emails
$pendingEmails = dbFetchAll("
    SELECT * FROM email_queue
    WHERE status = 'pending'
    AND scheduled_at <= NOW()
    ORDER BY scheduled_at ASC
    LIMIT 50
");

$sent = 0;
$failed = 0;

foreach ($pendingEmails as $email) {
    // Attempt to send email
    $headers = "From: " . getSetting('clinic_email', 'info@mediclinic.com') . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    $result = mail($email['to_email'], $email['subject'], $email['body'], $headers);

    if ($result) {
        dbUpdate('email_queue', [
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s')
        ], "id = ?", [$email['id']]);
        $sent++;
        echo "Sent email to " . $email['to_email'] . "\n";
    } else {
        dbUpdate('email_queue', [
            'attempts' => $email['attempts'] + 1,
            'status' => $email['attempts'] >= 3 ? 'failed' : 'pending'
        ], "id = ?", [$email['id']]);
        $failed++;
        echo "Failed to send to " . $email['to_email'] . "\n";
    }
}

echo "Email queue processing complete. Sent: $sent, Failed: $failed\n";