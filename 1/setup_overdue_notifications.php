<?php
/**
 * Create overdue_notifications table if it doesn't exist
 * Run this once to set up the database for overdue notifications
 */

include 'libsystem/admin/includes/conn.php';

$sql = "
CREATE TABLE IF NOT EXISTS overdue_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    borrower_email VARCHAR(255) NOT NULL,
    borrower_name VARCHAR(255) NOT NULL,
    book_title VARCHAR(255) NOT NULL,
    days_overdue INT NOT NULL,
    notified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES borrow_transactions(id) ON DELETE CASCADE,
    KEY idx_transaction (transaction_id),
    KEY idx_date (notified_at)
)";

if ($conn->query($sql) === TRUE) {
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<h4>✅ Success!</h4>";
    echo "<p>The 'overdue_notifications' table has been created successfully.</p>";
    echo "<p><strong>You can now use the overdue notification system.</strong></p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
    echo "<h4>❌ Error</h4>";
    echo "<p>Error creating table: " . $conn->error . "</p>";
    echo "</div>";
}

$conn->close();
?>
