<?php
$mysqli = new mysqli('localhost', 'root', '', 'libsystem5');
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

echo "=== BOOKS TABLE STRUCTURE ===\n";
$result = $mysqli->query('DESCRIBE books');
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' (' . $row['Type'] . ') NULL:' . $row['Null'] . ' KEY:' . $row['Key'] . ' EXTRA:' . $row['Extra'] . "\n";
}

echo "\n=== SAMPLE CALL_NO VALUES (10 examples) ===\n";
$result = $mysqli->query('SELECT DISTINCT call_no FROM books WHERE call_no IS NOT NULL LIMIT 10');
while ($row = $result->fetch_assoc()) {
    echo $row['call_no'] . "\n";
}

echo "\n=== ALL TABLE NAMES IN DATABASE ===\n";
$result = $mysqli->query('SHOW TABLES');
while ($row = $result->fetch_row()) {
    echo $row[0] . "\n";
}

echo "\n=== CHECK FOR CLASSIFICATION/CATEGORY TABLES ===\n";
$result = $mysqli->query("SHOW TABLES WHERE Tables_in_libsystem5 LIKE '%classif%' OR Tables_in_libsystem5 LIKE '%dewey%' OR Tables_in_libsystem5 LIKE '%category%'");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_row()) {
        echo "Found: " . $row[0] . "\n";
    }
} else {
    echo "No specific classification tables found\n";
}

echo "\n=== COUNT OF BOOKS BY CALL_NO PREFIX (first 3 chars) ===\n";
$result = $mysqli->query('SELECT SUBSTRING(call_no, 1, 3) as prefix, COUNT(*) as count FROM books WHERE call_no IS NOT NULL GROUP BY prefix ORDER BY count DESC LIMIT 20');
while ($row = $result->fetch_assoc()) {
    echo $row['prefix'] . ': ' . $row['count'] . "\n";
}

$mysqli->close();
?>
