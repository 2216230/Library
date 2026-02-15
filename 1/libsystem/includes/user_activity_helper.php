<?php
/**
 * User Activity Helper - logs student/faculty activities
 * Include this in user-facing pages to track activities
 */

function logUserActivity($conn, $user_id, $user_type, $action, $description = '', $table_name = '', $record_id = '') {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $user_id = $conn->real_escape_string($user_id);
    $user_type = $conn->real_escape_string($user_type);
    $action = $conn->real_escape_string($action);
    $description = $conn->real_escape_string($description);
    $table_name = $conn->real_escape_string($table_name);
    $record_id = $conn->real_escape_string($record_id);
    $ip_address = $conn->real_escape_string($ip_address);
    $user_agent = $conn->real_escape_string($user_agent);
    
    $sql = "INSERT INTO user_activity_log (user_id, user_type, action, description, table_name, record_id, ip_address, user_agent) 
            VALUES ('$user_id', '$user_type', '$action', '$description', '$table_name', '$record_id', '$ip_address', '$user_agent')";
    
    return $conn->query($sql);
}

// Create table if not exists
function ensureUserActivityTable($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS `user_activity_log` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` varchar(50) NOT NULL,
        `user_type` enum('student','faculty','guest') NOT NULL,
        `action` varchar(100) NOT NULL,
        `description` text,
        `table_name` varchar(50) DEFAULT NULL,
        `record_id` varchar(50) DEFAULT NULL,
        `ip_address` varchar(45) DEFAULT NULL,
        `user_agent` text,
        `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        KEY `user_type` (`user_type`),
        KEY `action` (`action`),
        KEY `timestamp` (`timestamp`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    return $conn->query($sql);
}
?>
