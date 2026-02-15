<?php
include 'includes/session.php';
include 'includes/conn.php';

if(!isset($_SESSION['admin'])){
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $book_id = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
    
    if($book_id > 0){
        // Query discrepancy details (currently just returning empty structure)
        // Later this will fetch from inventory_validation_items table
        $response = [
            'success' => true,
            'discrepancy' => [
                'shelved' => '',
                'damaged' => '',
                'lost' => '',
                'repair' => '',
                'missing' => ''
            ]
        ];
        echo json_encode($response);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid book ID']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
