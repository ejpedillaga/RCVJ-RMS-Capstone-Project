let skillsSet = new Set();
document.addEventListener('DOMContentLoaded', function() {
    // Initialize ClassicEditor for job posting description
    ClassicEditor
        .create(document.querySelector('#jobposting-description'))
        .catch(error => {
            console.error(error);
        });

    // Tab functionality
    const tab1 = document.getElementById('tab1');
    const tab2 = document.getElementById('tab2');
    const tab1Content = document.getElementById('tab1-content');
    const tab2Content = document.getElementById('tab2-content');

    tab1.addEventListener('click', function() {
        tab1.classList.add('open');
        tab1.classList.remove('closed');
        tab2.classList.add('closed');
        tab2.classList.remove('open');
        tab1Content.classList.add('active');
        tab2Content.classList.remove('active');
    });

    tab2.addEventListener('click', function() {
        tab2.classList.add('open');
        tab2.classList.remove('closed');
        tab1.classList.add('closed');
        tab1.classList.remove('open');
        tab2Content.classList.add('active');
        tab1Content.classList.remove('active');
    });

    // Initialize the first tab as active
    tab1.click();


    // Popup functionality
    document.getElementById('popup').addEventListener('click', function(event) {
        event.stopPropagation();
    });

    // Prevent closing the popup when clicking outside
    document.getElementById('overlay').addEventListener('click', function() {
        closePopup();
    });
});

function redirectTo(url) {
    window.location.href = url;
}

/*Sidebar Nav*/
function toggleNav() {
    const sidebar = document.getElementById("mySidebar");
    sidebar.classList.toggle("closed");
    if (window.innerWidth <= 768) {
        sidebar.classList.toggle("open");
    }
}

/*Toggle Button*/
function toggle(element) {
    const openOption = document.querySelector('.toggle-option.open');
    const closedOption = document.querySelector('.toggle-option.closed');
    openOption.classList.toggle('open');
    openOption.classList.toggle('closed');
    closedOption.classList.toggle('open');
    closedOption.classList.toggle('closed');
}

/*Dialog Box*/
function showDialog() {
    document.getElementById('dialogBox').style.display = 'block';
    document.getElementById('dialogBox').classList.add('show');
    document.getElementById('overlay').classList.add('show');

    document.getElementById('addpartners-company-name').value = '';
    document.getElementById('addpartners-industry').value = '';
    document.getElementById('addpartners-location').value = '';
    document.getElementById('addpartners-company-description').value = '';
}

function hideDialog() {
    document.getElementById('dialogBox').style.display = 'none';
    document.getElementById('dialogBox').classList.remove('show');
    document.getElementById('overlay').classList.remove('show');

    // Reset the logo preview
    const logoPreview = document.getElementById('logo-preview');
    const uploadPlaceholder = document.getElementById('upload-placeholder');
    const logoUpload = document.getElementById('logo-upload');
    
    logoPreview.style.display = 'none';
    logoPreview.src = '';
    uploadPlaceholder.style.display = 'flex';
    logoUpload.value = ''; // Clear the file input
}

function showEmployeeDialog() {
    document.getElementById('dialogBox').style.display = 'block';
    document.getElementById('dialogBox').classList.add('show');
    document.getElementById('overlay').classList.add('show');

    document.getElementById('addemployees-firstname').value = '';
    document.getElementById('addemployees-lastname').value = '';
    document.getElementById('addemployees-userid').value = '';
    document.getElementById('addemployees-password').value = '';
    document.getElementById('addemployees-admin-password').value = '';
}

function hideEmployeeDialog() {
    document.getElementById('dialogBox').style.display = 'none';
    document.getElementById('dialogBox').classList.remove('show');
    document.getElementById('overlay').classList.remove('show');
}

/*Dialog Box Delete*/
function showDialogDelete() {
    document.getElementById('dialogBox-delete').style.display = 'block';
    document.getElementById('overlay').classList.add('show');
}

function hideDialogDelete() {
    document.getElementById('dialogBox-delete').style.display = 'none';
    document.getElementById('overlay').classList.remove('show');
}

function showDialogDeletePartner(partnerId) {
    deletePartnerId = partnerId;
    document.getElementById('dialogBox-delete').style.display = 'block';
    document.getElementById('overlay').classList.add('show');
}

function hideDialogDeletePartner(partnerId) {
    document.getElementById('dialogBox-delete').style.display = 'none';
    document.getElementById('overlay').classList.remove('show');
}

/*Dialog Box Edit*/
function showDialogEdit() {
    document.getElementById('dialogBox-edit').style.display = 'block';
}

function hideDialogEdit() {
    document.getElementById('dialogBox-edit').style.display = 'none';
}

function openThirdPopup() {
    document.getElementById('thirdPopup').classList.add('show');
    document.getElementById('overlay').classList.add('show');
    // Initialize pagination for the third popup
    initializePopupPagination('thirdPopup');
    initializeSkillsInput('thirdPopup','partner-jobposting-skills-input','partner-jobposting-skills-container');
    fetchOptions('thirdPopup');
}

