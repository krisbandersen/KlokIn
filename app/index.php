<?php
    error_reporting(E_ALL);
    require_once '../php/utils.php'; 
    ob_start();
    session_start();

    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['isEmployee']) || $_SESSION['isEmployee'] !== true) {
        header("Location: /app/login.php");
        exit;
    }

    function setInterval($f, $milliseconds)
    {
        $seconds=(int)$milliseconds/1000;
        while(true)
        {
            $f();
            sleep($seconds);
        }
    }

    function getAllUserTasks() {
        if (isset($_SESSION["employeeID"])) {
            $employeeId = $_SESSION["employeeID"]; // Get employee ID from session
            // Connect to the database
            $C = connect();
            if (!$C) {
                return json_encode(['error' => 'Could not connect to the database']);
            }
    
            // SQL query to fetch tasks assigned to the employee
            $query = "
                SELECT tasks.*
                FROM tasks
                JOIN task_assigned_employees ON tasks.id = task_assigned_employees.task_id
                WHERE task_assigned_employees.employee_id = ?
            ";
    
            $tasks = sqlSelect($C, $query, 'i', $employeeId);
    
            if ($tasks) {
                $taskData = [];
                while ($task = $tasks->fetch_assoc()) {
                    $taskData[] = $task;
                }
                $C->close();
                return json_encode(['tasks' => $taskData]);
            } else {
                $C->close();
                return json_encode(['error' => 'No tasks found for this employee']);
            }
        } else {
            return json_encode(['error' => 'Invalid request']);
        }
    }    

    function getUserTaskByDate($timestamp) {
        $allTasksJson = getAllUserTasks();
        $allTasksData = json_decode($allTasksJson, true);
    
        if (isset($allTasksData['error'])) {
            return $allTasksJson; // Return the error response
        }
    
        $date = date('Y-m-d', strtotime($timestamp));
    
        $filteredTasks = array_filter($allTasksData['tasks'], function($task) use ($date) {
            return isset($task['start_time']) && date('Y-m-d', strtotime($task['start_time'])) === $date;
        });
    
        return json_encode(['tasks' => array_values($filteredTasks)]);
    }
    
    function calculateDuration($startTime, $endTime) {
        $startDate = strtotime($startTime);
        $endDate = strtotime($endTime);

        $differenceInSeconds = $endDate - $startDate;

        if ($differenceInSeconds < 0) {
            return "00:00";
        }
    
        $hours = floor($differenceInSeconds / 3600);
        $minutes = floor(($differenceInSeconds % 3600) / 60);
    
        return sprintf('%02d:%02d', $hours, $minutes);
    }
    
    //if (!isMobile()) {
    //    header("Location: /index.php");
    //    exit;
    //}

    // Get the current page from URL, default to 'dashboard'
    $page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>

