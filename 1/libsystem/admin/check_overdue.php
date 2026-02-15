<?php
include 'includes/session.php';
include 'includes/conn.php';

// Set timezone
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');

// Check for overdue borrowers
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
AND DATE(bt.due_date) < CURDATE()
ORDER BY bt.due_date ASC
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$overdue_list = [];
$overdue_count = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Get borrower info depending on type
        if ($row['borrower_type'] === 'student') {
            $name = $row['s_fname'] . ' ' . $row['s_lname'];
            $email = $row['s_email'];
        } else {
            $name = $row['f_fname'] . ' ' . $row['f_lname'];
            $email = $row['f_email'];
        }

        $overdue_list[] = [
            'name' => $name,
            'email' => $email,
            'book_title' => $row['book_title'],
            'due_date' => date('M d, Y', strtotime($row['due_date'])),
            'days_overdue' => $row['days_overdue']
        ];
        $overdue_count++;
    }
    
    echo json_encode([
        'success' => true,
        'overdue_count' => $overdue_count,
        'overdue_list' => $overdue_list
    ]);
} else {
    echo json_encode([
        'success' => true,
        'overdue_count' => 0,
        'overdue_list' => [],
        'message' => 'No overdue books found'
    ]);
}
?>