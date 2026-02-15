<?php
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/activity_helper.php';

if(isset($_POST['id'])){
    $id = intval($_POST['id']);

    // Check if faculty has any transactions
    $checkTrans = $conn->prepare("SELECT COUNT(*) as trans_count FROM borrow_transactions WHERE borrower_id = ? AND borrower_type = 'faculty'");
    $checkTrans->bind_param("i", $id);
    $checkTrans->execute();
    $transResult = $checkTrans->get_result();
    $transData = $transResult->fetch_assoc();
    $checkTrans->close();

    if ($transData['trans_count'] > 0) {
        $_SESSION['error'] = 'Cannot delete faculty: This faculty member has ' . $transData['trans_count'] . ' transaction(s) on record. Faculty with transactions cannot be deleted.';
    } else {
        $stmt = $conn->prepare("UPDATE faculty SET archived = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);

        if($stmt->execute()){
            // Get faculty name for logging
            $faculty_query = $conn->prepare("SELECT firstname, lastname, faculty_id FROM faculty WHERE id = ?");
            $faculty_query->bind_param("i", $id);
            $faculty_query->execute();
            $faculty_data = $faculty_query->get_result()->fetch_assoc();
            $faculty_query->close();
            
            // Log the activity
            logActivity($conn, $user['id'], 'ARCHIVE', "Faculty '{$faculty_data['faculty_id']}' ({$faculty_data['firstname']} {$faculty_data['lastname']}) archived", 'faculty', $id);
            $_SESSION['success'] = "Faculty archived successfully.";
        } else {
            $_SESSION['error'] = "Error archiving faculty.";
        }
    }
} else {
    $_SESSION['error'] = "No faculty selected.";
}

header("Location: faculty.php");
exit;
?>
