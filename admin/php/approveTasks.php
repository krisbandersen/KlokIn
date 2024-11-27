<?php 
require_once '../../php/utils.php';

$response = [
    'success' => false,
    'message' => 'An error occurred.',
    'errors' => [],
];

// Validate the tasks input
if (!isset($_POST['tasks']) || empty($_POST['tasks'])) {
    $response['errors'][] = 'Missing or invalid tasks';
    echo json_encode($response);
    exit;
}

// Decode the JSON data into an array
$tasks = json_decode($_POST['tasks'], true);

// Check if JSON decoding is successful
if (json_last_error() !== JSON_ERROR_NONE) {
    $response['errors'][] = 'Failed to decode tasks data.';
    echo json_encode($response);
    exit;
}

// Ensure all task IDs are integers
$tasks = array_map('intval', $tasks); // This will convert all task IDs to integers

// Check that tasks is an array
if (!is_array($tasks) || count($tasks) === 0) {
    $response['errors'][] = 'No tasks selected for approval.';
    echo json_encode($response);
    exit;
}

// If everything is valid, proceed with task approval logic
$C = connect();
if ($C) {
    // Get employee ID (make sure it's passed and validated)
    $employeeId = isset($_POST['employeeId']) ? intval($_POST['employeeId']) : 0;
    if ($employeeId === 0) {
        $response['errors'][] = 'Invalid employee ID';
        echo json_encode($response);
        exit;
    }

    // Check if the admin ID is stored in session correctly
    if (!isset($_SESSION['userID'])) {
        $response['errors'][] = 'Admin user ID is missing from session.';
        echo json_encode($response);
        exit;
    }

    // Set approved flag for tasks
    try {
        $updatedCount = 0;  // To track how many rows were updated
        foreach ($tasks as $taskId) {
            $sql = 'UPDATE work_sessions SET approved = 1, approved_by_admin_id = ? WHERE id = ? AND employee_id = ?';
            $result = sqlUpdate(
                $C,
                $sql,
                'iii', 
                $_SESSION['userID'],  // Assuming admin ID is stored in session
                $taskId,
                $employeeId
            );

            // Check if the update was successful
            if ($result) {
                $updatedCount++;
            }
        }

        // Check if any rows were updated
        if ($updatedCount > 0) {
            $response['success'] = true;
            $response['message'] = 'Tasks successfully approved!';
        } else {
            $response['errors'][] = 'No matching tasks found to approve.';
        }
    } catch (Exception $e) {
        $response['errors'][] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['errors'][] = 'Database connection failed';
}

echo json_encode($response);
