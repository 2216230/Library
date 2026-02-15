<?php
include 'includes/conn.php';

echo "<h2>Adding 'lost' value to book_copies.availability</h2>";

// First, get the current column definition
$result = $conn->query("SHOW CREATE TABLE book_copies");
$row = $result->fetch_assoc();

echo "<p><strong>Current table definition:</strong></p>";
echo "<pre>" . htmlspecialchars($row['Create Table']) . "</pre>";

// Try to modify the column to allow 'lost'
$alter_sql = "ALTER TABLE book_copies MODIFY COLUMN availability ENUM('available', 'borrowed', 'damaged', 'repair', 'lost', 'overdue') DEFAULT 'available'";

echo "<p>Executing: " . htmlspecialchars($alter_sql) . "</p>";

if ($conn->query($alter_sql)) {
    echo "<p style='color: green;'><strong>✓ Column modified successfully!</strong></p>";
    
    // Verify
    $verify = $conn->query("SHOW CREATE TABLE book_copies");
    $verify_row = $verify->fetch_assoc();
    echo "<p><strong>Updated table definition:</strong></p>";
    echo "<pre>" . htmlspecialchars($verify_row['Create Table']) . "</pre>";
} else {
    echo "<p style='color: red;'><strong>✗ Error: " . $conn->error . "</strong></p>";
}
?>
