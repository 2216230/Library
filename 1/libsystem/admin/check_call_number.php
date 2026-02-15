<?php
include 'includes/session.php';
include 'includes/conn.php';

header('Content-Type: application/json');

if (!isset($_POST['call_no'])) {
    echo json_encode(['exists' => false, 'error' => 'Missing call_no parameter']);
    exit();
}

$call_no = trim($_POST['call_no']);
$book_id = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;

// Prepare query - check if call_no exists, excluding current book if editing
if ($book_id > 0) {
    // Edit mode - exclude current book
    $stmt = $conn->prepare("SELECT id FROM books WHERE LOWER(call_no) = LOWER(?) AND id != ? LIMIT 1");
    $stmt->bind_param("si", $call_no, $book_id);
} else {
    // Add mode - check all books
    $stmt = $conn->prepare("SELECT id FROM books WHERE LOWER(call_no) = LOWER(?) LIMIT 1");
    $stmt->bind_param("s", $call_no);
}

$stmt->execute();
$stmt->store_result();
$exists = $stmt->num_rows > 0;
$stmt->close();

echo json_encode(['exists' => $exists]);
?>
