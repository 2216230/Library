<?php
include 'includes/conn.php';

$query = $_GET['query'] ?? '';
if (!$query) exit;

// Select only books that are not currently borrowed
$sql = "SELECT b.id, b.call_no, b.title, b.isbn, b.author, b.publish_date
        FROM books b
        LEFT JOIN borrow_transactions bt 
               ON b.id = bt.book_id AND bt.status = 'borrowed'
        WHERE (b.title LIKE '%$query%' OR b.isbn LIKE '%$query%' OR b.call_no LIKE '%$query%')
          AND bt.id IS NULL
        ORDER BY b.title ASC
        LIMIT 10";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $title = $row['title'];
        $details = "Call No: {$row['call_no']} | ISBN: {$row['isbn']} | Author: {$row['author']} | Published: {$row['publish_date']}";
        echo "<a href='#' class='list-group-item list-group-item-action book-item'
                data-id='$id' data-title='$title' data-details='$details'>
                $title<br><small>$details</small>
              </a>";
    }
} else {
    echo "<div class='list-group-item text-muted'>No books available</div>";
}
?>
