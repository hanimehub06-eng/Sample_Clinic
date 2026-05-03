<?php
require_once __DIR__ . '/../includes/data.php';
$day = (int)($_GET['day'] ?? 28);
$doc_filter = $_GET['doc'] ?? 'All';
$date_label = "May $day 2026";
$avail_slots = [
  ['time'=>'8 am',  'status'=>'Available'],
  ['time'=>'9 am',  'status'=>'Available'],
  ['time'=>'10 am', 'status'=>'Available'],
  ['time'=>'11 am', 'status'=>'Available'],
  ['time'=>'12 pm', 'status'=>'Available'],
  ['time'=>'1 pm',  'status'=>'Available'],
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Appointment Slots — Clerk</title>
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
        <a href="patients.php" class="btn-approve" style="border-radius:8px;padding:9px 16px;font-size:20px;line-height:1;text-decoration:none;">&#8592;</a>
        <select class="doctor-select" onchange="location.href='patients-slots.php?day=<?=$day?>&doc='+this.value">
          <option <?= $doc_filter==='All'?'selected':'' ?>>All</option>
          <?php foreach ($doctors as $d): ?>
          <option value="<?= $d['name'] ?>"<?= $doc_filter===$d['name']?' selected':'' ?>><?= $d['name'] ?></option>
          <?php endforeach; ?>
        </select>
        <span class="total-label">Total Appointments: 0</span>
      </div>

      <div class="slots-header">
        <button class="slot-nav-btn" onclick="prevDay()">&#8592;</button>
        <span><?= $date_label ?></span>
        <button class="slot-nav-btn" onclick="nextDay()">&#8594;</button>
      </div>

      <div class="slot-list">
        <?php foreach ($avail_slots as $slot): ?>
        <div class="slot-item">
          <div class="slot-time"><?= $slot['time'] ?></div>
          <div class="slot-avail-label"><?= $slot['status'] ?></div>
          <div class="slot-doctor-label"><?= ($doc_filter==='All' ? 'Dr. A' : $doc_filter) ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="cal-legend" style="margin-top:14px;">
        <span><span class="legend-dot legend-unavail"></span>Taken</span>
        <span><span class="legend-dot legend-orange"></span>Confirming</span>
        <span><span class="legend-dot legend-avail"></span>Available</span>
      </div>
    </div>
  </div>
</div>

<script>
function prevDay() {
  var d = <?= $day ?> - 1;
  if (d < 1) d = 1;
  location.href = 'patients-slots.php?day=' + d + '&doc=<?= urlencode($doc_filter) ?>';
}
function nextDay() {
  var d = <?= $day ?> + 1;
  if (d > 31) d = 31;
  location.href = 'patients-slots.php?day=' + d + '&doc=<?= urlencode($doc_filter) ?>';
}
</script>
</body>
</html>
