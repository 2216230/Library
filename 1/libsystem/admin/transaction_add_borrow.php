<?php
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/activity_helper.php';
header('Content-Type: application/json; charset=utf-8');

$borrower_id   = $_POST['borrower_id'] ?? null;
$borrower_type = $_POST['borrower_type'] ?? null;
$book_id       = $_POST['book_id'] ?? null;
$copy_id       = $_POST['copy_id'] ?? null;
$borrow_date   = $_POST['borrow_date'] ?? null;
$days          = $_POST['days'] ?? null;
$due_date      = $_POST['due_date'] ?? null;
$academic_year = $_POST['academic_year'] ?? null;
$semester      = $_POST['semester'] ?? null;

// ------------- VALIDATE REQUIRED FIELDS - WITH SPECIFIC MESSAGES ----------------
if(!$borrower_id) {
    echo json_encode(['status'=>'error','message'=>'Missing: Borrower ID']);
    exit;
}
if(!$borrower_type) {
    echo json_encode(['status'=>'error','message'=>'Missing: Borrower Type']);
    exit;
}
if(!$book_id) {
    echo json_encode(['status'=>'error','message'=>'Missing: Book ID']);
    exit;
}
if(!$copy_id) {
    echo json_encode(['status'=>'error','message'=>'Missing: Copy ID']);
    exit;
}
if(!$borrow_date) {
    echo json_encode(['status'=>'error','message'=>'Missing: Borrow Date']);
    exit;
}
if($days === null || $days === '') {
    echo json_encode(['status'=>'error','message'=>'Missing: Days/Duration']);
    exit;
}
if(!$due_date) {
    echo json_encode(['status'=>'error','message'=>'Missing: Due Date']);
    exit;
}

// ------------- SET DEFAULT SEMESTER IF MISSING ----------------
if(empty($semester)){
    // Try to get from settings
    $settings_result = $conn->query("SELECT active_semester FROM settings WHERE id=1 LIMIT 1");
    if($settings_result && $settings_result->num_rows > 0){
        $settings = $settings_result->fetch_assoc();
        $semester = $settings['active_semester'] ?? '1st';
    } else {
        $semester = '1st'; // Default to 1st semester
    }
}

// ------------- SET DEFAULT ACADEMIC YEAR IF MISSING ----------------
if(empty($academic_year)){
    $settings_result = $conn->query("SELECT active_academic_year FROM settings WHERE id=1 LIMIT 1");
    if($settings_result && $settings_result->num_rows > 0){
        $settings = $settings_result->fetch_assoc();
        $academic_year = $settings['active_academic_year'] ?? 1;
    } else {
        $academic_year = 1; // Default to first academic year
    }
}

// Validate academic_year exists
$stmt = $conn->prepare("SELECT id FROM academic_years WHERE id=? LIMIT 1");
$stmt->bind_param("i", $academic_year);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows === 0){
    echo json_encode(['status'=>'error','message'=>'Invalid academic year.']);
    exit;
}

// --------- INSERT BORROW TRANSACTION ----------------
$stmt = $conn->prepare("
    INSERT INTO borrow_transactions 
    (borrower_type, borrower_id, book_id, copy_id, borrow_date, due_date, academic_year_id, semester)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

if(!$stmt){
    echo json_encode(['status'=>'error','message'=>'Database error: ' . $conn->error]);
    exit;
}

$now = date('Y-m-d H:i:s');
$academic_year = (int)$academic_year;
$borrower_id = (int)$borrower_id;
$book_id = (int)$book_id;
$copy_id = (int)$copy_id;

// Proper bind_param: s=string, i=int, d=double
// (borrower_type, borrower_id, book_id, copy_id, borrow_date, due_date, academic_year, semester)
// (string, int, int, int, string, string, int, string)
$stmt->bind_param(
    "siiissis",
    $borrower_type,   // s - string
    $borrower_id,     // i - int
    $book_id,         // i - int
    $copy_id,         // i - int
    $borrow_date,     // s - string (YYYY-MM-DD format)
    $due_date,        // s - string (YYYY-MM-DD format)
    $academic_year,   // i - int
    $semester         // s - string
);

if($stmt->execute()){
    // Get transaction ID and details for logging
    $trans_id = $conn->insert_id;
    
    // Mark copy as borrowed
    $conn->query("UPDATE book_copies SET availability='borrowed' WHERE id=".$copy_id);
    
    // Get book title and borrower name for activity log
    $info_stmt = $conn->prepare("
        SELECT 
            b.title as book_title,
            CASE 
                WHEN ? = 'student' THEN CONCAT(s.firstname, ' ', s.lastname)
                WHEN ? = 'faculty' THEN CONCAT(f.firstname, ' ', f.lastname)
                ELSE 'Unknown'
            END as borrower_name
        FROM books b
        LEFT JOIN students s ON s.id = ?
        LEFT JOIN faculty f ON f.id = ?
        WHERE b.id = ?
        LIMIT 1
    ");
    $info_stmt->bind_param("ssiii", $borrower_type, $borrower_type, $borrower_id, $borrower_id, $book_id);
    $info_stmt->execute();
    $info_result = $info_stmt->get_result();
    $info = $info_result->fetch_assoc();
    $info_stmt->close();
    
    $book_title = $info['book_title'] ?? 'Unknown Book';
    $borrower_name = $info['borrower_name'] ?? 'Unknown Borrower';
    
    // Log activity
    $action = 'BORROW';
    $description = "Book borrowed: {$book_title} | Borrower: {$borrower_name} | Due: {$due_date} | Duration: {$days} days | Status: Borrowed";
    $adminId = $_SESSION['admin'] ?? $_SESSION['superadmin'] ?? null;
    if ($adminId) {
        logActivity($conn, $adminId, $action, $description, 'borrow_transactions', $trans_id);
    }
    
    echo json_encode(['status'=>'success']);
} else {
    echo json_encode(['status'=>'error','message'=>'Failed to add transaction: ' . $stmt->error]);
}

$stmt->close();
exit;
?>