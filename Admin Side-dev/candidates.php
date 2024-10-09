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

// Pagination Logic
$limit = 5; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch total number of candidates
$totalQuery = "SELECT COUNT(*) as total FROM candidate_list";
$totalResult = $conn->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalCandidates = $totalRow['total'];
$totalPages = ceil($totalCandidates / $limit);

// Fetch candidates from candidate_list table with pagination
$query = "SELECT full_name, job_title, company_name, date_applied, status FROM candidate_list LIMIT ?, ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();
?>




<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Side RCVJ</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>"></link>
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

            <div class="tabs">
                <div class="tab active" onclick="openTab('pending')">Pending</div>
                <div class="tab" onclick="openTab('scheduled')">Scheduled</div>
                <div class="tab" onclick="openTab('interviewed')">Interviewed</div>
                <div class="tab" onclick="openTab('deployed')">Deployed</div>
            </div>
            <div id="pending" class="tab-content active">
                    <table style="margin-bottom: 1rem">
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
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="tr1">
                                <td id="fullname" class="fullname"><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td id="job-title"><strong><?php echo htmlspecialchars($row['job_title']); ?></strong></td>
                                <td id="company-name"><?php echo htmlspecialchars($row['company_name']); ?></td>
                                <td id="date"><?php echo htmlspecialchars($row['date_applied']); ?></td>
                                <td>
                                    <select class="status-dropdown">
                                        <option value="Interview" <?php echo ($row['status'] === 'Interview') ? 'selected' : ''; ?>>Interview</option>
                                        <option value="Pending" <?php echo ($row['status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Rejected" <?php echo ($row['status'] === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                                        <option value="Deployed" <?php echo ($row['status'] === 'Deployed') ? 'selected' : ''; ?>>Deployed</option>
                                    </select>
                                </td>
                                <td class="candidates-tooltip-container">
                                    <i class="fa fa-info-circle fa-2xl" aria-hidden="true" style="color: #2C1875; cursor: pointer;" onclick="showInfo()"></i>
                                    <span class="tooltip-text">Candidate Information</span>
                                </td>
                                <td class="candidates-tooltip-container">
                                    <i class="fa-solid fa-trash fa-2xl" style="color: #EF9B50; cursor: pointer;" onclick="showDialog()"></i>
                                    <span class="tooltip-text">Delete Candidate</span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                    <!-- Pagination Controls -->
                    <div id="pagination">
                        <?php if ($totalPages > 1): ?>
                            <nav>
                                <ul class="pagination">
                                    <?php if ($page > 1): ?>
                                        <li><a href="?page=<?php echo $page - 1; ?>">&laquo;</a></li>
                                    <?php endif; ?>
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <?php if ($page < $totalPages): ?>
                                        <li><a href="?page=<?php echo $page + 1; ?>">&raquo;</a></li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div id="scheduled" class="tab-content">
                <table style="margin-bottom: 1rem">
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
                        
                        </tbody>
                    </table>
                    <!-- Pagination Controls -->
                    <div id="pagination">
                        <?php if ($totalPages > 1): ?>
                            <nav>
                                <ul class="pagination">
                                    <?php if ($page > 1): ?>
                                        <li><a href="?page=<?php echo $page - 1; ?>">&laquo;</a></li>
                                    <?php endif; ?>
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <?php if ($page < $totalPages): ?>
                                        <li><a href="?page=<?php echo $page + 1; ?>">&raquo;</a></li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="interviewed" class="tab-content">
                <table style="margin-bottom: 1rem">
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
                        
                        </tbody>
                    </table>
                    <!-- Pagination Controls -->
                    <div id="pagination">
                        <?php if ($totalPages > 1): ?>
                            <nav>
                                <ul class="pagination">
                                    <?php if ($page > 1): ?>
                                        <li><a href="?page=<?php echo $page - 1; ?>">&laquo;</a></li>
                                    <?php endif; ?>
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <?php if ($page < $totalPages): ?>
                                        <li><a href="?page=<?php echo $page + 1; ?>">&raquo;</a></li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="deployed" class="tab-content">
                <table style="margin-bottom: 1rem">
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
                        
                        </tbody>
                    </table>
                    <!-- Pagination Controls -->
                    <div id="pagination">
                        <?php if ($totalPages > 1): ?>
                            <nav>
                                <ul class="pagination">
                                    <?php if ($page > 1): ?>
                                        <li><a href="?page=<?php echo $page - 1; ?>">&laquo;</a></li>
                                    <?php endif; ?>
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <?php if ($page < $totalPages): ?>
                                        <li><a href="?page=<?php echo $page + 1; ?>">&raquo;</a></li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
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

