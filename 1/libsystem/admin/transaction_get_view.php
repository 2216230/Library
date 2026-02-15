<?php
include 'includes/session.php';
include 'includes/conn.php';
header('Content-Type: application/json');

if(isset($_POST['id'])){
    $id = $_POST['id'];

    // Get transaction details
$sql = "SELECT bt.*, b.title AS book_title, b.author AS book_author, b.call_no, b.isbn,
        CASE 
            WHEN bt.borrower_type = 'student' THEN CONCAT(s.firstname, ' ', IFNULL(s.middlename,''), ' ', s.lastname)
            WHEN bt.borrower_type = 'faculty' THEN CONCAT(f.firstname, ' ', IFNULL(f.middlename,''), ' ', f.lastname)
        END AS borrower_name,
        CASE 
            WHEN bt.borrower_type = 'student' THEN s.student_id
            WHEN bt.borrower_type = 'faculty' THEN f.faculty_id
        END AS borrower_code,
        CASE 
            WHEN bt.borrower_type = 'student' THEN s.email
            WHEN bt.borrower_type = 'faculty' THEN f.email
        END AS borrower_email,
        CASE 
            WHEN bt.borrower_type = 'student' THEN s.phone
            WHEN bt.borrower_type = 'faculty' THEN f.phone
        END AS borrower_phone
        FROM borrow_transactions bt
        LEFT JOIN books b ON b.id = bt.book_id
        LEFT JOIN students s ON bt.borrower_type='student' AND bt.borrower_id = s.id
        LEFT JOIN faculty f ON bt.borrower_type='faculty' AND bt.borrower_id = f.id
        WHERE bt.id = ?";


    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        $transaction = $result->fetch_assoc();
        echo json_encode([
            'status' => 'success',
            'data' => $transaction
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Transaction not found.'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'No transaction ID provided.'
    ]);
}
?>
