<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('clerk');

$user = getCurrentUser();
$doctors = getDoctors();

$filterDoctor = $_GET['doctor'] ?? null;
$filterStatus = $_GET['status'] ?? null;
$filterDate = $_GET['date'] ?? null;

$filters = [];
if ($filterDoctor) $filters['doctor_id'] = $filterDoctor;
if ($filterStatus) $filters['status'] = $filterStatus;
if ($filterDate) $filters['date'] = $filterDate;

$appointments = getAllAppointments($filters);

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['arrived_id'])) {
    markArrived($_POST['arrived_id']);
    $msg = 'Patient marked as arrived.';
    $appointments = getAllAppointments($filters);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    cancelAppointment($_POST['cancel_id']);
    sendCancellationEmail($_POST['cancel_id']);
    $msg = 'Appointment cancelled.';
    $appointments = getAllAppointments($filters);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>All Appointments — Medi Clinic</title>
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

    .filter-bar {
      background: #fff;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,.06);
      margin-bottom: 24px;
    }
    .filter-bar form {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      align-items: center;
    }
    .filter-bar select,
    .filter-bar input {
      padding: 10px 14px;
      border: 1.5px solid #d0d9d6;
      border-radius: 6px;
      font-size: 14px;
    }
    .filter-bar button {
      padding: 10px 20px;
      background: var(--nav-bg);
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
    }
    .filter-bar a {
      padding: 10px 20px;
      background: #f5f5f5;
      color: #666;
      border: none;
      border-radius: 6px;
      font-size: 14px;
      text-decoration: none;
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
    .appointment-table tr:last-child td {
      border-bottom: none;
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

    .action-btns {
      display: flex;
      gap: 8px;
    }
    .btn-sm {
      padding: 6px 10px;
      font-size: 11px;
      border-radius: 4px;
      text-decoration: none;
      cursor: pointer;
    }
    .btn-arrived {
      background: #e8f8f5;
      color: #27ae60;
      border: 1px solid #a8e6cf;
    }
    .btn-cancel {
      background: #fff;
      color: #c0392b;
      border: 1px solid #f5a0a0;
    }

    .alert-success {
      background: #e8f8f5;
      border: 1px solid #a8e6cf;
      color: #27ae60;
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/sidebar.php'; ?>

<main class="panel-main">
  <div class="panel-header">
    <div>
      <h1>All Appointments</h1>
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

    <div class="filter-bar">
      <form method="GET" action="appointments.php">
        <select name="doctor">
          <option value="">All Doctors</option>
          <?php foreach ($doctors as $d): ?>
          <option value="<?= $d['id'] ?>" <?= $filterDoctor == $d['id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['full_name']) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="status">
          <option value="">All Status</option>
          <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
          <option value="confirmed" <?= $filterStatus === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
          <option value="completed" <?= $filterStatus === 'completed' ? 'selected' : '' ?>>Completed</option>
          <option value="cancelled" <?= $filterStatus === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
        <input type="date" name="date" value="<?= htmlspecialchars($filterDate ?? '') ?>">
        <button type="submit">Filter</button>
        <a href="appointments.php">Clear</a>
      </form>
    </div>

    <?php if (empty($appointments)): ?>
    <p style="text-align: center; padding: 40px; color: #888;">No appointments found.</p>
    <?php else: ?>
    <table class="appointment-table">
      <thead>
        <tr>
          <th>Ref No.</th>
          <th>Patient</th>
          <th>Doctor</th>
          <th>Date</th>
          <th>Time</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($appointments as $appt): ?>
        <tr>
          <td><?= htmlspecialchars($appt['reference_no']) ?></td>
          <td><?= htmlspecialchars($appt['patient_name']) ?></td>
          <td><?= htmlspecialchars($appt['doctor_name']) ?></td>
          <td><?= formatDate($appt['appointment_date']) ?></td>
          <td><?= htmlspecialchars($appt['time_slot']) ?></td>
          <td><span class="status-badge status-<?= $appt['status'] ?>"><?= ucfirst($appt['status']) ?></span></td>
          <td class="action-btns">
            <?php if ($appt['status'] !== 'cancelled' && !$appt['arrived']): ?>
            <form method="POST" style="display: inline;">
              <input type="hidden" name="arrived_id" value="<?= $appt['id'] ?>">
              <button type="submit" class="btn-sm btn-arrived">Mark Arrived</button>
            </form>
            <?php endif; ?>
            <?php if ($appt['status'] === 'pending' || $appt['status'] === 'confirmed'): ?>
            <form method="POST" style="display: inline;" onsubmit="return confirm('Cancel this appointment?');">
              <input type="hidden" name="cancel_id" value="<?= $appt['id'] ?>">
              <button type="submit" class="btn-sm btn-cancel">Cancel</button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</main>
</body>
</html>