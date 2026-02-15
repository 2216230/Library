<?php
/**
 * Create Penalty Settlements Table
 * Executed directly to ensure table creation
 */

include 'includes/conn.php';

// Create penalty_settlements table
$create_table_sql = "
    CREATE TABLE IF NOT EXISTS penalty_settlements (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";

if ($conn->query($create_table_sql) === TRUE) {
    echo json_encode([
        'success' => true,
        'message' => 'penalty_settlements table created successfully!',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error creating penalty_settlements table: ' . $conn->error,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

$conn->close();
?>
