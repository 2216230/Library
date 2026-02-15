<?php
include 'includes/session.php';
include 'includes/conn.php';

if(!isset($_SESSION['admin'])){
    header('Content-Type: application/json');
    echo json_encode(null);
    exit();
}

header('Content-Type: application/json');

try {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($id <= 0) {
        echo json_encode(null);
        exit();
    }

    $sql = "SELECT b.id, b.title, b.author, b.call_no, b.section,
                   (SELECT COUNT(*) FROM book_copies WHERE book_id = b.id) as num_copies
            FROM books b
            WHERE b.id = $id";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(null);
    }

} catch (Exception $e) {
    echo json_encode(null);
}
?>