<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
	<meta name="csrf_token" content="<?php echo createToken(); ?>" />
    <meta name='viewport' content='width=device-width, initial-scale=1.0, viewport-fit=cover'>
    <title>KlokIn</title>
    <link rel='manifest' href='/site.webmanifest'>
    <base href="/app/">
    <link rel="canonical" href="http://localhost/app/">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.css" rel="stylesheet" />

    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
        }
        #map {
            height: 100vh;
            width: 100vw;
        }
        #bottom-navbar {
            position: fixed;
            z-index: 1000; /* Ensure navbar stays on top of the map */
        }
    </style>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin=""></script>

    <script src="https://unpkg.com/leaflet-rotatedmarker/leaflet.rotatedMarker.js"></script>

     
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="KlokIn">

    <link rel="icon" type="image/png" sizes="196x196" href="favicon-196.png">
    <link rel="apple-touch-icon" href="apple-icon-180.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="apple-touch-startup-image" href="apple-splash-1668-2388.jpg" media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
    <link rel="apple-touch-startup-image" href="apple-splash-1536-2048.jpg" media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
    <link rel="apple-touch-startup-image" href="apple-splash-1488-2266.jpg" media="(device-width: 744px) and (device-height: 1133px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
    <link rel="apple-touch-startup-image" href="apple-splash-1640-2360.jpg" media="(device-width: 820px) and (device-height: 1180px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
    <link rel="apple-touch-startup-image" href="apple-splash-1668-2224.jpg" media="(device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
    <link rel="apple-touch-startup-image" href="apple-splash-1620-2160.jpg" media="(device-width: 810px) and (device-height: 1080px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
    <link rel="apple-touch-startup-image" href="apple-splash-1290-2796.jpg" media="(device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
    <link rel="apple-touch-startup-image" href="apple-splash-1179-2556.jpg" media="(device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
    <link rel="apple-touch-startup-image" href="apple-splash-1284-2778.jpg" media="(device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
    <link rel="apple-touch-startup-image" href="apple-splash-1170-2532.jpg" media="(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
    <link rel="apple-touch-startup-image" href="apple-splash-1125-2436.jpg" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
    <link rel="apple-touch-startup-image" href="apple-splash-1242-2688.jpg" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
    <link rel="apple-touch-startup-image" href="apple-splash-828-1792.jpg" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
    <link rel="apple-touch-startup-image" href="apple-splash-1792-828.jpg" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
    <link rel="apple-touch-startup-image" href="apple-splash-1242-2208.jpg" media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
    <link rel="apple-touch-startup-image" href="apple-splash-750-1334.jpg" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
    <link rel="apple-touch-startup-image" href="apple-splash-1334-750.jpg" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
    <link rel="apple-touch-startup-image" href="apple-splash-640-1136.jpg" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
    <link rel="apple-touch-startup-image" href="apple-splash-1136-640.jpg" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