function closeThirdPopup() {
    document.getElementById('thirdPopup').classList.remove('show');
    document.getElementById('overlay').classList.remove('show');

    // Remove all skill tags from the container but keep the input field
    const skillsContainer = document.querySelector('#thirdPopup .jobposting-skills-container');
    const skills = skillsContainer.querySelectorAll('.jobposting-skill');
    skills.forEach(skill => skillsContainer.removeChild(skill));

    // Clear the skills set
    skillsSet.clear();

    // Optionally, you might want to clear the input field as well
    //document.querySelector('#thirdPopup #third-jobposting-skills-input').value = '';
}

// Function to open a specific popup and initialize its spinner
function openPopup(popupId) {
    document.getElementById(popupId).classList.add('show');
    document.getElementById('overlay').classList.add('show');
    fetchOptions(popupId);
}


// Function to close a specific popup
function closePopup(popupId) {
    document.getElementById(popupId).classList.remove('show');
    document.getElementById('overlay').classList.remove('show');
    
    // Optionally clear skills and other form data
    const skillsContainer = document.querySelector(`#${popupId} .jobposting-skills-container`);
    const skills = skillsContainer.querySelectorAll('.jobposting-skill');
    skills.forEach(skill => skillsContainer.removeChild(skill));
    
    // Clear the skills set
    skillsSet.clear();
    
    // Optionally, clear the input field
    //document.querySelector(`#${popupId} #${popupId}-skills-input`).value = '';
}

// Open specific popups
function openJobPostingPopup() {
    openPopup('popup');
    initializeSkillsInput('popup', 'jobposting-skills-input', 'add-jobposting-skills-container');
}


function closeEditJobPopup() {
    document.getElementById('editJob-popup').classList.remove('show');
    document.getElementById('overlay').classList.remove('show');

    // Remove all skill tags from the container but keep the input field
    const skillsContainer = document.querySelector('#edit-jobposting-skills-container');
    const skills = skillsContainer.querySelectorAll('.jobposting-skill');
    skills.forEach(skill => skillsContainer.removeChild(skill));
    
    // Clear the skills set
    skillsSet.clear();
    
    // Optionally, you might want to clear the input field as well
    document.getElementById('#edit-jobposting-skills-input').value = '';
}

function previewLogo(event) {
    const input = event.target;
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const preview = document.getElementById('logo-preview');
            preview.src = e.target.result;
            preview.style.display = 'block';
            document.getElementById('upload-placeholder').style.display = 'none';
        };
        
        reader.readAsDataURL(file);
    }
}


function previewEditLogo(event) {
    const input = event.target;
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const preview = document.getElementById('edit-logo-preview');
            preview.src = e.target.result;
            preview.style.display = 'block';
            document.getElementById('edit-upload-placeholder').style.display = 'none';
        };
        
        reader.readAsDataURL(file);
    }
}
/* First attempt to populate tables
//Check what page is currently viewed
document.addEventListener('DOMContentLoaded', function() {
    // Get the current page URL or some identifier
    const currentPage = window.location.pathname;

    if (currentPage.includes('candidates.html')) {
        initCandidatesPage();
    } else if (currentPage.includes('rejected.html')) {
        initRejectedPage();
    }
    // Add more conditions for other pages if needed
});

function initCandidatesPage() {
    fetch('fetch_candidates.php')
        .then(response => response.json())
        .then(data => {
            console.log(data); // Log the data to verify
            populateTable(data);
        })
        .catch(error => console.error('Error fetching candidates:', error));

    function populateTable(candidates) {
        const tableBody = document.querySelector('table');
        candidates.forEach(candidate => {
            const row = document.createElement('tr');
            row.classList.add('tr1');

            row.innerHTML = `
                <td class="fullname">${candidate.full_name}</td>
                <td><strong>${candidate.job_title}</strong></td>
                <td>${candidate.company_name}</td>
                <td>${candidate.date_applied}</td>
                <td>
                    <select class="status-dropdown">
                        <option ${candidate.status === 'Interview' ? 'selected' : ''}>Interview</option>
                        <option ${candidate.status === 'Pending' ? 'selected' : ''}>Pending</option>
                        <option ${candidate.status === 'Rejected' ? 'selected' : ''}>Rejected</option>
                        <option ${candidate.status === 'Deployed' ? 'selected' : ''}>Deployed</option>
                    </select>
                </td>
                <td>
                    <i class="fa-solid fa-trash fa-2xl" style="color: #EF9B50; cursor: pointer;" onclick="showDialog()"></i>
                </td>
            `;
            tableBody.appendChild(row);
        });
    }

    function showDialog() {
        document.getElementById('dialogBox').style.display = 'block';
        document.getElementById('overlay').style.display = 'block';
    }

    function closeDialog() {
        document.getElementById('dialogBox').style.display = 'none';
        document.getElementById('overlay').style.display = 'none';
    }

    document.getElementById('overlay').addEventListener('click', closeDialog);
}

function initRejectedPage() {
    fetch('fetch_rejecteds.php')
        .then(response => response.json())
        .then(data => {
            console.log(data); // Log the data to verify
            populateTable(data);
        })
        .catch(error => console.error('Error fetching rejecteds:', error));

    function populateTable(rejecteds) {
        const tableBody = document.querySelector('table');
        rejecteds.forEach(rejected => {
            const row = document.createElement('tr');
            row.classList.add('tr1');

            row.innerHTML = `
                <td class="fullname">${rejected.full_name}</td>
                <td><strong>${rejected.remarks}</strong></td>
                <td>${rejected.date_rejected}</td>
                <td>
                    <i class="fa-solid fa-trash fa-2xl" style="color: #EF9B50; cursor: pointer;" onclick="showDialog()"></i>
                </td>
            `;
            tableBody.appendChild(row);
        });
    }

    function showDialog() {
        document.getElementById('dialogBox').style.display = 'block';
        document.getElementById('overlay').style.display = 'block';
    }

    function closeDialog() {
        document.getElementById('dialogBox').style.display = 'none';
        document.getElementById('overlay').style.display = 'none';
    }

    document.getElementById('overlay').addEventListener('click', closeDialog);
}

function initRejectedPage() {
    fetch('fetch_rejecteds.php')
        .then(response => response.json())
        .then(data => {
            console.log(data); // Log the data to verify
            populateTable(data);
        })
        .catch(error => console.error('Error fetching rejecteds:', error));

    function populateTable(rejecteds) {
        const tableBody = document.querySelector('table');
        rejecteds.forEach(rejected => {
            const row = document.createElement('tr');
            row.classList.add('tr1');

            row.innerHTML = `
                <td class="fullname">${rejected.full_name}</td>
                <td><strong>${rejected.remarks}</strong></td>
                <td>${rejected.date_rejected}</td>
                <td>
                    <i class="fa-solid fa-trash fa-2xl" style="color: #EF9B50; cursor: pointer;" onclick="showDialog()"></i>
                </td>
            `;
            tableBody.appendChild(row);
        });
    }

    function showDialog() {
        document.getElementById('dialogBox').style.display = 'block';
        document.getElementById('overlay').style.display = 'block';
    }

    function closeDialog() {
        document.getElementById('dialogBox').style.display = 'none';
        document.getElementById('overlay').style.display = 'none';
    }

    document.getElementById('overlay').addEventListener('click', closeDialog);
}

function initIndexPage() {
    // Add your initialization code for index.html
}
*/

