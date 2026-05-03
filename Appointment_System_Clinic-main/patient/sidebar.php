<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$current = basename($_SERVER['PHP_SELF']);
$user = getCurrentUser();
$unread = getUnreadNotificationCount($user['id']);
?>
<aside class="panel-sidebar">
  <div class="sidebar-brand">
    <a href="../index.php">Medi Clinic</a>
  </div>

  <nav class="sidebar-nav">
    <a href="index.php" class="nav-item<?= $current === 'index.php' ? ' active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      Dashboard
    </a>
    <a href="appointments.php" class="nav-item<?= $current === 'appointments.php' ? ' active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      My Appointments
    </a>
    <a href="profile.php" class="nav-item<?= $current === 'profile.php' ? ' active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      My Profile
    </a>
    <a href="notifications.php" class="nav-item<?= $current === 'notifications.php' ? ' active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
      Notifications
      <?php if ($unread > 0): ?>
      <span class="nav-badge"><?= $unread ?></span>
      <?php endif; ?>
    </a>
  </nav>

  <div class="sidebar-footer">
    <a href="../book.php" class="btn-book-now">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Book Appointment
    </a>
  </div>
</aside>