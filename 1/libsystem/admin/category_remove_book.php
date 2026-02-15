<?php
include 'includes/conn.php';

if (isset($_POST['book_id'], $_POST['category_id'])) {
    $book_id = intval($_POST['book_id']);
    $category_id = intval($_POST['category_id']);

    $del = $conn->prepare("DELETE FROM book_category_map WHERE book_id = ? AND category_id = ?");
    $del->bind_param("ii", $book_id, $category_id);
    if ($del->execute()) {
        echo "Book removed from category.";
    } else {
        echo "Error removing book.";
    }
    $del->close();
}
?>
