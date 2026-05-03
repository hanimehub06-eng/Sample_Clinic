<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Check for required parameters
$doctorId = $_GET['doctor'] ?? null;
$date = $_GET['date'] ?? null;
$slot = $_GET['slot'] ?? null;

if (!$doctorId || !$date || !$slot) {
    header('Location: book.php');
    exit;
}

$doctor = getDoctorById($doctorId);
if (!$doctor) {
    header('Location: book.php');
    exit;
}

// Check if user is logged in as patient
if (!isLoggedIn() || getUserRole() !== 'patient') {
    $_SESSION['booking_redirect'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php?role=patient');
    exit;
}

$error = '';
$patient = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = trim($_POST['reason'] ?? '');

    if (empty($reason)) {
        $error = 'Please provide a reason for your visit.';
    } else {
        // Create appointment
        $result = createAppointment($patient['id'], $doctorId, $date, $slot, $reason);

        if ($result['success']) {
            // Send confirmation email
            sendAppointmentConfirmation($result['id']);

            // Store reference for confirmation page
            $_SESSION['last_appointment'] = $result['id'];

            header('Location: confirm.php');
            exit;
        } else {
            $error = $result['error'] ?? 'Failed to create appointment. Please try again.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Appointment Details — Medi Clinic</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .form-page {
      padding: 40px 0 60px;
      min-height: calc(100vh - 200px);
    }
    .form-card {
      max-width: 700px;
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

    .booking-summary {
      background: #f8faf9;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 24px;
    }
    .summary-title {
      font-size: 14px;
      font-weight: 700;
      color: #666;
      margin-bottom: 12px;
      text-transform: uppercase;
      letter-spacing: .5px;
    }
    .summary-row {
      display: flex;
      justify-content: space-between;
      padding: 8px 0;
      border-bottom: 1px solid #e0e0e0;
    }
    .summary-row:last-child {
      border-bottom: none;
    }
    .summary-label {
      color: #666;
    }
    .summary-value {
      font-weight: 600;
      color: var(--nav-bg);
    }

    .form-group {
      margin-bottom: 20px;
    }
    .form-group label {
      display: block;
      font-size: 14px;
      font-weight: 600;
      color: #444;
      margin-bottom: 8px;
    }
    .form-group textarea {
      width: 100%;
      padding: 14px;
      border: 1.5px solid #d0d9d6;
      border-radius: 8px;
      font-size: 14px;
      font-family: inherit;
      resize: vertical;
      min-height: 120px;
    }
    .form-group textarea:focus {
      outline: none;
      border-color: var(--teal-mid);
      box-shadow: 0 0 0 3px rgba(30,158,138,.12);
    }

    .form-error {
      background: #fff0f0;
      border: 1.5px solid #f5a0a0;
      color: #c0392b;
      border-radius: 8px;
      padding: 10px 14px;
      font-size: 13px;
      margin-bottom: 18px;
    }

    .form-actions {
      display: flex;
      gap: 12px;
    }
    .btn-primary {
      flex: 1;
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
    }
    .btn-primary:hover {
      background: #1b4036;
    }
    .btn-secondary {
      padding: 14px 24px;
      background: #f5f5f5;
      color: #666;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
    }
    .btn-secondary:hover {
      background: #e0e0e0;
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
  </style>
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="form-page">
  <div class="form-card">
    <a class="go-back" href="book.php?doctor=<?= $doctorId ?>&date=<?= $date ?>&slot=<?= urlencode($slot) ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
      Back
    </a>
    <p class="form-title">STEP 2 — APPOINTMENT DETAILS</p>

    <div class="booking-summary">
      <div class="summary-title">Booking Summary</div>
      <div class="summary-row">
        <span class="summary-label">Doctor</span>
        <span class="summary-value"><?= htmlspecialchars($doctor['full_name']) ?></span>
      </div>
      <div class="summary-row">
        <span class="summary-label">Specialization</span>
        <span class="summary-value"><?= htmlspecialchars($doctor['specialization']) ?></span>
      </div>
      <div class="summary-row">
        <span class="summary-label">Date</span>
        <span class="summary-value"><?= formatDate($date) ?></span>
      </div>
      <div class="summary-row">
        <span class="summary-label">Time</span>
        <span class="summary-value"><?= htmlspecialchars($slot) ?></span>
      </div>
      <div class="summary-row">
        <span class="summary-label">Patient</span>
        <span class="summary-value"><?= htmlspecialchars($patient['full_name']) ?></span>
      </div>
    </div>

    <?php if ($error): ?>
    <div class="form-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="details.php?doctor=<?= $doctorId ?>&date=<?= $date ?>&slot=<?= urlencode($slot) ?>">
      <div class="form-group">
        <label for="reason">Reason for Visit *</label>
        <textarea id="reason" name="reason" placeholder="Please describe your symptoms or reason for the appointment..."
                  required><?= htmlspecialchars($_POST['reason'] ?? '') ?></textarea>
      </div>

      <div class="form-actions">
        <a href="book.php?doctor=<?= $doctorId ?>" class="btn-secondary">Cancel</a>
        <button type="submit" class="btn-primary">CONFIRM BOOKING</button>
      </div>
    </form>

    <div class="progress-bar">
      <div class="progress-seg active"></div>
      <div class="progress-seg active"></div>
      <div class="progress-seg"></div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>