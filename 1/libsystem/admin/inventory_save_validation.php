<?php
include 'includes/session.php';
include 'includes/conn.php';

// Check if user is admin
if (!isset($_SESSION['admin'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $total_books = intval($_POST['total_books'] ?? 0);
    $books_with_issues = intval($_POST['books_with_issues'] ?? 0);
    $total_copies = intval($_POST['total_copies'] ?? 0);
    $discrepancy_copies = intval($_POST['discrepancy_copies'] ?? 0);
    $discrepancy_details = $_POST['discrepancy_details'] ?? '[]';
    $validation_date = $_POST['validation_date'] ?? date('Y-m-d H:i:s');
    
    // Insert validation record into inventory_validations table
    $sql = "INSERT INTO inventory_validations 
            (total_books, books_with_issues, total_copies, discrepancy_copies, discrepancy_details, validation_date, created_at)
            VALUES 
            ('$total_books', '$books_with_issues', '$total_copies', '$discrepancy_copies', '$discrepancy_details', '$validation_date', NOW())";
    
    if ($conn->query($sql)) {
        $validation_id = $conn->insert_id;
        
        // Update system_settings to record latest validation datetime
        $update_settings_sql = "UPDATE system_settings 
                              SET setting_value = '$validation_date'
                              WHERE setting_key = 'latest_physical_validation_datetime'";
        
        $conn->query($update_settings_sql);
        
        // If the setting doesn't exist, insert it
        $check_setting = $conn->query("SELECT id FROM system_settings WHERE setting_key = 'latest_physical_validation_datetime'");
        if ($check_setting->num_rows == 0) {
            $insert_settings_sql = "INSERT INTO system_settings (setting_key, setting_value, description, created_at)
                                   VALUES ('latest_physical_validation_datetime', '$validation_date', 'Latest Physical Book Validation DateTime', NOW())";
            $conn->query($insert_settings_sql);
        }
        
        // Log the validation activity
        $admin_id = $_SESSION['admin'];
        $log_sql = "INSERT INTO activity_log (admin_id, activity_type, description, created_at)
                   VALUES ('$admin_id', 'inventory_validation', 'Physical inventory validation completed. Books: $total_books, Issues: $books_with_issues, Total Copies: $total_copies, Discrepancies: $discrepancy_copies', NOW())";
        $conn->query($log_sql);
        
        http_response_code(200);
        echo json_encode(['success' => true, 'validation_id' => $validation_id]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save validation: ' . $conn->error]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

$conn->close();
?>
