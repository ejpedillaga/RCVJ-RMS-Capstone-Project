<?php
include 'connection.php'; // Include the database connection
include 'session_check.php';
$conn = connection();

// Fetch distinct company names
$query = "SELECT DISTINCT company_name FROM candidate_list WHERE status = 'Approved' AND deployment_status = 'Pending'";
$result = mysqli_query($conn, $query);
$company_names = [];
while ($row = mysqli_fetch_assoc($result)) {
    $company_names[] = htmlspecialchars($row['company_name']);
}

// Function to fetch job titles based on company
function fetchJobTitles($company) {
    global $conn;
    $query = "SELECT DISTINCT job_title FROM candidate_list WHERE company_name = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $company);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $job_titles = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $job_titles[] = htmlspecialchars($row['job_title']);
    }
    return $job_titles;
}

// Function to fetch candidates based on job title
function fetchCandidates($jobTitle) {
    global $conn;
    $query = "SELECT DISTINCT full_name FROM candidate_list WHERE job_title = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $jobTitle);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $full_names = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $full_names[] = htmlspecialchars($row['full_name']);
    }
    return $full_names;
}

mysqli_close($conn);
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, 
                initial-scale=1.0">
                <title>Schedules | RCVJ, Inc.</title>
                <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Encode+Sans+Expanded:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
                <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>"></link>
                <link rel="stylesheet" href="mediaqueries.css?=<?php echo filemtime('mediaqueries.css'); ?>"></link>
                <link rel="apple-touch-icon" sizes="180x180" href="rcvj-logo/apple-touch-icon.png">
                <link rel="icon" type="image/png" sizes="32x32" href="rcvj-logo/favicon-32x32.png">
                <link rel="icon" type="image/png" sizes="16x16" href="rcvj-logo/favicon-16x16.png">
                <link rel="manifest" href="rcvj-logo/site.webmanifest">
</head>

<body>
    <div id="mySidebar" class="sidebar closed">
        <div class="sidebar-header">
            <h3>RCVJ Inc.</h3>
            <button class="toggle-btn" onclick="toggleNav()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
            <a href="dashboard.php"><i class="fa-solid fa-chart-line"></i> <span>Dashboard</span></a>
            <a href="jobs.html"><i class="fa-solid fa-suitcase"></i> <span>Jobs</span></a>
            <a href="smartsearch.php"><i class="fa-solid fa-magnifying-glass"></i> <span>Smart Search</span></a>
            <a href="candidates.php"><i class="fa-solid fa-user"></i></i> <span>Candidates</span></a>
            <a href="schedules.php" class="active"><i class="fa-solid fa-calendar"></i></i> <span>Schedules</span></a>
            <a href="partners.php"><i class="fa-solid fa-handshake"></i> <span>Partners</span></a>
            <a href="chatbot.php"><i class="fa-solid fa-robot"></i> <span>Chatbot</span></a>
            <a href="activity_log.php"><i class="fa-solid fa-list"></i> <span>Activity Log</span></a> 

        </div>

        <div id="header">
            <img id="logo" src="img/logo.png" alt="logo">
            <div class="profile">
                <img src="img/pfp.png" alt="Profile Picture">
                <span class="name"><?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                <!-- LOGOUT -->
                <button class="logout-btn" onclick="confirmLogout()">
                    <i class="fas fa-sign-out-alt fa-lg"></i>
                </button>
            </div>
        </div>
    <div id="main">
    <div class="wrapper">
        <div class="container-calendar">
            <div id="left">
                <h1>Schedules</h1>
                <div id="event-section">
                    <h3>Add Schedule</h3>
                    <input type="text" placeholder="Select a date" onfocus="(this.type='date')" onblur="(this.type='text')" id="eventDate">
                    
                    <div id="time-section" style="display: flex; width: 90%; align-items: center;">
                        <input type="time" id="eventStartTime" placeholder="Start Time"> <i class="fa fa-arrow-right" aria-hidden="true"></i>
                        <input type="time" id="eventEndTime" placeholder="End Time">
                    </div>

                    <select id="companyName" onchange="updateJobTitles()">
                        <option value="" disabled selected>Company Name</option>
                        <?php foreach ($company_names as $company_name): ?>
                            <option value="<?= $company_name ?>"><?= $company_name ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select id="eventTitle" onchange="updateCandidates()">
                        <option value="" disabled selected>Job Title</option>
                    </select>
                    
                    <select id="eventDescription">
                        <option value="" disabled selected>Candidate Name</option>
                    </select>
                    <br>
                    <button id="addEvent" onclick="addEvent()">Add</button>
                </div>
            </div>
            <div id="right">
                <h3 id="monthAndYear"></h3>
                <div class="button-container-calendar">
                    <button id="previous"
                            onclick="previous()">
                        ‹
                    </button>
                    <button id="next"
                            onclick="next()">
                        ›
                    </button>
                </div>
                <table class="table-calendar"
                    id="calendar"
                    data-lang="en">
                    <thead id="thead-month"></thead>
                    <!-- Table body for displaying the calendar -->
                    <tbody id="calendar-body"></tbody>
                </table>
                <div class="footer-container-calendar">
                    <label for="month">Jump To: </label>
                    <!-- Dropdowns to select a specific month and year -->
                    <select id="month" onchange="jump()">
                        <option value=0>Jan</option>
                        <option value=1>Feb</option>
                        <option value=2>Mar</option>
                        <option value=3>Apr</option>
                        <option value=4>May</option>
                        <option value=5>Jun</option>
                        <option value=6>Jul</option>
                        <option value=7>Aug</option>
                        <option value=8>Sep</option>
                        <option value=9>Oct</option>
                        <option value=10>Nov</option>
                        <option value=11>Dec</option>
                    </select>
                    <!-- Dropdown to select a specific year -->
                    <select id="year" onchange="jump()"></select>
                </div>
            </div>
            <div id="reminder-section">
                <h3>Upcoming Schedules</h3>
                    <!-- List to display reminders -->
                    <ul id="reminderList">
                        <li data-event-id="1">
                            <strong>Event Title</strong>
                            - Event Description on Event Date
                            <button class="delete-event"
                                onclick="deleteEvent(1)">
                                Delete
                            </button>
                        </li>
                    </ul>
            </div>
        </div>
    </div>

    <!-- Overlay --> 
    <div class="overlay" id="overlay"></div>

    <div class="popup-schedule" id="info-sched">
        <!-- Back Button -->
        <div class="addpartners-back-button" onclick="hideInfo()">
            <i class="fas fa-chevron-left"></i> Back
        </div>
        <h2 style="margin-left: 1rem;">Candidate List</h2>
        <ul id="candidateList"></ul>
                
    </div>
