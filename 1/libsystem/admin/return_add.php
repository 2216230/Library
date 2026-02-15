<?php
include 'includes/session.php';

if(isset($_POST['add'])){
    $student_input = $_POST['student'];
    $errors = [];
    $returned = 0;

    // Check student
    $sql = "SELECT * FROM students WHERE student_id = '$student_input'";
    $query = $conn->query($sql);
    if($query->num_rows < 1){
        $errors[] = "Student not found";
    } else {
        $student = $query->fetch_assoc();
        $student_id = $student['id'];

        foreach($_POST['isbn'] as $isbn){
            if(!empty($isbn)){
                // Check book
                $sql = "SELECT * FROM books WHERE isbn = '$isbn'";
                $book_query = $conn->query($sql);

                if($book_query->num_rows < 1){
                    $errors[] = "Book not found: ISBN - $isbn";
                    continue;
                }

                $book = $book_query->fetch_assoc();
                $book_id = $book['id'];

                // Check active borrow
                $sql = "SELECT * FROM borrow WHERE student_id = '$student_id' AND book_id = '$book_id' AND status = 0";
                $borrow_query = $conn->query($sql);

                if($borrow_query->num_rows < 1){
                    $errors[] = "Borrow record not found: ISBN - $isbn, Student ID: $student_input";
                    continue;
                }

                $borrow = $borrow_query->fetch_assoc();
                $borrow_id = $borrow['id'];

                // Insert return record
                if($conn->query("INSERT INTO returns (student_id, book_id, date_return) VALUES ('$student_id', '$book_id', NOW())")){
                    $returned++;
                    $conn->query("UPDATE books SET status = 0 WHERE id = '$book_id'");
                    $conn->query("UPDATE borrow SET status = 1 WHERE id = '$borrow_id'");
                } else {
                    $errors[] = $conn->error;
                }
            }
        }
    }

    if($returned > 0){
        $book_text = ($returned == 1) ? "Book" : "Books";
        $_SESSION['success'] = "$returned $book_text successfully returned";
    }

    if(count($errors) > 0){
        $_SESSION['error'] = $errors;
    }

} else {
    $_SESSION['error'] = ["Fill up the form first"];
}

header('location: transactions.php');
?>
