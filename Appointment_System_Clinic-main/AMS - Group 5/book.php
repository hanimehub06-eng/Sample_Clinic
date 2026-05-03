<?php
require_once __DIR__ . '/includes/data.php';
$sel_doctor = $_GET['doctor'] ?? 'bongon';
$year  = 2026;
$month = 5;
$first_dow = date('N', mktime(0,0,0,$month,1,$year)) % 7;
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Book Appointment — Medi Clinic</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="form-page">
  <div class="form-card">
    <a class="go-back" href="doctors.php">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
      Back
    </a>
    <p class="form-title">STEP 1 — SELECT DOCTOR &amp; DATE</p>

    <div class="doctor-select-wrap">
      <select class="doctor-select" id="docSel" onchange="location.href='book.php?doctor='+this.value">
        <?php foreach ($doctors as $d): ?>
        <option value="<?= $d['id'] ?>"<?= $d['id']===$sel_doctor?' selected':''?>><?= $d['full_name'] ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="calendar-wrap">
      <div>
        <div class="calendar">
          <div class="cal-dow">
            <?php foreach (['Su','Mo','Tu','We','Th','Fr','Sa'] as $d): ?><div class="cal-dow-cell"><?=$d?></div><?php endforeach; ?>
          </div>
          <div class="cal-grid">
            <?php for ($i=0;$i<$first_dow;$i++): ?><div class="cal-cell dim"></div><?php endfor; ?>
            <?php for ($d=1;$d<=$days_in_month;$d++): ?>
            <div class="cal-cell">
              <input type="radio" name="cal_day" value="<?=$d?>" id="d<?=$d?>" onclick="pickDay(<?=$d?>)">
              <?=$d?>
            </div>
            <?php endfor; ?>
          </div>
        </div>
        <p class="cal-label">May 2026</p>
      </div>

      <div class="slots-panel" id="slotsPanel">
        <h4>Time Slots</h4>
        <p style="color:#888;font-size:13px;text-align:center;">Select a date to see available times</p>
      </div>
    </div>

    <div class="progress-bar">
      <div class="progress-seg active"></div>
      <div class="progress-seg"></div>
      <div class="progress-seg"></div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
function pickDay(d) {
  var slots = [
    {label:'8:00am - 9:00am', available:true},
    {label:'10:00am - 11:00am', available:true},
    {label:'1:00pm - 2:00pm', available:false},
    {label:'3:00pm - 4:00pm', available:true},
  ];
  var html = '<h4>Time Slots</h4>';
  slots.forEach(function(s, i) {
    var cls = s.available ? '' : ' unavailable';
    var pill = s.available ? '<span class="slot-pill pill-available">Available</span>' : '<span class="slot-pill pill-unavailable">Unavailable</span>';
    html += '<label class="slot-row'+cls+'">'
          + '<span class="slot-dot"></span>'
          + '<input type="radio" name="slot" value="'+i+'"'+(s.available?'':' disabled')+'>'
          + '<span class="slot-label">'+s.label+'</span>'
          + pill
          + '</label>';
  });
  html += '<br><a href="details.php" class="btn-primary" style="margin-top:14px;display:block;text-align:center;width:100%;max-width:none;padding:12px;">NEXT</a>';
  document.getElementById('slotsPanel').innerHTML = html;
}
</script>
</body>
</html>
