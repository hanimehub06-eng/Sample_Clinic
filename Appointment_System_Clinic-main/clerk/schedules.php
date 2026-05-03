<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('clerk');

$user = getCurrentUser();
$doctors = getDoctors();

$dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Doctor Schedules — Medi Clinic</title>
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

    .doctor-schedule-grid {
      display: grid;
      gap: 24px;
    }
    .doctor-schedule-card {
      background: #fff;
      border-radius: 12px;
      padding: 24px;
      box-shadow: 0 2px 8px rgba(0,0,0,.06);
    }
    .doctor-name {
      font-size: 18px;
      font-weight: 700;
      color: var(--nav-bg);
      margin-bottom: 4px;
    }
    .doctor-specialty {
      font-size: 14px;
      color: var(--teal-mid);
      margin-bottom: 16px;
    }

    .schedule-table {
      width: 100%;
      border-collapse: collapse;
    }
    .schedule-table th {
      text-align: left;
      padding: 10px;
      font-size: 12px;
      font-weight: 600;
      color: #666;
      background: #f8faf9;
      border-bottom: 1px solid #e0e0e0;
    }
    .schedule-table td {
      padding: 10px;
      font-size: 14px;
      border-bottom: 1px solid #f0f0f0;
    }
    .schedule-table tr:last-child td {
      border-bottom: none;
    }
    .schedule-inactive {
      color: #999;
      font-style: italic;
    }
    .schedule-active {
      color: #27ae60;
    }

    .btn-edit {
      padding: 8px 16px;
      background: var(--nav-bg);
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
    }
    .btn-edit:hover {
      background: #1b4036;
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/sidebar.php'; ?>

<main class="panel-main">
  <div class="panel-header">
    <div>
      <h1>Doctor Schedules</h1>
      <p class="page-subtitle">Manage weekly working hours for each doctor</p>
    </div>
    <div class="panel-user">
      <span><?= htmlspecialchars($user['full_name']) ?></span>
      <a href="../logout.php" class="btn-logout">Logout</a>
    </div>
  </div>

  <div class="panel-content">
    <div class="doctor-schedule-grid">
      <?php foreach ($doctors as $doctor): ?>
      <div class="doctor-schedule-card">
        <div class="doctor-name"><?= htmlspecialchars($doctor['full_name']) ?></div>
        <div class="doctor-specialty"><?= htmlspecialchars($doctor['specialization']) ?></div>

        <table class="schedule-table">
          <thead>
            <tr>
              <th>Day</th>
              <th>Start Time</th>
              <th>End Time</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php for ($d = 0; $d < 7; $d++):
              $schedule = dbFetch("
                SELECT * FROM doctor_schedules
                WHERE doctor_id = ? AND day_of_week = ?
              ", [$doctor['id'], $d]);
            ?>
            <tr>
              <td><?= $dayNames[$d] ?></td>
              <td><?= $schedule ? date('g:i A', strtotime($schedule['start_time'])) : '-' ?></td>
              <td><?= $schedule ? date('g:i A', strtotime($schedule['end_time'])) : '-' ?></td>
              <td class="<?= $schedule && $schedule['is_active'] ? 'schedule-active' : 'schedule-inactive' ?>">
                <?= $schedule && $schedule['is_active'] ? 'Active' : 'Inactive' ?>
              </td>
            </tr>
            <?php endfor; ?>
          </tbody>
        </table>

        <div style="margin-top: 16px;">
          <a href="schedule-edit.php?doctor=<?= $doctor['id'] ?>" class="btn-edit">Edit Schedule</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</main>
</body>
</html>