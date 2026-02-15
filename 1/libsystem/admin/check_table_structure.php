<?php
include 'includes/conn.php';

echo "<h2>Table Structure: borrow_transactions</h2>";

$result = $conn->query('DESCRIBE borrow_transactions');
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
?>
