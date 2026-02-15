<?php
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/activity_helper.php';

if(isset($_POST['id'])){
    $archive_id = intval($_POST['id']);

    // 1. Get archived book
    $stmt = $conn->prepare("SELECT * FROM archived_books WHERE archive_id = ?");
    $stmt->bind_param("i", $archive_id);
    $stmt->execute();
    $archived = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if($archived){
        // 2. Restore book
        $restore_stmt = $conn->prepare("
            INSERT INTO books 
            (isbn, call_no, location, title, subject, author, publisher, publish_date, num_copies,section)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,?)
        ");
        $restore_stmt->bind_param(
            "ssssssssis",
            $archived['isbn'],
            $archived['call_no'],
            $archived['location'],
            $archived['title'],
            $archived['subject'],
            $archived['author'],
            $archived['publisher'],
            $archived['publish_date'],
            $archived['num_copies'],
            $archived['section'],
        );
        $restore_stmt->execute();
        $new_book_id = $restore_stmt->insert_id;
        $restore_stmt->close();

        // 3. Restore categories
        $cat_res = $conn->query("SELECT category_id FROM archived_book_category_map WHERE archive_id = $archive_id");
        while($cat = $cat_res->fetch_assoc()){
            $map_stmt = $conn->prepare("INSERT INTO book_category_map (book_id, category_id) VALUES (?, ?)");
            $map_stmt->bind_param("ii", $new_book_id, $cat['category_id']);
            $map_stmt->execute();
            $map_stmt->close();
        }

        // 4. Restore copies
        $copy_res = $conn->query("SELECT copy_number, availability FROM archived_book_copies WHERE archive_id = $archive_id");
        while($copy = $copy_res->fetch_assoc()){
            $copy_stmt = $conn->prepare("
                INSERT INTO book_copies (book_id, copy_number, availability)
                VALUES (?, ?, ?)
            ");
            $copy_stmt->bind_param(
                "iss",
                $new_book_id,
                $copy['copy_number'],
                $copy['availability']
            );
            $copy_stmt->execute();
            $copy_stmt->close();
        }

        // 5. Delete from archive
        $conn->query("DELETE FROM archived_books WHERE archive_id = $archive_id");
        $conn->query("DELETE FROM archived_book_category_map WHERE archive_id = $archive_id");
        $conn->query("DELETE FROM archived_book_copies WHERE archive_id = $archive_id");

        // Log activity
        logActivity($conn, $user['id'], 'RESTORE', "Book '{$archived['title']}' (ID: {$archived['book_id']}) restored from archive", 'books', $new_book_id);
        $_SESSION['success'] = "Book restored successfully.";
    } else {
        $_SESSION['error'] = "Archived book not found.";
    }
} else {
    $_SESSION['error'] = "Select a book to restore.";
}

header("location: archived_book.php");
exit();
?>
