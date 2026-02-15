<?php
// transaction_settings_update.php
include 'includes/session.php';
include 'includes/conn.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    $_SESSION['error'] = "Invalid request.";
    header("Location: transactions.php");
    exit;
}

$academic_year_id = intval($_POST['academic_year_id'] ?? 0);
$semester = trim($_POST['semester'] ?? '');

if(!$academic_year_id || !$semester){
    $_SESSION['error'] = "Please select academic year and semester.";
    header("Location: transactions.php");
    exit;
}

// ensure table exists (create minimal schema)
$create_sql = "CREATE TABLE IF NOT EXISTS transaction_settings (
    id INT PRIMARY KEY,
    academic_year_id INT NOT NULL,
    semester VARCHAR(50) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$conn->query($create_sql);

// check if a row exists
$check = $conn->query("SELECT * FROM transaction_settings WHERE id=1");
if($check && $check->num_rows > 0){
    $stmt = $conn->prepare("UPDATE transaction_settings SET academic_year_id=?, semester=? WHERE id=1");
    $stmt->bind_param('is', $academic_year_id, $semester);
    $stmt->execute();
    $stmt->close();
} else {
    $stmt = $conn->prepare("INSERT INTO transaction_settings (id, academic_year_id, semester) VALUES (1, ?, ?)");
    $stmt->bind_param('is', $academic_year_id, $semester);
    $stmt->execute();
    $stmt->close();
}

$_SESSION['success'] = "Transaction settings updated.";
header("Location: transactions.php");
exit;
