<?php
$cur = basename($_SERVER['PHP_SELF']);
$base = '../';
?>
<aside class="sidebar" id="sidebar">
  <button class="sidebar-toggle" onclick="toggleSidebar()">
    <span class="hamburger">
      <span></span><span></span><span></span>
    </span>
    <span class="menu-label">MENU</span>
  </button>

  <nav class="sidebar-nav">
    <a href="index.php" class="nav-item <?= in_array($cur,['index.php']) ? 'active' : '' ?>">
      <span class="nav-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      </span>
      <span class="nav-label">HOME</span>
      <span class="nav-badge">1</span>
    </a>

    <a href="patients.php" class="nav-item <?= in_array($cur,['patients.php','patients-slots.php','appointment.php']) ? 'active' : '' ?>">
      <span class="nav-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </span>
      <span class="nav-label">APPOINTMENTS</span>
    </a>

    <a href="schedules.php" class="nav-item <?= in_array($cur,['schedules.php','schedule-edit.php']) ? 'active' : '' ?>">
      <span class="nav-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      </span>
      <span class="nav-label">SCHEDULES</span>
    </a>
  </nav>

  <div class="sidebar-footer">
    <a href="<?= $base ?>login.php" class="nav-item">
      <span class="nav-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      </span>
      <span class="nav-label">LOGOUT</span>
    </a>
  </div>
</aside>

<script>
(function(){
  var sidebar = document.getElementById('sidebar');
  var main = document.querySelector('.panel-main');
  var collapsed = localStorage.getItem('sidebarCollapsed') === '1';
  if (collapsed) {
    sidebar.classList.add('collapsed');
    if (main) main.style.marginLeft = 'var(--sidebar-collapsed)';
  }
})();

function toggleSidebar() {
  var sidebar = document.getElementById('sidebar');
  var main = document.querySelector('.panel-main');
  sidebar.classList.toggle('collapsed');
  var isCollapsed = sidebar.classList.contains('collapsed');
  localStorage.setItem('sidebarCollapsed', isCollapsed ? '1' : '0');
  if (main) {
    main.style.marginLeft = isCollapsed ? 'var(--sidebar-collapsed)' : 'var(--sidebar-width)';
  }
}
</script>
