<?php
include 'includes/session.php';
include 'includes/conn.php';

$id = $_POST['id'];
$borrower_id = $_POST['borrower_id'];
$borrow_date = $_POST['borrow_date'];
$due_date    = $_POST['due_date'];
$status      = $_POST['status'] ?? 'borrowed';

// Validate that dates are provided
if(!$borrow_date || !$due_date){
    echo json_encode(['status'=>'error','message'=>'Borrow date and due date are required']);
    exit;
}

// Validate date formats (should be YYYY-MM-DD)
$borrow_date_obj = DateTime::createFromFormat('Y-m-d', $borrow_date);
$due_date_obj = DateTime::createFromFormat('Y-m-d', $due_date);

if(!$borrow_date_obj || !$due_date_obj){
    echo json_encode(['status'=>'error','message'=>'Invalid date format. Use YYYY-MM-DD']);
    exit;
}

// Validate that due_date is not less than borrow_date
if($due_date_obj < $borrow_date_obj){
    echo json_encode(['status'=>'error','message'=>'Due date cannot be earlier than borrow date']);
    exit;
}

// Validate status
$valid_statuses = ['borrowed', 'returned', 'lost', 'damaged'];
if (!in_array(strtolower($status), $valid_statuses)) {
    echo json_encode(['status'=>'error','message'=>'Invalid status value']);
    exit;
}

// Get borrower_type for this borrower
$type_sql = "
SELECT 'student' as type FROM students WHERE id = ? 
UNION 
SELECT 'faculty' FROM faculty WHERE id = ?
LIMIT 1
";
$stmt = $conn->prepare($type_sql);
$stmt->bind_param('ii', $borrower_id, $borrower_id);
$stmt->execute();
$type_res = $stmt->get_result();
$type_row = $type_res->fetch_assoc();
$borrower_type = $type_row['type'] ?? null;

if(!$borrower_type){
    echo json_encode(['status'=>'error','message'=>'Borrower not found']);
    exit;
}

// Get copy_id before updating (needed for book_copies update)
$get_copy_stmt = $conn->prepare("SELECT copy_id FROM borrow_transactions WHERE id = ? LIMIT 1");
$get_copy_stmt->bind_param('i', $id);
$get_copy_stmt->execute();
$get_copy_res = $get_copy_stmt->get_result();
$copy_row = $get_copy_res->fetch_assoc();
$copy_id = $copy_row['copy_id'] ?? null;
$get_copy_stmt->close();

// Update transaction
$update_sql = "
UPDATE borrow_transactions 
SET borrower_id = ?, borrower_type = ?, borrow_date = ?, due_date = ?, status = ?
WHERE id = ?
";
$stmt = $conn->prepare($update_sql);
$stmt->bind_param('issssi', $borrower_id, $borrower_type, $borrow_date, $due_date, $status, $id);

if($stmt->execute()){
    // Map status to book_copies availability
    $status_lower = strtolower($status);
    $availability_map = [
        'borrowed' => 'borrowed',
        'returned' => 'available',
        'lost' => 'lost',
        'damaged' => 'damaged',
        'repair' => 'repair',
        'overdue' => 'overdue'
    ];
    
    $availability = $availability_map[$status_lower] ?? 'borrowed';
    
    // Update book_copies availability if copy exists
    if (!empty($copy_id)) {
        $update_copy_stmt = $conn->prepare("UPDATE book_copies SET availability = ? WHERE id = ?");
        $update_copy_stmt->bind_param('si', $availability, $copy_id);
        $update_copy_stmt->execute();
        $update_copy_stmt->close();
    }
    
    echo json_encode(['status'=>'success', 'message' => 'Transaction updated']);
} else {
    echo json_encode(['status'=>'error','message'=>'Update failed: '.$conn->error]);
}
$stmt->close();
?>