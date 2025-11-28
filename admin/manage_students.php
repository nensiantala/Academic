<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}
include '../db.php';
// Handle delete student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_student'])) {
    $student_id = intval($_POST['student_id'] ?? 0);
    if ($student_id > 0) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: manage_students.php');
    exit();
}

// Handle edit student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_student'])) {
    $student_id = intval($_POST['student_id'] ?? 0);
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    if ($student_id > 0) {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $email, $phone, $student_id);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: manage_students.php');
    exit();
}

// Fetch students
$result = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Students | Admin Panel</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="admin.css">
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
    <li><a href="manage_students.php" class="active"><i class="fas fa-user-graduate"></i>Students</a></li>
    <li><a href="manage_labs.php"><i class="fas fa-flask"></i>Labs</a></li>
    <li><a href="manage_notices.php"><i class="fas fa-bullhorn"></i>Notices</a></li>
    <li><a href="manage_placement.php"><i class="fas fa-briefcase"></i>Placements</a></li>
    <li><a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
  </ul>
</div>
<div class="admin-main">
  <div class="admin-header mb-4"><h2>Manage Students</h2></div>
  <div class="table-responsive">
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Registered At</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $row['id'] . '</td>';
                echo '<td>' . htmlspecialchars($row['name']) . '</td>';
                echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                echo '<td>' . htmlspecialchars($row['phone']) . '</td>';
                echo '<td>' . $row['created_at'] . '</td>';
                echo '<td>';
                echo '<button class="btn btn-sm btn-warning me-2" data-bs-toggle="modal" data-bs-target="#editStudentModal" ' .
                     'data-id="' . $row['id'] . '" data-name="' . htmlspecialchars($row['name'], ENT_QUOTES) . '" ' .
                     'data-email="' . htmlspecialchars($row['email'], ENT_QUOTES) . '" data-phone="' . htmlspecialchars($row['phone'], ENT_QUOTES) . '"><i class="fas fa-edit me-1"></i>Edit</button>';
                echo '<form method="POST" style="display:inline-block" onsubmit="return confirm(\'Delete this student?\');">';
                echo '<input type="hidden" name="delete_student" value="1">';
                echo '<input type="hidden" name="student_id" value="' . $row['id'] . '">';
                echo '<button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash me-1"></i>Delete</button>';
                echo '</form>';
                echo '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="6" class="text-center">No students found.</td></tr>';
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background-color:#ffc107; color:black;">
        <h5 class="modal-title"><i class="fa fa-pen-to-square me-2"></i>Edit Student</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <input type="hidden" name="edit_student" value="1">
        <input type="hidden" name="student_id" id="editStudentId" value="">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" id="editStudentName" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" id="editStudentEmail" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" id="editStudentPhone" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-warning">Update Student</button>
        </div>
      </form>
    </div>
  </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const editStudentModal = document.getElementById('editStudentModal');
  if (editStudentModal) {
    editStudentModal.addEventListener('show.bs.modal', function (event) {
      const btn = event.relatedTarget;
      document.getElementById('editStudentId').value = btn.getAttribute('data-id');
      document.getElementById('editStudentName').value = btn.getAttribute('data-name');
      document.getElementById('editStudentEmail').value = btn.getAttribute('data-email');
      document.getElementById('editStudentPhone').value = btn.getAttribute('data-phone');
    });
  }
</script>
</body>
</html>
