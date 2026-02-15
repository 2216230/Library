<?php
include 'includes/session.php';
include 'includes/conn.php';

if(isset($_POST['editFaculty'])){
  $id = $_POST['id'];
  $faculty_id = $_POST['faculty_id'];
  $firstname = $_POST['firstname'];
  $middlename = $_POST['middlename']; // fix: separate variable
  $lastname = $_POST['lastname'];
  $department = $_POST['department'];
  $email = $_POST['email'];
  $phone = $_POST['phone'];
  $password = $_POST['password'];

  if(!empty($password)){
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE faculty SET faculty_id=?, firstname=?, middlename=?, lastname=?, department=?, email=?, phone=?, password=? WHERE id=?");
    $stmt->bind_param("ssssssssi", $faculty_id, $firstname, $middlename, $lastname, $department, $email, $phone, $hashed_password, $id);
  } else {
    $stmt = $conn->prepare("UPDATE faculty SET faculty_id=?, firstname=?, middlename=?, lastname=?, department=?, email=?, phone=? WHERE id=?");
    $stmt->bind_param("sssssssi", $faculty_id, $firstname, $middlename, $lastname, $department, $email, $phone, $id);
  }

  if($stmt->execute()){
    $_SESSION['success'] = 'Faculty member updated successfully!';
  } else {
    $_SESSION['error'] = 'Error updating faculty member.';
  }

  header('location: faculty.php');
  exit();
}
?>
