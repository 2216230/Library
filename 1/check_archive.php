<?php
include 'libsystem/admin/includes/conn.php';

// Check table exists
$result = $conn->query("SHOW TABLES LIKE 'archived_transactions'");
echo "Table exists: " . ($result->num_rows > 0 ? "YES" : "NO") . "\n";

// Check record count
if ($result->num_rows > 0) {
    $count = $conn->query("SELECT COUNT(*) as cnt FROM archived_transactions");
    $row = $count->fetch_assoc();
    echo "Records: " . $row['cnt'] . "\n";
}
?>
