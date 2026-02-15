<?php
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/activity_helper.php';

header('Content-Type: application/json');

if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$id = intval($_POST['id']);

try {
    // 0️⃣ Ensure archived_transactions table exists
    $create_table_sql = "
        CREATE TABLE IF NOT EXISTS `archived_transactions` (
          `archive_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `id` int(11) NOT NULL,
          `borrower_type` varchar(50) NOT NULL,
          `borrower_id` int(11) NOT NULL,
          `book_id` int(11) NOT NULL,
          `copy_id` int(11),
          `borrow_date` date NOT NULL,
          `due_date` date NOT NULL,
          `return_date` date,
          `status` varchar(50) NOT NULL,
          `academic_year_id` int(11),
          `semester` varchar(50),
          `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `archived_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          KEY `idx_archived_transaction_id` (`id`),
          KEY `idx_archived_borrower` (`borrower_type`, `borrower_id`),
          KEY `idx_archived_book` (`book_id`),
          KEY `idx_archived_status` (`status`),
          KEY `idx_archived_date` (`archived_on`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if (!$conn->query($create_table_sql)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create archive table: ' . $conn->error]);
        exit;
    }

    // 1️⃣ Get transaction data before deleting
    $stmt = $conn->prepare("
        SELECT id, borrower_type, borrower_id, book_id, copy_id, borrow_date, due_date, 
               return_date, status, academic_year_id, semester, created_on
        FROM borrow_transactions
        WHERE id = ?
        LIMIT 1
    ");
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Transaction not found.']);
        $stmt->close();
        exit;
    }

    $transaction = $result->fetch_assoc();
    $stmt->close();

    // 1️⃣.5️⃣ Delete related penalty settlement records first (to avoid foreign key constraint)
    $penalty_stmt = $conn->prepare("DELETE FROM penalty_settlements WHERE transaction_id = ?");
    if ($penalty_stmt) {
        $penalty_stmt->bind_param('i', $id);
        $penalty_stmt->execute();
        $penalty_stmt->close();
    }

    // 2️⃣ Archive the transaction using direct query (safer for NULL values)
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

    $archive_sql = "
        INSERT INTO archived_transactions 
        (id, borrower_type, borrower_id, book_id, copy_id, borrow_date, due_date, 
         return_date, status, academic_year_id, semester, created_on)
        VALUES ($id_val, '$borrower_type_val', $borrower_id_val, $book_id_val, $copy_id_val, 
                '$borrow_date_val', '$due_date_val', $return_date_val, '$status_val', 
                $academic_year_id_val, $semester_val, '$created_on_val')
    ";

    if (!$conn->query($archive_sql)) {
        echo json_encode(['success' => false, 'message' => 'Failed to archive: ' . $conn->error]);
        exit;
    }

    // 3️⃣ Delete from borrow_transactions
    $delete_stmt = $conn->prepare("DELETE FROM borrow_transactions WHERE id = ?");
    
    if (!$delete_stmt) {
        echo json_encode(['success' => false, 'message' => 'Delete prepare failed: ' . $conn->error]);
        exit;
    }
    
    $delete_stmt->bind_param('i', $id);

    if (!$delete_stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Failed to delete: ' . $delete_stmt->error]);
        $delete_stmt->close();
        exit;
    }

    $delete_stmt->close();

    // 4️⃣ Mark the book copy as available again (if copy_id exists)
    if (!empty($transaction['copy_id'])) {
        $copy_id_val = intval($transaction['copy_id']);
        $update_copy_stmt = $conn->prepare("UPDATE book_copies SET availability = 'available' WHERE id = ?");
        
        if ($update_copy_stmt) {
            $update_copy_stmt->bind_param('i', $copy_id_val);
            $update_copy_stmt->execute();
            $update_copy_stmt->close();
        }
    }

    // 5️⃣ Log activity
    $adminId = $_SESSION['admin'] ?? $_SESSION['superadmin'] ?? null;
    if ($adminId) {
        // Get book title for better logging
        $book_title = 'Unknown Book';
        $borrower_name = 'Unknown Borrower';
        $book_stmt = $conn->prepare("
            SELECT b.title, 
                CASE 
                    WHEN ? = 'student' THEN (SELECT CONCAT(firstname, ' ', lastname) FROM students WHERE id = ?)
                    WHEN ? = 'faculty' THEN (SELECT CONCAT(firstname, ' ', lastname) FROM faculty WHERE id = ?)
                    ELSE 'Unknown'
                END as borrower_name
            FROM books b WHERE b.id = ?
        ");
        $book_stmt->bind_param('sisii', $transaction['borrower_type'], $transaction['borrower_id'], $transaction['borrower_type'], $transaction['borrower_id'], $transaction['book_id']);
        $book_stmt->execute();
        $book_result = $book_stmt->get_result();
        if ($book_row = $book_result->fetch_assoc()) {
            $book_title = $book_row['title'] ?? 'Unknown Book';
            $borrower_name = $book_row['borrower_name'] ?? 'Unknown Borrower';
        }
        $book_stmt->close();
        
        $action = 'ARCHIVE_REMOVE';
        $old_status = ucfirst($transaction['status']);
        $description = "Transaction archived & removed: {$book_title} | Borrower: {$borrower_name} | Previous status: {$old_status}";
        logActivity($conn, $adminId, $action, $description, 'archived_transactions', $id);
    }

    // 6️⃣ Return success
    echo json_encode([
        'success' => true,
        'message' => 'Transaction archived and removed successfully.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
