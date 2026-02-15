<?php
session_start();
include 'includes/conn.php';

// If already logged in, redirect to home
if(isset($_SESSION['admin'])){
    header("Location: home.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - BSU Library System</title>
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

        .login-container {
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

        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 100, 0, 0.3);
            overflow: hidden;
            border: 2px solid #F0D411;
        }

        .login-header {
            background: linear-gradient(135deg, #20650A 0%, #184d08 100%);
            color: #F0D411;
            padding: 40px 30px;
            text-align: center;
        }

        .login-header h2 {
            font-weight: 800;
            margin-bottom: 5px;
            font-size: 28px;
        }

        .login-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .login-body {
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

        .btn-login {
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

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 100, 0, 0.4);
            background: linear-gradient(135deg, #184d08 0%, #20650A 100%);
        }

        .forgot-password {
            text-align: right;
            margin-bottom: 20px;
        }

        .forgot-password a {
            color: #20650A;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .forgot-password a:hover {
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

        .admin-badge {
            display: inline-block;
            background: #F0D411;
            color: #20650A;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
        }

        .login-footer {
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

        @media (max-width: 576px) {
            .login-container {
                margin: 20px;
            }

            .login-header {
                padding: 30px 20px;
            }

            .login-header h2 {
                font-size: 24px;
            }

            .login-body {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <h2><i class="fa fa-lock"></i> Admin Login</h2>
                <p>BSU Library Management System</p>
                <span class="admin-badge"><i class="fa fa-shield"></i> Admin/SuperAdmin Only</span>
            </div>

            <!-- Body -->
            <div class="login-body">
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

                <!-- Login Form -->
                <form method="POST" action="login.php">
                    <div class="form-group">
                        <label for="username">
                            <i class="fa fa-envelope"></i> Email Address
                        </label>
                        <div class="input-icon">
                            <input type="email" 
                                   class="form-control form-control-with-icon" 
                                   id="username" 
                                   name="username" 
                                   placeholder="admin@example.com" 
                                   required>
                            <i class="fa fa-envelope"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fa fa-key"></i> Password
                        </label>
                        <div class="input-icon">
                            <input type="password" 
                                   class="form-control form-control-with-icon" 
                                   id="password" 
                                   name="password" 
                                   placeholder="••••••••" 
                                   required>
                            <i class="fa fa-lock"></i>
                        </div>
                    </div>

                    <!-- Forgot Password Link -->
                    <div class="forgot-password">
                        <a href="forgot_password.php">
                            <i class="fa fa-question-circle"></i> Forgot Password?
                        </a>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" name="login" class="btn-login">
                        <i class="fa fa-sign-in"></i> Login Now
                    </button>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <div class="login-footer">
            <p>
                <i class="fa fa-shield-alt"></i> 
                Secure Login | Admin & SuperAdmin Access Only
                <br>
                <i class="fa fa-copyright"></i> 2024 BSU Library Management System
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
