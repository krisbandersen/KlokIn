<?php
require_once '../../php/utils.php';

if (isset($_POST['csrf_token']) && validateToken($_POST['csrf_token'])) {
    $employeeId = $_SESSION["employeeID"]; // Hent medarbejder ID fra sessionen, eller modtag det via POST

    // Opret forbindelse til databasen
    $C = connect();
    if (!$C) {
        echo json_encode(['error' => 'Could not connect to the database']);
        exit();
    }

    // SQL-forespørgsel for at hente opgaver tildelt medarbejderen
    $query = "
        SELECT tasks.*
        FROM tasks
        JOIN task_assigned_employees ON tasks.id = task_assigned_employees.task_id
        WHERE task_assigned_employees.employee_id = ?
    ";

    $tasks = sqlSelect($C, $query, 'i', $employeeId);

    // Hvis opgaverne blev hentet korrekt
    if ($tasks) {
        $taskData = [];
        while ($task = $tasks->fetch_assoc()) {
            $taskData[] = $task;
        }
        echo json_encode(['tasks' => $taskData]);
    } else {
        echo json_encode(['error' => 'No tasks found for this employee']);
    }

    $C->close();
} else {
    // Ugyldigt CSRF-token eller manglende parametre
    echo json_encode(['error' => 'Invalid request']);
}
