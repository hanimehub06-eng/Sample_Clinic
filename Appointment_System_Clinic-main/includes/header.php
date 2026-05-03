<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

$current = basename($_SERVER['PHP_SELF']);
$user = getCurrentUser();
?>
<header class="site-header">
  <div class="container nav-container">
    <a class="brand" href="index.php">Medi Clinic</a>
    <nav class="nav">
      <a class="nav-link" href="index.php#about">ABOUT</a>
      <a class="nav-link<?= $current === 'doctors.php' ? ' active' : '' ?>" href="doctors.php">DOCTORS</a>
      <a class="nav-link" href="index.php#hours">CLINIC HOURS</a>
      <a class="btn-book" href="book.php">BOOK AN APPOINTMENT</a>
      <?php if (isLoggedIn()): ?>
        <?php if (getUserRole() === 'patient'): ?>
          <a class="nav-link" href="patient/">MY DASHBOARD</a>
        <?php elseif (getUserRole() === 'clerk'): ?>
          <a class="nav-link" href="clerk/">DASHBOARD</a>
        <?php elseif (getUserRole() === 'doctor'): ?>
          <a class="nav-link" href="doctor/">DASHBOARD</a>
        <?php endif; ?>
        <a class="nav-link" href="logout.php" style="font-size:12px;opacity:.75;">LOGOUT (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
      <?php else: ?>
        <a class="nav-link" href="login.php" style="font-size:12px;opacity:.75;">STAFF LOGIN</a>
      <?php endif; ?>
    </nav>
  </div>
</header>