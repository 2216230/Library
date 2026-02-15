<?php
include 'includes/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code']);
    $title = trim($_POST['title']);
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    // Validate fields
    if (empty($code) || empty($title)) {
        $_SESSION['error'] = 'Please fill out all fields before saving.';
        header('Location: course.php');
        exit();
    }

    // Check if course already exists
    $check = $conn->prepare("SELECT id FROM course WHERE code = ? AND id != ?");
    $check->bind_param("si", $code, $id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $_SESSION['error'] = 'A course with this code already exists.';
        $check->close();
        header('Location: course.php');
        exit();
    }
    $check->close();

    if ($id > 0) {
        // Update existing course
        $stmt = $conn->prepare("UPDATE course SET code = ?, title = ? WHERE id = ?");
        $stmt->bind_param("ssi", $code, $title, $id);
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Course updated successfully.';
        } else {
            $_SESSION['error'] = 'Error updating course: ' . $conn->error;
        }
        $stmt->close();
    } else {
        // Insert new course
        $stmt = $conn->prepare("INSERT INTO course (code, title) VALUES (?, ?)");
        $stmt->bind_param("ss", $code, $title);
        if ($stmt->execute()) {
            $_SESSION['success'] = 'New course added successfully.';
        } else {
            $_SESSION['error'] = 'Error adding course: ' . $conn->error;
        }
        $stmt->close();
    }
    
    header('Location: course.php');
    exit();
} else {
    $_SESSION['error'] = 'Invalid request method.';
    header('Location: course.php');
    exit();
}
?>
