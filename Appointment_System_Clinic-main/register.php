<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';
$success = false;

if (isLoggedIn()) {
    header('Location: patient/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($username) || empty($email) || empty($password) || empty($fullName)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif (userExists($email) || userExists($username)) {
        $error = 'Email or username already exists.';
    } else {
        // Create username from email if not provided
        if (empty($username)) {
            $username = explode('@', $email)[0];
        }

        $userId = registerUser($username, $email, $password, $fullName, 'patient', $phone);

        if ($userId) {
            $success = true;
        } else {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register — Medi Clinic</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .login-page {
      min-height: 100vh;
      background: url('imgs/hospital.png') center/cover no-repeat;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      padding: 40px 0;
    }
    .login-page::before {
      content: '';
      position: absolute;
      inset: 0;
      background: rgba(0,0,0,.50);
    }
    .login-card {
      position: relative;
      z-index: 1;
      background: #fff;
      border-radius: 16px;
      width: 420px;
      max-width: 94vw;
      padding: 40px 40px 36px;
      box-shadow: 0 12px 48px rgba(0,0,0,.28);
    }
    .login-logo {
      text-align: center;
      margin-bottom: 6px;
    }
    .login-logo-icon {
      width: 56px;
      height: 56px;
      background: var(--nav-bg);
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 10px;
    }
    .login-logo-icon svg { width: 28px; height: 28px; fill: #fff; }
    .login-title {
      font-size: 22px;
      font-weight: 800;
      color: var(--nav-bg);
      text-align: center;
      margin-bottom: 4px;
      letter-spacing: .3px;
    }
    .login-subtitle {
      text-align: center;
      color: #777;
      font-size: 13px;
      margin-bottom: 28px;
    }

    .login-field {
      margin-bottom: 18px;
    }
    .login-field label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: #444;
      margin-bottom: 6px;
    }
    .login-field input {
      width: 100%;
      padding: 11px 14px;
      border: 1.5px solid #d0d9d6;
      border-radius: 8px;
      font-size: 14px;
      font-family: inherit;
      color: #222;
      background: #f8faf9;
      outline: none;
      transition: border-color .18s, box-shadow .18s;
    }
    .login-field input:focus {
      border-color: var(--teal-mid);
      background: #fff;
      box-shadow: 0 0 0 3px rgba(30,158,138,.12);
    }

    .login-hint {
      font-size: 11.5px;
      color: #aaa;
      margin-top: 5px;
    }

    .login-error {
      background: #fff0f0;
      border: 1.5px solid #f5a0a0;
      color: #c0392b;
      border-radius: 8px;
      padding: 10px 14px;
      font-size: 13px;
      margin-bottom: 18px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .login-error svg { flex-shrink: 0; }

    .login-success {
      background: #f0fff4;
      border: 1.5px solid #90ee90;
      color: #27ae60;
      border-radius: 8px;
      padding: 10px 14px;
      font-size: 13px;
      margin-bottom: 18px;
      text-align: center;
    }

    .btn-login {
      width: 100%;
      padding: 13px 0;
      background: var(--nav-bg);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      font-weight: 700;
      letter-spacing: .5px;
      cursor: pointer;
      transition: background .18s, transform .12s;
      font-family: inherit;
      margin-top: 4px;
    }
    .btn-login:hover { background: #1b4036; }
    .btn-login:active { transform: scale(.98); }

    .login-back {
      text-align: center;
      margin-top: 20px;
      font-size: 13px;
      color: #888;
    }
    .login-back a {
      color: var(--teal-mid);
      font-weight: 600;
    }
    .login-back a:hover { text-decoration: underline; }

    .login-topbar {
      background: var(--nav-bg);
      height: 56px;
      display: flex;
      align-items: center;
      padding: 0 28px;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 200;
      box-shadow: 0 2px 8px rgba(0,0,0,.2);
    }
    .login-topbar-brand {
      color: #fff;
      font-size: 19px;
      font-weight: 800;
      letter-spacing: .3px;
    }
    body { padding-top: 56px; }
  </style>
</head>
<body>
  <div class="login-topbar">
    <a href="index.php" class="login-topbar-brand">Medi Clinic</a>
  </div>

  <div class="login-page">
    <div class="login-card">
      <div class="login-logo">
        <div class="login-logo-icon">
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
          </svg>
        </div>
        <div class="login-title">Patient Registration</div>
        <div class="login-subtitle">Create an account to book appointments</div>
      </div>

      <?php if ($error): ?>
      <div class="login-error">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <?php if ($success): ?>
      <div class="login-success">
        Registration successful! <a href="login.php?role=patient">Click here to login</a>
      </div>
      <?php endif; ?>

      <form method="POST" action="register.php">
        <div class="login-field">
          <label for="full_name">Full Name</label>
          <input type="text" id="full_name" name="full_name"
                 placeholder="John Doe"
                 value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                 required>
        </div>

        <div class="login-field">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email"
                 placeholder="john@example.com"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                 required>
        </div>

        <div class="login-field">
          <label for="phone">Phone Number (Optional)</label>
          <input type="text" id="phone" name="phone"
                 placeholder="(555) 123-4567"
                 value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
        </div>

        <div class="login-field">
          <label for="password">Password</label>
          <input type="password" id="password" name="password"
                 placeholder="••••••••"
                 autocomplete="new-password" required>
          <div class="login-hint">At least 6 characters</div>
        </div>

        <div class="login-field">
          <label for="confirm_password">Confirm Password</label>
          <input type="password" id="confirm_password" name="confirm_password"
                 placeholder="••••••••"
                 autocomplete="new-password" required>
        </div>

        <button type="submit" class="btn-login">
          Register
        </button>
      </form>

      <div class="login-back">
        Already have an account? <a href="login.php?role=patient">Login here</a>
      </div>
    </div>
  </div>
</body>
</html>