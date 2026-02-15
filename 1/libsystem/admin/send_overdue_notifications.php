<?php
// Start output buffering for clean JSON response
ob_start();

// Set timezone
date_default_timezone_set('Asia/Manila');

// Initialize session
session_start();

// Set JSON header
header('Content-Type: application/json');

// Verify admin is logged in
if (!isset($_SESSION['admin'])) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // Include database connection
    include 'includes/conn.php';
    
    // Include overdue notification functions
    include 'includes/overdue_notifier.php';
    
    // Call the function to send all overdue notifications
    $result = sendAllOverdueNotifications($conn);
    
    // Count failed notifications
    $failed = count($result['errors']);
    
    // Clean output buffer and return JSON
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'sent' => $result['sent'],
        'failed' => $failed,
        'message' => $result['sent'] . ' notification(s) sent',
        'errors' => $result['errors']
    ]);
    
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
exit;
?>
