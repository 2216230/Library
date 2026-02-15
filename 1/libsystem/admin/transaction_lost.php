<?php
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/activity_helper.php';

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'Transaction ID is required']);
    exit;
}

// Get transaction details for logging
$get_stmt = $conn->prepare("
    SELECT 
        bt.copy_id, 
        b.title AS book_title,
        CASE 
            WHEN bt.borrower_type = 'student' THEN CONCAT(s.firstname, ' ', s.lastname)
            WHEN bt.borrower_type = 'faculty' THEN CONCAT(f.firstname, ' ', f.lastname)
            ELSE 'Unknown'
        END as borrower_name
    FROM borrow_transactions bt
    LEFT JOIN books b ON bt.book_id = b.id
    LEFT JOIN students s ON bt.borrower_type = 'student' AND bt.borrower_id = s.id
    LEFT JOIN faculty f ON bt.borrower_type = 'faculty' AND bt.borrower_id = f.id
    WHERE bt.id = ? LIMIT 1
");
$get_stmt->bind_param('i', $id);
$get_stmt->execute();
$get_result = $get_stmt->get_result();
$transaction = $get_result->fetch_assoc();
$get_stmt->close();

// Update transaction status to 'lost'
$update_sql = "UPDATE borrow_transactions SET status = 'lost' WHERE id = ?";
$stmt = $conn->prepare($update_sql);
$stmt->bind_param('i', $id);

$execute_ok = $stmt->execute();

if ($execute_ok) {
    // Also update book_copies availability to 'lost' if copy_id exists
    if (!empty($transaction['copy_id'])) {
        $copy_id_int = intval($transaction['copy_id']);
        $update_copy = $conn->prepare("UPDATE book_copies SET availability = 'lost' WHERE id = ?");
        if ($update_copy) {
            $update_copy->bind_param('i', $copy_id_int);
            $update_copy->execute();
            $update_copy->close();
        }
    }
    
    // Log activity
    $action = 'MARK_LOST';
    $book_title = $transaction['book_title'] ?? 'Unknown Book';
    $borrower_name = $transaction['borrower_name'] ?? 'Unknown Borrower';
    $description = "Book marked as lost: {$book_title} | Borrower: {$borrower_name} | Status changed: Borrowed → Lost";
    $adminId = $_SESSION['admin'] ?? $_SESSION['superadmin'] ?? null;
    if ($adminId) {
        logActivity($conn, $adminId, $action, $description, 'borrow_transactions', $id);
    }

    echo json_encode(['status' => 'success', 'message' => 'Book marked as lost']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Update failed: ' . $conn->error]);
}
$stmt->close();
?>
