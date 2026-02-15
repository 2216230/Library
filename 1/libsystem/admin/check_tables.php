<?php
$conn = new mysqli('localhost', 'root', '', 'libsystem5');

// Check penalty_settlements table
echo "<h3>penalty_settlements Table Structure:</h3>";
$result = $conn->query("DESCRIBE penalty_settlements");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " | " . $row['Type'] . " | " . ($row['Null'] == 'YES' ? 'NULL' : 'NOT NULL') . "<br>";
    }
} else {
    echo "ERROR: " . $conn->error . "<br>";
}

echo "<hr>";

// Check borrow_transactions table
echo "<h3>borrow_transactions Table Structure:</h3>";
$result = $conn->query("DESCRIBE borrow_transactions");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " | " . $row['Type'] . "<br>";
}

echo "<hr>";

// Check if we have any test data in penalty_settlements
echo "<h3>Current Records in penalty_settlements:</h3>";
$result = $conn->query("SELECT COUNT(*) as cnt FROM penalty_settlements");
$row = $result->fetch_assoc();
echo "Total records: " . $row['cnt'] . "<br>";

$conn->close();
?>
