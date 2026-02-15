<?php
include 'includes/session.php';
include 'includes/conn.php';
header('Content-Type: application/json');

$id = $_GET['id'] ?? 0;

$sql = "
SELECT 
    bt.*,
    CONCAT(st.firstname, ' ', st.lastname) AS student_name,
    CONCAT(fc.firstname, ' ', fc.lastname) AS faculty_name
FROM borrow_transactions bt
LEFT JOIN students st ON bt.borrower_type = 'student' AND bt.borrower_id = st.id
LEFT JOIN faculty fc  ON bt.borrower_type = 'faculty' AND bt.borrower_id = fc.id
WHERE bt.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

if($row) {
    $borrower_name = '';
    if($row['borrower_type'] == 'student') $borrower_name = $row['student_name'];
    elseif($row['borrower_type'] == 'faculty') $borrower_name = $row['faculty_name'];

    echo json_encode([
        'id' => $row['id'],
        'borrower' => ($borrower_name ?: 'Unknown') . ' (' . ucfirst($row['borrower_type']) . ')',
        'borrower_id' => $row['borrower_id'],
        'borrow_date' => date('Y-m-d', strtotime($row['borrow_date'])), // format for <input type="date">
        'due_date'    => date('Y-m-d', strtotime($row['due_date'])),     // format for <input type="date">
        'status'      => $row['status']
    ]);
} else {
    echo json_encode(['error' => 'Transaction not found']);
}


?>
