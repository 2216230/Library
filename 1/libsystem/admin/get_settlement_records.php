<?php
/**
 * Settlement Records Retrieval
 * Fetches all settlement records from penalty_settlements table
 */

header('Content-Type: application/json');

include 'includes/session.php';
include 'includes/conn.php';

// Session is checked in includes/session.php

try {
    // First, ensure penalty_settlements table exists with all required columns
    $check_table = $conn->query("SHOW TABLES LIKE 'penalty_settlements'");
    
    if (!$check_table || $check_table->num_rows == 0) {
        // Table doesn't exist - create it
        $create_sql = "CREATE TABLE penalty_settlements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            transaction_id INT NOT NULL,
            borrower_id INT,
            book_id INT,
            borrower_name VARCHAR(255),
            book_title VARCHAR(255),
            days_overdue INT NOT NULL DEFAULT 0,
            due_date DATE,
            return_date DATE,
            fine_per_day DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            chargeable_days INT NOT NULL DEFAULT 0,
            calculated_fine DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            adjustment_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            adjustment_reason VARCHAR(100),
            adjustment_details TEXT,
            total_payable DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            return_status VARCHAR(50),
            settled_by INT,
            settled_by_name VARCHAR(255),
            settled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status VARCHAR(50) DEFAULT 'settled',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_transaction_id (transaction_id),
            KEY idx_borrower_id (borrower_id),
            KEY idx_book_id (book_id),
            KEY idx_settled_at (settled_at),
            KEY idx_status (status),
            FOREIGN KEY (transaction_id) REFERENCES borrow_transactions(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if ($conn->query($create_sql) === FALSE) {
            error_log('Failed to create penalty_settlements: ' . $conn->error);
            throw new Exception('Database table setup failed: ' . $conn->error);
        }
    }
    
    // Query to get all settlement records with actual schema
    // Join with borrow_transactions and related tables to build complete data
    
    // Build WHERE clause based on filters
    $whereConditions = array();
    
    if (!empty($_GET['from_date'])) {
        $fromDate = $conn->real_escape_string($_GET['from_date']);
        $whereConditions[] = "DATE(ps.settlement_date) >= '$fromDate'";
    }
    
    if (!empty($_GET['to_date'])) {
        $toDate = $conn->real_escape_string($_GET['to_date']);
        $whereConditions[] = "DATE(ps.settlement_date) <= '$toDate'";
    }
    
    $whereClause = '';
    if (!empty($whereConditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    }
    
    $query = "
        SELECT 
            ps.id,
            ps.transaction_id,
            ps.borrower_id,
            ps.book_id,
            CONCAT(COALESCE(st.firstname, fc.firstname, 'Unknown'), ' ', COALESCE(st.lastname, fc.lastname, 'Borrower')) AS borrower_name,
            COALESCE(b.title, 'Unknown Book') AS book_title,
            DATEDIFF(CURDATE(), bt.due_date) AS days_overdue,
            ps.calculated_fine,
            ps.adjustment_amount,
            ps.adjustment_reason,
            ps.adjustment_details,
            ps.total_payable,
            ps.return_status,
            admin.firstname AS settled_by_name,
            'settled' AS status,
            ps.settlement_date,
            ps.created_by,
            ps.notes
        FROM penalty_settlements ps
        LEFT JOIN borrow_transactions bt ON ps.transaction_id = bt.id
        LEFT JOIN students st ON bt.borrower_type = 'student' AND bt.borrower_id = st.id
        LEFT JOIN faculty fc ON bt.borrower_type = 'faculty' AND bt.borrower_id = fc.id
        LEFT JOIN books b ON bt.book_id = b.id
        LEFT JOIN admin ON ps.created_by = admin.id
        $whereClause
        ORDER BY ps.settlement_date DESC
        LIMIT 500
    ";

    $result = $conn->query($query);

    if ($result === false) {
        throw new Exception('Database query error: ' . $conn->error);
    }

    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }

    echo json_encode([
        'success' => true,
        'data' => $records,
        'count' => count($records)
    ]);

} catch (Exception $e) {
    error_log('Settlement Records Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving settlement records: ' . $e->getMessage()
    ]);
}
?>
