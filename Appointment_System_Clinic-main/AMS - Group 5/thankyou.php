<?php require_once __DIR__ . '/includes/data.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Thank You — Medi Clinic</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="thankyou-wrap">
  <div class="thankyou-box">
    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#2e9880" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    <h1>Appointment Booked!</h1>
    <p>Your appointment has been successfully submitted. You will receive a confirmation shortly. Please arrive 10 minutes before your scheduled time.</p>
    <a class="btn-home" href="index.php">BACK TO HOME</a>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
