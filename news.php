<?php
require 'db.php';
include 'header.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id){
  $stmt = $conn->prepare("SELECT * FROM announcements WHERE id=?");
  $stmt->bind_param('i',$id);
  $stmt->execute();
  $res = $stmt->get_result()->fetch_assoc();
  $stmt->close();
}
$all = $conn->query("SELECT * FROM news ORDER BY date DESC");
?>
<head>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<div class="row g-3">
  <div class="col-lg-8">
    <div class="card card-spot p-3">
      <h5>News & Announcements</h5>
      <?php if($id && $res): ?>
        <div id="ann<?=$res['id']?>">
          <h6><?=htmlspecialchars($res['title'])?></h6>
          <div class="muted-small"><?=date('d M Y',strtotime($res['date_posted']))?> — Posted by <?=htmlspecialchars($res['posted_by'])?></div>
          <div class="mt-3"><?=nl2br(htmlspecialchars($res['body']))?></div>
          <a class="btn btn-sm btn-outline-brand mt-3" href="news.php">Back</a>
        </div>
      <?php else: ?>
        <div class="list-group mt-2">
          <?php if($all && $all->num_rows): while($a=$all->fetch_assoc()): ?>
            <a href="news.php?id=<?=$a['id']?>" class="list-group-item list-group-item-action">
              <div class="d-flex justify-content-between align-items-start gap-3">
                <?php 
                // Handle image path - check if it's already a full path or just filename
                $img = '';
                if (!empty($a['image']) && is_string($a['image'])) {
                    $imageValue = trim($a['image']);
                    // If it already contains 'uploads/news/', use it as is
                    if (strpos($imageValue, 'uploads/news/') !== false) {
                        $img = $imageValue;
                    } 
                    // If it starts with 'uploads/', use it as is
                    elseif (strpos($imageValue, 'uploads/') === 0) {
                        $img = $imageValue;
                    }
                    // Otherwise, prepend 'uploads/news/'
                    else {
                        $img = 'uploads/news/' . $imageValue;
                    }
                    
                    // Verify file exists
                    if (!file_exists($img)) {
                        $img = ''; // Don't show image if file doesn't exist
                    }
                }
                if (!empty($img)): ?>
                  <img src="<?= htmlspecialchars($img) ?>" alt="News Image" style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px; flex-shrink: 0;">
                <?php endif; ?>
                <div class="flex-grow-1">
                  <div class="d-flex justify-content-between">
                    <div><strong><?=htmlspecialchars($a['title'])?></strong><div class="muted-small"><?=substr(strip_tags($a['content']),0,120)?>...</div></div>
                    <small class="text-muted"><?=date('d M Y',strtotime($a['date']))?></small>
                  </div>
                </div>
              </div>
            </a>
          <?php endwhile; else: ?>
            <div class="p-3 muted-small">No announcements yet.</div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card p-3 card-spot">
      <h6>Quick Notice</h6>
      <p class="muted-small">Important notices will appear here. Admin can set visibility to internal/public.</p>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
