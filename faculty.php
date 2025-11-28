<?php
include 'header.php';
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Faculty Information | Academic Portal</title>

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(120deg, #f7fafd 60%, #e0f7fa 100%);
      color: #222;
    }
    .header {
      background: linear-gradient(rgba(0,169,180,0.7), rgba(0,140,151,0.7)), url('assets/faculty-bg.jpg') center/cover no-repeat;
      color: white;
      text-align: center;
      padding: 90px 20px 60px 20px;
      border-bottom-left-radius: 30px;
      border-bottom-right-radius: 30px;
      box-shadow: 0 2px 18px rgba(0,169,180,0.08);
    }
    .section-title {
      color: #00A9B4;
      font-weight: 700;
      text-align: center;
      margin: 40px 0 20px;
      letter-spacing: 1px;
    }
    .faculty-card {
      border-radius: 18px;
      box-shadow: 0 6px 24px rgba(0,169,180,0.08);
      border: none;
      transition: transform 0.2s, box-shadow 0.2s;
      background: #fff;
      overflow: hidden;
    }
    .faculty-card:hover {
      transform: scale(1.03);
      box-shadow: 0 12px 32px rgba(0,169,180,0.14);
    }
    .faculty-card img {
      border-top-left-radius: 18px;
      border-top-right-radius: 18px;
      height: 220px;
      width: 100%;
      object-fit: cover;
      box-shadow: 0 2px 12px rgba(0,169,180,0.08);
    }
    .faculty-card .card-body {
      padding: 1.5rem 1rem;
    }
    .card-title {
      color: #00A9B4;
      font-weight: 600;
      font-size: 1.2rem;
    }
    .text-muted {
      color: #008c97 !important;
      font-weight: 500;
    }
    .faculty-info {
      font-size: 0.98rem;
      color: #444;
      margin-bottom: 0.5rem;
    }
    .faculty-contact {
      font-size: 0.95rem;
      color: #555;
    }
    .faculty-directory-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
    }
    .tag-badge {
      background: linear-gradient(45deg, #28a745, #20c997);
      color: white;
      padding: 3px 8px;
      border-radius: 10px;
      font-size: 0.75rem;
      margin: 1px;
      display: inline-block;
    }
    .scholar-link {
      color: #4285f4;
      text-decoration: none;
      font-size: 0.9rem;
    }
    .scholar-link:hover {
      color: #1a73e8;
      text-decoration: underline;
    }
    .experience-section {
      background: #f8f9fa;
      border-radius: 8px;
      padding: 10px;
      margin: 8px 0;
    }
    .experience-title {
      font-weight: 600;
      color: #00A9B4;
      font-size: 0.9rem;
      margin-bottom: 5px;
    }
    .subjects-list {
      font-size: 0.85rem;
      color: #666;
    }
    @media (max-width: 768px) {
      .faculty-directory-header {
        flex-direction: column;
        gap: 1rem;
      }
    }
  </style>
</head>
<body>

<!-- Header -->
<div class="container-fluid header">
  <h1>Our Faculty Members</h1>
  <p class="lead">Dedicated, Experienced & Innovative Educators</p>
</div>

<div class="container my-5">
  <h2 class="section-title">Faculty Directory</h2>
  <div class="faculty-directory-header">
    <div>
      <span class="fw-bold" style="font-size:1.1rem; color:#008c97;">Meet our talented educators and researchers</span>
    </div>
  </div>
  
  <!-- Faculty Grid -->
  <div class="row g-4">
    <?php
    $result = $conn->query("SELECT * FROM faculty ORDER BY id DESC");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Check if profile_photo already contains the full path
            if (!empty($row['profile_photo'])) {
                // If it already starts with 'uploads/', use it as is, otherwise prepend 'uploads/'
                $photo = (strpos($row['profile_photo'], 'uploads/') === 0) ? $row['profile_photo'] : 'uploads/' . $row['profile_photo'];
            } else {
                $photo = 'assets/default-avatar.png';
            }
            $tags = !empty($row['tags']) ? explode(',', $row['tags']) : [];
            $subjects = !empty($row['subjects_undertaken']) ? explode(',', $row['subjects_undertaken']) : [];
            
            echo '
            <div class="col-md-4">
              <div class="card faculty-card h-100">
                <img src="' . $photo . '" alt="Faculty Photo">
                <div class="card-body text-center">
                  <h5 class="card-title mb-1">' . htmlspecialchars($row['name']) . '</h5>
                  <p class="text-muted mb-2">' . htmlspecialchars($row['designation']) . '</p>
                  
                  
                  <div class="faculty-info"><strong>Specialization:</strong> ' . htmlspecialchars($row['specialization']) . '</div>
                  
                  <!-- Subjects Undertaken -->
                  <div class="subjects-list mb-2">';
            if (!empty($subjects)) {
                echo '<strong>Subjects:</strong> ' . implode(', ', array_map('htmlspecialchars', $subjects));
            }
            echo '</div>
                  
                  <!-- Tags -->
                  <div class="mb-2">';
            foreach ($tags as $tag) {
                $tag = trim($tag);
                if (!empty($tag)) {
                    echo '<span class="tag-badge">' . htmlspecialchars($tag) . '</span>';
                }
            }
            echo '</div>
                  
                  <div class="faculty-contact"><i class="fa-solid fa-envelope me-2"></i>' . htmlspecialchars($row['email']) . '</div>
                  <div class="faculty-contact"><i class="fa-solid fa-phone me-2"></i>' . htmlspecialchars($row['phone']) . '</div>';
                  
            if (!empty($row['google_scholar_link'])) {
                echo '<div class="faculty-contact">
                        <a href="' . htmlspecialchars($row['google_scholar_link']) . '" target="_blank" class="scholar-link">
                          <i class="fab fa-google me-2"></i>Google Scholar
                        </a>
                      </div>';
            }
            
            // See Experiences Button
            echo '<div class="text-center mt-3">
                    <a href="faculty_detail.php?id=' . $row['id'] . '" class="btn btn-outline-primary btn-sm">
                      <i class="fas fa-eye me-2"></i>See Experiences
                    </a>
                  </div>
                </div>
              </div>
            </div>';
        }
    } else {
        echo '<p class="text-center">No faculty records found.</p>';
    }
    ?>
  </div>
</div>

<?php include 'footer.php'; ?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>