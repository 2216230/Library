<?php 
include 'includes/session.php';
include 'includes/conn.php';

if ($_POST['id']) {
    $id = intval($_POST['id']);
    
    // Get validation record first
    $result = $conn->query("SELECT * FROM inventory_validations WHERE id = $id");
    $validation = $result->fetch_assoc();
    
    // Delete validation
    $delete_sql = "DELETE FROM inventory_validations WHERE id = $id";
    
    if ($conn->query($delete_sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}
?>
