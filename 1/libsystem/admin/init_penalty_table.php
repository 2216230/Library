<?php
/**
 * FINAL SETUP: Penalty Settlements Table Creation
 * Directly creates the table without dependencies
 * Visit this file's URL to initialize
 */

// Raw mysqli connection to ensure it works
$mysqli = new mysqli("localhost", "root", "", "libsystem5");

// Check connection
if ($mysqli->connect_errno) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Set charset
$mysqli->set_charset("utf8mb4");

// Check if table already exists
$result = $mysqli->query("SHOW TABLES LIKE 'penalty_settlements'");

if ($result && $result->num_rows > 0) {
    // Table exists
    echo "✓ penalty_settlements table already exists<br>";
    
    // Show its structure
    $columns = $mysqli->query("DESCRIBE penalty_settlements");
    echo "<h3>Current Structure:</h3>";
    echo "<ul>";
    while ($row = $columns->fetch_assoc()) {
        echo "<li><strong>" . $row['Field'] . "</strong> (" . $row['Type'] . ")</li>";
    }
    echo "</ul>";
    $mysqli->close();
    exit;
}

// Table does NOT exist - Create it
$sql = <<<SQL
CREATE TABLE penalty_settlements (
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
SQL;

if ($mysqli->query($sql) === TRUE) {
    echo "✓ penalty_settlements table CREATED successfully!<br>";
    
    // Verify it was created
    $verify = $mysqli->query("SHOW TABLES LIKE 'penalty_settlements'");
    if ($verify && $verify->num_rows > 0) {
        echo "✓ VERIFIED: Table exists in database<br>";
        
        // Show structure
        $columns = $mysqli->query("DESCRIBE penalty_settlements");
        echo "<h3>Created with " . $columns->num_rows . " columns:</h3>";
        echo "<ul>";
        while ($row = $columns->fetch_assoc()) {
            echo "<li>" . $row['Field'] . " (" . $row['Type'] . ")</li>";
        }
        echo "</ul>";
    }
} else {
    echo "✗ FAILED to create table<br>";
    echo "Error: " . $mysqli->error . "<br>";
}

$mysqli->close();
?>
