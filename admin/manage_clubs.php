<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}
include '../db.php';

$upload_dir = '../uploads/clubs/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Handle delete club
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_club'])) {
    $club_id = intval($_POST['club_id']);
    
    // Fetch images_json from clubs table
    $stmt = $conn->prepare("SELECT images_json FROM clubs WHERE id = ?");
    $stmt->bind_param("i", $club_id);
    $stmt->execute();
    $stmt->bind_result($images_json);
    $stmt->fetch();
    $stmt->close();

    // Decode JSON and delete files
    if ($images_json) {
        $images_array = json_decode($images_json, true);
        if (is_array($images_array)) {
            foreach ($images_array as $image_path) {
                if (file_exists('../' . $image_path)) {
                    unlink('../' . $image_path);
                }
            }
        }
    }
    
    // Delete club (cascade will handle club_images)
    $stmt = $conn->prepare("DELETE FROM clubs WHERE id = ?");
    $stmt->bind_param("i", $club_id);
    $stmt->execute();
    $stmt->close();
    
    header('Location: manage_clubs.php?success=deleted');
    exit();
}

// Handle add club
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_club'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $coordinator = $_POST['coordinator'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $category = $_POST['category'] ?? 'non-tech';
    
    $stmt = $conn->prepare("INSERT INTO clubs (name, description, coordinator, contact, category) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $description, $coordinator, $contact, $category);
    
    if ($stmt->execute()) {
        $new_club_id = $conn->insert_id;
        
        // Handle multiple image uploads
        if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0) {
            $images_array = [];
            foreach ($_FILES['images']['name'] as $key => $name) {
                if ($_FILES['images']['error'][$key] == UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES['images']['tmp_name'][$key];
                    $ext = pathinfo($name, PATHINFO_EXTENSION);
                    $new_filename = uniqid() . '_' . time() . '.' . $ext;
                    $upload_path = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($tmp_name, $upload_path)) {
                        $image_path = 'uploads/clubs/' . $new_filename;
                        $images_array[] = $image_path;
                    }
                }
            }
            if (!empty($images_array)) {
                $images_json = json_encode($images_array);
                $stmt = $conn->prepare("UPDATE clubs SET images_json = ? WHERE id = ?");
                $stmt->bind_param("si", $images_json, $new_club_id);
                $stmt->execute();
                $stmt->close();
            }
        }
        
        header('Location: manage_clubs.php?success=added');
        exit();
    }
    $stmt->close();
}

