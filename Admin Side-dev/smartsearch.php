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
    $query = "SELECT c.id, a.userid, a.fname, a.lname, a.gender, a.email, a.phone, a.birthday, a.personal_description, 
    e.educational_attainment, e.course, e.school, e.sy_started, e.sy_ended, 
    v.school AS vocational_school, v.course AS vocational_course, v.year_started AS vocational_year_started, v.year_ended AS vocational_year_ended, 
    a.classi, a.subclassi, a.location, c.date_applied, 
    j.job_title, p.company_name, a.profile_image, SUM(je.year_ended - je.year_started) AS total_years_experience
    FROM candidate_list c
    JOIN applicant_table a ON c.userid = a.userid
    LEFT JOIN education_table e ON a.userid = e.userid
    LEFT JOIN vocational_table v ON a.userid = v.userid
    LEFT JOIN job_experience_table je ON a.userid = je.userid
    LEFT JOIN job_table j ON c.job_id = j.id
    LEFT JOIN partner_table p ON j.company_name = p.company_name
    WHERE c.status = 'Pending'
    AND c.job_id = $job_id -- Use job_id instead of job_title
    GROUP BY a.userid";

    $result = mysqli_query($conn, $query);
    $candidates = [];

    while ($row = mysqli_fetch_assoc($result)) {
        // Convert the binary image data to base64
    $profile_image = base64_encode($row['profile_image']);
         // Fetch past jobs for each candidate
    $past_jobs_query = "SELECT je.job_title, je.company_name, je.year_started, je.year_ended 
                        FROM job_experience_table je
                        WHERE je.userid = '".$row['userid']."'";
    $past_jobs_result = mysqli_query($conn, $past_jobs_query);
    $past_jobs = [];
    while ($job_row = mysqli_fetch_assoc($past_jobs_result)) {
        $past_jobs[] = [
        'job_title' => $job_row['job_title'],
        'company_name' => $job_row['company_name'],
        'year_started' => $job_row['year_started'],
        'year_ended' => $job_row['year_ended'],
        ];
    }

    // Fetch applicant's resume from the resume_table
    $resume_query = "SELECT resume FROM resume_table WHERE userid = '".$row['userid']."'";
    $resume_result = mysqli_query($conn, $resume_query);
    $resume_blob = null;
    if ($resume_row = mysqli_fetch_assoc($resume_result)) {
        $resume_blob = $resume_row['resume']; // Store the resume blob
    }

    // Convert the binary resume data to base64 (if needed for frontend display)
    $resume_base64 = $resume_blob ? base64_encode($resume_blob) : null;

    // Score applicants based on matching criteria
    $score = 0;
    $max_score = 0; // Initialize max score

    // Create an array to hold matching details
    $matchingDetails = [];

    // Match classification
    if ($row['classi'] == $job_details['classification']) {
        $score += 1;
        $matchingDetails[] = '<div class="matched">Classification <i class="fa fa-check" aria-hidden="true"></i></div>';
    } else {
        $matchingDetails[] = '<div class="not-matched">Classification <i class="fa fa-times" aria-hidden="true"></i></div>';
    }
    $max_score += 1; // Increment max score

    // Match subclassification
    if ($row['subclassi'] == $job_details['subclassification']) {
        $score += 1;
        $matchingDetails[] = '<div class="matched">Sub-classification <i class="fa fa-check" aria-hidden="true"></i></div>';
    } else {
        $matchingDetails[] = '<div class="not-matched">Sub-classification <i class="fa fa-times" aria-hidden="true"></i></div>';
    }
    $max_score += 1; // Increment max score

    // Match gender
    if ($row['gender'] == $job_details['gender']) {
        $score += 1;
        $matchingDetails[] = '<div class="matched">Gender <i class="fa fa-check" aria-hidden="true"></i></div>';
    } else {
        $matchingDetails[] = '<div class="not-matched">Gender <i class="fa fa-times" aria-hidden="true"></i></div>';
    }
    $max_score += 1; // Increment max score

    // Match educational attainment
    if ($row['educational_attainment'] == $job_details['educational_attainment']) {
        $score += 1;
        $matchingDetails[] = '<div class="matched">Education <i class="fa fa-check" aria-hidden="true"></i></div>';
    } else {
        $matchingDetails[] = '<div class="not-matched">Education <i class="fa fa-times" aria-hidden="true"></i></div>';
    }
    $max_score += 1; // Increment max score

    // Check if the total years of experience fall within the required range
    if ($job_details['years_of_experience'] !== '-') { // Check if years_of_experience is not "-"
        list($min_exp, $max_exp) = explode('-', $job_details['years_of_experience']);
        if ($row['total_years_experience'] >= (int)$min_exp && $row['total_years_experience'] <= (int)$max_exp) {
            $score += 1; // Add point for matching experience range
            $matchingDetails[] = '<div class="matched">Experience <i class="fa fa-check" aria-hidden="true"></i></div>';
        } else {
            $matchingDetails[] = '<div class="not-matched">Experience <i class="fa fa-times" aria-hidden="true"></i></div>';
        }
        $max_score += 1; // Increment max score
    } else {
        $matchingDetails[] = '<div class="not-matched">Experience <i class="fa fa-times" aria-hidden="true"></i></div>';
        $max_score += 1; // Increment max score
    }

    // Match job location
    if ($row['location'] == $job_details['job_location']) { // Assuming job_details has job_location
        $score += 1; // Add point if location matches
        $matchingDetails[] = '<div class="matched">Location <i class="fa fa-check" aria-hidden="true"></i></div>';
    } else {
        $matchingDetails[] = '<div class="not-matched">Location <i class="fa fa-times" aria-hidden="true"></i></div>';
    }
    $max_score += 1; // Increment max score for location match

    // Fetch skills of the applicant
    $skills_query = "SELECT skill_table.skill_name 
                     FROM user_skills_table 
                     JOIN skill_table ON user_skills_table.skill_id = skill_table.skill_id 
                     WHERE user_skills_table.userid = ?";
    
    $stmt_skills = $conn->prepare($skills_query);
    $stmt_skills->bind_param("s", $row['userid']);
    $stmt_skills->execute();
    $skills_result = $stmt_skills->get_result();

    $applicant_skills = [];
    while ($skill_row = $skills_result->fetch_assoc()) {
        $applicant_skills[] = $skill_row['skill_name']; // Store the skill names instead of IDs
    }

    // Fetch required skills for the job
    $required_skills_query = "SELECT skill_table.skill_name 
                              FROM job_skills_table 
                              JOIN job_title_table ON job_skills_table.job_title_id = job_title_table.id
                              JOIN skill_table ON job_skills_table.skill_id = skill_table.skill_id
                              WHERE job_title_table.job_title = ?";
    
    $stmt_required_skills = $conn->prepare($required_skills_query);
    $stmt_required_skills->bind_param("s", $row['job_title']);
    $stmt_required_skills->execute();
    $required_skills_result = $stmt_required_skills->get_result();
    
    $required_skills = [];
    while ($req_skill_row = $required_skills_result->fetch_assoc()) {
        $required_skills[] = $req_skill_row['skill_name'];
    }

    // Calculate skill matching
    $matched_skills = array_intersect($applicant_skills, $required_skills);
    $total_matched_skills = count($matched_skills);
    $total_required_skills = count($required_skills);

    // Add individual skill matches to the score
    $score += $total_matched_skills;
    $max_score += $total_required_skills; // Increment max score by total required skills

    // Determine the collective matching class for $matchingDetails
    if ($total_matched_skills === $total_required_skills) {
        $matchingDetails[] = '<div class="matched">Skills <i class="fa fa-check" aria-hidden="true"></i></div>';
    } elseif ($total_matched_skills > 0) {
        $matchingDetails[] = '<div class="near-matched">Skills <i class="fa fa-exclamation-circle" aria-hidden="true"></i></div>';
    } else {
        $matchingDetails[] = '<div class="not-matched">Skills <i class="fa fa-times" aria-hidden="true"></i></div>';
    }

    // Fetch applicant's licenses
    $licenses_query = "SELECT license_name, month_issued, year_issued, month_expired, year_expired, attachment 
    FROM certification_license_table 
    WHERE userid = '".$row['userid']."'";
    $licenses_result = mysqli_query($conn, $licenses_query);
    $applicant_licenses = [];

    // Store licenses without cleaning for matching
    while ($license_row = mysqli_fetch_assoc($licenses_result)) {
    // Use the license name as it is for matching
    $license_name = $license_row['license_name']; 

    // Convert the blob to base64 for frontend display if needed
    $attachment_base64 = $license_row['attachment'] ? base64_encode($license_row['attachment']) : null;

    // Store the license details
    $applicant_licenses[] = [
    'license_name' => $license_name, // Store raw license name
    'month_issued' => $license_row['month_issued'], 
    'year_issued' => $license_row['year_issued'], 
    'month_expired' => $license_row['month_expired'], 
    'year_expired' => $license_row['year_expired'], 
    'attachment' => $attachment_base64, 
    ];
    }

    // Check if any license matches the job's cert_license
    $license_names = array_column($applicant_licenses, 'license_name'); // Get all license names

    // Perform the matching check
    if (in_array($job_details['cert_license'], $license_names)) {
    $score += 1; // Add point if there's a match
    $matchingDetails[] = '<div class="matched">License <i class="fa fa-check" aria-hidden="true"></i></div>';
    } else {
    $matchingDetails[] = '<div class="not-matched">License <i class="fa fa-times" aria-hidden="true"></i></div>';
    }
    $max_score += 1; // Increment max score

    // Clean license names for HTML display
    foreach ($applicant_licenses as &$license) {
    $license['license_name'] = htmlspecialchars($license['license_name'], ENT_QUOTES); // Clean for HTML display
    }

    // Store candidate data
    $candidates[] = [
        'candidate' => [
            'userid' => $row['userid'],
            'job_id' => $job_id,
            'fname' => $row['fname'],
            'lname' => $row['lname'],
            'location' => $row['location'],
            'gender' => $row['gender'],
            'phone' => $row['phone'],
            'birthday' => (new DateTime($row['birthday']))->format('m/d/y'),
            'email' => $row['email'],
            'personal_description' => $row['personal_description'],
            'job_title' => $row['job_title'],
            'company_name' => $row['company_name'], 
            'date_applied' => (new DateTime($row['date_applied']))->format('m/d/y'), 
            'profile_image' => $profile_image,
            'past_jobs' => $past_jobs,
            'education' => [
                'educational_attainment' => $row['educational_attainment'],
                'course' => $row['course'],
                'school' => $row['school'],
                'sy_started' => $row['sy_started'],
                'sy_ended' => $row['sy_ended'],
            ],
            'vocational' => [
                'course' => $row['vocational_course'],
                'school' => $row['vocational_school'],
                'year_started' => $row['vocational_year_started'],
                'year_ended' => $row['vocational_year_ended'],
            ],
            'skills' => $applicant_skills, 
            'licenses' => $applicant_licenses,
            'resume' => $resume_base64,
        ],
        'score' => $score,
        'max_score' => $max_score,
        'matchingDetails' => $matchingDetails, 
    ];
    }

    // Sort candidates by score (higher score first)
    usort($candidates, function($a, $b) {
        return $b['score'] - $a['score'];
    });

    mysqli_close($conn);
    header('Content-Type: application/json'); // Set header to application/json
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
    <link rel="stylesheet" href="mediaqueries.css?=<?php echo filemtime('mediaqueries.css'); ?>"></link>
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

        function populateSmartSearchTable(data) {
            const rowTemplate = (candidate, statusClass, statusText, matchDetails) => `
                <tr class="tr1">
                    <td class="fullname">${candidate.candidate.fname} ${candidate.candidate.lname}</td>
                    <td><strong>${candidate.candidate.job_title}</strong></td>
                    <td>${candidate.candidate.company_name}</td>
                    <td>${candidate.candidate.date_applied}</td>
                    <td class="status candidates-tooltip-container">
                        <span class="${statusClass}">${statusText}</span>
                        <span class="tooltip-text">${tooltipText}</span>
                    </td>
                     <td>
                        <div class="match-column">
                            ${matchDetails.map(match => match).join('')}
                        </div>
                    </td>
                    <td class="candidates-tooltip-container">
                        <i class="fa fa-info-circle fa-2xl" aria-hidden="true" style="color: #2C1875; cursor: pointer;" onclick='showInfo(${JSON.stringify(candidate.candidate)}, ${candidate.candidate.job_id})'></i>
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
                const noCandidatesRow = `
                    <tr>
                        <td colspan="7" style="text-align: center; font-weight: bold; color: #2C1875; ">No candidates found</td>
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
                } else if (score > (0.6 * maxScore)) {
                    statusClass = 'status-label-Underqualified';
                    statusText = 'Underqualified';
                    tooltipText = 'Great qualifications, but not the best fit for role';
                } else if (score > 1) {
                    statusClass = 'status-label-unqualified';
                    statusText = 'Unqualified';
                    tooltipText = 'Limited qualities, skills, and experience';
                } else {
                    statusClass = 'status-label-not';
                    statusText = 'Not Qualified';
                    tooltipText = 'Does not meet any of the qualifications';
                }

                // Append row to the table
                tableBody.innerHTML += rowTemplate(candidate, statusClass, statusText, candidate.matchingDetails);
            });
        }

        function approveApplication(userId, jobId) { 
        
        fetch('approve_candidate.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                userid: userId,
                jobid: jobId 
            })
        })
        .then(response => {
            return response.text().then(text => {
                console.log('Raw response:', text); 
                return JSON.parse(text);
            });
        })
        .then(data => {
            if (data.success) {
                alert('Applicant has been approved.');
                hideInfo();
                location.reload();
            } else {
                console.error('Error updating candidate status:', data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
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
                        <th>Matching</th>
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
                <h3 style="color: #2C1875">Review applicant information:</h3>
                <p>This information was provided by the applicant.</p>
                <div class="candidate-container">
                    <div class="candidate-header">
                        <div>
                            <h2><?php echo htmlspecialchars($user_name); ?></h2>
                            <div class="locationemail">
                                <i class="fa fa-map-pin" aria-hidden="true"></i><h4>Location</h4>
                            </div>
                            <div class="locationemail">
                                <i class="fa fa-envelope" aria-hidden="true"></i><h4>Email</h4>
                            </div>
                            <div class="locationemail">
                                <i class="fa fa-venus-mars" aria-hidden="true"></i><h4>gender</h4>
                            </div>
                            <div class="locationemail">
                                <i class="fa fa-phone" aria-hidden="true"></i><h4>phone</h4>
                            </div>
                            <div class="locationemail">
                                <i class="fa fa-birthday-cake" aria-hidden="true"></i><h4>birthday</h4>
                            </div>
                        </div>
                        <div>
                            <?php if ($profile_image): ?>
                            <img src="data:image/jpeg;base64,<?php echo $profile_image; ?>" alt="Profile Picture" class="large-profile-photo">
                            <?php else: ?>
                                <img src="img/user.svg" alt="Default Profile Picture" class="large-profile-photo" style="background-color: #d4d6ff00;">
                            <?php endif; ?>
                        </div>
                    </div>
                    <div id="personal-info">
                        <h3>Personal Information</h3>
                        <p id="personal-desc">personal description</p>
                    </div>
                    <!-- Past Jobs Information -->
                    <div id="past-jobs-container">
                        <h3>Past Jobs</h3>
                        <ul class="past-jobs-list" id="past-jobs-list">
                            
                        </ul>
                    </div>
                    <!-- Education Information -->
                    <div id="education">
                        <h3>Educational Attainment</h3>
                        <ul class="education-list" id="education-list">
                            
                        </ul>
                    </div>

                    <!-- Vocational Education Information -->
                    <div id="vocational">
                        <h3>Vocational</h3>
                        <ul class="vocational-list" id="vocational-list">
                            
                        </ul>
                    </div>
                    <div id="skills">
                        <h3>Skills</h3>
                        <ul class="skills-list" id="skills-list">
                            
                        </ul>
                    </div>
                    <div id="licenses-container">
                        <h3>Licenses</h3>
                        <ul id="licenses-list"></ul>
                    </div>
                    <div id="resume-container">
                        <h3>Resume</h3>
                        <embed id="resume-display" style="display: block;" src="" type="application/pdf" width="100%" height="500px" />
                        <p id="no-resume-message" style="display: none;">No resume available</p>
                    </div>
                </div>

                <div class="buttons-container" id="buttons-container">
                    
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
        <div class="shape-container2">
            <div class="rectangle-4"></div>
            <div class="rectangle-5"></div>
        </div>    
        </div>
    </div>
    <div class="shape-container2">
        <div class="rectangle-4"></div>
        <div class="rectangle-5"></div>
    </div>                            
</body>

