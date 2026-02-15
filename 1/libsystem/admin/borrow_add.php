<?php
include 'includes/session.php';
include 'includes/conn.php';

if(isset($_POST['add'])){
    $student_id_input = $_POST['student'];
    $phone_input = $_POST['phone'];
    $due_date = $_POST['due_date'];

    // Check if student exists
    $sql = "SELECT * FROM students WHERE student_id = '$student_id_input'";
    $query = $conn->query($sql);

    if($query->num_rows < 1){
        $_SESSION['error'][] = "Student not found";
    } else {
        $student = $query->fetch_assoc();
        $student_db_id = $student['id'];

        // Update student phone if changed
        if($student['phone'] != $phone_input){
            $conn->query("UPDATE students SET phone = '$phone_input' WHERE id = '$student_db_id'");
        }

        $added = 0;
        foreach($_POST['isbn'] as $isbn){
            if(!empty($isbn)){
                // Check book availability
                $sql = "SELECT * FROM books WHERE isbn = '$isbn' AND status = 0";
                $book_query = $conn->query($sql);

                if($book_query->num_rows > 0){
                    $book = $book_query->fetch_assoc();
                    $book_id = $book['id'];

                    // Insert into borrow table
                    $sql = "INSERT INTO borrow (student_id, book_id, date_borrow, due_date, status) 
                            VALUES ('$student_db_id', '$book_id', NOW(), '$due_date', 0)";
                    if($conn->query($sql)){
                        $added++;
                        // Update book status
                        $conn->query("UPDATE books SET status = 1 WHERE id = '$book_id'");
                    } else {
                        $_SESSION['error'][] = $conn->error;
                    }
                } else {
                    $_SESSION['error'][] = "Book with ISBN $isbn unavailable";
                }
            }
        }

        if($added > 0){
            $book_text = ($added == 1) ? "Book" : "Books";
            $_SESSION['success'] = "$added $book_text successfully borrowed";
        }
    }
} else {
    $_SESSION['error'][] = "Fill up add form first";
}

header('location: transactions.php');
?>
