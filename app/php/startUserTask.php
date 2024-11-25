<?php
require_once '../../php/utils.php';

// Validate CSRF Token
if (!isset($_POST['csrf_token']) || !validateToken($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(["error" => "Invalid CSRF token"]);
    exit;
}

// Validate and sanitize input
if (!isset($_POST['taskId']) || !is_numeric($_POST['taskId'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid task ID"]);
    exit;
}

$taskId = intval($_POST['taskId']);
$employeeId = $_SESSION['employeeID'] ?? null; // Assuming employee ID is stored in session
$organizationId = $_SESSION['organization_id'] ?? null; // Assuming organization ID is stored in session

if (!$employeeId || !$organizationId) {
    http_response_code(401);
    echo json_encode(["error" => "User not authenticated"]);
    exit;
}

// Database connection
$C = connect();
if (!$C) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// Verify if user is assigned to the task
$assignedQuery = "
    SELECT 1
    FROM task_assigned_employees ta
    INNER JOIN tasks t ON ta.task_id = t.id
    WHERE ta.employee_id = ? AND ta.task_id = ? AND t.organization_id = ?
";

$assignedResult = sqlSelect($C, $assignedQuery, "iii", $employeeId, $taskId, $organizationId);

if ($assignedResult->num_rows === 0) {
    http_response_code(403);
    echo json_encode(["error" => "User is not assigned to this task"]);
    $C->close();
    exit;
}

// Insert into work_sessions table
$startTimeTimestamp = intval($_POST['startTime']) / 1000;
$startTime = date('Y-m-d H:i:s', $startTimeTimestamp);
$latitude = floatval($_POST['latitude']);
$longitude = floatval($_POST['longitude']);
$startSessionQuery = "
    INSERT INTO work_sessions (employee_id, organization_id, task_id, latitude, longitude, start_time)
    VALUES (?, ?, ?, ?, ?, ?)
";

$workSessionId = sqlInsert($C, $startSessionQuery, "iiidds", $employeeId, $organizationId, $taskId, $latitude, $longitude, $startTime);

if ($workSessionId === -1) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to start task"]);
    $C->close();
    exit;
}

// Store task details in session
$_SESSION['task_id'] = $taskId;
$_SESSION['work_session_id'] = $workSessionId;

// Fetch task details to store in session
$taskDetailsQuery = "SELECT * FROM tasks WHERE id = ?";
$taskDetailsResult = sqlSelect($C, $taskDetailsQuery, "i", $taskId);

if ($taskDetailsRow = $taskDetailsResult->fetch_assoc()) {
    $_SESSION['task_title'] = $taskDetailsRow['title'];
    $_SESSION['task_description'] = $taskDetailsRow['description'];
    $_SESSION['task_latitude'] = $taskDetailsRow['latitude'];
    $_SESSION['task_longitude'] = $taskDetailsRow['longitude'];
} else {
    http_response_code(500);
    echo json_encode(["error" => "Task details retrieval failed"]);
    $C->close();
    exit;
}

// Close connection
$C->close();

// Redirect to task page
echo json_encode(["success" => true]);
