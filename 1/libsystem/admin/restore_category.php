<?php
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/activity_helper.php';

if(isset($_POST['id'])){
    $id = $_POST['id'];
    $cat = $conn->query("SELECT * FROM archived_category WHERE id='$id'")->fetch_assoc();
    if($cat){
        $stmt = $conn->prepare("INSERT INTO category (name) VALUES (?)");
        $stmt->bind_param("s", $cat['name']);
        $stmt->execute();
        $conn->query("DELETE FROM archived_category WHERE id='$id'");
        
        // Log activity
        logActivity($conn, $user['id'], 'RESTORE', "Category '{$cat['name']}' restored from archive", 'category', $id);
        $_SESSION['success'] = 'Category restored successfully';
    } else { $_SESSION['error'] = 'Category not found'; }
} else { $_SESSION['error'] = 'No category selected'; }

header('location: archived_category.php');
?>
