<?php
// transaction_export_word.php
include 'includes/session.php';
include 'includes/conn.php';

// Accept optional ?filter=borrowed_today|returned_today|overdue
$filter = $_GET['filter'] ?? '';
$where = '';
$title = 'All Transactions';
$today = date('Y-m-d');

switch ($filter) {
    case 'borrowed_today':
        $where = "WHERE DATE(bt.borrow_date) = CURDATE()";
        $title = 'Borrowed Today';
        break;
    case 'returned_today':
        $where = "WHERE DATE(bt.return_date) = CURDATE() AND bt.status = 'returned'";
        $title = 'Returned Today';
        break;
    case 'overdue':
        $where = "WHERE bt.status = 'borrowed' AND DATE(bt.due_date) < CURDATE()";
        $title = 'Overdue Books';
        break;
    default:
        $where = '';
        $title = 'All Transactions';
}

$sql = "SELECT bt.*, b.call_no, b.title,
        s.student_id, s.firstname AS s_fname, s.middlename AS s_mname, s.lastname AS s_lname,
        f.faculty_id, f.firstname AS f_fname, f.middlename AS f_mname, f.lastname AS f_lname
        FROM borrow_transactions bt
        LEFT JOIN books b ON bt.book_id = b.id
        LEFT JOIN students s ON (bt.borrower_type='student' AND bt.borrower_id=s.id)
        LEFT JOIN faculty f ON (bt.borrower_type='faculty' AND bt.borrower_id=f.id)
        $where
        ORDER BY bt.borrow_date DESC";

$res = $conn->query($sql);

header("Content-Type: application/vnd.ms-word");
header("Content-Disposition: attachment; filename=book_transactions_report_" . date('Y-m-d') . ".doc");
header("Pragma: no-cache");
header("Expires: 0");

echo "<html><head><meta charset='utf-8'><title>Book Transactions Report</title></head><body>";
echo "<h1 style='text-align:center;'>BOOK TRANSACTIONS REPORT</h1>";
echo "<p style='text-align:center;'>$title | " . date('F j, Y') . "</p>";
echo "<table border='1' cellpadding='6' cellspacing='0' style='border-collapse:collapse; width:100%'>";
echo "<tr style='background:#20650A;color:#fff;'><th>Borrower Type</th><th>ID</th><th>Name</th><th>Call No.</th><th>Title</th><th>Borrowed</th><th>Due</th><th>Status</th></tr>";

if($res && $res->num_rows > 0){
    while($row = $res->fetch_assoc()){
        if ($row['borrower_type'] == 'student') {
            $borrowerID = $row['student_id'];
            $borrowerName = $row['s_fname'] . ' ' . (!empty($row['s_mname']) ? $row['s_mname'].' ' : '') . $row['s_lname'];
        } else {
            $borrowerID = $row['faculty_id'];
            $borrowerName = $row['f_fname'] . ' ' . (!empty($row['f_mname']) ? $row['f_mname'].' ' : '') . $row['f_lname'];
        }

        $status = ($row['status'] == 'returned') ? 'Returned' : 
                 ((date('Y-m-d') > date('Y-m-d', strtotime($row['due_date']))) ? 'Overdue' : 'Borrowed');

        echo "<tr>";
        echo "<td>".ucfirst($row['borrower_type'])."</td>";
        echo "<td>{$borrowerID}</td>";
        echo "<td>{$borrowerName}</td>";
        echo "<td>{$row['call_no']}</td>";
        echo "<td>{$row['title']}</td>";
        echo "<td>".date('M d, Y', strtotime($row['borrow_date']))."</td>";
        echo "<td>".date('M d, Y', strtotime($row['due_date']))."</td>";
        echo "<td>{$status}</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='8' style='text-align:center;'>No records found</td></tr>";
}

echo "</table>";
echo "<p style='text-align:center; margin-top:20px;'>Generated on " . date('F j, Y \a\t g:i A') . "</p>";
echo "</body></html>";
exit;
