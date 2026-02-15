<?php
include 'includes/session.php';
if(isset($_POST['id'])){
    $id = $_POST['id'];

    // Delete the course from archived_course
    $conn->query("DELETE FROM archived_course WHERE id='$id'");

    $_SESSION['success'] = 'Course deleted permanently';
}
header('Location: archived_course.php');
?>
