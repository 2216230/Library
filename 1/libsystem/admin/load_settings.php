<?php
include 'includes/session.php';
include 'includes/conn.php';
header('Content-Type: application/json');

// Fetch from correct columns: active_academic_year and active_semester
$sql = "
SELECT 
    s.active_academic_year as academic_year,
    s.active_semester as semester,
    ay.year_start,
    ay.year_end
FROM settings s
LEFT JOIN academic_years ay ON s.active_academic_year = ay.id
WHERE s.id=1
";

$result = $conn->query($sql);

if(!$result) {
    // Query failed
    echo json_encode([
        'academic_year' => 0,
        'semester' => '-',
        'academic_year_label' => 'Database Error',
        'semester_label' => '-'
    ]);
    exit;
}

$data = $result->fetch_assoc();

if(!$data) {
    // No settings record found
    echo json_encode([
        'academic_year' => 0,
        'semester' => '-',
        'academic_year_label' => 'No A.Y. Selected',
        'semester_label' => '-'
    ]);
    exit;
}

// Construct academic year display (e.g., "2025-2026")
$ay_display = '-';
if($data['year_start'] && $data['year_end']) {
    $ay_display = $data['year_start'] . '-' . $data['year_end'];
} elseif($data['academic_year']) {
    $ay_display = 'A.Y. ' . $data['academic_year'];
}

// Settings found - format response properly
$response = [
    'academic_year' => $data['academic_year'] ?: 0,
    'semester' => $data['semester'],  // Return actual value (null or the semester)
    'academic_year_label' => $ay_display,
    'semester_label' => $data['semester'] ?: '-'
];

echo json_encode($response);
?>
