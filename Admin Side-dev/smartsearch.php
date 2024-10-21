<?php
session_start();
include 'connection.php'; 
$conn = connection();

if (!$conn) {
    die("Database connection failed.");
}

$user_name = 'Sign Up';
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
    $max_score = 0; 

    // Create an array to hold matching details
    $matchingDetails = [];

    // Match classification
    if ($row['classi'] == $job_details['classification']) {
        $score += 1;
        $matchingDetails[] = '<div class="candidates-tooltip-container"><div class="matched">Classification <i class="fa fa-check" aria-hidden="true"></i></div><span class="tooltip-text">'. $job_details['classification'] . '</span>
    </div>';
    } else {
        $matchingDetails[] = '<div class="candidates-tooltip-container">
        <div class="not-matched">Classification <i class="fa fa-times" aria-hidden="true"></i></div>
        <span class="tooltip-text">' . $job_details['classification'] . ' ≠ ' . $row['classi'] . '</span>
    </div>';
    }
    $max_score += 1; // Increment max score

   // Match subclassification
    if ($row['subclassi'] == $job_details['subclassification']) {
        $score += 1;
        $matchingDetails[] = '<div class="candidates-tooltip-container"><div class="matched">Sub-classification <i class="fa fa-check" aria-hidden="true"></i></div><span class="tooltip-text">' . $job_details['subclassification'] . '</span></div>';
    } else {
        $matchingDetails[] = '<div class="candidates-tooltip-container"><div class="not-matched">Sub-classification <i class="fa fa-times" aria-hidden="true"></i></div><span class="tooltip-text">' . $job_details['subclassification'] . ' ≠ ' . $row['subclassi'] . '</span></div>';
    }
    $max_score += 1; // Increment max score

    // Match gender
    if ($row['gender'] == $job_details['gender']) {
        $score += 1;
        $matchingDetails[] = '<div class="candidates-tooltip-container"><div class="matched">Gender <i class="fa fa-check" aria-hidden="true"></i></div><span class="tooltip-text">' . $job_details['gender'] . '</span></div>';
    } else {
        $matchingDetails[] = '<div class="candidates-tooltip-container"><div class="not-matched">Gender <i class="fa fa-times" aria-hidden="true"></i></div><span class="tooltip-text">' . $job_details['gender'] . ' ≠ ' . $row['gender'] . '</span></div>';
    }
    $max_score += 1; // Increment max score

    // Hierarchy of educational attainment
    $educationHierarchy = [
        'Undergraduate' => 1,
        'Highschool Graduate' => 2,
        'College Graduate' => 3
    ];

    // Get the numeric value of the candidate's and job's educational attainment
    $candidateEducationLevel = $educationHierarchy[$row['educational_attainment']] ?? 0;
    $jobEducationLevel = $educationHierarchy[$job_details['educational_attainment']] ?? 0;

    // Match educational attainment based on the hierarchy
    if ($candidateEducationLevel >= $jobEducationLevel) {
        $score += 1; // Add 1 point if candidate meets or exceeds the required education
        $matchingDetails[] = '<div class="candidates-tooltip-container"><div class="matched">Education <i class="fa fa-check" aria-hidden="true"></i></div><span class="tooltip-text">' . $job_details['educational_attainment'] . '</span></div>';
    } else {
        $matchingDetails[] = '<div class="candidates-tooltip-container"><div class="not-matched">Education <i class="fa fa-times" aria-hidden="true"></i></div><span class="tooltip-text">' . $job_details['educational_attainment'] . ' ≠ ' . $row['educational_attainment'] . '</span></div>';
    }
    $max_score += 1; // Increment max score

    // Check if the total years of experience fall within the required range
    if ($job_details['years_of_experience'] !== '-') { // Check if years_of_experience is not "-"
        list($min_exp, $max_exp) = explode('-', $job_details['years_of_experience']);
        if ($row['total_years_experience'] >= (int)$min_exp && $row['total_years_experience'] <= (int)$max_exp) {
            $score += 1; // Add point for matching experience range
            $matchingDetails[] = '<div class="candidates-tooltip-container"><div class="matched">Experience <i class="fa fa-check" aria-hidden="true"></i></div><span class="tooltip-text">Meets required experience</span></div>';
        } elseif ($row['total_years_experience'] > (int)$max_exp) {
            $score += 2; // Add 2 points if experience exceeds the max required years
            $matchingDetails[] = '<div class="candidates-tooltip-container"><div class="near-matched">Experience <i class="fa fa-exclamation-circle" aria-hidden="true"></i></div><span class="tooltip-text">Exceeds required experience</span></div>';
            $max_score += 0.5; // Increment max score for near-matched experience
        } else {
            $matchingDetails[] = '<div class="candidates-tooltip-container"><div class="not-matched">Experience <i class="fa fa-times" aria-hidden="true"></i></div><span class="tooltip-text">Less than required experience</span></div>';
        }
        $max_score += 1; // Increment max score
    } else {
        $matchingDetails[] = '<div class="candidates-tooltip-container"><div class="not-matched">Experience <i class="fa fa-times" aria-hidden="true"></i></div><span class="tooltip-text">Experience not provided</span></div>';
        $max_score += 1; // Increment max score
    }

    // Match job location
    if ($row['location'] == $job_details['job_location']) { // Assuming job_details has job_location
        $score += 1; // Add point if location matches
        $matchingDetails[] = '<div class="candidates-tooltip-container"><div class="matched">Location <i class="fa fa-check" aria-hidden="true"></i></div><span class="tooltip-text">' . $job_details['job_location'] . '</span></div>';
    } else {
        $matchingDetails[] = '<div class="candidates-tooltip-container"><div class="not-matched">Location <i class="fa fa-times" aria-hidden="true"></i></div><span class="tooltip-text">' . $job_details['job_location'] . ' ≠ ' . $row['location'] . '</span></div>';
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
        $matchingDetails[] = '<div class="candidates-tooltip-container"><div class="matched">Skills <i class="fa fa-check" aria-hidden="true"></i></div><span class="tooltip-text">All required skills matched</span></div>';
    } elseif ($total_matched_skills > 0) {
        $matchingDetails[] = '<div class="candidates-tooltip-container"><div class="near-matched">Skills <i class="fa fa-exclamation-circle" aria-hidden="true"></i></div><span class="tooltip-text">Partial skill match</span></div>';
    } else {
        $matchingDetails[] = '<div class="candidates-tooltip-container"><div class="not-matched">Skills <i class="fa fa-times" aria-hidden="true"></i></div><span class="tooltip-text">No skills matched</span></div>';
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
        $matchingDetails[] = '<div class="candidates-tooltip-container"><div class="matched">License <i class="fa fa-check" aria-hidden="true"></i></div><span class="tooltip-text">' . $job_details['cert_license'] . '</span></div>';
    } else {
        $matchingDetails[] = '<div class="candidates-tooltip-container"><div class="not-matched">License <i class="fa fa-times" aria-hidden="true"></i></div><span class="tooltip-text">' . $job_details['cert_license'] . ' not found</span></div>';
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
                document.getElementById('statusFilter').value = 'all';
            });
        }

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
                    document.getElementById('candidateResults').innerHTML = 
                        "An error occurred: " + candidates.error;
                    return;
                }

                // Populate the table with candidates
                populateSmartSearchTable(candidates);

                // Show the filter dropdown after fetching candidates
                document.getElementById('filterContainer').style.display = 'block';

                // Reset the status filter to "all"
                document.getElementById('statusFilter').value = 'all';
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('candidateResults').innerHTML = 
                    "An error occurred: " + error.message;
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

        let currentPage = 1; 
        const resultsPerPage = 5; 
        let candidates = []; 

        function populateSmartSearchTable(data) {
            candidates = data; // Store the candidate data globally
            renderTable(candidates);
        }

        // Function to render table based on candidates and current page
        function renderTable(data) {
            const rowTemplate = (candidate, statusClass, statusText, matchDetails, statusValue) => `
                <tr class="tr1" data-status="${statusValue}">
                    <td class="fullname">${candidate.candidate.fname} ${candidate.candidate.lname}</td>
                    <td><strong>${candidate.candidate.job_title}</strong></td>
                    <td>${candidate.candidate.company_name}</td>
                    <td class="candidates-tooltip-container">
                        <i class="fa fa-file-text fa-2xl" aria-hidden="true" 
                        style="color: #2C1875; cursor: pointer;" 
                        onclick="window.open('../User Side/JobDetails.php?id=${candidate.candidate.job_id}', '_blank')"></i>
                        <span class="tooltip-text">Job Details</span>
                    </td>
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
                        <i class="fa fa-info-circle fa-2xl" aria-hidden="true" 
                        style="color: #2C1875; cursor: pointer;" 
                        onclick='showInfo(${JSON.stringify(candidate.candidate)}, ${candidate.candidate.job_id})'></i>
                        <span class="tooltip-text">Candidate Information</span>
                    </td>
                </tr>
            `;

            const tableBody = document.getElementById('candidateTableBody');
            tableBody.innerHTML = ''; // Clear previous results

            if (candidates.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align: center; font-weight: bold; color: #2C1875;">
                            No candidates found
                        </td>
                    </tr>`;
                updatePaginationControls(0); // No pages if no candidates
                return;
            }

            // Pagination logic
            const filteredCandidates = applyCurrentFilter(); // Apply the current filter
            const totalResults = filteredCandidates.length;
            const totalPages = Math.ceil(totalResults / resultsPerPage);
            const startIndex = (currentPage - 1) * resultsPerPage;
            const endIndex = Math.min(startIndex + resultsPerPage, totalResults);

            // Populate table rows for the current page
            if (totalResults === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align: center; font-weight: bold; color: #2C1875;">
                            No candidates found with the selected status.
                        </td>
                    </tr>`;
            } else {
                for (let i = startIndex; i < endIndex; i++) {
                    const candidate = filteredCandidates[i];
                    const { score, max_score } = candidate;
                    let statusClass = '', statusText = '', statusValue = '';

                    // Determine status based on the score
                    if (score === max_score) {
                        statusClass = 'status-label-identical';
                        statusText = 'Identical';
                        statusValue = 'Identical';
                        tooltipText = 'Perfect fit for the role';
                    } else if (score > max_score) {
                        statusClass = 'status-label-overqualified';
                        statusText = 'Overqualified';
                        statusValue = 'Overqualified';
                        tooltipText = 'Possesses excessive qualifications';
                    } else if (score > 0.6 * max_score) {
                        statusClass = 'status-label-underqualified';
                        statusText = 'Underqualified';
                        statusValue = 'Underqualified';
                        tooltipText = 'Great qualifications, but not the best fit for role';
                    } else if (score > 1) {
                        statusClass = 'status-label-unqualified';
                        statusText = 'Unqualified';
                        statusValue = 'Unqualified';
                        tooltipText = 'Limited qualities, skills, and experience';
                    } else {
                        statusClass = 'status-label-not';
                        statusText = 'Not Qualified';
                        statusValue = 'Not-Qualified';
                        tooltipText = 'Does not meet any of the qualifications';
                    }

                    tableBody.innerHTML += rowTemplate(candidate, statusClass, statusText, candidate.matchingDetails, statusValue);
                }
            }

            // Update pagination controls
            updatePaginationControls(totalPages);
        }

        // Function to apply the current filter and return filtered candidates
        function applyCurrentFilter() {
            const filterValue = document.getElementById('statusFilter').value;
            return candidates.filter(candidate => {
                // Determine the candidate's status based on their score
                const { score, max_score } = candidate;
                let statusValue = '';

                if (score === max_score) {
                    statusValue = 'Identical';
                } else if (score > max_score) {
                    statusValue = 'Overqualified';
                } else if (score > 0.6 * max_score) {
                    statusValue = 'Underqualified';
                } else if (score > 1) {
                    statusValue = 'Unqualified';
                } else {
                    statusValue = 'Not-Qualified';
                }

                return filterValue === 'all' || filterValue === statusValue;
            });
        }

        // Function to create pagination controls
        function updatePaginationControls(totalPages) {
            const paginationContainer = document.getElementById('paginationControls');
            paginationContainer.innerHTML = ''; // Clear existing controls

            // Create pagination links
            if (totalPages > 1) {
                const firstPageLink = `<a href="#" onclick="changePage(1)">First</a>`;
                paginationContainer.innerHTML += firstPageLink;

                for (let i = 1; i <= totalPages; i++) {
                    const pageLink = `<a href="#" onclick="changePage(${i})" class="pagination-link ${i === currentPage ? 'active' : ''}">${i}</a>`;
                    paginationContainer.innerHTML += pageLink;
                }

                const lastPageLink = `<a href="#" onclick="changePage(${totalPages})">Last</a>`;
                paginationContainer.innerHTML += lastPageLink;
            }
        }

        // Function to change page
        function changePage(page) {
            currentPage = page;
            renderTable(candidates); // Use the globally stored candidates data
        }

        // Function to apply filter and update table and pagination
        function applyFilter() {
            currentPage = 1; // Reset to the first page when filter is applied
            renderTable(candidates); // Render table based on the current filter and pagination
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

        function rejectApplication(userId, jobId, remarks) { 
            fetch('reject_candidate.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    userid: userId,
                    jobid: jobId,
                    remarks: remarks
                })
            })
            .then(response => response.text())
            .then(text => {
                console.log('Raw response:', text); 
                const data = JSON.parse(text);

                if (data.success) {
                    alert('Applicant has been rejected.');
                    hideDialogReject();
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
            <a href="partners.php"><i class="fa-solid fa-handshake"></i> <span>Partners</span></a>
            <a href="employees.php"><i class="fa-solid fa-user-tie"></i> <span>Employees</span></a>
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
                <div id="filterContainer" style="display: none;">
                    <select id="statusFilter" class="select-company" onchange="applyFilter()">
                        <option value="all">All</option>
                        <option value="Identical">Identical</option>
                        <option value="Overqualified">Overqualified</option>
                        <option value="Underqualified">Underqualified</option>
                        <option value="Unqualified">Unqualified</option>
                        <option value="Not-Qualified">Not Qualified</option>
                    </select>
                </div>
                <div id="candidateResults">
                    <!-- Candidates who applied will be displayed here -->
                </div>
                <button class="rejected-button" onclick="redirectTo('rejected.php')">Rejected</button>
            </div>
            
            <div>
            <table>
                <thead>
                    <tr class="th1">
                        <th>Candidate</th>
                        <th>Job Title</th>
                        <th>Company</th>
                        <th></th>
                        <th>Date Applied</th>
                        <th>Status</th>
                        <th>Matching</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="candidateTableBody">
                    <!-- Candidate results will be populated here -->
                </tbody>
            </table>
            
            <!-- Pagination Controls -->
            <div id="paginationControls" class="pagination"></div>
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

            <!-- Overlay --> 
            <div class="overlay2" id="overlay2"></div>

            <!-- Dialog Box -->
            <div class="rejected-dialog-box" id="dialogBox-reject">
                <div class="rejected-back-button" onclick="hideDialogReject()">
                    <i class="fas fa-chevron-left"></i> Back
                </div>
                
                <h2 style="text-align: center;">Are you sure you want to reject this candidate?</h2>
                <div class="rejected-form-group">
                    <label for="rejected-remarks">Remarks:</label>
                    <input type="text" id="rejected-remarks">
                    <button class="rejected-save-button">Confirm</button>
                </div>
            </div>
        </div>
    </div>
    <div class="shape-container2">
        <div class="rectangle-4"></div>
        <div class="rectangle-5"></div>
    </div>                            
</body>

