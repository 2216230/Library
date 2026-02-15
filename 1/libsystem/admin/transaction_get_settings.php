<?php
include 'includes/session.php';
include 'includes/conn.php';

$res = $conn->query("SELECT s.academic_year_id, s.semester 
                     FROM settings s LIMIT 1");
$data = $res->fetch_assoc();
echo json_encode($data);
?>
