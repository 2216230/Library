<?php
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/activity_helper.php';

if(isset($_POST['edit'])) {

    $book_id = intval($_POST['book_id']);
    $isbn       = trim($_POST['isbn']);
    $call_no    = trim($_POST['call_no']);
    $title      = trim($_POST['title']);
    $author     = trim($_POST['author']);
    $publisher  = trim($_POST['publisher']);
    $publish_date   = trim($_POST['publish_date']);
    $subject    = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $section    = trim($_POST['section']);  
    $num_copies = isset($_POST['num_copies']) ? intval($_POST['num_copies']) : 1;
    $category   = isset($_POST['category']) ? intval($_POST['category']) : 0; 
    $course_subjects = isset($_POST['course_subject']) ? $_POST['course_subject'] : [];

    // Validation
    if(empty($section)){
        $_SESSION['error'] = "Please select a section.";
        header("location: book.php");
        exit();
    }
    if(empty($category)){
        $_SESSION['error'] = "Please select a category.";
        header("location: book.php");
        exit();
    }
    if(!empty($publish_date) && !preg_match("/^\d{4}$/", $publish_date)){
        $_SESSION['error'] = "Publish Year must be a 4-digit year.";
        header("location: book.php");
        exit();
    }

    // ✅ CHECK FOR DUPLICATE CALL NUMBER (excluding current book, case insensitive)
    if (!empty($call_no)) {
        $check_call_stmt = $conn->prepare("SELECT id FROM books WHERE LOWER(call_no) = LOWER(?) AND id != ? LIMIT 1");
        $check_call_stmt->bind_param("si", $call_no, $book_id);
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



    // ================================
    // 1. Update book metadata
    // ================================
    $stmt = $conn->prepare("
        UPDATE books SET 
        isbn=?, call_no=?, title=?, author=?, publisher=?, publish_date=?, subject=?, section=?, num_copies=?
        WHERE id=?
    ");
    $stmt->bind_param(
        "ssssssssii",
        $isbn,
        $call_no,
        $title,
        $author,
        $publisher,
        $publish_date,
        $subject,
        $section,
        $num_copies,
        $book_id
    );
    $stmt->execute();
    $stmt->close();

    // ================================
    // 2. Update category mapping (single)
    // ================================
    $conn->query("DELETE FROM book_category_map WHERE book_id = $book_id");
    if(!empty($category)){
        $cat_stmt = $conn->prepare("INSERT INTO book_category_map (book_id, category_id) VALUES (?, ?)");
        $cat_stmt->bind_param("ii", $book_id, $category);
        $cat_stmt->execute();
        $cat_stmt->close();
    }

    // ================================
    // 3. Update subject mapping (optional multiple)
    // ================================
    $conn->query("DELETE FROM book_subject_map WHERE book_id = $book_id");
    if(!empty($course_subjects)){
        $sub_stmt = $conn->prepare("INSERT INTO book_subject_map (book_id, subject_id) VALUES (?, ?)");
        foreach($course_subjects as $sub_id){
            $sub_id = intval($sub_id);
            $sub_stmt->bind_param("ii", $book_id, $sub_id);
            $sub_stmt->execute();
        }
        $sub_stmt->close();
    }

    // ================================
    // 4. Update copies
    // ================================
    $copy_res = $conn->query("SELECT copy_number, availability FROM book_copies WHERE book_id = $book_id ORDER BY copy_number ASC");
    $existing_copies = [];
    while($row = $copy_res->fetch_assoc()){
        $existing_copies[] = $row;
    }
    $current = count($existing_copies);

    if($num_copies > $current){
        // Add extra copies
        for($i=$current+1; $i<=$num_copies; $i++){
            $copy_stmt = $conn->prepare("INSERT INTO book_copies (book_id, copy_number, availability) VALUES (?, ?, 'Available')");
            $copy_stmt->bind_param("ii", $book_id, $i);
            $copy_stmt->execute();
            $copy_stmt->close();
        }
    } elseif($num_copies < $current){
        // Remove excess copies (only available ones)
        $delete_count = $current - $num_copies;
        $del_q = $conn->prepare("
            DELETE FROM book_copies 
            WHERE book_id=? AND availability='Available' 
            ORDER BY copy_number DESC LIMIT ?
        ");
        $del_q->bind_param("ii", $book_id, $delete_count);
        $del_q->execute();
        $del_q->close();
    }

    // Log activity
    $adminId = $_SESSION['admin'] ?? $_SESSION['superadmin'] ?? null;
    if ($adminId) {
        $action = 'EDIT_BOOK';
        $description = "Book updated: {$title} | Author: {$author} | Call No: {$call_no} | Copies: {$num_copies}";
        logActivity($conn, $adminId, $action, $description, 'books', $book_id);
    }

    $_SESSION['success'] = "Book updated successfully.";
    header("location: book.php");
    exit();

} else {
    $_SESSION['error'] = "Fill up the edit form first.";
    header("location: book.php");
    exit();
}
?>
