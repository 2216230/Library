<?php
session_start();

// Check if admin is logged in
if(!isset($_SESSION['admin'])){
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include 'includes/conn.php';

// Get DataTables parameters
$draw = intval($_POST['draw']);
$start = intval($_POST['start']);
$length = intval($_POST['length']);
$search = $_POST['search']['value'];
$orderColumnIndex = intval($_POST['order'][0]['column']);
$orderDir = $_POST['order'][0]['dir'];

// Get filters
$catid = isset($_POST['category']) ? intval($_POST['category']) : 0;
$subjid = isset($_POST['subject']) ? intval($_POST['subject']) : 0;

// Build WHERE clause
$where = "WHERE 1=1";
if ($catid > 0) {
    $where .= " AND EXISTS (SELECT 1 FROM book_category_map WHERE book_category_map.book_id = books.id AND book_category_map.category_id = $catid)";
}
if ($subjid > 0) {
    $where .= " AND EXISTS (SELECT 1 FROM book_subject_map WHERE book_subject_map.book_id = books.id AND book_subject_map.subject_id = $subjid)";
}

// Search filter
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $where .= " AND (books.title LIKE '%$search%' 
                OR books.author LIKE '%$search%' 
                OR books.isbn LIKE '%$search%' 
                OR books.call_no LIKE '%$search%' 
                OR books.publisher LIKE '%$search%')";
}

// Column mapping for ordering
$columns = ['category_list', 'book_subject', 'subject_list', 'isbn', 'call_no', 'title', 'author', 'publisher', 'publish_date', 'date_created', 'copy_number', 'num_copies', 'borrow_status'];
$orderColumn = $columns[$orderColumnIndex] ?? 'books.id';
if ($orderColumn == 'category_list' || $orderColumn == 'subject_list') {
    $orderColumn = 'books.id'; // Default for aggregated columns
}

// Get total records (without filtering)
$totalQuery = $conn->query("SELECT COUNT(DISTINCT books.id) as total FROM books");
$totalRecords = $totalQuery->fetch_assoc()['total'];

// Get filtered records count
$filteredQuery = $conn->query("SELECT COUNT(DISTINCT books.id) as total FROM books $where");
$filteredRecords = $filteredQuery->fetch_assoc()['total'];

// Fetch data
$sql = "
    SELECT 
        books.id AS bookid,
        books.isbn,
        books.call_no,
        books.title,
        books.author,
        books.publisher,
        books.publish_date,
        books.date_created,
        books.copy_number,
        books.num_copies,
        books.subject AS book_subject,
        GROUP_CONCAT(DISTINCT subject.name ORDER BY subject.name SEPARATOR ', ') AS subject_list,
        GROUP_CONCAT(DISTINCT category.name ORDER BY category.name SEPARATOR ', ') AS category_list,
        bt.status AS borrow_status,
        bt.due_date AS borrow_due_date
    FROM books
    LEFT JOIN book_category_map bcm ON books.id = bcm.book_id
    LEFT JOIN category ON bcm.category_id = category.id
    LEFT JOIN book_subject_map bsm ON books.id = bsm.book_id
    LEFT JOIN subject ON bsm.subject_id = subject.id
    LEFT JOIN (
        SELECT * 
        FROM borrow_transactions
        WHERE status = 'borrowed'
    ) bt ON books.id = bt.book_id
    $where
    GROUP BY books.id
    ORDER BY $orderColumn $orderDir
    LIMIT $start, $length
";

$query = $conn->query($sql);
$data = [];
$today = date('Y-m-d');

while ($row = $query->fetch_assoc()) {
    // Determine book status
    if (!$row['borrow_status']) {
        $status = '<span class="label" style="background: linear-gradient(135deg, #10b981 0%, #0f766e 100%); color: white; padding: 5px 12px; border-radius: 15px; font-weight: 600;">Available</span>';
    } elseif ($row['borrow_status'] == 'borrowed' && $today > $row['borrow_due_date']) {
        $status = '<span class="label" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; padding: 5px 12px; border-radius: 15px; font-weight: 600;">Overdue</span>';
    } elseif ($row['borrow_status'] == 'borrowed') {
        $status = '<span class="label" style="background: linear-gradient(135deg, #F0D411 0%, #FFA500 100%); color: white; padding: 5px 12px; border-radius: 15px; font-weight: 600;">Borrowed</span>';
    } else {
        $status = '<span class="label" style="background: linear-gradient(135deg, #10b981 0%, #0f766e 100%); color: white; padding: 5px 12px; border-radius: 15px; font-weight: 600;">Available</span>';
    }

    $data[] = [
        'category' => '<small>' . htmlspecialchars($row['category_list'] ?: 'Uncategorized') . '</small>',
        'subject' => '<small>' . htmlspecialchars($row['book_subject'] ?: '-') . '</small>',
        'course_subject' => '<small>' . htmlspecialchars($row['subject_list'] ?: 'Unassigned') . '</small>',
        'isbn' => '<code>' . htmlspecialchars($row['isbn']) . '</code>',
        'call_no' => htmlspecialchars($row['call_no']),
        'title' => '<span style="font-weight: 500; color: #064e3b;">' . htmlspecialchars($row['title']) . '</span>',
        'author' => htmlspecialchars($row['author']),
        'publisher' => htmlspecialchars($row['publisher']),
        'publish_date' => '<small>' . htmlspecialchars($row['publish_date']) . '</small>',
        'date_created' => '<small>' . htmlspecialchars(date('F d, Y', strtotime($row['date_created']))) . '</small>',
        'copy_number' => '<div style="text-align: center; font-weight: 600;">' . htmlspecialchars($row['copy_number']) . '</div>',
        'num_copies' => '<div style="text-align: center; font-weight: 600;">' . htmlspecialchars($row['num_copies']) . '</div>',
        'status' => '<div style="text-align: center;">' . $status . '</div>',
        'tools' => '
            <div class="btn-group btn-group-sm" role="group">
                    <button class="btn btn-warning edit" data-id="' . $row['bookid'] . '" title="Edit" style="border-radius: 5px; margin-right: 5px; background: linear-gradient(135deg, #F0D411 0%, #FFA500 100%); border: none; color: #064e3b;">
                    <i class="fa fa-edit"></i> Edit
                </button>
                <button class="btn btn-danger delete" data-id="' . $row['bookid'] . '" title="Delete" style="border-radius: 5px; background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); border: none;">
                    <i class="fa fa-trash"></i> Delete
                </button>
            </div>
        '
    ];
}

// Return JSON response
echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $totalRecords,
    'recordsFiltered' => $filteredRecords,
    'data' => $data
]);
?>
