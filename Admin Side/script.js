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

    const skillsInput = document.getElementById('jobposting-skills-input');
    const skillsContainer = document.querySelector('.jobposting-skills-container');
    
    // Event listener for adding a new skill
    skillsInput.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            const value = skillsInput.value.trim();
            if (value && !skillsSet.has(value.toLowerCase())) {
                addSkill(value);
                skillsInput.value = '';
            }
        }
    });
    
    // Function to add a new skill
    function addSkill(text) {
        skillsSet.add(text.toLowerCase());
    
        const skill = document.createElement('div');
        skill.classList.add('jobposting-skill');
        skill.textContent = text;
    
        const closeBtn = document.createElement('span');
        closeBtn.classList.add('close');
        closeBtn.innerHTML = '&times;';
        closeBtn.addEventListener('click', function() {
            skillsContainer.removeChild(skill);
            skillsSet.delete(text.toLowerCase());
        });
    
        skill.appendChild(closeBtn);
        skillsContainer.insertBefore(skill, skillsInput); // Insert the skill before the input
    }
    
    // Add existing skills to the Set
    document.querySelectorAll('.jobposting-skill').forEach(skillElement => {
        const text = skillElement.textContent.trim().toLowerCase();
        skillsSet.add(text);
    
        // Add close button functionality to existing skills
        const closeBtn = skillElement.querySelector('.close');
        closeBtn.addEventListener('click', function() {
            skillsContainer.removeChild(skillElement);
            skillsSet.delete(text);
        });
    });
    
    //Qualification adding
document.getElementById('jobposting-qualifications-input').addEventListener('keydown', function(event) {
    if (event.key === 'Enter') {
        event.preventDefault(); // Prevent the form from submitting

        var skillInput = document.getElementById('jobposting-qualifications-input');
        var skillValue = skillInput.value.trim();

        if (skillValue !== '') {
            var ul = document.getElementById('added_qualifications_list');
            var li = document.createElement('li');
            li.innerHTML = `
                <span>${skillValue}</span>
                <button class="close-btn">&times;</button>
            `;
            ul.appendChild(li);
            skillInput.value = ''; // Clear the input after adding

            // Add event listener to the close button
            li.querySelector('.close-btn').addEventListener('click', function() {
                li.remove();
            });
        }
    }
});


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

/*Dialog Box Delete*/
function showDialogDelete() {
    document.getElementById('dialogBox-delete').style.display = 'block';
    document.getElementById('overlay').classList.add('show');
}

function hideDialogDelete() {
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

function openPopup() {
    document.getElementById('popup').classList.add('show');
    document.getElementById('overlay').classList.add('show');
}

function closePopup() {
    document.getElementById('popup').classList.remove('show');
    document.getElementById('overlay').classList.remove('show');

    // Remove all skill tags from the container but keep the input field
    const skillsContainer = document.querySelector('.jobposting-skills-container');
    const skills = skillsContainer.querySelectorAll('.jobposting-skill');
    skills.forEach(skill => skillsContainer.removeChild(skill));
    
    // Clear the skills set
    skillsSet.clear();
    
    // Optionally, you might want to clear the input field as well
    document.getElementById('jobposting-skills-input').value = '';
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
