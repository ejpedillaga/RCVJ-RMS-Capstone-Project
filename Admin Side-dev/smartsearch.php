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

$user_name = 'Sign Up'; // Default username if not logged in
$user_info = [];
$education_list = [];
$vocational_list = [];
$job_experience_list = [];
$profile_image = null; // Initialize profile image
$skills = [];
$company_name = '';
$job_title = '';

// Check if user is logged in
if (isset($_SESSION['user'])) {
    $user_email = $_SESSION['user']; // Fetch user's email from session
    
    // Fetch user information from applicant_table
    $sql = "SELECT userid, email, fname, lname, gender, birthday, location, phone, personal_description, profile_image FROM applicant_table WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user_info = $result->fetch_assoc();
        $user_name = $user_info['fname'] . ' ' . $user_info['lname']; // Get full name
        $userid = $user_info['userid']; // Get user ID
        $profile_image = !empty($user_info['profile_image']) ? base64_encode($user_info['profile_image']) : null;

        // Fetch education details
        $sql = "SELECT educational_attainment, school, course, sy_started, sy_ended FROM education_table WHERE userid = ?";
        $stmt_edu = $conn->prepare($sql);
        $stmt_edu->bind_param("i", $userid);
        $stmt_edu->execute();
        $result_edu = $stmt_edu->get_result();

        if ($result_edu->num_rows > 0) {
            while ($row = $result_edu->fetch_assoc()) {
                $education_list[] = $row;
            }
        }
        $stmt_edu->close();

        // Fetch vocational education details
        $sql = "SELECT school, course, year_started, year_ended FROM vocational_table WHERE userid = ?";
        $stmt_voc = $conn->prepare($sql);
        $stmt_voc->bind_param("i", $userid);
        $stmt_voc->execute();
        $result_voc = $stmt_voc->get_result();

        if ($result_voc->num_rows > 0) {
            while ($row = $result_voc->fetch_assoc()) {
                $vocational_list[] = $row;
            }
        }
        $stmt_voc->close();

        // Fetch job experience details
        $sql = "SELECT job_title, company_name, month_started, year_started, month_ended, year_ended FROM job_experience_table WHERE userid = ?";
        $stmt_job = $conn->prepare($sql);
        $stmt_job->bind_param("i", $userid);
        $stmt_job->execute();
        $result_job = $stmt_job->get_result();

        if ($result_job->num_rows > 0) {
            while ($row = $result_job->fetch_assoc()) {
                $job_experience_list[] = $row;
            }
        }
        $stmt_job->close();
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
            <a href="smartsearch.php" class="active"><i class="fa-solid fa-magnifying-glass"></i> <span>Smart Search</span></a>
            <a href="candidates.php"><i class="fa-solid fa-user"></i></i> <span>Candidates</span></a>
            <a href="schedules.php"><i class="fa-solid fa-calendar"></i></i> <span>Schedules</span></a>
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
            <h2 style="font-size: 36px;">Smart Search</h2>
            <div class="filter-container">
                <div class="search-wrapper">
                    <div class="input-container">
                        <input type="text" class="job-bar" id="job-bar" placeholder="Search Jobs">
                        <label for="job-bar" class="input-label">Job:</label>
                    </div>
                </div>
            
                <div class="search-wrapper">
                    <div class="input-container">
                        <input type="text" class="location-bar" id="location-bar" placeholder="Search Location">
                        <label for="location-bar" class="input-label">Location:</label>
                    </div>
                </div>
            
                <div class="search-wrapper">
                    <div class="input-container">
                        <input type="text" class="skills-bar" id="skills-bar" placeholder="Search Skills">
                        <label for="skills-bar" class="input-label">Skills:</label>
                    </div>
                </div>
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
                    <thead>

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
                    
                    <tr class="tr1">
                        <td class="fullname" id="fullname">Juan Miguel Escalante</td>
                        <td id="job-title"><strong>SUPERMARKET BAGGER</strong></td>
                        <td id="company-name">WalterMart Supermarket - Dasmarinas</td>
                        <td id="#date">5/13/2024</td>
                        <td class="status" id="status-identical">
                          <span class="status-label-identical">Identical</span>
                        </td>
                        <td class="candidates-tooltip-container">
                            <i class="fa fa-info-circle fa-2xl" aria-hidden="true" style="color: #2C1875; cursor: pointer;" onclick="showInfo()"></i>
                            <span class="tooltip-text">Candidate Information</span>
                        </td>
                        <td><i class="fa-solid fa-trash fa-2xl" style="color: #EF9B50; cursor: pointer;"></i></td>
                    </tr>
                    
                    <tr class="tr1">
                        <td class="fullname" id="fullname">Juan Miguel Escalante</td>
                        <td id="job-title"><strong>SUPERMARKET BAGGER</strong></td>
                        <td id="company-name">WalterMart Supermarket - Dasmarinas</td>
                        <td id="#date">5/13/2024</td>
                        <td class="status" id="status-qualified">
                          <span class="status-label-qualified">Qualified</span>
                        </td>
                        </td>
                        <td class="candidates-tooltip-container">
                            <i class="fa fa-info-circle fa-2xl" aria-hidden="true" style="color: #2C1875; cursor: pointer;" onclick="showInfo()"></i>
                            <span class="tooltip-text">Candidate Information</span>
                        </td>
                        <td><i class="fa-solid fa-trash fa-2xl" style="color: #EF9B50; cursor: pointer;"></i></td>
                    </tr>
                      
                    <tr class="tr1">
                        <td class="fullname" id="fullname">Juan Miguel Escalante</td>
                        <td id="job-title"><strong>SUPERMARKET BAGGER</strong></td>
                        <td id="company-name">WalterMart Supermarket - Dasmarinas</td>
                        <td id="#date">5/13/2024</td>
                        <td class="status" id="status-overqualified">
                          <span class="status-label-overqualified">Overqualified</span>
                        </td>
                        <td class="candidates-tooltip-container">
                            <i class="fa fa-info-circle fa-2xl" aria-hidden="true" style="color: #2C1875; cursor: pointer;" onclick="showInfo()"></i>
                            <span class="tooltip-text">Candidate Information</span>
                        </td>
                        <td><i class="fa-solid fa-trash fa-2xl" style="color: #EF9B50; cursor: pointer;"></i></td>
                    </tr>
                </table>
            </div>

            <!-- Overlay --> 
            <div class="overlay" id="overlay"></div>

            <!--Candidate Info Popup-->
            <div class="popup" id="info">
                <!-- Back Button -->
                <div class="addpartners-back-button" onclick="hideInfo()">
                    <i class="fas fa-chevron-left"></i> Back
                </div>
                <h3 style="color: #2C1875">Review your information:</h3>
                <p>This information will be reviewed by the employer.</p>
                <div class="candidate-container">
                    <div class="candidate-header">
                        <div>
                            <h2><?php echo htmlspecialchars($user_name); ?></h2>
                            <div class="locationemail">
                                <i class="fa fa-map-pin" aria-hidden="true"></i><h4><?php echo htmlspecialchars($user_info['location']); ?></h4>
                            </div>
                            <div class="locationemail">
                                <i class="fa fa-envelope" aria-hidden="true"></i><h4><?php echo htmlspecialchars($user_info['email']); ?></h4>
                            </div>
                            <div class="locationemail">
                                <i class="fa fa-venus-mars" aria-hidden="true"></i><h4><?php echo htmlspecialchars($user_info['gender']); ?></h4>
                            </div>
                            <div class="locationemail">
                                <i class="fa fa-phone" aria-hidden="true"></i><h4><?php echo htmlspecialchars($user_info['phone']); ?></h4>
                            </div>
                            <div class="locationemail">
                                <i class="fa fa-birthday-cake" aria-hidden="true"></i><h4><?php echo htmlspecialchars($user_info['birthday']); ?></h4>
                            </div>
                        </div>
                        <div>
                            <?php if ($profile_image): ?>
                            <img src="data:image/jpeg;base64,<?php echo $profile_image; ?>" alt="Profile Picture" class="large-profile-photo">
                            <?php else: ?>
                                <img src="images/user.svg" alt="Default Profile Picture" class="large-profile-photo">
                            <?php endif; ?>
                        </div>
                    </div>
                    <div id="personal-info">
                        <h3>Personal Information</h3>
                        <p id="personal-desc"><?php echo nl2br(htmlspecialchars($user_info['personal_description'])); ?></p>
                    </div>
                    <!-- Past Jobs Information -->
                    <div id="past-jobs">
                        <h3>Past Jobs</h3>
                        <ul class="pastjobs-list">
                            <?php if (!empty($job_experience_list)): ?>
                                <?php foreach ($job_experience_list as $job_exp): ?>
                                    <li>
                                        <?php echo htmlspecialchars($job_exp['job_title']) . " at " . htmlspecialchars($job_exp['company_name']) . " (" . htmlspecialchars($job_exp['month_started']) . " " . htmlspecialchars($job_exp['year_started']) . " - " . (!empty($job_exp['month_ended']) ? htmlspecialchars($job_exp['month_ended']) . " " . htmlspecialchars($job_exp['year_ended']) : 'Present') . ")"; ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li>No past job experience records found.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <!-- Education Information -->
                    <div id="education">
                        <h3>Educational Attainment</h3>
                        <ul class="education-list">
                            <?php if (!empty($education_list)): ?>
                                <?php foreach ($education_list as $education): ?>
                                    <li>
                                        <?php echo htmlspecialchars($education['educational_attainment']) . " in " . htmlspecialchars($education['course']) . " from " . htmlspecialchars($education['school']) . " (" . htmlspecialchars($education['sy_started']) . " - " . htmlspecialchars($education['sy_ended']) . ")"; ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li>No education records found.</li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <!-- Vocational Education Information -->
                    <div id="vocational">
                        <h3>Vocational</h3>
                        <ul class="vocational-list">
                            <?php if (!empty($vocational_list)): ?>
                                <?php foreach ($vocational_list as $vocational): ?>
                                    <li>
                                        <?php echo htmlspecialchars($vocational['course']) . " from " . htmlspecialchars($vocational['school']) . " (" . htmlspecialchars($vocational['year_started']) . " - " . htmlspecialchars($vocational['year_ended']) . ")"; ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li>No vocational education records found.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div id="skills">
                        <h3>Skills</h3>
                        <ul class="skills-list">
                            <li>Skills</li>
                            <li>Education</li>
                        </ul>
                    </div>
                </div>

                <div class="buttons-container">
                    <button class="button-apply">Approve Application</button>
                </div>
            </div>
        </div>
    </div>

</body>

