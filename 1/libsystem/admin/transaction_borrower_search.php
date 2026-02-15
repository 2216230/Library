<?php
include 'includes/session.php';
include 'includes/conn.php';
header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');
$type = $_GET['type'] ?? '';

if(strlen($q) < 2){
    echo json_encode(['success'=>false,'message'=>'Query too short','data'=>[]]);
    exit;
}

$data = [];

// Students
if($type === '' || strtolower($type) === 'student'){
    $sql = "SELECT id, student_id AS id_number, firstname, middlename, lastname, 'student' AS type
            FROM students
            WHERE student_id LIKE ? OR firstname LIKE ? OR lastname LIKE ?
            LIMIT 10";
    $like = "%{$q}%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $like, $like, $like);
    $stmt->execute();
    $res = $stmt->get_result();
    while($r = $res->fetch_assoc()){
        $r['fullname'] = trim($r['firstname'].' '.($r['middlename'] ? $r['middlename'].' ' : '').$r['lastname']);
        $r['name'] = $r['fullname']; // for JS suggestions
        
        // Get count of currently borrowed books (including overdue)
        $countSql = "SELECT COUNT(*) as borrowed_count FROM borrow_transactions 
                     WHERE borrower_id = ? AND borrower_type = 'student' AND status = 'borrowed'";
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param('i', $r['id']);
        $countStmt->execute();
        $countRes = $countStmt->get_result()->fetch_assoc();
        $r['borrowed_count'] = $countRes['borrowed_count'] ?? 0;
        
        $data[] = $r;
    }
}

// Faculty
if($type === '' || strtolower($type) === 'faculty'){
    $sql = "SELECT id, faculty_id AS id_number, firstname, middlename, lastname, 'faculty' AS type
            FROM faculty
            WHERE faculty_id LIKE ? OR firstname LIKE ? OR lastname LIKE ?
            LIMIT 10";
    $like = "%{$q}%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $like, $like, $like);
    $stmt->execute();
    $res = $stmt->get_result();
    while($r = $res->fetch_assoc()){
        $r['fullname'] = trim($r['firstname'].' '.($r['middlename'] ? $r['middlename'].' ' : '').$r['lastname']);
        $r['name'] = $r['fullname']; // for JS suggestions
        
        // Get count of currently borrowed books (including overdue)
        $countSql = "SELECT COUNT(*) as borrowed_count FROM borrow_transactions 
                     WHERE borrower_id = ? AND borrower_type = 'faculty' AND status = 'borrowed'";
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param('i', $r['id']);
        $countStmt->execute();
        $countRes = $countStmt->get_result()->fetch_assoc();
        $r['borrowed_count'] = $countRes['borrowed_count'] ?? 0;
        
        $data[] = $r;
    }
}

echo json_encode(['success'=>true,'data'=>$data]);
exit;
