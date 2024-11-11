<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees | RCVJ, Inc.</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>"></link>
    <link rel="stylesheet" href="mediaqueries.css">
    <script src="script.js?v=<?php echo filemtime('script.js'); ?>"></script>
      
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
            <a href="schedules.php"><i class="fa-solid fa-calendar"></i></i> <span>Schedules</span></a>
            <a href="partners.php"><i class="fa-solid fa-handshake"></i> <span>Partners</span></a>
            <a href="employees.php" class="active"><i class="fa-solid fa-user-tie"></i> <span>Employees</span></a>
        </div>

        <div id="header">
            <img id="logo" src="img/logo.png" alt="logo">
            <div class="profile">
                <img src="img/pfp.png" alt="Profile Picture">
                <span class="name">Admin</span>
                <!-- LOGOUT -->
                <button class="logout-btn" onclick="confirmLogout()">
                    <i class="fas fa-sign-out-alt fa-lg"></i>
                </button>
            </div>
        </div>

        <div id="main">
            <h2 style="font-size: 36px;">Employees</h2>
            <div class="btnGrp">
                <div class="toggle-button">
                    <div class="toggle-option open" id="tab1" onclick="toggle(this)">Active (<span id="open">2</span>)</div>
                    <div class="toggle-option closed" id="tab2" onclick="toggle(this)">Inactive (<span id="close">1</span>)</div>
                </div>
            </div>


            <div class="tab-content" id="tab1-content">
                <div class="filter-container">
                    <div class="search-wrapper">
                        <i class="fas fa-magnifying-glass search-icon"></i>
                        <input type="text" id="search-bar-active" class="search-employees" placeholder="Search Active Employees">
                    </div>
                    <select class="sort-by">
                        <option value="date_added">Sort by: Date Created</option>
                        <option value="last_name">Sort by: Last Name</option>
                        <option value="first_name">Sort by: First Name</option>
                    </select>
                    <select class="order-sort">
                        <option value="ASC">Ascending</option>
                        <option value="DESC">Descending</option>
                    </select>

                    <button class="add-employees-button" onclick="showEmployeeDialog()">Create Account</button>
                </div>

                <table>
                    <thead>
                        <tr class="th1">
                            <th>Employee</th>
                            <th>Date Added</th>
                            <th>Status</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="active-employees-body">
                        <!-- Active employees populated here -->
                    </tbody>
                </table>
                <div id="no-active-employees-message" style="text-align: center; color: #2C1875; font-weight: bold;">
                    No active employees found
                </div>
            </div>

            <div class="tab-content" id="tab2-content" style="display: none;">
                <div class="filter-container">
                    <div class="search-wrapper">
                        <i class="fas fa-magnifying-glass search-icon"></i>
                        <input type="text" id="search-bar-inactive" class="search-employees" placeholder="Search Inactive Employees">
                    </div>
                    <select id="sort-by-inactive" class="sort-by">
                        <option value="date_added">Sort by: Date Created</option>
                        <option value="last_name">Sort by: Last Name</option>
                        <option value="first_name">Sort by: First Name</option>
                    </select>
                    <select id="order-sort-inactive" class="order-sort">
                        <option value="asc">Ascending</option>
                        <option value="desc">Descending</option>
                    </select>
                    <button class="add-employees-button" onclick="showEmployeeDialog()">Create Account</button>
                </div>

                <table>
                    <thead>
                        <tr class="th1">
                            <th>Employee</th>
                            <th>Date Added</th>
                            <th>Status</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="inactive-employees-body">
                        <!-- Inside your inactive employee table -->
                    </tbody>
                </table>
                <div id="no-inactive-employees-message" style="text-align: center; color: #2C1875; font-weight: bold;">
                    No inactive employees found
                </div>
                
            </div>

        

        <!-- Overlay -->
        <div class="overlay" id="overlay"></div>

        <!-- Dialog Box Employees-->
        <div class="addemployees-dialog-box" id="dialogBox">
            <div class="addpartners-back-button" onclick="hideEmployeeDialog()">
                <i class="fas fa-chevron-left"></i> Back
            </div>

            <div class="addemployees-form-group">
                <label for="addemployees-role">Role</label>
                <select id="addemployees-role">
                    <option value="hr-staff">HR Staff</option>
                    <option value="hr-manager">HR Manager</option>
                    <option value="it-staff">IT Staff</option>
                </select>
            </div>
    
            <div class="addemployees-form-group">
                <label for="addemployees-firstname">Firstname</label>
                <input type="text" id="addemployees-firstname">
            </div>

            <div class="addemployees-form-group">
                <label for="addemployees-lastname">Lastname</label>
                <input type="text" id="addemployees-lastname">
            </div>

            <div class="addemployees-form-group">
                <label for="addemployees-username">Username</label>
                <input type="text" id="addemployees-username">
            </div>
            
            <div class="addemployees-form-group">
                <label for="addemployees-password">Password</label>
                <input type="text" id="addemployees-password">
            </div>

            <div class="addemployees-form-group">
                <label for="addemployees-admin-password">Admin Password</label>
                <input type="password" id="admin-password" name="admin_password" placeholder="Enter Admin Password" required>

            </div>

            <div style="text-align: center;">
                <button class="addemployees-save-button" onclick="addNewEmployee()">Save and Add</button>
            </div>
        </div>

        <!-- Dialog Box Edit Employees-->
        <div class="addemployees-dialog-box" id="edit-dialogBox">
            <div class="addpartners-back-button" onclick="hideEditDialog()">
                <i class="fas fa-chevron-left"></i> Back
            </div>

            <div class="addemployees-form-group">
                <label for="addemployees-role">Role</label>
                <select id="addemployees-role">
                    <option value="hr-staff">HR Staff</option>
                    <option value="hr-manager">HR Manager</option>
                    <option value="it-staff">IT Staff</option>
                </select>
            </div>
    
            <div class="addemployees-form-group">
                <label for="addemployees-firstname">Firstname</label>
                <input type="text" id="edit-employees-firstname">
            </div>

            <div class="addemployees-form-group">
                <label for="addemployees-lastname">Lastname</label>
                <input type="text" id="edit-employees-lastname">
            </div>

            <div class="addemployees-form-group">
                <label for="addemployees-admin-password">Admin Password</label>
                <input type="text" id="edit-employees-admin-password">
            </div>
    
            <div style="text-align: center;">
                <button class="addemployees-save-button" onclick="editEmployee()">Save and Add</button>
            </div>
        </div>

        <!-- Dialog Box Delete Employees-->
        <div class="delete-employees-dialog-box" id="dialogBox-delete">
            <div class="addpartners-back-button" onclick="hideDialogDelete()">
                <i class="fas fa-chevron-left"></i> Back
            </div>
    
            <h2 style="text-align: center;">Are you sure you want to delete this employee?</h1>
            <div class="delete-employees-form-group">
                <label for="delete-employees-firstname">Admin Password:</label>
                <input type="text" id="delete-employees-firstname">
            </div>

            <button class="delete-employees-save-button">Delete</button>
        </div>
    </div>
    <div class="shape-container2">
        <div class="rectangle-4"></div>
        <div class="rectangle-5"></div>
    </div>
    <script>
    function toggle(element) {
        // Remove 'open' and 'closed' classes from all toggle options
        const tabs = document.querySelectorAll('.toggle-option');
        tabs.forEach(tab => {
            tab.classList.remove('open'); // Remove open class
            tab.classList.remove('closed'); // Remove closed class
        });

        // Determine which tab was clicked and apply the appropriate classes
        if (element.id === 'tab1') {
            element.classList.add('open');
            document.getElementById('tab2').classList.add('closed');
            document.getElementById('tab1-content').style.display = 'block';
            document.getElementById('tab2-content').style.display = 'none';
        } else {
            element.classList.add('open');
            document.getElementById('tab1').classList.add('closed');
            document.getElementById('tab2-content').style.display = 'block';
            document.getElementById('tab1-content').style.display = 'none';
        }

        // Save the active tab to local storage
        localStorage.setItem('activeEmployeeTab', element.id);
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Fetch the counts from the server
        fetch('getEmployeeCounts.php')
            .then(response => response.json())
            .then(data => {
                // Update the toggle buttons with the counts
                document.getElementById('open').textContent = data.active; // Active count
                document.getElementById('close').textContent = data.inactive; // Inactive count
                
                // Set the default active tab
                const activeTab = localStorage.getItem('activeEmployeeTab') || 'tab1'; // Default to 'tab1' if none is set
                const activeTabElement = document.getElementById(activeTab);
                
                // Call toggle to set the tab as active
                toggle(activeTabElement);

                // Check if there are employees to display
                if (data.active === 0 && data.inactive === 0) {
                    document.getElementById('no-employees-message').style.display = 'block'; // Show message
                } else {
                    document.getElementById('no-employees-message').style.display = 'none'; // Hide message
                }
            })
            .catch(error => {
                console.error('Error fetching employee counts:', error);
            });
    });


    // Function to fetch and display employees
    // Function to fetch employees with optional search
    // Function to fetch employees with optional search
    function fetchEmployees(searchTerm = '') {
        fetch(`fetch_employees.php?search=${encodeURIComponent(searchTerm)}`)
            .then(response => response.json())
            .then(data => {
                populateEmployeesTable(data);
            })
            .catch(error => console.error('Error fetching employees:', error));
    }

    // Filter employees based on search input
    function filterEmployees() {
        const searchTerm = document.querySelector('.search-employees').value.trim();
        fetchEmployees(searchTerm);
    }

    // Add event listener to search input
    document.querySelector('.search-employees').addEventListener('input', filterEmployees);

    // Initial fetch to populate table
    fetchEmployees(); // Call this initially to load all employees

    function sortAndFetchEmployees() {
        const sortBy = document.querySelector('.sort-by').value; // Get sorting field
        const order = document.querySelector('.order-sort').value; // Get sorting order
        const searchTerm = document.querySelector('.search-employees').value.trim();

        // Fetch employees with sorting
        fetch(`fetch_employees.php?search=${encodeURIComponent(searchTerm)}&sort_by=${encodeURIComponent(sortBy)}&order=${encodeURIComponent(order)}`)
            .then(response => response.json())
            .then(data => {
                populateEmployeesTable(data); // Populate the table with sorted data
            })
            .catch(error => console.error('Error fetching employees:', error));
    }

    // Add event listeners for sorting dropdowns
    document.querySelector('.sort-by').addEventListener('change', sortAndFetchEmployees);
    document.querySelector('.order-sort').addEventListener('change', sortAndFetchEmployees);

    </script>
</body>

