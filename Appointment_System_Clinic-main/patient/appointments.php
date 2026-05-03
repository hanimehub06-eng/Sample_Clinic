<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('patient');

$user = getCurrentUser();
$filter = $_GET['filter'] ?? 'upcoming';
$appointments = getPatientAppointments($user['id'], $filter === 'all' ? null : ($filter === 'upcoming' ? 'pending' : null));

// Filter for upcoming vs past
if ($filter === 'upcoming') {
    $appointments = array_filter($appointments, function($a) {
        return $a['status'] === 'pending' || $a['status'] === 'confirmed';
    });
} elseif ($filter === 'past') {
    $appointments = array_filter($appointments, function($a) {
        return $a['status'] === 'completed' || $a['status'] === 'cancelled' || $a['status'] === 'no_show';
    });
}

// Handle cancellation
$cancelMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $result = cancelAppointment($_POST['cancel_id'], $user['id']);
    if ($result) {
        sendCancellationEmail($_POST['cancel_id']);
        $cancelMsg = 'Appointment cancelled successfully.';
    }
    // Refresh appointments
    $appointments = getPatientAppointments($user['id'], null);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Appointments — Medi Clinic</title>
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
    .filter-tabs {
      display: flex;
      gap: 8px;
      background: #f5f5f5;
      padding: 4px;
      border-radius: 8px;
    }
    .filter-tab {
      padding: 8px 16px;
      border: none;
      background: none;
      border-radius: 6px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      color: #666;
    }
    .filter-tab.active {
      background: #fff;
      color: var(--nav-bg);
      box-shadow: 0 1px 3px rgba(0,0,0,.1);
    }

    .alert-message {
      background: #e8f8f5;
      border: 1px solid #a8e6cf;
      color: #27ae60;
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
    }

    .appointments-list {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }
    .appointment-card {
      background: #fff;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,.06);
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 16px;
    }
    @media (max-width: 600px) {
      .appointment-card { grid-template-columns: 1fr; }
    }
    .appointment-main h3 {
      font-size: 16px;
      font-weight: 700;
      color: var(--nav-bg);
      margin-bottom: 8px;
    }
    .appointment-details {
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
      font-size: 13px;
      color: #666;
    }
    .appointment-detail {
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .appointment-detail svg {
      width: 16px;
      height: 16px;
      fill: #999;
    }
    .appointment-reason {
      margin-top: 8px;
      font-size: 13px;
      color: #666;
      background: #f8faf9;
      padding: 8px 12px;
      border-radius: 6px;
    }
    .appointment-actions {
      display: flex;
      flex-direction: column;
      gap: 8px;
      align-items: flex-end;
    }
    .appointment-status {
      font-size: 12px;
      padding: 6px 12px;
      border-radius: 4px;
      font-weight: 600;
    }
    .status-confirmed { background: #e8f8f5; color: #27ae60; }
    .status-pending { background: #fef5ec; color: #e67e22; }
    .status-cancelled { background: #fce8e8; color: #c0392b; }
    .status-completed { background: #f0f4f8; color: #2c3e50; }
    .status-no_show { background: #f5f5f5; color: #999; }

    .btn-action {
      padding: 8px 16px;
      border-radius: 6px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
    }
    .btn-cancel {
      background: #fff;
      border: 1px solid #f5a0a0;
      color: #c0392b;
    }
    .btn-cancel:hover {
      background: #fff0f0;
    }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #888;
    }
    .empty-state svg {
      width: 64px;
      height: 64px;
      fill: #ccc;
      margin-bottom: 16px;
    }
    .empty-state h3 {
      font-size: 18px;
      color: #666;
      margin-bottom: 8px;
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/sidebar.php'; ?>

<main class="panel-main">
  <div class="panel-header">
    <div>
      <h1>My Appointments</h1>
    </div>
    <div class="panel-user">
      <span><?= htmlspecialchars($user['full_name']) ?></span>
      <a href="../logout.php" class="btn-logout">Logout</a>
    </div>
  </div>

  <div class="panel-content">
    <?php if ($cancelMsg): ?>
    <div class="alert-message"><?= htmlspecialchars($cancelMsg) ?></div>
    <?php endif; ?>

    <div class="page-header">
      <h2 class="page-title">All Appointments</h2>
      <div class="filter-tabs">
        <a href="?filter=upcoming" class="filter-tab<?= $filter === 'upcoming' ? ' active' : '' ?>">Upcoming</a>
        <a href="?filter=past" class="filter-tab<?= $filter === 'past' ? ' active' : '' ?>">Past</a>
        <a href="?filter=all" class="filter-tab<?= $filter === 'all' ? ' active' : '' ?>">All</a>
      </div>
    </div>

    <?php if (empty($appointments)): ?>
    <div class="empty-state">
      <svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>
      <h3>No appointments found</h3>
      <p>Book an appointment to get started</p>
      <a href="../book.php" class="btn-primary" style="display: inline-block; margin-top: 16px; padding: 12px 24px;">Book Appointment</a>
    </div>
    <?php else: ?>
    <div class="appointments-list">
      <?php foreach ($appointments as $appt): ?>
      <div class="appointment-card">
        <div class="appointment-main">
          <h3><?= htmlspecialchars($appt['doctor_name']) ?></h3>
          <div class="appointment-details">
            <div class="appointment-detail">
              <svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>
              <?= formatDate($appt['appointment_date']) ?>
            </div>
            <div class="appointment-detail">
              <svg viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/><path d="M12.5 7H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
              <?= htmlspecialchars($appt['time_slot']) ?>
            </div>
            <div class="appointment-detail">
              <svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
              <?= htmlspecialchars($appt['specialization']) ?>
            </div>
          </div>
          <div class="appointment-reason">
            <strong>Reason:</strong> <?= htmlspecialchars($appt['reason']) ?>
          </div>
          <div style="margin-top: 8px; font-size: 12px; color: #999;">
            Ref: <?= htmlspecialchars($appt['reference_no']) ?>
          </div>
        </div>
        <div class="appointment-actions">
          <span class="appointment-status status-<?= $appt['status'] ?>"><?= ucfirst($appt['status']) ?></span>
          <?php if ($appt['status'] === 'pending' || $appt['status'] === 'confirmed'): ?>
          <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
            <input type="hidden" name="cancel_id" value="<?= $appt['id'] ?>">
            <button type="submit" class="btn-action btn-cancel">Cancel Appointment</button>
          </form>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</main>
</body>
</html>