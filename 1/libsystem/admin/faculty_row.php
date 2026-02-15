<?php
include 'includes/session.php';
include 'includes/conn.php';

if(isset($_POST['id'])){
  $id = $_POST['id'];
  $sql = "SELECT * FROM faculty WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  echo json_encode($result->fetch_assoc());
}
?>
