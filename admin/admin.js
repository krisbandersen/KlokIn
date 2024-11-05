function request(url, data, callback) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', url, true);

    var loader = document.createElement('div');
    loader.className = 'loader';
    document.body.appendChild(loader);

    // Make sure the request is processed only when it's completed
    xhr.addEventListener('readystatechange', function() {
        if (xhr.readyState === 4) { // Check if the request is complete
            loader.remove(); // Remove loader once the request is done

            if (xhr.status === 200) {
                if (callback) {
                    callback(xhr.response);
                }
            } else {
                console.error('Request failed with status:', xhr.status);
            }
        }
    });

    var formdata;
    if (data) {
        formdata = data instanceof FormData ? data : new FormData(document.querySelector(data));
    } else {
        formdata = new FormData(); // Empty FormData object if no data provided
    }

    var csrfMetaTag = document.querySelector('meta[name="csrf_token"]');
    if (csrfMetaTag) {
        formdata.append('csrf_token', csrfMetaTag.getAttribute('content'));
    }

    xhr.send(formdata);
}

var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

// Change the icons inside the button based on previous settings
if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    themeToggleLightIcon.classList.remove('hidden');
} else {
    themeToggleDarkIcon.classList.remove('hidden');
}

var themeToggleBtn = document.getElementById('theme-toggle');

themeToggleBtn.addEventListener('click', function() {

    // toggle icons inside button
    themeToggleDarkIcon.classList.toggle('hidden');
    themeToggleLightIcon.classList.toggle('hidden');

    // if set via local storage previously
    if (localStorage.getItem('color-theme')) {
        if (localStorage.getItem('color-theme') === 'light') {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme', 'light');
        }

    // if NOT set via local storage previously
    } else {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme', 'light');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme', 'dark');
        }
    }
    
});

// Admin create employee
function createNewEmployee() {
    // Get form data from the modal
    const firstName = document.getElementById('first-name').value;
    const lastName = document.getElementById('last-name').value;
    const email = document.getElementById('email').value;
	const employee_number = document.getElementById('employee-number').value;
    const phoneNumber = document.getElementById('phone-number').value;

    // Validate required fields
    if (!firstName || !lastName || !email || !phoneNumber) {
        alert('Please fill in all fields.');
        return;
    }

    // Create a FormData object to send to the server
    const formData = new FormData();
    formData.append('firstname', firstName);
    formData.append('lastname', lastName);
    formData.append('email', email);
	formData.append('employee_number', employee_number);
    formData.append('phone_number', phoneNumber);

    // Make the request to the server
    request('php/createNewEmployee.php', formData, function(response) {
        data = JSON.parse(response)[0];

        switch(data) {
			case 0:
				window.location = '?page=employees&error=0';
				break;
			case 1:
                window.location = '?page=employees&error=1';
				break;
			case 2:
                window.location = '?page=employees&error=2';
				break;
            case 6:
                window.location = '?page=employees&error=6';
                break;
            case 8:
                window.location = '?page=employees&error=8';
                break;
            case 9:
                window.location = '?page=employees&error=9';
                break;
			default:
                window.location = '?page=employees&error=10';
		}
    });
}

function logout() {
	request('php/logout.php', false, function(response) {
		window.location = '/';
	});
}


const params = new URLSearchParams(window.location.search);

const page = params.get('page') ? params.get('page') : 'dashboard';
const underPage = params.get('underpage') ? params.get('underpage') : '';

