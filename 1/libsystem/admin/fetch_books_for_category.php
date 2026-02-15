<?php
include 'includes/conn.php';

if (isset($_POST['query'])) {
    $query = trim($_POST['query']);
    $category_id = intval($_POST['category_id']);

    // find books matching the search query
    $stmt = $conn->prepare("
        SELECT b.id, b.title, b.author
        FROM books b
        WHERE b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?
        ORDER BY b.title ASC
    ");
    $like = "%$query%";
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($book = $result->fetch_assoc()) {
            // check if this book is already assigned
            $check = $conn->prepare("SELECT * FROM book_category_map WHERE book_id = ? AND category_id = ?");
            $check->bind_param("ii", $book['id'], $category_id);
            $check->execute();
            $is_assigned = $check->get_result()->num_rows > 0;
            $check->close();

            echo "<tr>";
            echo "<td><input type='checkbox' class='book-checkbox-cat' value='" . htmlspecialchars($book['id']) . "' " . ($is_assigned ? "checked disabled" : "") . "></td>";
            echo "<td>" . htmlspecialchars($book['title']) . "</td>";
            echo "<td>" . htmlspecialchars($book['author']) . "</td>";
            echo "<td>" . ($is_assigned ? "<span class='text-success'>Assigned</span>" : "<span class='text-muted'>Available</span>") . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='4' class='text-center text-muted'>No matching books found.</td></tr>";
    }

    $stmt->close();
}
?>
