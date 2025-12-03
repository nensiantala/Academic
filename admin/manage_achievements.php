<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}
include '../db.php';

// Handle add achievement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_achievement'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $category = $_POST['category'];
    $student_name = $_POST['student_name'] ?? null;
    $faculty_name = $_POST['faculty_name'] ?? null;
    $achievement_type = $_POST['achievement_type'] ?? null;
    
    // Handle image upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = '../uploads/achievements/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = uniqid() . '_' . time() . '.' . $ext;
        $imagePath = $uploadDir . $imageName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            $image = 'uploads/achievements/' . $imageName;
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO achievements (title, description, date, category, student_name, faculty_name, achievement_type, image, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssssssss", $title, $description, $date, $category, $student_name, $faculty_name, $achievement_type, $image);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Achievement added successfully!'); window.location.href='manage_achievements.php';</script>";
}

// Handle edit achievement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_achievement'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $category = $_POST['category'];
    $student_name = $_POST['student_name'] ?? null;
    $faculty_name = $_POST['faculty_name'] ?? null;
    $achievement_type = $_POST['achievement_type'] ?? null;
    
    // Handle image upload
    $image = $_POST['current_image'] ?? null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = '../uploads/achievements/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        // Delete old image if exists
        if (!empty($image) && $image !== 'assets/img/default-achievement.jpg') {
            $oldImageValue = trim($image);
            // Handle different path formats when deleting old image
            if (strpos($oldImageValue, 'uploads/achievements/') !== false || strpos($oldImageValue, 'uploads/') === 0) {
                $oldPath = '../' . $oldImageValue;
            } elseif (strpos($oldImageValue, 'achievements/') !== false) {
                $oldPath = '../uploads/' . $oldImageValue;
            } else {
                $oldPath = '../uploads/achievements/' . $oldImageValue;
            }
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = uniqid() . '_' . time() . '.' . $ext;
        $imagePath = $uploadDir . $imageName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            $image = 'uploads/achievements/' . $imageName;
        }
    }
    
    $stmt = $conn->prepare("UPDATE achievements SET title=?, description=?, date=?, category=?, student_name=?, faculty_name=?, achievement_type=?, image=? WHERE id=?");
    $stmt->bind_param("ssssssssi", $title, $description, $date, $category, $student_name, $faculty_name, $achievement_type, $image, $id);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Achievement updated successfully!'); window.location.href='manage_achievements.php';</script>";
}

// Handle delete achievement
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM achievements WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Achievement deleted successfully!'); window.location.href='manage_achievements.php';</script>";
}

