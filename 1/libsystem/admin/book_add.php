<?php
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/activity_helper.php';

if (isset($_POST['add'])) {

    // ==========================
    // 1. GET INPUT FIELDS
    // ==========================
    $isbn       = trim($_POST['isbn']);
    $call_no    = trim($_POST['call_no']);
    $title      = trim($_POST['title']);
    $author     = trim($_POST['author']);
    $publisher  = trim($_POST['publisher']);
    $pub_date   = trim($_POST['pub_date']);
    $subject    = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $section    = trim($_POST['section']);  // General, Filipiniana, Reference, etc.
    $copies     = isset($_POST['num_copies']) ? intval($_POST['num_copies']) : 1;

    $category       = isset($_POST['category']) ? intval($_POST['category']) : 0; // single category
    $course_subjects = isset($_POST['course_subject']) ? $_POST['course_subject'] : [];

    // ==========================
    // 2. VALIDATION
    // ==========================
    if (empty($section)) {
        $_SESSION['error'] = 'Please select a circulation type (section).';
        header('location: book.php');
        exit();
    }

    if (empty($category)) {
        $_SESSION['error'] = 'Please select a category.';
        header('location: book.php');
        exit();
    }

    // Validate date format
    if(!empty($pub_date) && !preg_match("/^\d{4}$/", $pub_date)){
        $_SESSION['error'] = "Publish Year must be a 4-digit year.";
        header("location: book.php");
        exit();
    }

    // ✅ CHECK FOR DUPLICATE CALL NUMBER (case insensitive)
    if (!empty($call_no)) {
        $check_call_stmt = $conn->prepare("SELECT id FROM books WHERE LOWER(call_no) = LOWER(?) LIMIT 1");
        $check_call_stmt->bind_param("s", $call_no);
        $check_call_stmt->execute();
        $check_call_stmt->store_result();
        
        if ($check_call_stmt->num_rows > 0) {
            $_SESSION['error'] = "Call Number already exists. Please use a unique call number.";
            $check_call_stmt->close();
            header('location: book.php');
            exit();
        }
        $check_call_stmt->close();
    }



    // ==========================
    // 3. INSERT INTO BOOKS TABLE
    // ==========================
    $stmt = $conn->prepare("
        INSERT INTO books 
        (isbn, call_no, title, author, publisher, publish_date, subject, location, section, type, num_copies) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Library', ?, 'Book', ?)
    ");

            $stmt->bind_param(
                "ssssssssi",
                $isbn,
                $call_no,
                $title,
                $author,
                $publisher,
                $pub_date,
                $subject,
                $section,
                $copies
            );


    if (!$stmt->execute()) {
        $_SESSION['error'] = 'Failed to add book metadata.';
        header('location: book.php');
        exit();
    }

    $book_id = $stmt->insert_id;
    $stmt->close();

    // ==========================
    // 4. INSERT CATEGORY MAPPING (SINGLE)
    // ==========================
    if (!empty($category)) {
        $cat_stmt = $conn->prepare("INSERT INTO book_category_map (book_id, category_id) VALUES (?, ?)");
        $cat_stmt->bind_param("ii", $book_id, $category);
        $cat_stmt->execute();
        $cat_stmt->close();
    }

    // ==========================
    // 5. INSERT SUBJECT MAPPING (OPTIONAL MULTIPLE)
    // ==========================
    if (!empty($course_subjects)) {
        $sub_stmt = $conn->prepare("INSERT INTO book_subject_map (book_id, subject_id) VALUES (?, ?)");
        foreach($course_subjects as $sub_id) {
            $sub_id = intval($sub_id);
            $sub_stmt->bind_param("ii", $book_id, $sub_id);
            $sub_stmt->execute();
        }
        $sub_stmt->close();
    }

    // ==========================
    // 6. INSERT MULTIPLE COPIES IN book_copies
    // ==========================
    $copy_stmt = $conn->prepare("
        INSERT INTO book_copies (book_id, copy_number, availability)
        VALUES (?, ?, 'Available')
    ");

    for ($i = 1; $i <= $copies; $i++) {
        $copy_stmt->bind_param("ii", $book_id, $i);
        $copy_stmt->execute();
    }

    $copy_stmt->close();

    // ==========================
    // 7. LOG ACTIVITY
    // ==========================
    $adminId = $_SESSION['admin'] ?? $_SESSION['superadmin'] ?? null;
    if ($adminId) {
        $action = 'ADD_BOOK';
        $description = "Book added: {$title} | Author: {$author} | Call No: {$call_no} | Copies: {$copies}";
        logActivity($conn, $adminId, $action, $description, 'books', $book_id);
    }

    // ==========================
    // 8. SUCCESS
    // ==========================
    $_SESSION['success'] = "Successfully added $copies copy/copies of the book.";
    header('location: book.php');
    exit();

} else {
   // If there’s an error
$_SESSION['add_error'] = 'Please select a category.';
$_SESSION['old_inputs'] = $_POST; // Save previous inputs
header('location: book.php');
exit();

}
?>
