<?php
/**
 * API: Get Due Date Count
 * Returns the count of books due within 1 day
 */

include 'session.php';
include 'conn.php';

header('Content-Type: application/json');

// Check if user is logged in (admin or superadmin)
if (!isset($_SESSION['admin']) || empty($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Verify user exists and has proper role
$admin_check = $conn->query("SELECT id, role FROM admin WHERE id = '{$_SESSION['admin']}' LIMIT 1");
if (!$admin_check || $admin_check->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
$admin_user = $admin_check->fetch_assoc();
if (!in_array($admin_user['role'], ['admin', 'superadmin'])) {
    echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
    exit;
}

// Get count of books due within 1 day
$due_query = "
SELECT COUNT(*) as count
FROM borrow_transactions bt
WHERE bt.status = 'borrowed' 
AND bt.due_date >= CURDATE() 
AND bt.due_date <= DATE_ADD(CURDATE(), INTERVAL 1 DAY)
";

$result = $conn->query($due_query);
$count = 0;

if ($result) {
    $row = $result->fetch_assoc();
    $count = $row['count'] ?? 0;
}

echo json_encode([
    'success' => true,
    'count' => intval($count)
]);

$conn->close();
?>
