<?php
include 'includes/session.php';
include 'includes/conn.php';

if(isset($_POST['id'])){
    $id = intval($_POST['id']);

    $query = $conn->prepare("SELECT * FROM archived_faculty WHERE id=?");
    $query->bind_param("i", $id);
    $query->execute();
    $result = $query->get_result();
    $employee = $result->fetch_assoc();

    if($employee){
        // Restore faculty with all required fields
        $faculty_id = $employee['faculty_id'] ?? '';
        $password = $employee['password'] ?? password_hash($employee['faculty_id'], PASSWORD_DEFAULT);
        $firstname = $employee['firstname'] ?? '';
        $middlename = $employee['middlename'] ?? '';
        $lastname = $employee['lastname'] ?? '';
        $phone = $employee['phone'] ?? '';
        $email = $employee['email'] ?? '';
        $department = $employee['department'] ?? '';
        $created_on = $employee['created_on'] ?? date('Y-m-d');
        
        $insert = $conn->prepare("INSERT INTO faculty (faculty_id, password, firstname, middlename, lastname, phone, email, department, created_on) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert->bind_param("sssssssss", $faculty_id, $password, $firstname, $middlename, $lastname, $phone, $email, $department, $created_on);
        $insert->execute();

        $delete = $conn->prepare("DELETE FROM archived_faculty WHERE id=?");
        $delete->bind_param("i", $id);
        $delete->execute();

        $_SESSION['success'] = 'Employee restored successfully.';
    } else {
        $_SESSION['error'] = 'Employee not found.';
    }
} else {
    $_SESSION['error'] = 'Invalid request.';
}

header('location: archived_employee.php');
exit;
?>
