<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header('Location: admin_login.php');
  exit();
}
include '../db.php';

$upload_dir = '../uploads/notices/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Handle Add Notice
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_notice'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $file = '';

    if (!empty($_FILES['notice_file']['name'])) {
        $ext = pathinfo($_FILES['notice_file']['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '_' . time() . '.' . $ext;
        $target = $upload_dir . $new_filename;
        if (move_uploaded_file($_FILES['notice_file']['tmp_name'], $target)) {
            $file = 'uploads/notices/' . $new_filename;
        }
    }

    $stmt = $conn->prepare("INSERT INTO notices (title, description, date, file) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $title, $description, $date, $file);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Notice added successfully!'); window.location.href='manage_notices.php';</script>";
}

// Handle Delete Notice
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_notice'])) {
    $notice_id = intval($_POST['notice_id'] ?? 0);
    if ($notice_id > 0) {
        $res = $conn->query("SELECT file FROM notices WHERE id = $notice_id");
        if ($res && ($r = $res->fetch_assoc())) {
            $f = $r['file'] ?? '';
            if (!empty($f)) {
                $fileValue = trim($f);
                // Handle different path formats
                if (strpos($fileValue, 'uploads/notices/') !== false || strpos($fileValue, 'uploads/') === 0) {
                    $path = '../' . $fileValue;
                } elseif (strpos($fileValue, 'notices/') !== false) {
                    $path = '../uploads/' . $fileValue;
                } else {
                    $path = '../uploads/notices/' . $fileValue;
                }
                if (file_exists($path)) @unlink($path);
            }
        }
        $stmt = $conn->prepare("DELETE FROM notices WHERE id = ?");
        $stmt->bind_param("i", $notice_id);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: manage_notices.php');
    exit();
}

// Handle Edit Notice
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_notice'])) {
    $notice_id = intval($_POST['notice_id'] ?? 0);
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $date = $_POST['date'] ?? '';
    $newFile = null;
    if (!empty($_FILES['notice_file']['name'])) {
        $ext = pathinfo($_FILES['notice_file']['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '_' . time() . '.' . $ext;
        $target = $upload_dir . $new_filename;
        if (move_uploaded_file($_FILES['notice_file']['tmp_name'], $target)) {
            $newFile = 'uploads/notices/' . $new_filename;
        }
    }
    if ($notice_id > 0) {
        if ($newFile !== null) {
            // delete old file
            $res = $conn->query("SELECT file FROM notices WHERE id = $notice_id");
            if ($res && ($r = $res->fetch_assoc())) {
                $old = $r['file'] ?? '';
                if (!empty($old)) {
                    $fileValue = trim($old);
                    // Handle different path formats when deleting old file
                    if (strpos($fileValue, 'uploads/notices/') !== false || strpos($fileValue, 'uploads/') === 0) {
                        $oldPath = '../' . $fileValue;
                    } elseif (strpos($fileValue, 'notices/') !== false) {
                        $oldPath = '../uploads/' . $fileValue;
                    } else {
                        $oldPath = '../uploads/notices/' . $fileValue;
                    }
                    if (file_exists($oldPath)) @unlink($oldPath);
                }
            }
            $stmt = $conn->prepare("UPDATE notices SET title = ?, description = ?, date = ?, file = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $title, $description, $date, $newFile, $notice_id);
        } else {
            $stmt = $conn->prepare("UPDATE notices SET title = ?, description = ?, date = ? WHERE id = ?");
            $stmt->bind_param("sssi", $title, $description, $date, $notice_id);
        }
        $stmt->execute();
        $stmt->close();
    }
    header('Location: manage_notices.php');
    exit();
}

// Fetch all notices
$result = $conn->query("SELECT * FROM notices ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Notices | Admin Panel</title>
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
    <li><a href="manage_students.php"><i class="fas fa-user-graduate"></i>Students</a></li>
    <li><a href="manage_labs.php"><i class="fas fa-flask"></i>Labs</a></li>
    <li><a href="manage_notices.php" class="active"><i class="fas fa-bullhorn"></i>Notices</a></li>
    <li><a href="manage_placement.php"><i class="fas fa-briefcase"></i>Placements</a></li>
    <li><a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
  </ul>
</div>

<div class="admin-main">
  <div class="admin-header mb-4"><h2>Manage Notices</h2></div>

  <button class="btn btn-admin mb-3" data-bs-toggle="modal" data-bs-target="#addNoticeModal">
    <i class="fa-solid fa-plus me-2"></i>Add Notice
  </button>

  <div class="row g-4">
    <?php if ($result && $result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="col-md-4">
          <div class="card card-admin h-100">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
              <p class="text-muted"><i class="fa-solid fa-calendar-days me-1"></i><?= htmlspecialchars($row['date']) ?></p>
              <p><?= nl2br(htmlspecialchars(substr($row['description'], 0, 120))) ?>...</p>
              <div class="d-flex gap-2">
                <?php 
                // Handle file path - check if it's already a full path or just filename
                $filePath = '';
                if (!empty($row['file'])) {
                    $fileValue = trim($row['file']);
                    // If it already contains 'uploads/notices/', use it as is
                    if (strpos($fileValue, 'uploads/notices/') !== false || strpos($fileValue, 'uploads/') === 0) {
                        $filePath = '../' . $fileValue;
                    } 
                    // If it contains 'notices/', prepend '../uploads/'
                    elseif (strpos($fileValue, 'notices/') !== false) {
                        $filePath = '../uploads/' . $fileValue;
                    }
                    // Otherwise, prepend '../uploads/notices/'
                    else {
                        $filePath = '../uploads/notices/' . $fileValue;
                    }
                }
                if (!empty($filePath)): ?>
                  <a href="<?= htmlspecialchars($filePath) ?>" target="_blank" class="btn btn-sm btn-outline-primary">View File</a>
                <?php endif; ?>
                <button 
                  class="btn btn-sm btn-warning"
                  data-bs-toggle="modal"
                  data-bs-target="#editNoticeModal"
                  data-id="<?= $row['id'] ?>"
                  data-title="<?= htmlspecialchars($row['title'], ENT_QUOTES) ?>"
                  data-description='<?= htmlspecialchars($row['description'], ENT_QUOTES) ?>'
                  data-date="<?= htmlspecialchars($row['date']) ?>">
                  <i class="fas fa-edit me-1"></i>Edit
                </button>
                <form method="POST" onsubmit="return confirm('Delete this notice?');">
                  <input type="hidden" name="delete_notice" value="1">
                  <input type="hidden" name="notice_id" value="<?= $row['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash me-1"></i>Delete</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="text-center">No notices found.</p>
    <?php endif; ?>
  </div>

  <!-- Add Notice Modal -->
  <div class="modal fade" id="addNoticeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa-solid fa-bullhorn me-2"></i>Add New Notice</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="add_notice" value="1">
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Notice Title</label>
                <input type="text" name="title" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" required>
              </div>
              <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4" required></textarea>
              </div>
              <div class="col-md-6">
                <label class="form-label">Attach File (optional)</label>
                <input type="file" name="notice_file" class="form-control">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-admin">Add Notice</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Notice Modal -->
  <div class="modal fade" id="editNoticeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa-solid fa-pen-to-square me-2"></i>Edit Notice</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="edit_notice" value="1">
          <input type="hidden" name="notice_id" id="editNoticeId" value="">
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Notice Title</label>
                <input type="text" name="title" id="editNoticeTitle" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Date</label>
                <input type="date" name="date" id="editNoticeDate" class="form-control" required>
              </div>
              <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" id="editNoticeDescription" class="form-control" rows="4" required></textarea>
              </div>
              <div class="col-md-6">
                <label class="form-label">Replace File (optional)</label>
                <input type="file" name="notice_file" class="form-control">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-warning">Update Notice</button>
          </div>
        </form>
      </div>
    </div>
  </div>

</div>
<footer class="admin-footer">&copy; 2025 Academic Admin Panel</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const editNoticeModal = document.getElementById('editNoticeModal');
  if (editNoticeModal) {
    editNoticeModal.addEventListener('show.bs.modal', function (event) {
      const btn = event.relatedTarget;
      document.getElementById('editNoticeId').value = btn.getAttribute('data-id');
      document.getElementById('editNoticeTitle').value = btn.getAttribute('data-title');
      document.getElementById('editNoticeDescription').value = btn.getAttribute('data-description');
      document.getElementById('editNoticeDate').value = btn.getAttribute('data-date');
    });
  }
</script>
</body>
</html>
