<?php
/**
 * Database Structure Inspector
 * Shows all tables and their columns
 */

include 'includes/session.php';
include 'includes/conn.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Structure Inspector</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <style>
        body { padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { color: #333; margin-top: 30px; border-bottom: 2px solid #FF6347; padding-bottom: 10px; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
        table { margin-top: 15px; }
        .table-name { background: #FF6347; color: white; padding: 10px; border-radius: 4px; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="glyphicon glyphicon-th"></i> Database Structure</h1>
        <p class="text-muted">Inspect tables and columns for settlement system setup</p>
        <hr>

        <?php
        // Get all tables
        $tables = ['borrow_transactions', 'penalty_settlements', 'students', 'faculty', 'books'];
        
        foreach ($tables as $table) {
            $result = $conn->query("DESCRIBE $table");
            
            if ($result) {
                echo '<div class="table-name">' . strtoupper($table) . '</div>';
                echo '<table class="table table-bordered table-striped">';
                echo '<thead><tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr></thead>';
                echo '<tbody>';
                
                while ($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td><code>' . $row['Field'] . '</code></td>';
                    echo '<td>' . $row['Type'] . '</td>';
                    echo '<td>' . ($row['Null'] == 'YES' ? 'Yes' : 'No') . '</td>';
                    echo '<td>' . ($row['Key'] ?: '-') . '</td>';
                    echo '<td>' . ($row['Default'] ?: '-') . '</td>';
                    echo '<td>' . ($row['Extra'] ?: '-') . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<div class="alert alert-warning">Table <code>' . $table . '</code> does not exist</div>';
            }
        }
        ?>
    </div>
</body>
</html>
