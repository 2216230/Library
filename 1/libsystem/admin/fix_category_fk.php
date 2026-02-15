<?php
include 'includes/session.php';
include 'includes/conn.php';

if(!isset($_SESSION['admin'])){
    header('location: index.php');
    exit();
}

echo "<h2>Fixing book_category_map Foreign Key</h2>";

// Drop the incorrect foreign key
$sql = "ALTER TABLE book_category_map DROP FOREIGN KEY book_category_map_ibfk_1";
if($conn->query($sql)){
    echo "<p style='color: green;'>✓ Dropped incorrect foreign key book_category_map_ibfk_1</p>";
} else {
    echo "<p style='color: red;'>✗ Error dropping foreign key: " . $conn->error . "</p>";
}

// Add correct foreign key pointing to books table
$sql = "ALTER TABLE book_category_map 
        ADD CONSTRAINT book_category_map_ibfk_1 
        FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE";
        
if($conn->query($sql)){
    echo "<p style='color: green;'>✓ Added correct foreign key pointing to books table</p>";
} else {
    echo "<p style='color: red;'>✗ Error adding foreign key: " . $conn->error . "</p>";
}

echo "<p><a href='book.php'>← Back to Books</a></p>";
?>
