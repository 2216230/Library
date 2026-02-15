<?php
session_start();
include 'includes/conn.php';

// Record logout time if user was logged in
if(isset($_SESSION['log_entry_id'])) {
    $logout_time = date('Y-m-d H:i:s');
    $log_entry_id = $_SESSION['log_entry_id'];
    
    // Calculate session duration
    $sql = "UPDATE user_logbook 
            SET logout_time = '$logout_time',
                session_duration = TIMESTAMPDIFF(SECOND, login_time, '$logout_time')
            WHERE id = '$log_entry_id'";
    $conn->query($sql);
}

session_destroy();
header('location: index.php');
?>