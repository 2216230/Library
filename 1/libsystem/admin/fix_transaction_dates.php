<?php
include 'includes/session.php';
include 'includes/conn.php';

echo "<h2>Fixing borrow_transactions table...</h2>";

// Alter borrow_date column to DATE (nullable)
$sql1 = "ALTER TABLE borrow_transactions MODIFY COLUMN borrow_date DATE NULL";
if($conn->query($sql1)) {
    echo "✓ borrow_date column changed to DATE<br>";
} else {
    echo "✗ Error changing borrow_date: " . $conn->error . "<br>";
}

// Alter due_date column to DATE (nullable)
$sql2 = "ALTER TABLE borrow_transactions MODIFY COLUMN due_date DATE NULL";
if($conn->query($sql2)) {
    echo "✓ due_date column changed to DATE<br>";
} else {
    echo "✗ Error changing due_date: " . $conn->error . "<br>";
}

echo "<h3>✓ Database migration complete!</h3>";
echo "<p><a href='transactions.php'>Back to Transactions</a></p>";
?>
