<?php
include 'includes/session.php';
include 'includes/conn.php';

if (isset($_POST['book_id'], $_POST['subject_id'])) {
    $book_id = intval($_POST['book_id']);
    $subject_id = intval($_POST['subject_id']);

    // Check if table exists
    $check = $conn->query("SHOW TABLES LIKE 'book_subject_map'");
    if($check->num_rows == 0) {
        echo "ERROR: book_subject_map table does not exist!";
        exit;
    }

    $sql = "INSERT IGNORE INTO book_subject_map (book_id, subject_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    
    if(!$stmt) {
        echo "ERROR: Prepare failed - " . $conn->error;
        exit;
    }
    
    $stmt->bind_param("ii", $book_id, $subject_id);
    
    if($stmt->execute()) {
        if($stmt->affected_rows > 0) {
            echo "Book assigned successfully.";
        } else {
            echo "Book already assigned or no changes made.";
        }
    } else {
        echo "ERROR: Execute failed - " . $stmt->error;
    }
    
    $stmt->close();
} else {
    echo "Invalid data. book_id: " . ($_POST['book_id'] ?? 'missing') . ", subject_id: " . ($_POST['subject_id'] ?? 'missing');
}
?>
