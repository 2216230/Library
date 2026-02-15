<?php 
include 'includes/conn.php';

// Check if table exists and its structure
$result = $conn->query("DESCRIBE inventory_validations");

if($result) {
    echo "Table columns:\n";
    while($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

// Try to get first few rows to see what data is there
$data = $conn->query("SELECT * FROM inventory_validations LIMIT 1");
if($data && $data->num_rows > 0) {
    echo "\nFirst row fields:\n";
    $row = $data->fetch_assoc();
    foreach($row as $key => $val) {
        echo "- " . $key . "\n";
    }
}
?>
