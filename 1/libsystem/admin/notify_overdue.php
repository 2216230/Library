<?php
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/mailer.php';

// Set timezone
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');

// Find overdue borrowers (not returned and due_date < today)
$sql = "
SELECT 
  bt.id AS transaction_id,
  bt.borrower_type,
  bt.borrower_id,
  b.title AS book_title,
  bt.due_date,
  s.firstname AS s_fname, s.lastname AS s_lname, s.email AS s_email,
  f.firstname AS f_fname, f.lastname AS f_lname, f.email AS f_email,
  DATEDIFF(CURDATE(), bt.due_date) AS days_overdue
FROM borrow_transactions bt
LEFT JOIN books b ON bt.book_id = b.id
LEFT JOIN students s ON (bt.borrower_type='student' AND bt.borrower_id=s.id)
LEFT JOIN faculty f ON (bt.borrower_type='faculty' AND bt.borrower_id=f.id)
WHERE bt.status = 'borrowed' 
AND bt.due_date < CURDATE()
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$notified_count = 0;
$failed_count = 0;

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'No overdue borrowers found.'
    ]);
    exit;
}

while ($row = $result->fetch_assoc()) {
    // Get borrower info depending on type
    if ($row['borrower_type'] === 'student') {
        $name = $row['s_fname'] . ' ' . $row['s_lname'];
        $email = $row['s_email'];
    } else {
        $name = $row['f_fname'] . ' ' . $row['f_lname'];
        $email = $row['f_email'];
    }

    $bookTitle = $row['book_title'];
    $dueDate = date('M d, Y', strtotime($row['due_date']));
    $daysOverdue = $row['days_overdue'];

    // Send overdue notice via PHPMailer
    if (sendOverdueNotice($name, $email, $bookTitle, $dueDate, $daysOverdue)) {
        $notified_count++;
        // Log the notification
        error_log("Overdue notification sent to: $name ($email) for book: $bookTitle");
    } else {
        $failed_count++;
        // Log the failure
        error_log("Failed to send overdue notification to: $name ($email)");
    }
}

echo json_encode([
    'success' => true,
    'notified_count' => $notified_count,
    'failed_count' => $failed_count,
    'message' => "Notifications sent successfully to $notified_count borrower(s)." . ($failed_count > 0 ? " Failed to send to $failed_count borrower(s)." : "")
]);
?>