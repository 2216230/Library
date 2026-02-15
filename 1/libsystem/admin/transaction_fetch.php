<?php
// transaction_fetch.php
include 'includes/session.php';
include 'includes/conn.php';
header('Content-Type: application/json; charset=utf-8');

$filter = $_GET['filter'] ?? '';
$where = '';

switch($filter){
    case 'borrowed_today':
        $where = "WHERE DATE(bt.borrow_date) = CURDATE()";
        break;
    case 'returned_today':
        $where = "WHERE DATE(bt.return_date) = CURDATE() AND bt.status = 'returned'";
        break;
    case 'overdue':
        $where = "WHERE bt.due_date < CURDATE() AND bt.status IN ('borrowed', 'overdue')";
        break;
    case 'active':
        $where = "WHERE bt.status IN ('borrowed', 'overdue')";
        break;
    case 'all':
        $where = ""; // Show all transactions with any status
        break;
    default:
        $where = "";
}

$sql = "SELECT bt.*,
               b.call_no, b.title,
               s.student_id, s.firstname AS s_fname, s.middlename AS s_mname, s.lastname AS s_lname,
               f.faculty_id, f.firstname AS f_fname, f.middlename AS f_mname, f.lastname AS f_lname
        FROM borrow_transactions bt
        LEFT JOIN books b ON bt.book_id = b.id
        LEFT JOIN students s ON (bt.borrower_type='student' AND bt.borrower_id = s.id)
        LEFT JOIN faculty f ON (bt.borrower_type='faculty' AND bt.borrower_id = f.id)
        $where
        ORDER BY bt.borrow_date DESC
        LIMIT 500";

$res = $conn->query($sql);
$rows = [];
while($r = $res->fetch_assoc()){
    // derive borrower display
    if($r['borrower_type'] == 'student'){
        $r['borrower_id_number'] = $r['student_id'];
        $r['borrower_name'] = trim($r['s_fname'].' '.($r['s_mname'] ? $r['s_mname'].' ' : '').$r['s_lname']);
    } else {
        $r['borrower_id_number'] = $r['faculty_id'];
        $r['borrower_name'] = trim($r['f_fname'].' '.($r['f_mname'] ? $r['f_mname'].' ' : '').$r['f_lname']);
    }
    $rows[] = $r;
}

echo json_encode(['success'=>true,'data'=>$rows]);
exit;
