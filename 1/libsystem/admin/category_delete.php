<?php
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/activity_helper.php';

if(isset($_POST['delete'])){
    $id = $_POST['id'];

    // Get category data first
    $sql = "SELECT * FROM category WHERE id = '$id'";
    $query = $conn->query($sql);
    $row = $query->fetch_assoc();

    if($row){
        // Insert into archived_category
        $insert = "INSERT INTO archived_category (name) VALUES ('".$row['name']."')";
        if($conn->query($insert)){
            // Delete from category table
            $conn->query("DELETE FROM category WHERE id = '$id'");
            $_SESSION['success'] = 'Category deleted and archived successfully';
        } else {
            $_SESSION['error'] = $conn->error;
        }
    } else {
        $_SESSION['error'] = 'Category not found';
    }
} else {
    $_SESSION['error'] = 'Select category to delete first';
}

header('location: category.php');
?>
