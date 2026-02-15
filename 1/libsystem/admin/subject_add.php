<?php
include 'includes/session.php';
include 'includes/conn.php';

if (isset($_POST['add_subject'])) {
    $name = trim($_POST['subject_name']);
    $subject_type = isset($_POST['subject_type']) ? $_POST['subject_type'] : 'GE';
    $course_id = isset($_POST['course_id']) && !empty($_POST['course_id']) ? intval($_POST['course_id']) : null;

    // Validate subject type
    $valid_types = ['GE', 'Major', 'Minor', 'Elective', 'Specialization'];
    if (!in_array($subject_type, $valid_types)) {
        $subject_type = 'GE';
    }

    if (empty($name)) {
        $_SESSION['error'] = "Subject name cannot be empty.";
    } else {
        // ✅ Check if subject already exists (case insensitive)
        $check_stmt = $conn->prepare("SELECT id FROM subject WHERE LOWER(name) = LOWER(?)");
        $check_stmt->bind_param("s", $name);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $_SESSION['error'] = "Subject already exists.";
        } else {
            // Check if the subject table has the new columns
            $has_subject_type = false;
            $col_check = $conn->query("SHOW COLUMNS FROM subject LIKE 'subject_type'");
            if ($col_check && $col_check->num_rows > 0) {
                $has_subject_type = true;
            }
            
            if ($has_subject_type) {
                // Enhanced insert with course_id and subject_type
                $stmt = $conn->prepare("INSERT INTO subject (name, course_id, subject_type) VALUES (?, ?, ?)");
                $stmt->bind_param("sis", $name, $course_id, $subject_type);
            } else {
                // Legacy insert (only name)
                $stmt = $conn->prepare("INSERT INTO subject (name) VALUES (?)");
                $stmt->bind_param("s", $name);
            }
            
            if ($stmt->execute()) {
                $type_labels = [
                    'GE' => 'General Education',
                    'Major' => 'Major',
                    'Minor' => 'Minor',
                    'Elective' => 'Elective',
                    'Specialization' => 'Specialization'
                ];
                $type_label = isset($type_labels[$subject_type]) ? $type_labels[$subject_type] : $subject_type;
                $_SESSION['success'] = "New {$type_label} subject '<strong>{$name}</strong>' added successfully.";
            } else {
                $_SESSION['error'] = "Failed to add subject: " . $conn->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
} else {
    $_SESSION['error'] = "Invalid request.";
}

header('location: subjects.php');
exit();
?>
