<?php
session_start();
include 'includes/conn.php';

$error = '';
$success = '';

if (isset($_GET['token']) && isset($_GET['type'])) {
    $token = $_GET['token'];
    $user_type = $_GET['type'];
    
    if ($user_type == 'student') {
        $stmt = $conn->prepare("SELECT * FROM students WHERE reset_token = ?");
    } elseif ($user_type == 'faculty') {
        $stmt = $conn->prepare("SELECT * FROM faculty WHERE reset_token = ?");
    } elseif ($user_type == 'admin') {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE reset_token = ?");
    } else {
        $error = "Invalid user type.";
    }
    
    if (!$error) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if (!$row || !isset($row['reset_expires']) || strtotime($row['reset_expires']) < time()) {
            $error = "Invalid or expired reset token. Please request a new password reset.";
        }
    }
} else {
    $error = "Invalid access. Please request a password reset.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['password']) && !$error) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match!";
    } elseif (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long!";
    } else {
        $new_pass = password_hash($password, PASSWORD_DEFAULT);
        
        if ($user_type == 'student') {
            $stmt = $conn->prepare("UPDATE students SET password=?, reset_token=NULL, reset_expires=NULL WHERE student_id=?");
            $user_id = $row['student_id'];
        } elseif ($user_type == 'faculty') {
            $stmt = $conn->prepare("UPDATE faculty SET password=?, reset_token=NULL, reset_expires=NULL WHERE faculty_id=?");
            $user_id = $row['faculty_id'];
        } elseif ($user_type == 'admin') {
            $stmt = $conn->prepare("UPDATE admin SET password=?, reset_token=NULL, reset_expires=NULL WHERE gmail=?");
            $user_id = $row['gmail'];
        }
        
        $stmt->bind_param("ss", $new_pass, $user_id);
        $stmt->execute();

        $_SESSION['success'] = "Your password has been successfully reset. You can now login with your new password.";
        header("Location: index.php");
        exit();
    }
    
    if (isset($_SESSION['error'])) {
        header("Location: reset_password_user.php?token={$token}&type={$user_type}");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - BSU Library System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #004d40 0%, #00695c 50%, #004d40 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, sans-serif;
            padding: 15px;
        }

        .reset-container {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        .card {
            background: white;
            border-radius: 10px;
            border: 2px solid #FFD700;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4), 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            background: linear-gradient(135deg, #004d00 0%, #198754 100%);
            color: #FFD700;
            border: none;
            padding: 18px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }

        .card-header h3 {
            margin: 0;
            font-weight: 700;
            font-size: 16px;
        }

        .card-body {
            padding: 19px 18px;
        }

        .form-group {
            margin-bottom: 13px;
        }

        .form-group label {
            font-weight: 600;
            color: #004d00;
            margin-bottom: 5px;
            display: block;
            font-size: 11px;
        }

        .form-control {
            border: 2px solid #004d00;
            border-radius: 5px;
            padding: 9px 10px;
            font-size: 12px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #FFD700;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.3);
            outline: none;
        }

        .btn-success {
            background: #004d00;
            border: 2px solid #FFD700;
            color: #FFD700;
            font-weight: 600;
            padding: 9px;
            border-radius: 5px;
            font-size: 12px;
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 77, 0, 0.4);
            color: #FFD700;
            background: #198754;
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background: #dc3545;
            color: white;
            border: none;
        }

        .password-hint {
            background: #f0f8f0;
            border-left: 3px solid #FFD700;
            padding: 7px 9px;
            border-radius: 4px;
            font-size: 10px;
            color: #333;
            margin-top: 5px;
        }

        .link-back {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .link-back a {
            color: #004d00;
            text-decoration: none;
        }

        .link-back a:hover {
            color: #198754;
            text-decoration: underline;
        }

        @media (max-width: 576px) {
            .reset-container {
                margin: 10px;
            }

            .card-header {
                padding: 20px;
            }

            .card-header h3 {
                font-size: 20px;
            }

            .card-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="card">
            <div class="card-header">
                <h3><i class="fa fa-lock"></i> Reset Password</h3>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                    <div class="text-center">
                        <a href="forgot_password_user.php" class="btn btn-outline-success">Request New Reset Link</a>
                    </div>
                <?php else: ?>
                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <i class="fa fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label for="password">
                                <i class="fa fa-lock"></i> New Password
                            </label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter new password" 
                                   minlength="8"
                                   required>
                            <div class="password-hint">
                                <i class="fa fa-info-circle"></i> 
                                Password must be at least 8 characters long
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">
                                <i class="fa fa-lock"></i> Confirm Password
                            </label>
                            <input type="password" 
                                   class="form-control" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   placeholder="Re-enter password" 
                                   minlength="8"
                                   required>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            <i class="fa fa-check"></i> Update Password
                        </button>
                    </form>

                    <div class="link-back">
                        <a href="index.php">Back to Login</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
