<?php
include 'includes/session.php';
include 'includes/conn.php';

if (isset($_POST['student_id'])) {
    $student_id = intval($_POST['student_id']);
    
    // Check for transactions
    $stmt = $conn->prepare("SELECT COUNT(*) as trans_count FROM borrow_transactions WHERE borrower_id = ? AND borrower_type = 'student'");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    
    $trans_count = $data['trans_count'] ?? 0;
    
    echo json_encode([
        'has_transactions' => $trans_count > 0,
        'transaction_count' => $trans_count
    ]);
} else {
    echo json_encode([
        'has_transactions' => false,
        'transaction_count' => 0
    ]);
}

$conn->close();
?>
