<?php
include 'includes/session.php';
include 'includes/conn.php'; // make sure your DB connection is included
include 'includes/activity_helper.php';

if(isset($_POST['id'])) {
    $id = $_POST['id'];

    // Fetch the specific archived student
    $stmt = $conn->prepare("SELECT * FROM archived_students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        $student = $result->fetch_assoc();

        // Insert into students table
        // Generate default password if not stored in archive
        $password = isset($student['password']) ? $student['password'] : password_hash($student['student_id'], PASSWORD_DEFAULT);
        $created_on = isset($student['created_on']) ? $student['created_on'] : date('Y-m-d H:i:s');
        
        $insert = $conn->prepare("
            INSERT INTO students 
            (student_id, firstname, middlename, lastname, email, phone, course_id, password, created_on) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insert->bind_param(
            "sssssssss",
            $student['student_id'],
            $student['firstname'],
            $student['middlename'],
            $student['lastname'],
            $student['email'],
            $student['phone'],
            $student['course_id'],
            $password,
            $created_on
        );
        $insert->execute();
        $insert->close();

        // Delete from archived_students
        $delete = $conn->prepare("DELETE FROM archived_students WHERE id = ?");
        $delete->bind_param("i", $id);
        $delete->execute();
        $delete->close();

        // Log activity
        logActivity($conn, $user['id'], 'RESTORE', "Student '{$student['student_id']}' ({$student['firstname']} {$student['lastname']}) restored from archive", 'students', $id);
        $_SESSION['success'] = 'Student restored successfully';
    } else {
        $_SESSION['error'] = 'Student not found';
    }
} else {
    $_SESSION['error'] = 'Invalid request';
}

// Redirect back to archived students page
header('Location: archived_student.php');
exit();
?>
