<?php
include 'header.php';
include 'db.php';

$result = $conn->query("SELECT * FROM placements ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Placements | Academic Portal</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">

  <style>
    .placement-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      gap: 22px;
      margin-top: 32px;
    }

    .placement-tag {
      position: relative;
      border-radius: 16px;
      overflow: hidden;
      background: #0b3a3c; /* fallback while image loads */
      border: 1px solid rgba(0, 169, 180, 0.18);
      box-shadow: 0 6px 18px rgba(0, 169, 180, 0.15);
      transition: transform 240ms ease, box-shadow 240ms ease;
    }

    .placement-tag:hover {
      transform: translateY(-6px);
      box-shadow: 0 14px 32px rgba(0, 169, 180, 0.25);
    }

    .placement-tag img {
      display: block;
      width: 100%;
      height: auto;
      aspect-ratio: 4 / 5;
      object-fit: cover;
      transform: scale(1.0);
      transition: transform 280ms ease;
    }

    .placement-tag:hover img {
      transform: scale(1.03);
    }

    @media (min-width: 1200px) {
      .placement-grid { gap: 26px; }
      .placement-tag { border-radius: 18px; }
    }
    
    .stats-section {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 40px;
    }
    
    .stat-card {
      background: linear-gradient(135deg, #35b36f 0%, #2a9c62 100%);
      padding: 25px;
      border-radius: 15px;
      text-align: center;
      color: white;
      box-shadow: 0 5px 20px rgba(0,169,180,0.2);
      transition: transform 0.3s;
    }
    
    .stat-card:hover {
      transform: translateY(-5px);
    }
    
    .stat-number {
      font-size: 2.5rem;
      font-weight: 700;
      line-height: 1;
    }
    
    .stat-label {
      font-size: 0.9rem;
      margin-top: 8px;
      opacity: 0.9;
    }
    
    .section-title {
      text-align: center;
      font-size: 2rem;
      font-weight: 700;
      color: #35b36f;
      margin: 50px 0 20px;
    }
  </style>
</head>
<body>

<div class="container py-5">
  
  <!-- Stats Section -->
  <div class="stats-section">
    <div class="stat-card">
      <div class="stat-number">
        <?php
        $total = $conn->query("SELECT COUNT(*) as total FROM placements");
        echo $total ? $total->fetch_assoc()['total'] : '0';
        ?>
      </div>
      <div class="stat-label"><i class="fas fa-users"></i> Successful Placements</div>
    </div>
  </div>

  <!-- Title -->
  <h2 class="section-title">
    <i class="fas fa-trophy me-2"></i>Our Successful Placements
  </h2>
  <p class="text-center text-muted mb-4">Recent placement highlights</p>

  <!-- Placements Grid -->
  <div class="placement-grid">
    <?php
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Handle image path - check if it's already a full path or just filename
            $photo = 'assets/default-avatar.png';
            if (!empty($row['student_photo'])) {
                $imageValue = trim($row['student_photo']);
                // Normalize backslashes to forward slashes for cross-platform compatibility
                $imageValue = str_replace('\\', '/', $imageValue);
                
                // If it already contains 'uploads/placements/', use it as is
                if (strpos($imageValue, 'uploads/placements/') !== false) {
                    $photo = $imageValue;
                } 
                // If it starts with 'uploads/', use it as is
                elseif (strpos($imageValue, 'uploads/') === 0) {
                    $photo = $imageValue;
                }
                // If it contains 'placements/', prepend 'uploads/'
                elseif (strpos($imageValue, 'placements/') !== false) {
                    $photo = 'uploads/' . $imageValue;
                }
                // Otherwise, prepend 'uploads/placements/'
                else {
                    $photo = 'uploads/placements/' . $imageValue;
                }
                
                // Verify file exists, otherwise use default
                if (!file_exists($photo)) {
                    $photo = 'assets/default-avatar.png';
                }
            }

            echo '
            <div class="placement-tag">
              <img src="' . $photo . '" alt="Placement image">
            </div>';
        }
    } else {
        echo '<div class="col-12 text-center py-5">
                <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                <p class="text-muted">No placement records available yet.</p>
              </div>';
    }
    ?>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'footer.php'; ?>
</body>
</html>
