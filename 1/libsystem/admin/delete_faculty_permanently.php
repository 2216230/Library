<?php
include 'includes/session.php';
include 'includes/conn.php';

if(isset($_POST['id'])){
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM faculty WHERE id = ?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        $_SESSION['success'] = "Faculty permanently deleted.";
    } else {
        $_SESSION['error'] = "Error deleting faculty.";
    }
}

header("Location: archived_faculty.php");
exit;
?>
