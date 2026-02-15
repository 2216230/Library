<?php
/**
 * CRITICAL: Create penalty_settlements table immediately
 * This script will create the table or display errors
 */

header('Content-Type: application/json');

// Direct connection
$conn = new mysqli('localhost', 'root', '', 'libsystem5');

if ($conn->connect_error) {
    echo json_encode(['error' => 'DB Connection: ' . $conn->connect_error]);
    exit;
}

// First, check if table exists
$check = $conn->query("SHOW TABLES LIKE 'penalty_settlements'");
if ($check && $check->num_rows > 0) {
    // Table exists, verify structure
    $columns = $conn->query("SHOW COLUMNS FROM penalty_settlements");
    $cols = [];
    while ($col = $columns->fetch_assoc()) {
        $cols[] = $col['Field'];
    }
    
    echo json_encode([
        'exists' => true,
        'columns' => $cols,
        'count' => count($cols)
    ]);
    exit;
}

// Table doesn't exist - CREATE IT
$sql = "CREATE TABLE penalty_settlements (
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

if ($conn->query($sql) === TRUE) {
    echo json_encode([
        'created' => true,
        'message' => 'penalty_settlements table created successfully'
    ]);
} else {
    echo json_encode([
        'created' => false,
        'error' => $conn->error
    ]);
}

$conn->close();
?>
