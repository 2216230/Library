<?php
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/activity_helper.php';

if(!isset($_POST['copy_id'], $_POST['copy_number'])){
    echo json_encode(['error'=>'Missing parameters']);
    exit();
}

$copy_id = intval($_POST['copy_id']);
$new_number = intval($_POST['copy_number']);
$new_availability = $_POST['availability'] ?? null;

if($new_number < 1){
    echo json_encode(['error'=>'Invalid copy number']);
    exit();
}

// Get current record
$check_stmt = $conn->prepare("SELECT availability FROM book_copies WHERE id=?");
$check_stmt->bind_param("i", $copy_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$current_record = $check_result->fetch_assoc();
$check_stmt->close();

if(!$current_record){
    echo json_encode(['error'=>'Copy not found']);
    exit();
}

$current_status = $current_record['availability'];

// Allow editing if: available, damaged, repair, or lost
// Prevent editing if: borrowed or overdue
$editable_statuses = ['available', 'damaged', 'repair', 'lost'];
if(!in_array(strtolower($current_status), $editable_statuses)){
    echo json_encode(['error'=>"Cannot edit - book is currently $current_status"]);
    exit();
}

// Update both copy number and availability
if($new_availability){
    // Validate availability value
    $valid_statuses = ['available', 'borrowed', 'damaged', 'repair', 'lost', 'overdue'];
    if(!in_array(strtolower($new_availability), $valid_statuses)){
        echo json_encode(['error'=>'Invalid availability status']);
        exit();
    }
    
    $stmt = $conn->prepare("UPDATE book_copies SET copy_number=?, availability=? WHERE id=?");
    $stmt->bind_param("isi", $new_number, $new_availability, $copy_id);
} else {
    // Only update copy number
    $stmt = $conn->prepare("UPDATE book_copies SET copy_number=? WHERE id=?");
    $stmt->bind_param("ii", $new_number, $copy_id);
}

$stmt->execute();

if($stmt->affected_rows > 0){
    // Log activity
    $adminId = $_SESSION['admin'] ?? $_SESSION['superadmin'] ?? null;
    if ($adminId) {
        // Get book info for logging
        $book_stmt = $conn->prepare("SELECT b.title FROM book_copies bc LEFT JOIN books b ON bc.book_id = b.id WHERE bc.id = ?");
        $book_stmt->bind_param("i", $copy_id);
        $book_stmt->execute();
        $book_info = $book_stmt->get_result()->fetch_assoc();
        $book_stmt->close();
        
        $book_title = $book_info['title'] ?? 'Unknown Book';
        $action = 'EDIT_COPY';
        $old_status = ucfirst($current_status);
        $new_status = $new_availability ? ucfirst($new_availability) : $old_status;
        $description = "Book copy updated: {$book_title} | Copy #: {$new_number}";
        if ($new_availability && strtolower($current_status) !== strtolower($new_availability)) {
            $description .= " | Status changed: {$old_status} → {$new_status}";
        }
        logActivity($conn, $adminId, $action, $description, 'book_copies', $copy_id);
    }
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['error'=>'No changes made']);
}
$stmt->close();
?>
