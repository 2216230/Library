#!/usr/bin/env php
<?php
/**
 * Reset/Create Admin Account
 * 
 * Usage in Render shell:
 * php reset_admin.php
 * 
 * Or specify custom credentials:
 * php reset_admin.php admin@bsu.edu.ph admin123 System Administrator
 */

// Check if running from CLI
if (php_sapi_name() !== 'cli') {
    die("This script must be run from command line.\n");
}

// Get database credentials from environment
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASSWORD') ?: '';
$db_name = getenv('DB_NAME') ?: 'libsystem4';

// Default admin credentials
$admin_email = $argc > 1 ? $argv[1] : 'admin@bsu.edu.ph';
$admin_password = $argc > 2 ? $argv[2] : 'admin123';
$admin_firstname = $argc > 3 ? $argv[3] : 'System';
$admin_lastname = $argc > 4 ? $argv[4] : 'Administrator';

echo "===========================================\n";
echo "Admin Account Reset Script\n";
echo "===========================================\n";
echo "Database: {$db_name}\n";
echo "Host: {$db_host}\n";
echo "-------------------------------------------\n";
echo "Email: {$admin_email}\n";
echo "Password: {$admin_password}\n";
echo "Name: {$admin_firstname} {$admin_lastname}\n";
echo "===========================================\n\n";

// Connect to database
echo "Connecting to database...\n";
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}

echo "Connected successfully!\n\n";

// Hash the password
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

// Check if admin table exists
$table_check = $conn->query("SHOW TABLES LIKE 'admin'");
if ($table_check->num_rows == 0) {
    echo "Creating admin table...\n";
    $create_table = "CREATE TABLE IF NOT EXISTS `admin` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `gmail` varchar(100) NOT NULL,
      `password` varchar(255) NOT NULL,
      `firstname` varchar(50) NOT NULL,
      `lastname` varchar(50) NOT NULL,
      `photo` varchar(200) DEFAULT NULL,
      `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `gmail` (`gmail`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($create_table)) {
        die("Error creating admin table: " . $conn->error . "\n");
    }
    echo "Admin table created!\n\n";
}

// Check if admin exists
$check_stmt = $conn->prepare("SELECT id FROM admin WHERE gmail = ?");
$check_stmt->bind_param("s", $admin_email);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    // Update existing admin
    echo "Admin account exists. Updating password...\n";
    $stmt = $conn->prepare("UPDATE admin SET password = ?, firstname = ?, lastname = ? WHERE gmail = ?");
    $stmt->bind_param("ssss", $hashed_password, $admin_firstname, $admin_lastname, $admin_email);
    
    if ($stmt->execute()) {
        echo "✓ Admin account updated successfully!\n";
    } else {
        die("Error updating admin: " . $stmt->error . "\n");
    }
    $stmt->close();
} else {
    // Insert new admin with default photo and timestamp
    echo "Creating new admin account...\n";
    $default_photo = 'profile.jpg'; // Default photo
    $created_on = date('Y-m-d H:i:s'); // Current timestamp
    $stmt = $conn->prepare("INSERT INTO admin (gmail, password, firstname, lastname, photo, created_on) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $admin_email, $hashed_password, $admin_firstname, $admin_lastname, $default_photo, $created_on);
    
    if ($stmt->execute()) {
        echo "✓ Admin account created successfully!\n";
    } else {
        die("Error creating admin: " . $stmt->error . "\n");
    }
    $stmt->close();
}

$check_stmt->close();
$conn->close();

echo "\n===========================================\n";
echo "Admin Account Ready!\n";
echo "===========================================\n";
echo "Login URL: https://bsu-bokod-lms.onrender.com/libsystem/admin/\n";
echo "Email: {$admin_email}\n";
echo "Password: {$admin_password}\n";
echo "\nIMPORTANT: Change your password after first login!\n";
echo "===========================================\n";
