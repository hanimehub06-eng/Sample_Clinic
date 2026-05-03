<?php require_once __DIR__ . '/includes/data.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Confirm Appointment — Medi Clinic</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="form-page">
  <div class="form-card">
    <p class="form-title">STEP 3 — CONFIRM</p>
    <div class="confirm-box">
      <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#2e9880" stroke-width="1.5"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.8 19.79 19.79 0 01.92 2.18 2 2 0 012.91 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L7.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
      <h2>Confirm your booking</h2>
      <p class="desc">We will send a verification code to your email address to confirm your appointment. Please enter the 6-digit code below.</p>
      <div class="code-row">
        <input class="code-box" type="text" maxlength="1" id="c1" oninput="nextField(this,'c2')">
        <input class="code-box" type="text" maxlength="1" id="c2" oninput="nextField(this,'c3')">
        <input class="code-box" type="text" maxlength="1" id="c3" oninput="nextField(this,'c4')">
        <input class="code-box" type="text" maxlength="1" id="c4" oninput="nextField(this,'c5')">
        <input class="code-box" type="text" maxlength="1" id="c5" oninput="nextField(this,'c6')">
        <input class="code-box" type="text" maxlength="1" id="c6">
      </div>
      <span class="resend-link">Didn't get the code? <a href="#">Resend</a></span>
      <a href="thankyou.php" class="btn-primary">CONFIRM</a>
    </div>
    <div class="progress-bar">
      <div class="progress-seg done"></div>
      <div class="progress-seg done"></div>
      <div class="progress-seg active"></div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
function nextField(el, nextId) {
  if (el.value.length >= 1) {
    var next = document.getElementById(nextId);
    if (next) next.focus();
  }
}
</script>
</body>
</html>
