<?php
include 'includes/session.php';
include 'includes/conn.php';

if(!isset($_SESSION['admin'])){
    header('Content-Type: application/json');
    echo json_encode(['success' => false]);
    exit();
}

header('Content-Type: application/json');

try {
    $book_id = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
    $remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';

    if ($book_id <= 0) {
        throw new Exception('Invalid book ID');
    }

    // Create table if it doesn't exist
    $table_check = $conn->query("SHOW TABLES LIKE 'inventory_remarks'");
    if ($table_check->num_rows == 0) {
        $create_table = "CREATE TABLE inventory_remarks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            book_id INT NOT NULL,
            remarks TEXT,
            saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (book_id) REFERENCES books(id),
            KEY (book_id)
        )";
        
        if (!$conn->query($create_table)) {
            throw new Exception('Could not create remarks table');
        }
    }

    // Check if remarks exist for this book
    $check_sql = "SELECT id FROM inventory_remarks WHERE book_id = $book_id";
    $check_result = $conn->query($check_sql);

    $remarks_safe = $conn->real_escape_string($remarks);

    if ($check_result->num_rows > 0) {
        // Update
        $sql = "UPDATE inventory_remarks SET remarks = '$remarks_safe', saved_at = NOW() WHERE book_id = $book_id";
    } else {
        // Insert
        $sql = "INSERT INTO inventory_remarks (book_id, remarks) VALUES ($book_id, '$remarks_safe')";
    }

    if (!$conn->query($sql)) {
        throw new Exception('Could not save remarks: ' . $conn->error);
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
