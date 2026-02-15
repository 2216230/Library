<?php
include 'includes/session.php';
include 'includes/conn.php';

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // 1. Fetch the selected book
    $sql = "SELECT * FROM books WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$book) {
        echo json_encode(['error' => 'Invalid book ID']);
        exit();
    }

    // 2. Fetch single category assigned to this book
    $selectedCat = '';
    $cat_sql = "
        SELECT c.id, c.name 
        FROM category c
        INNER JOIN book_category_map bcm ON c.id = bcm.category_id
        WHERE bcm.book_id = ? 
        LIMIT 1
    ";
    $stmt = $conn->prepare($cat_sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $selectedCat = (string)$row['id'];
    }
    $stmt->close();

    // 3. Fetch course subjects assigned to this book (optional multiple)
    $selectedSubjects = [];
    $sub_sql = "SELECT subject_id FROM book_subject_map WHERE book_id = ?";
    $stmt = $conn->prepare($sub_sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $selectedSubjects[] = (string)$row['subject_id'];
    }
    $stmt->close();

    // 4. Return JSON for edit modal
    // 4b. Check deletable: no borrow_transactions (borrowed/overdue) and no copies marked borrowed
    $deletable = true;
    $chk = $conn->prepare("SELECT COUNT(*) as cnt FROM borrow_transactions WHERE book_id = ? AND status IN ('borrowed','overdue')");
    $chk->bind_param("i", $id);
    $chk->execute();
    $chk_res = $chk->get_result()->fetch_assoc();
    $chk->close();
    if ($chk_res && intval($chk_res['cnt']) > 0) $deletable = false;

    $chk2 = $conn->prepare("SELECT COUNT(*) as cnt FROM book_copies WHERE book_id = ? AND LOWER(availability) = 'borrowed'");
    $chk2->bind_param("i", $id);
    $chk2->execute();
    $chk2_res = $chk2->get_result()->fetch_assoc();
    $chk2->close();
    if ($chk2_res && intval($chk2_res['cnt']) > 0) $deletable = false;

    echo json_encode([
        'id' => $book['id'],
        'isbn' => $book['isbn'],
        'call_no' => $book['call_no'],
        'title' => $book['title'],
        'author' => $book['author'],
        'publisher' => $book['publisher'],
        'publish_date' => $book['publish_date'],
        'subject' => $book['subject'] ?? '',
        'category' => $selectedCat,          // single category
        'subjects' => $selectedSubjects,     // multiple course subjects
        'section' => $book['section'] ?? '',
        'num_copies' => $book['num_copies'] ?? 1,
        'deletable' => $deletable
    ]);
}
?>
