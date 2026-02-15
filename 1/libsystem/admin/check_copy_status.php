<?php
include 'includes/conn.php';

echo "<h2>Check Book Copies Status</h2>";

// Get the last few transactions and their corresponding book_copies
$result = $conn->query("
    SELECT 
        bt.id,
        bt.status as transaction_status,
        bc.id as copy_id,
        bc.availability as copy_availability,
        b.title
    FROM borrow_transactions bt
    LEFT JOIN book_copies bc ON bt.copy_id = bc.id
    LEFT JOIN books b ON bt.book_id = b.id
    ORDER BY bt.id DESC
    LIMIT 10
");

echo "<table border='1' cellpadding='8'>";
echo "<tr><th>Transaction ID</th><th>Transaction Status</th><th>Copy ID</th><th>Copy Availability</th><th>Book Title</th></tr>";

while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['transaction_status'] . "</td>";
    echo "<td>" . ($row['copy_id'] ?: 'NULL') . "</td>";
    echo "<td style='background: " . ($row['copy_availability'] === 'lost' ? '#ffcccc' : ($row['copy_availability'] === 'available' ? '#ccffcc' : 'white')) . "'>" . ($row['copy_availability'] ?: 'NULL') . "</td>";
    echo "<td>" . ($row['title'] ?: 'Unknown') . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
