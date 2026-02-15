<?php
// Use direct session check for AJAX requests to avoid redirects
include 'includes/conn.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if ((!isset($_SESSION['admin']) || trim($_SESSION['admin']) == '') && (!isset($_SESSION['superadmin']) || trim($_SESSION['superadmin']) == '')) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
header('Content-Type: application/json');

// ====================================
// GET FILTER PARAMETERS AND PAGINATION
// ====================================
$academic_year = isset($_GET['academic_year']) && !empty($_GET['academic_year']) ? $_GET['academic_year'] : null;
$semester      = isset($_GET['semester']) && !empty($_GET['semester']) ? $_GET['semester'] : null;
$month         = isset($_GET['month']) && !empty($_GET['month']) ? $_GET['month'] : null;
$borrower_type = isset($_GET['borrower_type']) && !empty($_GET['borrower_type']) ? $_GET['borrower_type'] : null;

$search        = isset($_GET['search']) && !empty($_GET['search']) ? trim($_GET['search']) : null;
$status        = isset($_GET['status']) && !empty($_GET['status']) ? $_GET['status'] : null;
$filter        = isset($_GET['filter']) && !empty($_GET['filter']) ? $_GET['filter'] : null;
$page          = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page      = 10; // Records per page
$offset        = ($page - 1) * $per_page;

// ====================================
// BUILD DYNAMIC WHERE CLAUSE
// ====================================
$where_conditions = [];

// Handle dashboard filters (from home.php stat cards)
if ($filter === 'active') {
    $where_conditions[] = "bt.status IN ('borrowed', 'overdue')";
} elseif ($filter === 'overdue') {
    $where_conditions[] = "DATE(bt.due_date) < CURDATE() AND bt.status IN ('borrowed', 'overdue')";
} elseif ($filter === 'all') {
    // No additional filter - show all
}

if ($academic_year) {
    $where_conditions[] = "bt.academic_year_id = '" . $conn->real_escape_string($academic_year) . "'";
}

if ($semester) {
    $where_conditions[] = "bt.semester = '" . $conn->real_escape_string($semester) . "'";
}

if ($month) {
    $month_start = $month . '-01';
    $month_end = date("Y-m-t", strtotime($month_start));
    $where_conditions[] = "DATE(bt.borrow_date) BETWEEN '" . $conn->real_escape_string($month_start) . "' AND '" . $conn->real_escape_string($month_end) . "'";
}

if ($borrower_type) {
    $where_conditions[] = "bt.borrower_type = '" . $conn->real_escape_string($borrower_type) . "'";
}



if ($status) {
    if ($status === 'overdue') {
        // Special handling for overdue - check due_date is past and status is borrowed
        $where_conditions[] = "DATE(bt.due_date) < CURDATE() AND bt.status = 'borrowed'";
    } else {
        $where_conditions[] = "bt.status = '" . $conn->real_escape_string($status) . "'";
    }
}

if ($search) {
    $search_safe = $conn->real_escape_string(strtoupper($search));
    // Search across all relevant fields (case-insensitive using UPPER)
    $search_conditions = array(
        "UPPER(b.call_no) LIKE '%$search_safe%'",
        "UPPER(b.title) LIKE '%$search_safe%'",
        "UPPER(b.author) LIKE '%$search_safe%'",
        "UPPER(b.isbn) LIKE '%$search_safe%'",
        "UPPER(st.id) LIKE '%$search_safe%'",
        "UPPER(st.firstname) LIKE '%$search_safe%'",
        "UPPER(st.lastname) LIKE '%$search_safe%'",
        "UPPER(fc.id) LIKE '%$search_safe%'",
        "UPPER(fc.firstname) LIKE '%$search_safe%'",
        "UPPER(fc.lastname) LIKE '%$search_safe%'",
        "UPPER(bc.copy_number) LIKE '%$search_safe%'"
    );
    $where_conditions[] = "(" . implode(" OR ", $search_conditions) . ")";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// ====================================
// BUILD AND EXECUTE QUERY
// ====================================
$sql = "
SELECT 
    bt.id,
    bt.borrower_type,
    bt.borrower_id,
    b.title AS book_title,
    b.call_no,
    bc.copy_number AS copy_no,
    bt.borrow_date,
    bt.due_date,
    bt.status,
    CONCAT(st.firstname, ' ', st.lastname) AS student_name,
    CONCAT(fc.firstname, ' ', fc.lastname) AS faculty_name
FROM borrow_transactions bt
LEFT JOIN books b ON bt.book_id = b.id
LEFT JOIN book_copies bc ON bt.copy_id = bc.id
LEFT JOIN students st ON bt.borrower_type = 'student' AND bt.borrower_id = st.id
LEFT JOIN faculty fc ON bt.borrower_type = 'faculty' AND bt.borrower_id = fc.id
$where_clause
ORDER BY bt.borrow_date DESC
";

// Get total count for pagination
$count_sql = "
SELECT COUNT(DISTINCT bt.id) as total 
FROM borrow_transactions bt
LEFT JOIN books b ON bt.book_id = b.id
LEFT JOIN book_copies bc ON bt.copy_id = bc.id
LEFT JOIN students st ON bt.borrower_type = 'student' AND bt.borrower_id = st.id
LEFT JOIN faculty fc ON bt.borrower_type = 'faculty' AND bt.borrower_id = fc.id
$where_clause
";
$count_result = $conn->query($count_sql);
if (!$count_result) {
    die(json_encode(['error' => 'Count query failed: ' . $conn->error]));
}
$count_row = $count_result->fetch_assoc();
$total_records = $count_row['total'];
$total_pages = ceil($total_records / $per_page);

// Add pagination to main query
$sql .= " LIMIT $offset, $per_page";

$result = $conn->query($sql);
if (!$result) {
    die(json_encode(['error' => 'Query failed: ' . $conn->error]));
}

$transactions = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Determine borrower name
        $borrower_name = ($row['borrower_type'] === 'student') ? $row['student_name'] : $row['faculty_name'];
        
        // Check if borrowed item is overdue (compare dates only, without time)
        $due_date_only = date('Y-m-d', strtotime($row['due_date']));
        $today = date('Y-m-d');
        $is_overdue = ($row['status'] === 'borrowed' && $due_date_only < $today);
        
        // Determine display status
        $display_status = $is_overdue ? 'overdue' : $row['status'];
        
        // Get status badge color
        $status_lower = strtolower($display_status);
        $badge_colors = [
            'borrowed' => 'info',
            'returned' => 'success',
            'lost' => 'default',
            'damaged' => 'warning',
            'repair' => 'primary',
            'overdue' => 'danger'
        ];
        $badge = $badge_colors[$status_lower] ?? 'default';

        // Add transaction to array
        $transactions[] = [
            'id' => $row['id'],
            'borrower' => ($borrower_name ?: 'Unknown'),
            'borrower_type' => $row['borrower_type'],
            'book_title' => $row['book_title'] ?: 'Unknown',
            'call_no' => $row['call_no'] ?: '',
            'copy_no' => $row['copy_no'] ?: '',
            'borrow_date' => $row['borrow_date'],
            'due_date' => $row['due_date'],
            'status' => $display_status,
            'status_badge' => $badge
        ];
    }
}

// Return response with pagination info
$response = [
    'transactions' => $transactions,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $total_pages,
        'total_records' => $total_records,
        'per_page' => $per_page
    ],
    'debug' => [
        'search_param' => $search,
        'borrower_type_param' => $borrower_type,
        'where_clause' => $where_clause
    ]
];

echo json_encode($response);
?>
