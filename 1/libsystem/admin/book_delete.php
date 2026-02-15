<?php
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/activity_helper.php';

if(isset($_POST['delete'])) {

    $book_id = intval($_POST['id']);

    // Validation: do not allow deletion if any copy is currently borrowed or overdue
    $check_stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM borrow_transactions WHERE book_id = ? AND status IN ('borrowed','overdue')");
    $check_stmt->bind_param("i", $book_id);
    $check_stmt->execute();
    $check_res = $check_stmt->get_result()->fetch_assoc();
    $check_stmt->close();

    if ($check_res && intval($check_res['cnt']) > 0) {
        $_SESSION['error'] = "Cannot delete book: one or more copies are currently borrowed or overdue.";
        header("location: book.php");
        exit();
    }

    // Also ensure no copy has availability marked as 'borrowed'
    $copy_check = $conn->prepare("SELECT COUNT(*) as cnt FROM book_copies WHERE book_id = ? AND LOWER(availability) = 'borrowed'");
    $copy_check->bind_param("i", $book_id);
    $copy_check->execute();
    $copy_cnt = $copy_check->get_result()->fetch_assoc();
    $copy_check->close();

    if ($copy_cnt && intval($copy_cnt['cnt']) > 0) {
        $_SESSION['error'] = "Cannot delete book: one or more copies are currently marked as borrowed.";
        header("location: book.php");
        exit();
    }

    // 1. Fetch the book to archive
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if(!$book){
        $_SESSION['error'] = "Book not found.";
        header("location: book.php");
        exit();
    }

    // 2. Insert into archived_books
    $archive_stmt = $conn->prepare("
        INSERT INTO archived_books 
        (book_id, isbn, call_no, location, title, subject, author, publisher, publish_date,num_copies)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $archive_stmt->bind_param(
        "isssssssss",
        $book['id'],
        $book['isbn'],
        $book['call_no'],
        $book['location'],
        $book['title'],
        $book['subject'],
        $book['author'],
        $book['publisher'],
        $book['publish_date'],
        $book['num_copies']
    );

    $archive_stmt->execute();
    $archive_id = $archive_stmt->insert_id;
    $archive_stmt->close();

    // 3. Archive categories
    $cat_q = $conn->prepare("SELECT category_id FROM book_category_map WHERE book_id = ?");
    $cat_q->bind_param("i", $book_id);
    $cat_q->execute();
    $cat_res = $cat_q->get_result();
    while($cat = $cat_res->fetch_assoc()){
        $map_stmt = $conn->prepare("INSERT INTO archived_book_category_map (archive_id, category_id) VALUES (?, ?)");
        $map_stmt->bind_param("ii", $archive_id, $cat['category_id']);
        $map_stmt->execute();
        $map_stmt->close();
    }
    $cat_q->close();

    // 4. Archive copies
        $copy_q = $conn->prepare("SELECT * FROM book_copies WHERE book_id = ?");
        $copy_q->bind_param("i", $book_id);
        $copy_q->execute();
        $copy_res = $copy_q->get_result();
        while($copy = $copy_res->fetch_assoc()){
            $copy_stmt = $conn->prepare("
                INSERT INTO archived_book_copies
                (archive_id, copy_number, availability)
                VALUES (?, ?, ?)
            ");
            $copy_stmt->bind_param(
                "iss",
                $archive_id,
                $copy['copy_number'],
                $copy['availability']
            );
            $copy_stmt->execute();
            $copy_stmt->close();
        }
        $copy_q->close();


    // 5. Delete original book data
    $conn->query("DELETE FROM book_copies WHERE book_id = $book_id");
    $conn->query("DELETE FROM book_category_map WHERE book_id = $book_id");
    $conn->query("DELETE FROM books WHERE id = $book_id");

    // Log activity
    logActivity($conn, $user['id'], 'ARCHIVE & DELETE', "Book '{$book['title']}' (ID: $book_id) and its {$book['num_copies']} copy(ies) archived and deleted", 'books', $book_id);

    $_SESSION['success'] = "Book and its copies have been archived successfully.";
} else {
    $_SESSION['error'] = "Select a book to delete.";
}

header("location: book.php");
exit();
?>
