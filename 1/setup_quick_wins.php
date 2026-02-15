<?php
// Database setup script for Quick Wins features
include 'libsystem/admin/includes/conn.php';

echo "=== Database Setup for Quick Wins Features ===\n\n";

// 1. Add columns to admin table
echo "1. Adding columns to admin table...\n";

// Add last_login column
$check = $conn->query("SHOW COLUMNS FROM `admin` LIKE 'last_login'");
if($check->num_rows == 0){
    if($conn->query("ALTER TABLE `admin` ADD COLUMN `last_login` datetime DEFAULT NULL")){
        echo "   ✓ Added last_login column\n";
    } else {
        echo "   ✗ Error adding last_login: " . $conn->error . "\n";
    }
} else {
    echo "   ✓ last_login column already exists\n";
}

// Add status column (Active/Inactive)
$check = $conn->query("SHOW COLUMNS FROM `admin` LIKE 'status'");
if($check->num_rows == 0){
    if($conn->query("ALTER TABLE `admin` ADD COLUMN `status` enum('active','inactive') DEFAULT 'active'")){
        echo "   ✓ Added status column\n";
    } else {
        echo "   ✗ Error adding status: " . $conn->error . "\n";
    }
} else {
    echo "   ✓ status column already exists\n";
}

// Add created_by column (who created this admin account)
$check = $conn->query("SHOW COLUMNS FROM `admin` LIKE 'created_by'");
if($check->num_rows == 0){
    if($conn->query("ALTER TABLE `admin` ADD COLUMN `created_by` int DEFAULT NULL")){
        echo "   ✓ Added created_by column\n";
    } else {
        echo "   ✗ Error adding created_by: " . $conn->error . "\n";
    }
} else {
    echo "   ✓ created_by column already exists\n";
}

// 2. Create activity_log table
echo "\n2. Creating activity_log table...\n";

$check = $conn->query("SHOW TABLES LIKE 'activity_log'");
if($check->num_rows == 0){
    $sql = "CREATE TABLE `activity_log` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `admin_id` int(11) NOT NULL,
      `action` varchar(255) NOT NULL,
      `description` text,
      `table_name` varchar(50),
      `record_id` int(11),
      `old_value` text,
      `new_value` text,
      `ip_address` varchar(45),
      `user_agent` text,
      `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `admin_id` (`admin_id`),
      KEY `timestamp` (`timestamp`),
      KEY `action` (`action`),
      FOREIGN KEY (`admin_id`) REFERENCES `admin`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if($conn->query($sql)){
        echo "   ✓ Created activity_log table\n";
    } else {
        echo "   ✗ Error creating activity_log: " . $conn->error . "\n";
    }
} else {
    echo "   ✓ activity_log table already exists\n";
}

// 3. Create system_stats table
echo "\n3. Creating system_stats table...\n";

$check = $conn->query("SHOW TABLES LIKE 'system_stats'");
if($check->num_rows == 0){
    $sql = "CREATE TABLE `system_stats` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `stat_date` date NOT NULL,
      `total_users` int(11) DEFAULT 0,
      `total_books` int(11) DEFAULT 0,
      `total_ebooks` int(11) DEFAULT 0,
      `active_transactions` int(11) DEFAULT 0,
      `overdue_count` int(11) DEFAULT 0,
      `db_size_mb` decimal(10,2) DEFAULT 0,
      `last_backup` datetime,
      `backup_count` int(11) DEFAULT 0,
      `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `stat_date` (`stat_date`),
      KEY `timestamp` (`timestamp`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if($conn->query($sql)){
        echo "   ✓ Created system_stats table\n";
    } else {
        echo "   ✗ Error creating system_stats: " . $conn->error . "\n";
    }
} else {
    echo "   ✓ system_stats table already exists\n";
}

echo "\n=== Database Setup Complete ===\n";
echo "\nYou can now delete this file after running it.\n";

$conn->close();
?>
