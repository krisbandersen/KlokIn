<?php 
   require_once '../php/utils.php'; 

   if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] !== true) {
   header("Location: /login.php");
   exit;
   }

   $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
   $error = isset($_GET['error']) ? $_GET['error'] : '';


   function loadEmployeesByOrg($orgId) {
      $C = connect();

      if (!$C) {
         echo "<script>console.log('Failed to connect to DB.' );</script>";
         return [];
      }

      $res = sqlSelect($C, 'SELECT id, firstname, lastname, email, employee_number FROM employees WHERE organization_id = ?', 'i', $orgId);
      
      $employees = [];
      if ($res) {
         while ($row = $res->fetch_assoc()) {
            $employees[] = $row;
         }
      }
      
      return $employees;
   }

   function loadTasksByOrg($orgId) {
      $C = connect();

      if (!$C) {
         echo "<script>console.log('Failed to connect to DB.' );</script>";
         return [];
      }

      $res = sqlSelect($C, 'SELECT * FROM tasks WHERE organization_id = ?', 'i', $orgId);
      
      $tasks = [];
      if ($res) {
         while ($row = $res->fetch_assoc()) {
            $tasks[] = $row;
         }
      }
      
      return $tasks;
   }

   function loadRecentEmployeesByOrg($orgId) {
      $C = connect();
  
      if (!$C) {
          echo "<script>console.log('Failed to connect to DB.');</script>";
          return [];
      }
  
      // SQL query to select the 5 most recent employees by organization
      $res = sqlSelect($C, 
          'SELECT firstname, lastname, email, employee_number, TIMESTAMPDIFF(DAY, created_at, NOW()) AS days_since_added 
          FROM employees 
          WHERE organization_id = ? 
          ORDER BY created_at DESC 
          LIMIT 5', 
          'i', $orgId);
  
      $employees = [];
      if ($res) {
          while ($row = $res->fetch_assoc()) {
              $employees[] = $row;
          }
      }
  
      return $employees;
   }

   $organizationId = $_SESSION['organization_id'];
   $recentEmployees = loadRecentEmployeesByOrg($organizationId);
   $employees = loadEmployeesByOrg($organizationId);
   $tasks = loadTasksByOrg($organizationId);
?>

<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
	<meta name="csrf_token" content="<?php echo createToken(); ?>" />
    <meta name='viewport' content='width=device-width, initial-scale=1.0, viewport-fit=cover'>
    <title>KlokIn - Admin panel</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin=""></script>

    <script src="https://unpkg.com/leaflet-rotatedmarker/leaflet.rotatedMarker.js"></script>

   <style>
      /* Custom Multi Select */
      .sd-multiSelect {
      position: relative;
      }

      .sd-multiSelect .placeholder {
      opacity: 1;
      background-color: transparent;
      cursor: pointer;
      }

      .sd-multiSelect .ms-offscreen {
      height: 1px;
      width: 1px;
      opacity: 0;
      overflow: hidden;
      display: none;
      }

      .sd-multiSelect .sd-CustomSelect {
      width: 100% !important;
      }

      .sd-multiSelect .ms-choice {
      position: relative;
      text-align: left !important;
      width: 100%;
      border: 1px solid #e3e3e3;
      background: #ffff;
      box-shadow: none;
      font-size: 15px;
      height: 44px;
      font-weight: 500;
      color: #212529;
      line-height: 1.5;
      -webkit-appearance: none;
      -moz-appearance: none;
      appearance: none;
      border-radius: 0.25rem;
      transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
      }

      .sd-multiSelect .ms-choice:after {
      content: "↓";
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 18px;
      }

      .sd-multiSelect .ms-choice:focus {
      border-color: var(--theme-color);
      }

      .sd-multiSelect .ms-drop.bottom {
         display: none;
         background: #fff;
         border: 1px solid #e5e5e5;
         padding: 10px;
      }

      .sd-multiSelect .ms-drop li {
      position: relative;
      margin-bottom: 10px;
      }

      .sd-multiSelect .ms-drop li input[type="checkbox"] {
      padding: 0;
      height: initial;
      width: initial;
      margin-bottom: 0;
      display: none;
      cursor: pointer;
      }

      .sd-multiSelect .ms-drop li label {
      cursor: pointer;
      user-select: none;
      -ms-user-select: none;
      -moz-user-select: none;
      -webkit-user-select: none;
      }

      .sd-multiSelect .ms-drop li label:before {
      content: "";
      -webkit-appearance: none;
      background-color: transparent;
      padding: 8px;
      display: inline-block;
      position: relative;
      vertical-align: middle;
      cursor: pointer;
      margin-right: 5px;
      }

      .sd-multiSelect .ms-drop li input:checked + span:after {
      content: "✓";
      display: block;
      position: absolute;
      top: 0px;
      left: 5px;
      width: 10px;
      height: 10px;
      }
   </style>

    <script>
      // On page load or when changing themes, best to add inline in `head` to avoid FOUC
      if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
         document.documentElement.classList.add('dark');
      } else {
         document.documentElement.classList.remove('dark')
      }
   </script>
</head>
<body class="dark:bg-gray-900">

<button data-drawer-target="separator-sidebar" data-drawer-toggle="separator-sidebar" aria-controls="separator-sidebar" type="button" class="inline-flex items-center p-2 mt-2 ms-3 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
   <span class="sr-only">Open sidebar</span>
   <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
   <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
   </svg>
</button>

<aside id="separator-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0" aria-label="Sidebar">
   <div class="h-full px-3 py-4 overflow-y-auto bg-gray-800">
      <a href="/index.php" class="flex items-center ps-2.5 mb-5">
            <img src="/clock.svg" class="h-6 me-3 sm:h-7" alt="Flowbite Logo" />
            <span class="self-center text-xl font-semibold whitespace-nowrap text-white">KlokIn</span>
      </a>
      <ul class="space-y-2 font-medium">
         <li>
            <a href="?page=dashboard" class="flex items-center p-2 text-gray-900 rounded-lg text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
               <span class="ms-3">Dashboard</span>
            </a>
         </li>
         <li>
            <a href="?page=map" class="flex items-center p-2 text-gray-900 rounded-lg text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
               <span class="flex-1 ms-3 whitespace-nowrap">Kort</span>
            </a>
         </li>
         <li>
            <a href="?page=employees" class="flex items-center p-2 text-gray-900 rounded-lg text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
               <span class="flex-1 ms-3 whitespace-nowrap">Medarbejdere</span>
            </a>
         </li>
         <li>
            <a href="?page=tasks" class="flex items-center p-2 text-gray-900 rounded-lg text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
               <span class="flex-1 ms-3 whitespace-nowrap">Opgaver</span>
            </a>
         </li> 
      </ul>
      <ul class="pt-4 mt-4 space-y-2 font-medium border-t border-gray-700">
         <li>
            <a href="#" onclick="logout();" class="flex items-center p-2 text-gray-900 rounded-lg text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
               <span class="flex-1 ms-3 whitespace-nowrap">Log ud</span>
            </a>
         </li>
         <li>
            <a href="#" class="flex items-center p-2 text-white transition duration-75 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 group">
               <span class="ms-3">Hjælp</span>
            </a>
         </li>
      </ul>
   </div>
   <div id="toast-bottom-left" class="fixed flex items-center space-x-4 text-gray-500 bg-white divide-x rtl:divide-x-reverse divide-gray-200 rounded-lg shadow bottom-5 left-5" role="alert">
   <button id="theme-toggle" type="button" class="text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2.5">
      <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
      <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
   </button>
</div>
</aside>

