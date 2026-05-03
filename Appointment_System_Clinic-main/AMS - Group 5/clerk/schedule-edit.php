<?php
require_once __DIR__ . '/../includes/data.php';
$year = 2026; $month = 5;
$first_dow = date('N', mktime(0,0,0,$month,1,$year)) % 7;
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$doc_id = $_GET['doc'] ?? 'bongon';
$doc = $doctors[$doc_id] ?? $doctors['bongon'];
$selected_day = (int)($_GET['day'] ?? 1);

$time_slots_edit = [
  '8am - 9am',
  '10am - 11am',
  '12pm - 1pm',
  '2pm - 3pm',
  '4pm - 5pm',
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Schedule — Clerk</title>
  <link rel="stylesheet" href="../panel.css">
</head>
<body>
<div class="panel-layout">
  <?php include __DIR__ . '/sidebar.php'; ?>

  <div class="panel-main">
    <div class="panel-topbar">Medi Clinic</div>

    <div class="panel-content">
      <div class="page-hd">
        <a href="schedules.php?doc=<?= $doc_id ?>" class="back-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
          Go back
        </a>
        <h1>Doctor Schedule</h1>
      </div>

      <div class="schedule-split">
        <div class="schedule-mini-cal">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <button class="month-select" style="cursor:default;pointer-events:none;">May</button>
            
            <select class="doctor-select" style="font-size:13px;" onchange="location.href='schedule-edit.php?doc='+this.value+'&day=<?=$selected_day?>'">
              <?php foreach ($doctors as $d): ?>
              <option value="<?= $d['id'] ?>"<?= $d['id']===$doc_id?' selected':'' ?>><?= $d['name'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mini-calendar">
            <div class="mini-cal-dow">
              <?php foreach (['Su','Mo','Tu','We','Th','Fr','Sa'] as $d): ?>
              <div class="mini-cal-dow-cell"><?= $d ?></div>
              <?php endforeach; ?>
            </div>
            <div class="mini-cal-grid">
              <?php for ($i=0;$i<$first_dow;$i++): ?><div class="mini-cal-cell dim"></div><?php endfor; ?>
              <?php for ($d=1;$d<=$days_in_month;$d++): ?>
              <a href="schedule-edit.php?day=<?=$d?>&doc=<?=$doc_id?>" class="mini-cal-cell<?= $d===$selected_day?' active':'' ?>"><?= $d ?></a>
              <?php endfor; ?>
            </div>
          </div>

          <br>
          <button class="btn-save" onclick="saveSchedule()">Save</button>
        </div>

        <div class="schedule-panel">
          <h4>Schedule</h4>

          <?php
          $slot_states = ['8am - 9am' => false, '10am - 11am' => false, '12pm - 1pm' => true, '2pm - 3pm' => false, '4pm - 5pm' => false];
          foreach ($slot_states as $slot => $enabled):
          ?>
          <div class="schedule-slot-row<?= $enabled?' slot-enabled':'' ?>" id="slot_<?= md5($slot) ?>">
            <span><?= $slot ?></span>
            <?php if ($enabled): ?>
            <button class="btn-enable" onclick="toggleSlot(this, '<?= $slot ?>')">Enable</button>
            <?php else: ?>
            <button class="btn-disable" onclick="toggleSlot(this, '<?= $slot ?>')">Disable</button>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>

          <div class="schedule-slot-row" style="margin-top:6px;">
            <span><strong>All Schedule</strong></span>
            <button class="btn-disable" onclick="disableAll()">Disable</button>
          </div>
        </div>
      </div>

      <div class="cal-legend" style="margin-top:28px;">
        <span><span class="legend-dot legend-unavail"></span>Not Available</span>
        <span><span class="legend-dot legend-avail"></span>Available</span>
      </div>
    </div>
  </div>
</div>

<script>
function toggleSlot(btn, slot) {
  var row = btn.closest('.schedule-slot-row');
  if (row.classList.contains('slot-enabled')) {
    row.classList.remove('slot-enabled');
    btn.textContent = 'Disable';
    btn.className = 'btn-disable';
  } else {
    row.classList.add('slot-enabled');
    btn.textContent = 'Enable';
    btn.className = 'btn-enable';
  }
}
function disableAll() {
  document.querySelectorAll('.schedule-slot-row').forEach(function(row) {
    row.classList.remove('slot-enabled');
    var btn = row.querySelector('button');
    if (btn) { btn.textContent = 'Disable'; btn.className = 'btn-disable'; }
  });
}
function saveSchedule() {
  alert('Schedule saved!');
}
</script>
</body>
</html>
