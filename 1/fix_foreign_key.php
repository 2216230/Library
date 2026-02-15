<?php
$conn = new mysqli('localhost', 'root', '', 'libsystem5');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "Starting foreign key fix...\n";

// Drop the incorrect foreign key
$drop_result = $conn->query('ALTER TABLE book_subject_map DROP FOREIGN KEY book_subject_map_ibfk_1');
if ($drop_result) {
    echo "✓ Old constraint dropped successfully.\n";
} else {
    echo "✗ Error dropping constraint: " . $conn->error . "\n";
    exit(1);
}

// Add the correct foreign key
$add_result = $conn->query('ALTER TABLE book_subject_map ADD CONSTRAINT book_subject_map_ibfk_1 FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE');
if ($add_result) {
    echo "✓ New constraint added successfully.\n";
    echo "\n✓✓✓ Foreign key constraint fixed! book_subject_map now correctly references books table.\n";
} else {
    echo "✗ Error adding constraint: " . $conn->error . "\n";
    exit(1);
}

$conn->close();
?>
