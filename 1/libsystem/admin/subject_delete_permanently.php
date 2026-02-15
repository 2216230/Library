<?php
include 'includes/session.php';
include 'includes/conn.php';

if(isset($_POST['id'])){
    $id = intval($_POST['id']);
    
    // Delete from archived_subject table permanently
    $stmt = $conn->prepare("DELETE FROM archived_subject WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()){
        $_SESSION['success'] = 'Subject deleted permanently';
    } else {
        $_SESSION['error'] = $conn->error;
    }
    $stmt->close();
} else {
    $_SESSION['error'] = 'Select subject to delete first';
}

header('location: archived_subject.php');
exit();
?>