</head>
<body>
    <div class="overflow-auto h-[95vh] flex flex-col justify-between px-4 py-6">  
        <?php
            switch($page) {
                case 'home':
                    echo '
                        <div id="map" style="height: 100vh; width: 100vw;"></div>
                    ';
                    break;
                case 'today':
                    $postDateGet = isset($_GET['date']) ? $_GET['date'] : date("Y-m-d");

                    if (!isset($_GET['date'])) {
                        header("Location: index.php?page=today&date=".$postDateGet);
                    }

                    $date = new DateTimeImmutable(datetime: $postDateGet);

                    $userTasksJson = getUserTaskByDate($date->format("Y-m-d"));
                    $userTasksData = json_decode($userTasksJson, true);

                    $dayInfo = callApiRequest('https://api.kalendarium.dk/Dayinfo/'.date("d-m-Y", strtotime($date->format('Y-m-d'))).''); 
                    echo '
                        <h1 class="text-3xl font-semibold text-gray-800 mt-4 mb-4 text-center">'.htmlspecialchars($dayInfo["weekday"]).' d. '.htmlspecialchars(date("d-m-Y", strtotime($date->format('Y-m-d')))).' <br> Uge '.htmlspecialchars(date("W", strtotime($date->format('Y-m-d')))).'</h1>
                            <div class="cards">';
                                if ($userTasksData === null && json_last_error() !== JSON_ERROR_NONE) {
                                    echo "Error decoding JSON: " . json_last_error_msg();
                                } elseif ((count($userTasksData["tasks"]) == 0)) {
                                    echo '<h2 class="text-xl font-bold text-gray-900 mb-2 text-center">Ingen opgaver denne dag.</h2>';
                                } else {
                                    foreach ($userTasksData["tasks"] as $task) {
                                        if (!$task["completed"]) {
                                            echo '                            
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                                <div class="bg-white shadow-2xl rounded-lg p-6">
                                                    <h2 class="text-xl font-bold text-gray-900 mb-2">'.htmlspecialchars($task["title"]).'</h2>
                                                    <p class="text-gray-600 mb-4">'.htmlspecialchars($task["description"]).'</p>
                                                    <hr class="h-px my-8 bg-gray-200 border-0">
                                                    
                                                    <div class="py-4">
                                                        <div class="flex flex-col justify-center space-x-4">
                                                            <div>
                                                                <h5 class="block font-sans text-xl antialiased font-medium leading-snug tracking-normal text-blue-gray-900">
                                                                    Starttidspunkt
                                                                </h5>
                                                                <p class="text-gray-600">'.htmlspecialchars(date('H:i', strtotime($task["start_time"]))).'</p>
                                                                <p class="text-gray-600 mb-4">'.htmlspecialchars(date('d-m-Y', strtotime($task["start_time"]))).'</p>
                                                                <div class="inline-flex flex-wrap items-center gap-3 group">
                                                            </div>
                                                            <div>
                                                                <h5 class="block font-sans text-xl antialiased font-medium leading-snug tracking-normal text-blue-gray-900">
                                                                    Sluttidspunkt
                                                                </h5>
                                                                <p class="text-gray-600">'.htmlspecialchars(date('H:i', strtotime($task["end_time"]))).'</p>
                                                                <p class="text-gray-600 mb-4">'.htmlspecialchars(date('d-m-Y', strtotime($task["end_time"]))).'</p>
                                                                <div class="inline-flex flex-wrap items-center gap-3 group">
                                                            </div>
                                                            <div>
                                                                <h5 class="block font-sans text-xl antialiased font-medium leading-snug tracking-normal text-blue-gray-900">
                                                                    Samlet arbejdstid
                                                                </h5>
                                                                <p class="text-gray-600">'.htmlspecialchars(calculateDuration($task["start_time"], $task["end_time"])).'</p>
                                                                <div class="inline-flex flex-wrap items-center gap-3 group">
                                                            </div>
                                                        </div> 
                                                    </div>                                           
                                                </div>
                                                    <div class="p-6 pt-3">
                                                        <button data-modal-target="timepicker-modal" data-modal-toggle="timepicker-modal"
                                                            class="block w-full select-none rounded-lg bg-gray-900 py-3.5 px-7 text-center align-middle font-sans text-sm font-bold uppercase text-white shadow-md shadow-gray-900/10 transition-all hover:shadow-lg hover:shadow-gray-900/20 focus:opacity-[0.85] focus:shadow-none active:opacity-[0.85] active:shadow-none disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none"
                                                            type="button">
                                                            Start opgave
                                                        </button>
                                                    </div>                               
                                                </div>
                                            </div>';
                                        } else {
                                            echo '                            
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                                <div class="bg-gray-200 shadow-md rounded-lg p-6">
                                                    <h2 class="text-xl font-bold text-gray-600 mb-2">'.htmlspecialchars($task["title"]).'</h2>
                                                    <p class="text-gray-600 mb-4">'.htmlspecialchars($task["description"]).'</p>
                                                    <hr class="h-px my-8 bg-gray-400 border-0">
                                                    
                                                    <div class="py-4">
                                                        <div class="flex flex-col justify-center space-x-4">
                                                            <div>
                                                                <h5 class="block font-sans text-xl antialiased font-medium leading-snug tracking-normal text-blue-gray-900">
                                                                    Starttidspunkt
                                                                </h5>
                                                                <p class="text-gray-600">'.htmlspecialchars(date('H:i', timestamp: strtotime($task["start_time"]))).'</p>
                                                                <p class="text-gray-600 mb-4">'.htmlspecialchars(date('d-m-Y', timestamp: strtotime($task["start_time"]))).'</p>
                                                                <div class="inline-flex flex-wrap items-center gap-3 group">
                                                            </div>
                                                            <div>
                                                                <h5 class="block font-sans text-xl antialiased font-medium leading-snug tracking-normal text-blue-gray-900">
                                                                    Sluttidspunkt
                                                                </h5>
                                                                <p class="text-gray-600">'.htmlspecialchars(date('H:i', timestamp: strtotime($task["end_time"]))).'</p>
                                                                <p class="text-gray-600 mb-4">'.htmlspecialchars(date('d-m-Y', strtotime($task["end_time"]))).'</p>
                                                                <div class="inline-flex flex-wrap items-center gap-3 group">
                                                            </div>
                                                            <div>
                                                                <h5 class="block font-sans text-xl antialiased font-medium leading-snug tracking-normal text-blue-gray-900">
                                                                    Samlet arbejdstid
                                                                </h5>
                                                                <p class="text-gray-600">'.htmlspecialchars(calculateDuration($task["start_time"], $task["end_time"])).'</p>
                                                                <div class="inline-flex flex-wrap items-center gap-3 group">
                                                            </div>
                                                        </div> 
                                                    </div>                                           
                                                </div>
                                                    <div class="p-6 pt-3">
                                                        <button data-modal-target="timepicker-modal" data-modal-toggle="timepicker-modal"
                                                            class="block w-full select-none rounded-lg bg-gray-400 py-3.5 px-7 text-center align-middle font-sans text-sm font-bold uppercase text-white shadow-md shadow-gray-900/10 transition-all hover:shadow-lg hover:shadow-gray-900/20 focus:opacity-[0.85] focus:shadow-none active:opacity-[0.85] active:shadow-none disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none"
                                                            type="button">
                                                            Start opgave
                                                        </button>
                                                    </div>                               
                                                </div>
                                            </div>';
                                        }
                                        } 
                            } echo '
                            </div>

                        <div class="py-4">
                            <div class="flex justify-center space-x-4">
                                <button id="prevDay" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded flex items-center space-x-2 focus:outline-none focus:ring-2 focus:ring-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="w-5 h-5">
                                        <path d="M15.25 3.75a.75.75 0 010 1.06L7.56 12l7.69 7.19a.75.75 0 01-1.06 1.06L6.25 12l7.94-7.31a.75.75 0 011.06 0z"/>
                                    </svg>
                                    <span>Forrig dag</span>
                                </button>
                            
                                <button id="nextDay" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded flex items-center space-x-2 focus:outline-none focus:ring-2 focus:ring-gray-400">
                                    <span>Næste dag</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="w-5 h-5">
                                        <path d="M8.75 3.75a.75.75 0 011.06 0L18.44 12l-8.63 7.19a.75.75 0 01-1.06-1.06L16.44 12 8.75 4.81a.75.75 0 010-1.06z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>                             
                    ';
                    break;
                case 'profile':
                    echo '
                    <body class="bg-gray-100">
                        <div class="container mx-auto my-8 p-4">
                            <!-- Header -->
                            <h1 class="text-3xl font-bold text-center text-gray-800 mb-8">Medarbejder detaljer</h1>

                            <!-- Employee Card -->
                            <div class="max-w-xl mx-auto bg-white rounded-lg shadow-lg p-6">
                                <!-- Employee Avatar (Placeholder SVG) -->
                                <div class="flex justify-center mb-6">
                                    <svg class="w-24 h-24 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 12c2.97 0 5.39-2.42 5.39-5.39S14.97 1.22 12 1.22 6.61 3.64 6.61 6.61 9.03 12 12 12zm0 1.39c-3.15 0-9.45 1.57-9.45 4.73v1.39c0 .77.62 1.39 1.39 1.39h16.11c.77 0 1.39-.62 1.39-1.39v-1.39c0-3.16-6.3-4.73-9.45-4.73z"/>
                                    </svg>
                                </div>

                                <!-- Employee Details -->
                                <div class="text-center">
                                    <!-- Full Name -->
                                    <h2 class="text-2xl font-semibold text-gray-800 mb-2" id="fullname">John Doe</h2>
                                    <!-- Company Name -->
                                    <p class="text-gray-600 mb-4" id="company">ABC Corporation</p>
                                    <p class="text-gray-400 mb-4" id="cvr">CVR: DK112233</p>
                                    
                                    <!-- Contact Information -->
                                    <div class="space-y-2">
                                        <!-- Email -->
                                        <div class="flex items-center justify-center text-gray-700">
                                            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4.25L12 13 4 8.25V6l8 4.99L20 6v2.25z"/>
                                            </svg>
                                            <span id="email">john.doe@example.com</span>
                                        </div>
                                    </div>

                                    <!-- Sign Out Button -->
                                    <div class="mt-6">
                                        <button onclick="logout();" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">
                                            Log ud
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>            
                    ';
                    break;
                default:
                    echo '<h1>Page not found</h1>';
                    break;
            }
        ?>
    </div>
    
    <div id="timepicker-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-[23rem] max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-800">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Vælg starttidspunkt
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm h-8 w-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-toggle="timepicker-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="p-4 pt-0">
                    <div id="datepicker" inline-datepicker datepicker-autoselect-today class="mx-auto sm:mx-0 flex justify-center my-5"></div>
                    <label class="text-sm font-medium text-gray-900 dark:text-white mb-2 block">
                    Starttidspunkt
                    </label>

                    <div class="flex my-2">
                        <input type="time" id="startTime" class="rounded-none rounded-s-lg bg-gray-50 border text-gray-900 leading-none focus:ring-blue-500 focus:border-blue-500 block flex-1 w-full text-sm border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" min="00:00" max="24:00" value="12:00" required>
                        <span class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border rounded-s-0 border-s-0 border-gray-300 rounded-e-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm11-4a1 1 0 1 0-2 0v4a1 1 0 0 0 .293.707l3 3a1 1 0 0 0 1.414-1.414L13 11.586V8Z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" data-modal-hide="timepicker-modal" onclick="getSelectedDate()" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Gem</button>
                        <button type="button" data-modal-hide="timepicker-modal" class="py-2.5 px-5 mb-2 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Fortryd</button>
                    </div>
                </div>
            </div>
        </div>
    </div>    

    <div id="bottom-navbar">
        <div class="fixed bottom-0 left-0 z-50 w-full h-16 bg-white border-t border-gray-200 dark:bg-gray-700 dark:border-gray-600">
            <div class="grid h-full max-w-lg grid-cols-3 mx-auto font-medium">
                <button type="button" class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-800 group">
                <a href="?page=home">
                    <svg class="w-5 h-5 mb-2 text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                    </svg>
                    <span class="text-sm text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-500">Kort</span>
                </a>
                </button>
                <button type="button" class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-800 group">
                <a href="?page=today">
                    <svg class="w-5 h-5 mb-2 text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M11.074 4 8.442.408A.95.95 0 0 0 7.014.254L2.926 4h8.148ZM9 13v-1a4 4 0 0 1 4-4h6V6a1 1 0 0 0-1-1H1a1 1 0 0 0-1 1v13a1 1 0 0 0 1 1h17a1 1 0 0 0 1-1v-2h-6a4 4 0 0 1-4-4Z"/>
                        <path d="M19 10h-6a2 2 0 0 0-2 2v1a2 2 0 0 0 2 2h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1Zm-4.5 3.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2ZM12.62 4h2.78L12.539.41a1.086 1.086 0 1 0-1.7 1.352L12.62 4Z"/>
                    </svg>
                    <span class="text-sm text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-500">Se dag</span>
                </a>
                </button>
                <button type="button" class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-800 group">
                <a href="?page=profile">  
                    <svg class="w-5 h-5 mb-2 text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 0a10 10 0 1 0 10 10A10.011 10.011 0 0 0 10 0Zm0 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Zm0 13a8.949 8.949 0 0 1-4.951-1.488A3.987 3.987 0 0 1 9 13h2a3.987 3.987 0 0 1 3.951 3.512A8.949 8.949 0 0 1 10 18Z"/>
                    </svg>
                    <span class="text-sm text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-500">Profil</span>
                </a>
                </button>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="app.js"></script>
</body>
</html>
<?php
?>
