<?php
include 'header.php';
include 'db.php';

$faculty_id = $_GET['id'] ?? 0;
$faculty = null;

if ($faculty_id) {
    $stmt = $conn->prepare("SELECT * FROM faculty WHERE id = ?");
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $faculty = $result->fetch_assoc();
    $stmt->close();
}

if (!$faculty) {
    header('Location: faculty.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($faculty['name']) ?> | Faculty Details</title>

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      color: #333;
    }
    
    .main-container {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.1);
      margin: 20px auto;
      overflow: hidden;
    }
    
    .faculty-header {
      background: linear-gradient(135deg, #00A9B4, #008c97);
      color: white;
      padding: 20px 0;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    
    .faculty-header::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
      animation: float 6s ease-in-out infinite;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0px) rotate(0deg); }
      50% { transform: translateY(-20px) rotate(180deg); }
    }
    
    .faculty-photo {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid rgba(255,255,255,0.3);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
      margin-bottom: 10px;
      position: relative;
      z-index: 2;
      transition: transform 0.3s ease;
    }
    
    .faculty-photo:hover {
      transform: scale(1.05);
    }
    
    .faculty-name {
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 5px;
      text-shadow: 0 2px 4px rgba(0,0,0,0.3);
      position: relative;
      z-index: 2;
    }
    
    .faculty-designation {
      font-size: 1rem;
      opacity: 0.9;
      font-weight: 500;
      position: relative;
      z-index: 2;
    }
    
    .content-section {
      padding: 20px;
    }
    
    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 15px;
      margin-bottom: 20px;
    }
    
    .info-card {
      background: linear-gradient(135deg, #f8f9fa, #e9ecef);
      border-radius: 10px;
      padding: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.08);
      border: 1px solid rgba(0,169,180,0.1);
      transition: all 0.3s ease;
    }
    
    .info-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 35px rgba(0,0,0,0.12);
    }
    
    .info-item {
      display: flex;
      align-items: center;
      margin-bottom: 8px;
      font-size: 0.9rem;
      color: #555;
    }
    
    .info-item i {
      color: #00A9B4;
      margin-right: 10px;
      width: 20px;
      text-align: center;
      font-size: 1rem;
    }
    
    .section-title {
      font-size: 1.3rem;
      font-weight: 700;
      color: #00A9B4;
      margin: 20px 0 10px 0;
      position: relative;
      padding-left: 15px;
    }
    
    .section-title::before {
      content: '';
      position: absolute;
      left: 0;
      top: 50%;
      transform: translateY(-50%);
      width: 5px;
      height: 30px;
      background: linear-gradient(135deg, #00A9B4, #008c97);
      border-radius: 3px;
    }
    
    .experience-card {
      background: linear-gradient(135deg, #ffffff, #f8f9fa);
      border-radius: 10px;
      padding: 15px;
      margin-bottom: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.08);
      border-left: 4px solid #00A9B4;
      transition: all 0.3s ease;
    }
    
    .experience-card:hover {
      transform: translateX(3px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    }
    
    .experience-title {
      font-weight: 700;
      color: #00A9B4;
      font-size: 1rem;
      margin-bottom: 8px;
      display: flex;
      align-items: center;
    }
    
    .experience-title i {
      margin-right: 8px;
      font-size: 1.1rem;
    }
    
    .experience-content {
      font-size: 0.9rem;
      color: #555;
      line-height: 1.5;
      text-align: justify;
    }
    
    .tags-container {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      margin-top: 8px;
    }
    
    .tag-item, .subject-item {
      background: linear-gradient(135deg, #00A9B4, #008c97);
      color: white;
      padding: 4px 10px;
      border-radius: 15px;
      font-size: 0.8rem;
      font-weight: 600;
      box-shadow: 0 2px 8px rgba(0,169,180,0.3);
      transition: all 0.3s ease;
    }
    
    .subject-item {
      background: linear-gradient(135deg, #28a745, #20c997);
    }
    
    .tag-item:hover, .subject-item:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(0,169,180,0.4);
    }
    
    .scholar-link {
      color: #00A9B4;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .scholar-link:hover {
      color: #008c97;
      text-decoration: underline;
    }
    
    .back-btn {
      background: linear-gradient(135deg, #00A9B4, #008c97);
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 20px;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.9rem;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(0,169,180,0.3);
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    
    .back-btn:hover {
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(0,169,180,0.4);
    }
    
    .contact-section {
      background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
      border-radius: 10px;
      padding: 15px;
      margin-bottom: 20px;
    }
    
    @media (max-width: 768px) {
      .content-section {
        padding: 15px;
      }
      
      .faculty-name {
        font-size: 1.5rem;
      }
      
      .info-grid {
        grid-template-columns: 1fr;
        gap: 10px;
      }
    }
  </style>
</head>
<body>

<div class="container mt-4">
  <div class="main-container">
    <!-- Header Section -->
    <div class="faculty-header">
      <div class="d-flex justify-content-between align-items-center mb-4 px-4">
        <a href="faculty.php" class="back-btn">
          <i class="fas fa-arrow-left"></i>Back to Faculty
        </a>
      </div>
      
      <?php 
      // Handle image path - check if it's already a full path or just filename
      $photo = 'assets/default-avatar.png';
      if (!empty($faculty['profile_photo']) && $faculty['profile_photo'] !== 'assets/faculty-default.png') {
          $imageValue = trim($faculty['profile_photo']);
          // If it already contains 'uploads/faculty/', use it as is
          if (strpos($imageValue, 'uploads/faculty/') !== false) {
              $photo = $imageValue;
          } 
          // If it starts with 'uploads/', use it as is
          elseif (strpos($imageValue, 'uploads/') === 0) {
              $photo = $imageValue;
          }
          // If it contains 'faculty/', prepend 'uploads/'
          elseif (strpos($imageValue, 'faculty/') !== false) {
              $photo = 'uploads/' . $imageValue;
          }
          // Otherwise, prepend 'uploads/faculty/'
          else {
              $photo = 'uploads/faculty/' . $imageValue;
          }
          
          // Verify file exists, otherwise use default
          if (!file_exists($photo)) {
              $photo = 'assets/default-avatar.png';
          }
      }
      ?>
      <img src="<?= htmlspecialchars($photo) ?>" alt="<?= htmlspecialchars($faculty['name']) ?>" class="faculty-photo">
      <h1 class="faculty-name"><?= htmlspecialchars($faculty['name']) ?></h1>
      <p class="faculty-designation"><?= htmlspecialchars($faculty['designation']) ?></p>
    </div>

    <!-- Content Section -->
    <div class="content-section">
      <!-- Contact Information -->
      <div class="contact-section">
        <div class="info-grid">
          <div class="info-card">
            <h4 style="color: #00A9B4; margin-bottom: 20px;"><i class="fas fa-envelope me-2"></i>Contact Information</h4>
            <div class="info-item">
              <i class="fa-solid fa-envelope"></i>
              <span><?= htmlspecialchars($faculty['email']) ?></span>
            </div>
            <div class="info-item">
              <i class="fa-solid fa-phone"></i>
              <span><?= htmlspecialchars($faculty['phone']) ?></span>
            </div>
            <?php if (!empty($faculty['google_scholar_link'])): ?>
              <div class="info-item">
                <i class="fab fa-google"></i>
                <a href="<?= htmlspecialchars($faculty['google_scholar_link']) ?>" target="_blank" class="scholar-link">
                  Google Scholar Profile
                </a>
              </div>
            <?php endif; ?>
          </div>
          
          <div class="info-card">
            <h4 style="color: #00A9B4; margin-bottom: 20px;"><i class="fas fa-graduation-cap me-2"></i>Academic Information</h4>
            <div class="info-item">
              <i class="fas fa-user-graduate"></i>
              <span><strong>Qualification:</strong> <?= htmlspecialchars($faculty['qualification']) ?></span>
            </div>
            <div class="info-item">
              <i class="fas fa-cogs"></i>
              <span><strong>Specialization:</strong> <?= htmlspecialchars($faculty['specialization']) ?></span>
            </div>
          </div>
        </div>
      </div>

      <!-- About Section -->
      <?php if (!empty($faculty['bio'])): ?>
        <h2 class="section-title">About</h2>
        <div class="experience-card">
          <div class="experience-content"><?= nl2br(htmlspecialchars($faculty['bio'])) ?></div>
        </div>
      <?php endif; ?>

      <!-- Research Experience -->
      <?php if (!empty($faculty['research_experience'])): ?>
        <h2 class="section-title">Research Experience</h2>
        <div class="experience-card">
          <div class="experience-title"><i class="fas fa-microscope"></i>Research Experience</div>
          <div class="experience-content"><?= nl2br(htmlspecialchars($faculty['research_experience'])) ?></div>
        </div>
      <?php endif; ?>

      <!-- Teaching Experience -->
      <?php if (!empty($faculty['teaching_experience'])): ?>
        <h2 class="section-title">Teaching Experience</h2>
        <div class="experience-card">
          <div class="experience-title"><i class="fas fa-chalkboard-teacher"></i>Teaching Experience</div>
          <div class="experience-content"><?= nl2br(htmlspecialchars($faculty['teaching_experience'])) ?></div>
        </div>
      <?php endif; ?>

      <!-- Industrial Experience -->
      <?php if (!empty($faculty['industrial_experience'])): ?>
        <h2 class="section-title">Industrial Experience</h2>
        <div class="experience-card">
          <div class="experience-title"><i class="fas fa-industry"></i>Industrial Experience</div>
          <div class="experience-content"><?= nl2br(htmlspecialchars($faculty['industrial_experience'])) ?></div>
        </div>
      <?php endif; ?>

      <!-- Subjects and Tags -->
      <?php if (!empty($faculty['subjects_undertaken']) || !empty($faculty['tags'])): ?>
        <div class="row">
          <?php if (!empty($faculty['subjects_undertaken'])): ?>
            <div class="col-md-6">
              <h3 class="section-title">Subjects Undertaken</h3>
              <div class="tags-container">
                <?php
                $subjects = explode(',', $faculty['subjects_undertaken']);
                foreach ($subjects as $subject) {
                    echo '<span class="subject-item">' . htmlspecialchars(trim($subject)) . '</span>';
                }
                ?>
              </div>
            </div>
          <?php endif; ?>

          <?php if (!empty($faculty['tags'])): ?>
            <div class="col-md-6">
              <h3 class="section-title">Expertise Tags</h3>
              <div class="tags-container">
                <?php
                $tags = explode(',', $faculty['tags']);
                foreach ($tags as $tag) {
                    echo '<span class="tag-item">' . htmlspecialchars(trim($tag)) . '</span>';
                }
                ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
