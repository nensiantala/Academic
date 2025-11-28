<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | DeptConnect</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="admin.css">
</head>
<body>
<!-- Admin Sidebar -->
<div class="admin-sidebar">
  <h3><i class="fas fa-cogs me-2"></i>Admin Panel</h3>
  <ul>
    <li><a href="admin_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
    <li><a href="manage_faculty.php"><i class="fas fa-chalkboard-teacher"></i>Faculty</a></li>
    <li><a href="manage_events.php"><i class="fas fa-calendar-alt"></i>Events</a></li>
    <li><a href="manage_achievements.php"><i class="fas fa-trophy"></i>Achievements</a></li>
    <li><a href="manage_clubs.php"><i class="fas fa-users"></i>Clubs</a></li>
    <li><a href="manage_news.php"><i class="fas fa-newspaper"></i>News</a></li>
    <li><a href="manage_students.php"><i class="fas fa-user-graduate"></i>Students</a></li>
    <li><a href="manage_labs.php"><i class="fas fa-flask"></i>Labs</a></li>
    <li><a href="manage_notices.php"><i class="fas fa-bullhorn"></i>Notices</a></li>
    <li><a href="manage_placement.php"><i class="fas fa-briefcase"></i>Placements</a></li>
    <li><a href="manage_settings.php"><i class="fas fa-sliders"></i>Settings</a></li>
    <li><a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="admin-main">
  <div class="admin-header">
    <div class="d-flex justify-content-between align-items-center w-100" style="position: relative; z-index: 2;">
      <div>
        <h2><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h2>
        <p class="mb-0 opacity-75">Welcome back, <strong><?= htmlspecialchars($_SESSION['admin_name']) ?></strong>! Manage your academic portal efficiently.</p>
      </div>
      <div>
        <a href="admin_logout.php" class="btn btn-light shadow-sm" onclick="return confirm('Are you sure you want to logout?');" style="color: #00A9B4 !important;">
          <i class="fas fa-sign-out-alt me-2"></i>Logout
        </a>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-3 col-md-6">
      <div class="card card-admin h-100">
        <div class="card-body text-center">
          <i class="fas fa-chalkboard-teacher fa-3x text-primary mb-3"></i>
          <h5 class="card-title">Manage Faculty</h5>
          <p class="card-text">Add, edit, and manage faculty members</p>
          <a href="manage_faculty.php" class="btn btn-admin w-100">Manage</a>
        </div>
      </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
      <div class="card card-admin h-100">
        <div class="card-body text-center">
          <i class="fas fa-calendar-alt fa-3x text-success mb-3"></i>
          <h5 class="card-title">Manage Events</h5>
          <p class="card-text">Create and organize academic events</p>
          <a href="manage_events.php" class="btn btn-admin w-100">Manage</a>
        </div>
      </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
      <div class="card card-admin h-100">
        <div class="card-body text-center">
          <i class="fas fa-trophy fa-3x text-warning mb-3"></i>
          <h5 class="card-title">Manage Achievements</h5>
          <p class="card-text">Showcase department achievements</p>
          <a href="manage_achievements.php" class="btn btn-admin w-100">Manage</a>
        </div>
      </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
      <div class="card card-admin h-100">
        <div class="card-body text-center">
          <i class="fas fa-users fa-3x text-info mb-3"></i>
          <h5 class="card-title">Manage Clubs</h5>
          <p class="card-text">Organize student clubs and activities</p>
          <a href="manage_clubs.php" class="btn btn-admin w-100">Manage</a>
        </div>
      </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
      <div class="card card-admin h-100">
        <div class="card-body text-center">
          <i class="fas fa-newspaper fa-3x text-danger mb-3"></i>
          <h5 class="card-title">Manage News</h5>
          <p class="card-text">Publish department news and updates</p>
          <a href="manage_news.php" class="btn btn-admin w-100">Manage</a>
        </div>
      </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
      <div class="card card-admin h-100">
        <div class="card-body text-center">
          <i class="fas fa-user-graduate fa-3x text-primary mb-3"></i>
          <h5 class="card-title">Manage Students</h5>
          <p class="card-text">Student information and records</p>
          <a href="manage_students.php" class="btn btn-admin w-100">Manage</a>
        </div>
      </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
      <div class="card card-admin h-100">
        <div class="card-body text-center">
          <i class="fas fa-flask fa-3x text-success mb-3"></i>
          <h5 class="card-title">Manage Labs</h5>
          <p class="card-text">Laboratory facilities and equipment</p>
          <a href="manage_labs.php" class="btn btn-admin w-100">Manage</a>
        </div>
      </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
      <div class="card card-admin h-100">
        <div class="card-body text-center">
          <i class="fas fa-briefcase fa-3x text-warning mb-3"></i>
          <h5 class="card-title">Manage Placements</h5>
          <p class="card-text">Job placements and opportunities</p>
          <a href="manage_placement.php" class="btn btn-admin w-100">Manage</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
