<?php
/**
 * Cleanup redundant active sessions in user_logbook
 * Run this once to fix existing data
 */
include 'includes/conn.php';

echo "<h2>Cleaning up redundant active sessions...</h2>";

// Get all users with multiple active sessions
$sql = "SELECT user_id, user_type, COUNT(*) as active_count 
        FROM user_logbook 
        WHERE logout_time IS NULL 
        GROUP BY user_id, user_type 
        HAVING COUNT(*) > 1";
$result = $conn->query($sql);

$fixed = 0;
if($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Get the latest session ID for this user
        $latest_sql = "SELECT MAX(id) as latest_id FROM user_logbook 
                       WHERE user_id = '{$row['user_id']}' 
                       AND user_type = '{$row['user_type']}' 
                       AND logout_time IS NULL";
        $latest_result = $conn->query($latest_sql);
        $latest = $latest_result->fetch_assoc();
        
        // Close all other active sessions
        $update_sql = "UPDATE user_logbook 
                       SET logout_time = login_time, session_duration = 0 
                       WHERE user_id = '{$row['user_id']}' 
                       AND user_type = '{$row['user_type']}' 
                       AND logout_time IS NULL 
                       AND id != {$latest['latest_id']}";
        $conn->query($update_sql);
        $fixed += $conn->affected_rows;
        
        echo "Fixed {$row['user_type']}: {$row['user_id']} - closed " . ($row['active_count'] - 1) . " old sessions<br>";
    }
}

echo "<br><strong>Total sessions closed: $fixed</strong>";
echo "<br><br><a href='admin/activity_log.php?tab=users'>Go back to Activity Log</a>";
?>
