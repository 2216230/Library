<?php
// =====================================================
// CATALOG SEARCH API - Server-side search for catalog.php
// =====================================================
// This endpoint provides server-side search matching
// book.php and calibre_books.php search logic
// Includes: title, author, ISBN, identifiers, tags
// Plus: available copies count
// =====================================================

include 'includes/session.php';
include 'includes/conn.php';
include 'includes/user_activity_helper.php';

// Ensure user activity table exists
ensureUserActivityTable($conn);

header('Content-Type: application/json');

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : ''; // 'physical' or 'digital'

if (empty($search) || strlen($search) < 2) {
    echo json_encode(['success' => false, 'message' => 'Search query too short']);
    exit;
}

// Log search activity
if($userType == 'student' || $userType == 'faculty') {
    $user_id = $userType == 'student' ? ($currentUser['student_id'] ?? $_SESSION['student']) : ($currentUser['faculty_id'] ?? $_SESSION['faculty']);
    logUserActivity($conn, $user_id, $userType, 'SEARCH', "Searched for: $search", 'books', '');
}

$searchSafe = $conn->real_escape_string($search);
$results = [];

// ✅ Physical Books Search (matching book.php)
if ($type === '' || $type === 'physical') {
    $physical_sql = "
    SELECT 
        'physical' as type,
        b.id,
        b.title,
        b.author,
        b.isbn,
        b.call_no,
        b.location,
        b.subject,
        GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') AS categories,
        (SELECT COUNT(*) FROM book_copies bc WHERE bc.book_id = b.id) AS total_copies,
        (SELECT COUNT(*) FROM book_copies bc WHERE bc.book_id = b.id AND bc.availability = 'available') AS available_copies,
        'Physical Book' as item_type
    FROM books b
    LEFT JOIN book_category_map bcm ON b.id = bcm.book_id
    LEFT JOIN category c ON bcm.category_id = c.id
    WHERE b.title LIKE '$searchSafe%' 
       OR b.author LIKE '$searchSafe%' 
       OR b.isbn LIKE '$searchSafe%'
       OR b.subject LIKE '$searchSafe%'
       OR c.name LIKE '$searchSafe%'
    GROUP BY b.id
    LIMIT 20
    ";
    
    $physical_result = $conn->query($physical_sql);
    while ($row = $physical_result->fetch_assoc()) {
        $results[] = $row;
    }
}

// ✅ Digital Books Search (matching calibre_books.php)
if ($type === '' || $type === 'digital') {
    $digital_sql = "
    SELECT 
        'digital' as type,
        cb.id,
        cb.title,
        cb.author,
        cb.identifiers as isbn,
        '' as call_no,
        'Digital Collection' as location,
        cb.tags AS categories,
        1 AS total_copies,
        1 AS available_copies,
        'Digital Book' as item_type
    FROM calibre_books cb
    WHERE cb.title LIKE '$searchSafe%' 
       OR cb.author LIKE '$searchSafe%' 
       OR cb.identifiers LIKE '$searchSafe%'
       OR cb.tags LIKE '$searchSafe%'
    LIMIT 20
    ";
    
    $digital_result = $conn->query($digital_sql);
    while ($row = $digital_result->fetch_assoc()) {
        $results[] = $row;
    }
}

// Sort by available copies DESC, then by type (physical first)
usort($results, function($a, $b) {
    if ($a['available_copies'] !== $b['available_copies']) {
        return $b['available_copies'] <=> $a['available_copies'];
    }
    return ($a['type'] === 'physical' ? 0 : 1) <=> ($b['type'] === 'physical' ? 0 : 1);
});

echo json_encode([
    'success' => true,
    'count' => count($results),
    'results' => array_slice($results, 0, 20)
]);
?>
