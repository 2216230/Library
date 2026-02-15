<?php
include 'includes/session.php';
include 'includes/conn.php';
header('Content-Type: application/json; charset=utf-8');

$book_id = intval($_GET['book_id'] ?? 0);

if($book_id <= 0){
    echo json_encode([]);
    exit;
}

$sql = "SELECT id, copy_number, availability FROM book_copies WHERE book_id=? AND availability='available'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $book_id);
$stmt->execute();
$res = $stmt->get_result();

$data = [];
while($row = $res->fetch_assoc()){
    $data[] = $row;
}

echo json_encode($data);
exit;
