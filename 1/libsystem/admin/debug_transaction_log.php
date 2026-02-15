<?php
include 'includes/session.php';
include 'includes/conn.php';
header('Content-Type: application/json');

// Log all POST data
$log_data = array(
    'timestamp' => date('Y-m-d H:i:s'),
    'all_post' => $_POST,
    'received_fields' => array(
        'borrower_id' => $_POST['borrower_id'] ?? 'MISSING',
        'borrower_type' => $_POST['borrower_type'] ?? 'MISSING',
        'book_id' => $_POST['book_id'] ?? 'MISSING',
        'copy_id' => $_POST['copy_id'] ?? 'MISSING',
        'borrow_date' => $_POST['borrow_date'] ?? 'MISSING',
        'days' => $_POST['days'] ?? 'MISSING',
        'due_date' => $_POST['due_date'] ?? 'MISSING',
        'academic_year' => $_POST['academic_year'] ?? 'MISSING',
        'semester' => $_POST['semester'] ?? 'MISSING'
    )
);

// Write to file for debugging
file_put_contents('debug_transaction_log.txt', json_encode($log_data, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

echo json_encode(['status' => 'logged', 'data' => $log_data]);
?>
