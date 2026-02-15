<?php
include 'includes/session.php';
include 'includes/conn.php';

if(isset($_POST['confirm_remove_subject'])){
    $subject_id = intval($_POST['subject_id']);

    // 1️⃣ Fetch subject details
    $stmt = $conn->prepare("SELECT name FROM subject WHERE id = ?");
    $stmt->bind_param("i", $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $subject = $result->fetch_assoc();
    $stmt->close();

    if($subject){
        // 2️⃣ Insert into archived_subject table
        $stmt = $conn->prepare("INSERT INTO archived_subject (subject_id, name, date_archived) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $subject_id, $subject['name']);
        if($stmt->execute()){
            $stmt->close();

            // 3️⃣ Delete from main subject table
            $stmt = $conn->prepare("DELETE FROM subject WHERE id = ?");
            $stmt->bind_param("i", $subject_id);
            if($stmt->execute()){
                $_SESSION['success'] = "Subject archived successfully.";
            } else {
                $_SESSION['error'] = "Failed to delete subject after archiving.";
            }
            $stmt->close();
        } else {
            $_SESSION['error'] = "Failed to archive subject.";
        }
    } else {
        $_SESSION['error'] = "Subject not found.";
    }
} else {
    $_SESSION['error'] = "Invalid request.";
}

header('location: subjects.php');
exit();
?>
