<?php
include 'includes/session.php';
include 'includes/conn.php';

echo "<h2>Book-Subject Assignments Debug</h2>";

// Check if book_subject_map table exists
$check_table = $conn->query("SHOW TABLES LIKE 'book_subject_map'");
if($check_table->num_rows == 0) {
    echo "<p style='color: red;'>❌ ERROR: book_subject_map table does NOT exist!</p>";
    echo "<p>You need to create this table. Run this SQL:</p>";
    echo "<pre>CREATE TABLE IF NOT EXISTS book_subject_map (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    subject_id INT NOT NULL,
    UNIQUE KEY unique_book_subject (book_id, subject_id),
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subject(id) ON DELETE CASCADE
) ENGINE=InnoDB;</pre>";
} else {
    echo "<p style='color: green;'>✅ book_subject_map table exists</p>";
    
    // Show table structure
    echo "<h3>Table Structure:</h3>";
    $structure = $conn->query("DESCRIBE book_subject_map");
    if($structure) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while($col = $structure->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$col['Field']}</td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Key']}</td>";
            echo "<td>".(($col['Default'] ?? 'NULL'))."</td>";
            echo "<td>{$col['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check for ALL data (not just last 20)
    echo "<h3>ALL Data in book_subject_map:</h3>";
    $all_data = $conn->query("SELECT * FROM book_subject_map");
    echo "<p>Total rows: ".$all_data->num_rows."</p>";
    if($all_data->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Book ID</th><th>Subject ID</th></tr>";
        while($r = $all_data->fetch_assoc()) {
            echo "<tr><td>{$r['id']}</td><td>{$r['book_id']}</td><td>{$r['subject_id']}</td></tr>";
        }
        echo "</table>";
    }
    
    // Check for unique constraint
    echo "<h3>Table Indexes/Keys:</h3>";
    $keys = $conn->query("SHOW KEYS FROM book_subject_map");
    if($keys && $keys->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Key Name</th><th>Column</th><th>Unique</th></tr>";
        while($k = $keys->fetch_assoc()) {
            $unique = $k['Non_unique'] == 0 ? 'YES' : 'NO';
            echo "<tr><td>{$k['Key_name']}</td><td>{$k['Column_name']}</td><td>{$unique}</td></tr>";
        }
        echo "</table>";
    }
    
    // Try a manual insert to test
    echo "<h3>Manual Insert Test:</h3>";
    echo "<p>Trying to insert book_id=1, subject_id=19...</p>";
    $test_insert = $conn->query("INSERT IGNORE INTO book_subject_map (book_id, subject_id) VALUES (1, 19)");
    if($test_insert) {
        echo "<p style='color: green;'>✅ Manual insert successful! Affected rows: ".$conn->affected_rows."</p>";
        if($conn->affected_rows == 0) {
            echo "<p style='color: orange;'>⚠️ This combination already exists (UNIQUE constraint)!</p>";
            // Check if it exists
            $check = $conn->query("SELECT * FROM book_subject_map WHERE book_id=1 AND subject_id=19");
            if($check && $check->num_rows > 0) {
                echo "<p style='color: red;'>Found existing row!</p>";
            } else {
                echo "<p style='color: red;'>No existing row found - something else is wrong!</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>❌ Manual insert failed: ".$conn->error."</p>";
    }
    
    // Show all assignments
    $sql = "SELECT bsm.*, b.title AS book_title, s.name AS subject_name 
            FROM book_subject_map bsm
            LEFT JOIN books b ON b.id = bsm.book_id
            LEFT JOIN subject s ON s.id = bsm.subject_id
            ORDER BY bsm.id DESC
            LIMIT 20";
    $result = $conn->query($sql);
    
    echo "<h3>Recent Assignments (Last 20):</h3>";
    if($result->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Book ID</th><th>Book Title</th><th>Subject ID</th><th>Subject Name</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>".$row['id']."</td>";
            echo "<td>".$row['book_id']."</td>";
            echo "<td>".htmlspecialchars($row['book_title'] ?? 'NULL')."</td>";
            echo "<td>".$row['subject_id']."</td>";
            echo "<td>".htmlspecialchars($row['subject_name'] ?? 'NULL')."</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No assignments found in book_subject_map table</p>";
    }
}

// Check subjects
echo "<h3>Available Subjects:</h3>";
$subjects = $conn->query("SELECT * FROM subject ORDER BY name ASC");
if($subjects->num_rows > 0) {
    echo "<ul>";
    while($s = $subjects->fetch_assoc()) {
        echo "<li>ID: {$s['id']} - Name: ".htmlspecialchars($s['name'])."</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No subjects found</p>";
}

// Check sample books
echo "<h3>Sample Books (First 5):</h3>";
$books = $conn->query("SELECT id, title, call_no FROM books LIMIT 5");
if($books->num_rows > 0) {
    echo "<ul>";
    while($b = $books->fetch_assoc()) {
        echo "<li>ID: {$b['id']} - Title: ".htmlspecialchars($b['title'])." - Call No: ".htmlspecialchars($b['call_no'])."</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No books found</p>";
}
?>
