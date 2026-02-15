<?php
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/activity_helper.php';

if(isset($_POST['id'])){
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("UPDATE faculty SET archived = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        $_SESSION['success'] = "Faculty restored successfully.";
    } else {
        $_SESSION['error'] = "Error restoring faculty.";
    }
}

header("Location: archived_faculty.php");
exit;
?>
