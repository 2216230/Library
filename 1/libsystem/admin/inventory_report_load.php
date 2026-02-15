<?php
include 'includes/session.php';
include 'includes/conn.php';

if(!isset($_SESSION['admin'])){
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    // Get filter parameters
    $category = isset($_GET['category']) ? $_GET['category'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'title';
    
    // Validate sort parameter
    $allowed_sorts = ['title', 'call_no', 'author', 'section'];
    if (!in_array($sort, $allowed_sorts)) {
        $sort = 'title';
    }

    // Build WHERE clause
    $where = "WHERE b.id IS NOT NULL";
    
    if ($status !== '' && $status !== null) {
        $where .= " AND b.status = '" . $conn->real_escape_string($status) . "'";
    } else {
        // Default to active books
        $where .= " AND b.status = 1";
    }
    
    if ($category !== '' && $category !== null) {
        $where .= " AND bc.category_id = '" . $conn->real_escape_string($category) . "'";
    }

    // Get books with validation data
    $sql = "SELECT DISTINCT
            b.id,
            b.title,
            b.author,
            b.call_no,
            b.section,
            (SELECT COUNT(*) FROM book_copies WHERE book_id = b.id) as total_copies
            FROM books b
            LEFT JOIN book_category_map bc ON b.id = bc.book_id
            $where
            ORDER BY b." . $conn->real_escape_string($sort) . " ASC";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception('Query error: ' . $conn->error);
    }

    $books = [];
    $total_copies = 0;

    while ($row = $result->fetch_assoc()) {
        $book_id = $row['id'];
        $total_copies += $row['total_copies'];

        // Get validation records for this book
        $validation_sql = "SELECT actual_count, validation_date, notes 
                          FROM inventory_validations 
                          WHERE book_id = '$book_id'
                          ORDER BY validation_date DESC
                          LIMIT 1";
        $validation_result = $conn->query($validation_sql);
        
        $row['validation_records'] = [];
        while ($val = $validation_result->fetch_assoc()) {
            $row['validation_records'][] = $val;
        }

        $books[] = $row;
    }

    // Calculate stats
    $stats = [
        'total_books' => count($books),
        'total_copies' => $total_copies,
        'books_validated' => 0,
        'discrepancies' => 0
    ];

    // Count validated books and discrepancies
    foreach ($books as $book) {
        if (!empty($book['validation_records'])) {
            $stats['books_validated']++;
            $actual = $book['validation_records'][0]['actual_count'];
            if ($actual != $book['num_copies']) {
                $stats['discrepancies']++;
            }
        }
    }

    echo json_encode([
        'success' => true,
        'books' => $books,
        'stats' => $stats
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
