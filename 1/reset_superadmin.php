<?php
include 'libsystem/admin/includes/conn.php';

echo "<h1>SuperAdmin Password Reset</h1>";

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_password'])) {
    $new_password = $_POST['new_password'];
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    $result = $conn->query("UPDATE admin SET password='$hashed_password' WHERE id=10");
    
    if($result) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>";
        echo "<h3 style='margin-top: 0;'>✓ Password Reset Successfully!</h3>";
        echo "<p><strong>Admin ID:</strong> 10</p>";
        echo "<p><strong>Email:</strong> superadmin@library.local</p>";
        echo "<p><strong>New Password:</strong> " . htmlspecialchars($new_password) . "</p>";
        echo "<p style='color: red; font-weight: bold;'>Make sure to save this password securely!</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px;'>";
        echo "<h3>✗ Error updating password</h3>";
        echo "</div>";
    }
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 500px;
    margin: 50px auto;
    padding: 20px;
}

form {
    background: #f5f5f5;
    padding: 20px;
    border-radius: 5px;
    border: 1px solid #ddd;
}

input[type="password"], input[type="text"] {
    width: 100%;
    padding: 10px;
    margin: 10px 0 20px 0;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 14px;
}

button {
    background: #007bff;
    color: white;
    padding: 10px 30px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
}

button:hover {
    background: #0056b3;
}
</style>

<form method="POST">
    <h3>Set New SuperAdmin Password</h3>
    <label for="new_password"><strong>New Password:</strong></label>
    <input type="password" name="new_password" id="new_password" required placeholder="Enter new password">
    
    <button type="submit">Reset Password</button>
</form>

<p style="color: #666; margin-top: 20px; font-size: 12px;">
    <strong>Info:</strong> This will update the password for superadmin@library.local (ID=10)
</p>