document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname;

    if (currentPage.includes('candidates.html')) {
        fetchData('fetch_candidates.php', populateCandidatesTable);
    } else if (currentPage.includes('rejected.html')) {
        fetchData('fetch_rejects.php', populateRejectsTable);
    } else if (currentPage.includes('employees.html')){
        fetchData('fetch_employees.php', populateEmployeesTable)
    } else if (currentPage.includes('partners.html')){
        fetchData('fetch_partners.php', populatePartnersTable)
    } else if(document.querySelector('.container-calendar')){
        displaySchedule();
    } else if (currentPage.includes('index.html')) {
        fetchJobCounts();
        //Show Open Tab as default
        fetchJobsByStatus('Open');
        
        //Changes data based on active tab
        document.getElementById('tab1').addEventListener('click', function() {
            fetchJobsByStatus('Open');
        });

        document.getElementById('tab2').addEventListener('click', function() {
            fetchJobsByStatus('Closed');
        }); 
    } 
});
function displaySchedule(){
    // Define an array to store events
    let events = [];

    // letiables to store event input fields and reminder list
    let eventDateInput =
        document.getElementById("eventDate");
    let eventTitleInput =
        document.getElementById("eventTitle");
    let eventDescriptionInput =
        document.getElementById("eventDescription");
    let reminderList =
        document.getElementById("reminderList");

    // Counter to generate unique event IDs
    let eventIdCounter = 1;

    // Function to add events
    function addEvent() {
        let date = eventDateInput.value;
        let title = eventTitleInput.value;
        let description = eventDescriptionInput.value;

        if (date && title) {
            // Create a unique event ID
            let eventId = eventIdCounter++;

            events.push(
                {
                    id: eventId, date: date,
                    title: title,
                    description: description
                }
            );
            showCalendar(currentMonth, currentYear);
            eventDateInput.value = "";
            eventTitleInput.value = "";
            eventDescriptionInput.value = "";
            displayReminders();
        }
    }

    // Function to delete an event by ID
    function deleteEvent(eventId) {
        // Find the index of the event with the given ID
        let eventIndex =
            events.findIndex((event) =>
                event.id === eventId);

        if (eventIndex !== -1) {
            // Remove the event from the events array
            events.splice(eventIndex, 1);
            showCalendar(currentMonth, currentYear);
            displayReminders();
        }
    }

    // Function to display reminders
    function displayReminders() {
        reminderList.innerHTML = "";
        for (let i = 0; i < events.length; i++) {
            let event = events[i];
            let eventDate = new Date(event.date);
            if (eventDate.getMonth() ===
                currentMonth &&
                eventDate.getFullYear() ===
                currentYear) {
                let listItem = document.createElement("li");
                listItem.innerHTML =
                    `<strong>${event.title}</strong> - 
                ${event.description} on 
                ${eventDate.toLocaleDateString()}`;

                // Add a delete button for each reminder item
                let deleteButton =
                    document.createElement("button");
                deleteButton.className = "delete-event";
                deleteButton.textContent = "Delete";
                deleteButton.onclick = function () {
                    deleteEvent(event.id);
                };

                listItem.appendChild(deleteButton);
                reminderList.appendChild(listItem);
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

                    if (
                        date === today.getDate() &&
                        year === today.getFullYear() &&
                        month === today.getMonth()
                    ) {
                        cell.className = "date-picker selected";
                    }

                    // Check if there are events on this date
                    if (hasEventOnDate(date, month, year)) {
                        cell.classList.add("event-marker");
                        cell.appendChild(
                            createEventTooltip(date, month, year)
                    );
                    }

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
}

function fetchData(url, callback) {
    fetch(url)
        .then(response => response.json())
        .then(data => {
            console.log(data); // Log the data to verify
            callback(data);
        })
        .catch(error => console.error('Error fetching data:', error));
}

function populateTable(data, tableSelector, rowTemplate) {
    const tableBody = document.querySelector(tableSelector + ' tbody');
    tableBody.innerHTML = ''; // Clear existing rows
    data.forEach(item => {
        const row = document.createElement('tr');
        row.classList.add('tr1'); // Add the 'tr1' class to each row
        row.innerHTML = rowTemplate(item);
        tableBody.appendChild(row);
    });
}

function populateCandidatesTable(data) {
    const rowTemplate = (candidate) => `  
        <td id="fullname" class="fullname">${candidate.full_name}</td>
        <td id="job-title"><strong>${candidate.job_title}</strong></td>
        <td id="company-name">${candidate.company_name}</td>
        <td id="date">${candidate.date_applied}</td>
        <td>
            <select class="status-dropdown">
                <option ${candidate.status === 'Interview' ? 'selected' : ''}>Interview</option>
                <option ${candidate.status === 'Pending' ? 'selected' : ''}>Pending</option>
                <option ${candidate.status === 'Rejected' ? 'selected' : ''}>Rejected</option>
                <option ${candidate.status === 'Deployed' ? 'selected' : ''}>Deployed</option>
            </select>
        </td>
        <td>
            <i class="fa-solid fa-trash fa-2xl" style="color: #EF9B50; cursor: pointer;" onclick="showDialog()"></i>
        </td> 
    `;
    populateTable(data, 'table', rowTemplate);
}

function populateRejectsTable(data) {
    const rowTemplate = (reject) => `
        <td id="fullname" class="fullname">${reject.full_name}</td>
        <td id="remarks">${reject.remarks}</td>
        <td id="date">${reject.date_rejected}</td>
        <td><i class="fa-solid fa-rotate-left fa-2xl" style="color: #2C1875;"></i></td>
        <td><i class="fa-solid fa-trash fa-2xl" style="color: #EF9B50; cursor: pointer;" onclick="showEditDialog()"></i></td>
    `;
    populateTable(data, 'table', rowTemplate);
}

function populateEmployeesTable(data) {
    const rowTemplate = (employee) => `
        <td id="fullname" class="fullname">${employee.full_name}</td>
        <td id="date">${employee.date_added}</td>
        <td>
            <select class="status-dropdown">
                <option ${employee.status === 'Active' ? 'selected' : ''}>Active</option>
                <option ${employee.status === 'Inactive' ? 'selected' : ''}>Inactive</option>
            </select>
        </td>
        <td><i class="fa-solid fa-pen-to-square fa-2xl" style="color: #2C1875; cursor: pointer;" onclick="showEditDialog(${employee.employee_id})"></i></td>
        <td><i class="fa-solid fa-trash fa-2xl" style="color: #EF9B50; cursor: pointer;" onclick="showDialogDelete(${employee.employee_id})"></i></td>
    `;
    populateTable(data, 'table', rowTemplate);
}

function populatePartnersTable(data) {
    const rowTemplate = (partner) => `
        <td>
        <img src="data:image/jpeg;base64,${partner.logo}" alt="${partner.company_name}" width="100"></td>
        <td id="company-name">${partner.company_name}</td>
        <td><i class="fa-solid fa fa-file fa-2xl" style="color: #2C1875; cursor: pointer;" onclick="openThirdPopup()"></i></td>
        <td id="date">${partner.date_added}</td>
        <td><i class="fa-solid fa-pen-to-square fa-2xl" style="color: #2C1875; cursor: pointer;" onclick="showEditPartnerDialog(${partner.id})"></i></td>
        <td><i class="fa-solid fa-trash fa-2xl" style="color: #EF9B50; cursor: pointer;" onclick="showDialogDeletePartner(${partner.id})"></i></td>
    `;
    
    populateTable(data, 'table', rowTemplate);
}

function populateJobsTable(containerSelector, data) {
    const rowTemplate = (job) => `
        <td><img src="data:image/jpeg;base64,${job.company_logo}" alt="${job.company_name} Logo"></td>
        <td id="company-name"><strong>${job.job_title}</strong><br>${job.job_location}</td>
        <td id="date">${job.date_posted}</td>
        <td id="available">${job.job_candidates}</td>
        <td>
            <select id="job-status-dropdown" class="status-dropdown">
                <option ${job.job_status === 'Open' ? 'selected' : ''}>Open</option>
                <option ${job.job_status === 'Closed' ? 'selected' : ''}>Closed</option>
            </select>
        </td>
        <td id="edit"><i class="fa-solid fa-pen-to-square fa-2xl" style="color: #2C1875; cursor: pointer;" onclick="openEditJobPopup(${job.id})"></i></td>
    `;

    populateTable(data, containerSelector + ' table', rowTemplate); // Use the table within the container
}

function openEditJobPopup(jobId) {
    currentJobId = jobId; // Store the job ID for use in the popup

    // Show the edit job popup and overlay
    document.getElementById('editJob-popup').classList.add('show');
    document.getElementById('overlay').classList.add('show');
    

    fetch(`getJobData.php?id=${jobId}`)
        .then(response => response.json())
        .then(job => {
            console.log(job); // Log the entire response

            // Populate form fields with the job's data
            document.getElementById('edit-jobposting-partner-company').value = job.company_name;
            document.getElementById('edit-jobposting-job-title').value = job.job_title;
            document.getElementById('edit-jobposting-location').value = job.job_location;
            document.getElementById('edit-jobposting-openings').value = job.job_candidates;
            document.getElementById('edit-jobposting-description').value = job.job_description;

            initializeSkillsInput('editJob-popup', 'edit-jobposting-skills-input', 'edit-jobposting-skills-container');

            // Clear any existing skills
            const skillsContainer = document.querySelector('#edit-jobposting-skills-container');
            const skillsInput = document.querySelector('#edit-jobposting-skills-input');

            // Add fetched skills to the container
            job.skills.forEach(skill => addSkill(skillsContainer, skill, skillsInput));
        })
        .catch(error => console.error('Error fetching job data:', error));
}


// Function to save and post the edited job
function editJob() {

    const companySelect = document.getElementById('edit-jobposting-partner-company');
    const companyName = companySelect.options[companySelect.selectedIndex].text; // Get the text
    const jobTitle = document.getElementById('edit-jobposting-job-title').value;
    const location = document.getElementById('edit-jobposting-location').value;
    const openings = document.getElementById('edit-jobposting-openings').value;
    const description = document.getElementById('edit-jobposting-description').value.trim();

    // Collect skills
    const skillsArray = Array.from(skillsSet); // Convert the Set to an array
    console.log('Company Name:', companyName)
    console.log('Job Title:', jobTitle);
    console.log('Location:', location);
    console.log('Openings:', openings);
    console.log('Description:', description);
    console.log('Skills Array:', skillsArray);

    // Input validation
    if (!jobTitle || !location || !openings) {
        alert('Please fill out all required fields.');
        return; // Prevent form submission
    }

    // Validate openings field
    const openingsInt = parseInt(openings, 10);
    if (isNaN(openingsInt) || openingsInt < 1 || openingsInt > 100) {
        alert('The number of openings must be an integer between 1 and 100.');
        return; // Prevent form submission
    }

    // Create form data
    const formData = new FormData();
    formData.append('company_name', companyName);
    formData.append('job_title', jobTitle);
    formData.append('job_location', location);
    formData.append('job_candidates', openings);
    formData.append('job_description', description);
    formData.append('skills', JSON.stringify(skillsArray)); // Send the skills as a JSON string
    formData.append('id', currentJobId); // Include the job ID for update

    // Send data using fetch
    fetch('editJob.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            console.log('Success:', data.message);
            alert('Job post updated successfully!');
            closePopup('editJob-popup');

            fetchJobCounts();
            fetchJobsByStatus('Open');
        } else {
            console.error('Error:', data.error);
            alert('An error occurred while updating the job: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the job.');
    });
}

//Function to show number of open and closed jobs
function fetchJobCounts() {
    fetch('fetch_job_counts.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('open').textContent = data.open_count;
            document.getElementById('close').textContent = data.closed_count;
        })
        .catch(error => console.error('Error fetching job counts:', error));
}

//Fetches jobs depending on status(Open or Closed)
function fetchJobsByStatus(status) {
    fetch(`fetch_jobs.php?status=${status}`)
        .then(response => response.json())
        .then(data => {
            const containerSelector = status === 'Open' ? '#tab1-content' : '#tab2-content';
            populateJobsTable(containerSelector, data); // Populate the correct table based on the status
        })
        .catch(error => console.error('Error fetching jobs:', error));
}

/*function showDialog() {
    document.getElementById('dialogBox').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
}

function closeDialog() {
    document.getElementById('dialogBox').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

document.getElementById('overlay').addEventListener('click', closeDialog);*/

//Inputs for Admin Side

// Function to fetch options and populate spinner for a specific popup
function fetchOptions(popupId) {
    fetch('fetch_jobPostPartners.php')
        .then(response => response.json())
        .then(data => {
            populateSpinner(popupId, data);
        })
        .catch(error => console.error('Error fetching options:', error));
}

// Function to populate spinner with data for a specific popup
function populateSpinner(popupId, options) {
    const spinner = document.getElementById(popupId).querySelector('.jobposting-select');
    
    if (!spinner) return;

    spinner.innerHTML = '';

    options.forEach(option => {
        const opt = document.createElement('option');
        opt.value = option.id; // ID field
        opt.textContent = option.company_name; // value to display
        spinner.appendChild(opt);
    });
}


//Functions to add partners
function submitForm() {
    const companyName = document.getElementById('addpartners-company-name').value.trim();
    const industry = document.getElementById('addpartners-industry').value.trim();
    const location = document.getElementById('addpartners-location').value.trim();
    const description = document.getElementById('addpartners-company-description').value.trim();
    const logoFile = document.getElementById('logo-upload').files[0];

    console.log('Company Name:', companyName);
    console.log('Industry:', industry);
    console.log('Location:', location);
    console.log('Description:', description);

     // Validate form fields
     if (!companyName || !industry || !location || !description) {
        alert('Please fill out all required fields.');
        return; // Prevent form submission
    }

    // Create FormData object to handle file and text data
    const formData = new FormData();
    formData.append('company_name', companyName);
    formData.append('industry', industry);
    formData.append('location', location);
    formData.append('description', description);

    if (logoFile) {
        formData.append('logo', logoFile);
    }

    // Send data using Fetch
    fetch('addPartner.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            console.log('Success:', data.message);
            alert('Partner added successfully!');
            // Hide the form after successful submission
            hideDialog();

            // Clear the form fields
            document.getElementById('addpartners-company-name').value = '';
            document.getElementById('addpartners-industry').value = '';
            document.getElementById('addpartners-location').value = '';
            document.getElementById('addpartners-company-description').value = '';
            document.getElementById('logo-upload').value = ''; // Clear file input
            document.getElementById('logo-preview').src = ''; // Clear image preview
            document.getElementById('logo-preview').style.display = 'none';

            //Fetch and display new data
            fetchData('fetch_partners.php', populatePartnersTable)
            
        } else {
            console.error('Error:', data.error);
            alert('An error occurred while adding the partner.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the partner.');
    });
}

