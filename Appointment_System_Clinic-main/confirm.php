<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Check if user is logged in as patient
if (!isLoggedIn() || getUserRole() !== 'patient') {
    header('Location: login.php?role=patient');
    exit;
}

// Get the last appointment
$appointmentId = $_SESSION['last_appointment'] ?? null;
if (!$appointmentId) {
    header('Location: book.php');
    exit;
}

$appointment = dbFetch("
    SELECT a.*,
        p.full_name as patient_name, p.email as patient_email, p.phone as patient_phone,
        d.full_name as doctor_name, d.specialization
    FROM appointments a
    JOIN users p ON p.id = a.patient_id
    JOIN users d ON d.id = a.doctor_id
    WHERE a.id = ? AND a.patient_id = ?
", [$appointmentId, getUserId()]);

if (!$appointment) {
    header('Location: book.php');
    exit;
}

$clinicAddress = getSetting('clinic_address', '123 Medical Center Drive, City, State 12345');
$clinicPhone = getSetting('clinic_phone', '(555) 123-4567');

// Clear the last appointment from session
unset($_SESSION['last_appointment']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Appointment Confirmed — Medi Clinic</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .thankyou-page {
      padding: 40px 0 60px;
      min-height: calc(100vh - 200px);
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .thankyou-card {
      max-width: 600px;
      margin: 0 auto;
      background: #fff;
      border-radius: 12px;
      padding: 48px 40px;
      box-shadow: 0 4px 20px rgba(0,0,0,.08);
      text-align: center;
    }
    .thankyou-icon {
      width: 80px;
      height: 80px;
      background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 24px;
    }
    .thankyou-icon svg {
      width: 40px;
      height: 40px;
      fill: #fff;
    }
    .thankyou-title {
      font-size: 24px;
      font-weight: 700;
      color: var(--nav-bg);
      margin-bottom: 8px;
    }
    .thankyou-subtitle {
      color: #666;
      font-size: 15px;
      margin-bottom: 32px;
    }

    .ref-box {
      background: #f8faf9;
      border: 2px dashed #d0d9d6;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 24px;
    }
    .ref-label {
      font-size: 12px;
      color: #666;
      text-transform: uppercase;
      letter-spacing: .5px;
      margin-bottom: 4px;
    }
    .ref-number {
      font-size: 28px;
      font-weight: 700;
      color: var(--nav-bg);
      letter-spacing: 2px;
    }

    .booking-details {
      text-align: left;
      background: #f8faf9;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 24px;
    }
    .details-title {
      font-size: 14px;
      font-weight: 700;
      color: #666;
      margin-bottom: 12px;
      text-transform: uppercase;
      letter-spacing: .5px;
    }
    .details-row {
      display: flex;
      justify-content: space-between;
      padding: 8px 0;
      border-bottom: 1px solid #e0e0e0;
    }
    .details-row:last-child {
      border-bottom: none;
    }
    .details-label {
      color: #666;
    }
    .details-value {
      font-weight: 600;
      color: var(--nav-bg);
    }

    .clinic-info {
      font-size: 13px;
      color: #666;
      margin-bottom: 32px;
    }
    .clinic-info p {
      margin: 4px 0;
    }

    .thankyou-actions {
      display: flex;
      gap: 12px;
      justify-content: center;
    }
    .btn-primary {
      padding: 14px 28px;
      background: var(--nav-bg);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      text-decoration: none;
    }
    .btn-primary:hover {
      background: #1b4036;
    }
    .btn-outline {
      padding: 14px 28px;
      background: #fff;
      color: var(--nav-bg);
      border: 2px solid var(--nav-bg);
      border-radius: 8px;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      text-decoration: none;
    }
    .btn-outline:hover {
      background: #f0f8f6;
    }

    .progress-bar {
      display: flex;
      gap: 8px;
      margin-top: 40px;
      max-width: 300px;
      margin-left: auto;
      margin-right: auto;
    }
    .progress-seg {
      flex: 1;
      height: 4px;
      background: var(--nav-bg);
      border-radius: 2px;
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="thankyou-page">
  <div class="thankyou-card">
    <div class="thankyou-icon">
      <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
      </svg>
    </div>
    <h1 class="thankyou-title">Appointment Confirmed!</h1>
    <p class="thankyou-subtitle">A confirmation email has been sent to your email address.</p>

    <div class="ref-box">
      <div class="ref-label">Booking Reference Number</div>
      <div class="ref-number"><?= htmlspecialchars($appointment['reference_no']) ?></div>
    </div>

    <div class="booking-details">
      <div class="details-title">Appointment Details</div>
      <div class="details-row">
        <span class="details-label">Doctor</span>
        <span class="details-value"><?= htmlspecialchars($appointment['doctor_name']) ?></span>
      </div>
      <div class="details-row">
        <span class="details-label">Specialization</span>
        <span class="details-value"><?= htmlspecialchars($appointment['specialization']) ?></span>
      </div>
      <div class="details-row">
        <span class="details-label">Date</span>
        <span class="details-value"><?= formatDate($appointment['appointment_date']) ?></span>
      </div>
      <div class="details-row">
        <span class="details-label">Time</span>
        <span class="details-value"><?= htmlspecialchars($appointment['time_slot']) ?></span>
      </div>
      <div class="details-row">
        <span class="details-label">Reason</span>
        <span class="details-value"><?= htmlspecialchars($appointment['reason']) ?></span>
      </div>
    </div>

    <div class="clinic-info">
      <p><strong>Clinic Address:</strong> <?= htmlspecialchars($clinicAddress) ?></p>
      <p><strong>Phone:</strong> <?= htmlspecialchars($clinicPhone) ?></p>
      <p style="margin-top: 12px; color: #e67e22;">Please arrive 15 minutes before your scheduled time.</p>
    </div>

    <div class="thankyou-actions">
      <a href="patient/appointments.php" class="btn-primary">View My Appointments</a>
      <a href="index.php" class="btn-outline">Back to Home</a>
    </div>

    <div class="progress-bar">
      <div class="progress-seg active"></div>
      <div class="progress-seg active"></div>
      <div class="progress-seg active"></div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>