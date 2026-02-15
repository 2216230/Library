<?php
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/activity_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        $_SESSION['error'] = 'Invalid faculty selected.';
        header('Location: archived_faculty.php'); // change to main faculty page if needed
        exit();
    }

    // Check if faculty exists in archive
    $check = $conn->prepare("SELECT * FROM archived_faculty WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error'] = 'Faculty not found in archive.';
        $check->close();
        header('Location: archived_faculty.php');
        exit();
    }
    $check->close();

    // Delete the faculty permanently
    $stmt = $conn->prepare("DELETE FROM archived_faculty WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Faculty permanently deleted successfully.';
    } else {
        $_SESSION['error'] = 'Error deleting faculty: ' . $stmt->error;
    }

    $stmt->close();
} else {
    $_SESSION['error'] = 'Invalid request method.';
}

$conn->close();
header('Location: archived_faculty.php');
exit();
?>
