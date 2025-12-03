<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}
include '../db.php';

// Handle add placement (single image only)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_placement'])) {
    $created_at = date('Y-m-d H:i:s');
    $photo = '';
    if (!empty($_FILES['student_photo']['name'])) {
        $targetDir = '../uploads/placements/';
        if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
        $ext = pathinfo($_FILES['student_photo']['name'], PATHINFO_EXTENSION);
        $photo = uniqid() . '_' . time() . '.' . $ext;
        $targetFilePath = $targetDir . $photo;
        move_uploaded_file($_FILES['student_photo']['tmp_name'], $targetFilePath);
    }

    $stmt = $conn->prepare("INSERT INTO placements (student_photo, created_at) VALUES (?, ?)");
    $relativePath = 'placements/' . $photo;
    $stmt->bind_param("ss", $relativePath, $created_at);
    if ($stmt->execute()) {
        echo "<script>alert('Placement added successfully!'); window.location.href='manage_placement.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Handle delete placement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_placement'])) {
    $placement_id = intval($_POST['placement_id'] ?? 0);
    if ($placement_id > 0) {
        $res = $conn->query("SELECT student_photo FROM placements WHERE id = $placement_id");
        if ($res && ($row = $res->fetch_assoc())) {
            $photo = $row['student_photo'] ?? '';
            if (!empty($photo)) {
                $imageValue = trim($photo);
                // Handle different path formats
                if (strpos($imageValue, 'uploads/placements/') !== false || strpos($imageValue, 'uploads/') === 0) {
                    $path = '../' . $imageValue;
                } elseif (strpos($imageValue, 'placements/') !== false) {
                    $path = '../uploads/' . $imageValue;
                } else {
                    $path = '../uploads/placements/' . $imageValue;
                }
                if (file_exists($path)) @unlink($path);
            }
        }
        $conn->query("DELETE FROM placements WHERE id = $placement_id");
    }
    header('Location: manage_placement.php');
    exit();
}

