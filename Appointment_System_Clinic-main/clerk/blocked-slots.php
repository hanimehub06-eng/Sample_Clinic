<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('clerk');

$user = getCurrentUser();
$doctors = getDoctors();
$msg = '';
$error = '';

// Handle blocking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['block'])) {
    $doctorId = $_POST['doctor_id'] ?: null;
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'] ?: $startDate;
    $reason = $_POST['reason'] ?? '';

    if (empty($startDate)) {
        $error = 'Start date is required.';
    } else {
        // Create blocked slots for each date in range
        $current = strtotime($startDate);
        $end = strtotime($endDate);

        while ($current <= $end) {
            $date = date('Y-m-d', $current);

            // Check if already blocked
            $existing = dbFetch("
                SELECT id FROM blocked_slots
                WHERE blocked_date = ? AND (doctor_id = ? OR doctor_id IS NULL)
            ", [$date, $doctorId]);

            if (!$existing) {
                dbInsert('blocked_slots', [
                    'doctor_id' => $doctorId ?: null,
                    'blocked_date' => $date,
                    'is_all_day' => 1,
                    'reason' => $reason,
                    'created_by' => $user['id']
                ]);
            }

            $current = strtotime('+1 day', $current);
        }

        $msg = 'Dates blocked successfully.';
    }
}

// Handle unblocking
if (isset($_GET['unblock'])) {
    $blockId = (int)$_GET['unblock'];

    // Check if there are appointments in this slot
    $block = dbFetch("SELECT * FROM blocked_slots WHERE id = ?", [$blockId]);
    if ($block) {
        $hasAppointments = dbFetch("
            SELECT COUNT(*) as cnt FROM appointments
            WHERE doctor_id = COALESCE(?, doctor_id)
            AND appointment_date = ?
            AND status != 'cancelled'
        ", [$block['doctor_id'], $block['blocked_date']]);

        if ($hasAppointments['cnt'] > 0) {
            $error = 'Cannot unblock: there are existing appointments in this slot.';
        } else {
            dbDelete('blocked_slots', "id = ?", [$blockId]);
            $msg = 'Slot unblocked successfully.';
        }
    }
}

// Get blocked slots
$blockedSlots = dbFetchAll("
    SELECT b.*, d.full_name as doctor_name, u.full_name as created_by_name
    FROM blocked_slots b
    LEFT JOIN users d ON d.id = b.doctor_id
    LEFT JOIN users u ON u.id = b.created_by
    ORDER BY b.blocked_date DESC
");
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Blocked Slots — Medi Clinic</title>
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
    .page-subtitle {
      font-size: 14px;
      color: #666;
    }

    .block-form {
      background: #fff;
      border-radius: 12px;
      padding: 24px;
      box-shadow: 0 2px 8px rgba(0,0,0,.06);
      margin-bottom: 24px;
    }
    .form-title {
      font-size: 18px;
      font-weight: 700;
      color: var(--nav-bg);
      margin-bottom: 16px;
    }
    .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 16px;
    }
    .form-group {
      margin-bottom: 0;
    }
    .form-group label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: #444;
      margin-bottom: 6px;
    }
    .form-group select,
    .form-group input {
      width: 100%;
      padding: 10px 12px;
      border: 1.5px solid #d0d9d6;
      border-radius: 6px;
      font-size: 14px;
    }

    .btn-block {
      padding: 10px 20px;
      background: var(--nav-bg);
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      align-self: flex-end;
    }
    .btn-block:hover {
      background: #1b4036;
    }

    .alert-error {
      background: #fff0f0;
      border: 1px solid #f5a0a0;
      color: #c0392b;
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
    }
    .alert-success {
      background: #e8f8f5;
      border: 1px solid #a8e6cf;
      color: #27ae60;
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
    }

    .blocked-list {
      background: #fff;
      border-radius: 12px;
      padding: 24px;
      box-shadow: 0 2px 8px rgba(0,0,0,.06);
    }
    .section-title {
      font-size: 18px;
      font-weight: 700;
      color: var(--nav-bg);
      margin-bottom: 16px;
    }

    .blocked-table {
      width: 100%;
      border-collapse: collapse;
    }
    .blocked-table th {
      text-align: left;
      padding: 12px;
      font-size: 12px;
      font-weight: 600;
      color: #666;
      background: #f8faf9;
      border-bottom: 2px solid #f0f0f0;
    }
    .blocked-table td {
      padding: 12px;
      font-size: 14px;
      border-bottom: 1px solid #f0f0f0;
    }

    .btn-unblock {
      padding: 6px 12px;
      background: #fff;
      border: 1px solid #f5a0a0;
      color: #c0392b;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
    }
    .btn-unblock:hover {
      background: #fff0f0;
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/sidebar.php'; ?>

<main class="panel-main">
  <div class="panel-header">
    <div>
      <h1>Blocked Slots</h1>
      <p class="page-subtitle">Block dates or date ranges to prevent bookings</p>
    </div>
    <div class="panel-user">
      <span><?= htmlspecialchars($user['full_name']) ?></span>
      <a href="../logout.php" class="btn-logout">Logout</a>
    </div>
  </div>

  <div class="panel-content">
    <?php if ($error): ?>
    <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($msg): ?>
    <div class="alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form method="POST" class="block-form">
      <h3 class="form-title">Block New Date(s)</h3>
      <div class="form-grid">
        <div class="form-group">
          <label for="doctor_id">Doctor (Optional)</label>
          <select name="doctor_id" id="doctor_id">
            <option value="">All Doctors</option>
            <?php foreach ($doctors as $d): ?>
            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['full_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="start_date">Start Date *</label>
          <input type="date" name="start_date" id="start_date" required min="<?= date('Y-m-d') ?>">
        </div>
        <div class="form-group">
          <label for="end_date">End Date</label>
          <input type="date" name="end_date" id="end_date" min="<?= date('Y-m-d') ?>">
        </div>
        <div class="form-group">
          <label for="reason">Reason (Optional)</label>
          <input type="text" name="reason" id="reason" placeholder="e.g., Personal leave">
        </div>
        <button type="submit" name="block" class="btn-block">Block Date(s)</button>
      </div>
    </form>

    <div class="blocked-list">
      <h3 class="section-title">Currently Blocked Dates</h3>
      <?php if (empty($blockedSlots)): ?>
      <p style="color: #888; text-align: center; padding: 20px;">No blocked dates.</p>
      <?php else: ?>
      <table class="blocked-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Doctor</th>
            <th>Reason</th>
            <th>Blocked By</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($blockedSlots as $block): ?>
          <tr>
            <td><?= formatDate($block['blocked_date']) ?></td>
            <td><?= $block['doctor_name'] ? htmlspecialchars($block['doctor_name']) : 'All Doctors' ?></td>
            <td><?= htmlspecialchars($block['reason'] ?? '-') ?></td>
            <td><?= htmlspecialchars($block['created_by_name']) ?></td>
            <td>
              <a href="?unblock=<?= $block['id'] ?>" class="btn-unblock" onclick="return confirm('Are you sure you want to unblock this date?');">Unblock</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</main>
</body>
</html>