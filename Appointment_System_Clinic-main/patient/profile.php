<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('patient');

$user = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;

    if (empty($fullName)) {
        $error = 'Full name is required.';
    } else {
        // Update basic info
        dbUpdate('users', [
            'full_name' => $fullName,
            'phone' => $phone,
            'email_notifications' => $emailNotifications
        ], "id = ?", [$user['id']]);

        // Update password if provided
        if (!empty($currentPassword) || !empty($newPassword)) {
            if (empty($currentPassword)) {
                $error = 'Current password is required to change password.';
            } elseif (!verifyPassword($currentPassword, $user['password'])) {
                $error = 'Current password is incorrect.';
            } elseif (strlen($newPassword) < 6) {
                $error = 'New password must be at least 6 characters.';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'New passwords do not match.';
            } else {
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                dbUpdate('users', ['password' => $hash], "id = ?", [$user['id']]);
                $success = 'Profile and password updated successfully.';
            }
        } else {
            $success = 'Profile updated successfully.';
        }

        // Refresh user data
        $user = getCurrentUser();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Profile — Medi Clinic</title>
  <link rel="stylesheet" href="../style.css">
  <link rel="stylesheet" href="../panel.css">
  <style>
    .profile-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
    }
    @media (max-width: 800px) {
      .profile-grid { grid-template-columns: 1fr; }
    }
    .profile-card {
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
      padding: 12px 14px;
      border: 1.5px solid #d0d9d6;
      border-radius: 8px;
      font-size: 14px;
      font-family: inherit;
    }
    .form-group input:focus {
      outline: none;
      border-color: var(--teal-mid);
      box-shadow: 0 0 0 3px rgba(30,158,138,.12);
    }
    .form-group input:disabled {
      background: #f5f5f5;
      color: #666;
    }
    .form-hint {
      font-size: 12px;
      color: #888;
      margin-top: 4px;
    }

    .checkbox-group {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .checkbox-group input {
      width: 18px;
      height: 18px;
    }
    .checkbox-group label {
      margin-bottom: 0;
      font-weight: 400;
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

    .btn-save {
      padding: 12px 24px;
      background: var(--nav-bg);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 700;
      cursor: pointer;
    }
    .btn-save:hover {
      background: #1b4036;
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/sidebar.php'; ?>

<main class="panel-main">
  <div class="panel-header">
    <div>
      <h1>My Profile</h1>
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

    <?php if ($success): ?>
    <div class="alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="profile.php">
      <div class="profile-grid">
        <div class="profile-card">
          <h3 class="card-title">Personal Information</h3>

          <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
          </div>

          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
            <p class="form-hint">Email cannot be changed</p>
          </div>

          <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
          </div>

          <div class="form-group checkbox-group">
            <input type="checkbox" id="email_notifications" name="email_notifications" value="1" <?= $user['email_notifications'] ? 'checked' : '' ?>>
            <label for="email_notifications">Receive email notifications and reminders</label>
          </div>
        </div>

        <div class="profile-card">
          <h3 class="card-title">Change Password</h3>

          <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password">
          </div>

          <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password">
          </div>

          <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password">
          </div>

          <button type="submit" class="btn-save">Save Changes</button>
        </div>
      </div>
    </form>
  </div>
</main>
</body>
</html>