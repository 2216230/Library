<?php
session_start();
include 'conn.php';

if (!isset($_GET['query']) || trim($_GET['query']) === '') {
    echo "<p class='text-muted text-center'>Please enter a search term.</p>";
    exit;
}

$raw = trim($_GET['query']);
$search = $conn->real_escape_string($raw);

// Main query: get book info with available copies from book_copies table
// Optimized: prefix search (%search% → search%) for index usage, LIMIT 10 for performance
$sql = "
SELECT
  b.id,
  b.title,
  b.author,
  COALESCE(b.call_no, '') AS call_no,
  COALESCE(b.location, '') AS location,
  COALESCE(b.publish_date, '') AS publish_date,
  COALESCE(b.subject, '') AS book_subject,
  GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') AS categories,
  GROUP_CONCAT(DISTINCT s.name SEPARATOR ', ') AS subjects,
  'Book' AS type,
  (SELECT COUNT(*) FROM book_copies bc WHERE bc.book_id = b.id) AS total_copies,
  (SELECT COUNT(*) FROM book_copies bc WHERE bc.book_id = b.id AND bc.availability = 'available') AS available_copies
FROM books b
LEFT JOIN book_category_map bcm ON bcm.book_id = b.id
LEFT JOIN category c ON c.id = bcm.category_id
LEFT JOIN book_subject_map bsm ON bsm.book_id = b.id
LEFT JOIN subject s ON s.id = bsm.subject_id
WHERE
  b.title LIKE '$search%'
  OR b.author LIKE '$search%'
  OR b.subject LIKE '$search%'
  OR c.name LIKE '$search%'
  OR s.name LIKE '$search%'
GROUP BY
  b.id, b.title, b.author, COALESCE(b.call_no, ''), COALESCE(b.location, ''), COALESCE(b.publish_date, ''), COALESCE(b.subject, '')
UNION ALL
-- Calibre / digital entries
SELECT
  cb.id,
  cb.title,
  cb.author,
  '' AS call_no,
  'Available for download at the library via Calibre' AS location,
  DATE_FORMAT(cb.published_date, '%Y-%m-%d') AS publish_date,
  '' AS book_subject,
  NULL AS categories,
  cb.tags AS subjects,
  'Digital Library' AS type,
  1 AS total_copies,
  1 AS available_copies
FROM calibre_books cb
WHERE
  cb.title LIKE '$search%'
  OR cb.author LIKE '$search%'
  OR cb.tags LIKE '$search%'
ORDER BY title ASC
LIMIT 10
";

$result = $conn->query($sql);

if (!$result) {
    echo "<p class='text-danger small'>Query error: " . htmlspecialchars($conn->error) . "</p>";
    exit;
}

if ($result->num_rows === 0) {
    echo "<p class='text-muted mt-3 text-center'>No matching results found. You can ask the campus librarian for guidance.</p>";
    exit;
}

echo "<div class='list-group shadow-sm'>";

while ($row = $result->fetch_assoc()) {
    $total = intval($row['total_copies']);
    $available = intval($row['available_copies']);

    // status text and color based on available copies
    if ($available > 0) {
        $statusText = "Available ({$available}/{$total})";
        $statusClass = "text-success";
    } else {
        $statusText = "Unavailable";
        $statusClass = "text-danger";
    }

    // badges (categories, book subject, subjects/topics)
    $badges = '';
    if (!empty($row['categories'])) {
        $cats = array_map('trim', explode(',', $row['categories']));
        foreach ($cats as $c) {
            $badges .= "<span class='badge bg-light text-dark me-1 mb-1' style='max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;' title='".htmlspecialchars($c)."'>".htmlspecialchars($c)."</span>";
        }
    }
    if (!empty($row['book_subject'])) {
        $badges .= "<span class='badge bg-warning text-dark me-1 mb-1' style='max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;' title='Subject: ".htmlspecialchars($row['book_subject'])."'>".htmlspecialchars($row['book_subject'])."</span>";
    }
    if (!empty($row['subjects'])) {
        $subs = array_map('trim', explode(',', $row['subjects']));
        foreach ($subs as $s) {
            $badges .= "<span class='badge bg-info text-dark me-1 mb-1' style='max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;' title='".htmlspecialchars($s)."'>".htmlspecialchars($s)."</span>";
        }
    }

    $typeBadge = "<span class='badge bg-success'>" . htmlspecialchars($row['type']) . "</span>";

    // meta text
    $meta = [];
    if (!empty($row['author'])) $meta[] = "by " . htmlspecialchars($row['author']);
    if (!empty($row['call_no'])) $meta[] = "Call No: " . htmlspecialchars($row['call_no']);
    if (!empty($row['location'])) $meta[] = "Location: " . htmlspecialchars($row['location']);
    if (!empty($row['publish_date'])) $meta[] = "Published: " . htmlspecialchars($row['publish_date']);
    $metaText = implode(' · ', $meta);

    // clickable to catalog search
    
    $searchParam = urlencode($row['title']);

    // clickable to catalog search
    $searchParam = urlencode($row['title']);
    
    if (isset($_SESSION['student']) || isset($_SESSION['faculty']) || isset($_SESSION['admin'])) {
        // User is logged in - make it clickable
        echo "
        <a href='catalog.php?search=$searchParam' class='text-decoration-none text-reset'>
          <div class='list-group-item list-group-item-action d-flex justify-content-between align-items-start flex-wrap'>
            <div class='flex-grow-1 me-3'>
              <h5 class='fw-bold mb-1 text-success'>".htmlspecialchars($row['title'])."</h5>
              <small class='text-muted d-block'>
                {$metaText}
                <div class='d-flex flex-wrap gap-1 mt-1'>
                  {$badges}
                  {$typeBadge}
                </div>
              </small>
            </div>
            <div class='text-end'>
              <div class='fw-bold {$statusClass} mt-2 mt-sm-0'>{$statusText}</div>
              <small class='text-muted d-block'>Total copy/ies: {$total}</small>
            </div>
          </div>
        </a>
        ";
    } else {
        // User is not logged in - show non-clickable version
        echo "
        <div class='list-group-item d-flex justify-content-between align-items-start flex-wrap'>
          <div class='flex-grow-1 me-3'>
            <h5 class='fw-bold mb-1 text-success'>".htmlspecialchars($row['title'])."</h5>
            <small class='text-muted d-block'>
              {$metaText}
              <div class='d-flex flex-wrap gap-1 mt-1'>
                {$badges}
                {$typeBadge}
              </div>
            </small>
          </div>
          <div class='text-end'>
            <div class='fw-bold {$statusClass} mt-2 mt-sm-0'>{$statusText}</div>
            <small class='text-muted d-block'>Total copy/ies: {$total}</small>
            <small class='text-danger d-block mt-1'><i class='fas fa-lock'></i> Login to access</small>
          </div>
        </div>
        ";
    }
}

echo "</div>";

// helper css
echo "
<style>
.list-group-item .badge { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.list-group-item .flex-wrap { max-width: calc(100% - 140px); }
</style>
";
?>
