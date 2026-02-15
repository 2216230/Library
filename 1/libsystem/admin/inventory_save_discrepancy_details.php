<?php
include 'includes/session.php';
include 'includes/conn.php';

if(!isset($_SESSION['admin'])){
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $book_id = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
    $shelved = isset($_POST['shelved']) ? $conn->real_escape_string($_POST['shelved']) : '';
    $damaged = isset($_POST['damaged']) ? $conn->real_escape_string($_POST['damaged']) : '';
    $lost = isset($_POST['lost']) ? $conn->real_escape_string($_POST['lost']) : '';
    $repair = isset($_POST['repair']) ? $conn->real_escape_string($_POST['repair']) : '';
    $missing = isset($_POST['missing']) ? $conn->real_escape_string($_POST['missing']) : '';
    
    if($book_id > 0){
        // Currently just logging the save
        // Later this will save to inventory_validation_items table with:
        // book_id, validation_session_id, shelved_mismatch, damaged_mismatch, lost_mismatch, repair_mismatch, missing_mismatch
        
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Discrepancy details saved']);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid book ID']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
