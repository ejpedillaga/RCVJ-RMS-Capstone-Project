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

$user_name = 'Sign Up'; // Default username if not logged in
$user_info = [];
$education_list = [];
$vocational_list = [];
$job_experience_list = [];
$profile_image = null; // Initialize profile image
$skills = [];
$company_name = '';
$job_title = '';

// Assuming $passed_userid is the userid you want to use
$passed_userid = '';

// Check if the passed_userid is set and is valid
if (isset($passed_userid)) {
    // Fetch user information from applicant_table based on passed userid
    $sql = "SELECT userid, email, fname, lname, gender, birthday, location, phone, personal_description, profile_image FROM applicant_table WHERE userid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $passed_userid);
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
} else {
    echo "User ID is not set.";
}

//JOB MATCHING ALGORITHM HERE

// Fetch partner companies with open job postings
function fetchPartnerCompanies() {
    $conn = connection();
    $query = "
        SELECT DISTINCT p.id, p.company_name 
        FROM partner_table p 
        JOIN job_table j ON p.company_name = j.company_name 
        WHERE j.job_status = 'Open'
    ";
    $result = mysqli_query($conn, $query);
    
    echo "<select id='companyDropdown' class='select-company' onchange='fetchJobs(this.value)'>";
    echo "<option value='' disabled selected>Select Company</option>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<option value='".$row['id']."'>".$row['company_name']."</option>";
    }
    echo "</select>";

    mysqli_close($conn);
}

// Fetch job postings for selected company (use job_id now)
if (isset($_POST['fetch_jobs']) && isset($_POST['company_id'])) {
    $company_id = $_POST['company_id'];
    $conn = connection();

    $query = "SELECT id, job_title, job_location, job_candidates 
              FROM job_table 
              WHERE company_name = (SELECT company_name FROM partner_table WHERE id = $company_id) 
              AND job_status = 'Open'";
    
    $result = mysqli_query($conn, $query);
    echo "<select id='jobDropdown' class='select-job' onchange='fetchJobDetails(this.value, $company_id)'>";
    echo "<option value='' disabled selected>Select Job</option>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<option value='".$row['id']."'>".$row['job_title'].", ".$row['job_location']." (".$row['job_candidates'].")</option>";
    }
    echo "</select>";

    mysqli_close($conn);
    exit();
}

