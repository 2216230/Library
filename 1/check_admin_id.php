<?php
include 'libsystem/admin/includes/conn.php';

$result = $conn->query("DESCRIBE admin");

echo "<h2>Admin Table Structure:</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<h2>All Admin Accounts:</h2>";

$result = $conn->query("SELECT * FROM admin LIMIT 10");
if($result->num_rows > 0) {
    $cols = $result->fetch_fields();
    echo "<table border='1' cellpadding='10'>";
    echo "<tr>";
    foreach($cols as $col) {
        echo "<th>" . $col->name . "</th>";
    }
    echo "</tr>";
    
    $result->data_seek(0);
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach($row as $val) {
            echo "<td>" . htmlspecialchars($val) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}
?>
