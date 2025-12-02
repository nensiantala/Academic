<?php
include 'header.php';
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Achievements | Academic Department</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .achievement-card {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      border: none;
      border-radius: 15px;
      overflow: hidden;
    }
    .achievement-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    .category-badge {
      position: absolute;
      top: 10px;
      right: 10px;
      z-index: 2;
    }
    .student-badge {
      background: linear-gradient(45deg, #28a745, #20c997);
    }
    .faculty-badge {
      background: linear-gradient(45deg, #007bff, #6610f2);
    }
    .achievement-image {
      height: 220px;
      object-fit: cover;
      transition: transform 0.3s ease;
    }
    .achievement-card:hover .achievement-image {
      transform: scale(1.05);
    }
    .filter-buttons {
      margin-bottom: 30px;
    }
    .filter-btn {
      margin: 0 5px;
      border-radius: 25px;
      padding: 8px 20px;
    }
    .filter-btn.active {
      background: linear-gradient(45deg, #00a9b4, #008c97);
      border-color: #00a9b4;
    }
  </style>
</head>
<body>
<div class="container my-5">
  <h2 class="section-title text-center mb-4">Our Achievements</h2>
  
  <!-- Filter Buttons -->
  <div class="text-center filter-buttons">
    <button class="btn btn-outline-primary filter-btn active" data-filter="all">All Achievements</button>
    <button class="btn btn-outline-success filter-btn" data-filter="student">Student Achievements</button>
    <button class="btn btn-outline-info filter-btn" data-filter="faculty">Faculty Achievements</button>
  </div>

  <div class="row g-4" id="achievementsContainer">
    <?php
    $result = $conn->query("SELECT * FROM achievements ORDER BY date DESC, created_at DESC");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Handle image path - check if it's already a full path or just filename
            $img = 'assets/img/default-achievement.jpg';
            if (!empty($row['image'])) {
                $imageValue = trim($row['image']);
                // If it already contains 'uploads/achievements/', use it as is
                if (strpos($imageValue, 'uploads/achievements/') !== false) {
                    $img = $imageValue;
                } 
                // If it starts with 'uploads/', use it as is
                elseif (strpos($imageValue, 'uploads/') === 0) {
                    $img = $imageValue;
                }
                // Otherwise, prepend 'uploads/achievements/'
                else {
                    $img = 'uploads/achievements/' . $imageValue;
                }
                
                // Verify file exists, otherwise use default
                if (!file_exists($img)) {
                    $img = 'assets/img/default-achievement.jpg';
                }
            }
            
            $category = $row['category'];
            $name = $category == 'student' ? $row['student_name'] : $row['faculty_name'];
            $badgeClass = $category == 'student' ? 'student-badge' : 'faculty-badge';
            $badgeText = $category == 'student' ? 'Student' : 'Faculty';
            
            echo '
            <div class="col-md-4 achievement-item" data-category="' . $category . '">
              <div class="card achievement-card p-0 shadow-sm h-100">
                <div class="position-relative">
                  <img src="' . $img . '" class="achievement-image w-100" alt="Achievement Image">
                  <span class="badge category-badge ' . $badgeClass . '">' . $badgeText . '</span>
                </div>
                <div class="card-body p-3">
                  <h5 class="card-title text-theme fw-semibold">' . htmlspecialchars($row['title']) . '</h5>
                  <p class="text-muted small mb-2">' . date('d M Y', strtotime($row['date'])) . '</p>';
                  
            if (!empty($name)) {
                echo '<p class="text-primary small mb-2"><strong>' . htmlspecialchars($name) . '</strong></p>';
            }
            
            if (!empty($row['achievement_type'])) {
                echo '<p class="text-info small mb-2"><em>' . htmlspecialchars($row['achievement_type']) . '</em></p>';
            }
            
            echo '<p class="card-text text-muted">' . htmlspecialchars(substr($row['description'], 0, 100)) . '...</p>
                </div>
              </div>
            </div>';
        }
    } else {
        echo '<div class="col-12"><p class="text-center">No achievements found.</p></div>';
    }
    ?>
  </div>
</div>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const achievementItems = document.querySelectorAll('.achievement-item');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            const filter = this.getAttribute('data-filter');
            
            achievementItems.forEach(item => {
                if (filter === 'all' || item.getAttribute('data-category') === filter) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});
</script>
</body>
</html>
