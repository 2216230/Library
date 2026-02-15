<?php
// Setup Role-Based Permissions System
include 'libsystem/admin/includes/conn.php';

echo "=== Setting Up Role-Based Permissions System ===\n\n";

// 1. Add admin_role column to admin table
echo "1. Adding role system to admin table...\n";

$check = $conn->query("SHOW COLUMNS FROM `admin` LIKE 'admin_role'");
if($check->num_rows == 0){
    if($conn->query("ALTER TABLE `admin` ADD COLUMN `admin_role` enum('superadmin','admin') DEFAULT 'admin'")){
        echo "   ✓ Added admin_role column\n";
    } else {
        echo "   ✗ Error adding admin_role: " . $conn->error . "\n";
    }
} else {
    echo "   ✓ admin_role column already exists\n";
}

// Set existing admin with id=1 as superadmin
$check = $conn->query("SELECT admin_role FROM admin WHERE id = 1");
if($check->num_rows > 0){
    $admin = $check->fetch_assoc();
    if($admin['admin_role'] != 'superadmin'){
        if($conn->query("UPDATE admin SET admin_role = 'superadmin' WHERE id = 1")){
            echo "   ✓ Set admin ID 1 as superadmin\n";
        }
    } else {
        echo "   ✓ Admin ID 1 already set as superadmin\n";
    }
}

// 2. Create permissions table
echo "\n2. Creating permissions table...\n";

$check = $conn->query("SHOW TABLES LIKE 'permissions'");
if($check->num_rows == 0){
    $sql = "CREATE TABLE `permissions` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `permission_key` varchar(100) NOT NULL UNIQUE,
      `permission_name` varchar(255) NOT NULL,
      `description` text,
      `category` varchar(50),
      `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if($conn->query($sql)){
        echo "   ✓ Created permissions table\n";
        
        // Insert default permissions
        $permissions = [
            ['manage_admins', 'Manage Admin Accounts', 'Create, edit, delete admin accounts', 'Administration'],
            ['view_activity_log', 'View Activity Log', 'Access activity log and audit trails', 'Administration'],
            ['view_system_status', 'View System Status', 'Access system status monitor', 'Administration'],
            ['manage_backups', 'Manage Backups', 'Create, restore, delete database backups', 'System'],
            ['manage_database', 'Manage Database', 'Run schema fixes and database tools', 'System'],
            ['manage_books', 'Manage Books', 'Add, edit, delete books', 'Library'],
            ['manage_categories', 'Manage Categories', 'Add, edit, delete book categories', 'Library'],
            ['manage_subjects', 'Manage Subjects', 'Add, edit, delete book subjects', 'Library'],
            ['manage_ebooks', 'Manage E-Books', 'Upload and manage e-books', 'Library'],
            ['manage_transactions', 'Manage Transactions', 'Create, edit, archive transactions', 'Circulation'],
            ['manage_students', 'Manage Students', 'Add, edit, delete student accounts', 'Users'],
            ['manage_faculty', 'Manage Faculty', 'Add, edit, delete faculty accounts', 'Users'],
            ['manage_posts', 'Manage Posts', 'Create and edit announcements', 'Communications'],
            ['view_reports', 'View Reports', 'Access system reports and analytics', 'Reports'],
        ];
        
        foreach($permissions as $perm){
            $sql = "INSERT INTO permissions (permission_key, permission_name, description, category) 
                    VALUES ('".$conn->real_escape_string($perm[0])."', 
                            '".$conn->real_escape_string($perm[1])."',
                            '".$conn->real_escape_string($perm[2])."',
                            '".$conn->real_escape_string($perm[3])."')";
            $conn->query($sql);
        }
        echo "   ✓ Inserted 14 default permissions\n";
    } else {
        echo "   ✗ Error creating permissions: " . $conn->error . "\n";
    }
} else {
    echo "   ✓ permissions table already exists\n";
}

// 3. Create role_permissions mapping table
echo "\n3. Creating role-permissions mapping...\n";

$check = $conn->query("SHOW TABLES LIKE 'role_permissions'");
if($check->num_rows == 0){
    $sql = "CREATE TABLE `role_permissions` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `admin_role` varchar(50) NOT NULL,
      `permission_id` int(11) NOT NULL,
      `granted_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `role_perm` (`admin_role`, `permission_id`),
      FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if($conn->query($sql)){
        echo "   ✓ Created role_permissions table\n";
        
        // Grant superadmin ALL permissions
        $perms = $conn->query("SELECT id FROM permissions");
        while($perm = $perms->fetch_assoc()){
            $sql = "INSERT INTO role_permissions (admin_role, permission_id) 
                    VALUES ('superadmin', ".$perm['id'].")";
            $conn->query($sql);
        }
        echo "   ✓ Granted superadmin ALL permissions\n";
        
        // Grant admin basic permissions (non-admin management)
        $admin_perms = [
            'manage_books', 'manage_categories', 'manage_subjects', 'manage_ebooks',
            'manage_transactions', 'view_reports'
        ];
        
        $sql_template = "INSERT INTO role_permissions (admin_role, permission_id) 
                         SELECT 'admin', id FROM permissions WHERE permission_key = '%s'";
        
        foreach($admin_perms as $perm_key){
            $sql = sprintf($sql_template, $conn->real_escape_string($perm_key));
            $conn->query($sql);
        }
        echo "   ✓ Granted admin basic library management permissions\n";
    } else {
        echo "   ✗ Error creating role_permissions: " . $conn->error . "\n";
    }
} else {
    echo "   ✓ role_permissions table already exists\n";
}

echo "\n=== Setup Complete ===\n";
echo "✓ Role-based permission system initialized\n";
echo "✓ SuperAdmin role has all permissions\n";
echo "✓ Admin role has library management only\n";

$conn->close();
?>