//Adding new job post
// Function to collect form data and send to the server
function saveAndPostJob() {
    // Collect data from the popup
    const companySelect = document.getElementById('jobposting-partner-company');
    const companyName = companySelect.options[companySelect.selectedIndex].text; // Get the text

    const jobTitle = document.getElementById('jobposting-job-title').value;
    const location = document.getElementById('jobposting-location').value;
    const candidates = document.getElementById('jobposting-openings').value;
    const description = document.getElementById('jobposting-description').value.trim();


    // Collect skills
    const skillsArray = Array.from(skillsSet); // Convert the Set to an array

    console.log('Company Name:', companyName);
    console.log('Job Title:', jobTitle);
    console.log('Location:', location);
    console.log('Candidates:', candidates);
    console.log('Description:', description);
    console.log('Skills Array:', skillsArray);

    // Input validation
    if (!companyName || !jobTitle || !location || !candidates || skillsArray.length === 0) {
        alert('Please fill out all required fields.');
        return; // Prevent form submission
    }

    // Validate candidates field
    const candidatesInt = parseInt(candidates, 10);
    if (isNaN(candidatesInt) || candidatesInt < 1 || candidatesInt > 100) {
        alert('The number of candidates must be an integer between 1 and 100.');
        return; // Prevent form submission
    }

    // Create form data
    const formData = new FormData();
    formData.append('company_name', companyName);
    formData.append('job_title', jobTitle);
    formData.append('location', location);
    formData.append('candidates', candidates);
    formData.append('description', description);
    formData.append('skills', JSON.stringify(skillsArray)); // Send the skills as a JSON string

    

    // Send data using fetch
    fetch('addJobPost.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.message){
            console.log('Success:', data.message);
            // Handle success, e.g., close the popup or display a message
            alert('Job post added successfully!');
            closePopup('popup');

            fetchJobCounts();
            fetchJobsByStatus('Open');

            window.location.reload();
        }
        else {
            console.error('Error:', data.error);
            alert('An error occurred while adding the job: ' + data.error);
        }     
    })
    .catch(error => {
        console.error('Error:', error);
        // Handle error, e.g., display an error message
        alert('An error occurred while adding the job.');
    });
}

