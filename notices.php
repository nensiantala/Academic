<?php
require 'db.php';
include 'header.php';

// Fetch notices from database (order by date descending)
$notices = $conn->query("SELECT * FROM notices ORDER BY date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notices | Academic Portal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .notice-card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      transition: transform 0.2s, box-shadow 0.2s;
      margin-bottom: 1.5rem;
      border-left: 4px solid #00A9B4;
    }
    .notice-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .notice-card .card-body {
      padding: 1.5rem;
    }
    .notice-date {
      color: #6c757d;
      font-size: 0.9rem;
    }
    .notice-title {
      color: #333;
      font-weight: 600;
      margin-bottom: 0.5rem;
    }
    .notice-description {
      color: #555;
      line-height: 1.6;
      margin-bottom: 1rem;
    }
    .file-download-btn {
      background-color: #00A9B4;
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 6px;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      transition: background-color 0.3s;
    }
    .file-download-btn:hover {
      background-color: #008f99;
      color: white;
    }
  </style>
</head>
<body>

<div class="container py-4">
  <div class="card card-spot p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Notices</h4>
    </div>

    <div class="row mt-3">
      <div class="col-lg-12">
        <?php if ($notices && $notices->num_rows > 0): ?>
          <?php while ($notice = $notices->fetch_assoc()): ?>
            <div class="card notice-card">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <h5 class="notice-title"><?= htmlspecialchars($notice['title'] ?? 'Untitled Notice') ?></h5>
                  <div class="notice-date">
                    <i class="fas fa-calendar-alt me-1"></i>
                    <?= !empty($notice['date']) ? date('d M Y', strtotime($notice['date'])) : '' ?>
                  </div>
                </div>

                <?php if (!empty($notice['description'])): ?>
                  <div class="notice-description">
                    <?= nl2br(htmlspecialchars($notice['description'])) ?>
                  </div>
                <?php endif; ?>

                <?php 
                // Handle file path - check if it's already a full path or just filename
                $filePath = '';
                if (!empty($notice['file'])) {
                    $fileValue = trim($notice['file']);
                    // If it already contains 'uploads/notices/', use it as is
                    if (strpos($fileValue, 'uploads/notices/') !== false) {
                        $filePath = $fileValue;
                    } 
                    // If it starts with 'uploads/', use it as is
                    elseif (strpos($fileValue, 'uploads/') === 0) {
                        $filePath = $fileValue;
                    }
                    // If it contains 'notices/', prepend 'uploads/'
                    elseif (strpos($fileValue, 'notices/') !== false) {
                        $filePath = 'uploads/' . $fileValue;
                    }
                    // Otherwise, prepend 'uploads/notices/'
                    else {
                        $filePath = 'uploads/notices/' . $fileValue;
                    }
                    
                    // Verify file exists
                    if (!file_exists($filePath)) {
                        $filePath = ''; // Don't show link if file doesn't exist
                    }
                }
                if (!empty($filePath)): ?>
                  <div class="mt-2">
                    <a href="<?= htmlspecialchars($filePath) ?>" 
                       target="_blank" 
                       class="file-download-btn">
                      <i class="fas fa-file-download"></i>
                      Download Attachment
                    </a>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No notices available at the moment.
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'footer.php'; ?>
</body>
</html>
