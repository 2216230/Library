<?php
session_start();
include '../includes/conn.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $conn->prepare("SELECT * FROM admin WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row || strtotime($row['reset_expires']) < time()) {
        $_SESSION['error'] = "Invalid or expired reset token.";
        header("Location: forgot_password.php");
        exit();
    }

    $email = $row['gmail'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $new_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admin SET password=?, reset_token=NULL, reset_expires=NULL WHERE gmail=?");
        $stmt->bind_param("ss", $new_pass, $email);
        $stmt->execute();

        $_SESSION['success'] = "Your password has been successfully reset.";
        header("Location: index.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Invalid access.";
    header("Location: forgot_password.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #0b3d2e; display:flex; align-items:center; justify-content:center; height:100vh; }
.card { width:400px; border-radius:12px; border:2px solid #d4af37; }
.btn-gold { background-color:#d4af37; color:#0b3d2e; font-weight:bold; }
.btn-gold:hover { background-color:#e6c75b; }
</style>
</head>
<body>
  <div class="card p-4">
    <h3 class="text-center mb-3 text-success">Reset Password</h3>
    <form method="POST">
      <div class="mb-3">
        <input type="password" name="password" class="form-control" placeholder="Enter new password" required>
      </div>
      <button type="submit" class="btn btn-gold w-100">Update Password</button>
    </form>
  </div>
</body>
</html>
