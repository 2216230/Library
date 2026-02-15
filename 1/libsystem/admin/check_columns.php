<?php
include 'includes/conn.php';

$result = $conn->query('DESCRIBE borrow_transactions');
while($row = $result->fetch_assoc()) {
    if(in_array($row['Field'], ['borrow_date', 'due_date'])) {
        echo $row['Field'] . ': ' . $row['Type'] . '<br>';
    }
}
?>
