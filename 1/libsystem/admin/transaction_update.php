<?php
// transaction_update.php
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/activity_helper.php';
header('Content-Type: application/json; charset=utf-8');

$id = $_POST['id'] ?? null;
$borrower_type = $_POST['borrower_type'] ?? null;
$borrower_id = $_POST['borrower_id'] ?? null;
$due_date = $_POST['due_date'] ?? null;

if(!$id || !$borrower_type || !$borrower_id || !$due_date){
    echo json_encode(['status'=>'error','msg'=>'Missing required fields.']);
    exit;
}

$borrower_type = strtolower($borrower_type);
if(!in_array($borrower_type, ['student','faculty'])){
    echo json_encode(['status'=>'error','msg'=>'Invalid borrower type.']);
    exit;
}

// Validate transaction exists
$stmt = $conn->prepare("SELECT id FROM borrow_transactions WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows === 0){
    echo json_encode(['status'=>'error','msg'=>'Transaction not found.']);
    exit;
}

// Validate borrower exists depending on type
if($borrower_type === 'student'){
    $stmt2 = $conn->prepare("SELECT id FROM students WHERE id = ? LIMIT 1");
} else {
    $stmt2 = $conn->prepare("SELECT id FROM faculty WHERE id = ? LIMIT 1");
}
$stmt2->bind_param("i", $borrower_id);
$stmt2->execute();
$res2 = $stmt2->get_result();
if($res2->num_rows === 0){
    echo json_encode(['status'=>'error','msg'=>'Borrower not found for the selected type.']);
    exit;
}

// Optional: validate due_date is valid date
if(!strtotime($due_date)){
    echo json_encode(['status'=>'error','msg'=>'Invalid due date.']);
    exit;
}

// Perform update
$update = $conn->prepare("UPDATE borrow_transactions SET borrower_type = ?, borrower_id = ?, due_date = ? WHERE id = ?");
$update->bind_param("sisi", $borrower_type, $borrower_id, $due_date, $id);

if($update->execute()){
    // Get details for activity log
    $info_stmt = $conn->prepare("
        SELECT 
            b.title as book_title,
            CASE 
                WHEN ? = 'student' THEN CONCAT(s.firstname, ' ', s.lastname)
                WHEN ? = 'faculty' THEN CONCAT(f.firstname, ' ', f.lastname)
                ELSE 'Unknown'
            END as borrower_name
        FROM borrow_transactions bt
        LEFT JOIN books b ON bt.book_id = b.id
        LEFT JOIN students s ON s.id = ?
        LEFT JOIN faculty f ON f.id = ?
        WHERE bt.id = ?
        LIMIT 1
    ");
    $info_stmt->bind_param("ssiii", $borrower_type, $borrower_type, $borrower_id, $borrower_id, $id);
    $info_stmt->execute();
    $info_result = $info_stmt->get_result();
    $info = $info_result->fetch_assoc();
    $info_stmt->close();
    
    $book_title = $info['book_title'] ?? 'Unknown Book';
    $borrower_name = $info['borrower_name'] ?? 'Unknown Borrower';
    
    // Log activity
    $action = 'UPDATE_TRANSACTION';
    $description = "Updated transaction: {$book_title} | New borrower: {$borrower_name} | New due date: {$due_date}";
    $adminId = $_SESSION['admin'] ?? $_SESSION['superadmin'] ?? null;
    if ($adminId) {
        logActivity($conn, $adminId, $action, $description, 'borrow_transactions', $id);
    }
    
    echo json_encode(['status'=>'success','msg'=>'Transaction updated.']);
} else {
    echo json_encode(['status'=>'error','msg'=>'Failed to update transaction.']);
}
exit;
