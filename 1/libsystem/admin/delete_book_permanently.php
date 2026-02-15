<?php
include 'includes/session.php';
include 'includes/conn.php';

if (isset($_POST['id'])) {
    $archive_id = intval($_POST['id']);

    // Step 1: Check if the book exists in archived_books
    $stmt = $conn->prepare("SELECT * FROM archived_books WHERE archive_id = ?");
    $stmt->bind_param("i", $archive_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
    $stmt->close();

    if ($book) {
        // Step 2: Delete related category mappings
        $delMap = $conn->prepare("DELETE FROM archived_book_category_map WHERE archive_id = ?");
        $delMap->bind_param("i", $archive_id);
        $delMap->execute();
        $delMap->close();

        // Step 3: Delete the archived book
        $delete = $conn->prepare("DELETE FROM archived_books WHERE archive_id = ?");
        $delete->bind_param("i", $archive_id);
        $delete->execute();
        $delete->close();

        $_SESSION['success'] = 'Archived book deleted permanently.';
    } else {
        $_SESSION['error'] = 'Book not found in archive.';
    }
} else {
    $_SESSION['error'] = 'No book selected.';
}

header('location: archived_book.php');
exit();
?>
