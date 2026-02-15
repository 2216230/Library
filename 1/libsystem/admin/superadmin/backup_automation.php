<?php
/**
 * Backup Automation Handler
 * Handles automatic database backups based on system settings
 * Can be called from cron jobs or manually
 */

include '../includes/conn.php';
include '../includes/activity_helper.php';
$action = $argv[1] ?? 'status';

// Get backup settings
function getBackupSettings($conn) {
    $settings = [];
    $result = $conn->query("SELECT setting_key, setting_value FROM system_settings 
                           WHERE setting_key IN ('auto_backup', 'backup_frequency', 'backup_time', 'backup_retention_days')");
    while($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

// Create backup directory if it doesn't exist
$backup_dir = __DIR__ . '/backups';
if(!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

if($action == 'auto') {
    // Automatic backup called from cron
    $settings = getBackupSettings($conn);
    
    if($settings['auto_backup'] != '1') {
        echo "Automatic backups are disabled.\n";
        exit(0);
    }
    
    // Create backup filename with timestamp
    $db_name = getenv('DB_NAME') ?: 'libsystem5';
    $timestamp = date('Y-m-d_H-i-s');
    $backup_file = $backup_dir . '/auto_backup_' . $timestamp . '.sql';
    
    // Get database credentials from connection
    $host = getenv('DB_HOST') ?: 'localhost';
    $user = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';
    
    // Execute mysqldump
    $command = "mysqldump --single-transaction --routines --triggers -h$host -u$user" . 
               (!empty($password) ? " -p$password" : "") . " $db_name > " . escapeshellarg($backup_file);
    
    system($command, $return_var);
    
    if($return_var === 0) {
        echo "Backup created successfully: $backup_file\n";
        
        // Log the backup action
        $size = filesize($backup_file);
        $conn->query("INSERT INTO activity_log (admin_id, action, description, table_name, record_id, ip_address) 
                     VALUES (1, 'AUTO_BACKUP', 'Automatic backup created (" . formatBytes($size) . ")', 'backup', 0, 'CRON')");
        
        // Clean old backups based on retention settings
        cleanOldBackups($conn, $backup_dir, $settings['backup_retention_days'] ?? 30);
    } else {
        echo "Error creating backup.\n";
        $conn->query("INSERT INTO activity_log (admin_id, action, description, table_name, record_id, ip_address) 
                     VALUES (1, 'AUTO_BACKUP_FAILED', 'Backup failed during execution', 'backup', 0, 'CRON')");
    }
    
} elseif($action == 'manual') {
    // Manual backup triggered by superadmin
    $db_name = getenv('DB_NAME') ?: 'libsystem5';
    $timestamp = date('Y-m-d_H-i-s');
    $backup_file = $backup_dir . '/manual_backup_' . $timestamp . '.sql';
    
    $host = getenv('DB_HOST') ?: 'localhost';
    $user = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';
    
    $command = "mysqldump --single-transaction --routines --triggers -h$host -u$user" . 
               (!empty($password) ? " -p$password" : "") . " $db_name > " . escapeshellarg($backup_file);
    
    system($command, $return_var);
    
    if($return_var === 0) {
        $size = filesize($backup_file);
        // Log was already done in backup_manager.php
        exit(0);
    } else {
        exit(1);
    }
    
} elseif($action == 'cleanup') {
    // Clean old backups
    $settings = getBackupSettings($conn);
    $retention_days = intval($settings['backup_retention_days'] ?? 30);
    
    $deleted = cleanOldBackups($conn, $backup_dir, $retention_days);
    echo "Deleted $deleted old backup files (older than $retention_days days).\n";
}

/**
 * Clean old backup files based on retention period
 */
function cleanOldBackups($conn, $backup_dir, $retention_days) {
    $deleted = 0;
    $cutoff_time = time() - ($retention_days * 24 * 60 * 60);
    
    if(!is_dir($backup_dir)) return 0;
    
    $files = scandir($backup_dir);
    foreach($files as $file) {
        if($file == '.' || $file == '..') continue;
        
        $filepath = $backup_dir . '/' . $file;
        
        // Only delete .sql backup files
        if(!is_file($filepath) || pathinfo($file, PATHINFO_EXTENSION) != 'sql') continue;
        
        $file_time = filemtime($filepath);
        
        if($file_time < $cutoff_time) {
            $size = filesize($filepath);
            if(unlink($filepath)) {
                $deleted++;
                $conn->query("INSERT INTO activity_log (admin_id, action, description, table_name, record_id, ip_address) 
                             VALUES (1, 'BACKUP_DELETED', 'Old backup deleted: $file (" . formatBytes($size) . ")', 'backup', 0, 'CRON')");
            }
        }
    }
    
    return $deleted;
}

/**
 * Format bytes to human-readable format
 */
function formatBytes($bytes) {
    $size = array('B','KB','MB','GB','TB');
    if($bytes == 0) return '0 B';
    $i = intval(floor(log($bytes, 1024)));
    return round($bytes/pow(1024, $i), 2) . ' ' . $size[$i];
}
