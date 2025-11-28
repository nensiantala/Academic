<?php
require 'db.php';
include 'header.php';

$q = $conn->query("SELECT * FROM labs ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Labs & Facilities | Academic Portal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .lab-card {
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      transition: transform 0.2s, box-shadow 0.2s;
      overflow: hidden;
    }
    .lab-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .lab-images {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      margin-top: 10px;
    }
    .lab-image {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 8px;
      border: 2px solid #e0e0e0;
      cursor: pointer;
      transition: transform 0.2s;
    }
    .lab-image:hover {
      transform: scale(1.05);
      border-color: #2a9c62;
    }
    .lab-image-modal {
      max-width: 100%;
      max-height: 80vh;
      border-radius: 8px;
    }
    /* Make carousel images smaller */
    .lab-carousel-img {
      max-height: 260px;
      width: 100%;
      object-fit: cover;
      border-radius: 8px;
    }
  </style>
</head>
<body>

<div class="container py-4">
  <div class="card card-spot p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0"><i class="fas fa-flask me-2"></i>Laboratories & Facilities</h4>
    </div>

    <div class="row mt-3">
  <?php if($q && $q->num_rows): while($l=$q->fetch_assoc()): 
$images = !empty($l['images_json']) ? json_decode($l['images_json'], true) : [];
$hasImages = !empty($images);

  ?>
    <div class="col-md-6 mb-4">
      <div class="card lab-card">
        <div class="card-body p-4">
          <h5 class="mb-3">
            <i class="fas fa-flask me-2 text-success"></i><?= htmlspecialchars($l['name']) ?>
          </h5>
          
          <div class="mb-3">
            <p class="text-muted small mb-1">
              <i class="fas fa-user me-1"></i>
              <strong>In-charge:</strong> <?= htmlspecialchars($l['incharge']) ?>
            </p>
            <p class="text-muted small mb-1">
              <i class="fas fa-clock me-1"></i>
              <strong>Timings:</strong> <?= htmlspecialchars($l['timings']) ?>
            </p>
          </div>
          
          <div class="mb-3">
            <p class="text-muted small mb-2"><strong>Equipment:</strong></p>
            <p class="text-muted small"><?= nl2br(htmlspecialchars($l['equipment'])) ?></p>
          </div>

          <!-- Carousel Section -->
          <?php if (!empty($images)): ?>
  <div id="labCarousel<?= $l['id'] ?>" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
      <?php foreach ($images as $index => $img): ?>
        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
          <img src="<?= htmlspecialchars($img) ?>" class="d-block lab-carousel-img" alt="Lab Image">
        </div>
      <?php endforeach; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#labCarousel<?= $l['id'] ?>" data-bs-slide="prev">
      <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#labCarousel<?= $l['id'] ?>" data-bs-slide="next">
      <span class="carousel-control-next-icon"></span>
    </button>
  </div>
<?php endif; ?>

        </div>
      </div>
    </div>
  <?php endwhile; else: ?>
    <div class="col-12">
      <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>No lab information available.
      </div>
    </div>
  <?php endif; ?>

  </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-transparent border-0">
      <div class="modal-body text-center">
        <button type="button" class="btn-close btn-close-white mb-3" data-bs-dismiss="modal"></button>
        <img id="modalImage" src="" class="lab-image-modal" alt="Lab Image">
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function showImageModal(imageUrl) {
    document.getElementById('modalImage').src = imageUrl;
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
  }
</script>

<?php include 'footer.php'; ?>
</body>
</html>
