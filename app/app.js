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

// Define the cache expiry time (e.g., 1 hour in milliseconds)
const CACHE_EXPIRY_TIME = 60 * 60 * 1000;

// Get the current URL's query parameters
const params = new URLSearchParams(window.location.search);

// Get the 'page' parameter or default to 'home'
const page = params.get('page') ? params.get('page') : 'home';

// Switch statement to handle different pages
switch (page) {
    case 'home':
        console.log("You are on the map page");

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
    case 'today':
        console.log("You are on the Tasks page");

        
        document.getElementById('prevDay').addEventListener('click', () => {
            changeDay('previous');
        });

        document.getElementById('nextDay').addEventListener('click', () => {
            changeDay('next');
        });

        function changeDay(action) {
            var formData = new FormData();
            formData.append('action', action);

            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('date')) {
                var date = urlParams.get('date');
                formData.append('date', date);
            }

            request('php/updateDay.php', formData, function(response) {
                window.location = '/app/index.php?page=today&date='+response;
            });
        }

        // Task handling
        var taskID = null
        function setHighlightedTask (taskid) {
            taskID = taskid
        }

        window.addEventListener('DOMContentLoaded', initializeDatepicker);

        let datepickerInstance;
        let selectedDate;
        
        function initializeDatepicker() {
            const datepickerEl = document.getElementById('datepicker');
        
            if (datepickerEl && !datepickerInstance) {
                // Get today's date and ensure it's a valid Date object
                const today = new Date();
                selectedDate = today

                // Initialize the datepicker
                datepickerInstance = new Datepicker(datepickerEl, {
                    autoselect: true,
                    defaultViewDate: today, 
                    inline: true 
                });
        
                // Manually set the default date
                datepickerInstance.setDate(today);
        
                // Listen for changes
                datepickerEl.addEventListener('changeDate', (event) => {
                    selectedDate = event.detail.date;
                    console.log("test", selectedDate)
                });
            }
        }
        

        function getTaskStartTime() {
            var date = selectedDate;
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

        function startTask() {            
            if (navigator.geolocation) {
                // Get the user's current position
                navigator.geolocation.getCurrentPosition(function(position) {
                    var latitude = position.coords.latitude;
                    var longitude = position.coords.longitude;
        
                    var formData = new FormData();
                    formData.append('taskId', taskID);
                    formData.append('startTime', getTaskStartTime());
                    formData.append('latitude', latitude);  // Append latitude
                    formData.append('longitude', longitude);  // Append longitude
        
                    // Send the data to the PHP script
                    request('php/startUserTask.php', formData, function(response) {
                        let parsedResponse;
                        try {
                            parsedResponse = JSON.parse(response); // Parse response as JSON
                        } catch (e) {
                            alert("Error parsing response from server.");
                            return;
                        }
                    
                        // Check if the response indicates success
                        if (parsedResponse.success) {
                            window.location = '/app/task/'; // Redirect after success
                        } else {
                            alert(parsedResponse.error || "An error occurred.");
                        }
                    });
                }, function(error) {
                    console.log('Geolocation error: ', error);
                    // Handle geolocation error if user denies location access
                });
            } else {
                console.log("Geolocation is not supported by this browser.");
            }
        }

        
        break;
    case 'profile':
        console.log("You are on the Profile page");

        function updateProfileInfo(data) {
            document.getElementById('fullname').innerText = data.firstname + " " + data.lastname;
            document.getElementById('email').innerText = data.email;
            document.getElementById('company').innerText = data.organization_label_name;
            document.getElementById('cvr').innerText = "CVR: DK" + data.organization_cvr;
        }

        // Check if the profile data is already cached and still valid
        var cachedData = localStorage.getItem('profileData');
        var cachedTimestamp = localStorage.getItem('profileDataTimestamp');
        var currentTime = Date.now();

        if (cachedData && cachedTimestamp && (currentTime - cachedTimestamp < CACHE_EXPIRY_TIME)) {
            // Use cached data if it's still valid
            updateProfileInfo(JSON.parse(cachedData));
            console.log("Used cached profiledata.")
        } else {
            // Fetch data from the server if not cached or cache is expired
            request('php/getUserInformation.php', null, function(response) {
                try {
                    // Parse the JSON response
                    const data = JSON.parse(response);

                    if (data.error) {
                        // Handle error response
                        alert('Error fetching user information: ' + data.error);
                    } else {
                        // Cache the data and update the timestamp
                        localStorage.setItem('profileData', JSON.stringify(data));
                        localStorage.setItem('profileDataTimestamp', currentTime);

                        // Update the profile information
                        updateProfileInfo(data);
                        console.log("Used server loaded profiledata.")
                    }
                } catch (e) {
                    // Handle parsing errors
                    alert('Error parsing server response: ' + response);
                }
            });
        }

        function logout() {
            request('php/logout.php', false, function(data) {
                if(data === '0') {
                    window.location = 'login.php';
                }
            });
        }

        break;
    default:
        console.log("Page not found, redirecting to home");
        window.location.href = "?page=home";
        break;
}

function login() {
	request('php/auth.php', '#loginForm', function(data) {
        console.log(data);
		document.getElementById('errs').innerHTML = "";
		var transition = document.getElementById('errs').style.transition;
		document.getElementById('errs').style.transition = "none";
		document.getElementById('errs').style.opacity = 0;
		switch(data) {
			case '0':
				window.location = 'index.php';
				break;
			case '1':
				document.getElementById('errs').innerHTML += '<div class="err">Incorrect username or password</div>';
				break;
			case '2':
				document.getElementById('errs').innerHTML += '<div class="err">Failed to connect to database. Please try again later.</div>';
				break;
			case '3':
				document.getElementById('errs').innerHTML += '<div class="err">Invalid data or missing data.</div>';
				break;
			case '4':
				document.getElementById('errs').innerHTML += '<div class="err">Your email has not been validated. Please check your email for a validation link or <a href="./validate">click here</a> to send another link</div>';
				break;
			default:
				document.getElementById('errs').innerHTML += '<div class="err">An unknown error occurred. Please try again later.</div>';
		}
		setTimeout(function() {
			document.getElementById('errs').style.transition = transition;
			document.getElementById('errs').style.opacity = 1;
		}, 10);
	});
}