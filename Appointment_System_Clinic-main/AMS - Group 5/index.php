<?php require_once __DIR__ . '/includes/data.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Medi Clinic — Where care comes first</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<section class="hero">
  <div class="hero-content">
    <h1>WELCOME TO MEDICLINIC</h1>
    <p class="hero-tagline">Where care comes first</p>
    <div class="info-bar">
      <div class="info-item">
        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#2e9880" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.8 19.79 19.79 0 01.92 2.18 2 2 0 012.91 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L7.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
        <span>(02) 1234-4567<br>(Medicare)</span>
      </div>
      <div class="info-item">
        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#2e9880" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
        <span>0912-345-6789<br>(Mcare)</span>
      </div>
      <div class="info-item">
        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#2e9880" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
        <span>123 street 3rd flr<br>Building Pasig</span>
      </div>
    </div>
  </div>
</section>

<section id="about">
  <div class="services-grid">
    <div class="service-card teal-light">
      <img src="imgs/icon-shield.png" alt="Clinic">
      <h3>Trusted Clinic Service</h3>
      <p>Providing reliable healthcare support for everyone.</p>
    </div>
    <div class="service-card teal-mid">
      <img src="imgs/icon-badge.png" alt="Doctors">
      <h3>Professional Doctors</h3>
      <p>Experienced and qualified medical staff</p>
    </div>
    <div class="service-card navy">
      <img src="imgs/icon-computer.png" alt="System">
      <h3>Modern System</h3>
      <p>Schedule appointments in just a few clicks, no waiting in line</p>
    </div>
  </div>
</section>

<section class="find-doctor-banner">
  <div class="find-doctor-content">
    <h2>FIND YOUR DOCTOR, BOOK YOUR TIME</h2>
    <p>EXPLORE QUALIFICATIONS AND MEDICAL EXPERTISE EASILY</p>
    <a class="btn-outline-white" href="doctors.php">ABOUT DOCTORS</a>
  </div>
</section>

<div id="hours" style="height:0;overflow:hidden;"></div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
