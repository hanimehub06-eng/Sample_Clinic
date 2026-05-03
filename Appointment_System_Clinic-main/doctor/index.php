<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('doctor');

$user = getCurrentUser();
$today = date('Y-m-d');

// Get today's appointments
$todaysAppts = getDoctorAppointments($user['id'], $today);

// Get upcoming appointments
$upcomingAppts = dbFetchAll("
    SELECT a.*, p.full_name as patient_name
    FROM appointments a
    JOIN users p ON p.id = a.patient_id
    WHERE a.doctor_id = ?
    AND a.appointment_date >= ?
    AND a.status NOT IN ('cancelled', 'completed')
    ORDER BY a.appointment_date ASC, a.time_slot ASC
    LIMIT 10
", [$user['id'], $today]);

// Get notifications
$notifications = getNotifications($user['id'], true);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Doctor Dashboard — Medi Clinic</title>
  <link rel="stylesheet" href="../style.css">
  <link rel="stylesheet" href="../panel.css">
  <style>
    .welcome-section {
      margin-bottom: 24px;
    }
    .welcome-name {
      font-size: 24px;
      font-weight: 700;
      color: var(--nav-bg);
    }
    .welcome-specialty {
      font-size: 14px;
      color: var(--teal-mid);
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 16px;
      margin-bottom: 32px;
    }
    .stat-card {
      background: #fff;
      border-radius: 12px;
      padding: 24px;
      box-shadow: 0 2px 8px rgba(0,0,0,.06);
    }
    .stat-label {
      font-size: 13px;
      color: #666;
      margin-bottom: 8px;
    }
    .stat-value {
      font-size: 32px;
      font-weight: 700;
      color: var(--nav-bg);
    }

    .dashboard-section {
      background: #fff;
      border-radius: 12px;
      padding: 24px;
      box-shadow: 0 2px 8px rgba(0,0,0,.06);
      margin-bottom: 24px;
    }
    .section-title {
      font-size: 18px;
      font-weight: 700;
      color: var(--nav-bg);
      margin-bottom: 16px;
    }

    .appointment-list {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .appointment-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 16px;
      background: #f8faf9;
      border-radius: 8px;
      border: 1px solid #e8eceb;
    }
    .appointment-time {
      font-size: 14px;
      font-weight: 700;
      color: var(--nav-bg);
      margin-bottom: 4px;
    }
    .appointment-patient {
      font-size: 13px;
      color: #666;
    }
    .appointment-status {
      font-size: 12px;
      padding: 4px 10px;
      border-radius: 4px;
      font-weight: 600;
    }
    .status-pending { background: #fef5ec; color: #e67e22; }
    .status-confirmed { background: #e8f8f5; color: #27ae60; }

    .notification-list {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .notification-item {
      padding: 16px;
      background: #f8faf9;
      border-radius: 8px;
      border-left: 4px solid var(--teal-mid);
    }
    .notification-title {
      font-size: 14px;
      font-weight: 700;
      color: var(--nav-bg);
      margin-bottom: 4px;
    }
    .notification-message {
      font-size: 13px;
      color: #666;
    }
    .notification-time {
      font-size: 12px;
      color: #999;
      margin-top: 8px;
    }
    .view-all-link {
      display: block;
      text-align: center;
      margin-top: 16px;
      color: var(--teal-mid);
      font-size: 14px;
      font-weight: 600;
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/sidebar.php'; ?>

<main class="panel-main">
  <div class="panel-header">
    <div>
      <h1>Doctor Dashboard</h1>
    </div>
    <div class="panel-user">
      <span><?= htmlspecialchars($user['full_name']) ?></span>
      <a href="../logout.php" class="btn-logout">Logout</a>
    </div>
  </div>

  <div class="panel-content">
    <div class="welcome-section">
      <h2 class="welcome-name">Dr. <?= htmlspecialchars(explode(' ', $user['full_name'])[1] ?? $user['full_name']) ?></h2>
      <p class="welcome-specialty"><?= htmlspecialchars($user['specialization']) ?></p>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-label">Today's Appointments</div>
        <div class="stat-value"><?= count($todaysAppts) ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Upcoming This Week</div>
        <div class="stat-value"><?= count(array_filter($upcomingAppts, function($a) { return strtotime($a['appointment_date']) <= strtotime('+7 days'); })) ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Pending Appointments</div>
        <div class="stat-value"><?= count(array_filter($upcomingAppts, function($a) { return $a['status'] === 'pending'; })) ?></div>
      </div>
    </div>

    <div class="dashboard-section">
      <h3 class="section-title">Today's Schedule (<?= formatDate($today) ?>)</h3>
      <?php if (empty($todaysAppts)): ?>
      <p style="color: #888; text-align: center; padding: 20px;">No appointments scheduled for today.</p>
      <?php else: ?>
      <div class="appointment-list">
        <?php foreach ($todaysAppts as $appt): ?>
        <div class="appointment-item">
          <div>
            <div class="appointment-time"><?= htmlspecialchars($appt['time_slot']) ?></div>
            <div class="appointment-patient"><?= htmlspecialchars($appt['patient_name']) ?></div>
          </div>
          <span class="appointment-status status-<?= $appt['status'] ?>"><?= ucfirst($appt['status']) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <a href="appointments.php" class="view-all-link">View All Appointments</a>
    </div>

    <div class="dashboard-section">
      <h3 class="section-title">Recent Notifications</h3>
      <?php if (empty($notifications)): ?>
      <p style="color: #888; text-align: center; padding: 20px;">No new notifications.</p>
      <?php else: ?>
      <div class="notification-list">
        <?php foreach (array_slice($notifications, 0, 3) as $notif): ?>
        <div class="notification-item">
          <div class="notification-title"><?= htmlspecialchars($notif['title']) ?></div>
          <div class="notification-message"><?= htmlspecialchars($notif['message']) ?></div>
          <div class="notification-time"><?= date('g:i A', strtotime($notif['created_at'])) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
      <a href="notifications.php" class="view-all-link">View All Notifications</a>
      <?php endif; ?>
    </div>
  </div>
</main>
</body>
</html>