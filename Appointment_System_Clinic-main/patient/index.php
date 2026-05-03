<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('patient');

$user = getCurrentUser();
$upcoming = getPatientAppointments($user['id'], 'pending');
$past = getPatientAppointments($user['id'], null);
$past = array_filter($past, function($a) {
    return $a['status'] !== 'pending';
});
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Dashboard — Medi Clinic</title>
  <link rel="stylesheet" href="../style.css">
  <link rel="stylesheet" href="../panel.css">
  <style>
    .dashboard-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
    }
    .welcome-text {
      font-size: 14px;
      color: #666;
    }
    .welcome-name {
      font-size: 20px;
      font-weight: 700;
      color: var(--nav-bg);
    }
    .quick-actions {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 16px;
      margin-bottom: 32px;
    }
    .quick-action {
      background: #fff;
      border-radius: 12px;
      padding: 24px;
      text-decoration: none;
      box-shadow: 0 2px 8px rgba(0,0,0,.06);
      transition: transform .2s, box-shadow .2s;
    }
    .quick-action:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,.1);
    }
    .quick-action-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 12px;
    }
    .quick-action-icon svg {
      width: 24px;
      height: 24px;
    }
    .qa-teal { background: #e8f8f5; }
    .qa-teal svg { fill: var(--teal-mid); }
    .qa-navy { background: #f0f4f8; }
    .qa-navy svg { fill: var(--nav-bg); }
    .qa-orange { background: #fef5ec; }
    .qa-orange svg { fill: #e67e22; }
    .quick-action-title {
      font-size: 16px;
      font-weight: 700;
      color: var(--nav-bg);
    }
    .quick-action-desc {
      font-size: 13px;
      color: #666;
      margin-top: 4px;
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
    .appointment-info h4 {
      font-size: 15px;
      font-weight: 700;
      color: var(--nav-bg);
      margin-bottom: 4px;
    }
    .appointment-meta {
      font-size: 13px;
      color: #666;
    }
    .appointment-meta span {
      margin-right: 16px;
    }
    .appointment-status {
      font-size: 12px;
      padding: 4px 10px;
      border-radius: 4px;
      font-weight: 600;
    }
    .status-confirmed {
      background: #e8f8f5;
      color: #27ae60;
    }
    .status-pending {
      background: #fef5ec;
      color: #e67e22;
    }
    .status-cancelled {
      background: #fce8e8;
      color: #c0392b;
    }
    .status-completed {
      background: #f0f4f8;
      color: #2c3e50;
    }
    .appointment-actions {
      display: flex;
      gap: 8px;
    }
    .btn-sm {
      padding: 8px 14px;
      font-size: 12px;
      border-radius: 6px;
      text-decoration: none;
    }
    .btn-outline-sm {
      background: #fff;
      border: 1px solid #d0d9d6;
      color: #666;
    }
    .btn-outline-sm:hover {
      background: #f5f5f5;
    }
    .btn-danger-sm {
      background: #fff;
      border: 1px solid #f5a0a0;
      color: #c0392b;
    }
    .btn-danger-sm:hover {
      background: #fff0f0;
    }

    .empty-state {
      text-align: center;
      padding: 40px;
      color: #888;
    }
    .empty-state svg {
      width: 48px;
      height: 48px;
      fill: #ccc;
      margin-bottom: 12px;
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/sidebar.php'; ?>

<main class="panel-main">
  <div class="panel-header">
    <div>
      <h1>My Dashboard</h1>
    </div>
    <div class="panel-user">
      <span><?= htmlspecialchars($user['full_name']) ?></span>
      <a href="../logout.php" class="btn-logout">Logout</a>
    </div>
  </div>

  <div class="panel-content">
    <div class="dashboard-header">
      <div>
        <p class="welcome-text">Welcome back,</p>
        <h2 class="welcome-name"><?= htmlspecialchars($user['full_name']) ?></h2>
      </div>
    </div>

    <div class="quick-actions">
      <a href="../book.php" class="quick-action">
        <div class="quick-action-icon qa-teal">
          <svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
        </div>
        <div class="quick-action-title">Book Appointment</div>
        <div class="quick-action-desc">Schedule a new visit</div>
      </a>
      <a href="appointments.php" class="quick-action">
        <div class="quick-action-icon qa-navy">
          <svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>
        </div>
        <div class="quick-action-title">View Appointments</div>
        <div class="quick-action-desc">See all your visits</div>
      </a>
      <a href="profile.php" class="quick-action">
        <div class="quick-action-icon qa-orange">
          <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        </div>
        <div class="quick-action-title">My Profile</div>
        <div class="quick-action-desc">Update your info</div>
      </a>
    </div>

    <?php if (!empty($upcoming)): ?>
    <div class="dashboard-section">
      <h3 class="section-title">Upcoming Appointments</h3>
      <div class="appointment-list">
        <?php foreach (array_slice($upcoming, 0, 3) as $appt): ?>
        <div class="appointment-item">
          <div class="appointment-info">
            <h4><?= htmlspecialchars($appt['doctor_name']) ?></h4>
            <div class="appointment-meta">
              <span><?= formatDate($appt['appointment_date']) ?></span>
              <span><?= htmlspecialchars($appt['time_slot']) ?></span>
              <span><?= htmlspecialchars($appt['specialization']) ?></span>
            </div>
          </div>
          <div class="appointment-actions">
            <span class="appointment-status status-<?= $appt['status'] ?>"><?= ucfirst($appt['status']) ?></span>
            <a href="appointments.php" class="btn-sm btn-outline-sm">View</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php if (count($upcoming) > 3): ?>
      <div style="text-align: center; margin-top: 16px;">
        <a href="appointments.php" class="btn-sm btn-outline-sm">View All (<?= count($upcoming) ?>)</a>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (empty($upcoming)): ?>
    <div class="dashboard-section">
      <div class="empty-state">
        <svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>
        <p>No upcoming appointments</p>
        <a href="../book.php" class="btn-primary" style="display: inline-block; margin-top: 12px; padding: 12px 24px;">Book Now</a>
      </div>
    </div>
    <?php endif; ?>
  </div>
</main>
</body>
</html>