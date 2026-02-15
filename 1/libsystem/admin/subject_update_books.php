<?php
include 'includes/session.php';
include 'includes/conn.php';

if(isset($_POST['subject_id'])) {
    $subject_id = intval($_POST['subject_id']);
    $book_ids = isset($_POST['book_ids']) ? $_POST['book_ids'] : [];

    // Remove all old mappings for this subject
    $del_stmt = $conn->prepare("DELETE FROM book_subject_map WHERE subject_id = ?");
    $del_stmt->bind_param("i", $subject_id);
    $del_stmt->execute();
    $del_stmt->close();

    // Insert new mappings
    if(!empty($book_ids)) {
        $ins_stmt = $conn->prepare("INSERT INTO book_subject_map (subject_id, book_id) VALUES (?, ?)");
        foreach($book_ids as $book_id) {
            $ins_stmt->bind_param("ii", $subject_id, $book_id);
            $ins_stmt->execute();
        }
        $ins_stmt->close();
    }

    $_SESSION['success'] = "Books assigned to subject successfully.";
} else {
    $_SESSION['error'] = "Invalid request.";
}

header("location: subjects.php");
exit();
?>
