<?php
include 'includes/session.php';
include 'includes/conn.php';

if (isset($_POST['query']) && isset($_POST['subject_id'])) {
    $query = "%" . trim($_POST['query']) . "%";
    $subject_id = intval($_POST['subject_id']);

    $sql = "SELECT b.id, b.call_no, b.title, b.author, b.publish_date,
                   IF(m.id IS NULL, 0, 1) AS is_assigned
            FROM books b
            LEFT JOIN book_subject_map m 
              ON b.id = m.book_id AND m.subject_id = ?
            WHERE b.call_no LIKE ? 
               OR b.title LIKE ?
               OR b.author LIKE ?
               OR b.publish_date LIKE ?
            ORDER BY b.title ASC
            LIMIT 50";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $subject_id, $query, $query, $query, $query);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $checked = $row['is_assigned'] ? 'checked' : '';

            // Add "highlighted-row" CSS class if assigned
            $highlightClass = $row['is_assigned'] ? 'highlighted-row' : '';

            echo "
            <tr class='{$highlightClass}'>
              <td>
                <input type='checkbox' class='book-checkbox' 
                       value='{$row['id']}'
                       data-call-no='" . htmlspecialchars($row['call_no'], ENT_QUOTES) . "'
                       data-title='" . htmlspecialchars($row['title'], ENT_QUOTES) . "'
                       data-published='" . htmlspecialchars($row['publish_date'], ENT_QUOTES) . "'
                       {$checked}>
              </td>
              <td>" . htmlspecialchars($row['call_no']) . "</td>
              <td>" . htmlspecialchars($row['title']) . "</td>
              <td>" . htmlspecialchars($row['author']) . "</td>
              <td>" . htmlspecialchars($row['publish_date']) . "</td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='5' class='text-center'>No books found.</td></tr>";
    }
} else {
    echo "<tr><td colspan='5' class='text-center text-danger'>Invalid request.</td></tr>";
}
?>