// Function to collect form data and send to the server from partner section
function partnerSaveAndPostJob() {
    // Collect data from the popup
    const companySelect = document.getElementById('jobposting-partner-company');
    const companyName = companySelect.options[companySelect.selectedIndex].text; // Get the text

    const jobTitle = document.getElementById('partner-jobposting-job-title').value;
    const location = document.getElementById('partner-jobposting-location').value;
    const candidates = document.getElementById('partner-jobposting-openings').value;
    const description = document.getElementById('partner-jobposting-description').value.trim();


    // Collect skills
    const skillsArray = Array.from(skillsSet); // Convert the Set to an array

    console.log('Company Name:', companyName);
    console.log('Job Title:', jobTitle);
    console.log('Location:', location);
    console.log('Candidates:', candidates);
    console.log('Description:', description);
    console.log('Skills Array:', skillsArray);

    // Input validation
    if (!companyName || !jobTitle || !location || !candidates || skillsArray.length === 0) {
        alert('Please fill out all required fields.');
        return; // Prevent form submission
    }

    // Validate candidates field
    const candidatesInt = parseInt(candidates, 10);
    if (isNaN(candidatesInt) || candidatesInt < 1 || candidatesInt > 100) {
        alert('The number of candidates must be an integer between 1 and 100.');
        return; // Prevent form submission
    }

    // Create form data
    const formData = new FormData();
    formData.append('company_name', companyName);
    formData.append('job_title', jobTitle);
    formData.append('location', location);
    formData.append('candidates', candidates);
    formData.append('description', description);
    formData.append('skills', JSON.stringify(skillsArray)); // Send the skills as a JSON string

    

    // Send data using fetch
    fetch('addJobPost.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.message){
            console.log('Success:', data.message);
            // Handle success, e.g., close the popup or display a message
            alert('Job post added successfully!');
            closePopup('popup');


            window.location.reload();
        }
        else {
            console.error('Error:', data.error);
            alert('An error occurred while adding the job: ' + data.error);
        }     
    })
    .catch(error => {
        console.error('Error:', error);
        // Handle error, e.g., display an error message
        alert('An error occurred while adding the job.');
    });
}
function showEditDialog(employeeId){
    currentEmployeeId = employeeId;
    document.getElementById('edit-dialogBox').style.display = 'block';
    document.getElementById('edit-dialogBox').classList.add('show');
    document.getElementById('overlay').classList.add('show');

    console.log(employeeId);

    fetch(`getEmployeeData.php?id=${employeeId}`)
        .then(response => response.json())
        .then(employee => {

            // Populate form fields with the partner's data
            document.getElementById('edit-employees-firstname').value = employee.first_name;
            document.getElementById('edit-employees-lastname').value = employee.last_name;
            document.getElementById('edit-employees-userid').value = employee.employee_id;
        })
        .catch(error => console.error('Error fetching partner data:', error));
}

