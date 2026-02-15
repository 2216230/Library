<?php
include 'includes/session.php';
include 'includes/conn.php';

if(isset($_POST['delete'])){

    $id = $_POST['id'];

    $sql = "DELETE FROM suggested_books WHERE id='$id'";

    if($conn->query($sql)){
        $_SESSION['success'] = "Suggested book has been deleted.";
    } 
    else {
        $_SESSION['error'] = "Failed to delete suggested book.";
    }

}
else{
    $_SESSION['error'] = "Invalid request.";
}

header('location: suggested_books.php');
?>
