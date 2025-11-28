<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}
include '../db.php';

$upload_dir = '../uploads/events/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$event_id = intval($_GET['id'] ?? 0);

// Fetch event details
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    header('Location: manage_events.php');
    exit();
}

// Handle update event
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_event'])) {
    $title = $_POST['title'];
    $venue = $_POST['venue'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $description = $_POST['description'] ?? '';
    
    // Get existing images
    $existing_images = json_decode($event['images_json'] ?? '[]', true);
    if (!is_array($existing_images)) {
        $existing_images = [];
    }
    
    // Handle removed images
    if (!empty($_POST['removed_images'])) {
        $removed_images = json_decode($_POST['removed_images'], true);
        if (is_array($removed_images)) {
            foreach ($removed_images as $removed_path) {
                // Delete file from server
                if (file_exists('../' . $removed_path)) {
                    @unlink('../' . $removed_path);
                }
                // Remove from array
                $existing_images = array_values(array_filter($existing_images, function($img) use ($removed_path) {
                    return $img !== $removed_path;
                }));
            }
        }
    }
    
    // Handle new image uploads
    $new_images = [];
    if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0) {
        foreach ($_FILES['images']['name'] as $key => $name) {
            if ($_FILES['images']['error'][$key] == UPLOAD_ERR_OK) {
                $tmp_name = $_FILES['images']['tmp_name'][$key];
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $new_filename = uniqid() . '_' . time() . '_' . $key . '.' . $ext;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($tmp_name, $upload_path)) {
                    $new_images[] = 'uploads/events/' . $new_filename;
                }
            }
        }
    }
    
    // Merge existing (non-removed) images with new images
    $all_images = array_merge($existing_images, $new_images);
    $images_json = json_encode($all_images);
    
    // Update event
    $stmt = $conn->prepare("UPDATE events SET title = ?, venue = ?, start_date = ?, end_date = ?, description = ?, images_json = ? WHERE id = ?");
    $stmt->bind_param("ssssssi", $title, $venue, $start_date, $end_date, $description, $images_json, $event_id);
    
    if ($stmt->execute()) {
        header('Location: manage_events.php?success=updated');
        exit();
    }
    $stmt->close();
}

// Get existing images for display
$existing_images = json_decode($event['images_json'] ?? '[]', true);
if (!is_array($existing_images)) {
    $existing_images = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Event | Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="admin.css">
  <style>
    .image-preview-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
      gap: 15px;
      margin-top: 10px;
    }
    .image-preview-item {
      position: relative;
      border: 2px solid #ddd;
      border-radius: 8px;
      overflow: hidden;
      aspect-ratio: 1;
      transition: opacity 0.3s;
    }
    .image-preview-item.removed {
      opacity: 0.4;
      border-color: #dc3545;
    }
    .image-preview-item img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .image-preview-item .remove-btn {
      position: absolute;
      top: 5px;
      right: 5px;
      background: rgba(220, 53, 69, 0.9);
      color: white;
      border: none;
      border-radius: 50%;
      width: 32px;
      height: 32px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s;
      z-index: 10;
    }
    .image-preview-item .remove-btn:hover {
      background: rgba(220, 53, 69, 1);
      transform: scale(1.1);
    }
    .new-image-preview {
      width: 150px;
      height: 150px;
      object-fit: cover;
      border-radius: 8px;
      border: 2px solid #28a745;
      margin: 5px;
    }
    .new-images-container {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 10px;
    }
  </style>
</head>
<body>

<div class="admin-sidebar">
  <h3><i class="fas fa-cogs me-2"></i>Admin Panel</h3>
  <ul>
    <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
    <li><a href="manage_faculty.php"><i class="fas fa-chalkboard-teacher"></i>Faculty</a></li>
    <li><a href="manage_events.php" class="active"><i class="fas fa-calendar-alt"></i>Events</a></li>
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
    <h2><i class="fas fa-edit me-2"></i>Edit Event</h2>
  </div>
  
  <div class="card">
    <div class="card-body">
      <form method="POST" enctype="multipart/form-data" id="eventForm">
        <input type="hidden" name="update_event" value="1">
        <input type="hidden" name="removed_images" id="removedImages" value="">
        
        <div class="mb-3">
          <label class="form-label"><i class="fas fa-heading me-1"></i>Title <span class="text-danger">*</span></label>
          <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($event['title']) ?>" required>
        </div>
        
        <div class="mb-3">
          <label class="form-label"><i class="fas fa-file-alt me-1"></i>Description</label>
          <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
        </div>
        
        <div class="mb-3">
          <label class="form-label"><i class="fas fa-map-marker-alt me-1"></i>Venue <span class="text-danger">*</span></label>
          <input type="text" name="venue" class="form-control" value="<?= htmlspecialchars($event['venue']) ?>" required>
        </div>
        
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label"><i class="fas fa-calendar-plus me-1"></i>Start Date <span class="text-danger">*</span></label>
            <input type="date" name="start_date" class="form-control" value="<?= $event['start_date'] ?>" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label"><i class="fas fa-calendar-minus me-1"></i>End Date <span class="text-danger">*</span></label>
            <input type="date" name="end_date" class="form-control" value="<?= $event['end_date'] ?>" required>
          </div>
        </div>
        
        <div class="mb-3">
          <label class="form-label"><i class="fas fa-images me-1"></i>Existing Images</label>
          <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>Click the red X button on any image to remove it. Changes will be saved when you update the event.
          </div>
          <div class="image-preview-container" id="existingImages">
            <?php if (!empty($existing_images)): ?>
              <?php foreach ($existing_images as $index => $img_path): ?>
                <div class="image-preview-item" data-image-path="<?= htmlspecialchars($img_path, ENT_QUOTES) ?>">
                  <img src="../<?= htmlspecialchars($img_path) ?>" alt="Event Image">
                  <button type="button" class="remove-btn" onclick="removeExistingImage(this, '<?= htmlspecialchars($img_path, ENT_QUOTES) ?>')" title="Remove this image">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-muted">No images uploaded yet.</div>
            <?php endif; ?>
          </div>
        </div>
        
        <div class="mb-3">
          <label class="form-label"><i class="fas fa-images me-1"></i>Add New Images</label>
          <input type="file" name="images[]" class="form-control" accept="image/*" multiple id="imageInput">
          <div class="form-text">You can select multiple images to add</div>
          <div class="new-images-container" id="newImagePreview"></div>
        </div>
        
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Update Event
          </button>
          <a href="manage_events.php" class="btn btn-secondary">
            <i class="fas fa-times me-2"></i>Cancel
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  let removedImages = [];
  
  // Handle removal of existing images
  function removeExistingImage(button, imagePath) {
    if (confirm('Are you sure you want to remove this image? It will be deleted permanently when you save.')) {
      const imageItem = button.closest('.image-preview-item');
      
      // Mark as removed visually
      imageItem.classList.add('removed');
      button.style.display = 'none';
      
      // Add to removed images array
      if (!removedImages.includes(imagePath)) {
        removedImages.push(imagePath);
      }
      
      // Update hidden input
      document.getElementById('removedImages').value = JSON.stringify(removedImages);
    }
  }
  
  // Preview new images
  document.getElementById('imageInput').addEventListener('change', function(e) {
    const container = document.getElementById('newImagePreview');
    container.innerHTML = '';
    
    if (this.files) {
      Array.from(this.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
          const img = document.createElement('img');
          img.src = e.target.result;
          img.className = 'new-image-preview';
          img.title = file.name;
          container.appendChild(img);
        };
        reader.readAsDataURL(file);
      });
    }
  });
</script>

</body>
</html>
