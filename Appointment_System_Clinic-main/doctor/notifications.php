<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('doctor');

$user = getCurrentUser();

// Mark notification as read
if (isset($_GET['read'])) {
    markNotificationRead($_GET['read'], $user['id']);
    header('Location: notifications.php');
    exit;
}

$notifications = getNotifications($user['id']);
$unreadCount = getUnreadNotificationCount($user['id']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Notifications — Medi Clinic</title>
  <link rel="stylesheet" href="../style.css">
  <link rel="stylesheet" href="../panel.css">
  <style>
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
    }
    .page-title {
      font-size: 24px;
      font-weight: 700;
      color: var(--nav-bg);
    }

    .notifications-list {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .notification-item {
      background: #fff;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,.06);
      display: flex;
      gap: 16px;
      align-items: flex-start;
    }
    .notification-item.unread {
      border-left: 4px solid var(--teal-mid);
    }
    .notification-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      background: #e8f8f5;
    }
    .notification-icon svg {
      width: 20px;
      height: 20px;
      fill: var(--teal-mid);
    }

    .notification-content {
      flex: 1;
    }
    .notification-title {
      font-size: 15px;
      font-weight: 700;
      color: var(--nav-bg);
      margin-bottom: 4px;
    }
    .notification-message {
      font-size: 14px;
      color: #666;
      line-height: 1.5;
    }
    .notification-time {
      font-size: 12px;
      color: #999;
      margin-top: 8px;
    }
    .notification-actions {
      margin-top: 8px;
    }
    .notification-actions a {
      font-size: 13px;
      color: var(--teal-mid);
      text-decoration: none;
    }
    .notification-actions a:hover {
      text-decoration: underline;
    }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #888;
    }
    .empty-state svg {
      width: 64px;
      height: 64px;
      fill: #ccc;
      margin-bottom: 16px;
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/sidebar.php'; ?>

<main class="panel-main">
  <div class="panel-header">
    <div>
      <h1>Notifications</h1>
    </div>
    <div class="panel-user">
      <span><?= htmlspecialchars($user['full_name']) ?></span>
      <a href="../logout.php" class="btn-logout">Logout</a>
    </div>
  </div>

  <div class="panel-content">
    <div class="page-header">
      <h2 class="page-title">All Notifications</h2>
      <?php if ($unreadCount > 0): ?>
      <span style="background: #e67e22; color: #fff; padding: 4px 12px; border-radius: 12px; font-size: 13px; font-weight: 600;">
        <?= $unreadCount ?> unread
      </span>
      <?php endif; ?>
    </div>

    <?php if (empty($notifications)): ?>
    <div class="empty-state">
      <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
      <h3>No notifications</h3>
      <p>You're all caught up!</p>
    </div>
    <?php else: ?>
    <div class="notifications-list">
      <?php foreach ($notifications as $notif): ?>
      <div class="notification-item<?= !$notif['is_read'] ? ' unread' : '' ?>">
        <div class="notification-icon">
          <svg viewBox="0 0 24 24">
            <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
          </svg>
        </div>
        <div class="notification-content">
          <div class="notification-title"><?= htmlspecialchars($notif['title']) ?></div>
          <div class="notification-message"><?= htmlspecialchars($notif['message']) ?></div>
          <div class="notification-time"><?= date('F j, Y g:i A', strtotime($notif['created_at'])) ?></div>
          <?php if (!$notif['is_read']): ?>
          <div class="notification-actions">
            <a href="?read=<?= $notif['id'] ?>">Mark as read</a>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</main>
</body>
</html>