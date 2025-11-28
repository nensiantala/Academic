<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}
include '../db.php';

$id = $_GET['id'] ?? 0;
$faculty = null;

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM faculty WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $faculty = $result->fetch_assoc();
    $stmt->close();
}

if (!$faculty) {
    header('Location: manage_faculty.php');
    exit();
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_faculty'])) {
    $name = $_POST['name'];
    $designation = $_POST['designation'];
    $qualification = $_POST['qualification'];
    $specialization = $_POST['specialization'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $bio = $_POST['bio'];
    $research_experience = $_POST['research_experience'];
    $teaching_experience = $_POST['teaching_experience'];
    $industrial_experience = $_POST['industrial_experience'];
    $google_scholar_link = $_POST['google_scholar_link'];
    $subjects_undertaken = $_POST['subjects_undertaken'];
    $tags = $_POST['tags'];
    
    // Handle image upload
    $photo = $faculty['profile_photo'];
    if (!empty($_FILES['profile_photo']['name'])) {
        $uploadDir = '../uploads/faculty/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $imageName = time() . '_' . basename($_FILES['profile_photo']['name']);
        $target = $uploadDir . $imageName;
        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target)) {
            $photo = 'uploads/faculty/' . $imageName;
        }
    }
    
    $stmt = $conn->prepare("UPDATE faculty SET name=?, designation=?, qualification=?, specialization=?, email=?, phone=?, profile_photo=?, bio=?, research_experience=?, teaching_experience=?, industrial_experience=?, google_scholar_link=?, subjects_undertaken=?, tags=? WHERE id=?");
    $stmt->bind_param("ssssssssssssssi", $name, $designation, $qualification, $specialization, $email, $phone, $photo, $bio, $research_experience, $teaching_experience, $industrial_experience, $google_scholar_link, $subjects_undertaken, $tags, $id);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Faculty updated successfully!'); window.location.href='manage_faculty.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Faculty | Admin Panel</title>
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
    <li><a href="manage_faculty.php" class="active"><i class="fas fa-chalkboard-teacher"></i>Faculty</a></li>
    <li><a href="manage_events.php"><i class="fas fa-calendar-alt"></i>Events</a></li>
    <li><a href="manage_achievements.php"><i class="fas fa-trophy"></i>Achievements</a></li>
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
    <h2><i class="fas fa-user-edit me-2"></i>Edit Faculty</h2>
    <a href="manage_faculty.php" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left me-2"></i>Back to Faculty
    </a>
  </div>

  <div class="row justify-content-center">
    <div class="col-md-10">
      <div class="card">
        <div class="card-body">
          <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="update_faculty" value="1">
            
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Full Name *</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($faculty['name']) ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Designation *</label>
                <input type="text" name="designation" class="form-control" value="<?= htmlspecialchars($faculty['designation']) ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Qualification *</label>
                <input type="text" name="qualification" class="form-control" value="<?= htmlspecialchars($faculty['qualification']) ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Specialization *</label>
                <input type="text" name="specialization" class="form-control" value="<?= htmlspecialchars($faculty['specialization']) ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($faculty['email']) ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Phone *</label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($faculty['phone']) ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Current Photo</label>
                <?php if (!empty($faculty['profile_photo'])): ?>
                  <div class="mb-2">
                    <img src="../<?= htmlspecialchars($faculty['profile_photo']) ?>" alt="Current Photo" style="max-width: 150px; max-height: 150px; border-radius: 8px;">
                  </div>
                <?php endif; ?>
                <label class="form-label">Update Photo</label>
                <input type="file" name="profile_photo" class="form-control" accept="image/*">
                <small class="text-muted">Leave empty to keep current photo</small>
              </div>
              <div class="col-md-6">
                <label class="form-label">Google Scholar Link</label>
                <input type="url" name="google_scholar_link" class="form-control" value="<?= htmlspecialchars($faculty['google_scholar_link'] ?? '') ?>" placeholder="https://scholar.google.com/...">
              </div>
              <div class="col-12">
                <label class="form-label">Bio</label>
                <textarea name="bio" class="form-control" rows="3"><?= htmlspecialchars($faculty['bio'] ?? '') ?></textarea>
              </div>
              <div class="col-12">
                <label class="form-label">Research Experience</label>
                <textarea name="research_experience" class="form-control" rows="3" placeholder="Describe research experience, publications, projects..."><?= htmlspecialchars($faculty['research_experience'] ?? '') ?></textarea>
              </div>
              <div class="col-12">
                <label class="form-label">Teaching Experience</label>
                <textarea name="teaching_experience" class="form-control" rows="3" placeholder="Describe teaching experience, courses taught..."><?= htmlspecialchars($faculty['teaching_experience'] ?? '') ?></textarea>
              </div>
              <div class="col-12">
                <label class="form-label">Industrial Experience</label>
                <textarea name="industrial_experience" class="form-control" rows="3" placeholder="Describe industry experience, companies worked for..."><?= htmlspecialchars($faculty['industrial_experience'] ?? '') ?></textarea>
              </div>
              <div class="col-12">
                <label class="form-label">Subjects Undertaken</label>
                <input type="text" name="subjects_undertaken" class="form-control" value="<?= htmlspecialchars($faculty['subjects_undertaken'] ?? '') ?>" placeholder="Separate multiple subjects with commas (e.g., Data Structures, Algorithms, Machine Learning)">
              </div>
              <div class="col-12">
                <label class="form-label">Tags</label>
                <input type="text" name="tags" class="form-control" value="<?= htmlspecialchars($faculty['tags'] ?? '') ?>" placeholder="Separate multiple tags with commas (e.g., AI, Machine Learning, Research, Industry Expert)">
              </div>
            </div>
            
            <div class="d-flex gap-2 mt-4">
              <button type="submit" class="btn btn-admin">
                <i class="fas fa-save me-2"></i>Update Faculty
              </button>
              <a href="manage_faculty.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



