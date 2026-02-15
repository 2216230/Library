<?php
include 'includes/session.php';
include 'includes/conn.php';

if (isset($_POST['subject_id'])) {
    $subject_id = intval($_POST['subject_id']);
    $selected_books = isset($_POST['book_ids']) ? $_POST['book_ids'] : [];

    // Remove all existing assignments for this subject
    $conn->query("DELETE FROM book_subject_map WHERE subject_id = $subject_id");

    // Add new selected books
    if (!empty($selected_books)) {
        $stmt = $conn->prepare("INSERT INTO book_subject_map (subject_id, book_id) VALUES (?, ?)");
        foreach ($selected_books as $book_id) {
            $book_id = intval($book_id);
            $stmt->bind_param("ii", $subject_id, $book_id);
            $stmt->execute();
        }
        $stmt->close();
    }

    $_SESSION['success'] = "Book assignments updated successfully!";
    header("Location: subject.php");
    exit();
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: subject.php");
    exit();
}
?>
