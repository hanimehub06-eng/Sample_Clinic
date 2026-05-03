<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('clerk');

$user = getCurrentUser();
$doctorId = $_GET['doctor'] ?? null;

if (!$doctorId) {
    header('Location: schedules.php');
    exit;
}

$doctor = getDoctorById($doctorId);
if (!$doctor) {
    header('Location: schedules.php');
    exit;
}

$dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($d = 0; $d < 7; $d++) {
        $isActive = isset($_POST['active_' . $d]) ? 1 : 0;
        $startTime = $_POST['start_' . $d] ?? null;
        $endTime = $_POST['end_' . $d] ?? null;

        if ($isActive && $startTime && $endTime) {
            // Check if schedule exists
            $existing = dbFetch("SELECT id FROM doctor_schedules WHERE doctor_id = ? AND day_of_week = ?", [$doctorId, $d]);

            if ($existing) {
                // Check if there are existing appointments for future dates
                $today = date('Y-m-d');
                $hasAppointments = dbFetch("
                    SELECT COUNT(*) as cnt FROM appointments
                    WHERE doctor_id = ? AND appointment_date >= ? AND status != 'cancelled'
                ", [$doctorId, $today]);

                if ($hasAppointments['cnt'] == 0) {
                    // No existing appointments, safe to update
                    dbUpdate('doctor_schedules', [
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'is_active' => $isActive
                    ], "doctor_id = ? AND day_of_week = ?", [$doctorId, $d]);
                } else {
                    // Update but don't change times if appointments exist
                    dbUpdate('doctor_schedules', [
                        'is_active' => $isActive
                    ], "doctor_id = ? AND day_of_week = ?", [$doctorId, $d]);
                }
            } else {
                dbInsert('doctor_schedules', [
                    'doctor_id' => $doctorId,
                    'day_of_week' => $d,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'is_active' => $isActive
                ]);
            }
        } else {
            // Deactivate
            $existing = dbFetch("SELECT id FROM doctor_schedules WHERE doctor_id = ? AND day_of_week = ?", [$doctorId, $d]);
            if ($existing) {
                dbUpdate('doctor_schedules', ['is_active' => 0], "doctor_id = ? AND day_of_week = ?", [$doctorId, $d]);
            }
        }
    }
    $msg = 'Schedule updated successfully.';
}

// Get current schedule
$schedules = [];
for ($d = 0; $d < 7; $d++) {
    $schedules[$d] = dbFetch("SELECT * FROM doctor_schedules WHERE doctor_id = ? AND day_of_week = ?", [$doctorId, $d]);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Schedule — Medi Clinic</title>
  <link rel="stylesheet" href="../style.css">
  <link rel="stylesheet" href="../panel.css">
  <style>
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
    }
    .page-title {
      font-size: 24px;
      font-weight: 700;
      color: var(--nav-bg);
    }
    .page-subtitle {
      font-size: 14px;
      color: #666;
    }

    .schedule-form {
      background: #fff;
      border-radius: 12px;
      padding: 24px;
      box-shadow: 0 2px 8px rgba(0,0,0,.06);
      max-width: 700px;
    }
    .doctor-info {
      margin-bottom: 24px;
      padding-bottom: 16px;
      border-bottom: 1px solid #e0e0e0;
    }
    .doctor-name {
      font-size: 20px;
      font-weight: 700;
      color: var(--nav-bg);
    }
    .doctor-specialty {
      font-size: 14px;
      color: var(--teal-mid);
    }

    .day-row {
      display: grid;
      grid-template-columns: 120px 1fr 1fr auto;
      gap: 16px;
      align-items: center;
      padding: 16px 0;
      border-bottom: 1px solid #f0f0f0;
    }
    .day-row:last-child {
      border-bottom: none;
    }
    .day-name {
      font-weight: 600;
      color: var(--nav-bg);
    }
    .time-inputs {
      display: flex;
      gap: 12px;
      align-items: center;
    }
    .time-inputs input {
      padding: 10px 12px;
      border: 1.5px solid #d0d9d6;
      border-radius: 6px;
      font-size: 14px;
    }
    .checkbox-wrapper {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .checkbox-wrapper input {
      width: 20px;
      height: 20px;
    }

    .form-actions {
      display: flex;
      gap: 12px;
      margin-top: 24px;
    }
    .btn-save {
      padding: 12px 24px;
      background: var(--nav-bg);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 700;
      cursor: pointer;
    }
    .btn-save:hover {
      background: #1b4036;
    }
    .btn-cancel {
      padding: 12px 24px;
      background: #f5f5f5;
      color: #666;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      text-decoration: none;
    }
    .btn-cancel:hover {
      background: #e0e0e0;
    }

    .alert-note {
      background: #e3f2fd;
      border: 1px solid #90caf9;
      color: #1565c0;
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 13px;
    }
    .alert-success {
      background: #e8f8f5;
      border: 1px solid #a8e6cf;
      color: #27ae60;
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/sidebar.php'; ?>

<main class="panel-main">
  <div class="panel-header">
    <div>
      <h1>Edit Doctor Schedule</h1>
      <p class="page-subtitle">Changes will apply to future bookings</p>
    </div>
    <div class="panel-user">
      <span><?= htmlspecialchars($user['full_name']) ?></span>
      <a href="../logout.php" class="btn-logout">Logout</a>
    </div>
  </div>

  <div class="panel-content">
    <?php if ($msg): ?>
    <div class="alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="alert-note">
      Note: If there are existing confirmed appointments, schedule time changes won't affect them.
    </div>

    <form method="POST" class="schedule-form">
      <div class="doctor-info">
        <div class="doctor-name"><?= htmlspecialchars($doctor['full_name']) ?></div>
        <div class="doctor-specialty"><?= htmlspecialchars($doctor['specialization']) ?></div>
      </div>

      <?php for ($d = 0; $d < 7; $d++):
        $schedule = $schedules[$d] ?? null;
      ?>
      <div class="day-row">
        <div class="day-name"><?= $dayNames[$d] ?></div>
        <div class="time-inputs">
          <input type="time" name="start_<?= $d ?>" value="<?= $schedule ? $schedule['start_time'] : '08:00' ?>">
          <span>to</span>
          <input type="time" name="end_<?= $d ?>" value="<?= $schedule ? $schedule['end_time'] : '17:00' ?>">
        </div>
        <div class="checkbox-wrapper">
          <input type="checkbox" id="active_<?= $d ?>" name="active_<?= $d ?>" value="1" <?= $schedule && $schedule['is_active'] ? 'checked' : '' ?>>
          <label for="active_<?= $d ?>">Active</label>
        </div>
      </div>
      <?php endfor; ?>

      <div class="form-actions">
        <button type="submit" class="btn-save">Save Schedule</button>
        <a href="schedules.php" class="btn-cancel">Cancel</a>
      </div>
    </form>
  </div>
</main>
</body>
</html>