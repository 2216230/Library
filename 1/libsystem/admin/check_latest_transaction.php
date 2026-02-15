<?php
include 'includes/conn.php';

echo "<h2>Check Latest Inserted Transaction</h2>";

// Get the most recent transaction
$result = $conn->query("SELECT id, borrower_id, borrow_date, due_date, status, created_at FROM borrow_transactions ORDER BY id DESC LIMIT 1");

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "<p><strong>Latest Transaction ID:</strong> " . $row['id'] . "</p>";
    echo "<p><strong>Borrower ID:</strong> " . $row['borrower_id'] . "</p>";
    echo "<p><strong>Borrow Date:</strong> " . ($row['borrow_date'] ?: 'NULL/EMPTY') . "</p>";
    echo "<p><strong>Due Date:</strong> " . ($row['due_date'] ?: 'NULL/EMPTY') . "</p>";
    echo "<p><strong>Status:</strong> " . $row['status'] . "</p>";
    echo "<p><strong>Created At:</strong> " . ($row['created_at'] ?: 'N/A') . "</p>";
    
    echo "<hr>";
    
    // Show all columns and their values
    echo "<h3>All columns for this transaction:</h3>";
    echo "<pre>";
    print_r($row);
    echo "</pre>";
} else {
    echo "No transactions found!";
}

// Show last 5 for context
echo "<h2>Last 5 Transactions</h2>";
$result = $conn->query("SELECT id, borrower_id, borrow_date, due_date FROM borrow_transactions ORDER BY id DESC LIMIT 5");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Borrower</th><th>Borrow Date</th><th>Due Date</th></tr>";
while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['borrower_id'] . "</td>";
    echo "<td>" . ($row['borrow_date'] ?: 'NULL') . "</td>";
    echo "<td>" . ($row['due_date'] ?: 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
