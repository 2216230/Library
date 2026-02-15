<?php
include 'includes/session.php';
include 'includes/conn.php';

if(!isset($_SESSION['admin'])){
    header('location: index.php');
    exit();
}

// Get all books with their copies
$sql = "SELECT 
            b.id,
            b.call_no,
            b.title,
            b.author,
            b.publish_date,
            b.section,
            IFNULL((
                SELECT GROUP_CONCAT(c.name SEPARATOR ', ')
                FROM book_category_map bcm
                JOIN category c ON bcm.category_id = c.id
                WHERE bcm.book_id = b.id
            ), '') AS categories,
            (SELECT COUNT(*) FROM book_copies bc WHERE bc.book_id = b.id) AS total_copies
        FROM books b
        ORDER BY b.call_no ASC";

$result = $conn->query($sql);

// Set headers for CSV download
$filename = 'book_collection_' . date('Y-m-d_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Create output
$output = fopen('php://output', 'w');

// Add BOM for Excel UTF-8 compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write title row
fputcsv($output, array('BOOK LISTS INVENTORY'));
fputcsv($output, array(''));  // Empty row for spacing

// Write header row
fputcsv($output, array('Call No.', 'Title', 'Author', 'Year', 'Copy Number', 'Status'));

// Fetch and write data
if($result && $result->num_rows > 0) {
    while($book = $result->fetch_assoc()) {
        // Get all copies for this book
        $copies_sql = "SELECT copy_number, availability FROM book_copies WHERE book_id = " . $book['id'] . " ORDER BY copy_number ASC";
        $copies_result = $conn->query($copies_sql);
        
        if($copies_result && $copies_result->num_rows > 0) {
            // Write one row per copy, with book details in first copy only
            $first_copy = true;
            while($copy = $copies_result->fetch_assoc()) {
                // Map availability status to user-friendly names
                $status_map = array(
                    'available' => 'Available (Good)',
                    'borrowed' => 'Borrowed',
                    'damaged' => 'Damaged',
                    'lost' => 'Lost',
                    'repair' => 'Under Repair',
                    'overdue' => 'Overdue'
                );
                $status_display = isset($status_map[$copy['availability']]) ? $status_map[$copy['availability']] : ucfirst($copy['availability']);
                
                if($first_copy) {
                    fputcsv($output, array(
                        $book['call_no'],
                        $book['title'],
                        $book['author'] ?? '',
                        $book['publish_date'] ?? '',
                        'c.' . $copy['copy_number'],
                        $status_display
                    ));
                    $first_copy = false;
                } else {
                    // For subsequent copies, blank out book details (creates merged cell effect in Excel)
                    fputcsv($output, array(
                        '',
                        '',
                        '',
                        '',
                        'c.' . $copy['copy_number'],
                        $status_display
                    ));
                }
            }
        } else {
            // Book with no copies
            fputcsv($output, array(
                $book['call_no'],
                $book['title'],
                $book['author'] ?? '',
                $book['publish_date'] ?? '',
                '(no copies)',
                'N/A'
            ));
        }
    }
}

fclose($output);
$conn->close();
exit();
?>