function hideEditDialog(){
    document.getElementById('edit-dialogBox').style.display = 'none';
    document.getElementById('edit-dialogBox').classList.remove('show');
    document.getElementById('overlay').classList.remove('show');
}

function addNewEmployee(){
    const firstName = document.getElementById('addemployees-firstname').value.trim();
    const lastName = document.getElementById('addemployees-lastname').value.trim();
    const password = document.getElementById('addemployees-password').value.trim();

    console.log(firstName);
    console.log(lastName);
    console.log(password);
    

    // Input validation
    if (!firstName || !lastName) {
        alert('Please fill out all required fields.');
        return; // Prevent form submission
    }

    // Create form data
    const formData = new FormData();
    formData.append('first_name', firstName);
    formData.append('last_name', lastName);
    formData.append('password', password);

    // Send data using Fetch
    fetch('addEmployee.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            console.log('Success:', data.message);
            alert('Employee added successfully!');
            // Hide the form after successful submission
            hideEmployeeDialog();

            // Clear the form fields
            document.getElementById('addemployees-firstname').value = '';
            document.getElementById('addemployees-lastname').value = '';
            document.getElementById('addemployees-password').value = '';

            //Fetch and display new data
            fetchData('fetch_employees.php', populateEmployeesTable)
            
        } else {
            console.error('Error:', data.error);
            alert('An error occurred while adding the Employee.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the Employee.');
    });
}

