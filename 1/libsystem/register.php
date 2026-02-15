<?php
session_start();
include 'includes/user_conn.php'; // your database connection
$message = '';

if(isset($_POST['register'])){
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']); // plain text password

    // Check if email exists
    $sql = "SELECT * FROM students WHERE email=? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0){
        $message = "Email already exists.";
    } else {
        $sql = "INSERT INTO students (firstname, lastname, email, password) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $firstname, $lastname, $email, $password);
        if($stmt->execute()){
            $message = "Registration successful! <a href='login.php'>Login now</a>.";
        } else {
            $message = "Registration failed.";
        }
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body style="display:flex; justify-content:center; align-items:center; height:100vh; background:#f5f5f5;">
<div class="card p-4" style="width:400px;">
    <h4 class="text-center">Register</h4>
    <?php if($message) echo "<div class='alert alert-info'>$message</div>"; ?>
    <form method="POST">
        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="firstname" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="lastname" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="text" name="password" class="form-control" required>
        </div>
        <button type="submit" name="register" class="btn btn-success btn-block">Register</button>
    </form>
    <div class="mt-3 text-center">
        <a href="login.php">Already have an account? Login</a>
    </div>
</div>
</body>
</html>
