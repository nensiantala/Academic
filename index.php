<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header('Location: student_login.php');
    exit();
}
include 'header.php';
include 'db.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | Academic Portal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #fff;
      color: #333;
    }
    .hero-section {
      background: linear-gradient(rgba(0,169,180,0.7), rgba(0,140,151,0.7)), url('assets/department-bg.jpg') center/cover no-repeat;
      color: white;
      text-align: center;
      padding: 100px 20px;
    }
    .hero-section h1 {
      font-weight: 700;
      font-size: 3rem;
    }
    .quick-links .card {
      border: none;
      transition: transform 0.3s;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .quick-links .card:hover {
      transform: scale(1.05);
    }
    .card i {
      font-size: 40px;
      color: #00A9B4;
    }
    footer {
      background-color: #0a4a4e;
      color: white;
      text-align: center;
      padding: 20px 0;
      margin-top: 60px;
    }
    .announcement {
      background: #e0f7fa;
      border-left: 5px solid #00A9B4;
      padding: 10px 15px;
      margin-bottom: 10px;
      border-radius: 6px;
    }
    .recent-item {
      transition: all 0.3s ease;
      border-radius: 8px;
      padding: 10px;
    }
    .recent-item:hover {
      background: #f8f9fa;
      transform: translateX(5px);
    }
    .recent-item a {
      transition: color 0.3s;
    }
    .recent-item a:hover {
      color: #00A9B4 !important;
    }
    .card-header h5 {
      font-weight: 600;
      font-size: 1.1rem;
    }
  </style>
</head>
<body>

<!-- Hero Section -->
<section class="hero-section" data-aos="fade-up">
  <div class="container">
    <img src="assets/department-logo.png" alt="Department Logo" class="mb-4" width="100">
    <h1>Department of Computer Engineering</h1>
    <p class="lead mt-3">Empowering Innovation through Knowledge, Research, and Technology</p>
    <a href="about.php" class="btn btn-light mt-3 px-4 py-2">Explore More</a>
  </div>
</section>

<!-- Quick Links -->
<section class="quick-links py-5">
  <div class="container">
    <h2 class="text-center mb-4 text-uppercase fw-bold" style="color:#00A9B4;">Quick Access</h2>
    <div class="row g-4">
      <div class="col-md-3" data-aos="zoom-in"><a href="faculty.php" class="text-decoration-none text-dark">
        <div class="card text-center p-4"><i class="fa-solid fa-chalkboard-user mb-3"></i><h5>Faculty</h5></div></a></div>
      <div class="col-md-3" data-aos="zoom-in"><a href="courses.php" class="text-decoration-none text-dark">
        <div class="card text-center p-4"><i class="fa-solid fa-book-open mb-3"></i><h5>Courses</h5></div></a></div>
      <div class="col-md-3" data-aos="zoom-in"><a href="achievements.php" class="text-decoration-none text-dark">
        <div class="card text-center p-4"><i class="fa-solid fa-trophy mb-3"></i><h5>Achievements</h5></div></a></div>
      <div class="col-md-3" data-aos="zoom-in"><a href="clubs.php" class="text-decoration-none text-dark">
        <div class="card text-center p-4"><i class="fa-solid fa-users mb-3"></i><h5>Clubs</h5></div></a></div>
    </div>
  </div>
</section>

<!-- Latest Updates -->
<section class="py-5 bg-light">
  <div class="container">
    <h2 class="text-center mb-4 fw-bold" style="color:#00A9B4;">Latest Updates</h2>
    
    <div class="row g-4 mb-5">
      <!-- Events Carousel -->
      <div class="col-md-4">
        <div class="card h-100 shadow-sm">
          <div class="card-header text-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Recent Events</h5>
          </div>
          <div class="card-body p-0" style="height: 400px;">
            <?php
            $events = $conn->query("SELECT * FROM events ORDER BY created_at DESC LIMIT 3");
            if ($events && $events->num_rows > 1):
            ?>
              <div id="eventsCarousel" class="carousel slide h-100" data-bs-ride="carousel" data-bs-interval="4000">
                <div class="carousel-inner h-100">
                  <?php
                  $index = 0;
                  while ($e = $events->fetch_assoc()):
                  ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?> h-100">
                      <div class="p-4 d-flex flex-column justify-content-between h-100">
                        <div>
                          <h6 class="fw-semibold"><a href="events.php" class="text-decoration-none text-dark"><?= htmlspecialchars($e['title']) ?></a></h6>
                          <small class="text-muted d-block mb-2">
                            <i class="fas fa-calendar me-1"></i>
                            <?= date('d M Y', strtotime($e['start_date'])) ?> - <?= date('d M Y', strtotime($e['end_date'])) ?>
                          </small>
                          <div class="mb-2">
                            <span class="badge bg-primary"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($e['venue']) ?></span>
                          </div>
                          <?php if (!empty($e['description'])): ?>
                            <p class="small text-muted"><?= htmlspecialchars(substr($e['description'], 0, 80)) ?>...</p>
                          <?php endif; ?>
                        </div>
                        <div class="text-center mt-3">
                          <a href="events.php" class="btn btn-sm btn-primary">View All <i class="fas fa-arrow-right ms-2"></i></a>
                        </div>
                      </div>
                    </div>
                  <?php
                  $index++;
                  endwhile;
                  ?>
                </div>
                <div class="carousel-indicators" style="position: relative; bottom: 0; margin: 0;">
                  <?php for ($i = 0; $i < $events->num_rows; $i++): ?>
                    <button type="button" data-bs-target="#eventsCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>"></button>
                  <?php endfor; ?>
                </div>
              </div>
            <?php
            elseif ($events && $events->num_rows == 1):
              $e = $events->fetch_assoc();
            ?>
              <div class="p-4">
                <h6 class="fw-semibold"><a href="events.php" class="text-decoration-none text-dark"><?= htmlspecialchars($e['title']) ?></a></h6>
                <small class="text-muted d-block mb-2"><i class="fas fa-calendar me-1"></i><?= date('d M Y', strtotime($e['start_date'])) ?> - <?= date('d M Y', strtotime($e['end_date'])) ?></small>
                <div class="mb-2"><span a class="badge bg-primary"><?= htmlspecialchars($e['venue']) ?></span></div>
                <div class="text-center mt-3"><a href="events.php" class="btn btn-sm btn-primary">View All</a></div>
              </div>
            <?php else: ?>
              <div class="p-4 text-center text-muted">No events available</div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Achievements Carousel -->
      <div class="col-md-4">
        <div class="card h-100 shadow-sm">
          <div class="card-header text-center" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
            <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Recent Achievements</h5>
          </div>
          <div class="card-body p-0" style="height: 400px;">
            <?php
            $achievements = $conn->query("SELECT * FROM achievements ORDER BY created_at DESC LIMIT 3");
            if ($achievements && $achievements->num_rows > 1):
            ?>
              <div id="achievementsCarousel" class="carousel slide h-100" data-bs-ride="carousel" data-bs-interval="4000">
                <div class="carousel-inner h-100">
                  <?php
                  $index = 0;
                  while ($a = $achievements->fetch_assoc()):
                  ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?> h-100">
                      <div class="p-4 d-flex flex-column justify-content-between h-100">
                        <div>
                          <h6 class="fw-semibold"><a href="achievements.php" class="text-decoration-none text-dark"><?= htmlspecialchars($a['title']) ?></a></h6>
                          <small class="text-muted d-block mb-2"><i class="fas fa-calendar me-1"></i><?= date('d M Y', strtotime($a['date'])) ?></small>
                          <div class="mb-2">
                            <span class="badge <?= $a['category'] == 'student' ? 'bg-success' : 'bg-info' ?>"><?= ucfirst($a['category']) ?></span>
                          </div>
                          <p class="small text-muted"><?= htmlspecialchars(substr($a['description'], 0, 80)) ?>...</p>
                        </div>
                        <div class="text-center mt-3">
                          <a href="achievements.php" class="btn btn-sm btn-success">View All <i class="fas fa-arrow-right ms-2"></i></a>
                        </div>
                      </div>
                    </div>
                  <?php
                  $index++;
                  endwhile;
                  ?>
                </div>
                <div class="carousel-indicators" style="position: relative; bottom: 0; margin: 0;">
                  <?php for ($i = 0; $i < $achievements->num_rows; $i++): ?>
                    <button type="button" data-bs-target="#achievementsCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>"></button>
                  <?php endfor; ?>
                </div>
              </div>
            <?php
            elseif ($achievements && $achievements->num_rows == 1):
              $a = $achievements->fetch_assoc();
            ?>
              <div class="p-4">
                <h6 class="fw-semibold"><a href="achievements.php" class="text-decoration-none text-dark"><?= htmlspecialchars($a['title']) ?></a></h6>
                <small class="text-muted d-block mb-2"><i class="fas fa-calendar me-1"></i><?= date('d M Y', strtotime($a['date'])) ?></small>
                <div class="mb-2"><span class="badge <?= $a['category'] == 'student' ? 'bg-success' : 'bg-info' ?>"><?= ucfirst($a['category']) ?></span></div>
                <div class="text-center mt-3"><a href="achievements.php" class="btn btn-sm btn-success">View All</a></div>
              </div>
            <?php else: ?>
              <div class="p-4 text-center text-muted">No achievements available</div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- News Carousel -->
      <div class="col-md-4">
        <div class="card h-100 shadow-sm">
          <div class="card-header text-center" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white;">
            <h5 class="mb-0"><i class="fas fa-newspaper me-2"></i>Recent News</h5>
          </div>
          <div class="card-body p-0" style="height: 400px;">
            <?php
            $news = $conn->query("SELECT * FROM news ORDER BY created_at DESC LIMIT 3");
            if ($news && $news->num_rows > 1):
            ?>
              <div id="newsCarousel" class="carousel slide h-100" data-bs-ride="carousel" data-bs-interval="4000">
                <div class="carousel-inner h-100">
                  <?php
                  $index = 0;
                  while ($n = $news->fetch_assoc()):
                  ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?> h-100">
                      <div class="p-4 d-flex flex-column justify-content-between h-100">
                        <div>
                          <?php if (!empty($n['image']) && is_string($n['image'])): ?>
                            <img src="<?= htmlspecialchars($n['image']) ?>" alt="News Image" class="img-fluid rounded mb-3" style="max-height: 150px; width: 100%; object-fit: cover;">
                          <?php endif; ?>
                          <h6 class="fw-semibold"><a href="news.php?id=<?= $n['id'] ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($n['title']) ?></a></h6>
                          <small class="text-muted d-block mb-2"><i class="fas fa-calendar me-1"></i><?= date('d M Y', strtotime($n['date'])) ?></small>
                          <p class="small text-muted"><?= htmlspecialchars(substr(strip_tags($n['content']), 0, 80)) ?>...</p>
                        </div>
                        <div class="text-center mt-3">
                          <a href="news.php" class="btn btn-sm btn-info">View All <i class="fas fa-arrow-right ms-2"></i></a>
                        </div>
                      </div>
                    </div>
                  <?php
                  $index++;
                  endwhile;
                  ?>
                </div>
                <div class="carousel-indicators" style="position: relative; bottom: 0; margin: 0;">
                  <?php for ($i = 0; $i < $news->num_rows; $i++): ?>
                    <button type="button" data-bs-target="#newsCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>"></button>
                  <?php endfor; ?>
                </div>
              </div>
            <?php
            elseif ($news && $news->num_rows == 1):
              $n = $news->fetch_assoc();
            ?>
              <div class="p-4">
                <?php if (!empty($n['image']) && is_string($n['image'])): ?>
                  <img src="<?= htmlspecialchars($n['image']) ?>" alt="News Image" class="img-fluid rounded mb-3" style="max-height: 150px; width: 100%; object-fit: cover;">
                <?php endif; ?>
                <h6 class="fw-semibold"><a href="news.php?id=<?= $n['id'] ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($n['title']) ?></a></h6>
                <small class="text-muted d-block mb-2"><i class="fas fa-calendar me-1"></i><?= date('d M Y', strtotime($n['date'])) ?></small>
                <div class="text-center mt-3"><a href="news.php" class="btn btn-sm btn-info">View All</a></div>
              </div>
            <?php else: ?>
              <div class="p-4 text-center text-muted">No news available</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script>
  AOS.init({ duration: 1000, once: true });
</script>

</body>
</html>
<?php include 'footer.php'; ?>
