<?php
// transaction_add.php
include 'includes/session.php';
include 'includes/conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: transactions.php");
    exit;
}

// required values
$borrower_id = intval($_POST['borrower_id'] ?? 0);
$borrower_type = trim($_POST['borrower_type_hidden'] ?? '');
$book_id = intval($_POST['book_id'] ?? 0);
$copy_number = isset($_POST['copy_number']) ? trim($_POST['copy_number']) : null;
$borrow_date = trim($_POST['borrow_date'] ?? '');
$due_date = trim($_POST['due_date'] ?? '');

// fallback: if borrow_date is empty use now
if(empty($borrow_date)) $borrow_date = date('Y-m-d H:i:s');
if(empty($due_date)) $due_date = date('Y-m-d H:i:s', strtotime('+7 days'));

// basic validation
if(!$borrower_id || !$borrower_type || !$book_id){
    $_SESSION['error'] = 'Please select Borrower, Book and Copy (if needed).';
    header("Location: transactions.php");
    exit;
}

// check that the requested copy exists and is available (if copy_number provided)
$copy_id = null;
if($copy_number !== null && $copy_number !== ''){
    $stmt = $conn->prepare("SELECT id, availability FROM book_copies WHERE book_id=? AND copy_number=? LIMIT 1");
    $stmt->bind_param('ii', $book_id, $copy_number);
    $stmt->execute();
    $res = $stmt->get_result();
    if($res->num_rows == 0){
        $_SESSION['error'] = "Selected copy not found.";
        header("Location: transactions.php");
        exit;
    }
    $r = $res->fetch_assoc();
    if($r['availability'] !== 'available'){
        $_SESSION['error'] = "Selected copy is not available.";
        header("Location: transactions.php");
        exit;
    }
    $copy_id = intval($r['id']);
} else {
    // if no copy selected, pick first available copy
    $stmt = $conn->prepare("SELECT id, copy_number FROM book_copies WHERE book_id=? AND availability='available' ORDER BY copy_number LIMIT 1");
    $stmt->bind_param('i', $book_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if($res->num_rows == 0){
        $_SESSION['error'] = "No available copies for the selected book.";
        header("Location: transactions.php");
        exit;
    }
    $r = $res->fetch_assoc();
    $copy_id = intval($r['id']);
    $copy_number = $r['copy_number'];
}

// Optional: detect whether 'copy_number' and/or 'academic_year_id' columns exist in borrow_transactions
$cols = [];
$cr = $conn->query("SHOW COLUMNS FROM borrow_transactions");
while($c = $cr->fetch_assoc()){
    $cols[] = $c['Field'];
}

$has_copy_col = in_array('copy_number', $cols);
$has_acad_col = in_array('academic_year_id', $cols);

// Build insert query dynamically
$fields = ['borrower_type','borrower_id','book_id','borrow_date','due_date','status'];
$placeholders = ['?','?','?','?','?','\'borrowed\''];
$types = '';
$values = [];

$types .= 's'; $values[] = $borrower_type;
$types .= 'i'; $values[] = $borrower_id;
$types .= 'i'; $values[] = $book_id;
$types .= 's'; $values[] = $borrow_date;
$types .= 's'; $values[] = $due_date;

if($has_copy_col){
    $fields[] = 'copy_number';
    $placeholders[] = '?';
    $types .= 'i';
    $values[] = intval($copy_number);
}

// If there's an active transaction settings row, use it as academic_year_id and semester if exist
$academic_year_id_to_insert = null;
if($has_acad_col){
    $settings = $conn->query("SELECT academic_year_id FROM transaction_settings WHERE id=1 LIMIT 1");
    if($settings && $settings->num_rows > 0){
        $r = $settings->fetch_assoc();
        $academic_year_id_to_insert = intval($r['academic_year_id']);
        $fields[] = 'academic_year_id';
        $placeholders[] = '?';
        $types .= 'i';
        $values[] = $academic_year_id_to_insert;
    }
}

// Compose query
$fields_list = implode(',', $fields);
$placeholders_list = implode(',', array_map(function($p){
    return $p === "'borrowed'" ? $p : '?';
}, $placeholders));

// Prepare statement (we need correct ordering)
$insert_sql = "INSERT INTO borrow_transactions ($fields_list) VALUES (" . implode(',', array_map(function($f) use ($fields, $placeholders){
    return ($f === 'status') ? "'borrowed'" : '?';
}, $fields)) . ")";

// Simpler: create prepared statement mapping to the values we prepared earlier:
$insert_sql = "INSERT INTO borrow_transactions (" . implode(',', $fields) . ") VALUES (" . rtrim(str_repeat('?,', count($fields)), ',') . ")";
$stmt = $conn->prepare($insert_sql);

if(!$stmt){
    $_SESSION['error'] = "Prepare failed: " . $conn->error;
    header("Location: transactions.php");
    exit;
}

// build types string again according to $fields
$bind_types = '';
$bind_values = [];
foreach($fields as $f){
    switch($f){
        case 'borrower_type': $bind_types .= 's'; $bind_values[] = $borrower_type; break;
        case 'borrower_id': $bind_types .= 'i'; $bind_values[] = $borrower_id; break;
        case 'book_id': $bind_types .= 'i'; $bind_values[] = $book_id; break;
        case 'borrow_date': $bind_types .= 's'; $bind_values[] = $borrow_date; break;
        case 'due_date': $bind_types .= 's'; $bind_values[] = $due_date; break;
        case 'copy_number': $bind_types .= 'i'; $bind_values[] = intval($copy_number); break;
        case 'academic_year_id': $bind_types .= 'i'; $bind_values[] = $academic_year_id_to_insert; break;
        default:
            // fallback: treat as string
            $bind_types .= 's'; $bind_values[] = ${$f} ?? '';
    }
}

// bind dynamically
$array = array_merge([$bind_types], $bind_values);
$tmp = [];
foreach($array as $k => $v) $tmp[$k] = &$array[$k];
call_user_func_array([$stmt, 'bind_param'], $tmp);

$ok = $stmt->execute();
if(!$ok){
    $_SESSION['error'] = "Failed to save transaction: " . $stmt->error;
    header("Location: transactions.php");
    exit;
}

$transaction_id = $stmt->insert_id;
$stmt->close();

// update the book_copies availability to 'borrowed'
if($copy_id){
    $u = $conn->prepare("UPDATE book_copies SET availability='borrowed' WHERE id=?");
    $u->bind_param('i', $copy_id);
    $u->execute();
    $u->close();
}

$_SESSION['success'] = "Transaction saved successfully.";
header("Location: transactions.php");
exit;
