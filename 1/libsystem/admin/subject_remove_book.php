<?php
include 'includes/session.php';
include 'includes/conn.php';

if (isset($_POST['book_id'], $_POST['subject_id'])) {
    $book_id = intval($_POST['book_id']);
    $subject_id = intval($_POST['subject_id']);

    // Get details of the selected book
    $sql = "SELECT call_no, title, author, publish_date FROM books WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();

    if ($book) {
        // Delete all identical copies from the mapping table for this subject
        $delete = $conn->prepare("
            DELETE m FROM book_subject_map m
            JOIN books b ON m.book_id = b.id
            WHERE m.subject_id = ?
              AND b.call_no = ?
              AND b.title = ?
              AND b.author = ?
              AND b.publish_date = ?
        ");
        $delete->bind_param("issss", $subject_id, $book['call_no'], $book['title'], $book['author'], $book['publish_date']);
        $delete->execute();

        echo "Removed all copies of '{$book['title']}' from this subject.";
    } else {
        echo "Book not found.";
    }
} else {
    echo "Invalid request.";
}
?>
