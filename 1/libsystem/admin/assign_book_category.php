<?php
include 'includes/conn.php';

if (isset($_POST['book_id'], $_POST['category_id'])) {
    $book_id = intval($_POST['book_id']);
    $category_id = intval($_POST['category_id']);

    // check if already assigned
    $check = $conn->prepare("SELECT id FROM book_category_map WHERE book_id = ? AND category_id = ?");
    $check->bind_param("ii", $book_id, $category_id);
    $check->execute();
    $exists = $check->get_result()->num_rows > 0;
    $check->close();

    if (!$exists) {
        $insert = $conn->prepare("INSERT INTO book_category_map (book_id, category_id) VALUES (?, ?)");
        $insert->bind_param("ii", $book_id, $category_id);
        if ($insert->execute()) {
            echo "Book assigned successfully.";
        } else {
            echo "Error assigning book.";
        }
        $insert->close();
    } else {
        echo "Book already assigned.";
    }
}
?>
