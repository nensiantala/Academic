<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}
include '../db.php';

// Upload directory
$upload_dir = '../uploads/labs/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Handle delete lab
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_lab'])) {
    $lab_id = intval($_POST['lab_id']);

    // Delete images if exist
    $photo_res = $conn->query("SELECT images_json FROM labs WHERE id = $lab_id");
    if ($photo_row = $photo_res->fetch_assoc()) {
        $images = json_decode($photo_row['images_json'], true);
        if (is_array($images)) {
            foreach ($images as $img) {
                $path = '../' . $img;
                if (file_exists($path)) unlink($path);
            }
        }
    }

    // Delete lab
    $stmt = $conn->prepare("DELETE FROM labs WHERE id = ?");
    $stmt->bind_param("i", $lab_id);
    $stmt->execute();
    $stmt->close();

    header('Location: manage_labs.php?success=deleted');
    exit();
}

// Handle add lab
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_lab'])) {
    $lab_name = $_POST['name'];
    $incharge = $_POST['incharge'];
    $timings = $_POST['timings'];
    $equipment = $_POST['equipment'];

    $uploaded_images = [];

    // Handle multiple file upload
    if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0) {
        foreach ($_FILES['images']['name'] as $key => $originalName) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $tmp_name = $_FILES['images']['tmp_name'][$key];
                $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                $new_filename = uniqid() . '_' . time() . '.' . $ext;
                $upload_path = $upload_dir . $new_filename;
                if (move_uploaded_file($tmp_name, $upload_path)) {
                    $uploaded_images[] = 'uploads/labs/' . $new_filename;
                }
            }
        }
    }

    $images_json = json_encode($uploaded_images);

    $stmt = $conn->prepare("INSERT INTO labs (name, incharge, timings, equipment, images_json) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $lab_name, $incharge, $timings, $equipment, $images_json);

    if ($stmt->execute()) {
        header('Location: manage_labs.php?success=added');
        exit();
    }
    $stmt->close();
}

// Fetch labs
$result = $conn->query("SELECT * FROM labs ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Labs | Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="admin.css">
  <style>
    body { font-family: 'Poppins', sans-serif; }
    .lab-card {
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      transition: transform 0.2s;
    }
    .lab-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .image-preview {
      width: 70px;
      height: 70px;
      object-fit: cover;
      border-radius: 5px;
      border: 2px solid #ddd;
    }
    .btn-theme {
      background-color: #00A9B4;
      color: #fff;
    }
    .btn-theme:hover {
      background-color: #008f99;
      color: #fff;
    }
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
    <li><a href="manage_clubs.php"><i class="fas fa-users"></i>Clubs</a></li>
    <li><a href="manage_news.php"><i class="fas fa-newspaper"></i>News</a></li>
    <li><a href="manage_students.php"><i class="fas fa-user-graduate"></i>Students</a></li>
    <li><a href="manage_labs.php" class="active"><i class="fas fa-flask"></i>Labs</a></li>
    <li><a href="manage_notices.php"><i class="fas fa-bullhorn"></i>Notices</a></li>
    <li><a href="manage_placement.php"><i class="fas fa-briefcase"></i>Placements</a></li>
    <li><a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
  </ul>
</div>

<div class="admin-main">
  <div class="admin-header mb-4">
    <h2>Manage Labs</h2>
  </div>

  <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i>
      Lab <?= htmlspecialchars($_GET['success']) ?> successfully!
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <button class="btn btn-theme mb-3" data-bs-toggle="modal" data-bs-target="#addLabModal">
    <i class="fa fa-plus me-2"></i>Add Lab
  </button>

  <div class="row mt-3">
    <?php if ($result && $result->num_rows > 0): ?>
      <?php while ($lab = $result->fetch_assoc()): 
        $images = json_decode($lab['images_json'], true);
        if (!is_array($images)) $images = [];
      ?>
        <div class="col-md-4 mb-3">
          <div class="card lab-card h-100">
            <div class="card-body">
              <h5 class="mb-2"><?= htmlspecialchars($lab['name']) ?></h5>
              <p class="text-muted small mb-1"><i class="fas fa-user"></i> <strong>In-charge:</strong> <?= htmlspecialchars($lab['incharge']) ?></p>
              <p class="text-muted small mb-1"><i class="fas fa-clock"></i> <strong>Timings:</strong> <?= htmlspecialchars($lab['timings']) ?></p>
              <p class="text-muted small mb-3"><i class="fas fa-tools"></i> <strong>Equipment:</strong> <?= htmlspecialchars(substr($lab['equipment'], 0, 80)) ?>...</p>

              <div class="d-flex gap-2 flex-wrap mb-2">
                <?php if (count($images) > 0): ?>
                  <?php foreach ($images as $img): ?>
                    <img src="../<?= htmlspecialchars($img) ?>" class="image-preview" alt="Lab Image">
                  <?php endforeach; ?>
                <?php else: ?>
                  <p class="text-muted small">No images.</p>
                <?php endif; ?>
              </div>

              <div class="d-flex gap-2 mt-3">
                <a href="edit_lab.php?id=<?= $lab['id'] ?>" class="btn btn-sm btn-primary">
                  <i class="fas fa-edit"></i> Edit
                </a>
                <form method="POST" onsubmit="return confirm('Delete this lab?');">
                  <input type="hidden" name="delete_lab" value="1">
                  <input type="hidden" name="lab_id" value="<?= $lab['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-danger">
                    <i class="fas fa-trash"></i> Delete
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="col-12 text-muted">No labs found.</div>
    <?php endif; ?>
  </div>
</div>

<!-- Add Lab Modal -->
<div class="modal fade" id="addLabModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background-color:#00A9B4; color:white;">
        <h5 class="modal-title"><i class="fa fa-plus me-2"></i>Add New Lab</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="add_lab" value="1">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Lab Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">In-charge <span class="text-danger">*</span></label>
              <input type="text" name="incharge" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Timings <span class="text-danger">*</span></label>
              <input type="text" name="timings" class="form-control" required placeholder="e.g., 9 AM - 5 PM">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Equipment <span class="text-danger">*</span></label>
            <textarea name="equipment" class="form-control" rows="3" required></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Images (you can select multiple)</label>
            <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-plus me-2"></i>Add Lab
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Auto-hide success alert
  setTimeout(() => {
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