// Switch statement to handle different pages
switch (page) {
    case 'map':    
        //Map logics
        var map = L.map('map').setView([56.1572, 10.2107], 13); // Default to Copenhagen

        // Add a dark-themed tile layer (using Stadia Maps Dark mode)
        L.tileLayer('https://tiles.stadiamaps.com/tiles/alidade_smooth_dark/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://stadiamaps.com/">Stadia Maps</a>, &copy; <a href="https://openmaptiles.org/">OpenMapTiles</a> &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Create an arrow marker using the SVG you provided
        var arrowIcon = L.divIcon({
            html: `<svg xmlns="http://www.w3.org/2000/svg" height="14" width="14" viewBox="0 0 512 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path fill="#d01616" d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512z"/></svg>`,
            className: 'custom-icon', // Custom class for styling
            iconSize: [40, 40], // Size of the icon
            iconAnchor: [20, 20] // Point of the icon which corresponds to marker's location
        });

        var arrowMarker;

        // Function to handle success of geolocation
        function onLocationFound(e) {
            var latlng = e.latlng;

            // Create the marker if it doesn't exist yet, otherwise update it
            if (!arrowMarker) {
                arrowMarker = L.marker(latlng, { icon: arrowIcon, rotationAngle: 0 }).addTo(map);
            } else {
                arrowMarker.setLatLng(latlng);
            }

            map.setView(latlng, 16); // Update the map view to the user's current location
        }

        // Function to update the marker's heading (rotation) based on direction
        function onHeadingChange(heading) {
            if (arrowMarker) {
                arrowMarker.setRotationAngle(heading);
            }
        }

        // Handle location errors
        function onLocationError(e) {
            alert(e.message); // Show an error message if geolocation fails
        }

        // Watch the user's position and heading
        navigator.geolocation.watchPosition(function(position) {
            var lat = position.coords.latitude;
            var lon = position.coords.longitude;
            var accuracy = position.coords.accuracy;
            var heading = position.coords.heading; // This gives us the user's heading

            var latlng = L.latLng(lat, lon);

            // Update the marker position and rotation angle
            onLocationFound({ latlng: latlng, accuracy: accuracy });
            if (heading !== null) {
                onHeadingChange(heading);
            }
        }, onLocationError, {
            enableHighAccuracy: true, // Use high accuracy for GPS
            maximumAge: 30000, // Cache position for 30 seconds
            timeout: 27000 // Timeout after 27 seconds
        });

        break;
    case 'dashboard':
        const options = {
            colors: ["#1A56DB", "#FDBA8C"],
            series: [
              {
                name: "Organic",
                color: "#1A56DB",
                data: [
                  { x: "Mon", y: 231 },
                  { x: "Tue", y: 122 },
                  { x: "Wed", y: 63 },
                  { x: "Thu", y: 421 },
                  { x: "Fri", y: 122 },
                  { x: "Sat", y: 323 },
                  { x: "Sun", y: 111 },
                ],
              },
              {
                name: "Social media",
                color: "#FDBA8C",
                data: [
                  { x: "Mon", y: 232 },
                  { x: "Tue", y: 113 },
                  { x: "Wed", y: 341 },
                  { x: "Thu", y: 224 },
                  { x: "Fri", y: 522 },
                  { x: "Sat", y: 411 },
                  { x: "Sun", y: 243 },
                ],
              },
            ],
            chart: {
              type: "bar",
              height: "320px",
              fontFamily: "Inter, sans-serif",
              toolbar: {
                show: false,
              },
            },
            plotOptions: {
              bar: {
                horizontal: false,
                columnWidth: "70%",
                borderRadiusApplication: "end",
                borderRadius: 8,
              },
            },
            tooltip: {
              shared: true,
              intersect: false,
              style: {
                fontFamily: "Inter, sans-serif",
              },
            },
            states: {
              hover: {
                filter: {
                  type: "darken",
                  value: 1,
                },
              },
            },
            stroke: {
              show: true,
              width: 0,
              colors: ["transparent"],
            },
            grid: {
              show: false,
              strokeDashArray: 4,
              padding: {
                left: 2,
                right: 2,
                top: -14
              },
            },
            dataLabels: {
              enabled: false,
            },
            legend: {
              show: false,
            },
            xaxis: {
              floating: false,
              labels: {
                show: true,
                style: {
                  fontFamily: "Inter, sans-serif",
                  cssClass: 'text-xs font-normal fill-gray-500 dark:fill-gray-400'
                }
              },
              axisBorder: {
                show: false,
              },
              axisTicks: {
                show: false,
              },
            },
            yaxis: {
              show: false,
            },
            fill: {
              opacity: 1,
            },
          }
          
          if(document.getElementById("column-chart") && typeof ApexCharts !== 'undefined') {
            const chart = new ApexCharts(document.getElementById("column-chart"), options);
            chart.render();
          }

        break;
    case 'employees':

        break;
    case 'tasks':
      
      break;
    default:
        console.log("Page not found, redirecting to home");
        break;
}

document.addEventListener("DOMContentLoaded", function() {
  const entriesSelect = document.getElementById("entries-select");
  const searchInput = document.getElementById("search-input");
  const table = document.getElementById("employees-table");
  const rows = Array.from(table.querySelectorAll("tbody tr"));
  const prevButton = document.getElementById("prev-page");
  const nextButton = document.getElementById("next-page");
  const currentPageElement = document.getElementById("current-page");
  const totalPagesElement = document.getElementById("total-pages");
  
  let currentPage = 1;
  let totalPages = 1;

  function updateTable() {
      const searchQuery = searchInput.value.toLowerCase();
      const entriesCount = parseInt(entriesSelect.value);
      
      // Filter and show the matching rows
      let filteredRows = rows.filter(row => {
          return row.textContent.toLowerCase().includes(searchQuery);
      });
      
      // Calculate the total number of pages
      totalPages = Math.ceil(filteredRows.length / entriesCount);
      totalPagesElement.textContent = totalPages;
      
      // Clamp current page if out of bounds
      if (currentPage > totalPages) {
          currentPage = totalPages;
      }
      if (currentPage < 1) {
          currentPage = 1;
      }
      
      // Hide all rows first
      rows.forEach(row => row.style.display = "none");
      
      // Show only the rows for the current page
      const start = (currentPage - 1) * entriesCount;
      const end = start + entriesCount;
      filteredRows.slice(start, end).forEach(row => row.style.display = "");
      
      // Update page controls
      currentPageElement.textContent = currentPage;
      prevButton.disabled = currentPage === 1;
      nextButton.disabled = currentPage === totalPages;
  }

  // Event listeners for dropdown, search input, and pagination buttons
  entriesSelect.addEventListener("change", updateTable);
  searchInput.addEventListener("input", updateTable);
  prevButton.addEventListener("click", function() {
      if (currentPage > 1) {
          currentPage--;
          updateTable();
      }
  });
  nextButton.addEventListener("click", function() {
      if (currentPage < totalPages) {
          currentPage++;
          updateTable();
      }
  });
  
  // Initial table load
  updateTable();
});


document.getElementById("search-input").addEventListener("keydown", function(event) {
    if (event.key === "Enter") {
      event.preventDefault(); // Prevent form submission on Enter key press
    }
});

document.querySelectorAll('#editEmployee').forEach(button => {
    button.addEventListener('click', function() {
        const employeeId = this.getAttribute('data-id');
        console.log('Edit Employee ID:', employeeId);
    });
});

document.querySelectorAll('#deleteEmployee').forEach(button => {
    button.addEventListener('click', function() {
        const employeeId = this.getAttribute('data-id');
        if (confirm('Er du sikker på du vil slette denne ansat?')) {
            const formData = new FormData();
            formData.append('id', employeeId);

            request('php/deleteEmployee.php', formData, function(response) {
                data = JSON.parse(response)[0];

                switch(data) {
                    case 0:
                        window.location = '?page=employees&error=0';
                        break;
                    case 1:
                        window.location = '?page=employees&error=1';
                        break;
                    default:
                        window.location = '?page=employees&error=10';
                }
            });
        }    
    });
});

document.querySelectorAll('#deleteTask').forEach(button => {
  button.addEventListener('click', function() {
      const taskId = this.getAttribute('data-id');
      if (confirm('Er du sikker på du vil slette denne opgave?')) {
          const formData = new FormData();
          formData.append('id', taskId);

          request('php/deleteTask.php', formData, function(response) {
              data = JSON.parse(response)[0];

              switch(data) {
                  case 0:
                      window.location = '?page=tasks&error=0';
                      break;
                  case 1:
                      window.location = '?page=tasks&error=1';
                      break;
                  default:
                      window.location = '?page=tasks&error=10';
              }
          });
      }    
  });
});

$(document).ready(function() {
  $(".sd-CustomSelect").multipleSelect({
    selectAll: false,
    onOptgroupClick: function(view) {
      $(view).parents("label").addClass("selected-optgroup");
    }
  });
});

let datepickerInstance;

function initializeDatepicker() {
  const datepickerEl = document.getElementById('datepicker');

  if (!datepickerInstance && datepickerEl) {
    datepickerInstance = new Datepicker(datepickerEl, {
      autoselect: true
    });

    datepickerEl.addEventListener('changeDate', (event) => {
      const selectedDate = event.detail.date;
      datepickerInstance.selectedDate = selectedDate; 
    });
  }
}

function getTaskStartTime() {
  var date = getSelectedDate();
  var deadlineTime = document.getElementById('startTime').value;

  var [hours, minutes] = deadlineTime.split(':').map(Number);

  const year = date.getFullYear();
  const month = date.getMonth();
  const day = date.getDate();

  const deadlineDateTime = new Date(year, month, day, hours, minutes);

  if (isNaN(deadlineDateTime.getTime())) {
    console.error('Invalid date:', deadlineDateTime);
    return;
  }

  // Convert the Date object to a timestamp (in milliseconds)
  return deadlineDateTime.getTime(); // This will be the timestamp in milliseconds

}

function getTaskEndTime() {
  var date = getSelectedDate();
  var deadlineTime = document.getElementById('endTime').value;

  var [hours, minutes] = deadlineTime.split(':').map(Number);

  const year = date.getFullYear();
  const month = date.getMonth();
  const day = date.getDate();

  const deadlineDateTime = new Date(year, month, day, hours, minutes);

  if (isNaN(deadlineDateTime.getTime())) {
    console.error('Invalid date:', deadlineDateTime);
    return;
  }

  // Convert the Date object to a timestamp (in milliseconds)
  return deadlineDateTime.getTime(); // This will be the timestamp in milliseconds
}

function getSelectedDate() {
  if (datepickerInstance && datepickerInstance.selectedDate) {
    return datepickerInstance.selectedDate;
  } else {
    console.warn("Datepicker has no selected date");
    return undefined;
  }
}

window.addEventListener('DOMContentLoaded', initializeDatepicker);

mapIsInitialized = false;
taskLocation = null;

// Admin create employee
function createNewTask(event) {
  event.preventDefault();

  // Get form data from the modal
  const TaskTitle = document.getElementById('taskTitle').value;
  const TaskDescription = document.getElementById('taskDescription').value;

  const startTime = getTaskStartTime();
  const endTime = getTaskEndTime();
  console.log(startTime, endTime)
 
  const assignedEmployeesSelect = document.getElementById('current-job-role');
  const selectedEmployees = Array.from(assignedEmployeesSelect.selectedOptions).map(option => option.value);

  const formData = new FormData();
  formData.append('title', TaskTitle);
  formData.append('description', TaskDescription);
  formData.append('startTime', startTime);
  formData.append('endTime', endTime);
  formData.append('latitude', taskLocation.lat);
  formData.append('longitude', taskLocation.long);
  formData.append('assignedEmployees', JSON.stringify(selectedEmployees)); // Append selected employees as JSON

  taskLocation = null;
  request('php/createNewTask.php', formData, function(response) {
    const data = JSON.parse(response)[0];
    console.log(data)

    /*switch(data) {
      case 0:
        window.location = '?page=tasks&error=0';
        break;
      case 1:
        window.location = '?page=tasks&error=1';
        break;
      case 2:
        window.location = '?page=tasks&error=2';
        break;
      case 3:
          window.location = '?page=tasks&error=3';
          break;
      case 4:
          window.location = '?page=tasks&error=4';
          break;
      case 5:
          window.location = '?page=tasks&error=5';
          break;
      default:
          window.location = '?page=tasks&error=10';
    }*/
  });
}

function intializeMap() {
  if (!mapIsInitialized) {
    setTimeout(() => {
      // Map initialization
      var map = L.map('locationSelector').setView([56.1572, 10.2107], 13); // Default to Aarhus

      // Add a dark-themed tile layer (using Stadia Maps Dark mode)
      L.tileLayer('https://tiles.stadiamaps.com/tiles/alidade_smooth_dark/{z}/{x}/{y}{r}.png', {
          attribution: '&copy; <a href="https://stadiamaps.com/">Stadia Maps</a>, &copy; <a href="https://openmaptiles.org/">OpenMapTiles</a> &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
      }).addTo(map);

      // Create an arrow marker using the SVG you provided
      var arrowIcon = L.divIcon({
        html: `<svg xmlns="http://www.w3.org/2000/svg" height="30" width="30" viewBox="0 0 24 24" fill="none" stroke="#d01616" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20m0 0l-6-6m6 6l6-6"/></svg>`,
        className: 'custom-icon', // Custom class for styling
        iconSize: [40, 40], // Size of the icon
        iconAnchor: [20, 20] // Point of the icon which corresponds to marker's location
      });

      var selectedMarker; // Variable to store the user's selected location marker

      // Function to handle success of geolocation
      function onLocationFound(e) {
          var latlng = e.latlng;

          map.setView(latlng, 16); // Update the map view to the user's current location
      }

      // Handle location errors
      function onLocationError(e) {
          //alert(e.message); // Show an error message if geolocation fails
      }

      // Watch the user's position and heading
      navigator.geolocation.watchPosition(function(position) {
          var lat = position.coords.latitude;
          var lon = position.coords.longitude;
          var accuracy = position.coords.accuracy;

          var latlng = L.latLng(lat, lon);

          // Update the marker position and rotation angle
          onLocationFound({ latlng: latlng, accuracy: accuracy });
      }, onLocationError, {
          enableHighAccuracy: true, // Use high accuracy for GPS
          maximumAge: 30000, // Cache position for 30 seconds
          timeout: 27000 // Timeout after 27 seconds
      });

      // Add click event to the map to select location
      map.on('click', function(e) {
          var latlng = e.latlng;
          onLocationSelected(latlng.lat, latlng.lng);
          
          if (selectedMarker) {
              map.removeLayer(selectedMarker);
          }

          selectedMarker = L.marker(latlng, { icon: arrowIcon }).addTo(map);
      });

      function onLocationSelected(lat, lng) {
        taskLocation = {
          lat: lat, 
          long: lng
        }
      }   
    }, 100);
  }
}