function showEditPartnerDialog(partnerId) {
    currentPartnerId = partnerId;
    document.getElementById('editPartner-dialogBox').style.display = 'block';
    document.getElementById('editPartner-dialogBox').classList.add('show');
    document.getElementById('overlay').classList.add('show');
    
    console.log(partnerId);
    
    fetch(`getPartnerData.php?id=${partnerId}`)
        .then(response => response.json())
        .then(partner => {
            // Populate form fields with the partner's data
            document.getElementById('editpartners-company-name').value = partner.company_name;
            document.getElementById('editpartners-industry').value = partner.industry;
            document.getElementById('editpartners-location').value = partner.company_location;
            document.getElementById('editpartners-company-description').value = partner.company_description;
            document.getElementById('edit-upload-placeholder').style.display = 'none';

            var logoImg = document.getElementById('edit-logo-preview');
            logoImg.style.display = 'block' 
            logoImg.src = `data:image/png;base64,${partner.logo}`;
            // Handle the logo separately if needed
        })
        .catch(error => console.error('Error fetching partner data:', error));
}

function hideEditPartnerDialog() {
    document.getElementById('editPartner-dialogBox').style.display = 'none';
    document.getElementById('editPartner-dialogBox').classList.remove('show');
    document.getElementById('overlay').classList.remove('show');

    // Reset the logo preview
    const logoPreview = document.getElementById('logo-preview');
    const uploadPlaceholder = document.getElementById('upload-placeholder');
    const logoUpload = document.getElementById('logo-upload');
    
    logoPreview.style.display = 'none';
    logoPreview.src = '';
    uploadPlaceholder.style.display = 'flex';
    logoUpload.value = ''; // Clear the file input
}