</div>
<div class="shape-container2">
    <div class="rectangle-4"></div>
    <div class="rectangle-5"></div>
</div>    
<script>
                // Define an array to store events
        let events = [];

        // letiables to store event input fields and reminder list
        let eventDateInput =
            document.getElementById("eventDate");
        let eventTitleInput =
            document.getElementById("eventTitle");
        let eventDescriptionInput =
            document.getElementById("eventDescription");
        let companyNameInput = 
            document.getElementById("companyName");
        let reminderList =
            document.getElementById("reminderList");

        // Counter to generate unique event IDs
        let eventIdCounter = 1;

        function addEvent() {
            // Get input elements by their IDs
            let eventDateInput = document.getElementById("eventDate");
            let eventStartTimeInput = document.getElementById("eventStartTime");
            let eventEndTimeInput = document.getElementById("eventEndTime");
            let eventTitleInput = document.getElementById("eventTitle");
            let eventDescriptionInput = document.getElementById("eventDescription");
            let companyNameInput = document.getElementById("companyName");

            // Retrieve values from the inputs
            let date = eventDateInput.value;
            let startTime = eventStartTimeInput.value; // 24-hour format
            let endTime = eventEndTimeInput.value; // 24-hour format
            let title = eventTitleInput.value;
            let description = eventDescriptionInput.value;
            let companyName = companyNameInput.value;

            if (date && startTime && endTime && title && companyName && description) {
                // Validate time to ensure end time isn't earlier than start time
                if (endTime <= startTime) {
                    alert("End time cannot be earlier than or equal to start time.");
                    return;
                }

                // Create a unique event ID
                let eventId = eventIdCounter++;

                // Add event to events array
                events.push({
                    id: eventId,
                    date: date,
                    startTime: startTime, // Store in 24-hour format
                    endTime: endTime, // Store in 24-hour format
                    title: title,
                    description: description,
                    companyName: companyName
                });

                // Send event data to the server
                let xhr = new XMLHttpRequest();
                xhr.open("POST", "add_schedule.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        let response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            // Successfully added
                            showCalendar(currentMonth, currentYear);
                            eventDateInput.value = "";
                            eventStartTimeInput.value = "";
                            eventEndTimeInput.value = "";
                            eventTitleInput.value = "";
                            eventDescriptionInput.value = "";
                            companyNameInput.value = "";
                            displayReminders();
                        } else {
                            // Handle error
                            alert('Error: ' + response.message);
                        }
                    }
                };
                xhr.send(`job_title=${encodeURIComponent(title)}&company_name=${encodeURIComponent(companyName)}&candidate_name=${encodeURIComponent(description)}&scheduled_date=${encodeURIComponent(date)}&start_time=${encodeURIComponent(startTime)}&end_time=${encodeURIComponent(endTime)}`);
            }
        }

       // Function to delete an event by ID
        function deleteEvent(eventId) {
            // Ask for confirmation before proceeding with deletion
            if (confirm("Are you sure you want to delete this scheduled interview?")) {
                // Send an AJAX request to delete the event from the database
                let xhr = new XMLHttpRequest();
                xhr.open("GET", "delete_events.php?eventId=" + encodeURIComponent(eventId), true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        let response = JSON.parse(xhr.responseText);
                        if (response.status === "success") {
                            // Remove the event from the events array
                            let eventIndex = events.findIndex(event => event.id === eventId);
                            if (eventIndex !== -1) {
                                events.splice(eventIndex, 1);
                                showCalendar(currentMonth, currentYear);
                                displayReminders();
                            }
                        } else {
                            alert("Error: " + response.message);
                        }
                    }
                };
                xhr.send();
            } else {
                // User canceled the deletion
                console.log("Event deletion canceled.");
            }
        }

        function handleTimeBlur(input, type) {
            const timeValue = input.value; // This will be in HH:mm (24-hour format)
            
            // Format it for display in the input as 12-hour format
            const formattedTime = formatTime(timeValue);

            // Set the input value back to the formatted time for display
            input.value = formattedTime;

            // You might also want to save the original 24-hour time in a hidden field or similar
            if (type === 'start') {
                document.getElementById('start-time-hidden').value = timeValue; // Hidden input to store original value
            } else if (type === 'end') {
                document.getElementById('end-time-hidden').value = timeValue; // Hidden input to store original value
            }
        }

        function formatTime(time) {
            const [hour, minute] = time.split(':');
            let hour12 = parseInt(hour);
            const period = hour12 >= 12 ? 'PM' : 'AM';

            if (hour12 > 12) {
                hour12 -= 12;
            } else if (hour12 === 0) {
                hour12 = 12; // 12 AM
            }

            return `${hour12}:${minute} ${period}`;
        }

        // Function to display reminders
        function displayReminders() {
            reminderList.innerHTML = "";

            let selectedDate = document.querySelector(".date-picker.selected");

            if (selectedDate) {
                let date = parseInt(selectedDate.getAttribute("data-date"));
                let month = parseInt(selectedDate.getAttribute("data-month")) - 1;
                let year = parseInt(selectedDate.getAttribute("data-year"));

                let filteredEvents = events.filter(event => {
                    let eventDate = new Date(event.date);
                    return (
                        eventDate.getDate() === date &&
                        eventDate.getMonth() === month &&
                        eventDate.getFullYear() === year
                    );
                });

                if (filteredEvents.length === 0) {
                    let noEventsMessage = document.createElement("li");
                    noEventsMessage.textContent = "No events available.";
                    noEventsMessage.style.fontStyle = "italic";
                    reminderList.appendChild(noEventsMessage);
                } else {
                    filteredEvents.forEach(event => {
                        let listItem = document.createElement("li");
                        listItem.innerHTML = `
                            <strong>${event.description}</strong> <br> 
                            ${event.title} - <i>${event.companyName}</i> <br> 
                            ${new Date(event.date).toLocaleDateString()} <br>
                            Time: ${formatTime(event.startTime)} - ${formatTime(event.endTime)}
                        `;

                        let deleteButton = document.createElement("button");
                        deleteButton.className = "delete-event";
                        deleteButton.textContent = "Delete";
                        deleteButton.onclick = function () {
                            deleteEvent(event.id);
                        };

                        listItem.appendChild(deleteButton);
                        reminderList.appendChild(listItem);
                    });
                }
            }
        }

        // Function to generate a range of 
        // years for the year select input
        function generate_year_range(start, end) {
            let years = "";
            for (let year = start; year <= end; year++) {
                years += "<option value='" +
                    year + "'>" + year + "</option>";
            }
            return years;
        }

        // Initialize date-related letiables
        today = new Date();
        currentMonth = today.getMonth();
        currentYear = today.getFullYear();
        selectYear = document.getElementById("year");
        selectMonth = document.getElementById("month");

        createYear = generate_year_range(1970, 2050);

        document.getElementById("year").innerHTML = createYear;

        let calendar = document.getElementById("calendar");

        let months = [
            "January",
            "February",
            "March",
            "April",
            "May",
            "June",
            "July",
            "August",
            "September",
            "October",
            "November",
            "December"
        ];
        let days = [
            "Sun", "Mon", "Tue", "Wed",
            "Thu", "Fri", "Sat"];

        $dataHead = "<tr>";
        for (dhead in days) {
            $dataHead += "<th data-days='" +
                days[dhead] + "'>" +
                days[dhead] + "</th>";
        }
        $dataHead += "</tr>";

        document.getElementById("thead-month").innerHTML = $dataHead;

        monthAndYear =
            document.getElementById("monthAndYear");
        showCalendar(currentMonth, currentYear);

        // Function to navigate to the next month
        function next() {
            currentYear = currentMonth === 11 ?
                currentYear + 1 : currentYear;
            currentMonth = (currentMonth + 1) % 12;
            showCalendar(currentMonth, currentYear);
        }

        // Function to navigate to the previous month
        function previous() {
            currentYear = currentMonth === 0 ?
                currentYear - 1 : currentYear;
            currentMonth = currentMonth === 0 ?
                11 : currentMonth - 1;
            showCalendar(currentMonth, currentYear);
        }

        // Function to jump to a specific month and year
        function jump() {
            currentYear = parseInt(selectYear.value);
            currentMonth = parseInt(selectMonth.value);
            showCalendar(currentMonth, currentYear);
        }

        // Function to display the calendar
        function showCalendar(month, year) {
            let firstDay = new Date(year, month, 1).getDay();
            tbl = document.getElementById("calendar-body");
            tbl.innerHTML = "";
            monthAndYear.innerHTML = months[month] + " " + year;
            selectYear.value = year;
            selectMonth.value = month;

            let date = 1;
            for (let i = 0; i < 6; i++) {
                let row = document.createElement("tr");
                for (let j = 0; j < 7; j++) {
                    if (i === 0 && j < firstDay) {
                        cell = document.createElement("td");
                        cellText = document.createTextNode("");
                        cell.appendChild(cellText);
                        row.appendChild(cell);
                    } else if (date > daysInMonth(month, year)) {
                        break;
                    } else {
                        cell = document.createElement("td");
                        cell.setAttribute("data-date", date);
                        cell.setAttribute("data-month", month + 1);
                        cell.setAttribute("data-year", year);
                        cell.setAttribute("data-month_name", months[month]);
                        cell.className = "date-picker";
                        cell.innerHTML = "<span>" + date + "</span";

                        if (date === today.getDate() && year === today.getFullYear() && month === today.getMonth()) {
                            cell.className = "date-picker selected";
                        }

                        // Check if there are events on this date
                        if (hasEventOnDate(date, month, year)) {
                            cell.classList.add("event-marker");
                            cell.appendChild(createEventTooltip(date, month, year));
                        }

                        cell.onclick = function() {
                            document.querySelectorAll(".date-picker").forEach(el => el.classList.remove("selected"));
                            this.classList.add("selected");
                            displayReminders();
                        };

                        row.appendChild(cell);
                        date++;
                    }
                }
                tbl.appendChild(row);
            }

            displayReminders();
        }

        // Function to create an event tooltip
        function createEventTooltip(date, month, year) {
            let tooltip = document.createElement("div");
            tooltip.className = "event-tooltip";

            let eventsOnDate = getEventsOnDate(date, month, year);
            let eventCount = eventsOnDate.length;

            // Display the number of events on the tooltip
            let tooltipText = eventCount === 1 
                ? `1 interview` 
                : `${eventCount} interviews`;

            let eventCountElement = document.createElement("p");
            eventCountElement.textContent = tooltipText;

            tooltip.appendChild(eventCountElement);
            return tooltip;
        }

        // Function to get events on a specific date
        function getEventsOnDate(date, month, year) {
            return events.filter(function (event) {
                let eventDate = new Date(event.date);
                return (
                    eventDate.getDate() === date &&
                    eventDate.getMonth() === month &&
                    eventDate.getFullYear() === year
                );
            });
        }

        // Function to check if there are events on a specific date
        function hasEventOnDate(date, month, year) {
            return getEventsOnDate(date, month, year).length > 0;
        }

        // Function to get the number of days in a month
        function daysInMonth(iMonth, iYear) {
            return 32 - new Date(iYear, iMonth, 32).getDate();
        }

        // Call the showCalendar function initially to display the calendar
        showCalendar(currentMonth, currentYear);

        function updateJobTitles() {
            let company = document.getElementById("companyName").value;
            let eventTitle = document.getElementById("eventTitle");
            let eventDescription = document.getElementById("eventDescription");

            if (company) {
                let xhr = new XMLHttpRequest();
                xhr.open("GET", "ajax.php?company=" + encodeURIComponent(company), true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        let jobTitles = JSON.parse(xhr.responseText);
                        eventTitle.innerHTML = '<option value="" disabled selected>Job Title</option>';
                        eventDescription.innerHTML = '<option value="" disabled selected>Candidate Name</option>';
                        for (let i = 0; i < jobTitles.length; i++) {
                            let option = document.createElement("option");
                            option.value = jobTitles[i];
                            option.text = jobTitles[i];
                            eventTitle.add(option);
                        }
                    }
                };
                xhr.send();
            } else {
                eventTitle.innerHTML = '<option value="" disabled selected>Job Title</option>';
                eventDescription.innerHTML = '<option value="" disabled selected>Candidate Name</option>';
            }
        }

        function updateCandidates() {
            let jobTitle = document.getElementById("eventTitle").value;

            if (jobTitle) {
                let xhr = new XMLHttpRequest();
                xhr.open("GET", "ajax.php?jobTitle=" + encodeURIComponent(jobTitle), true);
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        let candidates = JSON.parse(xhr.responseText);
                        let candidateList = document.getElementById("candidateList");

                        // Clear previous list
                        candidateList.innerHTML = '';

                        // Add new candidates with profile image and other details
                        candidates.forEach(candidate => {
                            let profileImage = candidate.profile_image 
                                ? `data:image/jpeg;base64,${candidate.profile_image}` 
                                : 'img/user.svg'; // Use default image if profile_image is null
                            let listItem = document.createElement("li");

                            listItem.innerHTML = `
                                <div class="candidate-item">
                                    <img src="${profileImage}" 
                                        alt="${candidate.full_name}" width="100" height="100">
                                    <div class="candidate-details">
                                        <strong>${candidate.full_name}</strong><br>
                                        <span>Location: ${candidate.location}</span><br>
                                        <span>Phone: ${candidate.phone}</span><br>
                                        <span>Email: ${candidate.email}</span>
                                    </div>
                                </div>
                            `;

                            listItem.addEventListener('click', function () {
                                // Set the selected candidate name in the dropdown
                                let eventDescription = document.getElementById("eventDescription");
                                eventDescription.innerHTML = `<option value="${candidate.full_name}" selected>${candidate.full_name}</option>`;
                                
                                // Show the dropdown again and hide the popup
                                eventDescription.style.display = 'block';
                                hideInfo();
                            });

                            candidateList.appendChild(listItem);
                        });

                        // Hide the dropdown and show popup
                        document.getElementById('eventDescription').style.display = 'none';
                        document.getElementById('info-sched').style.display = 'block';
                        document.getElementById('info-sched').classList.add('show');
                        document.getElementById('overlay').classList.add('show');
                    }
                };
                xhr.send();
            }
        }


        function hideInfo() {
            document.getElementById('info-sched').style.display = 'none';
            document.getElementById('info-sched').classList.remove('show');
            document.getElementById('overlay').classList.remove('show');
            document.getElementById('eventDescription').style.display = 'block';
        }

        // Attach click event to eventDescription dropdown to trigger the popup
        document.getElementById('eventDescription').addEventListener('click', function (e) {
            e.preventDefault(); // Prevent default dropdown behavior
            updateCandidates();
        });

        // Function to fetch and display existing events
        function loadExistingEvents() {
            let xhr = new XMLHttpRequest();
            xhr.open("GET", "fetch_events.php", true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    let eventsFromServer = JSON.parse(xhr.responseText);
                    eventsFromServer.forEach(event => {
                        events.push({
                            id: event.id,
                            date: event.scheduled_date,
                            startTime: event.start_time,
                            endTime: event.end_time,
                            title: event.job_title,
                            description: event.candidate_name,
                            companyName: event.company_name
                        });
                    });
                    showCalendar(currentMonth, currentYear);
                    displayReminders();
                } else {
                    console.error('Failed to fetch events');
                }
            };
            xhr.send();
        }

        // Call this function to load existing events when the page loads
        loadExistingEvents();
        
        
        function confirmOpenLink(event) {
            var userConfirmation = confirm("This link will take you to the Tidio website where you can customize the Tidio Chatbot. Please note that a login is required to access the features. Do you want to continue?");
                
                if (!userConfirmation) {
                    event.preventDefault();
                    return false;
                }
                
                return true;
        }
    </script>
</body>

</html>
