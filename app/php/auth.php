<?php
require_once '../../php/utils.php';

if(isset($_POST['email']) && isset($_POST['employeenumber']) && isset($_POST['csrf_token']) && validateToken($_POST['csrf_token'])) {
	$email = $_POST['email'];
	$employee_number = $_POST['employeenumber'];

	$C = connect();
	if($C) {
		$res = sqlSelect($C, 'SELECT employees.id, employees.organization_id, employees.firstname, employees.lastname 
			FROM employees 
			WHERE employees.email=? 
			AND employees.employee_number=?', 'si', $email, $employee_number);
		
		if($res && $res->num_rows === 1) {
			$employee = $res->fetch_assoc();

			$orgRes = sqlSelect($C, 'SELECT organizations.id, organizations.label_name, organizations.cvr, organizations.phone 
			FROM organizations 
			WHERE organizations.id=?', 'i', $employee['organization_id']);

			$organization = $orgRes->fetch_assoc();

			// Save user information
			$_SESSION['loggedin'] = true;
			$_SESSION['isEmployee'] = true;
			$_SESSION['employeeID'] = $employee['id'];
			$_SESSION['organization_id'] = $employee['organization_id'];
			$_SESSION['firstname'] = $employee['firstname'];
			$_SESSION['lastname'] = $employee['lastname'];
			$_SESSION['email'] = $email;

			// Save organization information
			$_SESSION['org_id'] = $organization["id"];
			$_SESSION['org_label_name'] = $organization["label_name"];
			$_SESSION['org_cvr'] = $organization["cvr"];
			$_SESSION['org_phone'] = $organization["phone"];
			echo 0;
			$res->free_result();
		} else {
			echo 1; // Invalid email or employee number
		}
		$C->close();
	} else {
		echo 2; // Database connection error
	}
} else {
	echo 3; // Missing or invalid data
}