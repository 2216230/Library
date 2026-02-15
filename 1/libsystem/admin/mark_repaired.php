<?php
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/activity_helper.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: overdue_management.php?tab=repair');
    exit;
}

$id = intval($_GET['id']);

// Fetch transaction with book and borrower details for logging
$tstmt = $conn->prepare("
    SELECT 
        bt.id, bt.copy_id, b.title AS book_title,
        CASE 
            WHEN bt.borrower_type = 'student' THEN CONCAT(s.firstname, ' ', s.lastname)
            WHEN bt.borrower_type = 'faculty' THEN CONCAT(f.firstname, ' ', f.lastname)
            ELSE 'Unknown'
        END as borrower_name
    FROM borrow_transactions bt
    LEFT JOIN books b ON bt.book_id = b.id
    LEFT JOIN students s ON bt.borrower_type = 'student' AND bt.borrower_id = s.id
    LEFT JOIN faculty f ON bt.borrower_type = 'faculty' AND bt.borrower_id = f.id
    WHERE bt.id = ? LIMIT 1
");
$tstmt->bind_param('i', $id);
$tstmt->execute();
$tres = $tstmt->get_result();
if (!$tres || $tres->num_rows == 0) {
    header('Location: overdue_management.php?tab=repair');
    exit;
}
$tran = $tres->fetch_assoc();

$copy_id = $tran['copy_id'];
$book_title = $tran['book_title'] ?? 'Unknown Book';
$borrower_name = $tran['borrower_name'] ?? 'Unknown Borrower';

// Update copy availability if exists
if (!empty($copy_id)) {
    $u = $conn->prepare("UPDATE book_copies SET availability = 'available' WHERE id = ?");
    $u->bind_param('i', $copy_id);
    $u->execute();
}

// Mark transaction as returned and set return_date to today
$now = date('Y-m-d');
$u2 = $conn->prepare("UPDATE borrow_transactions SET status = 'returned', return_date = ? WHERE id = ?");
$u2->bind_param('si', $now, $id);
$u2->execute();

// Log activity
$adminId = $_SESSION['admin'] ?? $_SESSION['superadmin'] ?? null;
if ($adminId) {
    $action = 'MARK_REPAIRED';
    $description = "Book repaired and returned: {$book_title} | Borrower: {$borrower_name} | Status changed: Repair → Returned";
    logActivity($conn, $adminId, $action, $description, 'borrow_transactions', $id);
}

// Redirect back to repair tab in overdue management
header('Location: overdue_management.php?tab=repair');
exit;

?>
