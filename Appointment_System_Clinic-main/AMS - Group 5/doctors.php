<?php require_once __DIR__ . '/includes/data.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Our Doctors — Medi Clinic</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<section class="page-section">
  <div class="container">
    <h1 class="page-title">OUR DOCTORS</h1>
    <div class="doctors-grid">
      <?php foreach ($doctors as $d): ?>
      <a class="doctor-card" href="doctor.php?id=<?= $d['id'] ?>">
        <img src="<?= $d['photo'] ?>" alt="<?= $d['full_name'] ?>">
        <div class="doctor-card-label"><?= $d['full_name'] ?></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
