<?php
include 'includes/conn.php';

// Test: Try to update a specific book_copy to 'lost' directly
$test_copy_id = 1; // Change this to a real copy ID

echo "<h2>Test Update book_copies to 'lost'</h2>";

// First check current value
$check = $conn->query("SELECT id, availability FROM book_copies WHERE id = $test_copy_id");
$current = $check->fetch_assoc();

echo "<p><strong>Current value:</strong> ID=" . $current['id'] . ", Availability='" . $current['availability'] . "'</p>";

// Try to update
$update_result = $conn->query("UPDATE book_copies SET availability = 'lost' WHERE id = $test_copy_id");

if ($update_result) {
    echo "<p style='color: green;'><strong>✓ Update query successful</strong></p>";
    
    // Check new value
    $check2 = $conn->query("SELECT id, availability FROM book_copies WHERE id = $test_copy_id");
    $updated = $check2->fetch_assoc();
    echo "<p><strong>New value:</strong> ID=" . $updated['id'] . ", Availability='" . $updated['availability'] . "'</p>";
} else {
    echo "<p style='color: red;'><strong>✗ Update failed: " . $conn->error . "</strong></p>";
}
?>
