<?php
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/activity_helper.php';

if(isset($_POST['id']) && isset($_POST['condition'])) {

    $id = intval($_POST['id']);
    $condition = $_POST['condition']; // 'good', 'damaged', 'repair'

    // Map condition to borrow_transactions.status and book_copies.availability
    $status_map = [
        'good' => ['status'=>'returned', 'availability'=>'available'],
        'damaged' => ['status'=>'damaged', 'availability'=>'damaged'],
        'repair' => ['status'=>'repair', 'availability'=>'repair']
    ];

    if(!isset($status_map[$condition])){
        $condition = 'good'; // fallback
    }

    $status_to_set = $status_map[$condition]['status'];
    $availability_to_set = $status_map[$condition]['availability'];

    // Current datetime
    $now = date('Y-m-d');

    // 1️⃣ Get the transaction first
    $stmt = $conn->prepare("SELECT book_id, copy_id, borrower_id, due_date FROM borrow_transactions WHERE id=? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if($res && $res->num_rows > 0){
        $transaction = $res->fetch_assoc();
        
        // Check if item was overdue
        $due_date = $transaction['due_date'];
        $today = date('Y-m-d');
        $is_overdue = ($today > $due_date);
        $days_overdue = 0;
        
        if ($is_overdue) {
            $due_datetime = strtotime($due_date);
            $today_datetime = strtotime($today);
            $days_overdue = floor(($today_datetime - $due_datetime) / (60 * 60 * 24));
        }
        
        // Get borrower and book info for response
        $info_stmt = $conn->prepare("
            SELECT 
                b.title as book_title,
                CASE 
                    WHEN bt.borrower_type = 'student' THEN CONCAT(s.firstname, ' ', s.lastname)
                    WHEN bt.borrower_type = 'faculty' THEN CONCAT(f.firstname, ' ', f.lastname)
                    ELSE 'Unknown'
                END as borrower_name
            FROM borrow_transactions bt
            LEFT JOIN books b ON bt.book_id = b.id
            LEFT JOIN students s ON bt.borrower_type = 'student' AND bt.borrower_id = s.id
            LEFT JOIN faculty f ON bt.borrower_type = 'faculty' AND bt.borrower_id = f.id
            WHERE bt.id = ?
        ");
        $info_stmt->bind_param('i', $id);
        $info_stmt->execute();
        $info_result = $info_stmt->get_result();
        
        $borrower_name = 'Unknown';
        $book_title = 'Unknown';
        if ($info_result && $info_result->num_rows > 0) {
            $info = $info_result->fetch_assoc();
            $borrower_name = $info['borrower_name'];
            $book_title = $info['book_title'];
        }
        $info_stmt->close();

        // 2️⃣ Update borrow_transactions
        $update_bt = $conn->prepare("UPDATE borrow_transactions SET status=?, return_date=? WHERE id=?");
        $update_bt->bind_param('ssi', $status_to_set, $now, $id);
        $update_bt->execute();
        $update_bt->close();

        // 3️⃣ Update book_copies
        if(!empty($transaction['copy_id'])){
            $update_bc = $conn->prepare("UPDATE book_copies SET availability=? WHERE id=?");
            $update_bc->bind_param('si', $availability_to_set, $transaction['copy_id']);
            $update_bc->execute();
            $update_bc->close();
        }

        // Log RETURN activity for ALL returns (not just overdue)
        $adminId = $_SESSION['admin'] ?? $_SESSION['superadmin'] ?? null;
        if ($adminId) {
            $action = 'RETURN';
            $statusLabel = ucfirst($status_to_set);
            $description = "Book returned: {$book_title} | Borrower: {$borrower_name} | Condition: {$condition} | Status changed: Borrowed → {$statusLabel}";
            if ($is_overdue && $days_overdue > 0) {
                $description .= " | Overdue: {$days_overdue} days";
            }
            logActivity($conn, $adminId, $action, $description, 'borrow_transactions', $id);
        }

        // 4️⃣ Log settlement to penalty_settlements table
        if (isset($_POST['finePerDay']) && isset($_POST['calculatedFine'])) {
            // Ensure penalty_settlements table exists
            $check_table = $conn->query("SHOW TABLES LIKE 'penalty_settlements'");
            if ($check_table->num_rows == 0) {
                // Create table if it doesn't exist (with actual schema)
                $create_table_sql = "CREATE TABLE penalty_settlements (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    transaction_id INT NOT NULL,
                    borrower_id INT NOT NULL,
                    book_id INT NOT NULL,
                    settlement_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                    calculated_fine DECIMAL(10,2) NOT NULL,
                    adjustment_amount DECIMAL(10,2),
                    adjustment_reason VARCHAR(50),
                    adjustment_details TEXT,
                    total_payable DECIMAL(10,2) NOT NULL,
                    return_status VARCHAR(50),
                    notes TEXT,
                    created_by INT,
                    KEY idx_transaction_id (transaction_id),
                    KEY idx_borrower_id (borrower_id),
                    KEY idx_book_id (book_id),
                    FOREIGN KEY (transaction_id) REFERENCES borrow_transactions(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                $conn->query($create_table_sql);
            }
            
            $finePerDay = floatval($_POST['finePerDay']);
            $chargeableDays = intval($_POST['chargeableDays']) ?? 0;
            $calculatedFine = floatval($_POST['calculatedFine']);
            $adjustmentAmount = floatval($_POST['adjustmentAmount']) ?? 0;
            $adjustmentReason = $_POST['adjustmentReason'] ?? null;
            $adjustmentDetails = $_POST['adjustmentDetails'] ?? null;
            $totalPayable = floatval($_POST['totalPayable']) ?? $calculatedFine;
            
            // Get borrower and book info from transaction
            $info_stmt = $conn->prepare("
                SELECT 
                    bt.borrower_id, 
                    bt.book_id,
                    bt.due_date
                FROM borrow_transactions bt
                WHERE bt.id = ?
            ");
            $info_stmt->bind_param('i', $id);
            $info_stmt->execute();
            $info_result = $info_stmt->get_result();
            
            if ($info_result && $info_result->num_rows > 0) {
                $info = $info_result->fetch_assoc();
                $borrower_id = $info['borrower_id'];
                $book_id = $info['book_id'];
                $created_by_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null;
                $settlement_datetime = date('Y-m-d H:i:s');
                
                // Insert settlement record using actual schema columns
                $settlement_stmt = $conn->prepare("
                    INSERT INTO penalty_settlements (
                        transaction_id, borrower_id, book_id,
                        settlement_date, calculated_fine, adjustment_amount, adjustment_reason, adjustment_details,
                        total_payable, return_status, created_by, notes
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $notes = "Fine: ₱{$calculatedFine} | Days charged: {$chargeableDays} @ ₱{$finePerDay}/day";
                
                $settlement_stmt->bind_param(
                    'iiisddsssssi',
                    $id, $borrower_id, $book_id,
                    $settlement_datetime, $calculatedFine, $adjustmentAmount, $adjustmentReason, $adjustmentDetails,
                    $totalPayable, $status_to_set, $created_by_id, $notes
                );
                
                $settlement_stmt->execute();
                $settlement_stmt->close();
                
                // Log SETTLE activity
                $settleAdminId = $_SESSION['admin'] ?? $_SESSION['superadmin'] ?? null;
                if ($settleAdminId) {
                    $settleAction = 'SETTLE';
                    $statusLabel = ucfirst($status_to_set);
                    $settleDescription = "Penalty settled: {$book_title} | Borrower: {$borrower_name} | Status changed: Overdue → {$statusLabel}";
                    logActivity($conn, $settleAdminId, $settleAction, $settleDescription, 'penalty_settlements', $id);
                }
            }
            $info_stmt->close();
        }

        echo json_encode([
            'success'=>true, 
            'message'=>'Transaction updated successfully.', 
            'status'=>$status_to_set,
            'is_overdue' => $is_overdue,
            'was_overdue' => $is_overdue,
            'days_overdue' => $days_overdue,
            'due_date' => $due_date,
            'borrower_name' => $borrower_name,
            'book_title' => $book_title
        ]);
    } else {
        echo json_encode(['success'=>false, 'message'=>'Transaction not found.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success'=>false, 'message'=>'Invalid request.']);
}
?>