function editPartner() {
    const companyName = document.getElementById('editpartners-company-name').value;
    const companyLocation = document.getElementById('editpartners-location').value;
    const industry = document.getElementById('editpartners-industry').value;
    const companyDescription = document.getElementById('editpartners-company-description').value;
    const logoFile = document.getElementById('edit-logo-upload').files[0];


    console.log('Company Name:', companyName);
    console.log('Industry:', industry);
    console.log('Location:', companyLocation);
    console.log('Description:', companyDescription);

    // Create FormData object to handle file and text data
    const formData = new FormData();
    formData.append('company_name', companyName);
    formData.append('industry', industry);
    formData.append('company_location', companyLocation);
    formData.append('company_description', companyDescription);
    formData.append('id', currentPartnerId);

    if (logoFile) {
        formData.append('logo', logoFile);
    }

    // Send data using Fetch
    fetch('editPartner.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            console.log('Success:', data.message);
            alert('Partner updated successfully!');
            // Hide the form after successful submission
            hideEditPartnerDialog();

            // Clear the form fields
            document.getElementById('addpartners-company-name').value = '';
            document.getElementById('addpartners-industry').value = '';
            document.getElementById('addpartners-location').value = '';
            document.getElementById('addpartners-company-description').value = '';
            document.getElementById('logo-upload').value = ''; // Clear file input
            document.getElementById('logo-preview').src = ''; // Clear image preview
            document.getElementById('logo-preview').style.display = 'none';

            // Fetch and display new data
            fetchData('fetch_partners.php', populatePartnersTable);
            
        } else {
            console.error('Error:', data.error);
            alert('An error occurred while updating the partner.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the partner.');
    });
}

function confirmDelete() {
    if (deletePartnerId !== null) {
        const formData = new FormData();
        formData.append('id', deletePartnerId);

        fetch('deletePartner.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                alert('Partner deleted successfully!');
                hideDialogDelete(); // Hide the confirmation dialog
                fetchData('fetch_partners.php', populatePartnersTable); // Refresh the table
            } else {
                alert('An error occurred while deleting the partner.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the partner.');
        });
    }
}


function initializePopupPagination(popupId) {
    const popup = document.getElementById(popupId);
    if (!popup) return;

    const prevBtns = popup.querySelectorAll(".btn-prev");
    const nextBtns = popup.querySelectorAll(".btn-next");
    const steps = popup.querySelectorAll(".form-step");
    let currentStep = 0;

    function showStep(index) {
        steps.forEach((step, i) => {
            step.classList.toggle("form-step-active", i === index);
        });
    }

    prevBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            currentStep = Math.max(0, currentStep - 1);
            showStep(currentStep);
        });
    });

    nextBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            currentStep = Math.min(steps.length - 1, currentStep + 1);
            showStep(currentStep);
        });
    });

    // Show the first step initially
    showStep(currentStep);
}

// Initialize pagination for all popups
initializePopupPagination('popup');
initializePopupPagination('editJob-popup');
initializePopupPagination('thirdPopup')


// Function to initialize skill input handling for a specific popup
function initializeSkillsInput(popupId, inputId, containerSelector) {
    var popup = document.getElementById(popupId);
    var skillsInput = popup.querySelector(`#${inputId}`);
    var skillsContainer = popup.querySelector(`#${containerSelector}`);

    console.log(popup);
    console.log(skillsInput);
    console.log(skillsContainer);

    // Check if the container and input exist
    if (!skillsContainer || !skillsInput) {
        console.error('Invalid container or input element');
        return;
    }

    // Event listener for adding a new skill
    skillsInput.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            const value = skillsInput.value.trim();
            if (value && !skillsSet.has(value.toLowerCase())) {
                addSkill(skillsContainer, value, skillsInput); // Pass skillsInput as an argument
                skillsInput.value = '';
            }
        }
    });

    // Add existing skills to the Set
    popup.querySelectorAll('.jobposting-skill').forEach(skillElement => {
        var text = skillElement.textContent.trim().toLowerCase();
        skillsSet.add(text);

        // Add close button functionality to existing skills
        var closeBtn = skillElement.querySelector('.close');
        closeBtn.addEventListener('click', function() {
            skillsContainer.removeChild(skillElement);
            skillsSet.delete(text);
        });
    });
}

// Function to add a new skill
function addSkill(container, text, skillsInput) {
    // If the container or text is invalid, exit
    if (!container || !text) return;

    // Check if the skill already exists
    if (skillsSet.has(text.toLowerCase())) return;

    // Add the skill to the Set
    skillsSet.add(text.toLowerCase());

    const skill = document.createElement('div');
    skill.classList.add('jobposting-skill');
    skill.textContent = text;

    const closeBtn = document.createElement('span');
    closeBtn.classList.add('close');
    closeBtn.innerHTML = '&times;';
    closeBtn.addEventListener('click', function () {
        container.removeChild(skill);
        skillsSet.delete(text.toLowerCase());
    });

    skill.appendChild(closeBtn);

    // Insert the skill before the input element
    container.insertBefore(skill, skillsInput);
}



document.addEventListener('DOMContentLoaded', () => {
    const dropdown = document.getElementById('job-status-dropdown');
    let previousValue = dropdown.value;  // Store the initial value

    dropdown.addEventListener('change', function() {
        if (dropdown.value !== previousValue) {
            // Show a confirmation dialog
            const userConfirmed = confirm('Do you want to save the changes?');

            if (userConfirmed) {
                // Update the database with the new status
                updateDatabase(dropdown.value)
                    .then(() => {
                        previousValue = dropdown.value;  // Update previous value on successful save
                    })
                    .catch(error => {
                        console.error('Error updating database:', error);
                        // Optionally, show an error message to the user
                    });
            } else {
                // Revert to previous value
                dropdown.value = previousValue;
            }
        }
    });

    // Function to update the database
    function updateDatabase(newStatus) {
        return fetch('update_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ status: newStatus })
        }).then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        });
    }
});
