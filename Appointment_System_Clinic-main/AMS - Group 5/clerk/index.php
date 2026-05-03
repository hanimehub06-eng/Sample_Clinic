<?php require_once __DIR__ . '/../includes/data.php';
$filter = $_GET['filter'] ?? 'All';
$confirmed = array_filter($appointments, fn($a) => $a['status']==='Confirmed');
$pending   = array_filter($appointments, fn($a) => $a['status']==='Pending');
if ($filter === 'Confirmed') $list = $confirmed;
elseif ($filter === 'Pending') $list = $pending;
else $list = $appointments;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Appointment Requests — Clerk</title>
  <link rel="stylesheet" href="../panel.css">
</head>
<body>
<div class="panel-layout">
  <?php include __DIR__ . '/sidebar.php'; ?>

  <div class="panel-main">
    <div class="panel-topbar">Medi Clinic</div>

    <div class="panel-content">
      <div class="page-hd">
        <h1>Appointment Request</h1>
      </div>

      <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
        <div class="counter-row" style="margin-bottom:0;">
          <div class="counter-card">
            <div class="counter-icon green">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div class="counter-info">
              <span class="counter-label">Confirmed</span>
              <span class="counter-num"><?= count($confirmed) ?></span>
            </div>
          </div>
          <div class="counter-card">
            <div class="counter-icon gray">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div class="counter-info">
              <span class="counter-label">Pending</span>
              <span class="counter-num"><?= count($pending) ?></span>
            </div>
          </div>
        </div>

        <div class="filter-row" style="margin-bottom:0;">
          <select class="filter-select" onchange="location.href='index.php?filter='+this.value">
            <option <?= $filter==='All'?'selected':'' ?>>All</option>
            <option <?= $filter==='Confirmed'?'selected':'' ?>>Confirmed</option>
            <option <?= $filter==='Pending'?'selected':'' ?>>Pending</option>
          </select>
        </div>
      </div>

      <table class="data-table">
        <thead>
          <tr>
            <th>Date and Time</th>
            <th>Patient</th>
            <th>Status</th>
            <th>Doctor</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($list as $i => $appt): ?>
          <tr>
            <td><?= $appt['date'] ?></td>
            <td><a href="appointment.php?id=<?= $i ?>" style="color:inherit;"><?= $appt['patient'] ?></a></td>
            <td><span class="status-pill status-<?= strtolower($appt['status']) ?>"><?= $appt['status'] ?></span></td>
            <td><?= $appt['doctor'] ?></td>
            <td>
              <div class="action-btns">
                <?php if ($appt['status'] !== 'Confirmed'): ?>
                <button class="btn-approve" onclick="approveAppt(this)">Approve</button>
                <?php endif; ?>
                <button class="btn-cancel" onclick="cancelAppt(this)">Cancel</button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
function approveAppt(btn) {
  var row = btn.closest('tr');
  var statusCell = row.querySelector('.status-pill');
  statusCell.className = 'status-pill status-confirmed';
  statusCell.textContent = 'Confirmed';
  btn.remove();
}
function cancelAppt(btn) {
  var row = btn.closest('tr');
  if (confirm('Cancel this appointment?')) {
    var statusCell = row.querySelector('.status-pill');
    statusCell.className = 'status-pill status-cancelled';
    statusCell.textContent = 'Cancelled';
    row.querySelectorAll('.btn-approve, .btn-cancel').forEach(b => b.remove());
  }
}
</script>
</body>
</html>
