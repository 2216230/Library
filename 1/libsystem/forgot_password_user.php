<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
session_start();
include 'includes/conn.php';

require '../vendor/autoload.php';

// Ensure reset_token and reset_expires columns exist in students table
$checkCol = $conn->query("SHOW COLUMNS FROM students LIKE 'reset_token'");
if ($checkCol->num_rows == 0) {
    $conn->query("ALTER TABLE students ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL");
}
$checkCol = $conn->query("SHOW COLUMNS FROM students LIKE 'reset_expires'");
if ($checkCol->num_rows == 0) {
    $conn->query("ALTER TABLE students ADD COLUMN reset_expires DATETIME DEFAULT NULL");
}

// Ensure reset_token and reset_expires columns exist in faculty table
$checkCol = $conn->query("SHOW COLUMNS FROM faculty LIKE 'reset_token'");
if ($checkCol->num_rows == 0) {
    $conn->query("ALTER TABLE faculty ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL");
}
$checkCol = $conn->query("SHOW COLUMNS FROM faculty LIKE 'reset_expires'");
if ($checkCol->num_rows == 0) {
    $conn->query("ALTER TABLE faculty ADD COLUMN reset_expires DATETIME DEFAULT NULL");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = trim($_POST['user_id']);
    $email = trim($_POST['email']);
    
    $user = null;
    $user_type = null;
    
    // Secret admin check - if user_id is "admin", check admin table by email
    if (strtolower($user_id) === 'admin') {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE gmail = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $user_type = 'admin';
        }
    } else {
        // Check in students table
        $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ? AND email = ?");
        $stmt->bind_param("ss", $user_id, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $user_type = 'student';
        } else {
            // Check in faculty table
            $stmt = $conn->prepare("SELECT * FROM faculty WHERE faculty_id = ? AND email = ?");
            $stmt->bind_param("ss", $user_id, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $user_type = 'faculty';
            }
        }
    }

    if ($user) {
        $token = bin2hex(random_bytes(50));
        date_default_timezone_set('Asia/Manila');
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Update reset token in appropriate table
        if ($user_type == 'student') {
            $stmt_update = $conn->prepare("UPDATE students SET reset_token = ?, reset_expires = ? WHERE student_id = ?");
            $stmt_update->bind_param("sss", $token, $expiry, $user_id);
            $stmt_update->execute();
        } elseif ($user_type == 'faculty') {
            $stmt_update = $conn->prepare("UPDATE faculty SET reset_token = ?, reset_expires = ? WHERE faculty_id = ?");
            $stmt_update->bind_param("sss", $token, $expiry, $user_id);
            $stmt_update->execute();
        } elseif ($user_type == 'admin') {
            $stmt_update = $conn->prepare("UPDATE admin SET reset_token = ?, reset_expires = ? WHERE gmail = ?");
            $stmt_update->bind_param("sss", $token, $expiry, $email);
            $stmt_update->execute();
        }

        // Send reset email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'marijoysapditbsu@gmail.com'; // your Gmail
            $mail->Password   = 'ihzfufsmsyobxxaf';            // your app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('marijoysapditbsu@gmail.com', 'BSU Library System');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "Password Reset Request - BSU Library";
            
            // Get the base URL dynamically
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $resetLink = "{$protocol}://{$host}/libsystem5/1/libsystem/reset_password_user.php?token={$token}&type={$user_type}";
            
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #006400;'>BSU Library Management System</h2>
                    <p>Hello {$user['firstname']},</p>
                    <p>You requested a password reset for your library account. Click the button below to set a new password:</p>
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='{$resetLink}' style='background: #006400; color: #FFD700; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold; font-size: 16px;'>Reset Password</a>
                    </p>
                    <p>Or copy this link if the button doesn't work:</p>
                    <p style='background: #f5f5f5; padding: 10px; border-radius: 3px; word-break: break-all;'><code>{$resetLink}</code></p>
                    <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
                    <p><strong>⏰ Important:</strong> This link will expire in 1 hour for security purposes.</p>
                    <p style='color: #999; font-size: 12px;'>If you didn't request this password reset, please ignore this email. Your account will remain secure.</p>
                </div>
            ";

            $mail->send();
            $_SESSION['success'] = "A password reset link has been sent to your email ({$email}). Check your inbox!";
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to send email. Error: {$mail->ErrorInfo}";
        }
    } else {
        $_SESSION['error'] = "No account found with that User ID and Email combination.";
    }

    header("Location: forgot_password_user.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - BSU Library System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #004d40 0%, #00695c 50%, #004d40 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 15px;
        }

        .forgot-container {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .forgot-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4), 0 5px 15px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            border: 2px solid #FFD700;
        }

        .forgot-header {
            background: linear-gradient(135deg, #004d00 0%, #198754 100%);
            color: #FFD700;
            padding: 19px 18px;
            text-align: center;
        }

        .forgot-header h2 {
            font-weight: 700;
            margin-bottom: 4px;
            font-size: 18px;
        }

        .forgot-header p {
            font-size: 11px;
            opacity: 0.9;
            margin: 0;
        }

        .forgot-body {
            padding: 19px 18px;
        }

        .form-group {
            margin-bottom: 13px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #004d00;
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

        .btn-reset {
            width: 100%;
            padding: 9px;
            background: #004d00;
            color: #FFD700;
            border: 2px solid #FFD700;
            border-radius: 5px;
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 7px;
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 77, 0, 0.4);
            background: #198754;
        }

        .back-to-login {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .back-to-login a {
            color: #004d00;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .back-to-login a:hover {
            color: #198754;
            text-decoration: underline;
        }

        .alert-danger {
            background: #dc3545;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .alert-success {
            background: #198754;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .forgot-footer {
            text-align: center;
            padding: 10px 13px;
            color: rgba(255,255,255,0.8);
            font-size: 10px;
            background: transparent;
            border-top: none;
            margin-top: 8px;
        }

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #004d00;
            pointer-events: none;
        }

        .form-control-with-icon {
            padding-right: 40px;
        }

        .help-text {
            background: #f0f8f0;
            border-left: 3px solid #FFD700;
            padding: 9px 10px;
            border-radius: 4px;
            margin-bottom: 13px;
            font-size: 11px;
            color: #333;
        }

        @media (max-width: 576px) {
            .forgot-container {
                margin: 10px;
            }

            .forgot-header {
                padding: 25px 20px;
            }

            .forgot-header h2 {
                font-size: 20px;
            }

            .forgot-body {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-card">
            <!-- Header -->
            <div class="forgot-header">
                <h2><i class="fa fa-key"></i> Forgot Password</h2>
                <p>Student & Faculty Account Recovery</p>
            </div>

            <!-- Body -->
            <div class="forgot-body">
                <!-- Info Box -->
                <div class="help-text">
                    <i class="fa fa-info-circle"></i> 
                    <strong>Instructions:</strong> Enter your User ID and email address. We'll send you a link to reset your password.
                </div>

                <!-- Error Alert -->
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fa fa-exclamation-circle"></i>
                        <strong> Error!</strong> <?php echo $_SESSION['error']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- Success Alert -->
                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fa fa-check-circle"></i>
                        <strong> Success!</strong> <?php echo $_SESSION['success']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <!-- Reset Form -->
                <form method="POST">
                    <div class="form-group">
                        <label for="user_id">
                            <i class="fa fa-id-card"></i> User ID
                        </label>
                        <div class="input-icon">
                            <input type="text" 
                                   class="form-control form-control-with-icon" 
                                   id="user_id" 
                                   name="user_id" 
                                   placeholder="Student ID or Faculty ID" 
                                   required>
                            <i class="fa fa-id-card"></i>
                        </div>
                        <small class="text-muted" style="display: block; margin-top: 5px;">
                            Your Student ID (e.g., 2324) or Faculty ID (e.g., 56A)
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <i class="fa fa-envelope"></i> Email Address
                        </label>
                        <div class="input-icon">
                            <input type="email" 
                                   class="form-control form-control-with-icon" 
                                   id="email" 
                                   name="email" 
                                   placeholder="your.email@example.com" 
                                   required>
                            <i class="fa fa-envelope"></i>
                        </div>
                        <small class="text-muted" style="display: block; margin-top: 5px;">
                            Enter the email address associated with your account
                        </small>
                    </div>

                    <button type="submit" class="btn-reset">
                        <i class="fa fa-paper-plane"></i> Send Reset Link
                    </button>
                </form>

                <!-- Back to Login -->
                <div class="back-to-login">
                    <a href="index.php">
                        <i class="fa fa-arrow-left"></i> Back to Login
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="forgot-footer">
            <p>
                <i class="fa fa-lock"></i> 
                Your password reset link will expire in 1 hour for security purposes.
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
