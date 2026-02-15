<?php
include 'includes/conn.php';
session_start();

if(isset($_POST['id'])){
  $id = $_POST['id'];
  $query = $conn->query("SELECT * FROM archived_pdf_books WHERE id='$id'");
  
  if($query->num_rows > 0){
    $row = $query->fetch_assoc();
    $stmt = $conn->prepare("INSERT INTO pdf_books (title, file_path) VALUES (?, ?)");
    $stmt->bind_param("ss", $row['title'], $row['file_path']);
    if($stmt->execute()){
      $conn->query("DELETE FROM archived_pdf_books WHERE id='$id'");
      $_SESSION['success'] = "PDF restored successfully!";
    } else {
      $_SESSION['error'] = "Failed to restore PDF.";
    }
  } else {
    $_SESSION['error'] = "Record not found!";
  }
} else {
  $_SESSION['error'] = "Invalid request!";
}

header("Location: archived_pdf_books.php");
exit();
?>
