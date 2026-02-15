<?php
include 'includes/session.php';
include 'includes/conn.php';

if (isset($_POST['book_id'], $_POST['subject_id'])) {
    $book_id = intval($_POST['book_id']);
    $subject_id = intval($_POST['subject_id']);

    $stmt = $conn->prepare("DELETE FROM book_subject_map WHERE book_id = ? AND subject_id = ?");
    $stmt->bind_param("ii", $book_id, $subject_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
} else {
    echo "invalid";
}
?>
