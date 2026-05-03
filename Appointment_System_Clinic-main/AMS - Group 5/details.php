<?php require_once __DIR__ . '/includes/data.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Your Details — Medi Clinic</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="form-page">
  <div class="form-card">
    <a class="go-back" href="book.php">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
      Back
    </a>
    <p class="form-title">STEP 2 — YOUR DETAILS</p>

    <form action="confirm.php" method="GET">
      <div class="field">
        <label>Last Name <span class="req">*</span></label>
        <input class="input" type="text" name="last_name" required placeholder="e.g. Dela Cruz">
      </div>
      <div class="field">
        <label>Given Name <span class="req">*</span></label>
        <input class="input" type="text" name="given_name" required placeholder="e.g. Juan">
      </div>
      <div class="field">
        <label>Middle Name</label>
        <input class="input" type="text" name="middle_name" placeholder="e.g. Santos">
      </div>
      <div class="dob-age-row">
        <div class="dob-group">
          <label>Date of Birth <span class="req">*</span></label>
          <div class="dob-inputs">
            <input class="input" type="text" name="dob_month" placeholder="Month" required>
            <input class="input" type="text" name="dob_day" placeholder="Day" required>
            <input class="input" type="text" name="dob_year" placeholder="Year" required>
          </div>
        </div>
        <div class="age-group">
          <label>Age <span class="req">*</span></label>
          <input class="input" type="number" name="age" required min="1" max="120">
        </div>
      </div>
      <div class="field">
        <label>Sex <span class="req">*</span></label>
        <div class="sex-row">
          <label class="sex-option"><input type="radio" name="sex" value="Male"><span class="sex-btn">Male</span></label>
          <label class="sex-option"><input type="radio" name="sex" value="Female"><span class="sex-btn">Female</span></label>
        </div>
      </div>
      <div class="field">
        <label>Email <span class="req">*</span></label>
        <input class="input" type="email" name="email" required placeholder="e.g. juan@email.com">
      </div>
      <div class="field">
        <label>Phone Number <span class="req">*</span></label>
        <input class="input" type="tel" name="phone" required placeholder="e.g. 09123456789">
      </div>
      <div class="field">
        <label>Reason for Visit</label>
        <textarea class="input" name="reason" placeholder="Briefly describe your symptoms or concern..."></textarea>
      </div>

      <button type="submit" class="btn-primary">NEXT</button>

      <div class="progress-bar">
        <div class="progress-seg done"></div>
        <div class="progress-seg active"></div>
        <div class="progress-seg"></div>
      </div>
    </form>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
