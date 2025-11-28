<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Try to access DB to fetch site logo if available
if (!isset($conn)) {
  @include __DIR__ . '/db.php';
}
$siteLogo = '';
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
if (isset($conn) && $conn instanceof mysqli) {
  // Ensure settings table exists once (no-op if present)
  @$conn->query("CREATE TABLE IF NOT EXISTS settings (setting_key VARCHAR(100) PRIMARY KEY, setting_value TEXT NULL)");
  if ($stmt = @$conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'site_logo'")) {
    $stmt->execute();
    $stmt->bind_result($val);
    if ($stmt->fetch()) { $siteLogo = (string)$val; }
    $stmt->close();
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Department Portal</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

  <style>
    :root{
      --brand: #00a9b4;
      --brand-dark: #008f97;
      --accent: #ffffff;
      --muted: #6c757d;
      --shadow: 0 6px 24px rgba(0,0,0,0.08);
      --radius: .75rem;
      --container-max: 1100px;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--bg-color);
      color: var(--text-color);
      scroll-behavior: smooth;
    }

    .topbar {
      background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 50%, var(--brand) 100%);
      background-size: 200% 100%;
      animation: topbarGradient 4s ease-in-out infinite;
      color: var(--accent);
      padding: .8rem 0;
      position: relative;
      overflow: hidden;
    }

    @keyframes topbarGradient {
      0%, 100% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
    }

    .topbar::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
      animation: shimmer 3s infinite;
    }

    @keyframes shimmer {
      0% { left: -100%; }
      100% { left: 100%; }
    }

    .brand {
      display: flex; 
      align-items: center; 
      gap: .8rem;
      font-weight: 700; 
      color: var(--accent);
      position: relative;
      z-index: 2;
    }

    .brand .logo-img { width: 200px; height: 60px; }

    .brand-text {
      font-size: 1.1rem;
      line-height: 1.1;
      font-weight: 700;
      text-transform: uppercase;
    }

    .brand-main {
      font-size: 1.4rem;
      font-weight: 800;
      line-height: 1;
      margin-bottom: 2px;
    }

    .brand-subtitle {
      font-size: 0.7rem;
      opacity: 0.9;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    nav.navbar {
      background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
      box-shadow: 0 4px 20px rgba(0,169,180,0.1);
      border-bottom-left-radius: 0;
      border-bottom-right-radius: 0;
      border-bottom: 3px solid var(--brand);
      position: relative;
      z-index: 1000;
      padding-left: 0; /* remove default horizontal padding */
      padding-right: 0;
    }

    /* Remove default container side padding to align logo and nav to edges */
    .topbar .container,
    nav.navbar .container {
      padding-left: 0;
      padding-right: 0;
    }

    /* Nudge first nav item to align flush with left edge */
    nav.navbar .navbar-nav .nav-item:first-child .nav-link {
      padding-left: 0;
      margin-left: 0;
    }

    .nav-link, .navbar-brand {
      color: #333 !important;
      font-weight: 500;
      position: relative;
      transition: all 0.3s ease;
      padding: 0.4rem 0.6rem !important; /* minimal padding */
      border-radius: 6px;
      margin: 0 0.05rem; /* minimal gap */
    }

    .nav-link::before {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      width: 0;
      height: 3px;
      background: linear-gradient(90deg, var(--brand), var(--brand-dark));
      transition: all 0.3s ease;
      transform: translateX(-50%);
      border-radius: 2px;
    }

    .nav-link:hover::before {
      width: 80%;
    }

    .nav-link.active {
      color: var(--brand) !important;
      font-weight: 700;
      background: linear-gradient(135deg, rgba(0,169,180,0.1), rgba(0,140,151,0.1));
      border-radius: 8px;
    }

    .nav-link.active::before {
      width: 80%;
    }

    .nav-link:hover {
      color: var(--brand) !important;
      background: linear-gradient(135deg, rgba(0,169,180,0.05), rgba(0,140,151,0.05));
      transform: translateY(-1px);
    }

    .hero {
      background: linear-gradient(180deg, rgba(0,169,180,0.08), transparent 60%);
      padding: 1.25rem 0;
      border-radius: .5rem;
      margin-bottom: 1rem;
    }

    .card-spot {
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      border: none;
    }

    .btn-brand {
      background: var(--brand);
      color: white;
      border: 0;
    }
    .btn-outline-brand {
      border-color: var(--brand);
      color: var(--brand);
      background: transparent;
    }

    /* @media (min-width: 992px){
      .container-wide{ max-width: var(--container-max); }
    } */

    /* Utilities */
    .muted-small { color: var(--muted); font-size: .95rem; }
    .profile-pic { width:72px; height:72px; border-radius:10px; object-fit:cover; }
    .faculty-grid .card { min-height: 160px; }
    .table thead th { background: #f3f5f6; }
    
    /* User Profile Styles */
    .user-profile {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.75rem 1rem;
      border-radius: 12px;
      transition: all 0.3s ease;
      background: linear-gradient(135deg, rgba(0,169,180,0.05), rgba(0,140,151,0.05));
      border: 1px solid rgba(0,169,180,0.1);
      position: relative;
      overflow: hidden;
    }

    .user-profile::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(0,169,180,0.1), transparent);
      transition: left 0.5s ease;
    }

    .user-profile:hover::before {
      left: 100%;
    }

    .user-profile:hover {
      background: linear-gradient(135deg, rgba(0,169,180,0.1), rgba(0,140,151,0.1));
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0,169,180,0.15);
      border-color: rgba(0,169,180,0.2);
    }

    .user-avatar {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--brand), var(--brand-dark));
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 1.2rem;
      text-transform: uppercase;
      box-shadow: 0 4px 15px rgba(0,169,180,0.3);
      transition: all 0.3s ease;
      position: relative;
      z-index: 2;
    }

    .user-avatar:hover {
      transform: scale(1.1);
      box-shadow: 0 6px 20px rgba(0,169,180,0.4);
    }

    .user-info {
      display: flex;
      flex-direction: column;
      position: relative;
      z-index: 2;
    }

    .user-name {
      font-weight: 600;
      font-size: 0.95rem;
      color: #333;
      margin: 0;
      line-height: 1.2;
      transition: color 0.3s ease;
    }

    .user-role {
      font-size: 0.8rem;
      color: var(--muted);
      margin: 0;
      line-height: 1.2;
      font-weight: 500;
    }

    /* Enhanced Button Styles */
    .btn-brand {
      background: linear-gradient(135deg, var(--brand), var(--brand-dark));
      color: white;
      border: 0;
      border-radius: 25px;
      padding: 0.6rem 1.5rem;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0,169,180,0.3);
      position: relative;
      overflow: hidden;
    }

    .btn-brand::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.5s ease;
    }

    .btn-brand:hover::before {
      left: 100%;
    }

    .btn-brand:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0,169,180,0.4);
      background: linear-gradient(135deg, var(--brand-dark), var(--brand));
    }

    .btn-outline-brand {
      border: 2px solid var(--brand);
      color: var(--brand);
      background: transparent;
      border-radius: 25px;
      padding: 0.5rem 1.2rem;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-outline-brand:hover {
      background: var(--brand);
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0,169,180,0.3);
    }

    /* Contact Info Enhancement - scoped to topbar only to avoid footer conflicts */
    .topbar .contact-info {
      color: rgba(255,255,255,0.9);
      font-size: 0.9rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .topbar .contact-info i {
      color: rgba(255,255,255,0.8);
      transition: color 0.3s ease;
    }

    .topbar .contact-info:hover i {
      color: rgba(255,255,255,1);
    }

    /* Navbar Toggler Enhancement */
    .navbar-toggler {
      border: 2px solid var(--brand);
      border-radius: 8px;
      padding: 0.4rem 0.6rem;
      transition: all 0.3s ease;
    }

    .navbar-toggler:focus {
      box-shadow: 0 0 0 0.2rem rgba(0,169,180,0.25);
    }

    .navbar-toggler:hover {
      background-color: rgba(0,169,180,0.1);
      transform: scale(1.05);
    }

    .navbar-toggler-icon {
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%2300a9b4' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .contact-info {
        display: none;
      }
      
      .topbar {
        padding: 0.5rem 0;
      }
      
      .brand-text {
        font-size: 0.9rem;
      }
      
      .brand-subtitle {
        font-size: 0.75rem;
      }
    }
  </style>
