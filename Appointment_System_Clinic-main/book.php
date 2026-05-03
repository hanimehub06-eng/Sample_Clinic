<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$doctors = getDoctors();
$selDoctor = $_GET['doctor'] ?? ($doctors[0]['id'] ?? null);
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$selectedDate = $_GET['date'] ?? null;
$selectedSlot = $_GET['slot'] ?? null;

// Build calendar
$firstDay = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$firstDow = (int)date('N', $firstDay) - 1;
$monthName = date('F Y', $firstDay);

// Navigate months
$prevMonth = $month == 1 ? 12 : $month - 1;
$prevYear = $month == 1 ? $year - 1 : $year;
$nextMonth = $month == 12 ? 1 : $month + 1;
$nextYear = $month == 12 ? $year + 1 : $year;

// Get available slots for selected date
$timeSlots = [];
if ($selDoctor && $selectedDate) {
    $timeSlots = getTimeSlots($selDoctor, $selectedDate);
}

// Days of week labels
$dowLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Book Appointment — Medi Clinic</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .form-page {
      padding: 40px 0 60px;
      min-height: calc(100vh - 200px);
    }
    .form-card {
      max-width: 800px;
      margin: 0 auto;
      background: #fff;
      border-radius: 12px;
      padding: 32px;
      box-shadow: 0 4px 20px rgba(0,0,0,.08);
    }
    .go-back {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      color: #666;
      text-decoration: none;
      font-size: 14px;
      margin-bottom: 20px;
    }
    .go-back:hover { color: var(--nav-bg); }
    .form-title {
      font-size: 20px;
      font-weight: 700;
      color: var(--nav-bg);
      margin-bottom: 24px;
    }

    .doctor-select-wrap {
      margin-bottom: 24px;
    }
    .doctor-select {
      width: 100%;
      padding: 14px 16px;
      border: 1.5px solid #d0d9d6;
      border-radius: 8px;
      font-size: 15px;
      background: #fff;
      cursor: pointer;
    }

    .calendar-wrap {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 32px;
    }
    @media (max-width: 600px) {
      .calendar-wrap { grid-template-columns: 1fr; }
    }

    .calendar {
      background: #fff;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      overflow: hidden;
    }
    .cal-nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 16px;
      background: var(--nav-bg);
      color: #fff;
    }
    .cal-nav-btn {
      background: none;
      border: none;
      color: #fff;
      cursor: pointer;
      font-size: 14px;
      padding: 4px 8px;
    }
    .cal-month {
      font-weight: 600;
    }
    .cal-dow {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      background: #f5f5f5;
    }
    .cal-dow-cell {
      padding: 8px 4px;
      text-align: center;
      font-size: 12px;
      font-weight: 600;
      color: #666;
    }
    .cal-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
    }
    .cal-cell {
      aspect-ratio: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 1px solid #f0f0f0;
      font-size: 14px;
      cursor: pointer;
      position: relative;
    }
    .cal-cell:hover:not(.dim):not(.disabled) {
      background: #f0f8f6;
    }
    .cal-cell.dim {
      background: #f9f9f9;
      color: #ccc;
      cursor: default;
    }
    .cal-cell.disabled {
      background: #f0f0f0;
      color: #aaa;
      cursor: not-allowed;
    }
    .cal-cell.selected {
      background: var(--nav-bg);
      color: #fff;
    }
    .cal-cell.today {
      font-weight: 700;
      color: var(--teal-mid);
    }
    .cal-cell .cal-day-input {
      display: none;
    }
    .cal-cell label {
      cursor: pointer;
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .cal-label {
      text-align: center;
      margin-top: 12px;
      color: #666;
      font-size: 13px;
    }

    .slots-panel h4 {
      font-size: 16px;
      color: var(--nav-bg);
      margin-bottom: 16px;
    }
    .slot-row {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      margin-bottom: 12px;
      cursor: pointer;
      transition: background .2s;
    }
    .slot-row:hover:not(.unavailable) {
      background: #f8faf9;
    }
    .slot-row.selected {
      border-color: var(--nav-bg);
      background: #f0f8f6;
    }
    .slot-row.unavailable {
      opacity: 0.5;
      cursor: not-allowed;
    }
    .slot-row input {
      display: none;
    }
    .slot-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--teal-mid);
    }
    .slot-row.unavailable .slot-dot {
      background: #ccc;
    }
    .slot-label {
      flex: 1;
      font-size: 14px;
    }
    .slot-pill {
      font-size: 11px;
      padding: 4px 8px;
      border-radius: 4px;
      font-weight: 600;
    }
    .pill-available {
      background: #e8f5e9;
      color: #27ae60;
    }
    .pill-unavailable {
      background: #f5f5f5;
      color: #999;
    }

    .btn-primary {
      display: block;
      width: 100%;
      padding: 14px;
      background: var(--nav-bg);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      text-align: center;
      text-decoration: none;
      margin-top: 20px;
    }
    .btn-primary:hover {
      background: #1b4036;
    }
    .btn-primary:disabled {
      background: #ccc;
      cursor: not-allowed;
    }

    .progress-bar {
      display: flex;
      gap: 8px;
      margin-top: 32px;
    }
    .progress-seg {
      flex: 1;
      height: 4px;
      background: #e0e0e0;
      border-radius: 2px;
    }
    .progress-seg.active {
      background: var(--nav-bg);
    }

    .calendar-legend {
      display: flex;
      gap: 16px;
      margin-top: 12px;
      font-size: 12px;
      justify-content: center;
    }
    .legend-item {
      display: flex;
      align-items: center;
      gap: 6px;
      color: #666;
    }
    .legend-box {
      width: 16px;
      height: 16px;
      border-radius: 4px;
    }
    .legend-box.holiday {
      background: #ffebee;
      border: 1px solid #ffcdd2;
    }
    .legend-box.blocked {
      background: #fff3e0;
      border: 1px solid #ffe0b2;
    }
    .legend-box.selected {
      background: var(--nav-bg);
    }
  </style>
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
        <option value="<?= $d['id'] ?>"<?= $d['id']==$selDoctor?' selected':''?>><?= htmlspecialchars($d['full_name']) ?> (<?= htmlspecialchars($d['specialization']) ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="calendar-wrap">
      <div>
        <div class="calendar">
          <div class="cal-nav">
            <button class="cal-nav-btn" onclick="location.href='book.php?doctor=<?= $selDoctor ?>&month=<?= $prevMonth ?>&year=<?= $prevYear ?>'">&lt; Prev</button>
            <span class="cal-month"><?= $monthName ?></span>
            <button class="cal-nav-btn" onclick="location.href='book.php?doctor=<?= $selDoctor ?>&month=<?= $nextMonth ?>&year=<?= $nextYear ?>'">Next &gt;</button>
          </div>
          <div class="cal-dow">
            <?php foreach ($dowLabels as $d): ?><div class="cal-dow-cell"><?=$d?></div><?php endforeach; ?>
          </div>
          <div class="cal-grid">
            <?php for ($i=0;$i<$firstDow;$i++): ?><div class="cal-cell dim"></div><?php endfor; ?>
            <?php for ($d=1;$d<=$daysInMonth;$d++):
              $cellDate = sprintf('%04d-%02d-%02d', $year, $month, $d);
              $today = date('Y-m-d');
              $isPast = $cellDate < $today;
              $isToday = $cellDate === $today;
              $isHoliday = isHoliday($cellDate);
              $isBlocked = isDateBlocked($selDoctor, $cellDate);
              $dayOfWeek = (int)date('N', strtotime($cellDate)) - 1;

              // Check if doctor works on this day
              $schedule = dbFetch("
                SELECT * FROM doctor_schedules
                WHERE doctor_id = ? AND day_of_week = ? AND is_active = TRUE
              ", [$selDoctor, $dayOfWeek]);

              $disabled = $isPast || $isHoliday || $isBlocked || !$schedule;
              $selected = $selectedDate === $cellDate;
            ?>
            <div class="cal-cell<?= $disabled ? ' disabled' : '' ?><?= $selected ? ' selected' : '' ?><?= $isToday ? ' today' : '' ?>">
              <input type="radio" name="cal_day" value="<?= $cellDate ?>" id="d<?= $d ?>" <?= $selected ? 'checked' : '' ?>
                     onclick="selectDate('<?= $cellDate ?>', '<?= $selDoctor ?>')">
              <?php if (!$disabled): ?>
              <label for="d<?= $d ?>"><?=$d?></label>
              <?php else: ?>
              <?=$d?>
              <?php endif; ?>
            </div>
            <?php endfor; ?>
          </div>
        </div>
        <p class="cal-label"><?= $monthName ?></p>
        <div class="calendar-legend">
          <div class="legend-item"><div class="legend-box holiday"></div> Holiday</div>
          <div class="legend-item"><div class="legend-box blocked"></div> Blocked</div>
        </div>
      </div>

      <div class="slots-panel" id="slotsPanel">
        <h4>Time Slots</h4>
        <?php if ($selectedDate): ?>
          <?php if (empty($timeSlots)): ?>
          <p style="color:#888;font-size:13px;text-align:center;">No available time slots for this date.</p>
          <?php else: ?>
          <form id="slotForm">
            <?php foreach ($timeSlots as $i => $slot): ?>
            <div class="slot-row<?= $slot['available'] ? '' : ' unavailable' ?><?= $selectedSlot === $slot['time'] ? ' selected' : '' ?>" onclick="selectSlot('<?= htmlspecialchars($slot['time']) ?>')">
              <input type="radio" name="slot" value="<?= htmlspecialchars($slot['time']) ?>" <?= $slot['available'] ? '' : 'disabled' ?> <?= $selectedSlot === $slot['time'] ? 'checked' : '' ?>>
              <span class="slot-dot"></span>
              <span class="slot-label"><?= htmlspecialchars($slot['time']) ?></span>
              <?php if ($slot['available']): ?>
              <span class="slot-pill pill-available">Available</span>
              <?php else: ?>
              <span class="slot-pill pill-unavailable">Unavailable</span>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </form>
          <?php if ($selectedSlot): ?>
          <a href="details.php?doctor=<?= $selDoctor ?>&date=<?= $selectedDate ?>&slot=<?= urlencode($selectedSlot) ?>" class="btn-primary">NEXT</a>
          <?php endif; ?>
          <?php endif; ?>
        <?php else: ?>
        <p style="color:#888;font-size:13px;text-align:center;">Select a date to see available times</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="progress-bar">
      <div class="progress-seg active"></div>
      <div class="progress-seg<?= $selectedDate && $selectedSlot ? ' active' : '' ?>"></div>
      <div class="progress-seg"></div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
function selectDate(date, doctor) {
  location.href = 'book.php?doctor=' + doctor + '&date=' + date;
}

function selectSlot(slot) {
  var url = new URL(location.href);
  url.searchParams.set('slot', slot);
  location.href = url.toString();
}
</script>
</body>
</html>