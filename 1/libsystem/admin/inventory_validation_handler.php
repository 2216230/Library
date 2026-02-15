<?php
include 'includes/session.php';
include 'includes/conn.php';

if(!isset($_SESSION['admin'])){
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    $book_id = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
    $actual_count = isset($_POST['actual_count']) ? intval($_POST['actual_count']) : 0;
    $validation_date = isset($_POST['validation_date']) ? $_POST['validation_date'] : date('Y-m-d');
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
    $status = isset($_POST['status']) ? $_POST['status'] : 'available';

    // Validate inputs
    if ($book_id <= 0) {
        throw new Exception('Invalid book ID');
    }

    // Verify book exists
    $book_sql = "SELECT id, (SELECT COUNT(*) FROM book_copies WHERE book_id = b.id) as num_copies 
                 FROM books b WHERE b.id = $book_id";
    $book_result = $conn->query($book_sql);
    
    if (!$book_result || $book_result->num_rows == 0) {
        throw new Exception('Book not found');
    }

    $book = $book_result->fetch_assoc();
    $expected_count = $book['num_copies'];

    // Check if table exists, if not create it
    $table_check = $conn->query("SHOW TABLES LIKE 'inventory_validations'");
    if ($table_check->num_rows == 0) {
        $create_table = "CREATE TABLE inventory_validations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            book_id INT NOT NULL,
            expected_count INT NOT NULL,
            actual_count INT NOT NULL,
            discrepancy INT NOT NULL,
            validation_date DATE NOT NULL,
            status VARCHAR(50),
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (book_id) REFERENCES books(id),
            KEY (book_id),
            KEY (validation_date)
        )";
        
        if (!$conn->query($create_table)) {
            throw new Exception('Could not create validations table: ' . $conn->error);
        }
    }

    // Calculate discrepancy
    $discrepancy = $actual_count - $expected_count;

    // Insert validation record
    $notes_escaped = $conn->real_escape_string($notes);
    $sql = "INSERT INTO inventory_validations 
            (book_id, expected_count, actual_count, discrepancy, validation_date, status, notes)
            VALUES 
            ($book_id, $expected_count, $actual_count, $discrepancy, '$validation_date', '$status', '$notes_escaped')";
    
    if (!$conn->query($sql)) {
        throw new Exception('Could not save validation: ' . $conn->error);
    }

    // If there's a significant discrepancy or status is lost/damaged, log it
    if ($discrepancy != 0 || $status == 'lost' || $status == 'damaged') {
        $log_sql = "INSERT INTO book_validation_logs 
                   (book_id, validation_type, expected_count, actual_count, discrepancy, log_date, notes)
                   VALUES 
                   ($book_id, 'physical_validation', $expected_count, $actual_count, $discrepancy, NOW(), '$notes_escaped')
                   ON DUPLICATE KEY UPDATE log_date = NOW()";
        
        $conn->query($log_sql); // Non-critical, don't fail if this fails
    }

    echo json_encode([
        'success' => true,
        'message' => 'Validation saved successfully',
        'discrepancy' => $discrepancy
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
