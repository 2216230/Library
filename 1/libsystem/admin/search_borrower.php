<?php
include 'includes/conn.php';

$type = $_GET['type'] ?? '';
$query = $_GET['query'] ?? '';

if (!$type || !$query) exit;

if ($type === 'student') {
    // Join students with course table
    $sql = "SELECT s.id, s.student_id, s.firstname, s.lastname, c.title AS course_title, c.code AS course_code
            FROM students s
            LEFT JOIN course c ON s.course_id = c.id
            WHERE s.student_id LIKE ? OR s.lastname LIKE ?
            ORDER BY s.lastname ASC
            LIMIT 10";
    
    $stmt = $conn->prepare($sql);
    $search = "%{$query}%";
    $stmt->bind_param('ss', $search, $search);
} 
else {
    $sql = "SELECT id, faculty_id, firstname, lastname, department 
            FROM faculty 
            WHERE faculty_id LIKE ? OR lastname LIKE ?
            ORDER BY lastname ASC
            LIMIT 10";
    
    $stmt = $conn->prepare($sql);
    $search = "%{$query}%";
    $stmt->bind_param('ss', $search, $search);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = htmlspecialchars($row['id']);
        $name = htmlspecialchars($row['lastname'] . ', ' . $row['firstname']);

        if ($type === 'student') {
            $details = "ID: " . htmlspecialchars($row['student_id']) . 
                       " | " . htmlspecialchars($row['course_code']) . " - " . htmlspecialchars($row['course_title']);
        } else {
            $details = "ID: " . htmlspecialchars($row['faculty_id']) . 
                       " | " . htmlspecialchars($row['department']);
        }

        echo "<a href='#' class='list-group-item list-group-item-action borrower-item'
                data-id='$id' data-name='$name' data-details='$details'>
                $name<br><small>$details</small>
              </a>";
    }
} else {
    echo "<div class='list-group-item text-muted'>No results found</div>";
}
?>
