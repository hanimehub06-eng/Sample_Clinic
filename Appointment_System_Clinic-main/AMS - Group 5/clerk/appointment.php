<?php
require_once __DIR__ . '/../includes/data.php';
$id = (int)($_GET['id'] ?? 0);
$appt = $appointments[$id] ?? $appointments[0];
$year = 2026; $month = 5;
$first_dow = date('N', mktime(0,0,0,$month,1,$year)) % 7;
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$selected_day = 28;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Appointment Detail — Clerk</title>
  <link rel="stylesheet" href="../panel.css">
</head>
<body>
<div class="panel-layout">
  <?php include __DIR__ . '/sidebar.php'; ?>

  <div class="panel-main">
    <div class="panel-topbar">Medi Clinic</div>

    <div class="panel-content">
      <div class="page-hd">
        <a href="index.php" class="back-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
          Go back
        </a>
        <h1>Appointment Request</h1>
      </div>


      <div class="appt-detail-grid">
        <div>
          <div class="appt-section-title">Patient Information</div>

          <?php
          $parts = explode(',', $appt['patient'], 2);
          $last = trim($parts[0] ?? '');
          $first = trim($parts[1] ?? '');
          ?>

          <div class="appt-field">
            <label>Last Name <span class="req">*</span></label>
            <input class="appt-input" type="text" value="<?= htmlspecialchars($last) ?>" readonly>
          </div>
          <div class="appt-field">
            <label>Given Name <span class="req">*</span></label>
            <input class="appt-input" type="text" value="<?= htmlspecialchars($first) ?>" readonly>
          </div>
          <div class="appt-field">
            <label>Middle Name</label>
            <input class="appt-input" type="text" value="" readonly>
          </div>
          <div class="appt-field">
            <label>Date of Birth <span class="req">*</span></label>
            <div class="appt-dob-row">
              <input class="appt-input" type="text" value="December" readonly>
              <input class="appt-input" type="text" value="20" readonly>
              <input class="appt-input" type="text" value="2005" readonly>
              <input class="appt-input" type="text" value="20" readonly style="max-width:70px;">
            </div>
          </div>
          <div class="appt-field">
            <label>Sex <span class="req">*</span></label>
            <input class="appt-input" type="text" value="Male" readonly style="max-width:100px;">
          </div>
          <div class="appt-field">
            <label>Email <span class="req">*</span></label>
            <input class="appt-input" type="text" value="patient@gmail.com" readonly>
          </div>
          <div class="appt-field">
            <label>Phone Number <span class="req">*</span></label>
            <input class="appt-input" type="text" value="09123456789" readonly>
          </div>
          <div class="appt-field">
            <label>Reason</label>
            <textarea class="appt-input" rows="3" readonly style="resize:vertical;">Routine check-up and follow-up consultation.</textarea>
          </div>
        </div>

        <div class="appt-info-panel">
          <div class="appt-section-title">Appointment Information</div>
          <div class="appt-dr-name"><?= $appt['doctor'] ?></div>
          <div class="appt-month-label">Month: May 28 2026</div>

          <div class="appt-mini-cal">
            <div class="mini-cal-dow">
              <?php foreach (['Su','Mo','Tu','We','Th','Fr','Sa'] as $d): ?>
              <div class="mini-cal-dow-cell"><?= $d ?></div>
              <?php endforeach; ?>
            </div>
            <div class="mini-cal-grid">
              <?php for ($i=0;$i<$first_dow;$i++): ?><div class="mini-cal-cell dim"></div><?php endfor; ?>
              <?php for ($d=1;$d<=$days_in_month;$d++): ?>
              <div class="mini-cal-cell<?= $d===$selected_day?' active':'' ?>"><?= $d ?></div>
              <?php endfor; ?>
            </div>
          </div>

          <div class="appt-time-label" style="margin-top:14px;">
            <strong>Time of Appointment:</strong> 8:00 am - 9:00 am
          </div>

          <div style="display:flex;gap:12px;margin-top:24px;width:100%;">
            <button class="btn-approve" style="flex:1;padding:12px;" onclick="approveThis(this)">Approve</button>
            <button class="btn-cancel" style="flex:1;padding:12px;" onclick="cancelThis()">Cancel</button>
          </div>
        </div>
      </div>

      <div class="pagination" style="margin-top:28px;">
        <a href="index.php" class="page-arrow">&#8592;</a>
        <?php for ($p=1;$p<=9;$p++): ?>
        <a href="appointment.php?id=<?=$p-1?>" class="page-btn<?= $id===($p-1)?' current':'' ?>"><?=$p?></a>
        <?php endfor; ?>
        <a href="appointment.php?id=<?= min($id+1,8) ?>" class="page-arrow">&#8594;</a>
      </div>
    </div>
  </div>
</div>

<script>
function approveThis(btn) {
  btn.style.background = '#aaa';
  btn.disabled = true;
  btn.textContent = 'Approved';
  alert('Appointment approved!');
}
function cancelThis() {
  if (confirm('Cancel this appointment?')) {
    window.location.href = 'index.php';
  }
}
</script>
</body>
</html>
