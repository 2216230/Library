<?php 
	include 'includes/session.php';

	if(isset($_POST['id'])){
		$id = $_POST['id'];

		// Select all student fields including middle name
		$sql = "SELECT students.id AS studid, students.firstname, students.middlename, students.lastname, students.email, students.phone, students.course_id, course.code 
		        FROM students 
		        LEFT JOIN course ON course.id = students.course_id 
		        WHERE students.id = '$id'";
		$query = $conn->query($sql);
		$row = $query->fetch_assoc();

		echo json_encode($row);
	}
?>
