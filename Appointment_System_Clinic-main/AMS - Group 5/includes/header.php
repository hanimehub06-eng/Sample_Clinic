<?php $current = basename($_SERVER['PHP_SELF']); ?>
<header class="site-header">
  <div class="container nav-container">
    <a class="brand" href="index.php">Medi Clinic</a>
    <nav class="nav">
      <a class="nav-link" href="index.php#about">ABOUT</a>
      <a class="nav-link<?= $current === 'doctors.php' ? ' active' : '' ?>" href="doctors.php">DOCTORS</a>
      <a class="nav-link" href="index.php#hours">CLINIC HOURS</a>
      <a class="btn-book" href="book.php">BOOK AN APPOINTMENT</a>
      <a class="nav-link" href="login.php" style="font-size:12px;opacity:.75;">STAFF LOGIN</a>
    </nav>
  </div>
</header>
