<?php
include 'includes/session.php';
include 'includes/conn.php';

if(isset($_POST['subject_id'])){
    $subject_id = $_POST['subject_id'];
    $assigned_books = [];

    $query = $conn->query("SELECT book_id FROM book_subject_map WHERE subject_id='$subject_id'");
    while($row = $query->fetch_assoc()){
        $assigned_books[] = $row['book_id'];
    }

    echo json_encode($assigned_books);
}
?>
