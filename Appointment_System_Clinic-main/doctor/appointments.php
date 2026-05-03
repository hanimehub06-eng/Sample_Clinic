<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('doctor');

$user = getCurrentUser();
$today = date('Y-m-d');

// Get all appointments
$allAppts = getDoctorAppointments($user['id']);

// Filter by date
$view = $_GET['view'] ?? 'all';
$filteredAppts = $allAppts;

if ($view === 'today') {
    $filteredAppts = array_filter($allAppts, function($a) use ($today) {
        return $a['appointment_date'] === $today;
    });
} elseif ($view === 'upcoming') {
    $filteredAppts = array_filter($allAppts, function($a) use ($today) {
        return $a['appointment_date'] >= $today && $a['status'] !== 'cancelled';
    });
} elseif ($view === 'past') {
    $filteredAppts = array_filter($allAppts, function($a) use ($today) {
        return $a['appointment_date'] < $today || $a['status'] === 'completed';
    });
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
      text-decoration: none;
    }
    .filter-tab.active {
      background: #fff;
      color: var(--nav-bg);
      box-shadow: 0 1px 3px rgba(0,0,0,.1);
    }

    .appointment-table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0,0,0,.06);
    }
    .appointment-table th {
      text-align: left;
      padding: 14px;
      font-size: 12px;
      font-weight: 600;
      color: #666;
      background: #f8faf9;
      border-bottom: 2px solid #f0f0f0;
    }
    .appointment-table td {
      padding: 14px;
      font-size: 14px;
      border-bottom: 1px solid #f0f0f0;
    }

    .status-badge {
      font-size: 11px;
      padding: 4px 8px;
      border-radius: 4px;
      font-weight: 600;
    }
    .status-pending { background: #fef5ec; color: #e67e22; }
    .status-confirmed { background: #e8f8f5; color: #27ae60; }
    .status-completed { background: #f0f4f8; color: #2c3e50; }
    .status-cancelled { background: #fce8e8; color: #c0392b; }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #888;
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
    <div class="page-header">
      <h2 class="page-title">All Appointments</h2>
      <div class="filter-tabs">
        <a href="?view=all" class="filter-tab<?= $view === 'all' ? ' active' : '' ?>">All</a>
        <a href="?view=today" class="filter-tab<?= $view === 'today' ? ' active' : '' ?>">Today</a>
        <a href="?view=upcoming" class="filter-tab<?= $view === 'upcoming' ? ' active' : '' ?>">Upcoming</a>
        <a href="?view=past" class="filter-tab<?= $view === 'past' ? ' active' : '' ?>">Past</a>
      </div>
    </div>

    <?php if (empty($filteredAppts)): ?>
    <div class="empty-state">
      <p>No appointments found.</p>
    </div>
    <?php else: ?>
    <table class="appointment-table">
      <thead>
        <tr>
          <th>Date</th>
          <th>Time</th>
          <th>Patient</th>
          <th>Reason</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($filteredAppts as $appt): ?>
        <tr>
          <td><?= formatDate($appt['appointment_date']) ?></td>
          <td><?= htmlspecialchars($appt['time_slot']) ?></td>
          <td><?= htmlspecialchars($appt['patient_name']) ?></td>
          <td><?= htmlspecialchars($appt['reason']) ?></td>
          <td><span class="status-badge status-<?= $appt['status'] ?>"><?= ucfirst($appt['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</main>
</body>
</html>