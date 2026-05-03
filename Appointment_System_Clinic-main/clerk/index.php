<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('clerk');

$user = getCurrentUser();
$today = date('Y-m-d');

// Get stats
$totalAppointments = dbFetch("SELECT COUNT(*) as cnt FROM appointments")['cnt'] ?? 0;
$todayAppointments = dbFetch("SELECT COUNT(*) as cnt FROM appointments WHERE appointment_date = ?", [$today])['cnt'] ?? 0;
$pendingAppointments = dbFetch("SELECT COUNT(*) as cnt FROM appointments WHERE status = 'pending'")['cnt'] ?? 0;
$totalPatients = dbFetch("SELECT COUNT(*) as cnt FROM users WHERE role = 'patient'")['cnt'] ?? 0;

// Get today's appointments
$todaysAppts = dbFetchAll("
    SELECT a.*, p.full_name as patient_name, d.full_name as doctor_name
    FROM appointments a
    JOIN users p ON p.id = a.patient_id
    JOIN users d ON d.id = a.doctor_id
    WHERE a.appointment_date = ?
    ORDER BY a.time_slot ASC
", [$today]);

// Get recent appointments
$recentAppts = dbFetchAll("
    SELECT a.*, p.full_name as patient_name, d.full_name as doctor_name
    FROM appointments a
    JOIN users p ON p.id = a.patient_id
    JOIN users d ON d.id = a.doctor_id
    ORDER BY a.created_at DESC
    LIMIT 5
");

// Handle mark arrived
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['arrived_id'])) {
    markArrived($_POST['arrived_id']);
    $msg = 'Patient marked as arrived.';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Clerk Dashboard — Medi Clinic</title>
  <link rel="stylesheet" href="../style.css">
  <link rel="stylesheet" href="../panel.css">
  <style>
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

    .alert-banner {
      background: #fff3e0;
      border: 1px solid #ffcc80;
      border-radius: 8px;
      padding: 16px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .alert-banner svg {
      width: 24px;
      height: 24px;
      fill: #e65100;
      flex-shrink: 0;
    }
    .alert-banner-content {
      flex: 1;
    }
    .alert-banner h4 {
      font-size: 14px;
      font-weight: 700;
      color: #e65100;
      margin-bottom: 2px;
    }
    .alert-banner p {
      font-size: 13px;
      color: #666;
    }
    .alert-count {
      font-size: 28px;
      font-weight: 700;
      color: #e65100;
    }

    .appointment-table {
      width: 100%;
      border-collapse: collapse;
    }
    .appointment-table th {
      text-align: left;
      padding: 12px;
      font-size: 12px;
      font-weight: 600;
      color: #666;
      border-bottom: 2px solid #f0f0f0;
    }
    .appointment-table td {
      padding: 12px;
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

    .btn-sm {
      padding: 6px 12px;
      font-size: 12px;
      border-radius: 4px;
      text-decoration: none;
      cursor: pointer;
    }
    .btn-arrived {
      background: #e8f8f5;
      color: #27ae60;
      border: 1px solid #a8e6cf;
    }
    .btn-arrived:hover {
      background: #d4efdf;
    }
    .btn-disabled {
      background: #f5f5f5;
      color: #999;
      border: 1px solid #e0e0e0;
      cursor: default;
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/sidebar.php'; ?>

<main class="panel-main">
  <div class="panel-header">
    <div>
      <h1>Clerk Dashboard</h1>
    </div>
    <div class="panel-user">
      <span><?= htmlspecialchars($user['full_name']) ?></span>
      <a href="../logout.php" class="btn-logout">Logout</a>
    </div>
  </div>

  <div class="panel-content">
    <?php if ($msg): ?>
    <div class="alert-success" style="background: #e8f8f5; border: 1px solid #a8e6cf; color: #27ae60; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;">
      <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-label">Today's Appointments</div>
        <div class="stat-value"><?= $todayAppointments ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Pending Appointments</div>
        <div class="stat-value"><?= $pendingAppointments ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Total Patients</div>
        <div class="stat-value"><?= $totalPatients ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Total Appointments</div>
        <div class="stat-value"><?= $totalAppointments ?></div>
      </div>
    </div>

    <?php if (count(array_filter($todaysAppts, function($a) { return $a['status'] === 'pending'; })) > 0): ?>
    <div class="alert-banner">
      <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
      <div class="alert-banner-content">
        <h4>New Same-Day Appointments</h4>
        <p>These appointments need your attention</p>
      </div>
      <div class="alert-count"><?= count(array_filter($todaysAppts, function($a) { return $a['status'] === 'pending'; })) ?></div>
    </div>
    <?php endif; ?>

    <div class="dashboard-section">
      <h3 class="section-title">Today's Schedule (<?= formatDate($today) ?>)</h3>
      <?php if (empty($todaysAppts)): ?>
      <p style="color: #888; text-align: center; padding: 20px;">No appointments scheduled for today.</p>
      <?php else: ?>
      <table class="appointment-table">
        <thead>
          <tr>
            <th>Patient</th>
            <th>Doctor</th>
            <th>Time</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($todaysAppts as $appt): ?>
          <tr>
            <td><?= htmlspecialchars($appt['patient_name']) ?></td>
            <td><?= htmlspecialchars($appt['doctor_name']) ?></td>
            <td><?= htmlspecialchars($appt['time_slot']) ?></td>
            <td><span class="status-badge status-<?= $appt['status'] ?>"><?= ucfirst($appt['status']) ?></span></td>
            <td>
              <?php if ($appt['status'] !== 'cancelled' && !$appt['arrived']): ?>
              <form method="POST" style="display: inline;">
                <input type="hidden" name="arrived_id" value="<?= $appt['id'] ?>">
                <button type="submit" class="btn-sm btn-arrived">Mark Arrived</button>
              </form>
              <?php elseif ($appt['arrived']): ?>
              <span class="btn-sm btn-disabled">Arrived</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>

    <div class="dashboard-section">
      <h3 class="section-title">Recent Appointments</h3>
      <table class="appointment-table">
        <thead>
          <tr>
            <th>Patient</th>
            <th>Doctor</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentAppts as $appt): ?>
          <tr>
            <td><?= htmlspecialchars($appt['patient_name']) ?></td>
            <td><?= htmlspecialchars($appt['doctor_name']) ?></td>
            <td><?= formatDate($appt['appointment_date']) ?></td>
            <td><?= htmlspecialchars($appt['time_slot']) ?></td>
            <td><span class="status-badge status-<?= $appt['status'] ?>"><?= ucfirst($appt['status']) ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
</body>
</html>