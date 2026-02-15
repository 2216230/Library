<?php
include 'includes/conn.php';

echo "<h2>book_copies Table Structure</h2>";

$result = $conn->query('DESCRIBE book_copies');
echo "<table border='1' cellpadding='8'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . ($row['Key'] ?: '') . "</td>";
    echo "<td>" . ($row['Default'] ?: 'None') . "</td>";
    echo "<td>" . ($row['Extra'] ?: '') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>Sample book_copies Records</h2>";
$result = $conn->query("SELECT id, copy_number, availability, status FROM book_copies LIMIT 20");
echo "<table border='1' cellpadding='8'>";
echo "<tr><th>ID</th><th>Copy Number</th><th>Availability</th><th>Status</th></tr>";
while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['copy_number'] . "</td>";
    echo "<td>" . $row['availability'] . "</td>";
    echo "<td>" . ($row['status'] ?: 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>Unique availability values in book_copies</h2>";
$result = $conn->query("SELECT DISTINCT availability FROM book_copies");
echo "<ul>";
while($row = $result->fetch_assoc()) {
    echo "<li>" . htmlspecialchars($row['availability']) . "</li>";
}
echo "</ul>";
?>
