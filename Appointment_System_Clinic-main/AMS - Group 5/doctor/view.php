<?php
require_once __DIR__ . '/../includes/data.php';
$patient_name = $_GET['patient'] ?? 'Abella, Gabriel Rey';
$year = 2026; $month = 5;
$first_dow = date('N', mktime(0,0,0,$month,1,$year)) % 7;
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$selected_day = 28;

$parts = explode(',', $patient_name, 2);
$last = trim($parts[0] ?? '');
$first = trim($parts[1] ?? '');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>View Patient — Doctor</title>
  <link rel="stylesheet" href="../panel.css">
</head>
<body>
<div class="panel-layout">
  <?php include __DIR__ . '/sidebar.php'; ?>

  <div class="panel-main">
    <div class="panel-topbar">Medi Clinic</div>

    <div class="panel-content">
      <div class="page-hd">
        <h1>Your Patient Appointments</h1>
      </div>

      <div class="appt-detail-grid">
        <div>
          <div class="appt-section-title">Patient Information</div>

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
            <input class="appt-input" type="text" value="08abellagabriel@gmail.com" readonly>
          </div>
          <div class="appt-field">
            <label>Phone Number <span class="req">*</span></label>
            <input class="appt-input" type="text" value="0912345677" readonly>
          </div>
        </div>

        <div class="appt-info-panel">
          <div class="appt-section-title">Appointment Information</div>
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

          <button class="btn-done" style="margin-top:24px;" onclick="window.history.back()">DONE</button>
        </div>
      </div>

      <div class="pagination" style="margin-top:28px;">
        <a href="appointments.php" class="page-arrow">&#8592;</a>
        <?php for ($p=1;$p<=9;$p++): ?>
        <a href="#" class="page-btn<?= $p===1?' current':'' ?>"><?=$p?></a>
        <?php endfor; ?>
        <a href="#" class="page-arrow">&#8594;</a>
      </div>
    </div>
  </div>
</div>
</body>
</html>
