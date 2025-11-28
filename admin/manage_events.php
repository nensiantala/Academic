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

// Handle delete event
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_event'])) {
    $event_id = intval($_POST['event_id']);
    
    // Fetch images_json to delete associated files
    $stmt = $conn->prepare("SELECT images_json FROM events WHERE id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $stmt->bind_result($images_json);
    $stmt->fetch();
    $stmt->close();

    if ($images_json) {
        $image_paths = json_decode($images_json, true);
        if (is_array($image_paths)) {
            foreach ($image_paths as $image_path) {
                if (file_exists('../' . $image_path)) {
                    unlink('../' . $image_path);
                }
            }
        }
    }
    
    // Delete event (cascade will handle event_images)
    $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $stmt->close();
    
    header('Location: manage_events.php?success=deleted');
    exit();
}

// Handle add event
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_event'])) {
    $title = $_POST['title'];
    $venue = $_POST['venue'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $description = $_POST['description'] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO events (title, venue, start_date, end_date, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $title, $venue, $start_date, $end_date, $description);
    
    if ($stmt->execute()) {
        $new_event_id = $conn->insert_id;
        
        // Handle multiple image uploads
        if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0) {
            $image_paths = [];
            foreach ($_FILES['images']['name'] as $key => $name) {
                if ($_FILES['images']['error'][$key] == UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES['images']['tmp_name'][$key];
                    $ext = pathinfo($name, PATHINFO_EXTENSION);
                    $new_filename = uniqid() . '_' . time() . '.' . $ext;
                    $upload_path = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($tmp_name, $upload_path)) {
                        $image_paths[] = 'uploads/events/' . $new_filename;
                    }
                }
            }
            if (!empty($image_paths)) {
                $images_json = json_encode($image_paths);
                $stmt = $conn->prepare("UPDATE events SET images_json = ? WHERE id = ?");
                $stmt->bind_param("si", $images_json, $new_event_id);
                $stmt->execute();
                $stmt->close();
            }
        }
        
        header('Location: manage_events.php?success=added');
        exit();
    }
    $stmt->close();
}

// Fetch events with images
$result = $conn->query("SELECT * FROM events ORDER BY start_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Events | Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="admin.css">
  <style>
    .image-preview-container {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-top: 10px;
    }
    .image-preview {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 5px;
      border: 2px solid #ddd;
    }
    .event-card {
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      transition: transform 0.2s;
    }
    .event-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.15);
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
    <h2>Manage Events</h2>
  </div>
  
  <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i>
      Event <?php 
        $success_msg = ['added' => 'added', 'updated' => 'updated', 'deleted' => 'deleted'];
        echo $success_msg[$_GET['success']] ?? 'saved';
      ?> successfully!
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  
  <button class="btn btn-theme mb-3" data-bs-toggle="modal" data-bs-target="#addEventModal">
    <i class="fa fa-plus me-2"></i>Add Event
  </button>
  
  <div class="row mt-3">
    <div class="col-lg-12">
      <?php if($result && $result->num_rows): while($e = $result->fetch_assoc()): 
        // Decode images_json to get image paths for display
        $image_paths = json_decode($e['images_json'], true);
        $image_count = is_array($image_paths) ? count($image_paths) : 0;
      ?>
        <div class="card event-card mb-3">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
              <div class="flex-grow-1">
                <h5 class="mb-2">
                  <?= htmlspecialchars($e['title']) ?>
                  <?php 
                    $event_ended = strtotime($e['end_date']) < strtotime(date('Y-m-d'));
                    if ($event_ended): 
                  ?>
                    <span class="badge bg-secondary ms-2"><i class="fas fa-clock me-1"></i>Ended</span>
                  <?php endif; ?>
                </h5>
                <div class="text-muted small mb-2">
                  <i class="fas fa-calendar-alt me-1"></i>
                  <?= date('d M Y', strtotime($e['start_date'])) ?> - <?= date('d M Y', strtotime($e['end_date'])) ?>
                  <span class="mx-2">|</span>
                  <i class="fas fa-map-marker-alt me-1"></i>
                  <?= htmlspecialchars($e['venue']) ?>
                  <?php if ($image_count > 0): ?>
                    <span class="mx-2">|</span>
                    <i class="fas fa-images me-1"></i>
                    <?= $image_count ?> image<?= $image_count > 1 ? 's' : '' ?>
                  <?php endif; ?>
                </div>
                <?php if (!empty($e['description'])): ?>
                  <p class="text-muted small mb-2"><?= htmlspecialchars(substr($e['description'], 0, 150)) ?><?= strlen($e['description']) > 150 ? '...' : '' ?></p>
                <?php endif; ?>
                
                <!-- Preview Images -->
                <?php if ($image_paths && count($image_paths) > 0): ?>
                  <div class="d-flex gap-2 flex-wrap mt-2">
                    <?php foreach ($image_paths as $image_path): ?>
                      <img src="../<?= htmlspecialchars($image_path) ?>" alt="Event Image" style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd;">
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
              
              <div class="d-flex gap-2">
                <a href="edit_event.php?id=<?= $e['id'] ?>" class="btn btn-sm btn-primary">
                  <i class="fas fa-edit"></i> Edit
                </a>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this event?');">
                  <input type="hidden" name="delete_event" value="1">
                  <input type="hidden" name="event_id" value="<?= $e['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-danger">
                    <i class="fas fa-trash"></i> Delete
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; else: ?>
        <div class="alert alert-info">
          <i class="fas fa-info-circle me-2"></i>No events found.
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Add Event Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background-color:#00A9B4; color:white;">
        <h5 class="modal-title"><i class="fa fa-plus me-2"></i>Add New Event</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="add_event" value="1">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label"><i class="fas fa-heading me-1"></i>Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label"><i class="fas fa-file-alt me-1"></i>Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Enter event description..."></textarea>
          </div>
          
          <div class="mb-3">
            <label class="form-label"><i class="fas fa-map-marker-alt me-1"></i>Venue <span class="text-danger">*</span></label>
            <input type="text" name="venue" class="form-control" required>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="fas fa-calendar-plus me-1"></i>Start Date <span class="text-danger">*</span></label>
              <input type="date" name="start_date" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="fas fa-calendar-minus me-1"></i>End Date <span class="text-danger">*</span></label>
              <input type="date" name="end_date" class="form-control" required>
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label"><i class="fas fa-images me-1"></i>Images (Multiple allowed)</label>
            <input type="file" name="images[]" class="form-control" accept="image/*" multiple id="imageInput">
            <div class="form-text">You can select multiple images</div>
            <div class="image-preview-container" id="imagePreview"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-plus me-2"></i>Add Event
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Image preview
  document.getElementById('imageInput').addEventListener('change', function(e) {
    const container = document.getElementById('imagePreview');
    container.innerHTML = '';
    
    if (this.files) {
      Array.from(this.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
          const img = document.createElement('img');
          img.src = e.target.result;
          img.className = 'image-preview';
          container.appendChild(img);
        };
        reader.readAsDataURL(file);
      });
    }
  });
  
  // Auto-hide success message
  setTimeout(function() {
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
