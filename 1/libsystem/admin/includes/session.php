<?php
	session_start();
	include '../includes/conn.php';

	// Allow both admin and superadmin sessions
	if((!isset($_SESSION['admin']) || trim($_SESSION['admin']) == '') && (!isset($_SESSION['superadmin']) || trim($_SESSION['superadmin']) == '')){
		header('location: ../index.php');
		exit();
	}
 
	
	// Get the admin ID (from either admin or superadmin session)
	$admin_id = isset($_SESSION['admin']) ? $_SESSION['admin'] : (isset($_SESSION['superadmin']) ? $_SESSION['superadmin'] : null);
	$user_type = isset($_SESSION['admin']) ? 'admin' : 'superadmin';

	$sql = "SELECT * FROM admin WHERE id = '".$admin_id."'";
	$query = $conn->query($sql);
	$user = $query->fetch_assoc();
	
?>