<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'includes/session.php';
include 'includes/conn.php';

if(isset($_POST['id'])){
    $id = intval($_POST['id']);

    // 1️⃣ Fetch book info
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if(!$book){
        echo json_encode(['error' => 'Book not found']);
        exit();
    }

    // 2️⃣ Fetch single category
    $category = '';
    $stmt = $conn->prepare("
        SELECT c.name 
        FROM category c
        INNER JOIN book_category_map bcm ON c.id = bcm.category_id
        WHERE bcm.book_id = ? 
        LIMIT 1
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if($row = $res->fetch_assoc()){
        $category = $row['name'];
    }
    $stmt->close();

    // 3️⃣ Fetch multiple subjects
    $subjects = [];
    $stmt = $conn->prepare("
        SELECT s.name 
        FROM subjects s
        INNER JOIN book_subject_map bsm ON s.id = bsm.subject_id
        WHERE bsm.book_id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    while($row = $res->fetch_assoc()){
        $subjects[] = $row['name'];
    }
    $stmt->close();

    // 4️⃣ Fetch copies with borrower info
    $copies = [];
    $stmt = $conn->prepare("
        SELECT bc.id, bc.copy_number, bc.availability, u.name AS borrower
        FROM book_copies bc
        LEFT JOIN users u ON bc.borrower_id = u.id
        WHERE bc.book_id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    while($row = $res->fetch_assoc()){
        $copies[] = [
            'id' => $row['id'],
            'copy_number' => $row['copy_number'],
            'availability' => $row['availability'],
            'borrower' => $row['borrower'] ?? null
        ];
    }
    $stmt->close();

    // 5️⃣ Return JSON for modal
    echo json_encode([
        'isbn' => $book['isbn'],
        'call_no' => $book['call_no'],
        'title' => $book['title'],
        'author' => $book['author'],
        'publisher' => $book['publisher'],
        'publish_date' => $book['publish_date'],
        'section' => $book['section'],
        'category' => $category,
        'subjects' => $subjects,
        'total_copies' => count($copies),
        'copies' => $copies
    ]);
}
?>
