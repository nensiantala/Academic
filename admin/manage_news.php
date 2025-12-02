<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}
include '../db.php';

$upload_dir = '../uploads/news/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Handle add news
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_news'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $date = $_POST['date'];
    
    $stmt = $conn->prepare("INSERT INTO news (title, content, date) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $content, $date);
    
    if ($stmt->execute()) {
        $new_news_id = $conn->insert_id;
        
        // Handle image upload
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['image']['tmp_name'];
            $name = $_FILES['image']['name'];
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $new_filename = uniqid() . '_' . time() . '.' . $ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($tmp_name, $upload_path)) {
                $image_path = 'uploads/news/' . $new_filename;
                // Update news with image path as string
                $update_stmt = $conn->prepare("UPDATE news SET image = ? WHERE id = ?");
                $update_stmt->bind_param("si", $image_path, $new_news_id);
                $update_stmt->execute();
                $update_stmt->close();
            }
        }
        
        $stmt->close();
        echo "<script>alert('News added successfully!'); window.location.href='manage_news.php';</script>";
    } else {
        $stmt->close();
        echo "<script>alert('Error adding news!'); window.location.href='manage_news.php';</script>";
    }
}
// Handle delete news
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_news'])) {
    $news_id = intval($_POST['news_id'] ?? 0);
    if ($news_id > 0) {
        // Delete associated image file
        $stmt = $conn->prepare("SELECT image FROM news WHERE id = ?");
        $stmt->bind_param("i", $news_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if (!empty($row['image']) && is_string($row['image'])) {
                $imageValue = trim($row['image']);
                // Handle different path formats
                if (strpos($imageValue, 'uploads/news/') !== false || strpos($imageValue, 'uploads/') === 0) {
                    $image_path = '../' . $imageValue;
                } else {
                    $image_path = '../uploads/news/' . $imageValue;
                }
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
        }
        $stmt->close();
        
        // Delete news record
        $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
        $stmt->bind_param("i", $news_id);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: manage_news.php');
    exit();
}
// Handle edit news
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_news'])) {
    $news_id = intval($_POST['news_id'] ?? 0);
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $date = $_POST['date'] ?? '';
    
    if ($news_id > 0) {
        // Get existing image
        $stmt = $conn->prepare("SELECT image FROM news WHERE id = ?");
        $stmt->bind_param("i", $news_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $existing_image = '';
        if ($row = $result->fetch_assoc()) {
            $existing_image = $row['image'] ?? '';
        }
        $stmt->close();
        
        // Handle new image upload
        $image_path = $existing_image;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            // Delete old image if exists
            if (!empty($existing_image)) {
                $imageValue = trim($existing_image);
                // Handle different path formats
                if (strpos($imageValue, 'uploads/news/') !== false || strpos($imageValue, 'uploads/') === 0) {
                    $old_path = '../' . $imageValue;
                } else {
                    $old_path = '../uploads/news/' . $imageValue;
                }
                if (file_exists($old_path)) {
                    unlink($old_path);
                }
            }
            
            // Upload new image
            $tmp_name = $_FILES['image']['tmp_name'];
            $name = $_FILES['image']['name'];
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $new_filename = uniqid() . '_' . time() . '.' . $ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($tmp_name, $upload_path)) {
                $image_path = 'uploads/news/' . $new_filename;
            }
        }
        
        // Update news with image path as string
        $stmt = $conn->prepare("UPDATE news SET title = ?, content = ?, date = ?, image = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $title, $content, $date, $image_path, $news_id);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: manage_news.php');
    exit();
}
// Fetch news
$result = $conn->query("SELECT * FROM news ORDER BY date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage News | Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
    <li><a href="manage_news.php" class="active"><i class="fas fa-newspaper"></i>News</a></li>
    <li><a href="manage_students.php"><i class="fas fa-user-graduate"></i>Students</a></li>
    <li><a href="manage_labs.php"><i class="fas fa-flask"></i>Labs</a></li>
    <li><a href="manage_notices.php"><i class="fas fa-bullhorn"></i>Notices</a></li>
    <li><a href="manage_placement.php"><i class="fas fa-briefcase"></i>Placements</a></li>
    <li><a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
  </ul>
</div>
<div class="admin-main">
  <div class="admin-header mb-4"><h2>Manage News</h2></div>
  <button class="btn btn-theme mb-3" data-bs-toggle="modal" data-bs-target="#addNewsModal">
    <i class="fa fa-plus me-2"></i>Add News
  </button>
  <div class="row mt-3">
    <div class="col-lg-12">
      <?php if($result && $result->num_rows): ?>
        <?php while($n = $result->fetch_assoc()): ?>
          <div class="border rounded p-3 mb-3">
            <div class="d-flex gap-3">
              <?php 
              // Handle image path - check if it's already a full path or just filename
              $img = '';
              if (!empty($n['image']) && is_string($n['image'])) {
                  $imageValue = trim($n['image']);
                  // If it already contains 'uploads/news/', use it as is
                  if (strpos($imageValue, 'uploads/news/') !== false) {
                      $img = '../' . $imageValue;
                  } 
                  // If it starts with 'uploads/', use it as is
                  elseif (strpos($imageValue, 'uploads/') === 0) {
                      $img = '../' . $imageValue;
                  }
                  // Otherwise, prepend 'uploads/news/'
                  else {
                      $img = '../uploads/news/' . $imageValue;
                  }
                  
                  // Verify file exists
                  if (!file_exists($img)) {
                      $img = ''; // Don't show image if file doesn't exist
                  }
              }
              if (!empty($img)): ?>
                <div>
                  <img src="<?= htmlspecialchars($img) ?>" alt="News Image" style="width: 120px; height: 120px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd;">
                </div>
              <?php endif; ?>
              <div class="flex-grow-1">
                <h6><?= htmlspecialchars($n['title']) ?></h6>
                <div class="muted-small"><?= date('d M Y', strtotime($n['date'])) ?></div>
                <p><?= nl2br(htmlspecialchars($n['content'])) ?></p>
                <div class="d-flex gap-2">
                  <button 
                    class="btn btn-sm btn-warning"
                    data-bs-toggle="modal"
                    data-bs-target="#editNewsModal"
                    data-id="<?= $n['id'] ?>"
                    data-title="<?= htmlspecialchars($n['title'], ENT_QUOTES) ?>"
                    data-content='<?= htmlspecialchars($n['content'], ENT_QUOTES) ?>'
                    data-date="<?= htmlspecialchars($n['date']) ?>"
                    data-image="<?= htmlspecialchars($n['image'] ?? '', ENT_QUOTES) ?>">
                    <i class="fas fa-edit me-1"></i>Edit
                  </button>
                  <form method="POST" onsubmit="return confirm('Delete this news item?');">
                    <input type="hidden" name="delete_news" value="1">
                    <input type="hidden" name="news_id" value="<?= $n['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash me-1"></i>Delete</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No news found.</p>
      <?php endif; ?>
    </div>
  </div>
  <!-- Add News Modal -->
  <div class="modal fade" id="addNewsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background-color:#00A9B4; color:white;">
          <h5 class="modal-title"><i class="fa fa-plus me-2"></i>Add News</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="add_news" value="1">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Title</label>
              <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Content</label>
              <textarea name="content" class="form-control" required></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Date</label>
              <input type="date" name="date" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Image</label>
              <input type="file" name="image" class="form-control" accept="image/*">
              <small class="text-muted">Upload an image for this news item (optional)</small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-theme">Add News</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- Edit News Modal -->
  <div class="modal fade" id="editNewsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background-color:#ffc107; color:black;">
          <h5 class="modal-title"><i class="fa fa-pen-to-square me-2"></i>Edit News</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="edit_news" value="1">
          <input type="hidden" name="news_id" id="editNewsId" value="">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Title</label>
              <input type="text" name="title" id="editNewsTitle" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Content</label>
              <textarea name="content" id="editNewsContent" class="form-control" required></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Date</label>
              <input type="date" name="date" id="editNewsDate" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Image</label>
              <div id="editNewsImagePreview" class="mb-2"></div>
              <input type="file" name="image" id="editNewsImage" class="form-control" accept="image/*">
              <small class="text-muted">Upload a new image to replace the existing one (optional)</small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-warning">Update News</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const editNewsModal = document.getElementById('editNewsModal');
  if (editNewsModal) {
    editNewsModal.addEventListener('show.bs.modal', function (event) {
      const btn = event.relatedTarget;
      document.getElementById('editNewsId').value = btn.getAttribute('data-id');
      document.getElementById('editNewsTitle').value = btn.getAttribute('data-title');
      document.getElementById('editNewsContent').value = btn.getAttribute('data-content');
      document.getElementById('editNewsDate').value = btn.getAttribute('data-date');
      
      // Handle image preview
      const imagePath = btn.getAttribute('data-image');
      const previewDiv = document.getElementById('editNewsImagePreview');
      if (imagePath && imagePath.trim() !== '') {
        // Handle path - if it already starts with 'uploads/', use as is, otherwise prepend 'uploads/news/'
        let imgPath = imagePath.trim();
        if (imgPath.indexOf('uploads/news/') === -1 && imgPath.indexOf('uploads/') !== 0) {
          imgPath = 'uploads/news/' + imgPath;
        }
        previewDiv.innerHTML = '<img src="../' + imgPath + '" alt="Current Image" style="max-width: 200px; max-height: 150px; border-radius: 5px; border: 1px solid #ddd;">';
      } else {
        previewDiv.innerHTML = '<small class="text-muted">No image currently set</small>';
      }
    });
  }
</script>
</body>
</html>
