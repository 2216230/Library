<?php
	// ========================================
	// LOCAL DEVELOPMENT (COMMENTED OUT)
	// ========================================
	$conn = new mysqli('localhost', 'root', '', 'libsystem5');
	
	// ========================================
	// INFINITYFREE HOSTING (ACTIVE)
	// ========================================
	//$conn = new mysqli('sql100.infinityfree.com', 'if0_40349242', 'mAjimba12', 'if0_40349242_libsystem_db');

	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}
	
	// Disable ONLY_FULL_GROUP_BY for compatibility with legacy queries
	$conn->query("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
	
?>