// Handle edit placement (replace image optionally)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_placement'])) {
    $placement_id = intval($_POST['placement_id'] ?? 0);
    if ($placement_id > 0) {
        $current = '';
        $res = $conn->query("SELECT student_photo FROM placements WHERE id = $placement_id");
        if ($res && ($r = $res->fetch_assoc())) {
            $current = $r['student_photo'] ?? '';
        }
        $newPhoto = $current;
        if (!empty($_FILES['student_photo']['name'])) {
            $targetDir = '../uploads/placements/';
            if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
            $ext = pathinfo($_FILES['student_photo']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $ext;
            $targetFilePath = $targetDir . $filename;
            if (move_uploaded_file($_FILES['student_photo']['tmp_name'], $targetFilePath)) {
                if (!empty($current)) {
                    $imageValue = trim($current);
                    // Handle different path formats when deleting old image
                    if (strpos($imageValue, 'uploads/placements/') !== false || strpos($imageValue, 'uploads/') === 0) {
                        $oldPath = '../' . $imageValue;
                    } elseif (strpos($imageValue, 'placements/') !== false) {
                        $oldPath = '../uploads/' . $imageValue;
                    } else {
                        $oldPath = '../uploads/placements/' . $imageValue;
                    }
                    if (file_exists($oldPath)) @unlink($oldPath);
                }
                $newPhoto = 'placements/' . $filename;
            }
        }
        $stmt = $conn->prepare("UPDATE placements SET student_photo = ? WHERE id = ?");
        $stmt->bind_param("si", $newPhoto, $placement_id);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: manage_placement.php');
    exit();
}

// Fetch all placements
$result = $conn->query("SELECT * FROM placements ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Placement | Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="admin-sidebar">
  <h3>Admin Panel</h3>
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
    <li><a href="manage_placement.php" class="active"><i class="fas fa-briefcase"></i>Placements</a></li>
    <li><a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
  </ul>
</div>

<div class="admin-main">
  <div class="admin-header mb-4"><h2>Manage Placement</h2></div>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addPlacementModal"><i class="fa fa-plus me-2"></i>Add Placement</button>

    <div class="row mt-3">
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): 
          // Handle image path - check if it's already a full path or just filename
          $img = '../assets/default-avatar.png';
          if (!empty($row['student_photo'])) {
              $imageValue = trim($row['student_photo']);
              // If it already contains 'uploads/placements/', use it as is
              if (strpos($imageValue, 'uploads/placements/') !== false) {
                  $img = '../' . $imageValue;
              } 
              // If it starts with 'uploads/', use it as is
              elseif (strpos($imageValue, 'uploads/') === 0) {
                  $img = '../' . $imageValue;
              }
              // If it contains 'placements/', prepend '../uploads/'
              elseif (strpos($imageValue, 'placements/') !== false) {
                  $img = '../uploads/' . $imageValue;
              }
              // Otherwise, prepend '../uploads/placements/'
              else {
                  $img = '../uploads/placements/' . $imageValue;
              }
              
              // Verify file exists, otherwise use default
              if (!file_exists($img)) {
                  $img = '../assets/default-avatar.png';
              }
          }
        ?>
          <div class="col-md-4 mb-3">
            <div class="card lab-card h-100">
              <div class="card-body">
                <h5 class="mb-3"><i class="fas fa-briefcase me-2"></i>Placement #<?= $row['id'] ?></h5>
                <div class="mb-3 text-center">
                  <img src="<?= htmlspecialchars($img) ?>" alt="Placement" style="max-height:180px;object-fit:cover;border-radius:8px;border:2px solid #ddd;">
                </div>
                <p class="text-muted small mb-3"><i class="fas fa-clock me-1"></i><strong>Created:</strong> <?= htmlspecialchars($row['created_at']) ?></p>
                <div class="d-flex gap-2">
                  <button 
                    type="button" 
                    class="btn btn-sm btn-warning"
                    data-bs-toggle="modal" 
                    data-bs-target="#editPlacementModal"
                    data-id="<?= $row['id'] ?>"
                    data-photo="<?= htmlspecialchars($row['student_photo']) ?>">
                    <i class="fas fa-edit me-1"></i>Edit
                  </button>
                  <form method="POST" onsubmit="return confirm('Delete this placement?');">
                    <input type="hidden" name="delete_placement" value="1">
                    <input type="hidden" name="placement_id" value="<?= $row['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">
                      <i class="fas fa-trash me-1"></i>Delete
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="col-12 text-muted">No placements found.</div>
      <?php endif; ?>
    </div>
</div>

<!-- Add Placement Modal -->
<!-- Add Placement Modal -->
<div class="modal fade" id="addPlacementModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="add_placement" value="1">
        <div class="modal-header">
          <h5 class="modal-title">Add Placement</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Placement Image <span class="text-danger">*</span></label>
            <input type="file" name="student_photo" class="form-control" accept="image/*" required>
            <div class="form-text">Upload a single image that contains all details.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Add Placement</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Placement Modal -->
<div class="modal fade" id="editPlacementModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="edit_placement" value="1">
        <input type="hidden" name="placement_id" id="editPlacementId" value="">
        <div class="modal-header">
          <h5 class="modal-title">Edit Placement Image</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3 text-center">
            <img id="currentPlacementImage" src="" alt="Current" style="max-height:200px;border-radius:8px;display:none;">
          </div>
          <div class="mb-3">
            <label class="form-label">Replace Image</label>
            <input type="file" name="student_photo" class="form-control" accept="image/*">
            <div class="form-text">Leave blank to keep existing image.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-warning">Update</button>
        </div>
      </form>
    </div>
  </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const editModal = document.getElementById('editPlacementModal');
  if (editModal) {
    editModal.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const id = button.getAttribute('data-id');
      const photo = button.getAttribute('data-photo') || '';
      document.getElementById('editPlacementId').value = id;
      const img = document.getElementById('currentPlacementImage');
      if (photo) {
        // Handle path - if it already starts with 'uploads/', use as is, otherwise prepend 'uploads/'
        let imgPath = photo.trim();
        if (imgPath.indexOf('uploads/placements/') === -1 && imgPath.indexOf('uploads/') !== 0) {
          if (imgPath.indexOf('placements/') !== -1) {
            imgPath = 'uploads/' + imgPath;
          } else {
            imgPath = 'uploads/placements/' + imgPath;
          }
        }
        img.src = '../' + imgPath;
        img.style.display = 'block';
      } else {
        img.style.display = 'none';
      }
    });
  }
</script>
</body>
</html>
