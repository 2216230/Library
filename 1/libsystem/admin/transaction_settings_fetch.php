<?php
// transaction_settings_fetch.php
include 'includes/session.php';
include 'includes/conn.php';
header('Content-Type: application/json; charset=utf-8');

// if table missing, return empty
$check = $conn->query("SHOW TABLES LIKE 'transaction_settings'");
if(!$check || $check->num_rows == 0){
    echo json_encode(['success'=>true,'data'=>null]);
    exit;
}

$res = $conn->query("SELECT * FROM transaction_settings WHERE id=1 LIMIT 1");
if($res && $res->num_rows > 0){
    $r = $res->fetch_assoc();
    echo json_encode(['success'=>true,'data'=>$r]);
} else {
    echo json_encode(['success'=>true,'data'=>null]);
}
exit;
