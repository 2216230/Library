<?php
$mysqli = new mysqli('localhost', 'root', '', 'libsystem5');
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

echo "=== BOOK_CLASSIFICATION_TYPE TABLE STRUCTURE ===\n";
$result = $mysqli->query('DESCRIBE book_classification_type');
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' (' . $row['Type'] . ') NULL:' . $row['Null'] . ' KEY:' . $row['Key'] . "\n";
}

echo "\n=== BOOK_CLASSIFICATION_TYPE DATA ===\n";
$result = $mysqli->query('SELECT * FROM book_classification_type LIMIT 20');
while ($row = $result->fetch_assoc()) {
    print_r($row);
    echo "---\n";
}

echo "\n=== BOOK_CATEGORY_MAP TABLE STRUCTURE ===\n";
$result = $mysqli->query('DESCRIBE book_category_map');
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' (' . $row['Type'] . ') NULL:' . $row['Null'] . ' KEY:' . $row['Key'] . "\n";
}

echo "\n=== CATEGORY TABLE STRUCTURE ===\n";
$result = $mysqli->query('DESCRIBE category');
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' (' . $row['Type'] . ') NULL:' . $row['Null'] . ' KEY:' . $row['Key'] . "\n";
}

echo "\n=== CATEGORY DATA ===\n";
$result = $mysqli->query('SELECT * FROM category LIMIT 20');
while ($row = $result->fetch_assoc()) {
    print_r($row);
    echo "---\n";
}

echo "\n=== BOOKS WITH ACTUAL CALL_NO VALUES (20 samples) ===\n";
$result = $mysqli->query('SELECT id, title, call_no, location, section, type FROM books WHERE call_no IS NOT NULL AND call_no != "" LIMIT 20');
while ($row = $result->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | call_no: " . $row['call_no'] . " | section: " . $row['section'] . " | type: " . $row['type'] . "\n";
}

echo "\n=== BOOK_CATEGORY_MAP SAMPLES ===\n";
$result = $mysqli->query('SELECT * FROM book_category_map LIMIT 20');
while ($row = $result->fetch_assoc()) {
    echo "Book ID: " . $row['book_id'] . " | Category ID: " . $row['category_id'] . "\n";
}

$mysqli->close();
?>
