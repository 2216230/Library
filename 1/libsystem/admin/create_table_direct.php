<?php
/**
 * Direct Table Creation
 * This script creates the penalty_settlements table
 * Run this once: http://localhost/libsystem5/1/libsystem/admin/create_table_direct.php
 */

// Direct database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "libsystem5";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

$sql = "
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

if ($conn->query($sql) === TRUE) {
    // Verify table was created
    $verify = $conn->query("DESCRIBE penalty_settlements");
    if ($verify) {
        $fields = [];
        while ($row = $verify->fetch_assoc()) {
            $fields[] = $row['Field'];
        }
        echo json_encode([
            'success' => true,
            'message' => 'Penalty settlements table created successfully!',
            'fields' => $fields,
            'field_count' => count($fields)
        ]);
    }
} else {
    echo json_encode([
        'error' => $conn->error,
        'message' => 'Failed to create table'
    ]);
}

$conn->close();
?>
