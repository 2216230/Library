<?php
header('Content-Type: application/json; charset=utf-8');
include 'includes/session.php';
include 'includes/conn.php';

$active_academic_year = $_POST['active_academic_year'] ?? null; // ID
$active_semester      = $_POST['active_semester'] ?? null;       // String

if(!$active_academic_year || !$active_semester){
    echo json_encode(['status'=>'error','msg'=>'Missing required fields.']);
    exit;
}

// Validate academic_year exists
$stmt = $conn->prepare("SELECT id FROM academic_years WHERE id=? LIMIT 1");
$stmt->bind_param("i", $active_academic_year);
$stmt->execute();
$res = $stmt->get_result();

if($res->num_rows === 0){
    echo json_encode(['status'=>'error','msg'=>'Invalid academic year.']);
    exit;
}

// Normalize semester text
$active_semester = strtolower($active_semester) === 'short term' ? 'Short-Term' : $active_semester;

// Update settings table
$stmt = $conn->prepare("UPDATE settings SET active_academic_year=?, active_semester=? WHERE id=1");
$stmt->bind_param("is", $active_academic_year, $active_semester);

if($stmt->execute()){
    echo json_encode(['status'=>'success','msg'=>'Settings saved successfully!']);
} else {
    echo json_encode(['status'=>'error','msg'=>'Failed to save settings.']);
}
exit;
?>