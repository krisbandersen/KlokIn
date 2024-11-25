<?php
require_once '../../php/utils.php';

// Ensure the user is logged in
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['task_id'])) {
    header('Location: /login.php'); // Redirect to login if not authenticated
    exit;
}

$taskId = $_SESSION['task_id'];
$taskTitle = $_SESSION['task_title'];
$workSessionId = $_SESSION['work_session_id'];
$userId = $_SESSION['employeeID'];

$C = connect();
if (!$C) {
    die("Database connection failed.");
}

// Fetch current status of the work session
$currentStatus = 'ongoing'; // Default
$breaks = [];

$result = sqlSelect(
    $C,
    "SELECT end_time FROM work_sessions WHERE id = ?",
    'i',
    $workSessionId
);

if ($row = $result->fetch_assoc()) {
    if ($row['end_time'] !== null) {
        $currentStatus = 'stopped';
    }
}

// Fetch ongoing break details if any
$result = sqlSelect(
    $C,
    "SELECT id, break_start FROM break_sessions WHERE work_session_id = ? AND break_end IS NULL",
    'i',
    $workSessionId
);

if ($break = $result->fetch_assoc()) {
    $currentStatus = 'on_break';
    $breaks = $break;
}

$breakStartTime = null;

if ($currentStatus === 'on_break') {
    $breakStartTime = $break['break_start']; // Retrieve the break start time
}

$result->close();
$C->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task: <?= htmlspecialchars($taskTitle) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 50px;
        }
        button {
            font-size: 18px;
            padding: 10px 20px;
            margin: 10px;
            cursor: pointer;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <h1>Task: <?= htmlspecialchars($taskTitle) ?></h1>

    <div id="controls">
        <button id="start-btn" class="<?= $currentStatus !== 'stopped' ? 'hidden' : '' ?>">Start Task</button>
        <button id="pause-btn" class="<?= $currentStatus === 'ongoing' ? '' : 'hidden' ?>">Pause Task</button>
        <button id="resume-btn" class="<?= $currentStatus === 'on_break' ? '' : 'hidden' ?>">Resume Task</button>
        <button id="stop-btn" class="<?= $currentStatus === 'stopped' ? 'hidden' : '' ?>">Stop Task</button>
    </div>

    <div id="break-info" class="<?= $currentStatus === 'on_break' ? '' : 'hidden' ?>">
        <p>Break Duration: <span id="break-duration">00:00:00</span></p>
    </div>

    <script>
    // Initial setup based on the current status
    const currentStatus = "<?= $currentStatus ?>";
    const breakStartTime = <?= json_encode($breakStartTime) ?>;

    // Elements for buttons
    const startBtn = document.getElementById('start-btn');
    const pauseBtn = document.getElementById('pause-btn');
    const resumeBtn = document.getElementById('resume-btn');
    const stopBtn = document.getElementById('stop-btn');

    let breakInterval;

    // Update button visibility based on task status
    function updateButtonVisibility(status) {
        // Show/hide buttons depending on task status
        startBtn.classList.toggle('hidden', status !== 'stopped');
        pauseBtn.classList.toggle('hidden', status !== 'ongoing');
        resumeBtn.classList.toggle('hidden', status !== 'on_break');
        stopBtn.classList.toggle('hidden', status === 'stopped');
    }

    // Handle server requests (start, pause, resume, stop)
    function sendRequest(action) {
        const formData = new FormData();
        formData.append('action', action);

        return fetch('taskAction.php', {
            method: 'POST',
            body: formData,
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // When task is resumed or paused, we should update the visibility of the buttons
                    updateButtonVisibility(data.currentStatus);

                    if (data.currentStatus === 'on_break' && data.breakStart) {
                        startBreakTimer(new Date(data.breakStart));
                    } else {
                        stopBreakTimer();
                    }
                    return data;
                } else {
                    alert(data.error || 'An error occurred');
                    throw new Error(data.error);
                }
            })
            .catch(err => alert('Failed to perform action: ' + err));
    }

    // Start the break timer
    function startBreakTimer(breakStart) {
        const breakInfo = document.getElementById('break-info');
        const breakDurationDisplay = document.getElementById('break-duration');

        breakInfo.classList.remove('hidden');

        function updateBreakDuration() {
            const now = new Date();
            const elapsed = Math.floor((now - breakStart) / 1000);

            const hours = String(Math.floor(elapsed / 3600)).padStart(2, '0');
            const minutes = String(Math.floor((elapsed % 3600) / 60)).padStart(2, '0');
            const seconds = String(elapsed % 60).padStart(2, '0');

            breakDurationDisplay.textContent = `${hours}:${minutes}:${seconds}`;
        }

        updateBreakDuration();
        breakInterval = setInterval(updateBreakDuration, 1000);
    }

    // Stop the break timer
    function stopBreakTimer() {
        const breakInfo = document.getElementById('break-info');
        clearInterval(breakInterval);
        breakInterval = null;
        breakInfo.classList.add('hidden');
    }

    // Event Listeners for button actions
    startBtn.addEventListener('click', () => sendRequest('start'));
    pauseBtn.addEventListener('click', () => sendRequest('pause'));
    resumeBtn.addEventListener('click', () => {
        sendRequest('resume');
        stopBreakTimer();
    });
    stopBtn.addEventListener('click', () => {
        sendRequest('stop').then(() => {
            window.location.href = "../index.php"; // Redirect to index.php after stopping the task
        });
    });

    // Update button visibility when the page loads based on current status
    window.addEventListener('DOMContentLoaded', () => {
        updateButtonVisibility(currentStatus);

        // Start break timer if we are on a break
        if (currentStatus === 'on_break' && breakStartTime) {
            startBreakTimer(new Date(breakStartTime));
        }
    });
    </script>
</body>
</html>
