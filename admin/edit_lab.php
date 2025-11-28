<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}
include '../db.php';

$upload_dir = '../uploads/labs/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$lab_id = intval($_GET['id'] ?? 0);

// Fetch lab details
$stmt = $conn->prepare("SELECT * FROM labs WHERE id = ?");
$stmt->bind_param("i", $lab_id);
$stmt->execute();
$result = $stmt->get_result();
$lab = $result->fetch_assoc();

if (!$lab) {
    header('Location: manage_labs.php');
    exit();
}

// Decode existing images JSON
$existing_images = [];
if (!empty($lab['images_json'])) {
    $existing_images = json_decode($lab['images_json'], true);
    if (!is_array($existing_images)) $existing_images = [];
}

// Handle update lab
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_lab'])) {
    $name = $_POST['name'];
    $incharge = $_POST['incharge'];
    $timings = $_POST['timings'];
    $equipment = $_POST['equipment'];

    // Handle image deletion
    if (!empty($_POST['delete_images'])) {
        foreach ($_POST['delete_images'] as $del_img) {
            $path = '../' . $del_img;
            if (file_exists($path)) unlink($path);
            $existing_images = array_filter($existing_images, fn($img) => $img !== $del_img);
        }
        $existing_images = array_values($existing_images); // reindex
    }

    // Handle new image uploads
   // Handle new image uploads
if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0) {
  foreach ($_FILES['images']['name'] as $key => $originalName) {
      if ($_FILES['images']['error'][$key] == UPLOAD_ERR_OK) {
          $tmp_name = $_FILES['images']['tmp_name'][$key];
          $ext = pathinfo($originalName, PATHINFO_EXTENSION);
          $new_filename = uniqid() . '_' . time() . '.' . $ext;
          $upload_path = $upload_dir . $new_filename;

          if (move_uploaded_file($tmp_name, $upload_path)) {
              $existing_images[] = 'uploads/labs/' . $new_filename;
          }
      }
  }
}


    // Convert images to JSON
    $images_json = json_encode($existing_images);

    // Update labs table
    $stmt = $conn->prepare("UPDATE labs SET name = ?, incharge = ?, timings = ?, equipment = ?, images_json = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $name, $incharge, $timings, $equipment, $images_json, $lab_id);

    if ($stmt->execute()) {
        header('Location: manage_labs.php?success=updated');
        exit();
    } else {
        echo "<script>alert('Update failed!');</script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Lab | Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="admin.css">
  <style>
    .image-preview-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
      gap: 15px;
      margin-top: 10px;
    }
    .image-preview-item {
      position: relative;
      border: 2px solid #ddd;
      border-radius: 8px;
      overflow: hidden;
      aspect-ratio: 1;
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
      width: 30px;
      height: 30px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .new-image-preview {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 5px;
      border: 2px solid #ddd;
      margin: 5px;
    }
  </style>
</head>
<body>

<div class="admin-sidebar">
  <h3><i class="fas fa-cogs me-2"></i>Admin Panel</h3>
  <ul>
    <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
    <li><a href="manage_labs.php" class="active"><i class="fas fa-flask"></i>Labs</a></li>
    <li><a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
  </ul>
</div>

<div class="admin-main">
  <div class="admin-header mb-4">
    <h2><i class="fas fa-edit me-2"></i>Edit Lab</h2>
  </div>
  
  <div class="card">
    <div class="card-body">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="update_lab" value="1">
        
        <div class="mb-3">
          <label class="form-label">Lab Name <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($lab['name']) ?>" required>
        </div>
        
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">In-charge <span class="text-danger">*</span></label>
            <input type="text" name="incharge" class="form-control" value="<?= htmlspecialchars($lab['incharge']) ?>" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Timings <span class="text-danger">*</span></label>
            <input type="text" name="timings" class="form-control" value="<?= htmlspecialchars($lab['timings']) ?>" required>
          </div>
        </div>
        
        <div class="mb-3">
          <label class="form-label">Equipment <span class="text-danger">*</span></label>
          <textarea name="equipment" class="form-control" rows="4" required><?= htmlspecialchars($lab['equipment']) ?></textarea>
        </div>
        
        <div class="mb-3">
          <label class="form-label">Existing Images</label>
          <div class="image-preview-container">
          <?php
if (!empty($existing_images)) {
    foreach ($existing_images as $img) {
        $checkbox_id = 'delete_' . md5($img);
        ?>
        <div class="image-preview-item">
            <img src="../<?= htmlspecialchars($img) ?>" alt="Lab Image">
            <button type="button" class="remove-btn" onclick="removeExistingImage('<?= htmlspecialchars($img) ?>', '<?= $checkbox_id ?>')">
                <i class="fas fa-times"></i>
            </button>
            <input type="checkbox" name="delete_images[]" value="<?= htmlspecialchars($img) ?>" style="display:none;" id="<?= $checkbox_id ?>">
        </div>
        <?php
    }
} else {
    echo '<p class="text-muted">No images uploaded yet.</p>';
}
?>

          </div>
        </div>
        
        <div class="mb-3">
          <label class="form-label">Add New Images</label>
          <input type="file" name="images[]" class="form-control" accept="image/*" multiple id="imageInput">
          <div class="d-flex gap-2 flex-wrap mt-2" id="newImagePreview"></div>
        </div>
        
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Update Lab
          </button>
          <a href="manage_labs.php" class="btn btn-secondary">
            <i class="fas fa-times me-2"></i>Cancel
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function removeExistingImage(imgPath, md5id) {
    if (confirm('Delete this image?')) {
      // Check the hidden checkbox
      const checkbox = document.getElementById(md5id);
      if (checkbox) checkbox.checked = true;
      // Fade out the image preview
      const container = checkbox.closest('.image-preview-item');
      if (container) container.style.display = 'none';
    }
  }

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
          container.appendChild(img);
        };
        reader.readAsDataURL(file);
      });
    }
  });
</script>


</body>
</html>
