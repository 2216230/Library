<?php
include 'includes/conn.php';

echo "<h3>Password Debug Info</h3>";

// Check students
echo "<h4>Students:</h4>";
$sql = "SELECT student_id, password, LENGTH(password) as length FROM students LIMIT 5";
$query = $conn->query($sql);
while($row = $query->fetch_assoc()) {
    echo "ID: " . $row['student_id'] . " | Password: " . $row['password'] . " | Length: " . $row['length'] . "<br>";
}

// Check faculty  
echo "<h4>Faculty:</h4>";
$sql = "SELECT faculty_id, password, LENGTH(password) as length FROM faculty LIMIT 5";
$query = $conn->query($sql);
while($row = $query->fetch_assoc()) {
    echo "ID: " . $row['faculty_id'] . " | Password: " . $row['password'] . " | Length: " . $row['length'] . "<br>";
}
?>