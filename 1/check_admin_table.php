<?php
include 'libsystem/admin/includes/conn.php';

echo "<h2>All Admin Accounts:</h2>";

$result = $conn->query("SELECT id, gmail, firstname, lastname FROM admin ORDER BY id");

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Gmail</th><th>First Name</th><th>Last Name</th></tr>";

while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['gmail'] . "</td>";
    echo "<td>" . $row['firstname'] . "</td>";
    echo "<td>" . $row['lastname'] . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<h2>Checking: marijoysapditbsu@gmail.com</h2>";

$check = $conn->query("SELECT id, gmail FROM admin WHERE gmail='marijoysapditbsu@gmail.com'");
$user = $check->fetch_assoc();

if($user) {
    echo "<p style='font-size: 18px; font-weight: bold;'>";
    echo "ID: <span style='color: red;'>" . $user['id'] . "</span>";
    echo "</p>";
    
    if($user['id'] == 1) {
        echo "<p style='color: green; font-size: 16px;'><strong>✓ This IS SuperAdmin (ID=1)</strong></p>";
        echo "<p>They SHOULD see the SuperAdmin menu - this is CORRECT behavior</p>";
    } else {
        echo "<p style='color: orange; font-size: 16px;'><strong>⚠️ This is Regular Admin (ID=" . $user['id'] . ")</strong></p>";
        echo "<p>They should NOT see the SuperAdmin menu</p>";
    }
} else {
    echo "<p style='color: red;'>Not found in database</p>";
}
?>
