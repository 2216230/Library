<?php
// Direct test of archived_transactions_load.php logic
include 'libsystem/admin/includes/conn.php';

echo "=== TESTING ARCHIVED TRANSACTIONS ENDPOINT ===\n\n";

// 1. Check table exists
$check = $conn->query("SHOW TABLES LIKE 'archived_transactions'");
echo "1. Table exists: " . ($check->num_rows > 0 ? "YES" : "NO") . "\n";

if ($check->num_rows > 0) {
    // 2. Count records
    $count = $conn->query("SELECT COUNT(*) as cnt FROM archived_transactions");
    $row = $count->fetch_assoc();
    echo "2. Records in table: " . $row['cnt'] . "\n\n";
    
    // 3. Show table structure
    echo "3. Table structure:\n";
    $struct = $conn->query("DESCRIBE archived_transactions");
    while ($col = $struct->fetch_assoc()) {
        echo "   - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
    echo "\n4. Sample raw data from table:\n";
    $sample = $conn->query("SELECT * FROM archived_transactions LIMIT 2");
    if ($sample && $sample->num_rows > 0) {
        while ($record = $sample->fetch_assoc()) {
            echo "   Record ID: " . $record['id'] . "\n";
            echo "   - borrower_type: " . $record['borrower_type'] . "\n";
            echo "   - borrower_id: " . $record['borrower_id'] . "\n";
            echo "   - book_id: " . $record['book_id'] . "\n";
            echo "   - status: " . $record['status'] . "\n";
            echo "   - archived_on: " . $record['archived_on'] . "\n";
        }
    } else {
        echo "   No records found\n";
    }
    
    echo "\n5. Testing the query with joins:\n";
    $sql = "
    SELECT 
        at.archive_id,
        at.id,
        at.borrower_type,
        at.borrower_id,
        at.book_id,
        b.title AS book_title,
        b.call_no,
        CONCAT(st.firstname, ' ', st.lastname) AS student_name,
        CONCAT(fc.firstname, ' ', fc.lastname) AS faculty_name,
        at.borrow_date,
        at.due_date,
        at.return_date,
        at.status,
        at.archived_on
    FROM archived_transactions at
    LEFT JOIN books b ON at.book_id = b.id
    LEFT JOIN students st ON at.borrower_type = 'student' AND at.borrower_id = st.id
    LEFT JOIN faculty fc ON at.borrower_type = 'faculty' AND at.borrower_id = fc.id
    LIMIT 1
    ";
    
    $result = $conn->query($sql);
    if (!$result) {
        echo "   Query ERROR: " . $conn->error . "\n";
    } elseif ($result->num_rows === 0) {
        echo "   Query returned no results\n";
    } else {
        $row = $result->fetch_assoc();
        echo "   Query successful!\n";
        echo "   - book_title: " . $row['book_title'] . "\n";
        echo "   - student_name: " . $row['student_name'] . "\n";
        echo "   - faculty_name: " . $row['faculty_name'] . "\n";
    }
}

echo "\n=== END TEST ===\n";
?>
