<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header('Location: admin_login.php');
  exit();
}
include '../db.php';

// Ensure settings table exists
$conn->query("CREATE TABLE IF NOT EXISTS settings (setting_key VARCHAR(100) PRIMARY KEY, setting_value TEXT NULL)");

// Helper to get setting
function get_setting(mysqli $conn, string $key): string {
  $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
  $stmt->bind_param("s", $key);
  $stmt->execute();
  $stmt->bind_result($val);
  $valOut = '';
  if ($stmt->fetch()) { $valOut = (string)$val; }
  $stmt->close();
  return $valOut;
}

$currentLogo = get_setting($conn, 'site_logo');

// Handle logo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_logo'])) {
  $newPath = $currentLogo;
  if (!empty($_FILES['logo']['name'])) {
    $uploadDir = '../uploads/site/';
    if (!file_exists($uploadDir)) { mkdir($uploadDir, 0777, true); }
    $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
    $filename = 'logo_' . time() . '.' . $ext;
    $target = $uploadDir . $filename;
    if (move_uploaded_file($_FILES['logo']['tmp_name'], $target)) {
      // Save relative path from web root
      $newPath = 'uploads/site/' . $filename;
      // delete old
      if (!empty($currentLogo)) {
        $old = '../' . $currentLogo;
        if (file_exists($old)) @unlink($old);
      }
    }
  }

  // Upsert setting
  $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('site_logo', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
  $stmt->bind_param("s", $newPath);
  $stmt->execute();
  $stmt->close();

  header('Location: manage_settings.php?saved=1');
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Site Settings | Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="admin-sidebar">
  <h3><i class="fas fa-cogs me-2"></i>Admin Panel</h3>
  <ul>
    <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
    <li><a href="manage_faculty.php"><i class="fas fa-chalkboard-teacher"></i>Faculty</a></li>
    <li><a href="manage_events.php"><i class="fas fa-calendar-alt"></i>Events</a></li>
    <li><a href="manage_achievements.php"><i class="fas fa-trophy"></i>Achievements</a></li>
    <li><a href="manage_clubs.php"><i class="fas fa-users"></i>Clubs</a></li>
    <li><a href="manage_news.php"><i class="fas fa-newspaper"></i>News</a></li>
    <li><a href="manage_students.php"><i class="fas fa-user-graduate"></i>Students</a></li>
    <li><a href="manage_labs.php"><i class="fas fa-flask"></i>Labs</a></li>
    <li><a href="manage_notices.php"><i class="fas fa-bullhorn"></i>Notices</a></li>
    <li><a href="manage_placement.php"><i class="fas fa-briefcase"></i>Placements</a></li>
    <li><a href="manage_settings.php" class="active"><i class="fas fa-sliders"></i>Settings</a></li>
    <li><a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
  </ul>
</div>

<div class="admin-main">
  <div class="admin-header mb-4"><h2>Site Settings</h2></div>

  <?php if (!empty($_GET['saved'])): ?>
    <div class="alert alert-success">Settings saved.</div>
  <?php endif; ?>

  <div class="card card-admin">
    <div class="card-body">
      <h5 class="mb-3">Website Logo</h5>
      <?php if (!empty($currentLogo)): ?>
        <div class="mb-3"><img src="../<?= htmlspecialchars($currentLogo) ?>" alt="Current Logo" style="max-height:80px"></div>
      <?php endif; ?>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="save_logo" value="1">
        <div class="row g-3">
          <div class="col-md-6">
            <input type="file" name="logo" class="form-control" accept="image/*" required>
            <div class="form-text">Recommended height: 60-80px. PNG with transparent background.</div>
          </div>
          <div class="col-md-6">
            <button type="submit" class="btn btn-admin"><i class="fa fa-upload me-2"></i>Upload Logo</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


