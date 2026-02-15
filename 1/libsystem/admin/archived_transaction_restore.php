<?php
include 'includes/session.php';
include 'includes/conn.php';

header('Content-Type: application/json');

if (!isset($_POST['archive_id']) || !isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$archive_id = intval($_POST['archive_id']);
$transaction_id = intval($_POST['id']);

try {
    // 1. Get archived transaction data
    $stmt = $conn->prepare("
        SELECT id, borrower_type, borrower_id, book_id, copy_id, borrow_date, due_date, 
               return_date, status, academic_year_id, semester, created_on
        FROM archived_transactions
        WHERE archive_id = ? AND id = ?
        LIMIT 1
    ");
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param('ii', $archive_id, $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Archived transaction not found']);
        $stmt->close();
        exit;
    }
    
    $transaction = $result->fetch_assoc();
    $stmt->close();
    
    // 2. Insert back into borrow_transactions using direct query for NULL handling
    $id_val = intval($transaction['id']);
    $borrower_type_val = $conn->real_escape_string($transaction['borrower_type']);
    $borrower_id_val = intval($transaction['borrower_id']);
    $book_id_val = intval($transaction['book_id']);
    $copy_id_val = !empty($transaction['copy_id']) ? intval($transaction['copy_id']) : 'NULL';
    $borrow_date_val = $conn->real_escape_string($transaction['borrow_date']);
    $due_date_val = $conn->real_escape_string($transaction['due_date']);
    $return_date_val = !empty($transaction['return_date']) ? "'" . $conn->real_escape_string($transaction['return_date']) . "'" : 'NULL';
    $status_val = $conn->real_escape_string($transaction['status']);
    $academic_year_id_val = !empty($transaction['academic_year_id']) ? intval($transaction['academic_year_id']) : 'NULL';
    $semester_val = !empty($transaction['semester']) ? "'" . $conn->real_escape_string($transaction['semester']) . "'" : 'NULL';
    $created_on_val = $conn->real_escape_string($transaction['created_on']);
    
    // Check if transaction already exists in borrow_transactions
    $check_stmt = $conn->prepare("SELECT id FROM borrow_transactions WHERE id = ? LIMIT 1");
    $check_stmt->bind_param('i', $id_val);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Transaction already exists in active transactions']);
        $check_stmt->close();
        exit;
    }
    $check_stmt->close();
    
    // Insert into borrow_transactions
    $restore_sql = "
        INSERT INTO borrow_transactions 
        (id, borrower_type, borrower_id, book_id, copy_id, borrow_date, due_date, 
         return_date, status, academic_year_id, semester, created_on)
        VALUES ($id_val, '$borrower_type_val', $borrower_id_val, $book_id_val, $copy_id_val, 
                '$borrow_date_val', '$due_date_val', $return_date_val, '$status_val', 
                $academic_year_id_val, $semester_val, '$created_on_val')
    ";
    
    if (!$conn->query($restore_sql)) {
        echo json_encode(['success' => false, 'message' => 'Failed to restore: ' . $conn->error]);
        exit;
    }
    
    // 3. Delete from archived_transactions
    $delete_stmt = $conn->prepare("DELETE FROM archived_transactions WHERE archive_id = ?");
    
    if (!$delete_stmt) {
        echo json_encode(['success' => false, 'message' => 'Delete prepare failed: ' . $conn->error]);
        exit;
    }
    
    $delete_stmt->bind_param('i', $archive_id);
    
    if (!$delete_stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Failed to delete from archive: ' . $delete_stmt->error]);
        $delete_stmt->close();
        exit;
    }
    
    $delete_stmt->close();
    
    // 4. Return success
    echo json_encode([
        'success' => true,
        'message' => 'Transaction restored to active transactions successfully.'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
