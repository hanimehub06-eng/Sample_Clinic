<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';
$role = $_POST['role'] ?? $_GET['role'] ?? 'clerk';

if (isLoggedIn()) {
    switch (getUserRole()) {
        case 'clerk':
            header('Location: clerk/index.php');
            break;
        case 'doctor':
            header('Location: doctor/index.php');
            break;
        case 'patient':
            header('Location: patient/index.php');
            break;
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = $_POST['role'] ?? 'clerk';

    // Handle patient login with email
    if ($role === 'patient') {
        $user = dbFetch("SELECT * FROM users WHERE email = ? AND role = 'patient'", [$username]);
    } else {
        $user = dbFetch("SELECT * FROM users WHERE username = ? AND role = ?", [$username, $role]);
    }

    if ($user && verifyPassword($password, $user['password'])) {
        login($user);
    } else {
        $error = 'Invalid username or password. Please try again.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login — Medi Clinic</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .login-page {
      min-height: 100vh;
      background: url('imgs/hospital.png') center/cover no-repeat;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
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

    .role-tabs {
      display: flex;
      border: 2px solid var(--nav-bg);
      border-radius: 8px;
      overflow: hidden;
      margin-bottom: 26px;
    }
    .role-tab {
      flex: 1;
      padding: 10px 0;
      text-align: center;
      font-size: 14px;
      font-weight: 700;
      letter-spacing: .4px;
      cursor: pointer;
      color: var(--nav-bg);
      background: #fff;
      border: none;
      transition: background .18s, color .18s;
      text-decoration: none;
    }
    .role-tab.active {
      background: var(--nav-bg);
      color: #fff;
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

    .patient-login-note {
      font-size: 12px;
      color: #666;
      background: #f8faf9;
      padding: 10px 14px;
      border-radius: 8px;
      margin-bottom: 18px;
      text-align: center;
    }
    .patient-login-note a {
      color: var(--teal-mid);
      font-weight: 600;
    }
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
        <div class="login-title">Medi Clinic</div>
        <div class="login-subtitle">Staff Portal — Sign in to continue</div>
      </div>

      <div class="role-tabs">
        <a href="?role=clerk" class="role-tab<?= $role==='clerk' ?' active':'' ?>">Clerk</a>
        <a href="?role=doctor" class="role-tab<?= $role==='doctor'?' active':'' ?>">Doctor</a>
        <a href="?role=patient" class="role-tab<?= $role==='patient'?' active':'' ?>">Patient</a>
      </div>

      <?php if ($error): ?>
      <div class="login-error">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <?php if ($role === 'patient'): ?>
      <div class="patient-login-note">
        For patient login, use your registered email address.
        <br>New patient? <a href="register.php">Register here</a>
      </div>
      <?php endif; ?>

      <form method="POST" action="login.php">
        <input type="hidden" name="role" value="<?= htmlspecialchars($role) ?>">

        <div class="login-field">
          <label for="username"><?= $role === 'patient' ? 'Email' : 'Username' ?></label>
          <input type="text" id="username" name="username"
                 placeholder="<?= $role === 'patient' ? 'patient@clinic.com' : ($role === 'clerk' ? 'clerk' : 'doctor') ?>"
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                 autocomplete="username" required>
          <div class="login-hint">
            <?php if ($role === 'clerk'): ?>
              Demo: "clerk"
            <?php elseif ($role === 'doctor'): ?>
              Demo: "doctor"
            <?php else: ?>
              Demo: "patient@clinic.com"
            <?php endif; ?>
          </div>
        </div>

        <div class="login-field">
          <label for="password">Password</label>
          <input type="password" id="password" name="password"
                 placeholder="••••••••"
                 autocomplete="current-password" required>
          <div class="login-hint">
            <?php if ($role === 'patient'): ?>
              Demo: "patient123"
            <?php elseif ($role === 'clerk'): ?>
              Demo: "clerk123"
            <?php else: ?>
              Demo: "doctor123"
            <?php endif; ?>
          </div>
        </div>

        <button type="submit" class="btn-login">
          Sign in as <?= ucfirst($role) ?>
        </button>
      </form>

      <div class="login-back">
        <a href="index.php">← Back to patient portal</a>
      </div>
    </div>
  </div>
</body>
</html>