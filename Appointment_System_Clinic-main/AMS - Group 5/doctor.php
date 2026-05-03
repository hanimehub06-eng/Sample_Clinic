<?php
require_once __DIR__ . '/includes/data.php';
$id = $_GET['id'] ?? 'bongon';
$doc = $doctors[$id] ?? $doctors['bongon'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $doc['full_name'] ?> — Medi Clinic</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<section class="doctor-detail">
  <div class="container">
    <a class="go-back" href="doctors.php">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
      Back to Doctors
    </a>
    <div class="doctor-detail-grid">
      <div class="doctor-photo">
        <img src="<?= $doc['photo'] ?>" alt="<?= $doc['full_name'] ?>">
      </div>
      <div class="doctor-info">
        <h1><?= $doc['full_name'] ?><br><small style="font-size:16px;font-weight:500;color:#555;"><?= $doc['specialty'] ?></small></h1>
        <p class="bio"><?= $doc['bio'] ?></p>
        <div class="schedule-table">
          <div class="table-head">CLINIC SCHEDULE</div>
          <?php foreach ($doc['schedule'] as $day => $hrs): ?>
          <div class="schedule-row">
            <span class="day"><?= $day ?></span>
            <span><?= $hrs ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="book-cta">
          <a class="btn-primary" href="book.php?doctor=<?= $doc['id'] ?>" style="display:inline-block;width:auto;max-width:none;padding:14px 40px;">BOOK AN APPOINTMENT</a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
