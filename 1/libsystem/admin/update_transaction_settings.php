<?php
include 'includes/session.php';
include 'includes/conn.php';

if(isset($_POST['update_settings'])){
    $academic_year_id = $_POST['academic_year'];
    $semester = $_POST['semester'];

    // Save or update in a table called transaction_settings (create it if not exists)
    $conn->query("
        INSERT INTO transaction_settings (id, academic_year_id, semester)
        VALUES (1, $academic_year_id, '$semester')
        ON DUPLICATE KEY UPDATE academic_year_id=$academic_year_id, semester='$semester'
    ");

    $_SESSION['success'] = "Transaction settings updated successfully.";
    header("Location: transactions.php");
}
?>
