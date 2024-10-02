<?php
session_start();

// Include the connection.php file
include 'connection.php'; // Adjust this if necessary

// Call the connection function to establish the database connection
$conn = connection();

// Check if the connection is established
if (!$conn) {
    die("Database connection failed.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the current user's email from session
    $email = $_SESSION['user'];

    // Fetch full name from applicant_table
    $query = "SELECT fname, lname FROM applicant_table WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $fullName = $row['fname'] . ' ' . $row['lname'];

        // Set current date and default status
        $dateApplied = date('Y-m-d');
        $status = 'Pending';

        // Insert data into candidate_list table
        $insertQuery = "INSERT INTO candidate_list (full_name, job_title, company_name, date_applied, status) 
                        VALUES (?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);

        // Get job title and company name from POST request (sent from JobDetails.php)
        $jobTitle = isset($_POST['job_title']) ? $_POST['job_title'] : 'Job Title Placeholder';
        $companyName = isset($_POST['company_name']) ? $_POST['company_name'] : 'Company Placeholder';

        $insertStmt->bind_param("sssss", $fullName, $jobTitle, $companyName, $dateApplied, $status);
        $insertStmt->execute();
        $insertStmt->close();
    } else {
        echo "No user found.";
    }
    $stmt->close();
}

// Fetch candidates from candidate_list table
$query = "SELECT full_name, job_title, company_name, date_applied, status FROM candidate_list";
$result = $conn->query($query); // Execute the query
?>




<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Side RCVJ</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="mediaqueries.css">
    <script src="script.js"></script>
      
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
            <a href="smartsearch.php"><i class="fa-solid fa-magnifying-glass"></i> <span>Smart Search</span></a>
            <a href="candidates.php" class="active"><i class="fa-solid fa-user"></i></i> <span>Candidates</span></a>
            <a href="schedules.php" ><i class="fa-solid fa-calendar"></i></i> <span>Schedules</span></a>
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
            <h2 style="font-size: 36px;">Candidates</h2>

            <div class="filter-container-candidates">
                <div class="search-wrapper">
                    <i class="fas fa-magnifying-glass search-icon"></i>
                    <input type="text" class="search-candidates" placeholder="Search Candidates">
                </div>
                <select class="company-sort">
                    <option>Sort by: All</option>
                    <option>Sort by: WalterMart</option>
                    <option>Sort by: Jabile</option>
                </select>
                <select class="sort-by">
                    <option>Sort by: Date Applied</option>
                    <option>Sort by: Company Name</option>
                    <option>Sort by: Job Title</option>
                </select>
                <select class="order-sort">
                    <option>Ascending</option>
                    <option>Descending</option>
                </select>

                <select class="status-sort">
                    <option>Status: Interview</option>
                    <option>Status: Pending</option>
                    <option>Status: Deployed</option>
                </select>

                <button class="rejected-button" onclick="redirectTo('rejected.html')">Rejected</button>
            </div>

            <div>
                    <table>
                        <thead>
                            <tr class="th1">
                                <th>Candidate</th>
                                <th>Job Title</th>
                                <th>Company</th>
                                <th>Date Applied</th>
                                <th>Status</th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result && $result->num_rows > 0) { // Check if $result is set and has rows
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['job_title']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['company_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['date_applied']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No candidates found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
            </div>


        </div>

        <!-- Overlay -->
        <div class="overlay" id="overlay"></div>

        <!--Candidate Info Popup-->
        <div class="popup" style="background-color: white;" id="info">
            <!-- Back Button -->
            <div class="addpartners-back-button" onclick="hideInfo()">
                <i class="fas fa-chevron-left"></i> Back
            </div>

            <div class="candidate-container">
                <div class="candidate-header">
                    <div>
                        <h2>Candidate</h2>
                        <div class="locationemail">
                            <i class="fa fa-map-pin" aria-hidden="true"></i><h3>Location</h3>
                        </div>
                        <div class="locationemail">
                            <i class="fa fa-envelope" aria-hidden="true"></i><h3>Email</h3>
                        </div>
                    </div>
                    <div><!--pic goes here--></div>
                </div>
                <div class="candidate-body">
                    <div id="personal-info">
                        <h3>Personal Information</h3>
                        <p id="personal-desc">lorem ipsum</p>
                        <ul>
                            <li>Gender:</li>
                            <li>Birthday:</li>
                            <li>Contact Number:</li>
                        </ul>
                    </div>
                    <div id="past-jobs">
                        <h3>Past Jobs</h3>
                        <ul>
                            <li>Job</li>
                            <li>job</li>
                        </ul>
                    </div>
                    <div id="education">
                        <h3>Education</h3>
                        <ul>
                            <li>Education</li>
                            <li>Education</li>
                        </ul>
                    </div>
                    <div id="skills">
                        <h3>Skills</h3>
                        <ul>
                            <li>Skills</li>
                            <li>Education</li>
                        </ul>
                    </div>          
                </div>
            </div>
        </div>

        <!-- Dialog Box -->
        <div class="rejected-dialog-box" id="dialogBox">
            <div class="rejected-back-button" onclick="hideDialog()">
                <i class="fas fa-chevron-left"></i> Back
            </div>
            
            <h2 style="text-align: center;">Are you sure you want to reject this candidate?</h2>
            <div class="rejected-form-group">
                <label for="rejected-firstname">Remarks:</label>
                <input type="text" id="rejected-firstname">
                <button class="rejected-save-button">Confirm</button>
            </div>
        </div>

    </div>
</body>

