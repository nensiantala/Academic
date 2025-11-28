<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}
include '../db.php';

$id = $_GET['id'] ?? 0;
$achievement = null;

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM achievements WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $achievement = $result->fetch_assoc();
    $stmt->close();
}

if (!$achievement) {
    header('Location: manage_achievements.php');
    exit();
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_achievement'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $category = $_POST['category'];
    $student_name = $_POST['student_name'] ?? null;
    $faculty_name = $_POST['faculty_name'] ?? null;
    $achievement_type = $_POST['achievement_type'] ?? null;
    
    // Handle image upload
    $image = $achievement['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = '../uploads/achievements/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $imagePath = $uploadDir . $imageName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            $image = $imageName;
        }
    }
    
    $stmt = $conn->prepare("UPDATE achievements SET title=?, description=?, date=?, category=?, student_name=?, faculty_name=?, achievement_type=?, image=? WHERE id=?");
    $stmt->bind_param("ssssssssi", $title, $description, $date, $category, $student_name, $faculty_name, $achievement_type, $image, $id);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Achievement updated successfully!'); window.location.href='manage_achievements.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Achievement | Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="admin.css">
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
    <h2><i class="fas fa-edit me-2"></i>Edit Achievement</h2>
    <a href="manage_achievements.php" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left me-2"></i>Back to Achievements
    </a>
  </div>

  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card">
        <div class="card-body">
          <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="update_achievement" value="1">
            
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Title *</label>
                  <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($achievement['title']) ?>" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Category *</label>
                  <select name="category" class="form-control" required onchange="toggleNameFields(this.value)">
                    <option value="">Select Category</option>
                    <option value="student" <?= $achievement['category'] == 'student' ? 'selected' : '' ?>>Student Achievement</option>
                    <option value="faculty" <?= $achievement['category'] == 'faculty' ? 'selected' : '' ?>>Faculty Achievement</option>
                  </select>
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3" id="studentNameField" style="display: <?= $achievement['category'] == 'student' ? 'block' : 'none' ?>;">
                  <label class="form-label">Student Name</label>
                  <input type="text" name="student_name" class="form-control" value="<?= htmlspecialchars($achievement['student_name'] ?? '') ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3" id="facultyNameField" style="display: <?= $achievement['category'] == 'faculty' ? 'block' : 'none' ?>;">
                  <label class="form-label">Faculty Name</label>
                  <input type="text" name="faculty_name" class="form-control" value="<?= htmlspecialchars($achievement['faculty_name'] ?? '') ?>">
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Achievement Type</label>
                  <input type="text" name="achievement_type" class="form-control" value="<?= htmlspecialchars($achievement['achievement_type'] ?? '') ?>" placeholder="e.g., Research, Competition, Award">
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Date *</label>
                  <input type="date" name="date" class="form-control" value="<?= $achievement['date'] ?>" required>
                </div>
              </div>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Description *</label>
              <textarea name="description" class="form-control" rows="4" required><?= htmlspecialchars($achievement['description']) ?></textarea>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Current Image</label>
              <?php if (!empty($achievement['image'])): ?>
                <div class="mb-2">
                  <img src="../uploads/achievements/<?= htmlspecialchars($achievement['image']) ?>" alt="Current Image" style="max-width: 200px; max-height: 150px; border-radius: 8px;">
                </div>
              <?php endif; ?>
              <label class="form-label">Update Image</label>
              <input type="file" name="image" class="form-control" accept="image/*">
              <small class="text-muted">Leave empty to keep current image</small>
            </div>
            
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-admin">
                <i class="fas fa-save me-2"></i>Update Achievement
              </button>
              <a href="manage_achievements.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

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
</script>
</body>
</html>
