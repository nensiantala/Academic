<?php
session_start();
include '../db.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if (empty($token)) {
    header('Location: admin_forgot_password.php');
    exit;
}

// Verify token
$stmt = $conn->prepare("SELECT email FROM password_resets WHERE token=? AND expires_at > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if (!$row = $result->fetch_assoc()) {
    $error = "Invalid or expired reset link. Please request a new one.";
} else {
    $email = $row['email'];
    
    // Handle password reset
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password !== $confirm_password) {
            $error = "Passwords do not match.";
        } elseif (strlen($new_password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } else {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update admin password
            $update_stmt = $conn->prepare("UPDATE admins SET password=? WHERE email=?");
            $update_stmt->bind_param("ss", $hashed_password, $email);
            $update_stmt->execute();
            $update_stmt->close();
            
            // Delete used token
            $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE token=?");
            $delete_stmt->bind_param("s", $token);
            $delete_stmt->execute();
            $delete_stmt->close();
            
            $success = "Password reset successfully! Redirecting to login...";
            echo "<script>setTimeout(function(){ window.location.href='admin_login.php'; }, 2000);</script>";
        }
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password | Admin Portal</title>
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
    .reset-card {
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
      border: none;
    }
    .reset-icon {
      width: 80px;
      height: 80px;
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
      <div class="card reset-card p-4">
        <div class="reset-icon">
          <i class="fas fa-key"></i>
        </div>
        <h3 class="text-center mb-4">Reset Your Password</h3>
        <p class="text-center text-muted mb-4">Enter your new password below.</p>
        
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
        
        <?php if (empty($success) && empty($error)): ?>
          <form method="POST" id="resetForm">
            <div class="mb-3">
              <label class="form-label"><i class="fas fa-lock me-2"></i>New Password</label>
              <input type="password" name="new_password" class="form-control form-control-lg" placeholder="Enter new password" required minlength="6" id="newPassword">
              <div class="form-text">Must be at least 6 characters long</div>
            </div>
            <div class="mb-3">
              <label class="form-label"><i class="fas fa-lock me-2"></i>Confirm Password</label>
              <input type="password" name="confirm_password" class="form-control form-control-lg" placeholder="Confirm new password" required minlength="6" id="confirmPassword">
            </div>
            <button type="submit" class="btn btn-primary w-100 btn-lg">
              <i class="fas fa-check me-2"></i>Reset Password
            </button>
          </form>
        <?php endif; ?>
        
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
<script>
  // Password match validation
  document.getElementById('resetForm')?.addEventListener('submit', function(e) {
    const newPass = document.getElementById('newPassword').value;
    const confirmPass = document.getElementById('confirmPassword').value;
    
    if (newPass !== confirmPass) {
      e.preventDefault();
      alert('Passwords do not match!');
      return false;
    }
  });
</script>
</body>
</html>











