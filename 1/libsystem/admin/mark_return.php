<?php
include 'includes/conn.php';
if (isset($_POST['transaction_id'])) {
    $id = intval($_POST['transaction_id']);
    $date = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("UPDATE borrow_transactions SET status='returned', return_date=? WHERE id=?");
    $stmt->bind_param("si", $date, $id);
    if ($stmt->execute()) echo "success";
    else http_response_code(500);
}
?>
