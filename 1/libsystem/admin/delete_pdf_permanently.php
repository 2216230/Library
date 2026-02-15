<?php
include 'includes/session.php';
include 'includes/conn.php';

if(isset($_POST['id'])){
    $id = $_POST['id'];

    // Get the file path
    $result = $conn->query("SELECT file_path FROM archived_pdf_books WHERE id='$id'");
    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        $file = 'uploads/pdf_books/' . $row['file_path'];

        // Delete the file if it exists
        if(file_exists($file)){
            unlink($file);
        }

        // Delete the database record
        $conn->query("DELETE FROM archived_pdf_books WHERE id='$id'");

        $_SESSION['success'] = 'PDF book deleted permanently';
    } else {
        $_SESSION['error'] = 'Record not found';
    }
}

header('Location: archived_pdf_books.php');
exit;
?>
