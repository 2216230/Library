<?php
// Check what's currently in the overdue_notifications table
include 'libsystem/admin/includes/conn.php';

echo "=== AUTO-SEND OVERDUE NOTIFICATION SYSTEM STATUS ===\n\n";

// Check last notification run
$last_run_file = __DIR__ . '/.last_notification_run';
if (file_exists($last_run_file)) {
    $last_run = file_get_contents($last_run_file);
    echo "Last auto-send run: $last_run\n";
} else {
    echo "Last auto-send run: NEVER (will run on next page load)\n";
}

echo "\n=== TRULY OVERDUE BOOKS (DATE < TODAY) ===\n";
$overdue_query = "
SELECT 
    bt.id,
    bt.borrower_type,
    CONCAT(IFNULL(st.firstname, fc.firstname), ' ', IFNULL(st.lastname, fc.lastname)) AS borrower_name,
    b.title AS book_title,
    bt.due_date,
    DATEDIFF(CURDATE(), bt.due_date) AS days_overdue,
    IFNULL(st.email, fc.email) AS email
FROM borrow_transactions bt
LEFT JOIN books b ON bt.book_id = b.id
LEFT JOIN students st ON bt.borrower_type = 'student' AND bt.borrower_id = st.id
LEFT JOIN faculty fc ON bt.borrower_type = 'faculty' AND bt.borrower_id = fc.id
WHERE bt.status = 'borrowed' AND DATE(bt.due_date) < CURDATE()
ORDER BY bt.due_date DESC
";

$result = $conn->query($overdue_query);
if ($result->num_rows > 0) {
    echo "Found " . $result->num_rows . " truly overdue books:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- ID {$row['id']}: {$row['book_title']} (Due: {$row['due_date']}, {$row['days_overdue']} days overdue)\n";
        echo "  Borrower: {$row['borrower_name']} ({$row['email']})\n";
    }
} else {
    echo "No truly overdue books found.\n";
}

echo "\n=== NOTIFICATION LOG (Last 10) ===\n";
$notif_query = "
SELECT 
    on.id,
    on.transaction_id,
    on.borrower_name,
    on.book_title,
    on.notified_at
FROM overdue_notifications on
ORDER BY on.notified_at DESC
LIMIT 10
";

$result = $conn->query($notif_query);
if ($result->num_rows > 0) {
    echo "Found " . $result->num_rows . " notification records:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- TID {$row['transaction_id']}: {$row['borrower_name']} - {$row['book_title']} (Sent: {$row['notified_at']})\n";
    }
} else {
    echo "No notifications sent yet.\n";
}

echo "\n=== HOW AUTO-SEND WORKS ===\n";
echo "1. Opens overdue_management.php page\n";
echo "2. Checks if auto-send ran today\n";
echo "3. If NOT run today, it:\n";
echo "   - Queries ALL books with status='borrowed' AND due_date < TODAY\n";
echo "   - Sends email to each borrower (if not already notified today)\n";
echo "   - Logs notification timestamp\n";
echo "4. Next auto-send: Tomorrow when page is loaded\n";

$conn->close();
?>
