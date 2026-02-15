<?php
include 'includes/session.php';
include 'includes/conn.php';

if (!isset($_POST['id'])) {
    echo json_encode(['error' => 'Book ID is required']);
    exit();
}

$id = intval($_POST['id']);

// 1. Fetch the book info
$stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$book) {
    echo json_encode(['error' => 'Invalid book ID']);
    exit();
}

// 2. Fetch single category
$selectedCat = '';
$category_name = '';
$cat_stmt = $conn->prepare("
    SELECT c.id, c.name 
    FROM category c
    INNER JOIN book_category_map bcm ON c.id = bcm.category_id
    WHERE bcm.book_id = ? 
    LIMIT 1
");
$cat_stmt->bind_param("i", $id);
$cat_stmt->execute();
$cat_res = $cat_stmt->get_result();
if ($row = $cat_res->fetch_assoc()) {
    $selectedCat = (string)$row['id'];
    $category_name = $row['name'];
}
$cat_stmt->close();

// 3. Fetch subjects
$selectedSubjects = [];
$subjects_names = [];
$sub_stmt = $conn->prepare("SELECT s.id, s.name 
    FROM subject s
    INNER JOIN book_subject_map bsm ON s.id = bsm.subject_id
    WHERE bsm.book_id = ?");
$sub_stmt->bind_param("i", $id);
$sub_stmt->execute();
$sub_res = $sub_stmt->get_result();
while ($row = $sub_res->fetch_assoc()) {
    $selectedSubjects[] = (string)$row['id'];
    $subjects_names[] = $row['name'];
}
$sub_stmt->close();

// 4. Fetch all copies
$copies = [];
$copy_stmt = $conn->prepare("SELECT id, copy_number, availability FROM book_copies WHERE book_id = ? ORDER BY copy_number ASC");
$copy_stmt->bind_param("i", $id);
$copy_stmt->execute();
$copy_res = $copy_stmt->get_result();
while ($row = $copy_res->fetch_assoc()) {
    $copies[] = $row;
}
$copy_stmt->close();

// 4b. Count borrowed and overdue copies for notification
$borrowed_count = 0;
$overdue_count = 0;
$count_stmt = $conn->prepare("SELECT COUNT(*) AS cnt, SUM(CASE WHEN DATE(due_date) < CURDATE() THEN 1 ELSE 0 END) AS overdue_cnt FROM borrow_transactions WHERE book_id = ? AND status IN ('borrowed','overdue')");
$count_stmt->bind_param("i", $id);
$count_stmt->execute();
$cnt_res = $count_stmt->get_result()->fetch_assoc();
if ($cnt_res) {
    $borrowed_count = intval($cnt_res['cnt']);
    $overdue_count = intval($cnt_res['overdue_cnt']);
}
$count_stmt->close();

// 5. Return JSON
echo json_encode([
    'id' => $book['id'],
    'isbn' => $book['isbn'],
    'call_no' => $book['call_no'],
    'title' => $book['title'],
    'author' => $book['author'],
    'publisher' => $book['publisher'],
    'publish_date' => $book['publish_date'],
    'subject' => $book['subject'] ?? '',
    'category' => $selectedCat,
    'category_name' => $category_name,
    'subjects' => $selectedSubjects,
    'subjects_names' => $subjects_names,
    'section' => $book['section'] ?? '',
    'num_copies' => $book['num_copies'] ?? 1,
    'copies' => $copies
    , 'borrowed_count' => $borrowed_count
    , 'overdue_count' => $overdue_count
]);
