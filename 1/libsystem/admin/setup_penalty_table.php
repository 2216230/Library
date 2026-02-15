<?php
/**
 * Setup Penalty Settlements Table
 * Visit this page in your browser to automatically create the table if it doesn't exist
 * URL: http://localhost/libsystem5/1/libsystem/admin/setup_penalty_table.php
 */

include 'includes/session.php';
include 'includes/conn.php';

// Check if user is authenticated
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['id'])) {
    die('Unauthorized access');
}

$result_message = '';
$result_status = 'error';

try {
    // First, check if table exists
    $check = $conn->query("SHOW TABLES LIKE 'penalty_settlements'");
    
    if ($check->num_rows == 0) {
        // Table doesn't exist, create it
        $sql = "
            CREATE TABLE IF NOT EXISTS penalty_settlements (
                id INT AUTO_INCREMENT PRIMARY KEY,
                transaction_id INT NOT NULL,
                borrower_id INT,
                book_id INT,
                borrower_name VARCHAR(255),
                book_title VARCHAR(255),
                days_overdue INT NOT NULL DEFAULT 0,
                due_date DATE,
                return_date DATE,
                fine_per_day DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
                chargeable_days INT NOT NULL DEFAULT 0,
                calculated_fine DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
                adjustment_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
                adjustment_reason VARCHAR(100),
                adjustment_details TEXT,
                total_payable DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
                return_status VARCHAR(50),
                settled_by INT,
                settled_by_name VARCHAR(255),
                settled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                status VARCHAR(50) DEFAULT 'settled',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_transaction_id (transaction_id),
                KEY idx_borrower_id (borrower_id),
                KEY idx_book_id (book_id),
                KEY idx_settled_at (settled_at),
                KEY idx_status (status),
                FOREIGN KEY (transaction_id) REFERENCES borrow_transactions(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        if ($conn->query($sql) === TRUE) {
            $result_message = '✅ <strong>Success!</strong> Penalty settlements table has been created successfully.';
            $result_status = 'success';
        } else {
            $result_message = '❌ <strong>Error!</strong> ' . $conn->error;
        }
    } else {
        $result_message = '✅ <strong>Already Exists!</strong> Penalty settlements table is already in the database.';
        $result_status = 'success';
    }
} catch (Exception $e) {
    $result_message = '❌ <strong>Error!</strong> ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Penalty Settlements Table Setup</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <style>
        body {
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 600px;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .alert {
            margin-top: 20px;
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="glyphicon glyphicon-cog"></i> Penalty Settlements Setup</h1>
        
        <div class="alert alert-<?php echo $result_status; ?>">
            <?php echo $result_message; ?>
        </div>
        
        <?php if ($result_status === 'success'): ?>
            <div class="alert alert-info">
                <strong>Next Steps:</strong>
                <ul>
                    <li>Go to <strong>Overdue Management</strong> page</li>
                    <li>Click "Settlement Records" tab</li>
                    <li>Settle an overdue transaction to see records appear</li>
                </ul>
            </div>
        <?php endif; ?>
        
        <hr>
        
        <p><strong>Table Details:</strong></p>
        <ul>
            <li>Table Name: <code>penalty_settlements</code></li>
            <li>Stores: Fine calculations and settlement records</li>
            <li>Linked to: <code>borrow_transactions</code> table</li>
            <li>Auto-created on: First settlement submission</li>
        </ul>
    </div>
</body>
</html>
