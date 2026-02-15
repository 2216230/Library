<?php
include 'includes/session.php';

if(isset($_POST['id'])){
    $id = $_POST['id'];

    // Fetch from archived_course
    $sql = "SELECT * FROM archived_course WHERE id = '$id'";
    $query = $conn->query($sql);
    $row = $query->fetch_assoc();

    if($row){
        $code = $row['code'];      // column in archived_course
        $title = $row['title'];    // column in archived_course

        // Insert back into course table (use correct column name in course table)
        $insert = "INSERT INTO course (code, title) VALUES ('$code', '$title')";
        if($conn->query($insert)){
            // Delete from archived_course
            $conn->query("DELETE FROM archived_course WHERE id = '$id'");
            $_SESSION['success'] = 'Course restored successfully';
        } else {
            $_SESSION['error'] = 'Failed to restore course: '.$conn->error;
        }
    } else {
        $_SESSION['error'] = 'Archived course not found';
    }
} else {
    $_SESSION['error'] = 'Select course to restore first';
}

header('location: archived_course.php');
?>
