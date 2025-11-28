<?php
session_start();
require 'db.php';

// Register handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_event'])) {
    $eid = intval($_POST['event_id']);
    $name = $conn->real_escape_string(trim($_POST['name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $remarks = $conn->real_escape_string(trim($_POST['remarks']));

    // Check if event still accepts registrations (not ended)
    $check_query = $conn->query("SELECT end_date FROM events WHERE id = $eid");
    if ($check_query && $row = $check_query->fetch_assoc()) {
        $end_date = strtotime($row['end_date']);
        $today = strtotime(date('Y-m-d'));
        
        if ($end_date < $today) {
            $_SESSION['event_error'] = "Registration closed. This event has already ended.";
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        }
    }

    $stmt = $conn->prepare("INSERT INTO event_registrations (event_id, name, email, phone, remarks) VALUES (?,?,?,?,?)");
    if ($stmt) {
        $stmt->bind_param('issss', $eid, $name, $email, $phone, $remarks);
        $stmt->execute();
        $stmt->close();

        // store registered event in session so Register link is hidden for this session
        if (!isset($_SESSION['registered_events']) || !is_array($_SESSION['registered_events'])) {
            $_SESSION['registered_events'] = [];
        }
        $_SESSION['registered_events'][] = $eid;

        // small success message (will be shown in HTML)
        $_SESSION['event_success'] = "Thank you for registering. We will contact you soon.";
        // Redirect to avoid reposting on refresh (keeps message available in session)
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    } else {
        // optionally handle prepare error
        $_SESSION['event_error'] = "Unable to register at the moment. Please try again.";
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
}

// fetch events (most recent first)
$events = $conn->query("SELECT * FROM events ORDER BY start_date DESC");
include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Events | Academic Portal</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="assets/css/style.css"/>
  <style>
    /* small inline override for the success box */
    #successMsg {
      font-size: 0.95rem;
      padding: .5rem .75rem;
      max-width: 720px;
      margin: 12px auto;
    }
    
    .event-image-carousel {
      position: relative;
      margin: 15px 0;
      height: 350px;
      overflow: hidden;
      border-radius: 8px;
      background: #f5f5f5;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .event-image-carousel img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    
    .carousel-control-prev,
    .carousel-control-next {
      background: rgba(0,0,0,0.5);
      border-radius: 50%;
      width: 45px;
      height: 45px;
      top: 50%;
      transform: translateY(-50%);
      opacity: 0.8;
    }
    
    .carousel-control-prev:hover,
    .carousel-control-next:hover {
      opacity: 1;
    }
    
    .carousel-indicators {
      bottom: 10px;
      margin-bottom: 0;
    }
    
    .carousel-indicators button {
      background-color: rgba(255,255,255,0.8);
      border: 1px solid rgba(0,0,0,0.2);
      border-radius: 50%;
      width: 12px;
      height: 12px;
      margin: 0 4px;
    }
    
    .carousel-indicators button.active {
      background-color: #fff;
    }
    
    .event-card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      transition: transform 0.2s, box-shadow 0.2s;
      overflow: hidden;
    }
    
    .event-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    
    .event-card .card-body {
      padding: 1.5rem;
    }
    
    .event-ended {
      opacity: 0.8;
      background: linear-gradient(135deg, #f5f5f5 0%, #ffffff 100%);
    }
    
    .event-ended:hover {
      transform: none;
    }
    
    .past-event-badge {
      animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.7; }
    }
  </style>
</head>
<body>

<div class="container py-4">
  <!-- success message (from session) -->
  <?php if (!empty($_SESSION['event_success'])): ?>
    <div id="successMsg" class="alert alert-success small text-center">
      <?= htmlspecialchars($_SESSION['event_success']) ?>
    </div>
    <?php unset($_SESSION['event_success']); ?>
  <?php endif; ?>

  <?php if (!empty($_SESSION['event_error'])): ?>
    <div id="errorMsg" class="alert alert-danger small text-center">
      <?= htmlspecialchars($_SESSION['event_error']) ?>
    </div>
    <?php unset($_SESSION['event_error']); ?>
  <?php endif; ?>

  <div class="card card-spot p-3">
    <div class="d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Events</h4>
      <!-- reserved for future controls -->
    </div>

    <div class="row mt-3">
      <div class="col-lg-12">
        <?php if ($events && $events->num_rows): ?>
          <?php while ($e = $events->fetch_assoc()): ?>
            <?php
              // Check if event has ended
              $event_ended = strtotime($e['end_date']) < strtotime(date('Y-m-d'));
              $card_class = $event_ended ? 'event-ended' : '';
            ?>
            <div id="ev<?= $e['id'] ?>" class="card event-card mb-4 <?= $card_class ?>">
              <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div>
                    <h5 class="mb-2"><?= htmlspecialchars($e['title']) ?></h5>
                    <div class="text-muted small">
                      <i class="fas fa-calendar-alt me-1"></i>
                      <?= date('d M Y', strtotime($e['start_date'])) ?> - <?= date('d M Y', strtotime($e['end_date'])) ?>
                      <span class="mx-2">|</span>
                      <i class="fas fa-map-marker-alt me-1"></i>
                      <?= htmlspecialchars($e['venue']) ?>
                    </div>
                  </div>

                  <div class="text-end">
                    <?php if (!empty($e['event_type'])): ?>
                      <span class="badge bg-info text-dark mb-2"><?= htmlspecialchars($e['event_type']) ?></span><br>
                    <?php endif; ?>
                    <?php
                      // Check if user already registered
                      $registered = false;
                      if (!empty($_SESSION['registered_events']) && in_array($e['id'], $_SESSION['registered_events'])) {
                          $registered = true;
                      }
                      
                      if ($event_ended) {
                          echo '<span class="badge bg-secondary past-event-badge mb-2"><i class="fas fa-clock me-1"></i>Event Ended</span>';
                      } elseif ($registered) {
                          echo '<span class="text-success small fw-semibold"><i class="fas fa-check-circle me-1"></i>Registered</span>';
                      } else {
                          echo '<a href="#" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#registerModal" data-eventid="' . $e['id'] . '" data-eventtitle="' . htmlspecialchars($e['title'], ENT_QUOTES) . '"><i class="fas fa-user-plus me-1"></i>Register</a>';
                      }
                    ?>
                  </div>
                </div>

                <?php if (!empty($e['description'])): ?>
                  <p class="text-muted mb-3"><?= nl2br(htmlspecialchars($e['description'])) ?></p>
                <?php endif; ?>

                <!-- Image Carousel -->
                <?php 
                  $images = [];
                  if (!empty($e['images_json'])) {
                    $decoded = json_decode($e['images_json'], true);
                    if (is_array($decoded)) { $images = $decoded; }
                  } else {
                    // If images_json is empty, show a default or nothing
                    $images = [];
                  }
                if (count($images) > 0): ?>
                  <div id="carouselEvent<?= $e['id'] ?>" class="carousel slide event-image-carousel" data-bs-ride="carousel">
                    <!-- Carousel Indicators -->
                    <?php if (count($images) > 1): ?>
                      <div class="carousel-indicators">
                        <?php foreach ($images as $index => $img_url): ?>
                          <button type="button" data-bs-target="#carouselEvent<?= $e['id'] ?>" data-bs-slide-to="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>"></button>
                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>
                    
                    <!-- Carousel Items -->
                    <div class="carousel-inner">
                      <?php foreach ($images as $index => $img_url): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                          <img src="<?= htmlspecialchars($img_url) ?>" class="d-block w-100" alt="Event Image <?= $index + 1 ?>">
                        </div>
                      <?php endforeach; ?>
                    </div>
                    
                    <!-- Carousel Controls -->
                    <?php if (count($images) > 1): ?>
                      <button class="carousel-control-prev" type="button" data-bs-target="#carouselEvent<?= $e['id'] ?>" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                      </button>
                      <button class="carousel-control-next" type="button" data-bs-target="#carouselEvent<?= $e['id'] ?>" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                      </button>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No events available at the moment.
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Registration modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <input type="hidden" name="event_id" id="modal_event_id" value="">
      <div class="modal-header">
        <h5 class="modal-title">Register for Event</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">Name</label>
          <input name="name" required class="form-control" />
        </div>
        <div class="mb-2">
          <label class="form-label">Email</label>
          <input name="email" type="email" required class="form-control" />
        </div>
        <div class="mb-2">
          <label class="form-label">Phone</label>
          <input name="phone" class="form-control" />
        </div>
        <div class="mb-2">
          <label class="form-label">Remarks</label>
          <textarea name="remarks" class="form-control"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="register_event" class="btn btn-brand">Register</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Populate modal with event info
  var registerModal = document.getElementById('registerModal');
  if (registerModal) {
    registerModal.addEventListener('show.bs.modal', function (event) {
      var button = event.relatedTarget;
      var id = button.getAttribute('data-eventid');
      var title = button.getAttribute('data-eventtitle');
      registerModal.querySelector('.modal-title').textContent = 'Register: ' + title;
      registerModal.querySelector('#modal_event_id').value = id;
    });
  }

  // Auto-hide success message after 2 seconds
  document.addEventListener("DOMContentLoaded", function() {
    var msg = document.getElementById('successMsg');
    if (msg) {
      setTimeout(function() {
        msg.style.transition = "opacity 0.4s";
        msg.style.opacity = "0";
        setTimeout(function() { if (msg.parentNode) msg.parentNode.removeChild(msg); }, 450);
      }, 2000);
    }
  });
</script>

<?php include 'footer.php'; ?>
</body>
</html>