<div class="sm:ml-64 dark:bg-gray-900">
    <?php
        switch($page) {
            case 'dashboard':
               echo '
               <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
               <div class="max-w-sm mx-auto my-10">
                  <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6 space-y-4">
                        <div class="flex items-center space-x-4">
                           <div class="p-2 bg-purple-200 rounded-full">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a2 2 0 00-2-2h-3v4z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 15V7a2 2 0 012-2h10a2 2 0 012 2v8" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 15v4a2 2 0 002 2h3v-4" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 15h16" />
                              </svg>
                           </div>
                           <div>
                              <div class="text-gray-600 dark:dark:text-slate-200 text-sm">Antal ansatte</div>
                              <div class="text-gray-900 dark:dark:text-white text-2xl font-semibold">
                                    37
                              </div>
                           </div>
                        </div>
                  </div>
               </div>
               <div class="max-w-sm mx-auto my-10">
                  <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6 space-y-4">
                        <div class="flex items-center space-x-4">
                           <div class="p-2 bg-purple-200 rounded-full">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a2 2 0 00-2-2h-3v4z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 15V7a2 2 0 012-2h10a2 2 0 012 2v8" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 15v4a2 2 0 002 2h3v-4" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 15h16" />
                              </svg>
                           </div>
                           <div>
                              <div class="text-gray-600 dark:dark:text-slate-200 text-sm">Aktive ansatte</div>
                              <div class="text-gray-900 dark:dark:text-white text-2xl font-semibold">
                                    5
                              </div>
                           </div>
                        </div>
                  </div>
               </div>
               <div class="max-w-sm mx-auto my-10">
                  <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6 space-y-4">
                        <div class="flex items-center space-x-4">
                           <div class="p-2 bg-purple-200 rounded-full">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a2 2 0 00-2-2h-3v4z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 15V7a2 2 0 012-2h10a2 2 0 012 2v8" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 15v4a2 2 0 002 2h3v-4" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 15h16" />
                              </svg>
                           </div>
                           <div>
                              <div class="text-gray-600 dark:dark:text-slate-200 text-sm">Gns. timer per dag.</div>
                              <div class="text-gray-900 dark:dark:text-white text-2xl font-semibold">
                                    6.5
                              </div>
                           </div>
                        </div>
                  </div>
               </div>
               <div class="max-w-sm mx-auto my-10">
                  <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6 space-y-4">
                        <div class="flex items-center space-x-4">
                           <div class="p-2 bg-purple-200 rounded-full">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a2 2 0 00-2-2h-3v4z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 15V7a2 2 0 012-2h10a2 2 0 012 2v8" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 15v4a2 2 0 002 2h3v-4" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 15h16" />
                              </svg>
                           </div>
                           <div>
                              <div class="text-gray-600 dark:dark:text-slate-200 text-sm">Antal timer denne måned</div>
                              <div class="text-gray-900 dark:dark:text-white text-2xl font-semibold">
                                    5656
                              </div>
                           </div>
                        </div>
                  </div>
               </div>
               </div>

               <div class="grid gap-6 p-6 mb-8 md:grid-cols-3 xl:grid-cols-3">
               <div class="w-full mx-auto my-10 bg-white col-span-2 rounded-lg shadow dark:bg-gray-800 p-4 md:p-6">
                     <div class="flex justify-between pb-4 mb-4 border-b border-gray-200 dark:border-gray-700">
                           <div class="flex items-center">
                              <div class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center me-3">
                              <svg class="w-6 h-6 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 19">
                                 <path d="M14.5 0A3.987 3.987 0 0 0 11 2.1a4.977 4.977 0 0 1 3.9 5.858A3.989 3.989 0 0 0 14.5 0ZM9 13h2a4 4 0 0 1 4 4v2H5v-2a4 4 0 0 1 4-4Z"/>
                                 <path d="M5 19h10v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2ZM5 7a5.008 5.008 0 0 1 4-4.9 3.988 3.988 0 1 0-3.9 5.859A4.974 4.974 0 0 1 5 7Zm5 3a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm5-1h-.424a5.016 5.016 0 0 1-1.942 2.232A6.007 6.007 0 0 1 17 17h2a1 1 0 0 0 1-1v-2a5.006 5.006 0 0 0-5-5ZM5.424 9H5a5.006 5.006 0 0 0-5 5v2a1 1 0 0 0 1 1h2a6.007 6.007 0 0 1 4.366-5.768A5.016 5.016 0 0 1 5.424 9Z"/>
                              </svg>
                              </div>
                              <div>
                              <h5 class="leading-none text-2xl font-bold text-gray-900 dark:text-white pb-1">3.4k</h5>
                              <p class="text-sm font-normal text-gray-500 dark:text-gray-400">Samlet timer i den forrige uge</p>
                              </div>
                           </div>
                           <div>
                              <span class="bg-green-100 text-green-800 text-xs font-medium inline-flex items-center px-2.5 py-1 rounded-md dark:bg-green-900 dark:text-green-300">
                              <svg class="w-2.5 h-2.5 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 14">
                                 <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13V1m0 0L1 5m4-4 4 4"/>
                              </svg>
                              42.5%
                              </span>
                           </div>
                        </div>

                        <div id="column-chart"></div>
                           <div class="grid grid-cols-1 items-center border-gray-200 border-t dark:border-gray-700 justify-between">
                              <div class="flex justify-between items-center pt-5">
                              <a
                                 href="#"
                                 class="uppercase text-sm font-semibold inline-flex items-center rounded-lg text-blue-600 hover:text-blue-700 dark:hover:text-blue-500  hover:bg-gray-100 dark:hover:bg-gray-700 dark:focus:ring-gray-700 dark:border-gray-700 px-3 py-2">
                                 Se rapport
                                 <svg class="w-2.5 h-2.5 ms-1.5 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                                 </svg>
                              </a>
                           </div>
                        </div>
                     </div>
                  <div class="max-w-sm mx-auto my-10">
                     <div class="max-w-2xl mx-auto">
                        <div class="p-4 max-w-md bg-white rounded-lg border sm:p-8 shadow border-transparent dark:bg-gray-800">
                        <div class="flex justify-between items-center mb-4">
                           <h3 class="text-xl font-bold leading-none text-gray-900 dark:text-white">Senest tilføjede</h3>
                           <a href="?page=employees" class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-500">
                                 Vis alle
                           </a>
                        </div>
                        <div class="flow-root">
                           <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">';
                              if (!empty($recentEmployees)) {
                                 foreach ($recentEmployees as $employee) {
                                    echo '<li class="py-3 sm:py-4">
                                          <div class="flex items-center space-x-4">
                                             <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate dark:text-white">'
                                                   . htmlspecialchars($employee["firstname"] . ' ' . $employee["lastname"]) .
                                                '</p>
                                                <p class="text-sm text-gray-500 truncate dark:text-gray-400">'
                                                   . htmlspecialchars($employee["email"]) .
                                                '</p>
                                             </div>
                                             <div class="inline-flex items-center text-base font-semibold text-gray-900 dark:text-white">'
                                                . htmlspecialchars($employee["days_since_added"]) . 'd siden
                                             </div>
                                          </div>
                                       </li>';
                                 }
                              } else {
                                 echo '<li class="py-3 sm:py-4">No recent employees found.</li>';
                              }
                           
                           echo '
                           </ul>
                        </div>
                     </div>
                     </div>
                  </div>
                ';
                break;
            case 'map':
                echo '
                <div id="map" style="height: 100vh; width: 100vw;"></div>               
                ';
                break;
            case 'employees':

              function createErrorBox($message) {
                  return '
                      <div id="alert-2" class="flex items-center p-4 mb-4 text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                        <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                        </svg>
                        <span class="sr-only">Error</span>
                        <div class="ms-3 text-sm font-medium">'.$message.'</div>
                        <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-gray-700" data-dismiss-target="#alert-2" aria-label="Close">
                          <span class="sr-only">Close</span>
                          <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                          </svg>
                        </button>
                      </div>
                  ';
              }

              function createSuccesBox($message) {
               return '
                  <div id="alert-3" class="flex items-center p-4 mb-4 text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                  <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                     <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                  </svg>
                  <span class="sr-only">Info</span>
                  <div class="ms-3 text-sm font-medium">' . $message . '</div>
                  <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-green-50 text-green-500 rounded-lg focus:ring-2 focus:ring-green-400 p-1.5 hover:bg-green-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-green-400 dark:hover:bg-gray-700" data-dismiss-target="#alert-3" aria-label="Close">
                     <span class="sr-only">Close</span>
                     <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                     </svg>
                  </button>
                  </div>
               ';
           } 

               switch ($error) {
                  case 0:
                      echo createSuccesBox('Ny ansat oprettet i systemet.');
                      break;
                  case 1:
                      // Invalid name or employee number
                      echo createErrorBox('Ugyldigt navn eller medarbejdernummer');
                      break;
              
                  case 2:
                      // Invalid email
                      echo createErrorBox('Ugyldig e-mail.');
                      break;
              
                  case 6:
                      // Error adding new employee
                      echo createErrorBox('Fejl med at tilføje ny ansat.');
                      break;
              
                  case 8:
                      // Employee number already exists
                      echo createErrorBox('Der findes allerede en medarbejder med dette medarbejdernummer.');
                      break;
              
                  case 9:
                      // CSRF token error
                      echo createErrorBox('Fejl med CSRF token.');
                      break;
                  case 10:
                     echo createErrorBox('Der opstod en ukendt fejl. Prøv venligst igen senere.');
                     break;
              
                  default:
              }

               echo '
               <div class="p-4">
                  <div class="flex flex-col">
                        <div class="flex justify-between items-center my-2">
                        <!-- Left side: Dropdown -->
                        <div class="flex items-center space-x-2 dark:text-white">
                           <p>Vis</p>
                           <form>
                              <select id="entries-select" class="py-2.5 px-0 text-sm text-gray-500 bg-transparent border-0 border-b-2 border-gray-200 appearance-none dark:text-gray-400 dark:border-gray-700 focus:outline-none focus:ring-0 focus:border-gray-200">
                              <option selected>10</option>
                              <option value="25">25</option>
                              <option value="50">50</option>
                              <option value="100">100</option>
                              </select>
                           </form>
                           <p>poster</p>
                        </div>

                        <!-- Right side: Search form -->
                        <form id="search" class="max-w-md">
                           <label for="default-search" class="text-sm font-medium text-gray-900 sr-only dark:text-white">Search</label>
                           <div class="relative">
                              <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                              <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                 <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                              </svg>
                              </div>
                              <input type="search" id="search-input" class="block w-full ps-10 text-sm text-gray-900 border-0 border-b-2 border-gray-200 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Henrik, navn@gmail.com osv." required />
                           </div>
                        </form>
                        </div>
                        <div class=" overflow-x-auto pb-4">
                              <div class="block">
                                 <div class="overflow-x-auto w-full  border rounded-lg dark:border-transparent">
                                    <table id="employees-table" class="w-full rounded-xl">
                                          <thead>
                                             <tr class="bg-gray-50 dark:bg-gray-800">
                                                <th scope="col" class="p-5 text-left whitespace-nowrap text-sm leading-6 font-semibold text-gray-900 dark:text-white capitalize"> Medarbejdernummer </th>
                                                <th scope="col" class="p-5 text-left whitespace-nowrap text-sm leading-6 font-semibold text-gray-900 dark:text-white min-w-[150px]"> Navn &amp; Email </th>
                                                <th scope="col" class="p-5 text-left whitespace-nowrap text-sm leading-6 font-semibold text-gray-900 dark:text-white capitalize"> Status </th>
                                                <th scope="col" class="p-5 text-left whitespace-nowrap text-sm leading-6 font-semibold text-gray-900 dark:text-white capitalize"> Handlinger </th>
                                             </tr>
                                          </thead>
                                          <tbody class="divide-y divide-gray-300 dark:divide-gray-600 ">';
                                          usort($employees, function($a, $b) {
                                             return $a['employee_number'] - $b['employee_number'];
                                          });

                                          // Foreach loop to populate the table rows
                                          foreach ($employees as $employee) {
                                             echo '<tr class="bg-white dark:bg-gray-700 transition-all duration-500 hover:bg-gray-50 dark:hover:bg-gray-600">';
                                             echo '<td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900 dark:text-slate-200">' . htmlspecialchars($employee['employee_number']) . '</td>';
                                             echo '<td class=" px-5 py-3">';
                                                echo '<div class="w-48 flex items-center gap-3">';
                                                echo '<div class="data">';
                                                echo '<p class="font-normal text-sm text-gray-900 dark:text-slate-200">' . htmlspecialchars($employee['firstname']) . ' ' . htmlspecialchars($employee['lastname']) . '</p>';
                                                echo '<p class="font-normal text-xs leading-5 text-gray-400 dark:text-slate-400">' . htmlspecialchars($employee['email']) . '</p>';
                                                echo '</div>';
                                                echo '</div>';
                                             echo '</td>';

                                             echo '<td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900">';
                                                echo '<div class="py-1.5 px-2.5 bg-emerald-50 dark:bg-emerald-500 rounded-full flex justify-center w-20 items-center gap-1">';
                                                echo '<svg width="5" height="6" viewBox="0 0 5 6" fill="none" xmlns="http://www.w3.org/2000/svg">';
                                                echo '<circle cx="2.5" cy="3" r="2.5" fill="#059669"></circle>';
                                                echo '</svg>';
                                                echo '<span class="font-medium text-xs text-emerald-600 dark:text-emerald-900 ">Aktiv</span>';
                                                echo '</div>';
                                             echo '</td>';

                                             echo '
                                                <td class="flex p-5 items-center gap-0.5">
                                                      <button id="editEmployee" class="p-2  rounded-full bg-white dark:bg-slate-600 group transition-all duration-500 hover:bg-indigo-600 flex item-center" data-id="' . htmlspecialchars($employee['id']) .'">
                                                         <svg class="cursor-pointer" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path class="fill-indigo-500 group-hover:fill-white" d="M9.53414 8.15675L8.96459 7.59496L8.96459 7.59496L9.53414 8.15675ZM13.8911 3.73968L13.3215 3.17789V3.17789L13.8911 3.73968ZM16.3154 3.75892L15.7367 4.31126L15.7367 4.31127L16.3154 3.75892ZM16.38 3.82658L16.9587 3.27423L16.9587 3.27423L16.38 3.82658ZM16.3401 6.13595L15.7803 5.56438L16.3401 6.13595ZM11.9186 10.4658L12.4784 11.0374L11.9186 10.4658ZM11.1223 10.9017L10.9404 10.1226V10.1226L11.1223 10.9017ZM9.07259 10.9951L8.52556 11.5788L8.52556 11.5788L9.07259 10.9951ZM9.09713 8.9664L9.87963 9.1328V9.1328L9.09713 8.9664ZM9.05721 10.9803L8.49542 11.5498H8.49542L9.05721 10.9803ZM17.1679 4.99458L16.368 4.98075V4.98075L17.1679 4.99458ZM15.1107 2.8693L15.1171 2.06932L15.1107 2.8693ZM9.22851 8.51246L8.52589 8.12992L8.52452 8.13247L9.22851 8.51246ZM9.22567 8.51772L8.52168 8.13773L8.5203 8.1403L9.22567 8.51772ZM11.5684 10.7654L11.9531 11.4668L11.9536 11.4666L11.5684 10.7654ZM11.5669 10.7662L11.9507 11.4681L11.9516 11.4676L11.5669 10.7662ZM11.3235 3.30005C11.7654 3.30005 12.1235 2.94188 12.1235 2.50005C12.1235 2.05822 11.7654 1.70005 11.3235 1.70005V3.30005ZM18.3 9.55887C18.3 9.11705 17.9418 8.75887 17.5 8.75887C17.0582 8.75887 16.7 9.11705 16.7 9.55887H18.3ZM3.47631 16.5237L4.042 15.9581H4.042L3.47631 16.5237ZM16.5237 16.5237L15.958 15.9581L15.958 15.9581L16.5237 16.5237ZM10.1037 8.71855L14.4606 4.30148L13.3215 3.17789L8.96459 7.59496L10.1037 8.71855ZM15.7367 4.31127L15.8013 4.37893L16.9587 3.27423L16.8941 3.20657L15.7367 4.31127ZM15.7803 5.56438L11.3589 9.89426L12.4784 11.0374L16.8998 6.70753L15.7803 5.56438ZM10.9404 10.1226C10.3417 10.2624 9.97854 10.3452 9.72166 10.3675C9.47476 10.3888 9.53559 10.3326 9.61962 10.4113L8.52556 11.5788C8.9387 11.966 9.45086 11.9969 9.85978 11.9615C10.2587 11.9269 10.7558 11.8088 11.3042 11.6807L10.9404 10.1226ZM8.31462 8.8C8.19986 9.33969 8.09269 9.83345 8.0681 10.2293C8.04264 10.6393 8.08994 11.1499 8.49542 11.5498L9.619 10.4107C9.70348 10.494 9.65043 10.5635 9.66503 10.3285C9.6805 10.0795 9.75378 9.72461 9.87963 9.1328L8.31462 8.8ZM9.61962 10.4113C9.61941 10.4111 9.6192 10.4109 9.619 10.4107L8.49542 11.5498C8.50534 11.5596 8.51539 11.5693 8.52556 11.5788L9.61962 10.4113ZM15.8013 4.37892C16.0813 4.67236 16.2351 4.83583 16.3279 4.96331C16.4073 5.07234 16.3667 5.05597 16.368 4.98075L17.9678 5.00841C17.9749 4.59682 17.805 4.27366 17.6213 4.02139C17.451 3.78756 17.2078 3.53522 16.9587 3.27423L15.8013 4.37892ZM16.8998 6.70753C17.1578 6.45486 17.4095 6.21077 17.5876 5.98281C17.7798 5.73698 17.9607 5.41987 17.9678 5.00841L16.368 4.98075C16.3693 4.90565 16.4103 4.8909 16.327 4.99749C16.2297 5.12196 16.0703 5.28038 15.7803 5.56438L16.8998 6.70753ZM14.4606 4.30148C14.7639 3.99402 14.9352 3.82285 15.0703 3.71873C15.1866 3.62905 15.1757 3.66984 15.1044 3.66927L15.1171 2.06932C14.6874 2.06591 14.3538 2.25081 14.0935 2.45151C13.8518 2.63775 13.5925 2.9032 13.3215 3.17789L14.4606 4.30148ZM16.8941 3.20657C16.6279 2.92765 16.373 2.65804 16.1345 2.46792C15.8774 2.26298 15.5468 2.07273 15.1171 2.06932L15.1044 3.66927C15.033 3.66871 15.0226 3.62768 15.1372 3.71904C15.2704 3.82522 15.4387 3.999 15.7367 4.31126L16.8941 3.20657ZM8.96459 7.59496C8.82923 7.73218 8.64795 7.90575 8.5259 8.12993L9.93113 8.895C9.92075 8.91406 9.91465 8.91711 9.93926 8.88927C9.97002 8.85445 10.0145 8.80893 10.1037 8.71854L8.96459 7.59496ZM9.87963 9.1328C9.9059 9.00925 9.91925 8.94785 9.93124 8.90366C9.94073 8.86868 9.94137 8.87585 9.93104 8.89515L8.5203 8.1403C8.39951 8.36605 8.35444 8.61274 8.31462 8.8L9.87963 9.1328ZM8.52452 8.13247L8.52168 8.13773L9.92967 8.89772L9.9325 8.89246L8.52452 8.13247ZM11.3589 9.89426C11.27 9.98132 11.2252 10.0248 11.1909 10.055C11.1635 10.0791 11.1658 10.0738 11.1832 10.0642L11.9536 11.4666C12.1727 11.3462 12.3427 11.1703 12.4784 11.0374L11.3589 9.89426ZM11.3042 11.6807C11.4912 11.6371 11.7319 11.5878 11.9507 11.4681L11.1831 10.0643C11.2007 10.0547 11.206 10.0557 11.1697 10.0663C11.1248 10.0793 11.0628 10.0941 10.9404 10.1226L11.3042 11.6807ZM11.1837 10.064L11.1822 10.0648L11.9516 11.4676L11.9531 11.4668L11.1837 10.064ZM16.399 6.10097L13.8984 3.60094L12.7672 4.73243L15.2677 7.23246L16.399 6.10097ZM10.8333 16.7001H9.16667V18.3001H10.8333V16.7001ZM3.3 10.8334V9.16672H1.7V10.8334H3.3ZM9.16667 3.30005H11.3235V1.70005H9.16667V3.30005ZM16.7 9.55887V10.8334H18.3V9.55887H16.7ZM9.16667 16.7001C7.5727 16.7001 6.45771 16.6984 5.61569 16.5851C4.79669 16.475 4.35674 16.2728 4.042 15.9581L2.91063 17.0894C3.5722 17.751 4.40607 18.0369 5.4025 18.1709C6.37591 18.3018 7.61793 18.3001 9.16667 18.3001V16.7001ZM1.7 10.8334C1.7 12.3821 1.6983 13.6241 1.82917 14.5976C1.96314 15.594 2.24905 16.4279 2.91063 17.0894L4.042 15.9581C3.72726 15.6433 3.52502 15.2034 3.41491 14.3844C3.3017 13.5423 3.3 12.4273 3.3 10.8334H1.7ZM10.8333 18.3001C12.3821 18.3001 13.6241 18.3018 14.5975 18.1709C15.5939 18.0369 16.4278 17.751 17.0894 17.0894L15.958 15.9581C15.6433 16.2728 15.2033 16.475 14.3843 16.5851C13.5423 16.6984 12.4273 16.7001 10.8333 16.7001V18.3001ZM16.7 10.8334C16.7 12.4274 16.6983 13.5423 16.5851 14.3844C16.475 15.2034 16.2727 15.6433 15.958 15.9581L17.0894 17.0894C17.7509 16.4279 18.0369 15.594 18.1708 14.5976C18.3017 13.6241 18.3 12.3821 18.3 10.8334H16.7ZM3.3 9.16672C3.3 7.57275 3.3017 6.45776 3.41491 5.61574C3.52502 4.79674 3.72726 4.35679 4.042 4.04205L2.91063 2.91068C2.24905 3.57225 1.96314 4.40612 1.82917 5.40255C1.6983 6.37596 1.7 7.61798 1.7 9.16672H3.3ZM9.16667 1.70005C7.61793 1.70005 6.37591 1.69835 5.4025 1.82922C4.40607 1.96319 3.5722 2.24911 2.91063 2.91068L4.042 4.04205C4.35674 3.72731 4.79669 3.52507 5.61569 3.41496C6.45771 3.30175 7.5727 3.30005 9.16667 3.30005V1.70005Z" fill="#818CF8"></path>
                                                         </svg>
                                                      </button>
                                                      <button id="deleteEmployee" class="p-2 rounded-full bg-white dark:bg-slate-600 group transition-all duration-500 hover:bg-red-600 flex item-center" data-id="' . htmlspecialchars($employee['id']) .'">
                                                         <svg class="" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path class="fill-red-600 group-hover:fill-white" d="M4.00031 5.49999V4.69999H3.20031V5.49999H4.00031ZM16.0003 5.49999H16.8003V4.69999H16.0003V5.49999ZM17.5003 5.49999L17.5003 6.29999C17.9421 6.29999 18.3003 5.94183 18.3003 5.5C18.3003 5.05817 17.9421 4.7 17.5003 4.69999L17.5003 5.49999ZM9.30029 9.24997C9.30029 8.80814 8.94212 8.44997 8.50029 8.44997C8.05847 8.44997 7.70029 8.80814 7.70029 9.24997H9.30029ZM7.70029 13.75C7.70029 14.1918 8.05847 14.55 8.50029 14.55C8.94212 14.55 9.30029 14.1918 9.30029 13.75H7.70029ZM12.3004 9.24997C12.3004 8.80814 11.9422 8.44997 11.5004 8.44997C11.0585 8.44997 10.7004 8.80814 10.7004 9.24997H12.3004ZM10.7004 13.75C10.7004 14.1918 11.0585 14.55 11.5004 14.55C11.9422 14.55 12.3004 14.1918 12.3004 13.75H10.7004ZM4.00031 6.29999H16.0003V4.69999H4.00031V6.29999ZM15.2003 5.49999V12.5H16.8003V5.49999H15.2003ZM11.0003 16.7H9.00031V18.3H11.0003V16.7ZM4.80031 12.5V5.49999H3.20031V12.5H4.80031ZM9.00031 16.7C7.79918 16.7 6.97882 16.6983 6.36373 16.6156C5.77165 16.536 5.49093 16.3948 5.29823 16.2021L4.16686 17.3334C4.70639 17.873 5.38104 18.0979 6.15053 18.2013C6.89702 18.3017 7.84442 18.3 9.00031 18.3V16.7ZM3.20031 12.5C3.20031 13.6559 3.19861 14.6033 3.29897 15.3498C3.40243 16.1193 3.62733 16.7939 4.16686 17.3334L5.29823 16.2021C5.10553 16.0094 4.96431 15.7286 4.88471 15.1366C4.80201 14.5215 4.80031 13.7011 4.80031 12.5H3.20031ZM15.2003 12.5C15.2003 13.7011 15.1986 14.5215 15.1159 15.1366C15.0363 15.7286 14.8951 16.0094 14.7024 16.2021L15.8338 17.3334C16.3733 16.7939 16.5982 16.1193 16.7016 15.3498C16.802 14.6033 16.8003 13.6559 16.8003 12.5H15.2003ZM11.0003 18.3C12.1562 18.3 13.1036 18.3017 13.8501 18.2013C14.6196 18.0979 15.2942 17.873 15.8338 17.3334L14.7024 16.2021C14.5097 16.3948 14.229 16.536 13.6369 16.6156C13.0218 16.6983 12.2014 16.7 11.0003 16.7V18.3ZM2.50031 4.69999C2.22572 4.7 2.04405 4.7 1.94475 4.7C1.89511 4.7 1.86604 4.7 1.85624 4.7C1.85471 4.7 1.85206 4.7 1.851 4.7C1.05253 5.50059 1.85233 6.3 1.85256 6.3C1.85273 6.3 1.85297 6.3 1.85327 6.3C1.85385 6.3 1.85472 6.3 1.85587 6.3C1.86047 6.3 1.86972 6.3 1.88345 6.3C1.99328 6.3 2.39045 6.3 2.9906 6.3C4.19091 6.3 6.2032 6.3 8.35279 6.3C10.5024 6.3 12.7893 6.3 14.5387 6.3C15.4135 6.3 16.1539 6.3 16.6756 6.3C16.9364 6.3 17.1426 6.29999 17.2836 6.29999C17.3541 6.29999 17.4083 6.29999 17.4448 6.29999C17.4631 6.29999 17.477 6.29999 17.4863 6.29999C17.4909 6.29999 17.4944 6.29999 17.4968 6.29999C17.498 6.29999 17.4988 6.29999 17.4994 6.29999C17.4997 6.29999 17.4999 6.29999 17.5001 6.29999C17.5002 6.29999 17.5003 6.29999 17.5003 5.49999C17.5003 4.69999 17.5002 4.69999 17.5001 4.69999C17.4999 4.69999 17.4997 4.69999 17.4994 4.69999C17.4988 4.69999 17.498 4.69999 17.4968 4.69999C17.4944 4.69999 17.4909 4.69999 17.4863 4.69999C17.477 4.69999 17.4631 4.69999 17.4448 4.69999C17.4083 4.69999 17.3541 4.69999 17.2836 4.69999C17.1426 4.7 16.9364 4.7 16.6756 4.7C16.1539 4.7 15.4135 4.7 14.5387 4.7C12.7893 4.7 10.5024 4.7 8.35279 4.7C6.2032 4.7 4.19091 4.7 2.9906 4.7C2.39044 4.7 1.99329 4.7 1.88347 4.7C1.86974 4.7 1.86051 4.7 1.85594 4.7C1.8548 4.7 1.85396 4.7 1.85342 4.7C1.85315 4.7 1.85298 4.7 1.85288 4.7C1.85284 4.7 2.65253 5.49941 1.85408 6.3C1.85314 6.3 1.85296 6.3 1.85632 6.3C1.86608 6.3 1.89511 6.3 1.94477 6.3C2.04406 6.3 2.22573 6.3 2.50031 6.29999L2.50031 4.69999ZM7.05028 5.49994V4.16661H5.45028V5.49994H7.05028ZM7.91695 3.29994H12.0836V1.69994H7.91695V3.29994ZM12.9503 4.16661V5.49994H14.5503V4.16661H12.9503ZM12.0836 3.29994C12.5623 3.29994 12.9503 3.68796 12.9503 4.16661H14.5503C14.5503 2.8043 13.4459 1.69994 12.0836 1.69994V3.29994ZM7.05028 4.16661C7.05028 3.68796 7.4383 3.29994 7.91695 3.29994V1.69994C6.55465 1.69994 5.45028 2.8043 5.45028 4.16661H7.05028ZM2.50031 6.29999C4.70481 6.29998 6.40335 6.29998 8.1253 6.29997C9.84725 6.29996 11.5458 6.29995 13.7503 6.29994L13.7503 4.69994C11.5458 4.69995 9.84724 4.69996 8.12529 4.69997C6.40335 4.69998 4.7048 4.69998 2.50031 4.69999L2.50031 6.29999ZM13.7503 6.29994L17.5003 6.29999L17.5003 4.69999L13.7503 4.69994L13.7503 6.29994ZM7.70029 9.24997V13.75H9.30029V9.24997H7.70029ZM10.7004 9.24997V13.75H12.3004V9.24997H10.7004Z" fill="#F87171"></path>
                                                         </svg>
                                                      </button>
                                                </td>
                                             </tr>';
                                       }
                                       echo '
                                          </tbody>
                                    </table>
                                 </div>
                                 <nav class="flex items-center justify-center py-4 " aria-label="Table navigation">
                                    <ul class="flex items-center justify-center text-sm h-auto gap-12">
                                          <li>
                                             <a href="javascript:;" id="prev-page" class="flex items-center justify-center gap-2 px-3 h-8 ml-0 text-gray-500 dark:text-slate-50 bg-white dark:bg-gray-900 font-medium text-base leading-7  hover:text-slate-300 ">
                                                <svg class="dark:fill-white" width="21" height="20" viewBox="0 0 21 20" xmlns="http://www.w3.org/2000/svg">
                                                      <path d="M13.0002 14.9999L8 9.99967L13.0032 4.99652" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </svg> Tilbage </a>
                                          </li>
                                                <span id="current-page" class="font-normal text-base leading-7 text-gray-500 dark:text-slate-50 bg-white dark:bg-gray-800 py-2.5 px-4 rounded-full transition-all duration-500">1</span>
                                                <p class="text-gray-400 dark:text-slate-50">/</p>
                                                <span id="total-pages" class="font-normal text-base leading-7 text-gray-500 dark:text-slate-50 bg-white dark:bg-gray-800 py-2.5 px-4 rounded-full transition-all duration-500">1</span>
                                          <li>
                                             <a href="javascript:;" id="next-page" class="flex items-center justify-center gap-2 px-3 h-8 ml-0 text-gray-500 dark:text-slate-50 bg-white dark:bg-gray-900 font-medium text-base leading-7  hover:text-slate-300 "> frem <svg class="dark:fill-white" width="21" height="20" viewBox="0 0 21 20" xmlns="http://www.w3.org/2000/svg">
                                                      <path d="M8.00295 4.99646L13.0032 9.99666L8 14.9998" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </svg>
                                             </a>
                                          </li>
                                    </ul>                         
                                 </nav>
                              </div>
                        </div>
                  </div>  

               <div data-dial-init class="fixed end-6 bottom-6 group">
                  <div id="speed-dial-menu-square" class="flex flex-col items-center hidden mb-4 space-y-2">
                     <button type="button" data-modal-target="create-user-model" data-modal-toggle="create-user-model" data-tooltip-target="tooltip-create" data-tooltip-placement="left" class="flex justify-center items-center w-[52px] h-[52px] text-gray-500 hover:text-gray-900 bg-white rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm dark:hover:text-white dark:text-gray-400 hover:bg-gray-50 dark:bg-gray-700 dark:hover:bg-gray-600 focus:ring-4 focus:ring-gray-300 focus:outline-none dark:focus:ring-gray-400">
                           <svg class="w-5 h-5" aria-hidden="true" viewBox="0 0 20 22" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <circle cx="10" cy="8" r="4" stroke="#ffffff" stroke-linecap="round"></circle> <path d="M15.7956 20.4471C15.4537 19.1713 14.7004 18.0439 13.6526 17.2399C12.6047 16.4358 11.3208 16 10 16C8.6792 16 7.3953 16.4358 6.34743 17.2399C5.29957 18.0439 4.5463 19.1713 4.20445 20.4471" stroke="#ffffff" stroke-linecap="round"></path> <path d="M19 10L19 16" stroke="#ffffff" stroke-linecap="round"></path> <path d="M22 13L16 13" stroke="#ffffff" stroke-linecap="round"></path> </g></svg>
                           <span class="sr-only">Opret</span>
                     </button>
                     <div id="tooltip-create" role="tooltip" class="absolute z-10 invisible inline-block w-auto px-4 py-3 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                           Opret
                           <div class="tooltip-arrow" data-popper-arrow></div>
                     </div>
                  </div>
                  <button type="button" data-dial-toggle="speed-dial-menu-square" aria-controls="speed-dial-menu-square" aria-expanded="false" class="flex items-center justify-center text-white bg-blue-700 rounded-lg w-14 h-14 hover:bg-blue-800 dark:bg-blue-600 dark:hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 focus:outline-none dark:focus:ring-blue-800">
                     <svg class="w-5 h-5 transition-transform group-hover:rotate-45" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                           <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 1v16M1 9h16"/>
                     </svg>
                     <span class="sr-only">Åben menu</span>
                  </button>
               </div>
                  
               <!-- Main modal -->
               <div id="create-user-model" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                     <div class="relative p-4 w-full max-w-md max-h-full">
                        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                              <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                                 <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                    Opret ny ansat under dit firma
                                 </h3>
                                 <button type="button" class="end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="create-user-model">
                                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                                    </svg>
                                    <span class="sr-only">Luk</span>
                                 </button>
                              </div>
                              <div class="p-4 md:p-5">
                                 <form class="space-y-4" action="">
                                    <div class="col-span-2 sm:col-span-1">
                                          <label for="employee-number" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Medarbejdernummer</label>
                                          <input type="number" name="employee-number" id="employee-number" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="100" required="">
                                    </div>
                                    <div class="col-span-2">
                                          <label for="first-name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Fornavn</label>
                                          <input type="text" name="first-name" id="first-name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Sebastian" required="">
                                    </div>
                                    <div class="col-span-2">
                                          <label for="last-name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Efternavn</label>
                                          <input type="text" name="last-name" id="last-name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Andersen" required="">
                                    </div>
                                    <div>
                                          <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Ansattes email</label>
                                          <input type="email" name="email" id="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" placeholder="name@company.com" required />
                                    </div>
                                    <div class="col-span-2 sm:col-span-1">
                                          <label for="phone-number" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Ansattes telefonnummer </label>
                                          <input type="number" name="phone-number" id="phone-number" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="61685837" required="">
                                    </div>
                                    <button type="submit" onclick="createNewEmployee();" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Opret ny ansat</button>
                                 </form>
                              </div>
                        </div>
                     </div>
                  </div> 
               </div>
                
                ';
                break;
            case 'tasks':
               echo '
               <div class="p-4">
                  <div class="flex flex-col">
                        <div class="flex justify-between items-center my-2">
                        <!-- Left side: Dropdown -->
                        <div class="flex items-center space-x-2 dark:text-white">
                           <p>Vis</p>
                           <form>
                              <select id="entries-select" class="py-2.5 px-0 text-sm text-gray-500 bg-transparent border-0 border-b-2 border-gray-200 appearance-none dark:text-gray-400 dark:border-gray-700 focus:outline-none focus:ring-0 focus:border-gray-200">
                              <option selected>10</option>
                              <option value="25">25</option>
                              <option value="50">50</option>
                              <option value="100">100</option>
                              </select>
                           </form>
                           <p>poster</p>
                        </div>

                        <!-- Right side: Search form -->
                        <form id="search" class="max-w-md">
                           <label for="default-search" class="text-sm font-medium text-gray-900 sr-only dark:text-white">Search</label>
                           <div class="relative">
                              <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                              <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                 <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                              </svg>
                              </div>
                              <input type="search" id="search-input" class="block w-full ps-10 text-sm text-gray-900 border-0 border-b-2 border-gray-200 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Dagmar, 30-10-2024, 36.." required />
                           </div>
                        </form>
                        </div>
                        <div class=" overflow-x-auto pb-4">
                              <div class="block">
                                 <div class="overflow-x-auto w-full  border rounded-lg dark:border-transparent">
                                    <table id="employees-table" class="w-full rounded-xl">
                                          <thead>
                                             <tr class="bg-gray-50 dark:bg-gray-800">
                                                <th scope="col" class="p-5 text-left whitespace-nowrap text-sm leading-6 font-semibold text-gray-900 dark:text-white capitalize"> ID </th>
                                                <th scope="col" class="p-5 text-left whitespace-nowrap text-sm leading-6 font-semibold text-gray-900 dark:text-white capitalize"> Titel </th>
                                                <th scope="col" class="p-5 text-left whitespace-nowrap text-sm leading-6 font-semibold text-gray-900 dark:text-white min-w-[150px]"> Beskrivelse </th>
                                                <th scope="col" class="p-5 text-left whitespace-nowrap text-sm leading-6 font-semibold text-gray-900 dark:text-white capitalize"> Start- & sluttidspunkt </th>
                                                <th scope="col" class="p-5 text-left whitespace-nowrap text-sm leading-6 font-semibold text-gray-900 dark:text-white capitalize"> Lokation </th>
                                                <th scope="col" class="p-5 text-left whitespace-nowrap text-sm leading-6 font-semibold text-gray-900 dark:text-white capitalize"> Status </th>
                                                <th scope="col" class="p-5 text-left whitespace-nowrap text-sm leading-6 font-semibold text-gray-900 dark:text-white capitalize"> Handlinger </th>
                                             </tr>
                                          </thead>
                                          <tbody class="divide-y divide-gray-300 dark:divide-gray-600">';
                                          usort($tasks, function($a, $b) {
                                             return $a['id'] - $b['id'];
                                          });

                                          // Foreach loop to populate the table rows
                                          foreach ($tasks as $task) {
                                             echo '<tr class="bg-white dark:bg-gray-700 transition-all duration-500 hover:bg-gray-50 dark:hover:bg-gray-600">';

                                             // ID
                                             echo '<td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900 dark:text-slate-200">' . htmlspecialchars($task['id']) . '</td>';
                                             
                                             // Titel
                                             echo '<td class="p-5 max-w-xs truncate text-sm leading-6 font-medium text-gray-900 dark:text-slate-200">' . htmlspecialchars($task['title']) . '</td>';

                                             // Beskrivelse
                                             echo '<td class="p-5 max-w-xs truncate text-sm leading-6 font-medium text-gray-900 dark:text-slate-200">' . htmlspecialchars($task['description']) . '</td>';

                                             // Dato
                                             echo '
                                             <td class=" px-5 py-3">
                                                <div class="w-48 flex items-center gap-3">
                                                   <div class="data">
                                                      <p class="font-normal text-sm text-gray-900 dark:text-slate-200">'.htmlspecialchars(date('H:i', strtotime($task["start_time"]))).' - ' .htmlspecialchars(date('H:i', strtotime($task["end_time"]))). '</p>
                                                      <p class="font-normal text-xs leading-5 text-gray-400 dark:text-slate-400">'.htmlspecialchars(date('d-m-Y', strtotime($task["start_time"]))).'</p>
                                                   </div>
                                                </div>
                                             </td>';
                                             

                                             // Lokation
                                             echo '<td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900">
                                                      <button type="button" id="checkTaskLocation" onclick="checkTaskLocation();" data-id="' . htmlspecialchars($task['id']) .' data-modal-target="check-location-modal" data-modal-toggle="check-location-modal" class="text-gray-900 bg-white hover:bg-gray-100 border border-gray-200 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-2.5 text-center inline-flex items-center dark:focus:ring-gray-600 dark:bg-slate-600 dark:border-gray-700 dark:text-white dark:hover:bg-gray-700">
                                                         <svg class="w4 h-4 me-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                                         <path fill-rule="evenodd" d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm11-4a1 1 0 1 0-2 0v4a1 1 0 0 0 .293.707l3 3a1 1 0 0 0 1.414-1.414L13 11.586V8Z" clip-rule="evenodd"/>
                                                         </svg>
                                                         Se lokation
                                                      </button>
                                                   </td>';

                                             // Status
                                             if (!$task["completed"]) {
                                                echo '<td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900">';
                                                echo '<div class="py-1.5 px-2.5 bg-yellow-400 rounded-full flex justify-center w-20 items-center gap-1">';
                                                echo '<svg width="5" height="6" viewBox="0 0 5 6" fill="none" xmlns="http://www.w3.org/2000/svg">';
                                                echo '<circle cx="2.5" cy="3" r="2.5" fill="#b91c1c"></circle>';
                                                echo '</svg>';
                                                echo '<span class="font-medium text-xs text-yellow-700 ">Igang</span>';
                                                echo '</div>';
                                                echo '</td>';
                                             } else {
                                                echo '<td class="p-5 whitespace-nowrap text-sm leading-6 font-medium text-gray-900">';
                                                echo '<div class="py-1.5 px-2.5 bg-emerald-50 dark:bg-emerald-500 rounded-full flex justify-center w-20 items-center gap-1">';
                                                echo '<svg width="5" height="6" viewBox="0 0 5 6" fill="none" xmlns="http://www.w3.org/2000/svg">';
                                                echo '<circle cx="2.5" cy="3" r="2.5" fill="#059669"></circle>';
                                                echo '</svg>';
                                                echo '<span class="font-medium text-xs text-emerald-600 dark:text-emerald-900 ">Færdig</span>';
                                                echo '</div>';
                                                echo '</td>';
                                             }
 

                                             // Actions
                                             echo '
                                                <td class="flex p-5 items-center gap-0.5">
                                                      <button id="deleteTask" class="p-2  rounded-full bg-white dark:bg-slate-600 group transition-all duration-500 hover:bg-indigo-600 flex item-center" data-id="' . htmlspecialchars($task['id']) .'">
                                                         <svg class="cursor-pointer" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path class="fill-indigo-500 group-hover:fill-white" d="M9.53414 8.15675L8.96459 7.59496L8.96459 7.59496L9.53414 8.15675ZM13.8911 3.73968L13.3215 3.17789V3.17789L13.8911 3.73968ZM16.3154 3.75892L15.7367 4.31126L15.7367 4.31127L16.3154 3.75892ZM16.38 3.82658L16.9587 3.27423L16.9587 3.27423L16.38 3.82658ZM16.3401 6.13595L15.7803 5.56438L16.3401 6.13595ZM11.9186 10.4658L12.4784 11.0374L11.9186 10.4658ZM11.1223 10.9017L10.9404 10.1226V10.1226L11.1223 10.9017ZM9.07259 10.9951L8.52556 11.5788L8.52556 11.5788L9.07259 10.9951ZM9.09713 8.9664L9.87963 9.1328V9.1328L9.09713 8.9664ZM9.05721 10.9803L8.49542 11.5498H8.49542L9.05721 10.9803ZM17.1679 4.99458L16.368 4.98075V4.98075L17.1679 4.99458ZM15.1107 2.8693L15.1171 2.06932L15.1107 2.8693ZM9.22851 8.51246L8.52589 8.12992L8.52452 8.13247L9.22851 8.51246ZM9.22567 8.51772L8.52168 8.13773L8.5203 8.1403L9.22567 8.51772ZM11.5684 10.7654L11.9531 11.4668L11.9536 11.4666L11.5684 10.7654ZM11.5669 10.7662L11.9507 11.4681L11.9516 11.4676L11.5669 10.7662ZM11.3235 3.30005C11.7654 3.30005 12.1235 2.94188 12.1235 2.50005C12.1235 2.05822 11.7654 1.70005 11.3235 1.70005V3.30005ZM18.3 9.55887C18.3 9.11705 17.9418 8.75887 17.5 8.75887C17.0582 8.75887 16.7 9.11705 16.7 9.55887H18.3ZM3.47631 16.5237L4.042 15.9581H4.042L3.47631 16.5237ZM16.5237 16.5237L15.958 15.9581L15.958 15.9581L16.5237 16.5237ZM10.1037 8.71855L14.4606 4.30148L13.3215 3.17789L8.96459 7.59496L10.1037 8.71855ZM15.7367 4.31127L15.8013 4.37893L16.9587 3.27423L16.8941 3.20657L15.7367 4.31127ZM15.7803 5.56438L11.3589 9.89426L12.4784 11.0374L16.8998 6.70753L15.7803 5.56438ZM10.9404 10.1226C10.3417 10.2624 9.97854 10.3452 9.72166 10.3675C9.47476 10.3888 9.53559 10.3326 9.61962 10.4113L8.52556 11.5788C8.9387 11.966 9.45086 11.9969 9.85978 11.9615C10.2587 11.9269 10.7558 11.8088 11.3042 11.6807L10.9404 10.1226ZM8.31462 8.8C8.19986 9.33969 8.09269 9.83345 8.0681 10.2293C8.04264 10.6393 8.08994 11.1499 8.49542 11.5498L9.619 10.4107C9.70348 10.494 9.65043 10.5635 9.66503 10.3285C9.6805 10.0795 9.75378 9.72461 9.87963 9.1328L8.31462 8.8ZM9.61962 10.4113C9.61941 10.4111 9.6192 10.4109 9.619 10.4107L8.49542 11.5498C8.50534 11.5596 8.51539 11.5693 8.52556 11.5788L9.61962 10.4113ZM15.8013 4.37892C16.0813 4.67236 16.2351 4.83583 16.3279 4.96331C16.4073 5.07234 16.3667 5.05597 16.368 4.98075L17.9678 5.00841C17.9749 4.59682 17.805 4.27366 17.6213 4.02139C17.451 3.78756 17.2078 3.53522 16.9587 3.27423L15.8013 4.37892ZM16.8998 6.70753C17.1578 6.45486 17.4095 6.21077 17.5876 5.98281C17.7798 5.73698 17.9607 5.41987 17.9678 5.00841L16.368 4.98075C16.3693 4.90565 16.4103 4.8909 16.327 4.99749C16.2297 5.12196 16.0703 5.28038 15.7803 5.56438L16.8998 6.70753ZM14.4606 4.30148C14.7639 3.99402 14.9352 3.82285 15.0703 3.71873C15.1866 3.62905 15.1757 3.66984 15.1044 3.66927L15.1171 2.06932C14.6874 2.06591 14.3538 2.25081 14.0935 2.45151C13.8518 2.63775 13.5925 2.9032 13.3215 3.17789L14.4606 4.30148ZM16.8941 3.20657C16.6279 2.92765 16.373 2.65804 16.1345 2.46792C15.8774 2.26298 15.5468 2.07273 15.1171 2.06932L15.1044 3.66927C15.033 3.66871 15.0226 3.62768 15.1372 3.71904C15.2704 3.82522 15.4387 3.999 15.7367 4.31126L16.8941 3.20657ZM8.96459 7.59496C8.82923 7.73218 8.64795 7.90575 8.5259 8.12993L9.93113 8.895C9.92075 8.91406 9.91465 8.91711 9.93926 8.88927C9.97002 8.85445 10.0145 8.80893 10.1037 8.71854L8.96459 7.59496ZM9.87963 9.1328C9.9059 9.00925 9.91925 8.94785 9.93124 8.90366C9.94073 8.86868 9.94137 8.87585 9.93104 8.89515L8.5203 8.1403C8.39951 8.36605 8.35444 8.61274 8.31462 8.8L9.87963 9.1328ZM8.52452 8.13247L8.52168 8.13773L9.92967 8.89772L9.9325 8.89246L8.52452 8.13247ZM11.3589 9.89426C11.27 9.98132 11.2252 10.0248 11.1909 10.055C11.1635 10.0791 11.1658 10.0738 11.1832 10.0642L11.9536 11.4666C12.1727 11.3462 12.3427 11.1703 12.4784 11.0374L11.3589 9.89426ZM11.3042 11.6807C11.4912 11.6371 11.7319 11.5878 11.9507 11.4681L11.1831 10.0643C11.2007 10.0547 11.206 10.0557 11.1697 10.0663C11.1248 10.0793 11.0628 10.0941 10.9404 10.1226L11.3042 11.6807ZM11.1837 10.064L11.1822 10.0648L11.9516 11.4676L11.9531 11.4668L11.1837 10.064ZM16.399 6.10097L13.8984 3.60094L12.7672 4.73243L15.2677 7.23246L16.399 6.10097ZM10.8333 16.7001H9.16667V18.3001H10.8333V16.7001ZM3.3 10.8334V9.16672H1.7V10.8334H3.3ZM9.16667 3.30005H11.3235V1.70005H9.16667V3.30005ZM16.7 9.55887V10.8334H18.3V9.55887H16.7ZM9.16667 16.7001C7.5727 16.7001 6.45771 16.6984 5.61569 16.5851C4.79669 16.475 4.35674 16.2728 4.042 15.9581L2.91063 17.0894C3.5722 17.751 4.40607 18.0369 5.4025 18.1709C6.37591 18.3018 7.61793 18.3001 9.16667 18.3001V16.7001ZM1.7 10.8334C1.7 12.3821 1.6983 13.6241 1.82917 14.5976C1.96314 15.594 2.24905 16.4279 2.91063 17.0894L4.042 15.9581C3.72726 15.6433 3.52502 15.2034 3.41491 14.3844C3.3017 13.5423 3.3 12.4273 3.3 10.8334H1.7ZM10.8333 18.3001C12.3821 18.3001 13.6241 18.3018 14.5975 18.1709C15.5939 18.0369 16.4278 17.751 17.0894 17.0894L15.958 15.9581C15.6433 16.2728 15.2033 16.475 14.3843 16.5851C13.5423 16.6984 12.4273 16.7001 10.8333 16.7001V18.3001ZM16.7 10.8334C16.7 12.4274 16.6983 13.5423 16.5851 14.3844C16.475 15.2034 16.2727 15.6433 15.958 15.9581L17.0894 17.0894C17.7509 16.4279 18.0369 15.594 18.1708 14.5976C18.3017 13.6241 18.3 12.3821 18.3 10.8334H16.7ZM3.3 9.16672C3.3 7.57275 3.3017 6.45776 3.41491 5.61574C3.52502 4.79674 3.72726 4.35679 4.042 4.04205L2.91063 2.91068C2.24905 3.57225 1.96314 4.40612 1.82917 5.40255C1.6983 6.37596 1.7 7.61798 1.7 9.16672H3.3ZM9.16667 1.70005C7.61793 1.70005 6.37591 1.69835 5.4025 1.82922C4.40607 1.96319 3.5722 2.24911 2.91063 2.91068L4.042 4.04205C4.35674 3.72731 4.79669 3.52507 5.61569 3.41496C6.45771 3.30175 7.5727 3.30005 9.16667 3.30005V1.70005Z" fill="#818CF8"></path>
                                                         </svg>
                                                      </button>
                                                      <button id="deleteTask" class="p-2 rounded-full bg-white dark:bg-slate-600 group transition-all duration-500 hover:bg-red-600 flex item-center" data-id="' . htmlspecialchars($task['id']) .'">
                                                         <svg class="" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path class="fill-red-600 group-hover:fill-white" d="M4.00031 5.49999V4.69999H3.20031V5.49999H4.00031ZM16.0003 5.49999H16.8003V4.69999H16.0003V5.49999ZM17.5003 5.49999L17.5003 6.29999C17.9421 6.29999 18.3003 5.94183 18.3003 5.5C18.3003 5.05817 17.9421 4.7 17.5003 4.69999L17.5003 5.49999ZM9.30029 9.24997C9.30029 8.80814 8.94212 8.44997 8.50029 8.44997C8.05847 8.44997 7.70029 8.80814 7.70029 9.24997H9.30029ZM7.70029 13.75C7.70029 14.1918 8.05847 14.55 8.50029 14.55C8.94212 14.55 9.30029 14.1918 9.30029 13.75H7.70029ZM12.3004 9.24997C12.3004 8.80814 11.9422 8.44997 11.5004 8.44997C11.0585 8.44997 10.7004 8.80814 10.7004 9.24997H12.3004ZM10.7004 13.75C10.7004 14.1918 11.0585 14.55 11.5004 14.55C11.9422 14.55 12.3004 14.1918 12.3004 13.75H10.7004ZM4.00031 6.29999H16.0003V4.69999H4.00031V6.29999ZM15.2003 5.49999V12.5H16.8003V5.49999H15.2003ZM11.0003 16.7H9.00031V18.3H11.0003V16.7ZM4.80031 12.5V5.49999H3.20031V12.5H4.80031ZM9.00031 16.7C7.79918 16.7 6.97882 16.6983 6.36373 16.6156C5.77165 16.536 5.49093 16.3948 5.29823 16.2021L4.16686 17.3334C4.70639 17.873 5.38104 18.0979 6.15053 18.2013C6.89702 18.3017 7.84442 18.3 9.00031 18.3V16.7ZM3.20031 12.5C3.20031 13.6559 3.19861 14.6033 3.29897 15.3498C3.40243 16.1193 3.62733 16.7939 4.16686 17.3334L5.29823 16.2021C5.10553 16.0094 4.96431 15.7286 4.88471 15.1366C4.80201 14.5215 4.80031 13.7011 4.80031 12.5H3.20031ZM15.2003 12.5C15.2003 13.7011 15.1986 14.5215 15.1159 15.1366C15.0363 15.7286 14.8951 16.0094 14.7024 16.2021L15.8338 17.3334C16.3733 16.7939 16.5982 16.1193 16.7016 15.3498C16.802 14.6033 16.8003 13.6559 16.8003 12.5H15.2003ZM11.0003 18.3C12.1562 18.3 13.1036 18.3017 13.8501 18.2013C14.6196 18.0979 15.2942 17.873 15.8338 17.3334L14.7024 16.2021C14.5097 16.3948 14.229 16.536 13.6369 16.6156C13.0218 16.6983 12.2014 16.7 11.0003 16.7V18.3ZM2.50031 4.69999C2.22572 4.7 2.04405 4.7 1.94475 4.7C1.89511 4.7 1.86604 4.7 1.85624 4.7C1.85471 4.7 1.85206 4.7 1.851 4.7C1.05253 5.50059 1.85233 6.3 1.85256 6.3C1.85273 6.3 1.85297 6.3 1.85327 6.3C1.85385 6.3 1.85472 6.3 1.85587 6.3C1.86047 6.3 1.86972 6.3 1.88345 6.3C1.99328 6.3 2.39045 6.3 2.9906 6.3C4.19091 6.3 6.2032 6.3 8.35279 6.3C10.5024 6.3 12.7893 6.3 14.5387 6.3C15.4135 6.3 16.1539 6.3 16.6756 6.3C16.9364 6.3 17.1426 6.29999 17.2836 6.29999C17.3541 6.29999 17.4083 6.29999 17.4448 6.29999C17.4631 6.29999 17.477 6.29999 17.4863 6.29999C17.4909 6.29999 17.4944 6.29999 17.4968 6.29999C17.498 6.29999 17.4988 6.29999 17.4994 6.29999C17.4997 6.29999 17.4999 6.29999 17.5001 6.29999C17.5002 6.29999 17.5003 6.29999 17.5003 5.49999C17.5003 4.69999 17.5002 4.69999 17.5001 4.69999C17.4999 4.69999 17.4997 4.69999 17.4994 4.69999C17.4988 4.69999 17.498 4.69999 17.4968 4.69999C17.4944 4.69999 17.4909 4.69999 17.4863 4.69999C17.477 4.69999 17.4631 4.69999 17.4448 4.69999C17.4083 4.69999 17.3541 4.69999 17.2836 4.69999C17.1426 4.7 16.9364 4.7 16.6756 4.7C16.1539 4.7 15.4135 4.7 14.5387 4.7C12.7893 4.7 10.5024 4.7 8.35279 4.7C6.2032 4.7 4.19091 4.7 2.9906 4.7C2.39044 4.7 1.99329 4.7 1.88347 4.7C1.86974 4.7 1.86051 4.7 1.85594 4.7C1.8548 4.7 1.85396 4.7 1.85342 4.7C1.85315 4.7 1.85298 4.7 1.85288 4.7C1.85284 4.7 2.65253 5.49941 1.85408 6.3C1.85314 6.3 1.85296 6.3 1.85632 6.3C1.86608 6.3 1.89511 6.3 1.94477 6.3C2.04406 6.3 2.22573 6.3 2.50031 6.29999L2.50031 4.69999ZM7.05028 5.49994V4.16661H5.45028V5.49994H7.05028ZM7.91695 3.29994H12.0836V1.69994H7.91695V3.29994ZM12.9503 4.16661V5.49994H14.5503V4.16661H12.9503ZM12.0836 3.29994C12.5623 3.29994 12.9503 3.68796 12.9503 4.16661H14.5503C14.5503 2.8043 13.4459 1.69994 12.0836 1.69994V3.29994ZM7.05028 4.16661C7.05028 3.68796 7.4383 3.29994 7.91695 3.29994V1.69994C6.55465 1.69994 5.45028 2.8043 5.45028 4.16661H7.05028ZM2.50031 6.29999C4.70481 6.29998 6.40335 6.29998 8.1253 6.29997C9.84725 6.29996 11.5458 6.29995 13.7503 6.29994L13.7503 4.69994C11.5458 4.69995 9.84724 4.69996 8.12529 4.69997C6.40335 4.69998 4.7048 4.69998 2.50031 4.69999L2.50031 6.29999ZM13.7503 6.29994L17.5003 6.29999L17.5003 4.69999L13.7503 4.69994L13.7503 6.29994ZM7.70029 9.24997V13.75H9.30029V9.24997H7.70029ZM10.7004 9.24997V13.75H12.3004V9.24997H10.7004Z" fill="#F87171"></path>
                                                         </svg>
                                                      </button>
                                                </td>
                                             </tr>';
                                       }
                                       echo '
                                          </tbody>
                                    </table>
                                 </div>
                                 <nav class="flex items-center justify-center py-4 " aria-label="Table navigation">
                                    <ul class="flex items-center justify-center text-sm h-auto gap-12">
                                          <li>
                                             <a href="javascript:;" id="prev-page" class="flex items-center justify-center gap-2 px-3 h-8 ml-0 text-gray-500 dark:text-slate-50 bg-white dark:bg-gray-900 font-medium text-base leading-7  hover:text-slate-300 ">
                                                <svg class="dark:fill-white" width="21" height="20" viewBox="0 0 21 20" xmlns="http://www.w3.org/2000/svg">
                                                      <path d="M13.0002 14.9999L8 9.99967L13.0032 4.99652" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </svg> Tilbage </a>
                                          </li>
                                                <span id="current-page" class="font-normal text-base leading-7 text-gray-500 dark:text-slate-50 bg-white dark:bg-gray-800 py-2.5 px-4 rounded-full transition-all duration-500">1</span>
                                                <p class="text-gray-400 dark:text-slate-50">/</p>
                                                <span id="total-pages" class="font-normal text-base leading-7 text-gray-500 dark:text-slate-50 bg-white dark:bg-gray-800 py-2.5 px-4 rounded-full transition-all duration-500">1</span>
                                          <li>
                                             <a href="javascript:;" id="next-page" class="flex items-center justify-center gap-2 px-3 h-8 ml-0 text-gray-500 dark:text-slate-50 bg-white dark:bg-gray-900 font-medium text-base leading-7  hover:text-slate-300 "> frem <svg class="dark:fill-white" width="21" height="20" viewBox="0 0 21 20" xmlns="http://www.w3.org/2000/svg">
                                                      <path d="M8.00295 4.99646L13.0032 9.99666L8 14.9998" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </svg>
                                             </a>
                                          </li>
                                    </ul>                              
                                 </nav>
                              </div>
                        </div>
                  </div>  

               <div data-dial-init class="fixed end-6 bottom-6 group">
                  <div id="speed-dial-menu-square" class="flex flex-col items-center hidden mb-4 space-y-2">
                     <button type="button" data-modal-target="create-task-modal" data-modal-toggle="create-task-modal" data-tooltip-target="tooltip-create" data-tooltip-placement="left" class="flex justify-center items-center w-[52px] h-[52px] text-gray-500 hover:text-gray-900 bg-white rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm dark:hover:text-white dark:text-gray-400 hover:bg-gray-50 dark:bg-gray-700 dark:hover:bg-gray-600 focus:ring-4 focus:ring-gray-300 focus:outline-none dark:focus:ring-gray-400">
                           <svg class="w-5 h-5" aria-hidden="true" viewBox="0 0 20 22" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <circle cx="10" cy="8" r="4" stroke="#ffffff" stroke-linecap="round"></circle> <path d="M15.7956 20.4471C15.4537 19.1713 14.7004 18.0439 13.6526 17.2399C12.6047 16.4358 11.3208 16 10 16C8.6792 16 7.3953 16.4358 6.34743 17.2399C5.29957 18.0439 4.5463 19.1713 4.20445 20.4471" stroke="#ffffff" stroke-linecap="round"></path> <path d="M19 10L19 16" stroke="#ffffff" stroke-linecap="round"></path> <path d="M22 13L16 13" stroke="#ffffff" stroke-linecap="round"></path> </g></svg>
                           <span class="sr-only">Opret</span>
                     </button>
                     <div id="tooltip-create" role="tooltip" class="absolute z-10 invisible inline-block w-auto px-4 py-3 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                           Opret
                           <div class="tooltip-arrow" data-popper-arrow></div>
                     </div>
                  </div>
                  <button type="button" data-dial-toggle="speed-dial-menu-square" aria-controls="speed-dial-menu-square" aria-expanded="false" class="flex items-center justify-center text-white bg-blue-700 rounded-lg w-14 h-14 hover:bg-blue-800 dark:bg-blue-600 dark:hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 focus:outline-none dark:focus:ring-blue-800">
                     <svg class="w-5 h-5 transition-transform group-hover:rotate-45" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                           <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 1v16M1 9h16"/>
                     </svg>
                     <span class="sr-only">Åben menu</span>
                  </button>
               </div>
                  
               <!-- Main modal -->
               <div id="create-task-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                     <div class="relative p-4 w-full max-w-md max-h-full">
                        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                              <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                                 <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                    Opret opgave
                                 </h3>
                                 <button type="button" class="end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="create-task-modal">
                                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                                    </svg>
                                    <span class="sr-only">Luk</span>
                                 </button>
                              </div>
                              <div class="p-4 md:p-5">
                                 <form class="space-y-4" onsubmit="createNewTask(event); return false;">
                                    <div class="col-span-2">
                                          <label for="taskTitle" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Titel</label>
                                          <input type="text" name="taskTitle" id="taskTitle" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Dagmar petersens gade 27." required="">
                                    </div>
                                    <div class="col-span-2">
                                          <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Beskrivelse</label>
                                          <textarea id="taskDescription" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Beskrivelse her... anden etage til højre. Husk at følge proceduren."></textarea></div>
                                    <div>
                                          <label for="Deadline" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Deadline</label>
                                          <button type="button" data-modal-target="timepicker-modal" data-modal-toggle="timepicker-modal" class="text-gray-900 bg-white hover:bg-gray-100 border border-gray-200 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center dark:focus:ring-gray-600 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:bg-gray-700">
                                             <svg class="w4 h-4 me-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                             <path fill-rule="evenodd" d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm11-4a1 1 0 1 0-2 0v4a1 1 0 0 0 .293.707l3 3a1 1 0 0 0 1.414-1.414L13 11.586V8Z" clip-rule="evenodd"/>
                                             </svg>
                                             Sæt deadline
                                          </button>       
                                    </div>
                                    <div>
                                          <label for="location" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Lokation</label>
                                          <button type="button" onclick="intializeMap();" data-modal-target="location-modal" data-modal-toggle="location-modal" class="text-gray-900 bg-white hover:bg-gray-100 border border-gray-200 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center dark:focus:ring-gray-600 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:bg-gray-700">
                                             <svg class="w4 h-4 me-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                             <path fill-rule="evenodd" d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm11-4a1 1 0 1 0-2 0v4a1 1 0 0 0 .293.707l3 3a1 1 0 0 0 1.414-1.414L13 11.586V8Z" clip-rule="evenodd"/>
                                             </svg>
                                             Sæt lokation
                                          </button>       
                                    </div>                                    
                                    <div class="col-span-2 sm:col-span-1">
                                       <label for="assignedEmployees" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tildelte ansatte </label>
                                       <div class="modal-box">
                                       <div class="sd-multiSelect form-group">
                                          <select multiple id="current-job-role" class="sd-CustomSelect text-sm font-medium text-gray-900 dark:text-white overflow-y-auto">';
                                          usort($employees, function($a, $b) {
                                             return $a['employee_number'] - $b['employee_number'];
                                          });

                                          $optionID = 1;

                                          // Foreach loop to populate the table rows
                                          foreach ($employees as $employee) {
                                             $optionID ++;
                                             echo '<option value="'.$employee['id'].'">' . htmlspecialchars($employee['firstname']) . ' ' . htmlspecialchars($employee['lastname']) . ' - ' . htmlspecialchars($employee['employee_number']) . '</option>';
                                          }
                                          echo '</select>
                                       </div>
                                       </div>
                                    </div>
                                    <button type="submit" data-modal-hide="create-task-modal" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Opret ny opgave</button>
                                 </form>
                              </div>
                        </div>
                     </div>
                  </div> 
               </div>

               <!-- timepicker modal -->
               <div id="timepicker-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                  <div class="relative p-4 w-full max-w-[23rem] max-h-full">
                     <!-- Modal content -->
                     <div class="relative bg-white rounded-lg shadow dark:bg-gray-800">
                           <!-- Modal header -->
                           <div class="flex items-center justify-between p-4 border-b rounded-t dark:border-gray-600">
                              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                 Sæt deadline for opgave
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

                              <label class="text-sm font-medium text-gray-900 dark:text-white mb-2 block">
                              Sluttidspunkt
                              </label>

                              <div class="flex my-2">
                                 <input type="time" id="endTime" class="rounded-none rounded-s-lg bg-gray-50 border text-gray-900 leading-none focus:ring-blue-500 focus:border-blue-500 block flex-1 w-full text-sm border-gray-300 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" min="00:00" max="24:00" value="12:00" required>
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

               <!-- location modal - creating new task -->
               <div id="location-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                  <div class="relative p-4 w-full max-w-[32rem] max-h-full">
                     <div class="relative bg-white rounded-lg shadow dark:bg-gray-800">
                           <div class="flex items-center justify-between p-4 border-b rounded-t dark:border-gray-600">
                              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                 Sæt location for opgaven
                              </h3>
                              <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm h-8 w-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-toggle="location-modal">
                                 <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                       <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                                 </svg>
                                 <span class="sr-only">Close modal</span>
                              </button>
                           </div>
                           <!-- Modal body -->
                           <div class="p-4 pt-0">
                              <div class="flex my-2">
                               <div id="locationSelector" style="height: 32rem; width: 100vw;"></div>               
                              </div>

                              <div class="grid grid-cols-2 gap-2">
                                 <button type="button" data-modal-hide="location-modal" onclick="getSelectedDate()" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Gem</button>
                                 <button type="button" data-modal-hide="location-modal" class="py-2.5 px-5 mb-2 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Fortryd</button>
                              </div>
                           </div>
                     </div>
                  </div>
               </div>        
               
               <!-- location modal - check location -->
               <div id="check-location-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                  <div class="relative p-4 w-full max-w-[32rem] max-h-full">
                     <div class="relative bg-white rounded-lg shadow dark:bg-gray-800">
                           <div class="flex items-center justify-between p-4 border-b rounded-t dark:border-gray-600">
                              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                 Se lokation af opgave
                              </h3>
                              <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm h-8 w-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-toggle="check-location-modal">
                                 <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                       <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                                 </svg>
                                 <span class="sr-only">Close modal</span>
                              </button>
                           </div>
                           <!-- Modal body -->
                           <div class="p-4 pt-0">
                              <div class="flex my-2">
                               <div id="locationSelector" style="height: 32rem; width: 100vw;"></div>               
                              </div>
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
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://bsite.net/savrajdutta/cdn/multi-select.js"></script>
<script src="admin.js"></script>
</body>
</html>
