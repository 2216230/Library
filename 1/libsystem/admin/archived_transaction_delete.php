<?php
include 'includes/session.php';
include 'includes/conn.php';

header('Content-Type: application/json');

if (!isset($_POST['archive_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing archive_id parameter']);
    exit;
}

$archive_id = intval($_POST['archive_id']);

try {
    // Delete from archived_transactions
    $stmt = $conn->prepare("DELETE FROM archived_transactions WHERE archive_id = ?");
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param('i', $archive_id);
    
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Failed to delete: ' . $stmt->error]);
        $stmt->close();
        exit;
    }
    
    $affected_rows = $stmt->affected_rows;
    $stmt->close();
    
    if ($affected_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Archived transaction not found']);
        exit;
    }
    
    // Return success
    echo json_encode([
        'success' => true,
        'message' => 'Transaction permanently deleted.'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
