<?php 
// Direct database connection for setup
$servername = "localhost";
$username = "root";
$password = "";
$database = "libsystem5";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create inventory_validations table if it doesn't exist
$create_table = "CREATE TABLE IF NOT EXISTS inventory_validations (
  id INT PRIMARY KEY AUTO_INCREMENT,
  validation_date DATE NOT NULL,
  book_id INT NOT NULL,
  expected_count INT NOT NULL DEFAULT 0,
  actual_count INT NOT NULL DEFAULT 0,
  discrepancy INT NOT NULL DEFAULT 0,
  status ENUM('available', 'lost', 'damaged', 'reserved', 'archived') DEFAULT 'available',
  notes TEXT,
  validated_by VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
  INDEX idx_validation_date (validation_date),
  INDEX idx_book_id (book_id),
  INDEX idx_status (status)
)";

if ($conn->query($create_table)) {
    echo "✓ inventory_validations table created successfully\n";
    
    // Add status column to book_copies if it doesn't exist
    $check_column = $conn->query("SHOW COLUMNS FROM book_copies LIKE 'status'");
    
    if ($check_column->num_rows == 0) {
        $alter_table = "ALTER TABLE book_copies ADD COLUMN status ENUM('available', 'borrowed', 'overdue', 'lost', 'damaged', 'reserved', 'archived') DEFAULT 'available'";
        if ($conn->query($alter_table)) {
            echo "✓ status column added to book_copies table\n";
        } else {
            echo "✗ Error adding status column: " . $conn->error . "\n";
        }
    } else {
        echo "✓ status column already exists in book_copies\n";
    }
    
    echo "\nDatabase setup completed successfully!\n";
} else {
    echo "✗ Error creating table: " . $conn->error . "\n";
}

$conn->close();
?>
