<?php
include 'includes/session.php';
include 'includes/conn.php';

if(!isset($_SESSION['admin'])){
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid book ID']);
        exit();
    }

    $sql = "SELECT copy_number, availability, date_created 
            FROM book_copies 
            WHERE book_id = $id 
            ORDER BY copy_number ASC";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception('Query error: ' . $conn->error);
    }

    $copies = [];
    while ($row = $result->fetch_assoc()) {
        $copies[] = $row;
    }

    echo json_encode([
        'success' => true,
        'copies' => $copies
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
