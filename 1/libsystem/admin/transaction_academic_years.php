<?php
include 'includes/session.php';
include 'includes/conn.php';
header('Content-Type: application/json; charset=utf-8');

$result = $conn->query("SELECT id, year_start, year_end FROM academic_years ORDER BY year_start DESC");

$ays = [];
while($row = $result->fetch_assoc()){
    $row['label'] = $row['year_start'].'-'.$row['year_end']; // for dropdown display
    $ays[] = $row;
}

echo json_encode($ays);
?>
