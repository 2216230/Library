<?php
// borrowers_list.php
include 'includes/session.php';
include 'includes/conn.php';
header('Content-Type: application/json; charset=utf-8');

$type = $_GET['type'] ?? 'student';
$type = strtolower($type);

$data = ['success'=>false,'data'=>[]];

if($type === 'student'){
    $sql = "SELECT id, student_id AS id_number, CONCAT(firstname,' ', COALESCE(middlename,''),' ',lastname) AS fullname
            FROM students
            ORDER BY lastname, firstname
            LIMIT 1000";
    $res = $conn->query($sql);
    if($res){
        while($r = $res->fetch_assoc()) $data['data'][] = $r;
        $data['success'] = true;
    }
} elseif($type === 'faculty' || $type === 'teacher'){
    $sql = "SELECT id, faculty_id AS id_number, CONCAT(firstname,' ', COALESCE(middlename,''),' ',lastname) AS fullname
            FROM faculty
            ORDER BY lastname, firstname
            LIMIT 1000";
    $res = $conn->query($sql);
    if($res){
        while($r = $res->fetch_assoc()) $data['data'][] = $r;
        $data['success'] = true;
    }
} else {
    $data['success'] = false;
    $data['msg'] = 'Unknown type';
}

echo json_encode($data);
exit;
