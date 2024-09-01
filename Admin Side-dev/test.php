<?php
include 'connection.php'; // Include the database connection

$conn = connection();

// Fetch distinct company names
$query = "SELECT DISTINCT company_name FROM candidate_list";
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
                <title>Admin Side RCVJ</title>
                <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Encode+Sans+Expanded:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
                <link rel="stylesheet" href="style.css">
                <link rel="stylesheet" href="mediaqueries.css">
</head>

<body>
    <div id="mySidebar" class="sidebar closed">
        <div class="sidebar-header">
            <h3>RCVJ Inc.</h3>
            <button class="toggle-btn" onclick="toggleNav()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
            <a href="index.html"><i class="fa-solid fa-suitcase"></i> <span>Jobs</span></a>
            <a href="smartsearch.html"><i class="fa-solid fa-magnifying-glass"></i> <span>Smart Search</span></a>
            <a href="candidates.html"><i class="fa-solid fa-user"></i></i> <span>Candidates</span></a>
            <a href="schedules.html" class="active"><i class="fa-solid fa-calendar"></i></i> <span>Schedules</span></a>
            <a href="partners.html"><i class="fa-solid fa-handshake"></i> <span>Partners</span></a>
            <a href="employees.html"><i class="fa-solid fa-user-tie"></i> <span>Employees</span></a>
        </div>

        <div id="header">
            <img id="logo" src="img/logo.png" alt="logo">
            <div class="profile">
                <img src="img/pfp.png" alt="Profile Picture">
                <span class="name">Admin</span>
            </div>
        </div>
    <div id="main">
    <div class="wrapper">
        <div class="container-calendar">
            <div id="left">
                <h1>Schedules</h1>
                <div id="event-section">
                    <h3>Add Schedule</h3>
                    <input type="date" id="eventDate">
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
        </div>
    </div>
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

        // Function to add events
        function addEvent() {
            let date = eventDateInput.value;
            let title = eventTitleInput.value;
            let description = eventDescriptionInput.value;
            let companyName = companyNameInput.value;

            if (date && title && companyName && description) {
                // Create a unique event ID
                let eventId = eventIdCounter++;

                // Add event to events array
                events.push({
                    id: eventId, date: date, title: title, description: description, companyName: companyName
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
                xhr.send(`job_title=${encodeURIComponent(title)}&company_name=${encodeURIComponent(companyName)}&candidate_name=${encodeURIComponent(description)}&scheduled_date=${encodeURIComponent(date)}`);
            }
        }

        // Function to delete an event by ID
        function deleteEvent(eventId) {
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
        }

        // Function to display reminders
        function displayReminders() {
            reminderList.innerHTML = "";

            // Get the selected date from the calendar
            let selectedDate = document.querySelector(".date-picker.selected");
            
            if (selectedDate) {
                let date = parseInt(selectedDate.getAttribute("data-date"));
                let month = parseInt(selectedDate.getAttribute("data-month")) - 1; // Adjust for zero-based month
                let year = parseInt(selectedDate.getAttribute("data-year"));

                // Filter events for the selected date
                let filteredEvents = events.filter(event => {
                    let eventDate = new Date(event.date);
                    return eventDate.getDate() === date &&
                        eventDate.getMonth() === month &&
                        eventDate.getFullYear() === year;
                });

                // Display filtered events in the reminder list
                filteredEvents.forEach(event => {
                    let listItem = document.createElement("li");
                    listItem.innerHTML =
                        `<strong>${event.title}</strong> (<i>${event.companyName}</i>) - 
                        ${event.description} on 
                        ${new Date(event.date).toLocaleDateString()}`;

                    // Add a delete button for each reminder item
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
            for (let i = 0; i < eventsOnDate.length; i++) {
                let event = eventsOnDate[i];
                let eventDate = new Date(event.date);
                let eventText = `<strong>${event.title}</strong> - 
                    ${event.description} on 
                    ${eventDate.toLocaleDateString()}`;
                let eventElement = document.createElement("p");
                eventElement.innerHTML = eventText;
                tooltip.appendChild(eventElement);
            }
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

        // Function to update candidates based on selected job title
        function updateCandidates() {
            let jobTitle = document.getElementById("eventTitle").value;
            let eventDescription = document.getElementById("eventDescription");

            if (jobTitle) {
                let xhr = new XMLHttpRequest();
                xhr.open("GET", "ajax.php?jobTitle=" + encodeURIComponent(jobTitle), true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        let candidates = JSON.parse(xhr.responseText);
                        eventDescription.innerHTML = '<option value="" disabled selected>Candidate Name</option>';
                        for (let i = 0; i < candidates.length; i++) {
                            let option = document.createElement("option");
                            option.value = candidates[i];
                            option.text = candidates[i];
                            eventDescription.add(option);
                        }
                    }
                };
                xhr.send();
            } else {
                eventDescription.innerHTML = '<option value="" disabled selected>Candidate Name</option>';
            }
        }

        // Function to fetch and display existing events
        function loadExistingEvents() {
            let xhr = new XMLHttpRequest();
            xhr.open("GET", "fetch_events.php", true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    let eventsFromServer = JSON.parse(xhr.responseText);
                    // Iterate through events and add them to the events array
                    eventsFromServer.forEach(event => {
                        events.push({
                            id: event.id, 
                            date: event.scheduled_date, 
                            title: event.job_title, 
                            description: event.candidate_name, 
                            companyName: event.company_name
                        });
                    });
                    // Update the calendar and reminders
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
    </script>
</body>

</html>
