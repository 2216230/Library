<?php
include 'includes/session.php';
include 'includes/conn.php';

if(!isset($_SESSION['admin'])){
    http_response_code(401);
    die('Unauthorized');
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $book_id = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
    $discrepancy = isset($_POST['discrepancy']) ? intval($_POST['discrepancy']) : 0;
    
    if($book_id > 0){
        // Currently just logging - later will save to inventory_validation_items table
        // For now, we're just confirming the request was received
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Discrepancy status saved']);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid book ID']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
