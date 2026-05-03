<?php
require_once __DIR__ . '/../includes/data.php';
$year = 2026; $month = 5;
$first_dow = date('N', mktime(0,0,0,$month,1,$year)) % 7;
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$doc_id = $_GET['doc'] ?? 'bongon';
$doc = $doctors[$doc_id] ?? $doctors['bongon'];

$avail_days  = [1,2,3,6,7,8,9,10,13,14,15,16,17,22,23,24,29,30];
$unavail_days = [20,21,27];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Doctor Schedules — Clerk</title>
  <link rel="stylesheet" href="../panel.css">
</head>
<body>
<div class="panel-layout">
  <?php include __DIR__ . '/sidebar.php'; ?>

  <div class="panel-main">
    <div class="panel-topbar">Medi Clinic</div>

    <div class="panel-content">
      <div class="page-hd">
        <h1>Doctor Schedule</h1>
      </div>

      <div class="cal-header-row">
        <select class="month-select">
          <option selected>May</option>
          <option>June</option>
          <option>July</option>
        </select>

        <select class="doctor-select" onchange="location.href='schedules.php?doc='+this.value" style="margin-left:auto;">
          <?php foreach ($doctors as $d): ?>
          <option value="<?= $d['id'] ?>"<?= $d['id']===$doc_id?' selected':'' ?>><?= $d['name'] ?></option>
          <?php endforeach; ?>
        </select>
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
            if (in_array($d,$unavail_days)) $cls = ' cal-unavail';
            elseif (in_array($d,$avail_days)) $cls = ' cal-avail';
          ?>
          <a href="schedule-edit.php?day=<?=$d?>&doc=<?=$doc_id?>" class="big-cal-cell<?= $cls ?>"><?= $d ?></a>
          <?php endfor; ?>
        </div>
      </div>

      <div class="cal-legend">
        <span><span class="legend-dot legend-unavail"></span>Not Available</span>
        <span><span class="legend-dot legend-avail"></span>Available</span>
      </div>
    </div>
  </div>
</div>
</body>
</html>
