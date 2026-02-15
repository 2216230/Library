<?php
include 'includes/session.php';
include 'includes/conn.php';

echo "<h2>Fixing existing transaction dates...</h2>";

// Update all records with 0000-00-00 borrow_date to use due_date - days
// Since we don't have the original borrow date, we'll use a reasonable default
// For now, set borrow_date to 1 day before due_date
$sql = "UPDATE borrow_transactions 
        SET borrow_date = DATE_SUB(due_date, INTERVAL 7 DAY)
        WHERE borrow_date = '0000-00-00' OR borrow_date IS NULL OR borrow_date = '1970-01-01'";

if($conn->query($sql)) {
    echo "✓ Updated existing records<br>";
    $affected = $conn->affected_rows;
    echo "✓ Affected rows: " . $affected . "<br>";
} else {
    echo "✗ Error: " . $conn->error . "<br>";
}

echo "<h3>✓ Fix complete!</h3>";
echo "<p><a href='transactions.php'>Back to Transactions</a></p>";
?>
