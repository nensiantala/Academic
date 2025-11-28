<?php
include 'header.php';
include 'db.php';

$q = $conn->query("SELECT * FROM clubs ORDER BY category, name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Clubs & Activities | Academic Portal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .club-card {
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      transition: transform 0.2s, box-shadow 0.2s;
      overflow: hidden;
    }
    .club-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .category-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    .category-tech {
      background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
      color: white;
    }
    .category-non-tech {
      background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);
      color: white;
    }
    .club-images {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      margin-top: 10px;
    }
    .club-image {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 8px;
      border: 2px solid #e0e0e0;
      cursor: pointer;
      transition: transform 0.2s;
    }
    .club-image:hover {
      transform: scale(1.05);
      border-color: #2a9c62;
    }
    .club-image-modal {
      max-width: 100%;
      max-height: 80vh;
      border-radius: 8px;
    }
  </style>
</head>
<body>

<div class="container py-4">
  <div class="card card-spot p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0"><i class="fas fa-users me-2"></i>Clubs & Activities</h4>
      <div>
        <button class="btn btn-sm btn-outline-primary" onclick="filterClubs('all')">
          <i class="fas fa-filter me-1"></i>All
        </button>
        <button class="btn btn-sm btn-outline-primary" onclick="filterClubs('tech')">
          <i class="fas fa-code me-1"></i>Tech
        </button>
        <button class="btn btn-sm btn-outline-primary" onclick="filterClubs('non-tech')">
          <i class="fas fa-palette me-1"></i>Non-Tech
        </button>
      </div>
    </div>

    <div class="row mt-3" id="clubsContainer">
      <?php if($q && $q->num_rows): while($c=$q->fetch_assoc()): 
        $images = json_decode($c['images_json'] ?? '[]', true);
      ?>
        <div class="col-md-6 mb-4 club-item" data-category="<?= htmlspecialchars($c['category']) ?>">
          <div class="card club-card">
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                  <h5 class="mb-2"><?= htmlspecialchars($c['name']) ?></h5>
                  <span class="category-badge category-<?= htmlspecialchars($c['category']) ?>">
                    <i class="fas <?= $c['category'] == 'tech' ? 'fa-code' : 'fa-palette' ?> me-1"></i>
                    <?= strtoupper($c['category']) ?>
                  </span>
                </div>
              </div>
              
              <?php if (!empty($c['coordinator'])): ?>
                <p class="text-muted small mb-1">
                  <i class="fas fa-user me-1"></i>
                  <strong>Coordinator:</strong> <?= htmlspecialchars($c['coordinator']) ?>
                </p>
              <?php endif; ?>
              
              <?php if (!empty($c['contact'])): ?>
                <p class="text-muted small mb-2">
                  <i class="fas fa-phone me-1"></i>
                  <strong>Contact:</strong> <?= htmlspecialchars($c['contact']) ?>
                </p>
              <?php endif; ?>
              
              <p class="text-muted small mb-3"><?= nl2br(htmlspecialchars($c['description'])) ?></p>
              
              <?php if (count($images) > 0): ?>
  <div class="club-images">
    <?php foreach ($images as $img): 
      $img_url = is_array($img) ? $img['image_url'] : $img;
    ?>
      <img src="<?= htmlspecialchars($img_url) ?>" 
           alt="Club Image" 
           class="club-image"
           onclick="showImageModal('<?= htmlspecialchars($img_url) ?>')">
    <?php endforeach; ?>
  </div>
<?php endif; ?>

            </div>
          </div>
        </div>
      <?php endwhile; else: ?>
        <div class="col-12">
          <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No clubs available at the moment.
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-transparent border-0">
      <div class="modal-body text-center">
        <button type="button" class="btn-close btn-close-white mb-3" data-bs-dismiss="modal"></button>
        <img id="modalImage" src="" class="club-image-modal" alt="Club Image">
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function filterClubs(category) {
    const items = document.querySelectorAll('.club-item');
    items.forEach(item => {
      if (category === 'all' || item.dataset.category === category) {
        item.style.display = '';
      } else {
        item.style.display = 'none';
      }
    });
  }
  
  function showImageModal(imageUrl) {
    document.getElementById('modalImage').src = imageUrl;
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
  }
</script>

<?php include 'footer.php'; ?>
</body>
</html>
