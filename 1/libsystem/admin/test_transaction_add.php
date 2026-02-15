<?php
include 'includes/session.php';
include 'includes/conn.php';

// Test data - simulate a form submission
$_POST['borrower_id']   = 1;
$_POST['borrower_type'] = 'student';
$_POST['book_id']       = 1;
$_POST['copy_id']       = 1;
$_POST['borrow_date']   = '2025-12-07';
$_POST['days']          = '7';
$_POST['due_date']      = '2025-12-14';
$_POST['academic_year'] = 1;
$_POST['semester']      = '1st';

echo "<h2>Testing Transaction Add</h2>";
echo "<p>Data:</p>";
echo "Borrower ID: " . $_POST['borrower_id'] . "<br>";
echo "Borrow Date: " . $_POST['borrow_date'] . "<br>";
echo "Due Date: " . $_POST['due_date'] . "<br>";
echo "Academic Year: " . $_POST['academic_year'] . "<br>";

// Now run the actual logic
$borrower_id   = $_POST['borrower_id'] ?? null;
$borrower_type = $_POST['borrower_type'] ?? null;
$book_id       = $_POST['book_id'] ?? null;
$copy_id       = $_POST['copy_id'] ?? null;
$borrow_date   = $_POST['borrow_date'] ?? null;
$days          = $_POST['days'] ?? null;
$due_date      = $_POST['due_date'] ?? null;
$academic_year = $_POST['academic_year'] ?? null;
$semester      = $_POST['semester'] ?? null;

echo "<hr>";
echo "<h3>Validation</h3>";

if(!$borrower_id) echo "ERROR: Missing borrower_id<br>";
if(!$borrower_type) echo "ERROR: Missing borrower_type<br>";
if(!$book_id) echo "ERROR: Missing book_id<br>";
if(!$copy_id) echo "ERROR: Missing copy_id<br>";
if(!$borrow_date) echo "ERROR: Missing borrow_date<br>";
if($days === null || $days === '') echo "ERROR: Missing days<br>";
if(!$due_date) echo "ERROR: Missing due_date<br>";

echo "✓ All validations passed<br>";

echo "<hr>";
echo "<h3>Database Insert</h3>";

// Prepare statement
$stmt = $conn->prepare("
    INSERT INTO borrow_transactions 
    (borrower_type, borrower_id, book_id, copy_id, borrow_date, due_date, academic_year_id, semester)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

if(!$stmt){
    echo "ERROR preparing statement: " . $conn->error . "<br>";
    exit;
}

$academic_year = (int)$academic_year;
$borrower_id = (int)$borrower_id;
$book_id = (int)$book_id;
$copy_id = (int)$copy_id;

echo "Variables before bind:<br>";
echo "borrower_type: " . $borrower_type . " (type: " . gettype($borrower_type) . ")<br>";
echo "borrower_id: " . $borrower_id . " (type: " . gettype($borrower_id) . ")<br>";
echo "book_id: " . $book_id . " (type: " . gettype($book_id) . ")<br>";
echo "copy_id: " . $copy_id . " (type: " . gettype($copy_id) . ")<br>";
echo "borrow_date: " . $borrow_date . " (type: " . gettype($borrow_date) . ")<br>";
echo "due_date: " . $due_date . " (type: " . gettype($due_date) . ")<br>";
echo "academic_year: " . $academic_year . " (type: " . gettype($academic_year) . ")<br>";
echo "semester: " . $semester . " (type: " . gettype($semester) . ")<br>";

$stmt->bind_param(
    "siiiisss",
    $borrower_type,
    $borrower_id,
    $book_id,
    $copy_id,
    $borrow_date,
    $due_date,
    $academic_year,
    $semester
);

echo "<br>Attempting to execute...<br>";

if($stmt->execute()){
    echo "✓ Insert successful! Transaction ID: " . $stmt->insert_id . "<br>";
    
    // Now check what was actually stored
    echo "<hr>";
    echo "<h3>Verification - What was stored:</h3>";
    $verify = $conn->query("SELECT borrow_date, due_date, academic_year_id FROM borrow_transactions WHERE id=" . $stmt->insert_id);
    $row = $verify->fetch_assoc();
    echo "Stored borrow_date: " . $row['borrow_date'] . " (type: " . gettype($row['borrow_date']) . ")<br>";
    echo "Stored due_date: " . $row['due_date'] . " (type: " . gettype($row['due_date']) . ")<br>";
    echo "Stored academic_year_id: " . $row['academic_year_id'] . "<br>";
} else {
    echo "ERROR executing statement: " . $stmt->error . "<br>";
}

$stmt->close();
?>
