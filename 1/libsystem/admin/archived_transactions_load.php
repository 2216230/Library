<?php
error_reporting(0);
ini_set('display_errors', 0);

include 'includes/session.php';
include 'includes/conn.php';

// Clear any output that may have been sent
ob_clean();
header('Content-Type: application/json; charset=utf-8');

// Ensure archived_transactions table exists
$create_table_sql = "
    CREATE TABLE IF NOT EXISTS `archived_transactions` (
      `archive_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `id` int(11) NOT NULL,
      `borrower_type` varchar(50) NOT NULL,
      `borrower_id` int(11) NOT NULL,
      `book_id` int(11) NOT NULL,
      `copy_id` int(11),
      `borrow_date` date NOT NULL,
      `due_date` date NOT NULL,
      `return_date` date,
      `status` varchar(50) NOT NULL,
      `academic_year_id` int(11),
      `semester` varchar(50),
      `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `archived_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      KEY `idx_archived_transaction_id` (`id`),
      KEY `idx_archived_borrower` (`borrower_type`, `borrower_id`),
      KEY `idx_archived_book` (`book_id`),
      KEY `idx_archived_status` (`status`),
      KEY `idx_archived_date` (`archived_on`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";

if (!$conn->query($create_table_sql)) {
    error_log('Failed to ensure archived_transactions table: ' . $conn->error);
}

// Get filters and pagination
$academic_year = $_GET['academic_year'] ?? '';
$semester      = $_GET['semester'] ?? '';
$status        = $_GET['status'] ?? '';
$page          = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page      = 10; // Records per page
$offset        = ($page - 1) * $per_page;

// Base SQL
$sql = "
SELECT 
    at.archive_id,
    at.id,
    at.borrower_type,
    at.borrower_id,
    at.book_id,
    at.copy_id,
    at.borrow_date,
    at.due_date,
    at.return_date,
    at.status,
    at.archived_on,

    b.title AS book_title,
    b.call_no,

    CONCAT(st.firstname, ' ', st.lastname) AS student_name,
    CONCAT(fc.firstname, ' ', fc.lastname) AS faculty_name

FROM archived_transactions at

LEFT JOIN books b 
       ON at.book_id = b.id

LEFT JOIN students st 
       ON at.borrower_type = 'student'
      AND at.borrower_id = st.id

LEFT JOIN faculty fc
       ON at.borrower_type = 'faculty'
      AND at.borrower_id = fc.id

WHERE 1 
";

// Filters
if (!empty($academic_year)) {
    $sql .= " AND at.academic_year_id = " . $conn->real_escape_string($academic_year);
}

if (!empty($semester)) {
    $sql .= " AND at.semester = '" . $conn->real_escape_string($semester) . "'";
}

if (!empty($status)) {
    $sql .= " AND at.status = '" . $conn->real_escape_string($status) . "'";
}

// Order by archived date descending
$sql .= " ORDER BY at.archived_on DESC";

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM archived_transactions at WHERE 1";
if (!empty($academic_year)) {
    $count_sql .= " AND at.academic_year_id = " . $conn->real_escape_string($academic_year);
}
if (!empty($semester)) {
    $count_sql .= " AND at.semester = '" . $conn->real_escape_string($semester) . "'";
}
if (!empty($status)) {
    $count_sql .= " AND at.status = '" . $conn->real_escape_string($status) . "'";
}

$count_result = $conn->query($count_sql);
$count_row = $count_result->fetch_assoc();
$total_records = $count_row['total'];
$total_pages = ceil($total_records / $per_page);

// Add pagination to main query
$sql .= " LIMIT $offset, $per_page";

$result = $conn->query($sql);

$transactions = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {

        // Determine borrower name
        $borrower_name = '';

        if ($row['borrower_type'] === 'student') {
            $borrower_name = $row['student_name'];
        } elseif ($row['borrower_type'] === 'faculty') {
            $borrower_name = $row['faculty_name'];
        }

        // Safe date conversion
        $borrow_date = $row['borrow_date'] ? date('Y-m-d', strtotime($row['borrow_date'])) : '';
        $due_date = $row['due_date'] ? date('Y-m-d', strtotime($row['due_date'])) : '';
        $return_date = !empty($row['return_date']) ? date('Y-m-d', strtotime($row['return_date'])) : null;
        $archived_on = $row['archived_on'] ? date('Y-m-d H:i:s', strtotime($row['archived_on'])) : '';

        $transactions[] = [
            'archive_id' => intval($row['archive_id']),
            'id' => intval($row['id']),
            'borrower' => ($borrower_name ?: 'Unknown') . ' (' . ucfirst($row['borrower_type']) . ')',
            'book_title' => $row['book_title'] ?: 'Unknown',
            'call_no' => $row['call_no'] ?: '',
            'borrow_date' => $borrow_date,
            'due_date' => $due_date,
            'return_date' => $return_date,
            'status' => $row['status'] ?: 'unknown',
            'archived_on' => $archived_on
        ];
    }
} else {
    // Log query error for debugging
    error_log('Archived transactions query error: ' . $conn->error);
}

// Return both transactions and pagination info
$response = [
    'transactions' => $transactions,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $total_pages,
        'total_records' => $total_records,
        'per_page' => $per_page
    ]
];

echo json_encode($response);
?>
