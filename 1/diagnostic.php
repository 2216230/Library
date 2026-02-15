<?php
/**
 * COMPREHENSIVE DIAGNOSTIC FOR ARCHIVED TRANSACTIONS SYSTEM
 */

echo "<!DOCTYPE html><html><head><title>Archived Transactions Diagnostic</title>";
echo "<style>";
echo "body { font-family: Arial; padding: 20px; background: #f5f5f5; }";
echo ".section { background: white; padding: 20px; margin: 20px 0; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }";
echo ".success { color: green; font-weight: bold; }";
echo ".error { color: red; font-weight: bold; }";
echo ".warning { color: orange; font-weight: bold; }";
echo "pre { background: #f0f0f0; padding: 10px; overflow-x: auto; font-size: 12px; }";
echo "table { width: 100%; border-collapse: collapse; margin: 10px 0; }";
echo "th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }";
echo "th { background: #006400; color: white; }";
echo "tr:nth-child(even) { background: #f9f9f9; }";
echo "</style></head><body>";
echo "<h1>🔍 Archived Transactions System Diagnostic</h1>";

// Start session for session requirements
session_start();
if (!isset($_SESSION['admin'])) {
    $_SESSION['admin'] = 1; // Mock session
}

include 'libsystem/admin/includes/conn.php';

// ========== SECTION 1: DATABASE CONNECTIVITY ==========
echo "<div class='section'>";
echo "<h2>1️⃣ Database Connectivity</h2>";

if ($conn->connect_error) {
    echo "<p class='error'>❌ Connection Failed: " . $conn->connect_error . "</p>";
} else {
    echo "<p class='success'>✅ Connected to: " . $conn->get_server_info() . "</p>";
    echo "<p>Database: libsystem5</p>";
}
echo "</div>";

// ========== SECTION 2: TABLE STRUCTURE ==========
echo "<div class='section'>";
echo "<h2>2️⃣ Table Status</h2>";

$table_check = $conn->query("SHOW TABLES LIKE 'archived_transactions'");

if (!$table_check) {
    echo "<p class='error'>❌ Query Error: " . $conn->error . "</p>";
} else {
    if ($table_check->num_rows > 0) {
        echo "<p class='success'>✅ archived_transactions table EXISTS</p>";
        
        // Get record count
        $count_result = $conn->query("SELECT COUNT(*) as cnt FROM archived_transactions");
        $count_row = $count_result->fetch_assoc();
        echo "<p>📊 Total records: <strong>" . $count_row['cnt'] . "</strong></p>";
        
        // Show table structure
        echo "<h3>Table Columns:</h3>";
        echo "<table>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        
        $struct = $conn->query("DESCRIBE archived_transactions");
        while ($col = $struct->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $col['Field'] . "</td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . ($col['Null'] == 'YES' ? 'YES' : 'NO') . "</td>";
            echo "<td>" . ($col['Key'] ?: '-') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p class='warning'>⚠️ archived_transactions table DOES NOT EXIST (will be created on first archive)</p>";
    }
}

echo "</div>";

// ========== SECTION 3: SAMPLE DATA ==========
echo "<div class='section'>";
echo "<h2>3️⃣ Sample Data from archived_transactions</h2>";

$sample_result = $conn->query("SELECT * FROM archived_transactions LIMIT 3");

if (!$sample_result) {
    echo "<p class='error'>❌ Query Error: " . $conn->error . "</p>";
} else {
    $count = $sample_result->num_rows;
    echo "<p>Found <strong>$count</strong> sample records</p>";
    
    if ($count > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Borrower Type</th><th>Borrower ID</th><th>Book ID</th><th>Status</th><th>Archived On</th></tr>";
        
        while ($row = $sample_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['borrower_type'] . "</td>";
            echo "<td>" . $row['borrower_id'] . "</td>";
            echo "<td>" . $row['book_id'] . "</td>";
            echo "<td>" . $row['status'] . "</td>";
            echo "<td>" . $row['archived_on'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>No sample data available - archive some transactions first</p>";
    }
}

echo "</div>";

// ========== SECTION 4: QUERY TEST ==========
echo "<div class='section'>";
echo "<h2>4️⃣ Testing the Query with Joins</h2>";

$test_sql = "
SELECT 
    at.id,
    at.borrower_type,
    at.borrower_id,
    at.book_id,
    b.title AS book_title,
    b.call_no,
    CONCAT(st.firstname, ' ', st.lastname) AS student_name,
    CONCAT(fc.firstname, ' ', fc.lastname) AS faculty_name,
    at.status,
    at.archived_on
FROM archived_transactions at
LEFT JOIN books b ON at.book_id = b.id
LEFT JOIN students st ON at.borrower_type = 'student' AND at.borrower_id = st.id
LEFT JOIN faculty fc ON at.borrower_type = 'faculty' AND at.borrower_id = fc.id
LIMIT 3
";

$test_result = $conn->query($test_sql);

if (!$test_result) {
    echo "<p class='error'>❌ Query Error: " . $conn->error . "</p>";
} else {
    echo "<p class='success'>✅ Query executed successfully</p>";
    echo "<p>Rows returned: " . $test_result->num_rows . "</p>";
    
    if ($test_result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Book Title</th><th>Call No</th><th>Borrower</th><th>Status</th></tr>";
        
        while ($row = $test_result->fetch_assoc()) {
            $borrower = '';
            if ($row['borrower_type'] === 'student') {
                $borrower = $row['student_name'];
            } elseif ($row['borrower_type'] === 'faculty') {
                $borrower = $row['faculty_name'];
            }
            
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . ($row['book_title'] ?: 'N/A') . "</td>";
            echo "<td>" . ($row['call_no'] ?: 'N/A') . "</td>";
            echo "<td>" . ($borrower ?: 'Unknown') . "</td>";
            echo "<td>" . $row['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

echo "</div>";

// ========== SECTION 5: FILE CHECKS ==========
echo "<div class='section'>";
echo "<h2>5️⃣ Required Files</h2>";

$files = [
    'libsystem/admin/archived_transactions.php' => 'Main archived transactions page',
    'libsystem/admin/archived_transactions_load.php' => 'AJAX data loader endpoint',
    'libsystem/admin/transaction_archive_remove.php' => 'Archive handler',
    'libsystem/admin/includes/scripts.php' => 'Script loader',
];

echo "<table>";
echo "<tr><th>File</th><th>Status</th><th>Description</th></tr>";

foreach ($files as $file => $desc) {
    $exists = file_exists($file);
    $status = $exists ? '<span class="success">✅ EXISTS</span>' : '<span class="error">❌ MISSING</span>';
    echo "<tr>";
    echo "<td>" . basename($file) . "</td>";
    echo "<td>" . $status . "</td>";
    echo "<td>" . $desc . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "</div>";

// ========== SECTION 6: INSTRUCTIONS ==========
echo "<div class='section'>";
echo "<h2>6️⃣ Next Steps</h2>";
echo "<ol>";
echo "<li>Go to <a href='libsystem/admin/transactions.php' target='_blank'>Active Transactions</a></li>";
echo "<li>Add or open a transaction</li>";
echo "<li>Click the red Trash button to archive it</li>";
echo "<li>Go to <a href='libsystem/admin/archived_transactions.php' target='_blank'>Archived Transactions</a></li>";
echo "<li>The archived transaction should now appear in the table</li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";

$conn->close();
?>
