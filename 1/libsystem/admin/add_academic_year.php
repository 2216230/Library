<?php
include 'includes/session.php';
include 'includes/conn.php';
header('Content-Type: application/json; charset=utf-8');

$year = $_POST['academic_year'] ?? null; // expected format "2024-2025"

if(!$year || !preg_match('/^\d{4}-\d{4}$/', $year)){
    echo json_encode(['status'=>'error','msg'=>'Invalid format. Use YYYY-YYYY']);
    exit;
}

list($start, $end) = explode('-', $year);

// Check duplicate
$check = $conn->prepare("SELECT id FROM academic_years WHERE year_start=? AND year_end=?");
$check->bind_param("ii", $start, $end);
$check->execute();
$check->store_result();
if($check->num_rows > 0){
    echo json_encode(['status'=>'error','msg'=>'Academic year already exists']);
    exit;
}

// Insert
$insert = $conn->prepare("INSERT INTO academic_years (year_start, year_end) VALUES (?, ?)");
$insert->bind_param("ii", $start, $end);
if($insert->execute()){
    echo json_encode(['status'=>'success']);
}else{
    echo json_encode(['status'=>'error','msg'=>'Failed to add academic year']);
}
?>
