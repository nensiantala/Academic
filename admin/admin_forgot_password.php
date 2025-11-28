<?php
session_start();
include '../db.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    
    // Check if admin exists
    $stmt = $conn->prepare("SELECT id, name FROM admins WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store token in database
        $token_stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token=?, expires_at=?");
        $token_stmt->bind_param("sssss", $email, $token, $expiry, $token, $expiry);
        $token_stmt->execute();
        $token_stmt->close();
        
        // In a real application, you would send this link via email
        // For now, we'll display it on the page (DEMO ONLY)
        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/admin_reset_password.php?token=" . $token;
        
        $success = "If an account exists with this email, you will receive reset instructions.<br><br><strong>Demo Reset Link:</strong><br><a href='$reset_link' target='_blank'>$reset_link</a><br><br><small class='text-muted'>In production, this link would be sent via email.</small>";
    } else {
        // Don't reveal if email exists (security best practice)
        $success = "If an account exists with this email, you will receive reset instructions.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Forgot Password | Academic Portal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .forgot-card {
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
      border: none;
    }
    .forgot-icon {
      width: 80px;
      height: 80px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      color: white;
      font-size: 2rem;
    }
  </style>
</head>
<body>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card forgot-card p-4">
        <div class="forgot-icon">
          <i class="fas fa-lock"></i>
        </div>
        <h3 class="text-center mb-4">Forgot Password?</h3>
        <p class="text-center text-muted mb-4">Enter your email address and we'll send you a link to reset your password.</p>
        
        <?php if (!empty($success)): ?>
          <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            <?= $success ?>
          </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>
        
        <form method="POST">
          <div class="mb-3">
            <label class="form-label"><i class="fas fa-envelope me-2"></i>Email Address</label>
            <input type="email" name="email" class="form-control form-control-lg" placeholder="Enter your email" required>
          </div>
          <button type="submit" class="btn btn-primary w-100 btn-lg">
            <i class="fas fa-paper-plane me-2"></i>Send Reset Link
          </button>
        </form>
        
        <div class="mt-4 text-center">
          <a href="admin_login.php" class="text-decoration-none">
            <i class="fas fa-arrow-left me-2"></i>Back to Login
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>











