<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('patient');

$user = getCurrentUser();

// Mark notification as read
if (isset($_GET['read'])) {
    markNotificationRead($_GET['read'], $user['id']);
    header('Location: notifications.php');
    exit;
}

// Mark all as read
if (isset($_GET['mark_all_read'])) {
    dbUpdate('notifications', ['is_read' => 1], "user_id = ? AND is_read = FALSE", [$user['id']]);
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
    .btn-mark-all {
      padding: 8px 16px;
      background: #f5f5f5;
      border: none;
      border-radius: 6px;
      font-size: 13px;
      font-weight: 600;
      color: #666;
      cursor: pointer;
      text-decoration: none;
    }
    .btn-mark-all:hover {
      background: #e0e0e0;
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
    }
    .notif-icon-confirmation { background: #e8f8f5; }
    .notif-icon-confirmation svg { fill: #27ae60; }
    .notif-icon-reminder { background: #fef5ec; }
    .notif-icon-reminder svg { fill: #e67e22; }
    .notif-icon-cancellation { background: #fce8e8; }
    .notif-icon-cancellation svg { fill: #c0392b; }
    .notif-icon-default { background: #f0f4f8; }
    .notif-icon-default svg { fill: #2c3e50; }

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
      <a href="?mark_all_read=1" class="btn-mark-all">Mark all as read</a>
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
        <div class="notification-icon <?= 'notif-icon-' . $notif['type'] ?>">
          <svg viewBox="0 0 24 24" width="20" height="20">
            <?php if ($notif['type'] === 'confirmation'): ?>
            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
            <?php elseif ($notif['type'] === 'reminder'): ?>
            <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
            <?php elseif ($notif['type'] === 'cancellation'): ?>
            <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
            <?php else: ?>
            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/>
            <?php endif; ?>
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