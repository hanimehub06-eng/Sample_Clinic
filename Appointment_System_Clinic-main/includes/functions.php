<?php
require_once __DIR__ . '/db.php';

function getSetting($key, $default = '') {
    $row = dbFetch("SELECT setting_value FROM clinic_settings WHERE setting_key = ?", [$key]);
    return $row ? $row['setting_value'] : $default;
}

function getDoctors($specialty = null) {
    if ($specialty) {
        return dbFetchAll("
            SELECT u.*, d.bio, d.photo
            FROM users u
            JOIN doctors d ON d.user_id = u.id
            WHERE u.role = 'doctor' AND u.specialization = ?
            ORDER BY u.full_name
        ", [$specialty]);
    }
    return dbFetchAll("
        SELECT u.*, d.bio, d.photo
        FROM users u
        JOIN doctors d ON d.user_id = u.id
        WHERE u.role = 'doctor'
        ORDER BY u.full_name
    ");
}

function getDoctorById($id) {
    return dbFetch("
        SELECT u.*, d.bio, d.photo
        FROM users u
        JOIN doctors d ON d.user_id = u.id
        WHERE u.id = ? AND u.role = 'doctor'
    ", [$id]);
}

function getSpecializations() {
    return dbFetchAll("
        SELECT DISTINCT specialization
        FROM users
        WHERE role = 'doctor' AND specialization IS NOT NULL
        ORDER BY specialization
    ");
}

function getDoctorSchedule($doctorId) {
    return dbFetchAll("
        SELECT * FROM doctor_schedules
        WHERE doctor_id = ? AND is_active = TRUE
        ORDER BY day_of_week
    ", [$doctorId]);
}

function getBlockedSlots($doctorId = null, $date = null) {
    $sql = "SELECT * FROM blocked_slots WHERE 1=1";
    $params = [];

    if ($doctorId) {
        $sql .= " AND (doctor_id = ? OR doctor_id IS NULL)";
        $params[] = $doctorId;
    }
    if ($date) {
        $sql .= " AND blocked_date = ?";
        $params[] = $date;
    }

    return dbFetchAll($sql, $params);
}

function isDateBlocked($doctorId, $date) {
    $blocked = dbFetch("
        SELECT * FROM blocked_slots
        WHERE blocked_date = ?
        AND (doctor_id = ? OR doctor_id IS NULL)
    ", [$date, $doctorId]);
    return $blocked !== null;
}

function isHoliday($date) {
    return dbFetch("SELECT * FROM holidays WHERE holiday_date = ?", [$date]) !== null;
}

function getHolidays() {
    return dbFetchAll("SELECT * FROM holidays ORDER BY holiday_date");
}

function getTimeSlots($doctorId, $date) {
    $schedule = dbFetch("
        SELECT start_time, end_time FROM doctor_schedules
        WHERE doctor_id = ? AND day_of_week = ? AND is_active = TRUE
    ", [$doctorId, date('N', strtotime($date)) - 1]);

    if (!$schedule) {
        return [];
    }

    $slots = [];
    $start = strtotime($schedule['start_time']);
    $end = strtotime($schedule['end_time']);

    while ($start < $end) {
        $slotStart = date('H:i:s', $start);
        $slotEnd = date('H:i:s', strtotime('+1 hour', $start));
        $label = date('g:i A', $start) . ' - ' . date('g:i A', strtotime('+1 hour', $start));

        // Check if slot is blocked
        $blocked = dbFetch("
            SELECT * FROM blocked_slots
            WHERE blocked_date = ?
            AND doctor_id = ?
            AND (
                (is_all_day = TRUE)
                OR (
                    start_time <= ? AND end_time >= ?
                )
            )
        ", [$date, $doctorId, $slotStart, $slotEnd]);

        // Check if slot is already booked
        $booked = dbFetch("
            SELECT id FROM appointments
            WHERE doctor_id = ?
            AND appointment_date = ?
            AND time_slot = ?
            AND status NOT IN ('cancelled')
        ", [$doctorId, $date, $label]);

        $slots[] = [
            'time' => $label,
            'available' => !$blocked && !$booked
        ];

        $start = strtotime('+1 hour', $start);
    }

    return $slots;
}

function generateReferenceNo() {
    $date = date('Ymd');
    $count = dbFetch("
        SELECT COUNT(*) as cnt FROM appointments
        WHERE reference_no LIKE ?
    ", ['APT-' . $date . '-%']);

    $seq = ($count['cnt'] ?? 0) + 1;
    return 'APT-' . $date . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
}

function createAppointment($patientId, $doctorId, $date, $timeSlot, $reason) {
    // Check for duplicate
    $exists = dbFetch("
        SELECT id FROM appointments
        WHERE patient_id = ? AND doctor_id = ?
        AND appointment_date = ? AND time_slot = ?
        AND status NOT IN ('cancelled')
    ", [$patientId, $doctorId, $date, $timeSlot]);

    if ($exists) {
        return ['success' => false, 'error' => 'Duplicate booking'];
    }

    $referenceNo = generateReferenceNo();

    $id = dbInsert('appointments', [
        'reference_no' => $referenceNo,
        'patient_id' => $patientId,
        'doctor_id' => $doctorId,
        'appointment_date' => $date,
        'time_slot' => $timeSlot,
        'reason' => $reason,
        'status' => 'pending'
    ]);

    return ['success' => true, 'id' => $id, 'reference_no' => $referenceNo];
}

function getPatientAppointments($patientId, $status = null) {
    $sql = "
        SELECT a.*, u.full_name as doctor_name, u.specialization
        FROM appointments a
        JOIN users u ON u.id = a.doctor_id
        WHERE a.patient_id = ?
    ";
    $params = [$patientId];

    if ($status) {
        $sql .= " AND a.status = ?";
        $params[] = $status;
    }

    $sql .= " ORDER BY a.appointment_date DESC, a.time_slot DESC";

    return dbFetchAll($sql, $params);
}

function getDoctorAppointments($doctorId, $date = null) {
    $sql = "
        SELECT a.*, u.full_name as patient_name, u.email as patient_email, u.phone as patient_phone
        FROM appointments a
        JOIN users u ON u.id = a.patient_id
        WHERE a.doctor_id = ?
    ";
    $params = [$doctorId];

    if ($date) {
        $sql .= " AND a.appointment_date = ?";
        $params[] = $date;
    }

    $sql .= " ORDER BY a.appointment_date ASC, a.time_slot ASC";

    return dbFetchAll($sql, $params);
}

function getAllAppointments($filters = []) {
    $sql = "
        SELECT a.*,
            p.full_name as patient_name, p.email as patient_email, p.phone as patient_phone,
            d.full_name as doctor_name, d.specialization
        FROM appointments a
        JOIN users p ON p.id = a.patient_id
        JOIN users d ON d.id = a.doctor_id
        WHERE 1=1
    ";
    $params = [];

    if (!empty($filters['doctor_id'])) {
        $sql .= " AND a.doctor_id = ?";
        $params[] = $filters['doctor_id'];
    }
    if (!empty($filters['status'])) {
        $sql .= " AND a.status = ?";
        $params[] = $filters['status'];
    }
    if (!empty($filters['date'])) {
        $sql .= " AND a.appointment_date = ?";
        $params[] = $filters['date'];
    }

    $sql .= " ORDER BY a.appointment_date DESC, a.time_slot DESC";

    return dbFetchAll($sql, $params);
}

function cancelAppointment($id, $patientId = null) {
    $where = "id = ?";
    $params = [$id];

    if ($patientId) {
        $where .= " AND patient_id = ?";
        $params[] = $patientId;
    }

    return dbUpdate('appointments', ['status' => 'cancelled'], $where, $params);
}

function markArrived($id) {
    dbUpdate('appointments', ['arrived' => 1], "id = ?", [$id]);

    $appointment = dbFetch("
        SELECT a.*, p.full_name as patient_name, d.full_name as doctor_name
        FROM appointments a
        JOIN users p ON p.id = a.patient_id
        JOIN users d ON d.id = a.doctor_id
        WHERE a.id = ?
    ", [$id]);

    if ($appointment) {
        // Create notification for doctor
        dbInsert('notifications', [
            'user_id' => $appointment['doctor_id'],
            'type' => 'arrival',
            'title' => 'Patient Arrived',
            'message' => $appointment['patient_name'] . ' has arrived for their appointment at ' . $appointment['time_slot'],
            'related_id' => $id
        ]);
    }

    return true;
}

function addNotification($userId, $type, $title, $message, $relatedId = null) {
    return dbInsert('notifications', [
        'user_id' => $userId,
        'type' => $type,
        'title' => $title,
        'message' => $message,
        'related_id' => $relatedId
    ]);
}

function getNotifications($userId, $unreadOnly = false) {
    $sql = "SELECT * FROM notifications WHERE user_id = ?";
    $params = [$userId];

    if ($unreadOnly) {
        $sql .= " AND is_read = FALSE";
    }

    $sql .= " ORDER BY created_at DESC";

    return dbFetchAll($sql, $params);
}

function markNotificationRead($id, $userId) {
    return dbUpdate('notifications', ['is_read' => 1], "id = ? AND user_id = ?", [$id, $userId]);
}

function getUnreadNotificationCount($userId) {
    $result = dbFetch("SELECT COUNT(*) as cnt FROM notifications WHERE user_id = ? AND is_read = FALSE", [$userId]);
    return $result['cnt'] ?? 0;
}

function queueEmail($toEmail, $subject, $body, $scheduledAt = null) {
    if ($scheduledAt === null) {
        $scheduledAt = date('Y-m-d H:i:s');
    }

    return dbInsert('email_queue', [
        'to_email' => $toEmail,
        'subject' => $subject,
        'body' => $body,
        'scheduled_at' => $scheduledAt,
        'status' => 'pending'
    ]);
}

function sendAppointmentConfirmation($appointmentId) {
    $appointment = dbFetch("
        SELECT a.*,
            p.full_name as patient_name, p.email as patient_email,
            d.full_name as doctor_name, d.specialization
        FROM appointments a
        JOIN users p ON p.id = a.patient_id
        JOIN users d ON d.id = a.doctor_id
        WHERE a.id = ?
    ", [$appointmentId]);

    if (!$appointment) return false;

    $clinicAddress = getSetting('clinic_address', '123 Medical Center Drive, City, State 12345');

    $subject = 'Appointment Confirmation - ' . $appointment['reference_no'];
    $body = "Dear " . $appointment['patient_name'] . ",\n\n";
    $body .= "Your appointment has been confirmed!\n\n";
    $body .= "Reference Number: " . $appointment['reference_no'] . "\n";
    $body .= "Doctor: " . $appointment['doctor_name'] . "\n";
    $body .= "Specialization: " . $appointment['specialization'] . "\n";
    $body .= "Date: " . date('F j, Y', strtotime($appointment['appointment_date'])) . "\n";
    $body .= "Time: " . $appointment['time_slot'] . "\n";
    $body .= "Reason: " . $appointment['reason'] . "\n\n";
    $body .= "Clinic Address: " . $clinicAddress . "\n\n";
    $body .= "Please arrive 15 minutes before your scheduled time.\n\n";
    $body .= "To cancel or reschedule, please contact us.\n\n";
    $body .= "Best regards,\nMedi Clinic";

    return queueEmail($appointment['patient_email'], $subject, $body);
}

function sendCancellationEmail($appointmentId) {
    $appointment = dbFetch("
        SELECT a.*,
            p.full_name as patient_name, p.email as patient_email,
            d.full_name as doctor_name
        FROM appointments a
        JOIN users p ON p.id = a.patient_id
        JOIN users d ON d.id = a.doctor_id
        WHERE a.id = ?
    ", [$appointmentId]);

    if (!$appointment) return false;

    $subject = 'Appointment Cancelled - ' . $appointment['reference_no'];
    $body = "Dear " . $appointment['patient_name'] . ",\n\n";
    $body .= "Your appointment has been cancelled.\n\n";
    $body .= "Reference Number: " . $appointment['reference_no'] . "\n";
    $body .= "Doctor: " . $appointment['doctor_name'] . "\n";
    $body .= "Date: " . date('F j, Y', strtotime($appointment['appointment_date'])) . "\n";
    $body .= "Time: " . $appointment['time_slot'] . "\n\n";
    $body .= "If you would like to rebook, please visit our website.\n\n";
    $body .= "Best regards,\nMedi Clinic";

    return queueEmail($appointment['patient_email'], $subject, $body);
}

function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

function formatTime($time) {
    return date('g:i A', strtotime($time));
}