<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$specialty = $_GET['specialty'] ?? null;
$search = $_GET['search'] ?? '';
$specializations = getSpecializations();
$doctors = getDoctors($specialty);

// Filter by search term
if ($search) {
    $doctors = array_filter($doctors, function($d) use ($search) {
        return stripos($d['full_name'], $search) !== false || stripos($d['specialization'], $search) !== false;
    });
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Our Doctors — Medi Clinic</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .doctors-page {
      padding: 40px 0 60px;
    }
    .doctors-header {
      text-align: center;
      margin-bottom: 40px;
    }
    .doctors-header h1 {
      color: var(--nav-bg);
      font-size: 32px;
      margin-bottom: 8px;
    }
    .doctors-header p {
      color: #666;
      font-size: 15px;
    }

    .doctor-filters {
      max-width: 900px;
      margin: 0 auto 40px;
      display: flex;
      gap: 16px;
      flex-wrap: wrap;
      justify-content: center;
    }
    .doctor-filters select,
    .doctor-filters input {
      padding: 12px 16px;
      border: 1.5px solid #d0d9d6;
      border-radius: 8px;
      font-size: 14px;
      background: #fff;
      min-width: 200px;
    }
    .doctor-filters input {
      flex: 1;
      min-width: 200px;
    }

    .doctors-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 24px;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }

    .doctor-card {
      background: #fff;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 16px rgba(0,0,0,.08);
      transition: transform .2s, box-shadow .2s;
    }
    .doctor-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 24px rgba(0,0,0,.12);
    }

    .doctor-photo {
      height: 180px;
      background: linear-gradient(135deg, var(--teal-light) 0%, var(--nav-bg) 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 64px;
    }

    .doctor-info {
      padding: 20px;
    }

    .doctor-name {
      font-size: 18px;
      font-weight: 700;
      color: var(--nav-bg);
      margin-bottom: 4px;
    }

    .doctor-specialty {
      color: var(--teal-mid);
      font-size: 14px;
      font-weight: 600;
      margin-bottom: 12px;
    }

    .doctor-bio {
      color: #666;
      font-size: 13px;
      line-height: 1.5;
      margin-bottom: 16px;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .doctor-actions {
      display: flex;
      gap: 12px;
    }

    .btn-book-doctor {
      flex: 1;
      padding: 12px 16px;
      background: var(--nav-bg);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      text-align: center;
      text-decoration: none;
      transition: background .2s;
    }
    .btn-book-doctor:hover {
      background: #1b4036;
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="doctors-page">
  <div class="doctors-header">
    <h1>OUR DOCTORS</h1>
    <p>Find the right specialist for your healthcare needs</p>
  </div>

  <form class="doctor-filters" method="GET" action="doctors.php">
    <input type="text" name="search" placeholder="Search by name or specialty..."
           value="<?= htmlspecialchars($search) ?>">
    <select name="specialty">
      <option value="">All Specializations</option>
      <?php foreach ($specializations as $s): ?>
        <option value="<?= htmlspecialchars($s['specialization']) ?>" <?= $specialty === $s['specialization'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($s['specialization']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn-book-doctor" style="max-width: 120px;">Filter</button>
  </form>

  <div class="doctors-grid">
    <?php foreach ($doctors as $doctor): ?>
    <div class="doctor-card">
      <div class="doctor-photo">
        <?= strtoupper(substr($doctor['full_name'], 4, 2)) ?>
      </div>
      <div class="doctor-info">
        <div class="doctor-name"><?= htmlspecialchars($doctor['full_name']) ?></div>
        <div class="doctor-specialty"><?= htmlspecialchars($doctor['specialization']) ?></div>
        <div class="doctor-bio"><?= htmlspecialchars($doctor['bio'] ?? 'Experienced medical professional.') ?></div>
        <div class="doctor-actions">
          <a href="book.php?doctor=<?= $doctor['id'] ?>" class="btn-book-doctor">Book Appointment</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($doctors)): ?>
    <p style="text-align: center; grid-column: 1 / -1; color: #666;">
      No doctors found matching your criteria.
    </p>
    <?php endif; ?>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>