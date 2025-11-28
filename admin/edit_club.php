<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}
include '../db.php';

$upload_dir = '../uploads/clubs/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$club_id = intval($_GET['id'] ?? 0);

// Fetch club details
$stmt = $conn->prepare("SELECT * FROM clubs WHERE id = ?");
$stmt->bind_param("i", $club_id);
$stmt->execute();
$result = $stmt->get_result();
$club = $result->fetch_assoc();

if (!$club) {
    header('Location: manage_clubs.php');
    exit();
}

// Handle update club
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_club'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $coordinator = $_POST['coordinator'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $category = $_POST['category'] ?? 'non-tech';
    
    $stmt = $conn->prepare("UPDATE clubs SET name = ?, description = ?, coordinator = ?, contact = ?, category = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $name, $description, $coordinator, $contact, $category, $club_id);
    
    if ($stmt->execute()) {
        // Handle new image uploads
        $images_array = [];
        if (!empty($club['images_json'])) {
            $images_array = json_decode($club['images_json'], true) ?: [];
        }
        if (!empty($_POST['delete_images'])) {
            foreach ($_POST['delete_images'] as $delete_path) {
                $delete_path = trim($delete_path);
                $index = array_search($delete_path, $images_array);
                if ($index !== false) {
                    unset($images_array[$index]);
                    if (file_exists('../' . $delete_path)) {
                        unlink('../' . $delete_path);
                    }
                }
            }
            $images_array = array_values($images_array);
        }
        if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0) {
            foreach ($_FILES['images']['name'] as $key => $name) {
                if ($_FILES['images']['error'][$key] == UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES['images']['tmp_name'][$key];
                    $ext = pathinfo($name, PATHINFO_EXTENSION);
                    $new_filename = uniqid() . '_' . time() . '.' . $ext;
                    $upload_path = $upload_dir . $new_filename;
                    if (move_uploaded_file($tmp_name, $upload_path)) {
                        $images_array[] = 'uploads/clubs/' . $new_filename;
                    }
                }
            }
        }
        $images_json = json_encode(array_values($images_array));

// ✅ Save updated image list to DB
$update_json_stmt = $conn->prepare("UPDATE clubs SET images_json = ? WHERE id = ?");
$update_json_stmt->bind_param("si", $images_json, $club_id);
$update_json_stmt->execute();
$update_json_stmt->close();

header('Location: manage_clubs.php?success=updated');
exit();

    }
    $stmt->close();
}

// Fetch existing images
$images_array = [];
if (!empty($club['images_json'])) {
    $images_array = json_decode($club['images_json'], true) ?: [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Club | Admin Panel</title>
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
    <li><a href="manage_clubs.php" class="active"><i class="fas fa-users"></i>Clubs</a></li>
    <li><a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
  </ul>
</div>

<div class="admin-main">
  <div class="admin-header mb-4">
    <h2><i class="fas fa-edit me-2"></i>Edit Club</h2>
  </div>
  
  <div class="card">
    <div class="card-body">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="update_club" value="1">
        
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Club Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($club['name']) ?>" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Category <span class="text-danger">*</span></label>
            <select name="category" class="form-control" required>
              <option value="tech" <?= $club['category'] == 'tech' ? 'selected' : '' ?>>Tech</option>
              <option value="non-tech" <?= $club['category'] == 'non-tech' ? 'selected' : '' ?>>Non-Tech</option>
            </select>
          </div>
        </div>
        
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Coordinator</label>
            <input type="text" name="coordinator" class="form-control" value="<?= htmlspecialchars($club['coordinator'] ?? '') ?>">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Contact</label>
            <input type="text" name="contact" class="form-control" value="<?= htmlspecialchars($club['contact'] ?? '') ?>">
          </div>
        </div>
        
        <div class="mb-3">
          <label class="form-label">Description <span class="text-danger">*</span></label>
          <textarea name="description" class="form-control" rows="4" required><?= htmlspecialchars($club['description']) ?></textarea>
        </div>
        
        <div class="mb-3">
          <label class="form-label">Existing Images</label>
          <div class="image-preview-container" id="existingImages">
            <?php foreach ($images_array as $idx => $image_path): ?>
              <div class="image-preview-item">
                <img src="../<?= htmlspecialchars($image_path) ?>" alt="Club Image">
                <button type="button" class="remove-btn" onclick="removeExistingImage('<?= $idx ?>')">
                  <i class="fas fa-times"></i>
                </button>
                <input type="checkbox" name="delete_images[]" value="<?= htmlspecialchars($image_path) ?>" style="display:none;" id="delete_<?= $idx ?>">
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        
        <div class="mb-3">
          <label class="form-label">Add New Images</label>
          <input type="file" name="images[]" class="form-control" accept="image/*" multiple id="imageInput">
          <div class="d-flex gap-2 flex-wrap mt-2" id="newImagePreview"></div>
        </div>
        
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Update Club
          </button>
          <a href="manage_clubs.php" class="btn btn-secondary">
            <i class="fas fa-times me-2"></i>Cancel
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function removeExistingImage(idx) {
    if (confirm('Delete this image?')) {
      document.getElementById('delete_' + idx).checked = true;
      document.getElementById('delete_' + idx).closest('.image-preview-item').style.opacity = '0.5';
      document.getElementById('delete_' + idx).closest('.image-preview-item').style.pointerEvents = 'none';
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

