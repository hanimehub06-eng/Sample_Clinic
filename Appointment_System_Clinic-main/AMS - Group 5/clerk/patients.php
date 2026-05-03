<?php
require_once __DIR__ . '/../includes/data.php';
$year = 2026; $month = 5;
$first_dow = date('N', mktime(0,0,0,$month,1,$year)) % 7;
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$filter = $_GET['filter'] ?? 'All';
$avail_days = [1,2,3,6,7,8,9,10,13,14,15,16,17,20,21,22,23,24,27,28];
$full_days  = [];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Patient Appointments — Clerk</title>
  <link rel="stylesheet" href="../panel.css">
</head>
<body>
<div class="panel-layout">
  <?php include __DIR__ . '/sidebar.php'; ?>

  <div class="panel-main">
    <div class="panel-topbar">Medi Clinic</div>

    <div class="panel-content">
      <div class="page-hd">
        <h1>Patient Appointments</h1>
      </div>

      <div class="cal-header-row">
        <select class="month-select">
          <option selected>May</option>
          <option>June</option>
          <option>July</option>
        </select>
        <select class="filter-select" onchange="location.href='patients.php?filter='+this.value">
          <option <?= $filter==='All'?'selected':'' ?>>All</option>
          <option <?= $filter==='Available'?'selected':'' ?>>Available</option>
          <option <?= $filter==='Full'?'selected':'' ?>>Full</option>
        </select>
        <span class="total-label">Total Appointments: 100</span>
      </div>

      <div class="big-calendar">
        <div class="big-cal-dow">
          <?php foreach (['Su','Mo','Tu','We','Th','Fr','Sa'] as $d): ?>
          <div class="big-cal-dow-cell"><?= $d ?></div>
          <?php endforeach; ?>
        </div>
        <div class="big-cal-grid">
          <?php for ($i=0;$i<$first_dow;$i++): ?><div class="big-cal-cell dim"></div><?php endfor; ?>
          <?php for ($d=1;$d<=$days_in_month;$d++):
            $cls = '';
            if (in_array($d,$avail_days)) $cls = ' cal-avail';
            elseif (in_array($d,$full_days)) $cls = ' cal-unavail';
          ?>
          <a href="patients-slots.php?day=<?=$d?>" class="big-cal-cell<?= $cls ?>"><?= $d ?></a>
          <?php endfor; ?>
        </div>
      </div>

      <div class="cal-legend">
        <span><span class="legend-dot legend-unavail"></span>Appointment Full</span>
        <span><span class="legend-dot legend-avail"></span>Appointment many available slot</span>
      </div>
    </div>
  </div>
</div>
</body>
</html>
