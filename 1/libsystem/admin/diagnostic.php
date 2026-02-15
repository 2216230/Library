<?php
include 'includes/conn.php';

echo "<h2>Database Diagnostic</h2>";

// Check column types
echo "<h3>Column Types</h3>";
$result = $conn->query('DESCRIBE borrow_transactions');
while($row = $result->fetch_assoc()) {
    if(in_array($row['Field'], ['borrow_date', 'due_date', 'id', 'borrower_id'])) {
        echo $row['Field'] . ': ' . $row['Type'] . '<br>';
    }
}

// Check recent transactions
echo "<h3>Recent Transactions (Last 5)</h3>";
$result = $conn->query("SELECT id, borrower_id, borrow_date, due_date, status FROM borrow_transactions ORDER BY id DESC LIMIT 5");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Borrower ID</th><th>Borrow Date</th><th>Due Date</th><th>Status</th></tr>";
while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['borrower_id'] . "</td>";
    echo "<td>" . $row['borrow_date'] . "</td>";
    echo "<td>" . $row['due_date'] . "</td>";
    echo "<td>" . $row['status'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Check total count
$count_result = $conn->query("SELECT COUNT(*) as total FROM borrow_transactions");
$count = $count_result->fetch_assoc();
echo "<p><strong>Total Transactions:</strong> " . $count['total'] . "</p>";

// Test pagination query
echo "<h3>Pagination Test (Page 1, 10 per page)</h3>";
$sql = "SELECT COUNT(*) as total FROM borrow_transactions";
$count_result = $conn->query($sql);
$count_row = $count_result->fetch_assoc();
$total = $count_row['total'];
$per_page = 10;
$total_pages = ceil($total / $per_page);

echo "<p>Total Records: " . $total . "</p>";
echo "<p>Total Pages: " . $total_pages . "</p>";
echo "<p>Per Page: " . $per_page . "</p>";

// Test load query
echo "<h3>Load Query Test</h3>";
$sql_load = "
SELECT 
    bt.id,
    bt.borrower_type,
    bt.borrower_id,
    b.title AS book_title,
    b.call_no,
    bc.copy_number AS copy_no,
    bt.borrow_date,
    bt.due_date,
    bt.status,
    CONCAT(st.firstname, ' ', st.lastname) AS student_name,
    CONCAT(fc.firstname, ' ', fc.lastname) AS faculty_name
FROM borrow_transactions bt
LEFT JOIN books b ON bt.book_id = b.id
LEFT JOIN book_copies bc ON bt.copy_id = bc.id
LEFT JOIN students st ON bt.borrower_type = 'student' AND bt.borrower_id = st.id
LEFT JOIN faculty fc ON bt.borrower_type = 'faculty' AND bt.borrower_id = fc.id
ORDER BY bt.borrow_date DESC
LIMIT 0, 10
";

$result = $conn->query($sql_load);
if (!$result) {
    echo "Query Error: " . $conn->error . "<br>";
} else {
    echo "Query OK - Found " . $result->num_rows . " rows<br>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Borrower</th><th>Book</th><th>Borrow Date</th><th>Due Date</th><th>Status</th></tr>";
    while($row = $result->fetch_assoc()) {
        $borrower = ($row['borrower_type'] === 'student') ? $row['student_name'] : $row['faculty_name'];
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . ($borrower ?: 'Unknown') . "</td>";
        echo "<td>" . ($row['book_title'] ?: 'Unknown') . "</td>";
        echo "<td>" . $row['borrow_date'] . "</td>";
        echo "<td>" . $row['due_date'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>
