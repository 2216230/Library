<?php
include 'includes/session.php';
include 'includes/conn.php';

if(isset($_POST['id'])){
    $id = intval($_POST['id']);
    
    // Get archived subject details
    $stmt = $conn->prepare("SELECT subject_id, name FROM archived_subject WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $archived = $result->fetch_assoc();
    $stmt->close();
    
    if($archived){
        // Restore to subject table
        $stmt = $conn->prepare("INSERT INTO subject (id, name) VALUES (?, ?)");
        $stmt->bind_param("is", $archived['subject_id'], $archived['name']);
        
        if($stmt->execute()){
            $stmt->close();
            
            // Delete from archived_subject
            $stmt = $conn->prepare("DELETE FROM archived_subject WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            
            $_SESSION['success'] = 'Subject restored successfully';
        } else {
            $_SESSION['error'] = $conn->error;
        }
    } else {
        $_SESSION['error'] = 'Archived subject not found';
    }
} else {
    $_SESSION['error'] = 'Select subject to restore first';
}

header('location: archived_subject.php');
?>
