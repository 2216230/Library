<?php
/**
 * Activity Logger Helper
 * Logs all admin actions to activity_log table
 */

function logActivity($conn, $admin_id, $action, $description = '', $table_name = '', $record_id = '', $old_value = '', $new_value = '') {
    // If admin_id is missing or invalid, skip logging to avoid FK violations
    if (empty($admin_id)) {
        return false;
    }

    $admin_id = intval($admin_id);
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
    
    $sql = "INSERT INTO activity_log (admin_id, action, description, table_name, record_id, old_value, new_value, ip_address, user_agent) 
            VALUES (
                {$admin_id},
                '".$conn->real_escape_string($action)."',
                '".$conn->real_escape_string($description)."',
                '".$conn->real_escape_string($table_name)."',
                ".($record_id ? intval($record_id) : 'NULL').",
                '".$conn->real_escape_string(substr($old_value, 0, 255))."',
                '".$conn->real_escape_string(substr($new_value, 0, 255))."',
                '".$conn->real_escape_string($ip)."',
                '".$conn->real_escape_string(substr($user_agent, 0, 255))."'
            )";
    
    return $conn->query($sql);
}

/**
 * Update Last Login time for admin
 */
function updateLastLogin($conn, $admin_id) {
    $sql = "UPDATE admin SET last_login = NOW() WHERE id = ".intval($admin_id);
    return $conn->query($sql);
}

/**
 * Check if admin is active
 */
function isAdminActive($conn, $admin_id) {
    $query = $conn->query("SELECT status FROM admin WHERE id = ".intval($admin_id));
    if($query->num_rows > 0){
        $admin = $query->fetch_assoc();
        $status = isset($admin['status']) ? $admin['status'] : 'active';
        return $status == 'active';
    }
    return false;
}

/**
 * Check if admin has a specific permission
 */
function hasPermission($conn, $admin_id, $permission_key) {
    // SuperAdmin always has all permissions
    $admin_query = $conn->query("SELECT admin_role FROM admin WHERE id = ".intval($admin_id));
    if($admin_query->num_rows > 0){
        $admin = $admin_query->fetch_assoc();
        if(isset($admin['admin_role']) && $admin['admin_role'] == 'superadmin'){
            return true;
        }
        
        // Check if admin role has this permission
        $perm_query = $conn->query("SELECT COUNT(*) as count FROM role_permissions rp
                                    JOIN permissions p ON rp.permission_id = p.id
                                    WHERE rp.admin_role = 'admin' 
                                    AND p.permission_key = '".$conn->real_escape_string($permission_key)."'");
        $result = $perm_query->fetch_assoc();
        return $result['count'] > 0;
    }
    return false;
}

/**
 * Get all permissions for an admin role
 */
function getAdminPermissions($conn, $admin_role) {
    $permissions = [];
    $query = $conn->query("SELECT p.permission_key FROM role_permissions rp
                           JOIN permissions p ON rp.permission_id = p.id
                           WHERE rp.admin_role = '".$conn->real_escape_string($admin_role)."'");
    while($row = $query->fetch_assoc()){
        $permissions[] = $row['permission_key'];
    }
    return $permissions;
}

?>