</head>
<body>
  <div class="topbar">
    <div class="container d-flex justify-content-between align-items-center">
      <div class="brand">
        <?php if (!empty($siteLogo)): ?>
          <img src="<?= htmlspecialchars($siteLogo) ?>" class="logo-img" alt="Logo">
        <?php endif; ?>
      </div>
      <div class="contact-info">
        <i class="fas fa-envelope"></i>
        <span>dept@example.edu</span>
        <i class="fas fa-phone ms-3"></i>
        <span>+91 12345 67890</span>
      </div>
    </div>
  </div>

  <nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
      <a class="navbar-brand d-lg-none brand" href="index.php">
        <?php if (!empty($siteLogo)): ?>
          <img src="<?= htmlspecialchars($siteLogo) ?>" class="logo-img" alt="Logo">
        <?php else: ?>
          <div class="logo">
            <i class="fas fa-graduation-cap"></i>
          </div>
        <?php endif; ?>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link <?= in_array($currentPage, ['dashboard.php','index.php']) ? 'active':''?>" href="index.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='about.php' ? 'active':''?>" href="about.php">About</a></li>
          <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='faculty.php' ? 'active':''?>" href="faculty.php">Faculty</a></li>
          <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='courses.php' ? 'active':''?>" href="courses.php">Courses</a></li>
          <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='placements.php' ? 'active':''?>" href="placements.php">Placements</a></li>
          <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='labs.php' ? 'active':''?>" href="labs.php">Labs</a></li>
          <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='clubs.php' ? 'active':''?>" href="clubs.php">Clubs</a></li>
          <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='achievements.php' ? 'active':''?>" href="achievements.php">Achievements</a></li>
          <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='events.php' ? 'active':''?>" href="events.php">Events</a></li>
          <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='news.php' ? 'active':''?>" href="news.php">News</a></li>
          <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='notices.php' ? 'active':''?>" href="notices.php">Notices</a></li>
        </ul>

        <div class="d-flex gap-2 align-items-center">
          <?php if (isset($_SESSION['student_name'])): ?>
            <!-- Student Profile -->
            <div class="user-profile">
              <div class="user-avatar">
                <?= strtoupper(substr($_SESSION['student_name'], 0, 1)) ?>
              </div>
              <div class="user-info">
                <p class="user-name"><?= htmlspecialchars($_SESSION['student_name']) ?></p>
                <p class="user-role">Student</p>
              </div>
            </div>
            <a class="btn btn-sm btn-outline-danger" href="student_logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
          <?php elseif (isset($_SESSION['admin_name'])): ?>
            <!-- Admin Profile -->
            <div class="user-profile">
              <div class="user-avatar">
                <?= strtoupper(substr($_SESSION['admin_name'], 0, 1)) ?>
              </div>
              <div class="user-info">
                <p class="user-name"><?= htmlspecialchars($_SESSION['admin_name']) ?></p>
                <p class="user-role">Admin</p>
              </div>
            </div>
            <a class="btn btn-sm btn-outline-danger" href="admin/admin_logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
          <?php elseif (isset($_SESSION['name'])): ?>
            <!-- General User Profile -->
            <div class="user-profile">
              <div class="user-avatar">
                <?= strtoupper(substr($_SESSION['name'], 0, 1)) ?>
              </div>
              <div class="user-info">
                <p class="user-name"><?= htmlspecialchars($_SESSION['name']) ?></p>
                <p class="user-role"><?= ucfirst($_SESSION['role'] ?? 'User') ?></p>
              </div>
            </div>
            <a class="btn btn-sm btn-outline-danger" href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
          <?php else: ?>
            <!-- No user logged in - show login button -->
            <a class="btn btn-sm btn-brand" href="student_login.php"><i class="fa fa-sign-in-alt"></i> Student Login</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>

  <main class="container-fulid container-wide py-3">
