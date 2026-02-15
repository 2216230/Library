<?php
include 'includes/session.php';
include 'includes/conn.php';

if(isset($_POST['edit_subject'])) {
    $id = intval($_POST['subject_id']);
    $name = trim($_POST['subject_name']);
    $subject_type = isset($_POST['subject_type']) ? $_POST['subject_type'] : 'GE';
    $course_id = isset($_POST['course_id']) && !empty($_POST['course_id']) ? intval($_POST['course_id']) : null;

    // Validate subject type
    $valid_types = ['GE', 'Major', 'Minor', 'Elective', 'Specialization'];
    if (!in_array($subject_type, $valid_types)) {
        $subject_type = 'GE';
    }

    if(empty($name)) {
        $_SESSION['error'] = "Subject name cannot be empty.";
    } else {
        // Check for duplicate name
        $check = $conn->prepare("SELECT id FROM subject WHERE name = ? AND id != ?");
        $check->bind_param("si", $name, $id);
        $check->execute();
        $check->store_result();

        if($check->num_rows > 0) {
            $_SESSION['error'] = "Subject name already exists.";
        } else {
            // Check if the subject table has the new columns
            $has_subject_type = false;
            $col_check = $conn->query("SHOW COLUMNS FROM subject LIKE 'subject_type'");
            if ($col_check && $col_check->num_rows > 0) {
                $has_subject_type = true;
            }
            
            if ($has_subject_type) {
                // Enhanced update with course_id and subject_type
                $stmt = $conn->prepare("UPDATE subject SET name = ?, course_id = ?, subject_type = ? WHERE id = ?");
                $stmt->bind_param("sisi", $name, $course_id, $subject_type, $id);
            } else {
                // Legacy update (only name)
                $stmt = $conn->prepare("UPDATE subject SET name = ? WHERE id = ?");
                $stmt->bind_param("si", $name, $id);
            }

            if($stmt->execute()) {
                $_SESSION['success'] = "Subject '<strong>{$name}</strong>' updated successfully.";
            } else {
                $_SESSION['error'] = "Failed to update subject: " . $conn->error;
            }

            $stmt->close();
        }

        $check->close();
    }
} else {
    $_SESSION['error'] = "Invalid request.";
}

header("location: subjects.php");
exit();
?>
