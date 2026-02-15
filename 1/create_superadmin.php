<?php
// Create a superAdmin account
include 'libsystem/admin/includes/conn.php';

$email = 'superadmin@library.local';
$firstname = 'Super';
$lastname = 'Admin';
$password = password_hash('SuperAdmin@2025', PASSWORD_DEFAULT);

// Check if superadmin already exists
$check = $conn->query("SELECT id FROM admin WHERE gmail = '$email'");

if($check->num_rows > 0){
  echo "SuperAdmin account already exists!";
} else {
  $sql = "INSERT INTO admin (gmail, password, firstname, lastname, photo, created_on) 
          VALUES ('$email', '$password', '$firstname', '$lastname', 'profile.jpg', NOW())";
  
  if($conn->query($sql)){
    echo "✓ SuperAdmin account created successfully!\n";
    echo "Email: $email\n";
    echo "Password: SuperAdmin@2025\n";
    echo "Name: Super Admin\n";
  } else {
    echo "Error: " . $conn->error;
  }
}

$conn->close();
?>
