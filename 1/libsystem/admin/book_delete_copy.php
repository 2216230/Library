<?php
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/activity_helper.php';

if(!isset($_POST['copy_id'])){
    echo json_encode(['error'=>'Copy ID required']);
    exit();
}

$copy_id = intval($_POST['copy_id']);

// Get copy details for logging before deletion
$info_stmt = $conn->prepare("
    SELECT bc.copy_number, bc.availability, b.title AS book_title, b.id AS book_id
    FROM book_copies bc
    LEFT JOIN books b ON bc.book_id = b.id
    WHERE bc.id = ?
");
$info_stmt->bind_param("i", $copy_id);
$info_stmt->execute();
$copy_info = $info_stmt->get_result()->fetch_assoc();
$info_stmt->close();

// Allow deletion only when copy is not currently borrowed/overdue and is in an allowed availability state
$allowed_sql = "DELETE FROM book_copies WHERE id = ? AND LOWER(availability) IN ('available','damaged','lost')";
$stmt = $conn->prepare($allowed_sql);
$stmt->bind_param("i", $copy_id);
$stmt->execute();
if($stmt->affected_rows > 0){
    // Log activity
    $adminId = $_SESSION['admin'] ?? $_SESSION['superadmin'] ?? null;
    if ($adminId && $copy_info) {
        $action = 'DELETE_COPY';
        $book_title = $copy_info['book_title'] ?? 'Unknown Book';
        $copy_number = $copy_info['copy_number'] ?? 'Unknown';
        $description = "Book copy deleted: {$book_title} | Copy #: {$copy_number}";
        logActivity($conn, $adminId, $action, $description, 'book_copies', $copy_info['book_id'] ?? $copy_id);
    }
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['error'=>'Cannot delete copy - it may be borrowed or not in a deletable state']);
}
$stmt->close();
