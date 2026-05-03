<?php
$clinicName = getSetting('clinic_name', 'Medi Clinic');
$clinicAddress = getSetting('clinic_address', '123 Medical Center Drive');
$clinicPhone = getSetting('clinic_phone', '(555) 123-4567');
$clinicEmail = getSetting('clinic_email', 'info@mediclinic.com');
?>
<footer class="site-footer">
  <div class="container footer-grid">
    <div class="footer-brand">
      <h3><?= htmlspecialchars($clinicName) ?></h3>
      <p><?= htmlspecialchars($clinicAddress) ?></p>
    </div>
    <div class="footer-col">
      <h4>Quick Links</h4>
      <ul>
        <li><a href="index.php#about">ABOUT</a></li>
        <li><a href="index.php#hours">CLINIC HOURS</a></li>
        <li><a href="doctors.php">DOCTORS</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Contact Us</h4>
      <p><?= htmlspecialchars($clinicPhone) ?></p>
      <p><?= htmlspecialchars($clinicEmail) ?></p>
    </div>
    <div class="footer-col">
      <h4>Clinic Hours</h4>
      <div class="footer-hours">
        <div><span>Mon - Fri</span><span>8am - 5pm</span></div>
        <div><span>Sat - Sun</span><span>Closed</span></div>
      </div>
    </div>
  </div>
</footer>