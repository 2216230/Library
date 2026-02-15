<?php
include 'includes/session.php';
if(isset($_POST['id'])){
    $id = $_POST['id'];

    // Delete the category from archived_category
    $conn->query("DELETE FROM archived_category WHERE id='$id'");

    $_SESSION['success'] = 'Category deleted permanently';
}
header('Location: archived_category.php');
?>
