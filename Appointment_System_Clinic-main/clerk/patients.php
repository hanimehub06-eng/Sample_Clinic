<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('clerk');

$user = getCurrentUser();
$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM users WHERE role = 'patient'";
$params = [];

if ($search) {
    $sql .= " AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
}

$sql .= " ORDER BY created_at DESC";
$patients = dbFetchAll($sql, $params);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Patients — Medi Clinic</title>
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

    .search-bar {
      background: #fff;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,.06);
      margin-bottom: 24px;
    }
    .search-bar form {
      display: flex;
      gap: 12px;
    }
    .search-bar input {
      flex: 1;
      padding: 10px 14px;
      border: 1.5px solid #d0d9d6;
      border-radius: 6px;
      font-size: 14px;
    }
    .search-bar button {
      padding: 10px 24px;
      background: var(--nav-bg);
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
    }

    .patients-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 16px;
    }
    .patient-card {
      background: #fff;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,.06);
    }
    .patient-name {
      font-size: 16px;
      font-weight: 700;
      color: var(--nav-bg);
      margin-bottom: 8px;
    }
    .patient-detail {
      font-size: 13px;
      color: #666;
      margin-bottom: 4px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .patient-detail svg {
      width: 14px;
      height: 14px;
      fill: #999;
    }
    .patient-stats {
      margin-top: 12px;
      padding-top: 12px;
      border-top: 1px solid #f0f0f0;
      display: flex;
      gap: 16px;
      font-size: 12px;
    }
    .patient-stat {
      color: #666;
    }
    .patient-stat strong {
      color: var(--nav-bg);
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/sidebar.php'; ?>

<main class="panel-main">
  <div class="panel-header">
    <div>
      <h1>Patients</h1>
    </div>
    <div class="panel-user">
      <span><?= htmlspecialchars($user['full_name']) ?></span>
      <a href="../logout.php" class="btn-logout">Logout</a>
    </div>
  </div>

  <div class="panel-content">
    <div class="search-bar">
      <form method="GET" action="patients.php">
        <input type="text" name="search" placeholder="Search by name, email, or phone..."
               value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
      </form>
    </div>

    <?php if (empty($patients)): ?>
    <p style="text-align: center; padding: 40px; color: #888;">No patients found.</p>
    <?php else: ?>
    <div class="patients-grid">
      <?php foreach ($patients as $patient):
        // Get appointment counts
        $totalAppts = dbFetch("SELECT COUNT(*) as cnt FROM appointments WHERE patient_id = ?", [$patient['id']]);
        $upcomingAppts = dbFetch("SELECT COUNT(*) as cnt FROM appointments WHERE patient_id = ? AND appointment_date >= ? AND status != 'cancelled'", [$patient['id'], date('Y-m-d')]);
      ?>
      <div class="patient-card">
        <div class="patient-name"><?= htmlspecialchars($patient['full_name']) ?></div>
        <div class="patient-detail">
          <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          <?= htmlspecialchars($patient['email']) ?>
        </div>
        <div class="patient-detail">
          <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.8 19.79 19.79 0 01.92 2.18 2 2 0 012.91 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L7.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
          <?= htmlspecialchars($patient['phone'] ?? 'Not provided') ?>
        </div>
        <div class="patient-stats">
          <div class="patient-stat">Total: <strong><?= $totalAppts['cnt'] ?? 0 ?></strong></div>
          <div class="patient-stat">Upcoming: <strong><?= $upcomingAppts['cnt'] ?? 0 ?></strong></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</main>
</body>
</html>