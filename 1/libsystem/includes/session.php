<?php
session_start();

// User variables for templates
$currentUser = [];
$userType = '';

// Initialize individual user variables
$student = [];
$faculty = [];
$admin = [];

if(isset($_SESSION['admin'])){
    include 'conn.php';
    $sql = "SELECT * FROM admin WHERE id = '".$_SESSION['admin']."'";
    $query = $conn->query($sql);
    $currentUser = $query->fetch_assoc();
    $admin = $currentUser; // Set admin variable
    $userType = 'admin';
} 
else if(isset($_SESSION['student'])){
    include 'conn.php';
    $sql = "SELECT * FROM students WHERE id = '".$_SESSION['student']."'";
    $query = $conn->query($sql);
    $currentUser = $query->fetch_assoc();
    $student = $currentUser; // Set student variable
    $userType = 'student';
} 
else if(isset($_SESSION['faculty'])){
    include 'conn.php';
    $sql = "SELECT * FROM faculty WHERE id = '".$_SESSION['faculty']."'";
    $query = $conn->query($sql);
    $currentUser = $query->fetch_assoc();
    $faculty = $currentUser; // Set faculty variable
    $userType = 'faculty';
}
?>