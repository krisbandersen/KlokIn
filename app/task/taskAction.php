<?php
require_once '../../php/utils.php';

if (!isset($_SESSION['work_session_id']) || !isset($_SESSION['employeeID'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? null;
$workSessionId = $_SESSION['work_session_id'];
$C = connect();
if (!$C) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$response = ['success' => false]; // Default response

switch ($action) {
    case 'start':
        $result = sqlUpdate(
            $C,
            "UPDATE work_sessions SET start_time = NOW(), end_time = NULL WHERE id = ?",
            'i',
            $workSessionId
        );
        $response['success'] = $result;
        break;

    case 'pause':
        $result = sqlInsert(
            $C,
            "INSERT INTO break_sessions (work_session_id, break_start) VALUES (?, NOW())",
            'i',
            $workSessionId
        );
        if ($result > 0) {
            $response['success'] = true;
            $response['currentStatus'] = 'on_break';
            $response['breakStart'] = date('Y-m-d H:i:s'); // Include break start time
        }
        break;

    case 'resume':
        $result = sqlUpdate(
            $C,
            "UPDATE break_sessions SET break_end = NOW(), duration = TIMESTAMPDIFF(SECOND, break_start, NOW()) WHERE work_session_id = ? AND break_end IS NULL",
            'i',
            $workSessionId
        );
        $response['currentStatus'] = 'ongoing';
        $response['success'] = $result;
        break;

    case 'stop':
        // End any ongoing break
        $endBreak = sqlUpdate(
            $C,
            "UPDATE break_sessions 
            SET break_end = NOW(), 
                duration = TIMESTAMPDIFF(SECOND, break_start, NOW()) 
            WHERE work_session_id = ? AND break_end IS NULL",
            'i',
            $workSessionId
        );

        // Mark the session as ended
        $endSession = sqlUpdate(
            $C,
            "UPDATE work_sessions SET end_time = NOW() WHERE id = ?",
            'i',
            $workSessionId
        );

        // Mark the task as completed
        $updateTask = sqlUpdate(
            $C,
            "UPDATE tasks 
            SET completed = 1 
            WHERE id = ?",
            'i',
            $_SESSION['task_id']
        );

        // Set success response if all operations succeed
        $response['success'] = $endSession && $updateTask;
        break;
        

    default:
        $response['error'] = 'Invalid action';
}

echo json_encode($response);
$C->close();
?>
