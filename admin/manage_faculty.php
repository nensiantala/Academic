<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}
include '../db.php';

// Handle add faculty
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_faculty'])) {
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
    $photo = 'assets/faculty-default.png';
    if (!empty($_FILES['profile_photo']['name'])) {
        $uploadDir = '../uploads/faculty/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
        $imageName = uniqid() . '_' . time() . '.' . $ext;
        $target = $uploadDir . $imageName;
        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target)) {
            $photo = 'uploads/faculty/' . $imageName;
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO faculty (name, designation, qualification, specialization, email, phone, profile_photo, bio, research_experience, teaching_experience, industrial_experience, google_scholar_link, subjects_undertaken, tags, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssssssssssssss", $name, $designation, $qualification, $specialization, $email, $phone, $photo, $bio, $research_experience, $teaching_experience, $industrial_experience, $google_scholar_link, $subjects_undertaken, $tags);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Faculty added successfully!'); window.location.href='manage_faculty.php';</script>";
}

// Handle edit faculty
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_faculty'])) {
    $id = $_POST['id'];
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
    $photo = $_POST['current_photo'];
    if (!empty($_FILES['profile_photo']['name'])) {
        $uploadDir = '../uploads/faculty/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        // Delete old image if exists
        if (!empty($photo) && $photo !== 'assets/faculty-default.png') {
            $oldImageValue = trim($photo);
            // Handle different path formats when deleting old image
            if (strpos($oldImageValue, 'uploads/faculty/') !== false || strpos($oldImageValue, 'uploads/') === 0) {
                $oldPath = '../' . $oldImageValue;
            } elseif (strpos($oldImageValue, 'faculty/') !== false) {
                $oldPath = '../uploads/' . $oldImageValue;
            } else {
                $oldPath = '../uploads/faculty/' . $oldImageValue;
            }
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }
        $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
        $imageName = uniqid() . '_' . time() . '.' . $ext;
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

// Handle delete faculty
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // Delete associated image file
    $stmt = $conn->prepare("SELECT profile_photo FROM faculty WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $photo = $row['profile_photo'] ?? '';
        if (!empty($photo) && $photo !== 'assets/faculty-default.png') {
            $imageValue = trim($photo);
            // Handle different path formats
            if (strpos($imageValue, 'uploads/faculty/') !== false || strpos($imageValue, 'uploads/') === 0) {
                $path = '../' . $imageValue;
            } elseif (strpos($imageValue, 'faculty/') !== false) {
                $path = '../uploads/' . $imageValue;
            } else {
                $path = '../uploads/faculty/' . $imageValue;
            }
            if (file_exists($path)) {
                @unlink($path);
            }
        }
    }
    $stmt->close();
    
    // Delete faculty record
    $stmt = $conn->prepare("DELETE FROM faculty WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Faculty deleted successfully!'); window.location.href='manage_faculty.php';</script>";
}

// Fetch faculty
$result = $conn->query("SELECT * FROM faculty ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Faculty | Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="admin.css">
  <style>
    .faculty-card {
      border: 1px solid #e0e0e0;
      border-radius: 10px;
      transition: all 0.3s ease;
    }
    .faculty-card:hover {
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .faculty-image {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 8px;
    }
    .experience-badge {
      background: linear-gradient(45deg, #00A9B4, #008c97);
      color: white;
      padding: 2px 6px;
      border-radius: 8px;
      font-size: 0.7rem;
      margin: 1px;
      display: inline-block;
    }
    .tag-badge {
      background: linear-gradient(45deg, #28a745, #20c997);
      color: white;
      padding: 2px 6px;
      border-radius: 6px;
      font-size: 0.7rem;
      margin: 1px;
      display: inline-block;
    }
  </style>
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
    <h2><i class="fas fa-chalkboard-teacher me-2"></i>Manage Faculty</h2>
    <button class="btn btn-admin" data-bs-toggle="modal" data-bs-target="#addFacultyModal">
      <i class="fa-solid fa-plus me-2"></i>Add Faculty
    </button>
  </div>

  <div class="row g-4">
    <?php
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Handle image path - check if it's already a full path or just filename
            $photo = '../assets/default-avatar.png';
            if (!empty($row['profile_photo']) && $row['profile_photo'] !== 'assets/faculty-default.png') {
                $imageValue = trim($row['profile_photo']);
                // If it already contains 'uploads/faculty/', use it as is
                if (strpos($imageValue, 'uploads/faculty/') !== false || strpos($imageValue, 'uploads/') === 0) {
                    $photo = '../' . $imageValue;
                } 
                // If it contains 'faculty/', prepend '../uploads/'
                elseif (strpos($imageValue, 'faculty/') !== false) {
                    $photo = '../uploads/' . $imageValue;
                }
                // Otherwise, prepend '../uploads/faculty/'
                else {
                    $photo = '../uploads/faculty/' . $imageValue;
                }
                
                // Verify file exists, otherwise use default
                if (!file_exists($photo)) {
                    $photo = '../assets/default-avatar.png';
                }
            }
            $tags = !empty($row['tags']) ? explode(',', $row['tags']) : [];
            
            echo '
            <div class="col-md-6">
              <div class="card faculty-card p-3">
                <div class="d-flex align-items-start mb-3">
                  <img src="' . $photo . '" alt="Faculty Photo" class="faculty-image me-3">
                  <div class="flex-grow-1">
                    <h6 class="mb-1">' . htmlspecialchars($row['name']) . '</h6>
                    <p class="text-muted small mb-1">' . htmlspecialchars($row['designation']) . '</p>
                    <p class="text-primary small mb-1"><strong>' . htmlspecialchars($row['specialization']) . '</strong></p>';
                    
            // Experience badges
            if (!empty($row['research_experience'])) {
                echo '<span class="experience-badge"><i class="fas fa-microscope me-1"></i>Research</span>';
            }
            if (!empty($row['teaching_experience'])) {
                echo '<span class="experience-badge"><i class="fas fa-chalkboard-teacher me-1"></i>Teaching</span>';
            }
            if (!empty($row['industrial_experience'])) {
                echo '<span class="experience-badge"><i class="fas fa-industry me-1"></i>Industry</span>';
            }
            
            echo '</div>
                </div>
                
                <div class="mb-2">
                  <small class="text-muted"><i class="fas fa-envelope me-1"></i>' . htmlspecialchars($row['email']) . '</small><br>
                  <small class="text-muted"><i class="fas fa-phone me-1"></i>' . htmlspecialchars($row['phone']) . '</small>';
                  
            if (!empty($row['google_scholar_link'])) {
                echo '<br><small><a href="' . htmlspecialchars($row['google_scholar_link']) . '" target="_blank" class="text-info"><i class="fab fa-google me-1"></i>Google Scholar</a></small>';
            }
            
            echo '</div>
                
                <div class="mb-2">';
            foreach ($tags as $tag) {
                $tag = trim($tag);
                if (!empty($tag)) {
                    echo '<span class="tag-badge">' . htmlspecialchars($tag) . '</span>';
                }
            }
            echo '</div>
                
                <div class="d-flex gap-2">
                  <button class="btn btn-sm btn-outline-primary" onclick="editFaculty(' . $row['id'] . ')">
                    <i class="fas fa-edit"></i> Edit
                  </button>
                  <a href="?delete=' . $row['id'] . '" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Are you sure?\')">
                    <i class="fas fa-trash"></i> Delete
                  </a>
                </div>
              </div>
            </div>';
        }
    } else {
        echo '<div class="col-12"><p class="text-center">No faculty records found.</p></div>';
    }
    ?>
  </div>
</div>

<!-- Add Faculty Modal -->
<div class="modal fade" id="addFacultyModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-user-plus me-2"></i>Add New Faculty</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="add_faculty" value="1">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Full Name *</label>
              <input type="text" name="name" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Designation *</label>
              <input type="text" name="designation" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Qualification *</label>
              <input type="text" name="qualification" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Specialization *</label>
              <input type="text" name="specialization" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email *</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone *</label>
              <input type="text" name="phone" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Profile Photo</label>
              <input type="file" name="profile_photo" class="form-control" accept="image/*">
            </div>
            <div class="col-md-6">
              <label class="form-label">Google Scholar Link</label>
              <input type="url" name="google_scholar_link" class="form-control" placeholder="https://scholar.google.com/...">
            </div>
            <div class="col-12">
              <label class="form-label">Bio</label>
              <textarea name="bio" class="form-control" rows="3"></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Research Experience</label>
              <textarea name="research_experience" class="form-control" rows="3" placeholder="Describe research experience, publications, projects..."></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Teaching Experience</label>
              <textarea name="teaching_experience" class="form-control" rows="3" placeholder="Describe teaching experience, courses taught..."></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Industrial Experience</label>
              <textarea name="industrial_experience" class="form-control" rows="3" placeholder="Describe industry experience, companies worked for..."></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Subjects Undertaken</label>
              <input type="text" name="subjects_undertaken" class="form-control" placeholder="Separate multiple subjects with commas (e.g., Data Structures, Algorithms, Machine Learning)">
            </div>
            <div class="col-12">
              <label class="form-label">Tags</label>
              <input type="text" name="tags" class="form-control" placeholder="Separate multiple tags with commas (e.g., AI, Machine Learning, Research, Industry Expert)">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-admin">Add Faculty</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Faculty Modal -->
<div class="modal fade" id="editFacultyModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-user-edit me-2"></i>Edit Faculty</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" enctype="multipart/form-data" id="editForm">
        <input type="hidden" name="edit_faculty" value="1">
        <input type="hidden" name="id" id="editId">
        <input type="hidden" name="current_photo" id="currentPhoto">
        <div class="modal-body" id="editModalBody">
          <!-- Content will be loaded dynamically -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-admin">Update Faculty</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editFaculty(id) {
    // This would typically fetch data via AJAX
    // For now, we'll redirect to a separate edit page
    window.location.href = 'edit_faculty.php?id=' + id;
}
</script>
</body>
</html>
