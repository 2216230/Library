<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
session_start();
include '../includes/conn.php';

require '../../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    
    // Check if email exists in the admin table
    $stmt = $conn->prepare("SELECT * FROM admin WHERE gmail = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(50));
        date_default_timezone_set('Asia/Manila');
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // ✅ Save reset token directly in admin table
        $stmt_update = $conn->prepare("UPDATE admin SET reset_token = ?, reset_expires = ? WHERE gmail = ?");
        $stmt_update->bind_param("sss", $token, $expiry, $email);
        $stmt_update->execute();

        // Send reset email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'marijoysapditbsu@gmail.com'; // your Gmail
            $mail->Password   = 'ihzfufsmsyobxxaf';            // your 16-character app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('marijoysapditbsu@gmail.com', 'BSU Library System');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "Password Reset Request";
            
            // Get the base URL dynamically
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $resetLink = "{$protocol}://{$host}/libsystem5/1/libsystem/admin/reset_password.php?token={$token}";
            
            $mail->Body = "
                <h2>BSU Library Management System</h2>
                <p>You requested a password reset. Click the link below to set a new password:</p>
                <p><a href='{$resetLink}' style='background: #20650A; color: #F0D411; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>Reset Password</a></p>
                <p>Or copy this link: <br><code>{$resetLink}</code></p>
                <p><strong>This link will expire in 1 hour.</strong></p>
                <hr>
                <p style='color: #999; font-size: 12px;'>If you didn't request this password reset, please ignore this email.</p>
            ";

            $mail->send();
            $_SESSION['success'] = "A password reset link has been sent to your email.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $_SESSION['error'] = "No account found with that email.";
    }

    header("Location: forgot_password.php");
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
            background: linear-gradient(135deg, #20650A 0%, #184d08 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .forgot-container {
            width: 100%;
            max-width: 450px;
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
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 100, 0, 0.3);
            overflow: hidden;
            border: 2px solid #F0D411;
        }

        .forgot-header {
            background: linear-gradient(135deg, #20650A 0%, #184d08 100%);
            color: #F0D411;
            padding: 40px 30px;
            text-align: center;
        }

        .forgot-header h2 {
            font-weight: 800;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .forgot-header p {
            font-size: 14px;
            opacity: 0.9;
            margin: 0;
        }

        .forgot-body {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #20650A;
            font-size: 14px;
        }

        .form-control {
            border: 2px solid #20650A;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #F0D411;
            box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
            background-color: #f8fff8;
        }

        .form-control::placeholder {
            color: #999;
        }

        .btn-reset {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #20650A 0%, #184d08 100%);
            color: #F0D411;
            border: 2px solid #F0D411;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 100, 0, 0.4);
            background: linear-gradient(135deg, #184d08 0%, #20650A 100%);
            color: #F0D411;
            text-decoration: none;
        }

        .back-to-login {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .back-to-login a {
            color: #20650A;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-to-login a:hover {
            color: #F0D411;
            text-decoration: underline;
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
            border: none;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
            padding: 15px;
            border-left: 4px solid #c62828;
        }

        .alert-success {
            background: linear-gradient(135deg, #32CD32 0%, #184d08 100%);
            color: white;
            padding: 15px;
            border-left: 4px solid #1b5e20;
        }

        .forgot-footer {
            text-align: center;
            padding: 20px;
            color: #999;
            font-size: 12px;
        }

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #20650A;
            pointer-events: none;
        }

        .form-control-with-icon {
            padding-right: 40px;
        }

        .help-text {
            background: #f0fff0;
            border-left: 4px solid #20650A;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #333;
        }

        @media (max-width: 576px) {
            .forgot-container {
                margin: 20px;
            }

            .forgot-header {
                padding: 30px 20px;
            }

            .forgot-header h2 {
                font-size: 24px;
            }

            .forgot-body {
                padding: 30px 20px;
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
                <p>Admin & SuperAdmin Password Recovery</p>
            </div>

            <!-- Body -->
            <div class="forgot-body">
                <!-- Info Box -->
                <div class="help-text">
                    <i class="fa fa-info-circle"></i> 
                    <strong>Instructions:</strong> Enter your admin email address and we'll send you a link to reset your password.
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
                        <label for="email">
                            <i class="fa fa-envelope"></i> Email Address
                        </label>
                        <div class="input-icon">
                            <input type="email" 
                                   class="form-control form-control-with-icon" 
                                   id="email" 
                                   name="email" 
                                   placeholder="admin@example.com" 
                                   required>
                            <i class="fa fa-envelope"></i>
                        </div>
                        <small class="text-muted" style="display: block; margin-top: 5px;">
                            Enter the email address associated with your admin account
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
