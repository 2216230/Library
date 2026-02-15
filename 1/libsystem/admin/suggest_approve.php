<?php
include 'includes/session.php';
include 'includes/conn.php';

if(isset($_POST['approve'])){

    $id = $_POST['id'];

    // Get suggested book data
    $sql = "SELECT * FROM suggested_books WHERE id = '$id'";
    $query = $conn->query($sql);

    if($query->num_rows > 0){
        $row = $query->fetch_assoc();

        $title = mysqli_real_escape_string($conn, $row['title']);
        $author = mysqli_real_escape_string($conn, $row['author']);
        $isbn = mysqli_real_escape_string($conn, $row['isbn']);
        $subject = mysqli_real_escape_string($conn, $row['subject']);
        $description = mysqli_real_escape_string($conn, $row['description']);

        // Insert into physical books table with required columns
        $location = 'Library';
        $section = 'General';
        $type = 'Book';
        $call_no = '';
        $publisher = '';
        $publish_date = ''; // unknown from suggestion
        $copy_number = 1;
        $num_copies = 1;
        $status = 0;
        $date_created = date('Y-m-d H:i:s');

        $stmt = $conn->prepare("
            INSERT INTO books (isbn, call_no, title, author, publisher, publish_date, subject, location, section, type, copy_number, num_copies, date_created, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssssssssiisi", $isbn, $call_no, $title, $author, $publisher, $publish_date, $subject, $location, $section, $type, $copy_number, $num_copies, $date_created, $status);

        if($stmt->execute()){
            $stmt->close();
            // Instead of deleting, update status to 'Approved'
            $conn->query("UPDATE suggested_books SET status='Approved' WHERE id = '$id'");
            $_SESSION['success'] = "Suggested book has been approved and added to the library!";
        }
        else{
            $_SESSION['error'] = "Failed to approve suggested book: " . $conn->error;
        }
    }
    else{
        $_SESSION['error'] = "Book suggestion not found.";
    }

} 
else {
    $_SESSION['error'] = "Invalid request.";
}

header('location: suggested_books.php');
exit;
?>
