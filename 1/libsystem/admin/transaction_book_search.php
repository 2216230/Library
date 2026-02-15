<?php
include 'includes/session.php';
include 'includes/conn.php';
header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');
if(strlen($q) < 2){
    echo json_encode(['success'=>false,'message'=>'Query too short','data'=>[]]);
    exit;
}

// Get books that have at least one available copy
$sql = "SELECT b.id, b.call_no, b.title, b.author, b.publisher,
        (SELECT COUNT(*) FROM book_copies bc WHERE bc.book_id = b.id AND bc.availability='available') AS available_count
        FROM books b
        WHERE b.title LIKE ? OR b.call_no LIKE ? 
        HAVING available_count > 0
        LIMIT 20";

$like = "%{$q}%";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$res = $stmt->get_result();

$data = [];
while($row = $res->fetch_assoc()){
    $data[] = $row;
}

echo json_encode(['success'=>true,'data'=>$data]);
exit;
