<?php
include 'includes/session.php';

if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename']; // NEW
    $lastname = $_POST['lastname'];
    $course = $_POST['course'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    // Base SQL
    $sql = "UPDATE students 
            SET firstname = '$firstname',
                middlename = '$middlename',
                lastname = '$lastname',
                course_id = '$course',
                email = '$email',
                phone = '$phone'";

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql .= ", password = '$hashed_password'";
    }

    $sql .= " WHERE id = '$id'";

    if ($conn->query($sql)) {
        $_SESSION['success'] = 'Student updated successfully';
    } else {
        $_SESSION['error'] = $conn->error;
    }
} else {
    $_SESSION['error'] = 'Fill up edit form first';
}

header('location: student.php');
?>