// Fetch clubs with image counts
$result = $conn->query("SELECT * FROM clubs ORDER BY category, name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Clubs | Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="admin.css">
  <style>
    .club-card {
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      transition: transform 0.2s;
    }
    .club-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    .image-preview-container {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-top: 10px;
    }
    .image-preview {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 5px;
      border: 2px solid #ddd;
    }
    .category-tech { background: linear-gradient(45deg, #667eea 0%, #764ba2 100%); }
    .category-non-tech { background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%); }
  </style>
</head>
<body>

<div class="admin-sidebar">
  <h3><i class="fas fa-cogs me-2"></i>Admin Panel</h3>
  <ul>
    <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
    <li><a href="manage_faculty.php"><i class="fas fa-chalkboard-teacher"></i>Faculty</a></li>
    <li><a href="manage_events.php"><i class="fas fa-calendar-alt"></i>Events</a></li>
    <li><a href="manage_achievements.php"><i class="fas fa-trophy"></i>Achievements</a></li>
    <li><a href="manage_clubs.php" class="active"><i class="fas fa-users"></i>Clubs</a></li>
    <li><a href="manage_news.php"><i class="fas fa-newspaper"></i>News</a></li>
    <li><a href="manage_students.php"><i class="fas fa-user-graduate"></i>Students</a></li>
    <li><a href="manage_labs.php"><i class="fas fa-flask"></i>Labs</a></li>
    <li><a href="manage_notices.php"><i class="fas fa-bullhorn"></i>Notices</a></li>
    <li><a href="manage_placement.php"><i class="fas fa-briefcase"></i>Placements</a></li>
    <li><a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
  </ul>
</div>

<div class="admin-main">
  <div class="admin-header mb-4">
    <h2>Manage Clubs</h2>
  </div>
  
  <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i>
      Club <?php $success_msg = ['added' => 'added', 'updated' => 'updated', 'deleted' => 'deleted']; echo $success_msg[$_GET['success']] ?? 'saved'; ?> successfully!
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  
  <button class="btn btn-theme mb-3" data-bs-toggle="modal" data-bs-target="#addClubModal">
    <i class="fa fa-plus me-2"></i>Add Club
  </button>
  
  <div class="row mt-3">
    <?php if($result && $result->num_rows): while($c = $result->fetch_assoc()): 
      // Fetch images_json from clubs table
      $images_json = $c['images_json'];
      $images_array = json_decode($images_json, true);
    ?>
      <div class="col-md-6 mb-3">
        <div class="card club-card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
              <div class="flex-grow-1">
                <h6 class="mb-2">
                  <?= htmlspecialchars($c['name']) ?>
                  <?php if (!empty($images_array) && count($images_array) > 0): ?>
                    <span class="badge bg-info"><i class="fas fa-images"></i> <?= count($images_array) ?></span>
                  <?php endif; ?>
                </h6>
                <span class="badge category-<?= htmlspecialchars($c['category']) ?> text-white mb-2">
                  <?= strtoupper($c['category']) ?>
                </span>
                <?php if (!empty($c['coordinator'])): ?>
                  <p class="text-muted small mb-1"><i class="fas fa-user"></i> <?= htmlspecialchars($c['coordinator']) ?></p>
                <?php endif; ?>
                <?php if (!empty($c['contact'])): ?>
                  <p class="text-muted small mb-1"><i class="fas fa-phone"></i> <?= htmlspecialchars($c['contact']) ?></p>
                <?php endif; ?>
                <p class="text-muted small"><?= htmlspecialchars(substr($c['description'], 0, 100)) ?><?= strlen($c['description']) > 100 ? '...' : '' ?></p>
                
                <!-- Preview Images -->
                <?php if (!empty($images_array)): ?>
                  <div class="d-flex gap-2 flex-wrap mt-2">
                    <?php foreach ($images_array as $image_path): ?>
                      <img src="../<?= htmlspecialchars($image_path) ?>" alt="Club Image" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd;">
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
              
              <div class="d-flex gap-2">
                <a href="edit_club.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-primary">
                  <i class="fas fa-edit"></i>
                </a>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this club?');">
                  <input type="hidden" name="delete_club" value="1">
                  <input type="hidden" name="club_id" value="<?= $c['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-danger">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endwhile; else: ?>
      <div class="col-12">
        <p class="text-muted">No clubs found.</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Add Club Modal -->
<div class="modal fade" id="addClubModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background-color:#00A9B4; color:white;">
        <h5 class="modal-title"><i class="fa fa-plus me-2"></i>Add New Club</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="add_club" value="1">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Club Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Category <span class="text-danger">*</span></label>
              <select name="category" class="form-control" required>
                <option value="tech">Tech</option>
                <option value="non-tech">Non-Tech</option>
              </select>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Coordinator</label>
              <input type="text" name="coordinator" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Contact</label>
              <input type="text" name="contact" class="form-control">
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Description <span class="text-danger">*</span></label>
            <textarea name="description" class="form-control" rows="3" required></textarea>
          </div>
          
          <div class="mb-3">
            <label class="form-label"><i class="fas fa-images me-1"></i>Images (Multiple allowed)</label>
            <input type="file" name="images[]" class="form-control" accept="image/*" multiple id="imageInput">
            <div class="form-text">You can select multiple images</div>
            <div class="image-preview-container" id="imagePreview"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-plus me-2"></i>Add Club
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Image preview
  document.getElementById('imageInput').addEventListener('change', function(e) {
    const container = document.getElementById('imagePreview');
    container.innerHTML = '';
    
    if (this.files) {
      Array.from(this.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
          const img = document.createElement('img');
          img.src = e.target.result;
          img.className = 'image-preview';
          container.appendChild(img);
        };
        reader.readAsDataURL(file);
      });
    }
  });
  
  // Auto-hide success message
  setTimeout(function() {
    const alert = document.querySelector('.alert-success');
    if (alert) {
      alert.style.transition = 'opacity 0.5s';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 500);
    }
  }, 3000);
</script>

</body>
</html>
