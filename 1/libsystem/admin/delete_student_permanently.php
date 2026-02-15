<?php
include 'includes/session.php';
if(isset($_POST['id'])){
    $id = $_POST['id'];

    // Delete the student from archived_students
    $conn->query("DELETE FROM archived_students WHERE id='$id'");

    $_SESSION['success'] = 'Student deleted permanently';
}
header('Location: archived_student.php');
?>
