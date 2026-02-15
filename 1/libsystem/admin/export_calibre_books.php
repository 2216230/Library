<?php
include 'includes/session.php';
include 'includes/conn.php';

if(!isset($_SESSION['admin'])){
    header('location: index.php');
    exit();
}

// Get all e-books
$sql = "SELECT 
            id,
            identifiers,
            author,
            title,
            published_date,
            tags,
            external_link
        FROM calibre_books
        ORDER BY title ASC";

$result = $conn->query($sql);

// Set headers for CSV download
$filename = 'ebook_collection_' . date('Y-m-d_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Create output
$output = fopen('php://output', 'w');

// Add BOM for Excel UTF-8 compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write title row
fputcsv($output, array('E-BOOK COLLECTION INVENTORY'));
fputcsv($output, array(''));  // Empty row for spacing

// Write header row
fputcsv($output, array('Identifier', 'Title', 'Author', 'Published Date', 'Tags', 'External Link'));

// Fetch and write data
if($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        fputcsv($output, array(
            $row['identifiers'] ?? '',
            $row['title'] ?? '',
            $row['author'] ?? '',
            $row['published_date'] ?? '',
            $row['tags'] ?? '',
            $row['external_link'] ?? ''
        ));
    }
}

fclose($output);
$conn->close();
exit();
?>
