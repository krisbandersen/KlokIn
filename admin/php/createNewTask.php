<?php
require_once '../../php/utils.php';

$errors = [];


// Validate POST data
if (!isset($_POST['title']) || strlen($_POST['title']) > 255) {
    $errors[] = 1; // Invalid title
}
if (!isset($_POST['description']) || strlen($_POST['description']) > 1000) {
    $errors[] = 2; // Invalid description
}
if (!isset($_POST['latitude']) || !is_numeric($_POST['latitude'])) {
    $errors[] = 3; // Invalid latitude
}
if (!isset($_POST['longitude']) || !is_numeric($_POST['longitude'])) {
    $errors[] = 4; // Invalid longitude
}
if (!isset($_POST['startTime']) && !isset($_POST['endTime'])) {
    $errors[] = 5; // Invalid deadline
}
if (!isset($_POST['csrf_token']) || !validateToken($_POST['csrf_token'])) {
    $errors[] = 6; // Invalid CSRF token
}

if (count($errors) === 0) {
    $C = connect();
    if ($C) {
        if (isset($_SESSION['organization_id'])) {
            $org_id = $_SESSION['organization_id'];  
            $startTimeTimestamp = intval($_POST['startTime']) / 1000;
            $endTimeTimestamp = intval($_POST['endTime']) / 1000;

            try {
                $task_id = sqlInsert(
                    $C,
                    'INSERT INTO tasks (organization_id, title, description, latitude, longitude, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?, ?)',
                    'issssss',
                    $org_id,
                    $_POST['title'],
                    $_POST['description'],
                    $_POST['latitude'],
                    $_POST['longitude'],
                    date('Y-m-d H:i:s', $startTimeTimestamp),
                    date('Y-m-d H:i:s', $endTimeTimestamp)
                );

                if ($task_id === -1) { // Check if insertion failed
                    throw new Exception('Error during task creation');
                }

                // Assign employees to task if provided
                if (isset($_POST['assignedEmployees'])) {
                    $assignedEmployees = json_decode($_POST['assignedEmployees'], true); // Convert JSON to array

                    if (!empty($assignedEmployees)) {
                        foreach ($assignedEmployees as $employee_id) {
                            sqlInsert($C, 'INSERT INTO task_assigned_employees (task_id, employee_id, assigned_at) VALUES (?, ?, NOW())', 
                                       'ii', $task_id, $employee_id);
                        }
                    }
                }

                $errors[] = 0; // success

            } catch (Exception $e) {
                $errors[] = 7; // Custom error code for task creation failure
                error_log($e->getMessage()); // Log the error message for debugging
            }
        } else {
            $errors[] = 8; // No organization found in session
        }
    } else {
        $errors[] = 9; // Database connection failed
    }
}

echo json_encode( value: $errors);