// Fetch candidates that applied to the selected job by job_id
if (isset($_POST['fetch_job_details']) && isset($_POST['job_id']) && isset($_POST['company_id'])) {
    $job_id = $_POST['job_id']; // Use job_id now
    $company_id = $_POST['company_id']; // Get the company_id
    $conn = connection();

    // Fetch job requirements using job_id
    $query = "SELECT jt.classification, jt.subclassification, jt.gender, jt.educational_attainment, jt.years_of_experience, jt.cert_license, j.job_location
    FROM job_title_table jt
    JOIN job_table j ON jt.job_title = j.job_title
    WHERE j.id = $job_id"; // Use job_id to filter job

    $job_result = mysqli_query($conn, $query);
    if (!$job_result) {
        echo json_encode(['error' => mysqli_error($conn)]);
        exit();
    }
    $job_details = mysqli_fetch_assoc($job_result);

   // Fetch candidates with "Pending" status that applied for the job by job_id
    $query = "SELECT c.id, a.userid, a.fname, a.lname, a.gender, e.educational_attainment, 
    a.classi, a.subclassi, a.location, c.date_applied, 
    j.job_title, p.company_name, SUM(je.year_ended - je.year_started) AS total_years_experience
    FROM candidate_list c
    JOIN applicant_table a ON c.userid = a.userid
    LEFT JOIN education_table e ON a.userid = e.userid
    LEFT JOIN job_experience_table je ON a.userid = je.userid
    LEFT JOIN job_table j ON c.job_id = j.id
    LEFT JOIN partner_table p ON j.company_name = p.company_name
    WHERE c.status = 'Pending'
    AND c.job_id = $job_id -- Use job_id instead of job_title
    GROUP BY a.userid";

    $result = mysqli_query($conn, $query);
    $candidates = [];

    while ($row = mysqli_fetch_assoc($result)) {
    // Score applicants based on matching criteria
    $score = 0;
    $max_score = 0; // Initialize max score

    // Match classification
    if ($row['classi'] == $job_details['classification']) {
    $score += 1;
    }
    $max_score += 1; // Increment max score

    // Match subclassification
    if ($row['subclassi'] == $job_details['subclassification']) {
    $score += 1;
    }
    $max_score += 1; // Increment max score

    // Match gender
    if ($row['gender'] == $job_details['gender']) {
    $score += 1;
    }
    $max_score += 1; // Increment max score

    // Match educational attainment
    if ($row['educational_attainment'] == $job_details['educational_attainment']) {
    $score += 1;
    }
    $max_score += 1; // Increment max score

    // Check if the total years of experience fall within the required range
    if ($job_details['years_of_experience'] !== '-') { // Check if years_of_experience is not "-"
    list($min_exp, $max_exp) = explode('-', $job_details['years_of_experience']);
    if ($row['total_years_experience'] >= (int)$min_exp && $row['total_years_experience'] <= (int)$max_exp) {
    $score += 1; // Add point for matching experience range
    }
    $max_score += 1; // Increment max score
    }

    // Match job location
    if ($row['location'] == $job_details['job_location']) { // Assuming job_details has job_location
    $score += 1; // Add point if location matches
    }
    $max_score += 1; // Increment max score for location match

    // Fetch skills of the applicant
    $skills_query = "SELECT skill_id FROM user_skills_table WHERE userid = '".$row['userid']."'";
    $skills_result = mysqli_query($conn, $skills_query);
    $applicant_skills = [];
    while ($skill_row = mysqli_fetch_assoc($skills_result)) {
    $applicant_skills[] = $skill_row['skill_id'];
    }

    // Fetch required skills for the job
    $required_skills_query = "SELECT skill_id 
                          FROM job_skills_table 
                          WHERE job_title_id = (SELECT id FROM job_title_table WHERE job_title = (
                              SELECT job_title FROM job_table WHERE id = $job_id
                          ))";
    $required_skills_result = mysqli_query($conn, $required_skills_query);
    while ($req_skill_row = mysqli_fetch_assoc($required_skills_result)) {
    if (in_array($req_skill_row['skill_id'], $applicant_skills)) {
    $score += 1; // Increase score for each matching skill
    }
    $max_score += 1; // Increment max score
    }

    // Match certifications and licenses
    // Fetch applicant's licenses
    $licenses_query = "SELECT license_name FROM certification_license_table WHERE userid = '".$row['userid']."'";
    $licenses_result = mysqli_query($conn, $licenses_query);
    $applicant_licenses = [];
    while ($license_row = mysqli_fetch_assoc($licenses_result)) {
    $applicant_licenses[] = $license_row['license_name'];
    }

    // Check if any license matches the job's cert_license
    if (in_array($job_details['cert_license'], $applicant_licenses)) {
    $score += 1; // Add point if there's a match
    }
    $max_score += 1; // Increment max score

    // Add candidate to array with all required fields
    $candidates[] = [
        'candidate' => [
            'userid' => $row['userid'],
            'fname' => $row['fname'],
            'lname' => $row['lname'],
            'job_title' => $row['job_title'],
            'company_name' => $row['company_name'], 
            'date_applied' => $row['date_applied'], 
        ],
        'score' => $score,
        'max_score' => $max_score,
    ];
    }

    // Sort candidates by score (higher score first)
    usort($candidates, function($a, $b) {
        return $b['score'] - $a['score'];
    });

    mysqli_close($conn);
    echo json_encode($candidates);
    exit();
}
?>


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Side RCVJ</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>"></link>
    <link rel="stylesheet" href="mediaqueries.css">
    <script src="script.js?v=<?php echo filemtime('script.js'); ?>"></script>
    <script>
        // Fetch job postings for a selected company
        function fetchJobs(companyId) {
            let formData = new FormData();
            formData.append('fetch_jobs', true);
            formData.append('company_id', companyId);

            fetch('', { 
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById('jobDropdownContainer').innerHTML = data;
            });
        }

        // Fetch candidates that applied to the selected job and match them
        function fetchJobDetails(jobId, companyId) {
            let formData = new FormData();
            formData.append('fetch_job_details', true);
            formData.append('job_id', jobId);
            formData.append('company_id', companyId);

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(candidates => {
                if (candidates.error) {
                    console.error(candidates.error);
                    document.getElementById('candidateResults').innerHTML = "An error occurred: " + candidates.error;
                    return;
                }
                // Call the function to populate the candidates table
                populateSmartSearchTable(candidates);
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('candidateResults').innerHTML = "An error occurred: " + error.message;
            });
        }

        // Function to show the initial message when no search has been made
        function showInitialMessage() {
            const tableBody = document.getElementById('candidateTableBody');
            const initialMessageRow = `
                <tr>
                    <td colspan="7" style="text-align: center; margin-top: 20px; color: #2C1875; height: 200px; vertical-align: middle;">
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%;">
                            <img src="img/jobs.png" alt="Search Icon" style="width: 200px; margin-bottom: 10px;">
                            <span style="font-weight: bold; font-size: 20px;">Find the most fitting candidate for the job using smart search.</span>
                        </div>  
                    </td>
                </tr>
            `;
            tableBody.innerHTML = initialMessageRow;
        }

        // Call this function when the page loads
        window.onload = function() {
            showInitialMessage();
        };

        // Populate the candidates table with the fetched data
        function populateSmartSearchTable(data) {
            const rowTemplate = (candidate, statusClass, statusText) => `
                <tr class="tr1">
                    <td class="fullname">${candidate.candidate.fname} ${candidate.candidate.lname}</td>
                    <td><strong>${candidate.candidate.job_title}</strong></td>
                    <td>${candidate.candidate.company_name}</td>
                    <td>${candidate.candidate.date_applied}</td>
                    <td class="status candidates-tooltip-container">
                        <span class="${statusClass}">${statusText}</span>
                        <span class="tooltip-text">${tooltipText}</span>
                    </td>
                    <td class="candidates-tooltip-container">
                        <i class="fa fa-info-circle fa-2xl" aria-hidden="true" style="color: #2C1875; cursor: pointer;" onclick="showInfo(${candidate.candidate.userid})"></i>
                        <span class="tooltip-text">Candidate Information</span>
                    </td>
                    <td class="candidates-tooltip-container">
                        <i class="fa-solid fa-trash fa-2xl" style="color: #EF9B50; cursor: pointer;" onclick="showDialog()"></i>
                        <span class="tooltip-text">Delete Candidate</span>
                    </td>
                </tr>
            `;

            // Clear previous results
            const tableBody = document.getElementById('candidateTableBody');
            tableBody.innerHTML = ''; // Clear previous results

            // Check if there are any candidates
            if (data.length === 0) {
                // Add a row to display a message
                const noCandidatesRow = `
                    <tr class="tr1">
                        <td colspan="7" style="text-align: center; font-weight: bold; color: #999;">
                            No candidates found.
                        </td>
                    </tr>
                `;
                tableBody.innerHTML = noCandidatesRow; // Insert the message row
                return; // Exit the function early
            }

            // Populate table rows
            data.forEach(candidate => {
                // Calculate score ratio
                const score = candidate.score;
                const maxScore = candidate.max_score;
                let statusClass = '';
                let statusText = '';

                // Determine status based on the score
                if (score === maxScore) {
                    statusClass = 'status-label-identical';
                    statusText = 'Identical';
                    tooltipText = 'Perfect fit for the role';
                } else if (score > (maxScore / 2)) {
                    statusClass = 'status-label-Underqualified';
                    statusText = 'Underqualified';
                    tooltipText = 'Great qualifications, but not the best fit for role';
                } else if (score > 0) {
                    statusClass = 'status-label-unqualified';
                    statusText = 'Unqualified';
                    tooltipText = 'Limited qualities, skills, and experience';
                } else {
                    statusClass = 'status-label-not';
                    statusText = 'Not Qualified';
                    tooltipText = 'Does not meet any of the qualifications';
                }

                // Append row to the table
                tableBody.innerHTML += rowTemplate(candidate, statusClass, statusText);
            });
        }
    </script>
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
                <!-- Partner Companies Dropdown -->
                <div>
                    <?php fetchPartnerCompanies(); ?>
                </div>

                <!-- Job Postings Dropdown -->
                <div id="jobDropdownContainer">
                    <!-- Job dropdown will be populated here by JS -->
                </div>

                <!-- Display Candidates -->
                <div id="candidateResults">
                    <!-- Candidates who applied will be displayed here -->
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

                    <tbody id="candidateTableBody">
                        <!-- Candidate results will be populated here -->
                    </tbody>
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