// Fetch achievements
$result = $conn->query("SELECT * FROM achievements ORDER BY date DESC, created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Achievements | Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="admin.css">
  <style>
    .achievement-card {
      border: 1px solid #e0e0e0;
      border-radius: 10px;
      transition: all 0.3s ease;
    }
    .achievement-card:hover {
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .category-badge {
      font-size: 0.8rem;
      padding: 4px 8px;
    }
    .student-badge {
      background: linear-gradient(45deg, #28a745, #20c997);
    }
    .faculty-badge {
      background: linear-gradient(45deg, #007bff, #6610f2);
    }
    .achievement-image {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 8px;
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
    <li><a href="manage_achievements.php" class="active"><i class="fas fa-trophy"></i>Achievements</a></li>
    <li><a href="manage_clubs.php"><i class="fas fa-users"></i>Clubs</a></li>
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
    <h2><i class="fas fa-trophy me-2"></i>Manage Achievements</h2>
    <button class="btn btn-admin" data-bs-toggle="modal" data-bs-target="#addAchievementModal">
      <i class="fa fa-plus me-2"></i>Add Achievement
    </button>
  </div>

  <div class="row">
    <?php if($result && $result->num_rows): while($a = $result->fetch_assoc()): ?>
      <div class="col-md-6 mb-3">
        <div class="card achievement-card p-3">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h6 class="mb-0"><?=htmlspecialchars($a['title'])?></h6>
            <span class="badge category-badge <?= $a['category'] == 'student' ? 'student-badge' : 'faculty-badge' ?>">
              <?= ucfirst($a['category']) ?>
            </span>
          </div>
          
          <div class="d-flex align-items-center mb-2">
            <?php 
            // Handle image path - check if it's already a full path or just filename
            $img = '';
            if (!empty($a['image'])) {
                $imageValue = trim($a['image']);
                // If it already contains 'uploads/achievements/', use it as is
                if (strpos($imageValue, 'uploads/achievements/') !== false || strpos($imageValue, 'uploads/') === 0) {
                    $img = '../' . $imageValue;
                } 
                // If it contains 'achievements/', prepend '../uploads/'
                elseif (strpos($imageValue, 'achievements/') !== false) {
                    $img = '../uploads/' . $imageValue;
                }
                // Otherwise, prepend '../uploads/achievements/'
                else {
                    $img = '../uploads/achievements/' . $imageValue;
                }
                
                // Verify file exists
                if (!file_exists($img)) {
                    $img = ''; // Don't show image if file doesn't exist
                }
            }
            if (!empty($img)): ?>
              <img src="<?= htmlspecialchars($img) ?>" class="achievement-image me-3" alt="Achievement">
            <?php endif; ?>
            <div class="flex-grow-1">
              <p class="text-muted small mb-1"><?=date('d M Y', strtotime($a['date']))?></p>
              <?php if ($a['category'] == 'student' && !empty($a['student_name'])): ?>
                <p class="text-primary small mb-1"><strong>Student: <?= htmlspecialchars($a['student_name']) ?></strong></p>
              <?php elseif ($a['category'] == 'faculty' && !empty($a['faculty_name'])): ?>
                <p class="text-info small mb-1"><strong>Faculty: <?= htmlspecialchars($a['faculty_name']) ?></strong></p>
              <?php endif; ?>
              <?php if (!empty($a['achievement_type'])): ?>
                <p class="text-success small mb-1"><em><?= htmlspecialchars($a['achievement_type']) ?></em></p>
              <?php endif; ?>
            </div>
          </div>
          
          <p class="text-muted small"><?=htmlspecialchars(substr($a['description'], 0, 100))?>...</p>
          
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-primary" onclick="editAchievement(<?= $a['id'] ?>)">
              <i class="fas fa-edit"></i> Edit
            </button>
            <a href="?delete=<?= $a['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
              <i class="fas fa-trash"></i> Delete
            </a>
          </div>
        </div>
      </div>
    <?php endwhile; else: ?>
      <div class="col-12">
        <div class="text-center py-5">
          <i class="fas fa-trophy fa-3x text-muted mb-3"></i>
          <p class="text-muted">No achievements found.</p>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Add Achievement Modal -->
<div class="modal fade" id="addAchievementModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-plus me-2"></i>Add New Achievement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="add_achievement" value="1">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-control" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Category *</label>
                <select name="category" class="form-control" required onchange="toggleNameFields(this.value)">
                  <option value="">Select Category</option>
                  <option value="student">Student Achievement</option>
                  <option value="faculty">Faculty Achievement</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3" id="studentNameField" style="display: none;">
                <label class="form-label">Student Name</label>
                <input type="text" name="student_name" class="form-control">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3" id="facultyNameField" style="display: none;">
                <label class="form-label">Faculty Name</label>
                <input type="text" name="faculty_name" class="form-control">
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Achievement Type</label>
                <input type="text" name="achievement_type" class="form-control" placeholder="e.g., Research, Competition, Award">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Date *</label>
                <input type="date" name="date" class="form-control" required>
              </div>
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Description *</label>
            <textarea name="description" class="form-control" rows="4" required></textarea>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Image</label>
            <input type="file" name="image" class="form-control" accept="image/*">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-admin">Add Achievement</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Achievement Modal -->
<div class="modal fade" id="editAchievementModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-edit me-2"></i>Edit Achievement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" enctype="multipart/form-data" id="editForm">
        <input type="hidden" name="edit_achievement" value="1">
        <input type="hidden" name="id" id="editId">
        <input type="hidden" name="current_image" id="currentImage">
        <div class="modal-body" id="editModalBody">
          <!-- Content will be loaded dynamically -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-admin">Update Achievement</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleNameFields(category) {
    const studentField = document.getElementById('studentNameField');
    const facultyField = document.getElementById('facultyNameField');
    
    if (category === 'student') {
        studentField.style.display = 'block';
        facultyField.style.display = 'none';
    } else if (category === 'faculty') {
        studentField.style.display = 'none';
        facultyField.style.display = 'block';
    } else {
        studentField.style.display = 'none';
        facultyField.style.display = 'none';
    }
}

function editAchievement(id) {
    // This would typically fetch data via AJAX
    // For now, we'll redirect to a separate edit page or use a simpler approach
    window.location.href = 'edit_achievement.php?id=' + id;
}
</script>
</body>
</html>
