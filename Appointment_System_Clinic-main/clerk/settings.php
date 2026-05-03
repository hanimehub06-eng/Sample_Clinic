<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('clerk');

$user = getCurrentUser();
$msg = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $settings = [
        'clinic_name' => $_POST['clinic_name'],
        'clinic_address' => $_POST['clinic_address'],
        'clinic_phone' => $_POST['clinic_phone'],
        'clinic_email' => $_POST['clinic_email'],
        'clinic_operating_start' => $_POST['clinic_operating_start'],
        'clinic_operating_end' => $_POST['clinic_operating_end'],
    ];

    foreach ($settings as $key => $value) {
        dbUpdate('clinic_settings', ['setting_value' => $value], "setting_key = ?", [$key]);
    }

    $msg = 'Clinic settings saved.';
}

// Handle add holiday
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_holiday'])) {
    $holidayDate = $_POST['holiday_date'];
    $holidayName = $_POST['holiday_name'];

    if ($holidayDate && $holidayName) {
        // Check if exists
        $exists = dbFetch("SELECT id FROM holidays WHERE holiday_date = ?", [$holidayDate]);
        if (!$exists) {
            dbInsert('holidays', [
                'holiday_date' => $holidayDate,
                'name' => $holidayName,
                'created_by' => $user['id']
            ]);
            $msg = 'Holiday added.';
        } else {
            $msg = 'Holiday already exists for this date.';
        }
    }
}

// Handle delete holiday
if (isset($_GET['delete_holiday'])) {
    dbDelete('holidays', "id = ?", [$_GET['delete_holiday']]);
    $msg = 'Holiday removed.';
}

// Get current settings
$clinicName = getSetting('clinic_name', 'Medi Clinic');
$clinicAddress = getSetting('clinic_address', '');
$clinicPhone = getSetting('clinic_phone', '');
$clinicEmail = getSetting('clinic_email', '');
$clinicStart = getSetting('clinic_operating_start', '08:00:00');
$clinicEnd = getSetting('clinic_operating_end', '17:00:00');

// Get holidays
$holidays = getHolidays();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Clinic Settings — Medi Clinic</title>
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

    .settings-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
    }
    @media (max-width: 900px) {
      .settings-grid { grid-template-columns: 1fr; }
    }

    .settings-card {
      background: #fff;
      border-radius: 12px;
      padding: 24px;
      box-shadow: 0 2px 8px rgba(0,0,0,.06);
    }
    .card-title {
      font-size: 18px;
      font-weight: 700;
      color: var(--nav-bg);
      margin-bottom: 20px;
    }

    .form-group {
      margin-bottom: 16px;
    }
    .form-group label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: #444;
      margin-bottom: 6px;
    }
    .form-group input {
      width: 100%;
      padding: 10px 14px;
      border: 1.5px solid #d0d9d6;
      border-radius: 6px;
      font-size: 14px;
    }

    .btn-save {
      padding: 12px 24px;
      background: var(--nav-bg);
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: 14px;
      font-weight: 700;
      cursor: pointer;
    }
    .btn-save:hover {
      background: #1b4036;
    }

    .holiday-list {
      margin-top: 16px;
    }
    .holiday-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px;
      border-bottom: 1px solid #f0f0f0;
    }
    .holiday-item:last-child {
      border-bottom: none;
    }
    .holiday-date {
      font-weight: 600;
      color: var(--nav-bg);
    }
    .holiday-name {
      font-size: 14px;
      color: #666;
    }
    .btn-delete {
      padding: 6px 12px;
      background: #fff;
      border: 1px solid #f5a0a0;
      color: #c0392b;
      border-radius: 4px;
      font-size: 12px;
      cursor: pointer;
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
      <h1>Settings</h1>
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

    <div class="settings-grid">
      <div class="settings-card">
        <h3 class="card-title">Clinic Information</h3>
        <form method="POST">
          <div class="form-group">
            <label for="clinic_name">Clinic Name</label>
            <input type="text" id="clinic_name" name="clinic_name" value="<?= htmlspecialchars($clinicName) ?>">
          </div>
          <div class="form-group">
            <label for="clinic_address">Address</label>
            <input type="text" id="clinic_address" name="clinic_address" value="<?= htmlspecialchars($clinicAddress) ?>">
          </div>
          <div class="form-group">
            <label for="clinic_phone">Phone</label>
            <input type="text" id="clinic_phone" name="clinic_phone" value="<?= htmlspecialchars($clinicPhone) ?>">
          </div>
          <div class="form-group">
            <label for="clinic_email">Email</label>
            <input type="email" id="clinic_email" name="clinic_email" value="<?= htmlspecialchars($clinicEmail) ?>">
          </div>
          <div class="form-group">
            <label for="clinic_operating_start">Operating Hours Start</label>
            <input type="time" id="clinic_operating_start" name="clinic_operating_start" value="<?= substr($clinicStart, 0, 5) ?>">
          </div>
          <div class="form-group">
            <label for="clinic_operating_end">Operating Hours End</label>
            <input type="time" id="clinic_operating_end" name="clinic_operating_end" value="<?= substr($clinicEnd, 0, 5) ?>">
          </div>
          <button type="submit" name="save_settings" class="btn-save">Save Settings</button>
        </form>
      </div>

      <div class="settings-card">
        <h3 class="card-title">Holidays</h3>
        <form method="POST">
          <div class="form-group">
            <label for="holiday_date">Date</label>
            <input type="date" id="holiday_date" name="holiday_date" required>
          </div>
          <div class="form-group">
            <label for="holiday_name">Holiday Name</label>
            <input type="text" id="holiday_name" name="holiday_name" placeholder="e.g., Christmas Day" required>
          </div>
          <button type="submit" name="add_holiday" class="btn-save">Add Holiday</button>
        </form>

        <div class="holiday-list">
          <?php if (empty($holidays)): ?>
          <p style="color: #888; text-align: center; padding: 20px;">No holidays added.</p>
          <?php else: ?>
          <?php foreach ($holidays as $holiday): ?>
          <div class="holiday-item">
            <div>
              <div class="holiday-date"><?= formatDate($holiday['holiday_date']) ?></div>
              <div class="holiday-name"><?= htmlspecialchars($holiday['name']) ?></div>
            </div>
            <a href="?delete_holiday=<?= $holiday['id'] ?>" class="btn-delete" onclick="return confirm('Remove this holiday?');">Remove</a>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>
</body>
</html>